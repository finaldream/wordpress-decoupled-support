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
        $name = array_pop($parts);
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
class RestSingle
{

    const API_NAMESPACE = 'wp/v2';

    /**
     * Register menus route.
     * @return void
     */
    public function registerSingleRoutes()
    {

        register_rest_route( static::API_NAMESPACE, '/permalink', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [$this, 'getSingle'],
                'args'     => [
                    'q' => [
                        'default' => false
                    ],
                ],
            ]
        ]);
    }

    public function getSingle($request)
    {
        $q = $request['q'];

        if (!is_string($q) || empty($q)) {
            return new WP_Error('REST_INVALID', 'Please provide a valid permalink', ['status' => 400]);
        }

        // try finding a post "the official" way
        if ($q === '/' && get_option('show_on_front') === 'page') {
            $postId = get_option( 'page_on_front' );
            $template = 'index';
        } else {
            $args['name'] = $q;
            $postId = url_to_postid($q);
        }

        $post = get_post($postId);
        
        // alternately, try finding the post by it's slug
        if (!$post) {
            $post = findPostBySlug($q);
        }

        if (!$post) {
            return new WP_Error( 'REST_NOT_FOUND', 'No single found', ['status' => 404, 'url' => $q]);
        }

        $template = (!empty($template)) ? $template : $post->post_type;


        $serialized = $this->serialize($post, $request, $template);

        return rest_ensure_response($serialized);
    }

    private function serialize($post, $request, $template)
    {
        $controller = new WP_REST_Posts_Controller($post->post_type);

        $prepared = $controller->prepare_item_for_response($post, $request);

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
