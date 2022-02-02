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

add_action('init','handle_webhook_response');

function handle_webhook_response() {
	if (isset($_GET['mailchimp_generate_coupon_list']) && !empty($_GET['mailchimp_generate_coupon_list'])) {
		$event_type = $_POST['type'];
		if ($event_type === 'subscribe') {

		}
	}
}

function create_random_coupon($list_amount) {
	global $wpdb;
	$coupon_codes = $wpdb->get_col("SELECT post_name FROM $wpdb->posts WHERE post_type = 'shop_coupon'");
	$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	for ( $i = 0; $i < 1; $i++ ) {
		$res = "";
		for ($i = 0; $i < 10; $i++) {
		    $res .= $chars[mt_rand(0, strlen($chars)-1)];
		}
		if( in_array( $res, $coupon_codes ) ) {
			$i--;
		}
	}
	//echo $res;
	if ($res) {
		$coupon_code = $res; // Code
		$amount = $list_amount; // Amount
		$discount_type = 'percent'; // Type: fixed_cart, percent, fixed_product, percent_product

		$coupon = array(
		'post_title' => $coupon_code,
		'post_content' => '',
		'post_status' => 'publish',
		'post_author' => 1,
		'post_type' => 'shop_coupon');

		$new_coupon_id = wp_insert_post( $coupon );

		// Add meta
		update_post_meta( $new_coupon_id, 'discount_type', $discount_type );
		update_post_meta( $new_coupon_id, 'coupon_amount', $amount );
		update_post_meta( $new_coupon_id, 'individual_use', 'yes' );
		update_post_meta( $new_coupon_id, 'product_ids', '' );
		update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
		update_post_meta( $new_coupon_id, 'usage_limit', '1' );
		update_post_meta( $new_coupon_id, 'expiry_date', date("Y-m-d",strtotime('+30 days')) );
		update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
		update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
	}
}
