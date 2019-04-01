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
    public function registerRoutes()
    {

        register_rest_route(
            static::API_NAMESPACE,
            '/list',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getList'],
            ]
        );
    }


    public function getList($request)
    {

        global $wpdb;

        $result        = [];
        $show_on_front = get_option('show_on_front');
        $types         = get_post_types(['show_in_rest' => true]);
        $sqlTypes      = implode('","', $types);

        // Add Homepage
        $result['/'] = [
            'page_type' => $show_on_front,
            'post_id' => $show_on_front === 'page' ? get_option('page_on_front') : null,
        ];

        $query = "
            SELECT ID, post_title, post_name, post_type, post_date_gmt, post_modified_gmt            
            FROM $wpdb->posts 
            LEFT JOIN {$wpdb->prefix}icl_translations ON 
                ($wpdb->posts.ID = {$wpdb->prefix}icl_translations.element_id)
            WHERE post_status = 'publish' 
            AND post_type IN (\"$sqlTypes\")
            AND {$wpdb->prefix}icl_translations.language_code = '".ICL_LANGUAGE_CODE."'
        ";

        $posts = $wpdb->get_results($query);

        return rest_ensure_response($this->prepareResponse($posts));
    }


    private function prepareResponse($posts)
    {

        $homeUrl = home_url();
        $result  = [];

        foreach ($posts as &$post) {
            $post              = (array) $post;
            $post['permalink'] = get_permalink($post['ID']);

            $post              = apply_filters('rest_list_get_post_data', $post);
            $post['permalink'] = str_replace($homeUrl, '', $post['permalink']);

            $result[] = $post;
        }

        $response = [
            'result' => $result,
            'meta' => [
                'total' => count($posts),
            ],
        ];

        return apply_filters('rest_list_prepare_response', $response);
    }
}
