<?php
/**
 * Plugin Name:       Dcoupled Support
 * Plugin URI:        https://www.finaldream.de
 * Description:       Add Dcoupled client supports for WP REST API
 * Version:           0.0.1
 * Author:            Finaldream Productions
 * Author URI:        https://www.finaldream.de
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
include_once 'includes/RestAdmin.php';
include_once 'includes/RestPublishTrigger.php';
include_once 'includes/RestList.php';

function dcoupled_rest_api_init()
{
    (new RestSingle())->registerSingleRoutes();
    (new RestMenus())->registerMenuRoutes();
    (new RestFields())->registerRestFields();
    (new RestWPML())->registerFilters();
    (new RestList())->registerListRoutes();
}

function dcoupled_rest_authentication($result)
{
    (new RestToken())->protect($result);
}

add_action('rest_api_init', 'dcoupled_rest_api_init');
add_filter('rest_authentication_errors', 'dcoupled_rest_authentication');

function dcoupled_settings()
{
    (new RestAdmin())->addSettings();
    (new RestPublishTrigger())->register();
}

add_action('init', 'dcoupled_settings');
