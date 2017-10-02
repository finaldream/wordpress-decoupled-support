<?php
/**
 * Collection of URL-related functions
 *
 * @author Oliver Erdmann, <o.erdmann@finaldream.de>
 * @since  02.10.2017
 */

class UrlUtils {

    public static function stripDomain($url) {

        return preg_replace('/^(http)?s?:?\/\/[^\/]*(\/?.*)$/i', '$2', $url);

    }

}


