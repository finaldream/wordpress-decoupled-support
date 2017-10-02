<?php

include_once(__DIR__ . '/../../lib/UrlUtils.php');

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

        // Removes WPML-filters, which seem modify the permalinks based on the current (default) language
        remove_all_filters('page_link');
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
                'permalink' => UrlUtils::stripDomain(get_permalink($translation->element_id)),
                'language' => $translation->language_code,
                'post_title' => $translation->post_title,
            ];
        }

        return $result;
    }
}
