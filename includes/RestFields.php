<?php
/**
 * Register rest fields
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 12.06.2017
 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/modifying-responses/
 */

/**
 * Class RestFields
 */
class RestFields
{
    /**
     * Add rest fields
     */
    public function registerRestFields()
    {
        $this->registerPostClassesField();
        $this->registerPostThumbnailField();
        $this->registerACFField();
    }

    /**
     * Add Post classes to rest fields
     */
    public function registerPostClassesField()
    {
        register_rest_field( ['post', 'page', 'video', 'short_post'], 'classes', [
            'get_callback' => function( $object ) {
                return join( ' ', get_post_class());
            },
            'update_callback' => null,
            'schema' => [
                'description' => __( 'Post classes' ),
                'type'        => 'string'
            ],
        ]);
    }

    /**
     * Add Post thumbnail to rest fields
     */
    public function registerPostThumbnailField()
    {
        register_rest_field( ['post', 'page', 'video', 'short_post'], 'thumbnail', [
            'get_callback' => function( $object ) {
                $thumbnail = get_post(get_post_thumbnail_id());

                return [
                    'url' => get_the_post_thumbnail_url(),
                    'description' => $thumbnail->post_content,
                    'copyright' => $thumbnail->post_excerpt,
                ];
            },
            'update_callback' => null,
            'schema' => [
                'description' => __( 'Post thumbnail' ),
                'type'        => 'array'
            ],
        ]);
    }

    /**
     * Add ACF to rest fields
     */
    public function registerACFField()
    {
    	if (!function_exists('get_fields')) {
    		return;
	    }

        register_rest_field( ['post', 'page', 'video', 'short_post'], 'acf', [
                'get_callback'    => function($object) {
                    $allowed = apply_filters('dcoupled_rest_allowed_acf_fields', []);
                    $acfFields = get_fields($object[ 'id' ]);

                    if (!empty($allowed)) {
	                    $fields = [];

	                    foreach ($allowed as $field) {
		                    $fields[$field] = $acfFields[$field];
	                    }

	                    return $fields;
                    }

                    return $acfFields;
                },
                'update_callback' => null,
                'schema'          => [
                    'description' => __( 'ACF Fields' ),
                    'type'        => 'array'
                ],
            ]
        );
    }
}
