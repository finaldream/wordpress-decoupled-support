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
		$requestBody = $request->get_body();
		$validate = $this->validateRequest($requestBody);
		if ($validate !== true) return $validate;
		$transientName = static::DECOUPLED_NOTIFY_TRANSIENT_PREFIX.number_format(microtime(true), 4, '', '');
		set_transient( $transientName, $requestBody, 24 * HOUR_IN_SECONDS );
		return rest_ensure_response('success');
	}

	/**
	 * Get Notifications from Decoupled Notify 
	 * @param array optional $tags Array of strings
	 * @param bool optional $orderAsc default order is DESC (newer notifications first)
	 * @param int optional $limit Number of results
	 *  
	 * @return array
	 */
	
	public function getNotifications(array $tags = [], bool $orderAsc = false, int $limit = null) : array
	{
      global $wpdb;
	  $res = [];
	  $queryString = "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_decoupled_notification_%'";
	  if (sizeof($tags) > 0) 
	  {
		  $tagsString = '["'.implode('","',$tags).'"]';
		  $queryString .= " AND JSON_CONTAINS(option_value, '".$tagsString."', '$.tags')";
	  } 
	  $queryString .= $orderAsc ? " ORDER BY option_name ASC" : " ORDER BY option_name DESC";
	  if ($limit) $queryString .= " LIMIT ".$limit;
	  $set = $wpdb->get_results($queryString, OBJECT);
	  foreach ($set as $key => $value) {
		  $res[$key] = json_decode($value->option_value);
	  }
	  return $res;
	}

	/**
	 * Print Notifications from Decoupled Notify 
	 * Each notification is printed in a separtate <p> HTML element
	 * @param array optional $tags Array of strings
	 * @param bool optional $orderAsc default order is DESC (newer notifications first)
	 * @param int optional $limit Number of results
	 *  
	 * @return string
	 */
	
	public function printNotifications(array $tags = [], bool $orderAsc = false, int $limit = null) : string
	{
	  $log = $this->getNotifications($tags, $orderAsc, $limit);
	  $res = '';
	  if($log && sizeof($log) > 0) {
		foreach ($log as $event) {     
			$datetime = $event->date;
			$timezone = 'Europe/Berlin';
			$date = new \DateTime( $datetime, new \DateTimeZone( 'UTC' ) );
			$date->setTimezone( new \DateTimeZone( $timezone ) );
			if (is_scalar($event->payload)) {
				$message = $event->payload;
			} else {
				$message = serialize($event->payload);
			}
			$res .= '<li><strong>'.$date->format('Y/m/d H:i:s').'</strong> - '.$message.'</li>';
		}
	  } else  {
		$res .= '<li>There are currently no notifications</li>';
	  }
	  return $res;
	}

	private function validateRequest(string $body)
	{
		$decoded = json_decode($body);
		if (!is_object($decoded)) return new WP_Error( 'wrong payload', 'The payload received is not a valid JSON', array( 'status' => 400 ) );
		if (count(get_object_vars($decoded)) != 3) return new WP_Error( 'wrong payload', 'The payload received has incorrect number of params', array( 'status' => 400 ) );
		if (!isset($decoded->date) || !isset($decoded->tags) || !isset($decoded->payload)) return new WP_Error( 'wrong payload', 'The payload is missing a required param', array( 'status' => 400 ) );
		return (json_last_error() == JSON_ERROR_NONE);
	}
}