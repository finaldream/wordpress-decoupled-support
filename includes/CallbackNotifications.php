<?php
/**
 * Callback Notifications
 */


class CallbackNotifications {

    const API_NAMESPACE = 'wp/v2';

    protected $notificationsLog;

    /**
	 * CallbackNotifications constructor.
	 */
	public function __construct() {
        if ( false === ( $this->notificationsLog = get_transient( 'decoupled_notifications_log' ) ) ) {
            $this->notificationsLog = [
                "date" => (new DateTime())->format(DateTime::ATOM),
                "tags" => [],
                "message" => 'Notifications Log Initiated'
            ];
            set_transient( 'decoupled_notifications_log', $this->notificationsLog, 12 * HOUR_IN_SECONDS );
        }
	}
    


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
		$message = $requestBody['payload'];
		$tags = $requestBody['tags'];
        $date = $requestBody['date'];
		return rest_ensure_response('success');
	}
}