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
	public $objectTypes;

    /**
     * Add rest fields
     */
    public function registerRestFields()
    {
    	$postTypes = get_post_types([ 'show_in_rest' => true ]);
	    $this->objectTypes = apply_filters('dcoupled_rest_allowed_object_types', $postTypes);

	    $this->registerPermalinkField();
        $this->registerPostClassesField();
        $this->registerPostThumbnailField();
        $this->registerACFField();
        $this->registerWPMLField();
    }

	/**
	 * Relative permalink field
	 */
    public function registerPermalinkField() {
	    register_rest_field( $this->objectTypes, 'permalink', [
		    'get_callback' => function( $object ) {
	    	    $domainRegex = '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i';
			    return preg_replace ($domainRegex, '$2', '' . get_permalink());
		    },
		    'update_callback' => null,
		    'schema' => [
			    'description' => __( 'Permalink' ),
			    'type'        => 'string'
		    ],
	    ]);
    }

    /**
     * Add Post classes to rest fields
     */
    public function registerPostClassesField()
    {
        register_rest_field( $this->objectTypes, 'classes', [
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
        register_rest_field( $this->objectTypes, 'thumbnail', [
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

        register_rest_field( $this->objectTypes, 'acf', [
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

	/**
	 * WPML Locate filed
	 */
    public function registerWPMLField() {
	    if (!function_exists('wpml_get_language_information')) {
		    return;
	    }

	    register_rest_field( $this->objectTypes, 'wpml', [
		    'get_callback' => function( $object ) {
			    $languages = apply_filters('wpml_active_languages', []);
			    $translations = [];

			    foreach ($languages as $language) {

				    //$postId = wpml_object_id_filter($object['id'], 'post', false, $language['language_code']);
				    $postId = apply_filters( 'wpml_object_id', $object['id'], 'post', false, $language['language_code']);

				    if (!$postId || $postId === $object['id']) {
				    	continue;
				    }

				    $post = get_post($postId);
				    $uri = get_page_uri($postId);
				    $permalink = apply_filters('WPML_filter_link', $language['url'], $language);

				    if (strpos($permalink, '?') !== false) {
					    $permalink = str_replace('?', '/'.$uri.'/?', $permalink);
				    } else {
					    $permalink .= (substr($permalink, -1) !== '/') ? '/' : '';
					    $permalink .= $uri . '/';
				    }

				    $translations[] = [
				    	'locale' => $language['default_locale'],
					    'id' => $post->ID,
					    'post_title' => $post->post_title,
					    'permalink' => $permalink,
				    ];
			    }

			    return [
			    	'current_locate' => wpml_get_language_information($object),
				    'translations' => $translations,
			    ];
		    },
		    'update_callback' => null,
		    'schema' => [
			    'description' => __( 'WPML Locate' ),
			    'type'        => 'array'
		    ],
	    ]);
    }
}
