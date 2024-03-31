<?php
/**
 * Plugin Name: WooCommerce Bulk Variations
 * Description: A WooCommerce utility to let customers add multiple product variations at once to cart.
 * Version: 1.0.0
 * Author: Lars van Rhijn
 * Author URI: https://larsvanrhijn.nl/
 * Text Domain: woocommerce-bulk-variations
 * Domain Path: /languages/
 *
 * @package woocommerce-bulk-variations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WBV_PLUGIN_FILE' ) ) {
	define( 'WBV_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'WBV_PLUGIN_URI' ) ) {
	define( 'WBV_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	include_once __DIR__ . '/includes/WbvCore.php';
	$GLOBALS['WBVCore'] = WbvCore::instance();
} else {
	/**
	 * Display a notice when WooCommerce is inactive.
	 *
	 * @return void
	 */
	function wbv_admin_notice_woocommerce_inactive() {
		if ( is_admin() && current_user_can( 'edit_plugins' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html( __( 'Woocommerce Bulk Variations requires WooCommerce to be active. Please activate WooCommerce to use WooCommerce Bulk Variations.' ) ) . '</p></div>';
		}
	}
	add_action( 'admin_notices', 'wbv_admin_notice_woocommerce_inactive' );
}
