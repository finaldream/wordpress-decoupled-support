<?php
/**
 * Rest Single post/page query by permalink
 *
 * TODO: include non-static content like archives and taxonomy
 */

/**
 * Class RestList
 */
class RestList
{

    const API_NAMESPACE = 'wp/v2';

    /**
     * Register menus route.
     * @return void
     */
    public function registerListRoutes()
    {

        register_rest_route(
            static::API_NAMESPACE,
            '/list',
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [$this, 'getList'],
            ]
        );
    }

    public function getList($request)
    {

        global $wpdb;

        $result = [];
        $show_on_front = get_option( 'show_on_front' );
        $types = get_post_types([ 'show_in_rest' => true ]);
        $sqlTypes = implode('","', $types);

        // Add Homepage
        $result['/'] = [
            'page_type' => $show_on_front,
            'post_id' => $show_on_front === 'page' ? get_option( 'page_on_front' ): null,
        ];
        
        $query = "
            SELECT ID, post_title, post_name, post_type 
            FROM $wpdb->posts 
            WHERE post_status = 'publish' AND post_type IN (\"$sqlTypes\")
        ";

        $posts = $wpdb->get_results($query);

        return rest_ensure_response($this->serialize($posts));
    }

    private function serialize($posts)
    {

        $homeUrl = home_url();

        foreach ($posts as $post) {
            $id = $post->ID;
            $post->post_id = $id;
            unset($post->ID);

            $post->path = str_replace($homeUrl, '', get_permalink($id));
        }

        return $response = [
            'result' => $posts,
            'meta' => [
                'total' => count($posts),
            ],
        ];
    }
}
