<?php
/**
 * Rest Settings
 */

use \DecoupledSupport\UrlUtils;

class RestAdmin
{

    /**
     * Add Admin settings
     */
    public function addSettings()
    {

        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'settings']);
        add_filter('preview_post_link', [$this, 'previewPostLink'], 100, 2);
		add_filter('get_sample_permalink', [$this, 'samplePermalink'], 100, 5);
		add_filter('get_sample_permalink_html', [$this, 'samplePermalinkHTML'], 100, 1);
		add_filter('post_row_actions', [$this, 'rowActions'], 100, 1);
	    add_filter('page_row_actions', [$this, 'rowActions'], 100, 1);

        add_action('current_screen', [$this, 'currentScreen']);
    }

    /**
     * Current screen actions
     */
    public function currentScreen() {

        if ( function_exists('get_current_screen')) {
            $screen = get_current_screen();

            // Only override WP permalink on edit screen to avoid side effects
            if ($screen->base === 'post') {
                add_filter('post_link', [$this, 'alterPermalink'], 100, 3);
                add_filter('page_link', [$this, 'alterPermalink'], 100, 3);
            }
        }
    }

    /**
     * @param $permalink
     * @param $post
     * @param $leavename
     *
     * @return string
     */
    public function alterPermalink($permalink, $post, $leavename) {
        $clientDomain = get_option('decoupled_client_domain', false);

        if (!empty($clientDomain) && strpos($permalink, $clientDomain) === FALSE) {
            return UrlUtils::getInstance()->replaceDomain($permalink);
        }

        return $permalink;
    }

	/**
	 * Override sample permalink
	 *
	 * @param $permalink
	 * @param $postId
	 * @param $title
	 * @param $name
	 * @param $post
	 *
	 * @return array
	 */
    public function samplePermalink($permalink, $postId, $title, $name, $post) {

        $clientDomain = get_option('decoupled_client_domain', false);

        list($replaceable, $slug) = $permalink;

        if (!empty($clientDomain) && strpos($replaceable, $clientDomain) === FALSE) {
            return [
                UrlUtils::getInstance()->replaceDomain($replaceable),
                $slug,
            ];
        }

        return $permalink;
    }

	/**
	 * Replace permalink on post editor
	 *
	 * @param $return
	 *
	 * @return mixed
	 */
    public function samplePermalinkHTML($link) {

	    $clientDomain = get_option('decoupled_client_domain', false);

	    if (!empty($clientDomain) && strpos($link, $clientDomain) === FALSE) {
            $link = UrlUtils::getInstance()->replaceDomain($link);
	    }

	    return $link;

    }

	/**
	 * Replace view link on Post/Page listing table
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
    public function rowActions($actions) {

	    $clientDomain = get_option('decoupled_client_domain', false);

	    if (!empty($clientDomain)) {
	        if (isset($actions['view']) && strpos($actions['view'], $clientDomain) === FALSE) {
                $actions['view'] = UrlUtils::getInstance()->replaceDomain($actions['view']);
            }

            if (isset($actions['preview']) && strpos($actions['preview'], $clientDomain) === FALSE) {
                $actions['preview'] = UrlUtils::getInstance()->replaceDomain($actions['preview']);
            }
	    }

	    return $actions;
    }

	/**
	 * Override WP preview post link
	 *
	 * @param $original
	 * @param $post
	 *
	 * @return string
	 */
    public function previewPostLink($original, $post) {
		$clientDomain = get_option('decoupled_client_domain', false);

		if (!empty($clientDomain)) {

			return sprintf('%s/preview/?preview_id=%s&token=%s',
                untrailingslashit($clientDomain),
				$post->ID,
				base64_encode( 'decoupled-preview-token_'.$post->ID )
			);
		}

    	return $original;
    }

    /**
     * Option menu
     */
    public function menu()
    {

        add_options_page(
            'Decoupled Settings',
            'Decoupled Settings',
            'manage_options',
            'decoupled-support-settings',
            [$this, 'settingPage']
        );
    }

    /**
     * Setting fields
     */
    public function settings()
    {

        register_setting(
            'decoupled-settings-group',
            'decoupled_token',
            [$this, 'sanitize']
        );

        register_setting(
            'decoupled-settings-group',
            'decoupled_cache_invalidation_url',
            [$this, 'sanitize']
        );

	    register_setting(
		    'decoupled-settings-group',
		    'decoupled_client_domain',
		    [$this, 'sanitize']
	    );

        register_setting(
            'decoupled-settings-group',
            'decoupled_upload_url',
            [$this, 'sanitize']
        );
    }

    /**
     * Setting page
     */
    public function settingPage()
    {
        include_once 'templates/settings.php';
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param string $input
     * @return string
     */
    public function sanitize($input)
    {

        if (is_string($input)) {
            return sanitize_text_field($input);
        }

        return '';
    }
}