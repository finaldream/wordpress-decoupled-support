<?php
/**
 * Cache Invalidation
 */


class CacheInvalidation {

	public $url = '';


	/**
	 * CacheInvalidation constructor.
	 */
	public function __construct() {

		$this->url = defined('DECOUPLED_CACHE_INVALIDATION_URL') ? DECOUPLED_CACHE_INVALIDATION_URL : null;
	}

	/**
	 * Register WP actions
	 */
	public function register() {

		if ( empty( $this->url ) ) {
			return;
		}

		add_action('post_submitbox_start', [$this, 'clearCacheButton']);

		add_action( 'wp_ajax_decoupled_flush_cache', [ $this, 'ajaxFlushCache' ] );
		add_action( 'wp_ajax_decoupled_invalidate_cache', [ $this, 'ajaxInvalidateCache' ] );
		add_action( 'save_post', [ $this, 'invalidateCache' ], 10, 2 );

		add_filter( 'post_row_actions', [ $this, 'rowActions' ], 200, 2 );

		// Cache invalidation shortcuts on admin toolbar
		add_action('admin_bar_menu', [$this, 'adminToolbarMenu'], 200);
		add_action('admin_init', [$this, 'triggerToolbarCacheInvalidation']);
		add_filter('removable_query_args', [$this, 'removeCacheInvalidationQuery']);

	}

	/**
	 * Flush all cache
	 */
	public function ajaxFlushCache() {
		update_option( 'decoupled_cache_clear_status', 'Cache clear is in progress, started on '.date("d/m/Y H:i:s"));
		try {
			$this->triggered( [
				'action' => 'flush'
			] );
			wp_send_json_success( 'Cache clearing initialized' );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * Invalidate single post cache via AJAX
	 */
	public function ajaxInvalidateCache() {

		$postId = isset( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : 0;
		$post   = get_post( $postId );

		if ( empty( $post ) ) {
			wp_send_json_error( 'Invalid post' );
		}

		$this->invalidateCache( $postId, $post, true );

	}

	/**
	 * Invalidate single post's cache
	 *
	 * @param $post_id
	 * @param $post
	 * @param bool $isAjax
	 */
	public function invalidateCache( $post_id, $post, $isAjax = false ) {

		$postTypes = get_post_types( [ 'show_in_rest' => true ] );

		if ( ! in_array( $post->post_type, $postTypes ) || $post->post_status !== 'publish' ) {

			if ( $isAjax ) {
				wp_send_json_error( 'Invalid post' );
			}

			return;
		}

		try {

		    $params = [
			    'id'   => $post_id,
			    'type' => 'permalink',
			    'slug' => preg_replace( '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', get_permalink( $post_id ) ),
		    ];

			$params = apply_filters('decoupled_cache_invalidation_params', $params, $post);

			$this->triggered( [
				'action' => 'destroy',
				'params' => $params,
			] );

			if ( $isAjax ) {
				wp_send_json_success( 'Cache cleared' );
			}

		} catch ( Exception $e ) {
			if ( $isAjax ) {
				wp_send_json_error( $e->getMessage() );
			} else {
				wp_die( $e->getMessage() );
			}
		}
	}

	/**
     * Post clear cache button
     *
	 * @param $post
	 */
	public function clearCacheButton( $post ) {

		if ( empty( $post ) || $post->post_status !== 'publish' ) {
			return;
		}

		?>
        <div class="clear-cache-action">
            <button
                    class="decoupled-clear-cache button"
                    data-action="decoupled_invalidate_cache"
                    data-post-id="<?= $post->ID ?>"><?= __( 'Clear cache' ) ?>
            </button>
            <span class="spinner"></span>
        </div>
		<?php
	}

    public function rowActions( $actions, $post ) {

	    if ( !empty( $post ) && $post->post_status === 'publish') {
	        $actions['clear-cache'] = sprintf(
		        '<a class="decoupled-clear-cache" href="#" data-action="decoupled_invalidate_cache" data-post-id="%s">%s</a>',
		        $post->ID,
		        __( 'Clear cache' )
	        );
        }

        return $actions;
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

		$headers = [
			'Content-Type' => 'application/json; charset=utf-8'
		];
		if (defined(DECOUPLED_BASIC_AUTH) && DECOUPLED_BASIC_AUTH != null ) {
			$headers['Authorization'] = 'Basic '. base64_encode(DECOUPLED_BASIC_AUTH);
		}
		$response = wp_remote_post( $this->url, [
		    'headers' => $headers,
			'body'    => json_encode([ 'cache' => $args ]),
		] );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			throw new Exception( $error_message );
		}

	}

	/**
	 * Add items into WP Admin toolbar
	 * @param $adminBar
	 */
	public function adminToolbarMenu($adminBar)
	{
		$screen = get_current_screen();

		$adminBar->add_node([
			'id' => 'decoupled-cache-invalidation',
			'title' => __('Clear cache'),
			'href' => '#',
			'meta' => [
				'class' => 'decoupled-cache-invalidation',
				'title' => __('Clear cache'),
			]
		]);

		if ($screen->base === 'post' && $id = get_the_ID()) {
			$adminBar->add_node([
				'id' => 'decoupled-cache-invalidation-current-page',
				'title' => __('Current Page'),
				'href' => add_query_arg('decoupled-cache-invalidation', $id),
				'parent' => 'decoupled-cache-invalidation',
				'meta' => [
					'class' => 'toolbar-decoupled-cache-invalidation-current-page',
					'title' => __('Clear current page cache'),
				]
			]);
		}

		$adminBar->add_node([
			'id' => 'decoupled-cache-invalidation-flush',
			'title' => __('All Caches'),
			'href' => add_query_arg('decoupled-cache-invalidation', 'flush'),
			'parent' => 'decoupled-cache-invalidation',
			'meta' => [
				'class' => 'toolbar-decoupled-cache-invalidation-flush',
				'title' => __('Flush all caches')
			]
		]);
	}

	/**
	 * Trigger toolbar actions
	 */
	public function triggerToolbarCacheInvalidation()
	{
		$invalidationAction = $_GET['decoupled-cache-invalidation'] ?? false;

		if ($invalidationAction) {

			try {
				switch ($invalidationAction) {
					case 'flush':
						$this->triggered([
							'action' => 'flush'
						]);
						break;
					default:
						$post = get_post($invalidationAction);
						$this->invalidateCache($invalidationAction, $post);
						break;
				}

				add_action('admin_notices', function () {
					?>
                    <div class="notice updated is-dismissible">
                        <p><?= __('Decoupled cache invalidation triggered!'); ?></p>
                    </div>
					<?php
				});
			} catch (Exception $e) {
				wp_die($e->getMessage());
			}
		}
	}

	/**
	 * Remove toolbar query args after redirect
	 * @param array $args
	 * @return array
	 */
	public function removeCacheInvalidationQuery($args)
	{
		$args[] = 'decoupled-cache-invalidation';

		return $args;
	}
}
