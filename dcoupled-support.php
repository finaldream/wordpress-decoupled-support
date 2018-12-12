<?php
/**
 * Plugin Name:       Dcoupled Support
 * Plugin URI:        https://www.finaldream.de
 * Description:       Add Dcoupled client supports for WP REST API
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

function dcoupled_admin_scripts( $hook ) {

	$allows = ['edit.php', 'post.php', 'settings_page_dcoupled-support-settings'];

	if ( ! in_array( $hook, $allows )) {
		return;
	}

	wp_enqueue_script( 'dcoupled_admin', plugins_url('assets/js/dcoupled-admin.js', __FILE__) );
	wp_enqueue_style( 'dcoupled_admin', plugins_url('assets/css/dcoupled-admin.css', __FILE__) );
}

add_action( 'admin_enqueue_scripts', 'dcoupled_admin_scripts' );

function dcoupled_rest_api_init()
{

    (new RestPermalink())->registerRoutes();
	(new RestPreview())->registerRoutes();
    (new RestMenus())->registerRoutes();
    (new RestList())->registerRoutes();
    (new RestFields())->registerRestFields();
    (new RestWPML())->registerFilters();
    (new RestRewrite())->rewrite();
}

function dcoupled_rest_authentication($result)
{

    (new RestToken())->protect($result);
}

add_action('rest_api_init', 'dcoupled_rest_api_init');
add_filter('rest_authentication_errors', 'dcoupled_rest_authentication');


add_action('init', function () {

    (new RestAdmin())->addSettings();
	(new CacheInvalidation())->register();

    /* Initialize third-parties */
    if (WpmlSupport::isAvailable()) {
        new WpmlSupport();
    }
});
