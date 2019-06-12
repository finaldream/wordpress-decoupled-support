<?php
/**
 * Plugin Name:       Decoupled Support
 * Plugin URI:        https://www.finaldream.de
 * Description:       Add Decoupled client supports for WP REST API
 * Version:           1.11.1
 * Author:            Finaldream Productions
 * Author URI:        https://www.finaldream.de
 * License:           MIT
 */

if (!defined('ABSPATH')) {
    exit();
}

include_once 'lib/UrlUtils.php';

include_once 'includes/RestToken.php';
include_once 'includes/RestFields.php';
include_once 'includes/RestMenus.php';
include_once 'includes/RestPermalink.php';
include_once 'includes/RestPreview.php';
include_once 'includes/RestWPML.php';
include_once 'includes/RestAdmin.php';
include_once 'includes/RestList.php';
include_once 'includes/RestRewrite.php';
include_once 'includes/CacheInvalidation.php';

include_once 'includes/thirdparty/WpmlSupport.php';

function decoupled_admin_scripts( $hook ) {

	$allows = ['edit.php', 'post.php', 'settings_page_decoupled-support-settings'];

	if ( ! in_array( $hook, $allows )) {
		return;
	}

	wp_enqueue_script( 'decoupled_admin', plugins_url('assets/js/decoupled-admin.js', __FILE__) );
	wp_enqueue_style( 'decoupled_admin', plugins_url('assets/css/decoupled-admin.css', __FILE__) );
}

add_action( 'admin_enqueue_scripts', 'decoupled_admin_scripts' );

function decoupled_rest_api_init()
{

    (new RestPermalink())->registerRoutes();
	(new RestPreview())->registerRoutes();
    (new RestMenus())->registerRoutes();
    (new RestList())->registerRoutes();
    (new RestFields())->registerRestFields();
    (new RestWPML())->registerFilters();
    (new RestRewrite())->rewrite();

}

function decoupled_rest_authentication($result)
{

    (new RestToken())->protect($result);
}

function decoupled_admin_warning()
{
    if(in_array('administrator', wp_get_current_user()->roles)) //check the current user role 
    {
        $class = 'notice notice-warning is-dismissible';
        $message = 'Please check the messages in Decoupled Settings';
        printf( '<div class="%1$s"><p><strong>Decoupled:</strong> %2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }
}

add_action('rest_api_init', 'decoupled_rest_api_init');
add_filter('rest_authentication_errors', 'decoupled_rest_authentication');


add_action('init', function () {
    //Check Env Constants
    if ( !defined( 'DECOUPLED_TOKEN' ) || 
         !defined( 'DECOUPLED_CLIENT_URL' ) || 
         !defined( 'DECOUPLED_CACHE_INVALIDATION_URL' ) ||
         !defined( 'DECOUPLED_UPLOAD_URL' ) ) {
            // We might activate later on, saving dissmisses state
            //add_action( 'admin_notices', 'decoupled_admin_warning' );
    }

    (new RestAdmin())->addSettings();
	(new CacheInvalidation())->register();

    /* Initialize third-parties */
    if (WpmlSupport::isAvailable()) {
        new WpmlSupport();
    }
});
