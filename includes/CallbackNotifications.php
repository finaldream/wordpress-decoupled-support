<?php
/**
 * Callback Notifications
 */


class CallbackNotifications {

	const API_NAMESPACE = 'wp/v2';
	const DECOUPLED_NOTIFY_TRANSIENT_PREFIX = 'decoupled_notification_';

	/**
	 * Register Notifications callback route.
	 * @return void
	 */
	public function registerRoutes()
	{

		register_rest_route(static::API_NAMESPACE, '/decoupled-notify', [
			[
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => [$this, 'setNotification'],
				'args' => [
					'date' => [
						'default' => time(),
					],
					'tags' => [
						'default' => [],
					],
					'payload' => [
						'default' => false,
					]
				],
			]
		]);
    }
    

	public function setNotification($request) 
	{  
		$requestBody = $request->get_json_params();
		if (sizeof($requestBody) < 3) return new WP_Error( 'wrong payload', 'The payload received has incorrect number of params', array( 'status' => 400 ) );
		$logEvent = [
			"date" => $requestBody['date'],
			"tags" => $requestBody['tags'],
			"message" => $requestBody['payload'],
		];
		$transientName = static::DECOUPLED_NOTIFY_TRANSIENT_PREFIX.$this->getNotificationId();
		set_transient( $transientName, $logEvent, 24 * HOUR_IN_SECONDS );
		return rest_ensure_response('success'); 
	}
	
	public function getNotifications($tags)
	{
      global $wpdb;
	  $res = $wpdb->get_results("SELECT option_value FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_decoupled_notification_%'", OBJECT);
	  foreach ($res as $key => $value) {
		  $res[$key] = unserialize($value->option_value);
	  }
	  return $res;
	}

	private function getNotificationId()
	{
		$result = get_option('decoupled_notifications_next_id', '1');
		update_option('decoupled_notifications_next_id', $result+1, true);
		return $result;
	}
}