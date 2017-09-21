<?php
/**
 * Rest Settings
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 30.08.2017
 */

class RestAdmin {

	/**
	 * Add Admin settings
	 */
	public function addSettings() {
		add_action( 'admin_menu', [$this, 'menu'] );
		add_action( 'admin_init', [$this, 'settings'] );
	}

	/**
	 * Option menu
	 */
	public function menu() {
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
	public function settings() {
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
			'dcoupled_upload_url',
			[$this, 'sanitize']
		);
	}

	/**
	 * Setting page
	 */
	public function settingPage() {
		if (isset($_REQUEST['generate_all'])) {
			var_dump($_REQUEST['generate_all']);
		}

		include_once 'templates/settings.php';
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param string $input
	 * @return string
	 */
	public function sanitize( $input )
	{
		if (is_string($input)) {
			return sanitize_text_field($input);
		}

		return '';
	}
}