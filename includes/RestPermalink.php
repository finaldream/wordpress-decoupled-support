<?php
/**
 * Rest Single post/page query by permalink
 */

/**
 * TODO: validate parent slugs as well
 * @param $slug
 * @return null
 */
function findPostBySlug($slug)
{

    $name = trim($slug, '/');

    if (strpos($name, '/')) {
        $parts = explode('/', $name);
        $name  = array_pop($parts);
    }

    $query = new WP_Query([
    	'name' => $name,
	    'post_type' => 'any',
	    'post_status' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'inherit'],
	    'posts_per_page' => 1,
    ]);

    if (!empty($query->posts)) {
	    return array_pop($query->posts);
    }

    return null;
}


/**
 * Class RestSingle
 */
class RestPermalink
{

    const API_NAMESPACE = 'wp/v2';

    /**
     * Register menus route.
     * @return void
     */
    public function registerRoutes()
    {

        register_rest_route(static::API_NAMESPACE, '/permalink', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getPermalink'],
                'args' => [
                    'q' => [
                        'default' => false
                    ],
                ],
            ]
        ]);
    }

    public function getPermalink($request)
    {

        $q = $request['q'];
	    $preview = $request['preview'];
	    $previewToken = $request['token'];

	    if (!is_string($q) || empty($q)) {
            return new WP_Error('REST_INVALID', 'Please provide a valid permalink', ['status' => 400]);
        }

        // try finding a post "the official" way
        if ($q === '/' && get_option('show_on_front') === 'page') {
            $postId   = get_option('page_on_front');
        } else {
            $args['name'] = $q;
            $postId       = url_to_postid($q);
        }

        $post = get_post($postId);

        // alternately, try finding the post by it's slug
        if (!$post) {
            $post = findPostBySlug($q);
        }

	    $validPreview = ($post && !empty($preview) && !empty($previewToken) && (base64_decode( $previewToken) === 'dcoupled-preview-token_'.$post->ID));

	    if (!$post || ($post && $post->post_status !== 'publish' && !$validPreview)) {
            return new WP_Error('REST_NOT_FOUND', 'No single found', ['status' => 404, 'url' => $q]);
        }

	    if ($validPreview) {
	        $preview = wp_get_post_autosave( $post->ID );

	        if ( is_object( $preview ) ) {
		        $post->ID = $preview->ID;
	        }
        }

        $serialized = $this->serialize($post, $request);

        return rest_ensure_response($serialized);
    }

    function getTemplate($post) {


        $isHome = 'page' == get_option('show_on_front') &&  $post->ID == get_option('page_on_front');

        $template = ($isHome) ? 'index' : $post->post_type;

        return apply_filters('rest_permalink_get_template', $template, $post);

    }

    private function serialize($post, $request)
    {

        $controller = new WP_REST_Posts_Controller($post->post_type);

        $prepared = $controller->prepare_item_for_response($post, $request);
        $template = $prepared->data['template'] ?: '';

        return $response = [
            'result' => [$prepared->data],
            'meta' => [
                'type' => $post->post_type,
                'view_mode' => 'single',
                'template' => $template,
            ],
        ];
    }
}
