<?php
/**
 * Simple token protect
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 10.08.2017
 */

/**
 * Class RestToken
 */
class RestToken {

	protected $error = null;

	public function protect($result) {

		if (!empty($result)) {
			return $result;
		}

		$headers = getallheaders();

		if (!isset($headers['dcoupled-token'])) {
			$this->error = new WP_Error('rest_authentication_error','Access denied.');
		} elseif (!defined('WP_API_DCOUPLED_TOKEN') || $headers['dcoupled-token'] !== WP_API_DCOUPLED_TOKEN) {
			$this->error = new WP_Error('rest_authentication_error','Invalid token.');
		}

		if (is_wp_error($this->error)) {
			http_response_code(403);
			wp_send_json_error(['error' => $this->error->get_error_message()]);
		}

		return $result;
	}
}