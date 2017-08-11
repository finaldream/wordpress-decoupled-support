<?php
/**
 * Rest Single post/page query by permalink
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 26.07.2017
 */

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
    public function registerSingleRoutes() {

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

    public function getSingle($request) {
        $q = $request['q'];

        if (is_string($q) && !empty($q)) {
            $types = get_post_types([ 'show_in_rest' => true ]);

            $args = [
                'post_type' => array_keys($types),
                'post_status' => 'publish',
                'posts_per_page' => 1,
            ];

            if ($q === '/' && get_option('show_on_front') === 'page') {
                $args['p'] = get_option( 'page_on_front' );
                $template = 'index';
            } else {
                $args['name'] = $q;
            }

            $query = new WP_Query( $args );

            if( $query->have_posts() ) {
                $query->the_post();

                $template = (isset($template)) ? $template : $query->post->post_type;

                $serialized = $this->serialize($query->post, $request, $template);

                return rest_ensure_response($serialized);
            } else {
                return new WP_Error( 'REST_NOT_FOUND', 'No single found', ['status' => 404]);
            }

        } else {
            return new WP_Error('REST_INVALID', 'Please provide a valid permalink', ['status' => 400]);
        }
    }

    private function serialize($post, $request, $template) {
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
