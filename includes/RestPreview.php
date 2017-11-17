<?php
/**
 * Rest Preview links
 *
 * @author Louis Thai <louis.thai@finaldream.de>
 * @since 17.11.2017
 */

class RestPreview extends RestPermalink {

	const API_NAMESPACE = 'wp/v2';

	/**
	 * Register menus route.
	 * @return void
	 */
	public function registerRoutes()
	{

		register_rest_route(static::API_NAMESPACE, '/preview', [
			[
				'methods' => WP_REST_Server::READABLE,
				'callback' => [$this, 'getPreview'],
				'args' => [
					'preview_id' => [
						'default' => false,
					],
					'token' => [
						'default' => false,
					]
				],
			]
		]);
	}

	public function getPreview($request) {

		$previewId    = $request['preview_id'];
		$previewToken = $request['token'];

		if (empty($previewId) || empty($previewToken)) {
			return new WP_Error('REST_INVALID', 'Please provide a valid preview params', ['status' => 400]);
		}

		$post = get_post($previewId);

		$validPreview = ($post && !empty($previewToken) && (base64_decode( $previewToken) === 'dcoupled-preview-token_'.$post->ID));

		if (!$post || !$validPreview) {
			return new WP_Error('REST_INVALID', 'Invalid preview request', ['status' => 400, 'ID' => $previewId]);
		}

		$preview = wp_get_post_autosave( $post->ID );

		if ( is_object( $preview ) ) {
			$post->ID = $preview->ID;
		}

		$serialized = $this->serialize($post, $request);

		return rest_ensure_response($serialized);

	}


}