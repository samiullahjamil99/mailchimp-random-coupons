<?php
/*
Plugin Name: Mailchimp Random Coupons
Author: Samiullah Jamil
Version: 1.0.0
Author URI: https://www.upwork.com/freelancers/~017eb8a3e9972bc5bd
*/

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MRC_PLUGIN_FILE' ) ) {
	define( 'MRC_PLUGIN_FILE', __FILE__ );
}

require_once "vendor/autoload.php";
require_once "class-mailchimp-api.php";
require_once "admin-page.php";

function MRC_API() {
  return Mailchimp_API::instance();
}

$GLOBALS['mailchimp_api'] = MRC_API();

register_activation_hook( MRC_PLUGIN_FILE, 'mrc_activate' );

function mrc_activate() {

}
