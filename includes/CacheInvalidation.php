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

		$this->url = get_option( 'dcoupled_cache_invalidation_url', '' );
	}

	/**
	 * Register WP actions
	 */
	public function register() {

		if ( empty( $this->url ) ) {
			return;
		}

		add_action('post_submitbox_start', [$this, 'clearCacheButton']);

		add_action( 'wp_ajax_dcoupled_flush_cache', [ $this, 'ajaxFlushCache' ] );
		add_action( 'wp_ajax_dcoupled_invalidate_cache', [ $this, 'ajaxInvalidateCache' ] );
		add_action( 'save_post', [ $this, 'invalidateCache' ], 10, 2 );

		add_filter( 'post_row_actions', [ $this, 'rowActions' ], 200, 2 );
	}

	/**
	 * Flush all cache
	 */
	public function ajaxFlushCache() {
		try {
			$this->triggered( [
				'action' => 'flush'
			] );
			wp_send_json_success( 'All cache was cleared' );
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
			$this->triggered( [
				'action' => 'destroy',
				'params' => [
					'id'   => $post_id,
					'type' => 'permalink',
					'slug' => preg_replace( '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', get_permalink( $post_id ) ),
				]
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
                    class="dcoupled-clear-cache button"
                    data-action="dcoupled_invalidate_cache"
                    data-post-id="<?= $post->ID ?>"><?= __( 'Clear cache' ) ?>
            </button>
            <span class="spinner"></span>
        </div>
		<?php
	}

    public function rowActions( $actions, $post ) {

	    if ( !empty( $post ) && $post->post_status === 'publish') {
	        $actions['clear-cache'] = sprintf(
		        '<a class="dcoupled-clear-cache" href="#" data-action="dcoupled_invalidate_cache" data-post-id="%s">%s</a>',
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

		$args = [ 'cache' => http_build_query( $args, '', '&' ) ];

		$response = wp_remote_post( $this->url, [
			'headers' => $args,
		] );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			throw new Exception( $error_message );
		}

	}
}