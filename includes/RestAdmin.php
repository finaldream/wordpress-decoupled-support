<?php
/**
 * Rest Settings
 */

use \DcoupledSupport\UrlUtils;

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
		$clientDomain = get_option('dcoupled_client_domain', false);


		if (!empty($clientDomain)) {
			list($permalink, $post_name) = get_sample_permalink($post);
			$link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );

			return sprintf('%s%s?preview=true&token=%s',
				$clientDomain,
				UrlUtils::stripAllDomain($link),
				base64_encode( 'dcoupled-preview-token_'.$post->ID )
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
            'Dcoupled Settings',
            'Dcoupled Settings',
            'manage_options',
            'dcoupled-support-settings',
            [$this, 'settingPage']
        );
    }

    /**
     * Setting fields
     */
    public function settings()
    {

        register_setting(
            'dcoupled-settings-group',
            'dcoupled_token',
            [$this, 'sanitize']
        );

        register_setting(
            'dcoupled-settings-group',
            'dcoupled_publish_trigger_url',
            [$this, 'sanitize']
        );

	    register_setting(
		    'dcoupled-settings-group',
		    'dcoupled_client_domain',
		    [$this, 'sanitize']
	    );

        register_setting(
            'dcoupled-settings-group',
            'dcoupled_upload_url',
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