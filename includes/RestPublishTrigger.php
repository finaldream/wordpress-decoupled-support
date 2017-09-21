<?php
/**
 * Publish Web-Hook
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 30.08.2017
 */

class RestPublishTrigger {

	public $url = '';

	/**
	 * RestPublishTrigger constructor.
	 */
	public function __construct() {
		$this->url = get_option('dcoupled_publish_trigger_url', '');
	}

	/**
	 * Register WP actions
	 */
	public function register() {
		if (empty($this->url))
			return;

		add_action( 'wp_ajax_dcoupled_generate_all', [$this, 'generateAll'] );
		add_action( 'save_post', [$this, 'generateOnSave'], 10, 2 );
	}

	/**
	 * Generate all posts
	 */
	public function generateAll() {
		try {
			$this->triggered([
				'all' => true
			]);
			wp_send_json_success('Generating...');
		} catch (Exception $e) {
			wp_send_json_error($e->getMessage());
		}
	}

	/**
	 * Generate single post on-save
	 *
	 * @param $post_id
	 * @param $post
	 */
	public function generateOnSave($post_id, $post) {

		$postTypes = get_post_types([ 'show_in_rest' => true ]);

		if (!in_array($post->post_type, $postTypes) || $post->post_status !== 'publish')
			return;

		try {
			$this->triggered([
				'id' => $post_id,
				'slug' => preg_replace ('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', get_permalink($post_id)),
			]);
		} catch (Exception $e) {
			wp_die($e->getMessage());
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
	public function triggered($args) {
		$fields = '';

		foreach($args as $key => $value) {
			$fields .= sprintf('%s=%s&', $key , $value);
		}

		rtrim($fields, '&');

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_URL, $this->url);
		curl_setopt($ch,CURLOPT_POST, count($args));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);

		$result = curl_exec($ch);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($httpCode === 404) {
			throw new Exception("URL not found $this->url");
		}

		curl_close($ch);

		return $result;
	}
}