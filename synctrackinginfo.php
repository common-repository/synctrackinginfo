<?php

/**
 * Plugin Name: Synctrackinginfo
 * Description: Add tracking information such as tracking number, shipping carrier, and tracking link to your WooCommerce orders. Auto Sync shipping info to your Paypal account.
 * Version: 2.0.0
 * Author: Teqniatech
 * Author URI: https://teqniatech.com
 * Developer: Teqniatech
 * Developer URI: https://teqniatech.com
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 */

defined('ABSPATH') || die('Access Denied!');
define('TEQ_STF_SERVER', 'https://api.synctrackinginfo.com');

require 'functions.php';

/**
 * Register functions only if woocommerce is enabled
 **/


if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	// On plugin activation, teq_stf_activation() creates or gets store from API
	register_activation_hook(__FILE__, 'teq_stf_activation');

	// Add Go to Settings link in plugin page
	add_filter( 'plugin_action_links_synctrackinginfo/synctrackinginfo.php', 'teq_stf_settings_link' );

	// teq_stf_is_paypal_connected() checks if Paypal API is connected and shows a notice if not
	add_action('admin_notices', 'teq_stf_is_paypal_connected');

	// Hook when order status changes, it will sync tracking when at status selected by user in settings
	add_action('woocommerce_order_status_changed','teq_stf_order_status_hook', 10, 3);

	// teq_stf_register_otview() is the main widget view of the app
	add_action('add_meta_boxes', 'teq_stf_register_otview');

	// teq_stf_custom_column() adds a new column "Sync Status" to orders list
	add_filter('manage_edit-shop_order_columns', 'teq_stf_custom_column', 20);

	// oss_sti_custom_column_content() shows order sync status on column above
	add_action('manage_shop_order_posts_custom_column', 'teq_stf_custom_column_content', 20, 2);

	// teq_stf_add_settings() is the app settings page on: WooCommerce->Settings > Synctrackinginfo tab
	add_filter('woocommerce_get_settings_pages', 'teq_stf_add_settings');

	// teq_stf_save_settings() saves Sync Status in order's post meta
	add_action('save_post', 'teq_stf_save_settings');

}



