<?php
/**
 * Register custom rest fields
 *
 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/modifying-responses/
 */

use DecoupledSupport\UrlUtils;

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

        $postTypes         = get_post_types(['show_in_rest' => true]);
        $this->objectTypes = apply_filters('decoupled_rest_allowed_object_types', $postTypes);

        $this->registerPermalinkField();
        $this->registerPostClassesField();
        $this->registerPostThumbnailField();
        $this->registerACFField();
        $this->registerWPMLField();
        $this->registerTemplateField();
        $this->registerPostMetaField();
        $this->registerCategoriesField();
    }

    /**
     * Template field
     */
    public function registerTemplateField()
    {

        register_rest_field($this->objectTypes, 'template', [
            'get_callback' => function ($object) {

                $isHome = 'page' == get_option('show_on_front') &&  $object['id'] == get_option('page_on_front');
                $template = ($isHome) ? 'index' : $object['type'];

                return apply_filters('rest_permalink_get_template', $template, get_post($object['id']));

            },
            'update_callback' => null,
            'schema' => [
                'description' => __('Template type'),
                'type' => 'string'
            ],
        ]);
    }


    /**
     * Relative permalink field
     */
    public function registerPermalinkField()
    {

        register_rest_field($this->objectTypes, 'permalink', [
            'get_callback' => function ($object) {
                return UrlUtils::stripAllDomain('' . get_permalink());
            },
            'update_callback' => null,
            'schema' => [
                'description' => __('Permalink'),
                'type' => 'string'
            ],
        ]);
    }


    /**
     * Add Post classes to rest fields
     */
    public function registerPostClassesField()
    {

        register_rest_field($this->objectTypes, 'classes', [
            'get_callback' => function ($object) {

                return join(' ', get_post_class());
            },
            'update_callback' => null,
            'schema' => [
                'description' => __('Post classes'),
                'type' => 'string'
            ],
        ]);
    }


    /**
     * Add Post thumbnail to rest fields
     */
    public function registerPostThumbnailField()
    {

        register_rest_field($this->objectTypes, 'thumbnail', [
            'get_callback' => function ($object) {

        	    $object = get_post($object['id']);
        	    $post = (!has_post_thumbnail($object) && $object->post_type === 'revision') ? get_post($object->post_parent) : $object ;

                $thumbnail = get_post(get_post_thumbnail_id($post));

                return [
                    'url' => get_the_post_thumbnail_url($post),
                    'description' => $thumbnail->post_content,
                    'copyright' => $thumbnail->post_excerpt,
                ];
            },
            'update_callback' => null,
            'schema' => [
                'description' => __('Post thumbnail'),
                'type' => 'array'
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

        register_rest_field($this->objectTypes, 'acf', [
                'get_callback' => function ($object) {

                    $allowed   = apply_filters('decoupled_rest_allowed_acf_fields', []);
                    $acfFields = get_fields($object['id']);

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
                'schema' => [
                    'description' => __('ACF Fields'),
                    'type' => 'array'
                ],
            ]
        );
    }


    /**
     * WPML Locate filed
     */
    public function registerWPMLField()
    {

        if (!function_exists('wpml_get_language_information')) {
            return;
        }

        register_rest_field($this->objectTypes, 'wpml', [
            'get_callback' => function ($object) {

                $languages    = apply_filters('wpml_active_languages', []);
                $translations = [];
                $frontPageId  = get_option('page_on_front');
                $frontPages   = [];

                if ($frontPageId) {
                    $frontPages = array_values(array_map(function ($language) use ($frontPageId) {

                        return apply_filters('wpml_object_id', $frontPageId, 'page', false, $language['language_code']);
                    }, $languages));
                }

                foreach ($languages as $language) {

                    $postId = apply_filters('wpml_object_id', $object['id'], 'post', false, $language['language_code']);

                    if (!$postId || $postId === $object['id']) {
                        continue;
                    }

                    $post = get_post($postId);

                    if ($post && $post->post_status !== 'publish') {
                        continue;
                    }

                    $permalink = apply_filters('WPML_filter_link', $language['url'], $language);

                    // Not add uri into front pages
                    if (!in_array($postId, $frontPages)) {
                        $uri = get_page_uri($postId);

                        if (strpos($permalink, '?') !== false) {
                            $permalink = str_replace('?', '/' . $uri . '/?', $permalink);
                        } else {
                            $permalink .= (substr($permalink, -1) !== '/') ? '/' : '';
                            $permalink .= $uri . '/';
                        }
                    }

                    $translations[] = [
                        'locale' => $language['default_locale'],
                        'code' => $language['language_code'],
                        'id' => $post->ID,
                        'post_title' => $post->post_title,
                        'permalink' => UrlUtils::stripAllDomain(get_permalink($post->ID)),
                    ];
                }

                return [
                    'current_locate' => wpml_get_language_information($object),
                    'translations' => $translations,
                ];
            },
            'update_callback' => null,
            'schema' => [
                'description' => __('WPML Locate'),
                'type' => 'array'
            ],
        ]);
    }

    /**
     * Add Post classes to rest fields
     */
    public function registerPostMetaField()
    {

        register_rest_field($this->objectTypes, 'meta', [
            'get_callback' => function ($object) {

                return get_post_meta($object['id']);
            },
            'update_callback' => null,
            'schema' => [
                'description' => __('Post Meta'),
                'type' => 'array'
            ],
        ]);
    }

    /**
     * Add Post Categories to rest fields
     */
    public function registerCategoriesField() {

        register_rest_field( $this->objectTypes, 'categories', [
            'get_callback'    => function ( $object ) {
                return get_the_category( $object['id'] );
            },
            'update_callback' => null,
            'schema'          => [
                'description' => __( 'Category' ),
                'type'        => 'object'
            ],
        ] );

    }
}
