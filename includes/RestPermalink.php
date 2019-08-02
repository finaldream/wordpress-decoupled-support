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

    $posts = get_posts([
        'name' => $name,
        'post_type' => 'any',
        'post_status' => 'published',
    ]);

    if (count($posts) > 0) {
        return $posts[0];
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

        if (!is_string($q) || empty($q)) {
            return new WP_Error('REST_INVALID', 'Please provide a valid permalink', ['status' => 400]);
        }

        if (WpmlSupport::isAvailable()) {
			$q = WpmlSupport::extractAndSetActiveLanguage($q);
		}

        // try finding a post "the official" way
        if ($q === '/' && get_option('show_on_front') === 'page') {
            $postId = get_option('page_on_front');
        } else {
            $postId = url_to_postid($q);
        }

        $post = get_post($postId);

        // alternately, try finding the post by it's slug
        if (!$post) {
            $post = findPostBySlug($q);
        }

        if (!$post) {
            return $this->handleNotFound($q);
        }

        if ( trim(wp_make_link_relative(get_permalink($post)), '/') != trim($q, '/') ) {
            return $this->handleNotFound($q);
        }

        $serialized = $this->serialize($post, $request);

        return rest_ensure_response($serialized);
    }

    function getTemplate($post) {


        $isHome = 'page' == get_option('show_on_front') &&  $post->ID == get_option('page_on_front');

        $template = ($isHome) ? 'index' : $post->post_type;

        return apply_filters('rest_permalink_get_template', $template, $post);

    }

    protected function handleNotFound(string $queryString)
    {   
        if (defined('DECOUPLED_NOTFOUND_SLUG') && !empty(DECOUPLED_NOTFOUND_SLUG) ) {
            add_filter('rest_post_dispatch', function (\WP_REST_Response $response, \WP_REST_Server $server, \WP_REST_Request $request) {
                if ($response->status == 404 ){
                    $notFoundPage = findPostBySlug(DECOUPLED_NOTFOUND_SLUG);
                    if (isset($notFoundPage) && $notFoundPage instanceof \WP_Post) { 
                        $controller = new WP_REST_Posts_Controller($notFoundPage->post_type);
                        $prepared = $controller->prepare_item_for_response($notFoundPage, $request);     
                        $response->data['result'] = [$prepared->data];
                        $response->data['meta'] = [
                            'type' => $notFoundPage->post_type,
                            'view_mode' => 'single',
                            'template' => '404-error',
                        ];
                    }
                }
            
                return $response;
            
            }, 10, 3);
        }

        return new WP_Error('REST_NOT_FOUND', 'No single found', ['status' => 404, 'url' => $queryString]);
    }

    protected function serialize($post, $request, $viewMode = 'single')
    {

        $controller = new WP_REST_Posts_Controller($post->post_type);

        $prepared = $controller->prepare_item_for_response($post, $request);
        $template = $prepared->data['template'] ?: '';

        return $response = [
            'result' => [$prepared->data],
            'meta' => [
                'type' => $post->post_type,
                'view_mode' => $viewMode,
                'template' => $template,
            ],
        ];
    }
}
