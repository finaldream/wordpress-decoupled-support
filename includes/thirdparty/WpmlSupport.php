<?php

use DcoupledSupport\UrlUtils;

class WpmlSupport
{

    static function isAvailable()
    {

        return defined('ICL_SITEPRESS_VERSION');
    }


    function __construct()
    {

        if (!self::isAvailable()) {
            return;
        }
        add_filter('rest_list_get_post_data', [$this, 'sitepressGetPostDataFilter']);
        add_filter('rest_list_prepare_response', [$this, 'sitepressPrepareResponse']);
        add_filter('rest_permalink_get_template', [$this, 'sitepressPermalinkgetTemplate'], 1, 2);
        add_filter('rest_menus_get_active_languages', [$this, 'getActiveLanguagesFilter'], 10, 1);

        // Removes WPML-filters, which seem modify the permalinks based on the current (default) language
        remove_all_filters('page_link');
    }

    public function getActiveLanguagesFilter($languages) {

        $activeLanguages = wpml_get_active_languages_filter('', 'skip_missing=1');

        $result = [];

        foreach ($activeLanguages as $language) {
            $result[] = $language['language_code'];
        }

        return $result;

    }


    public function sitepressGetPostDataFilter($post)
    {

        $language = wpml_get_language_information(null, $post['ID']);

        if (!empty($language['language_code'])) {
            $post['language']     = $language['language_code'];
            $post['translations'] = $this->getTranslations($post);
        }

        return $post;
    }


    public function sitepressPrepareResponse($response)
    {


        $activeLanguages = wpml_get_active_languages_filter('', 'skip_missing=0');

        $languages = [];

        foreach ($activeLanguages as $language) {
            $languages[] = [
                'native_name' => $language['native_name'],
                'name' => $language['translated_name'],
                'url' => $language['url'],
                'language' => $language['language_code'],
                'locale' => $language['default_locale'],
            ];
        }

        $response['meta']['languages'] = $languages;

        return $response;
    }

    public function sitepressPermalinkgetTemplate($template, $post) {

        // Check the default translation for being set as 'page_for_posts".
        if ('page' == get_option('show_on_front') && $template != 'index') {

            $defaultLanguage = wpml_get_default_language();
            $originalId = wpml_object_id_filter($post->ID, $post->post_type, true, $defaultLanguage);

            if (get_option('page_on_front') == $originalId) {
                return 'index';
            }
        }

        return $template;

    }


    private function getTranslations($post)
    {

        $result = [];

        global $sitepress;

        $trid         = $sitepress->get_element_trid($post['ID']);
        $translations = $sitepress->get_element_translations($trid);

        foreach ($translations as $lang => $translation) {
            if ($translation->post_status !== 'publish') {
                continue;
            }

            $result[]               = [
                'ID' => $translation->element_id,
                'permalink' => UrlUtils::stripAllDomain(get_permalink($translation->element_id)),
                'language' => $translation->language_code,
                'post_title' => $translation->post_title,
            ];
        }

        return $result;
    }

	/**
	 * Extract and set active language from URL
	 *
	 * @param string $path
	 * @return string|string[]|null
	 */
    static function extractAndSetActiveLanguage($path)
	{
		global $sitepress;

		$settings = $sitepress->get_settings();
		$activeLanguages = $sitepress->get_active_languages();

		if (isset($sitepress) && $lang = self::extractLangFromPath($settings, $activeLanguages, $path)) {
			$path = preg_replace('{^/?' . $lang . '(.*$)}', '$1', $path);
			$sitepress->switch_lang($lang, true);
		}

		return $path;
	}

	/**
	 * Extract language code from URL
	 *
	 * @param array $settings
	 * @param array $activeLanguages
	 * @param string $path
	 * @return bool|mixed
	 */
	static function extractLangFromPath($settings, $activeLanguages, $path)
	{
		if ( $settings['language_negotiation_type'] == 1 ) {
			$parts = array_filter(explode( '/', $path ));
			$lang = array_shift($parts);

			return in_array( $lang, array_keys($activeLanguages) ) ? $lang : false;
		}

		return false;
	}
}
