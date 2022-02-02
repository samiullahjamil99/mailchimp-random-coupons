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

add_action('mailchimp_coupons_settings_updated','create_necessary_webhooks');
register_activation_hook( MRC_PLUGIN_FILE, 'create_necessary_webhooks' );

function create_necessary_webhooks() {
  $main_url = get_home_url() . "/?mailchimp_generate_coupon_list=";
  $lists = MRC_API()->get_lists();
  $mrc_options = get_option('mrc_options');
  foreach ($lists as $list) {
    $url = $main_url . $list->id;
    if ($mrc_options['list_'.$list->id]) {
      MRC_API()->create_subscribe_webhook($list->id,$url);
    } else {
      $webhooks = MRC_API()->get_list_webhooks($list->id);
      foreach($webhooks as $webhook) {
        if ($webhook->url === $url) {
          MRC_API()->delete_subscribe_webhook($list->id,$webhook->id);
        }
      }
    }
  }
}
