<?php
/**
 * Collection of URL-related functions
 *
 * @author Oliver Erdmann, <o.erdmann@finaldream.de>
 * @since  02.10.2017
 */

namespace DecoupledSupport;


class UrlUtils {

	/**
	 * @var string
	 */
	private $domainPattern;

	/**
	 * UrlUtils constructor.
	 */
	private function __construct() {
		$siteUrl           = get_option( 'siteurl' );
		$this->domainPattern = $this->generateRegex( $siteUrl );
	}

	/**
	 * Call this method to get singleton
	 *
	 * @return UrlUtils
	 */
	public static function getInstance() {
		static $inst = null;

		if ( $inst === null ) {
			$inst = new self();
		}

		return $inst;
	}

	/**
	 * Strip all domain
	 * @param $url
	 *
	 * @return mixed
	 */
	public static function stripAllDomain( $url ) {

		return preg_replace( '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', $url );

	}

	/**
	 * Generate domain pattern
	 * @param $url
	 *
	 * @return string
	 */
	private function generateRegex( $url ) {
		$info = parse_url( $url );
		$host = $info['host'];

		$parts = array_slice(explode( ".", $host ), -2, 2, true );
		$domainName = array_shift($parts);
		$domainExt = array_shift($parts);

		return '/(http)?s?:?\/\/([a-zA-Z\d-]+\.){0,}'. $domainName .'\.'. $domainExt .'/i';
	}

	/**
	 * Replace domain with Decoupled client domain
	 *
	 * @param $string
	 * @param bool $newDomain
	 *
	 * @return mixed
	 */
	public function replaceDomain( $string, $newDomain = false ) {

		$envUrl = defined('DECOUPLED_CLIENT_URL') ? DECOUPLED_CLIENT_URL : null;
		$newDomain = ($newDomain) ? $newDomain : $envUrl;

		return preg_replace( $this->domainPattern, untrailingslashit($newDomain), $string );
	}

	/***
	 * Retrieves post permalink even if post is draft or pending.
	 * Returns core function otherwise.
	 *
	 * @param int $id
	 * @return string
	 */
	public static function getPostPermalink ($id = 0) {

		$draft_or_pending = get_post_status($id) && in_array(get_post_status($id), ['draft', 'pending', 'auto-draft', 'future']);
		$util = static::getInstance();

		if (!$draft_or_pending) {
			return $util->replaceDomain(get_permalink($id));
		}

		require_once ABSPATH . '/wp-admin/includes/post.php';
		list($permalink, $postName) = get_sample_permalink($id);

		return $util->replaceDomain(str_replace('%postname%', $postName, $permalink));

	}

}


