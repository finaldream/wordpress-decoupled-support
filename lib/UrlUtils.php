<?php
/**
 * Collection of URL-related functions
 *
 * @author Oliver Erdmann, <o.erdmann@finaldream.de>
 * @since  02.10.2017
 */

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

	public static function stripAllDomain( $url ) {

		return preg_replace( '/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', $url );

	}

	private function generateRegex( $url ) {
		$info = parse_url( $url );
		$host = $info['host'];

		$parts = array_slice(explode( ".", $host ), -2, 2, true );
		$domainName = array_shift($parts);
		$domainExt = array_shift($parts);

		return '/^(http)?s?:?\/\/([a-zA-Z\d-]+\.){0,}'. $domainName .'\.'. $domainExt .'/i';
	}

	public function replaceDomain( $url, $newDomain = false ) {

		$newDomain = ($newDomain) ? $newDomain: get_option('dcoupled_client_domain', '');

		return preg_replace( $this->domainPattern, $newDomain, $url );
	}

}


