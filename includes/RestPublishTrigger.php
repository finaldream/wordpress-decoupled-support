<?php
/**
 * Publish Web-Hook
 */


class RestPublishTrigger {

	public $url = '';


	/**
	 * RestPublishTrigger constructor.
	 */
	public function __construct() {

		$this->url = get_option( 'dcoupled_publish_trigger_url', '' );
	}


	/**
	 * Register WP actions
	 */
	public function register() {

		if ( empty( $this->url ) ) {
			return;
		}

		add_action( 'wp_ajax_dcoupled_generate_all', [ $this, 'generateAll' ] );
		add_action( 'save_post', [ $this, 'generateOnSave' ], 10, 2 );
	}


	/**
	 * Generate all posts
	 */
	public function generateAll() {

		try {
			$this->triggered( [
				'action' => 'flush'
			] );
			wp_send_json_success( 'Generating...' );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}


	/**
	 * Generate single post on-save
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function generateOnSave( $post_id, $post ) {

		$postTypes = get_post_types( [ 'show_in_rest' => true ] );

		if ( ! in_array( $post->post_type, $postTypes ) || $post->post_status !== 'publish' ) {
			return;
		}

		try {
			$this->triggered( [
				'action' => 'destroy',
				'params' => [
					'id'   => $post_id,
					'type' => 'permalink',
					'slug' => preg_replace( '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', get_permalink( $post_id ) ),
				]
			] );
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	/**
	 * Trigger webhook
	 *
	 * @param $args
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function triggered( $args ) {

		$args = ['cache' => http_build_query($args, '', '&')];

		$response = wp_remote_post( $this->url, [
			'headers' => $args,
		] );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			throw new Exception( $error_message );
		}

	}
}