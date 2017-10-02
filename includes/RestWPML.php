<?php
/**
 * Rest WPML Support
 */


class RestWPML
{

    public $availableLangs = [];


    /**
     * RestWPML constructor.
     */
    public function __construct()
    {

        if (!function_exists('wpml_get_active_languages_filter')) {
            return;
        }

        $this->availableLangs = wpml_get_active_languages_filter('', array('skip_missing' => false,));
    }


    /**
     * Register REST API filters
     */
    public function registerFilters()
    {

        if (!function_exists('wpml_get_active_languages_filter')) {
            return;
        }

        $postTypes = get_post_types(['show_in_rest' => true]);

        foreach ($postTypes as $type) {
            add_filter('rest_' . $type . '_query', [$this, 'languageFilter'], 10, 2);
        }
    }


    /**
     * Language filter
     * @param $args
     * @param $request
     *
     * @return mixed
     */
    public function languageFilter($args, $request)
    {

        if (!empty($request['lang']) && !empty($this->availableLangs) && (!isset($GLOBALS['icl_language_switched']) || !$GLOBALS['icl_language_switched'])) {
            do_action('wpml_switch_language', $request['lang']);
        }

        return $args;
    }
}