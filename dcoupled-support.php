<?php
/**
 * Plugin Name:       Dcoupled Support
 * Plugin URI:        http://finaldream.de
 * Description:       Add Dcoupled client supports for WP REST API
 * Version:           0.0.1
 * Author:            Louis Thai
 * Author URI:        http://finaldream.de
 * License:           MIT
 */

if (!defined('ABSPATH')) {
	exit();
}

include_once 'includes/RestToken.php';
include_once 'includes/RestFields.php';
include_once 'includes/RestMenus.php';
include_once 'includes/RestSingle.php';
include_once 'includes/RestWPML.php';

function dcoupled_rest_api_init() {
	(new RestSingle())->registerSingleRoutes();
	(new RestMenus())->registerMenuRoutes();
	(new RestFields())->registerRestFields();
	(new RestWPML())->registerFilters();
}

function dcoupled_rest_authentication($result) {
	(new RestToken())->protect($result);
}

add_action('rest_api_init', 'dcoupled_rest_api_init');
add_filter('rest_authentication_errors', 'dcoupled_rest_authentication');
