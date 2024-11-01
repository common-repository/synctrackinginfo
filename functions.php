<?php
defined('ABSPATH') || die('Access Denied!');


/**
 * Hooked at register_activation_hook
 * This function runs on plugin activation
 * It requests adding new store from api
 */

function teq_stf_activation() {

	// request store creation from api
	$url = TEQ_STF_SERVER . '/stores';

	$post_data = array(
		'name' => get_bloginfo('name'),
		'url' => $_SERVER['SERVER_NAME'],
		'platform' => 'WooCommerce'
	);

	teq_stf_curl_json_post($url, json_encode($post_data));
}

/**
 * Hooked at plugin_action_links
 * Add settings link to plugin's page
 */
function teq_stf_settings_link($links) {
	// Build and escape the URL.
	$url = esc_url(add_query_arg(array (
			'page' => 'wc-settings',
			'tab' => 'synctrackinginfo'
		),
		get_admin_url() . 'admin.php'
	));
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the begining of the array.
	array_unshift(
		$links,
		$settings_link
	);
	return $links;
}


/**
 * Hooked at admin_notices
 * This function gets from the api if Paypal API credentials are working
 * It shows a notice usind teq_stf_show_error_notice() function bellow
 * It checks also for license but this functionality is disabled from the api,
 * license management will be done by WooCommerce
 */
function teq_stf_is_paypal_connected() {
	global $teq_stf_version;

	if ($teq_stf_version) {
		$url = TEQ_STF_SERVER . '/stores/paypal/connect';
		$resp = teq_stf_curl_get($url);
		if (400 == $resp['code']) { // Paypal config not ok
			$json = json_decode($resp['response']);
			teq_stf_show_error_notice('<b>Synctrackinginfo:</b> ' . $json->message . '. Please go to <a href="' . admin_url('admin.php?page=wc-settings&tab=synctrackinginfo') . '">WooCommerce->Settings->Synctrackinginfo</a> to set up your Paypal credentials.');
		}
	}
}


/**
 * Hooked at add_meta_boxes
 * Register teq_stf_main_view_cb to show main view meta box
 */
function teq_stf_register_otview() {

	add_meta_box(
		'oss_otview', // Unique ID
		'Synctrackinginfo', // Box title
		'teq_stf_main_view_cb', // Content callback, must be of type callable
		'shop_order', // Post type
		'side', // context
		'high' // priority
	);

}

/**
 * Echoes html of main widget view
 */
function teq_stf_main_view_cb($post, $metabox) {
	global $teq_stf_version;

	include 'teq_stf_main_view.php';
}

/**
 * Hooked at manage_edit-shop_order_columns
 * Adds a column after order_status in WooCommerce order list
 * This column is named Sync Status and shows order Sync Status
 *
 * @return: Edited reordered columns
 */
function teq_stf_custom_column($columns) {
	global $teq_stf_version;

	if ($teq_stf_version) {

		$reordered_columns = array();

		// Inserting columns to a specific location
		foreach ($columns as $key => $column) {
			$reordered_columns[$key] = $column;

			if ('order_status' == $key) {
				// Inserting after "Status" column
				$reordered_columns['sync-status'] = 'Sync Status';
			}
		}

		return $reordered_columns;

	}else {
		return $columns;
	}

}

/**
 * Hooked at woocommerce_order_status_changed
 */
function teq_stf_order_status_hook($order_id, $old_status, $new_status) {
	$target_status = WC_Admin_Settings::get_option('teq_stf_sync_on_status', 'completed');

	// if new status equals to selected sync status, and that is was changed from a different old status, then sync
	if (strtolower($target_status) == strtolower($new_status)) {
		// sync here
		if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'synctrackinginfo') {
			include 'sync_modules/synctrackinginfo.php';

		}else if (WC_Admin_Settings::get_option('teq_stf_tracking_app', 'synctrackinginfo') == 'ast') {
			include 'sync_modules/ast.php';

		}
		
		if ($result['code'] == 201) {
			$order = json_decode($result['response']);
			$ret = update_post_meta($order_id, '_oss_stf_sync_status', $order->sync_status);
		}
		
	}
}	

/**
 * Hooked at action manage_shop_order_posts_custom_column
 * Shows sync status for orders on Woocommerce order list
 */
function teq_stf_custom_column_content($column, $post_id) {
	global $teq_stf_version;

	if ($teq_stf_version) {
		if ('sync-status' == $column) {
			$status = get_post_meta($post_id, '_oss_stf_sync_status', true);

			if ('Ignored' == $status) {
				echo '<mark class="order-status status-cancelled tooltip"><span>Ignored</span> <span class="bottom">No Paypal Transaction Found. This Order Wasn\'t paid via Paypal.</span></mark>';

			} else if ('Synced' == $status) {
				echo '<mark class="order-status status-completed"><span>Synced</span></mark>';

			} else if ('Processing' == $status) {
				echo '<mark class="order-status status-processing"><span>Processing</span></mark>';

			} else if ('Processed' == $status) {
				echo '<mark class="order-status status-completed tooltip"><span>Processed</span> <span class="bottom">Order Processed but Sync not submitted. Upgrade or check your settings.</span></mark>';

			} else if ('Error' == $status) {
				echo '<mark class="order-status status-failed tooltip"><span>Error</span> <span class="bottom">Sync Failed Due to an Error, Please try again.</span></mark>';

			} else {
				echo '<mark class="order-status status-cancelled tooltip"><span>Unprocessed</span> <span class="bottom">This Order is Not Yet Processed, Waiting For Status.</span></mark>';

			}

			include 'style.css';
		}
	}

}


/**
 * Hooked at woocommerce_get_settings_pages
 * Adds a new tab in WooCommerce Settings page
 * Tab is named Synctrackinginfo and contains app settings
 *
 * @return: Edited settings
 */
function teq_stf_add_settings($settings) {
	include_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php';

	$settings[] = include 'class.stf_settings_page.php';

	return $settings;
}


/**
 * Hooked at save_post
 * When saving a post, the sync status is saved as post meta
 * This is the value shown on WooCommerce orders list in Sync Status tab
 */
function teq_stf_save_settings($post_id) {
	wp_verify_nonce('nonce');

	if (array_key_exists('_oss_stf_sync_status', $_POST)) {

		update_post_meta(
			$post_id,
			'_oss_stf_sync_status',
			sanitize_text_field($_POST['_oss_stf_sync_status'])
		);

	}
}


/**
 * Shows critical app errors, called by functions above
 *
 * @param: String msg
 */
function teq_stf_show_error_notice($msg){
?>
	<div class="error notice" id="error_notice">
		<p><?php echo sprintf($msg); ?></p>
	</div>
<?php
}


/**
 * What version is this
 */
$teq_stf_version = teq_stf_get_version();


function teq_stf_get_version() {

	$url = TEQ_STF_SERVER . '/stores';
	$resp = teq_stf_curl_get($url);

	if (200 != $resp['code']) {
		return false;

	} else {
		$json = json_decode($resp['response']);
		return $json->is_premium;

	}

}


/**
 * Performs a curl get on $url
 * curl is now replaced by wp_remote_get
 * @param:  String url
 * @return: array of response and status code
 */
function teq_stf_curl_get($url) {
	$key = '';

	if (class_exists('WC_Admin_Settings')) {
		$key = WC_Admin_Settings::get_option('oss_stf_activation_key', '');
	}

	$response = wp_remote_get(
		$url,
		array(
			'headers' => array(
				'Serial-Key' => $key,
				'Store-Url' => $_SERVER['SERVER_NAME']
			)
		)
	);

	$http_code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);

	return array(
		'response' => $body,
		'code' => $http_code
	);

}


/**
 * Performs a curl post on $url
 * curl is now replaced by wp_remote_post
 * @param:  String url
 * @param:  Array post_data
 * @return: array of response and status code
 */
function teq_stf_curl_json_post($url, $post_data) {

	// Set HTTP Header for POST request
	$headers = array (
		'Content-Type' => 'application/json',
		'Content-Length' => strlen($post_data),
		'Serial-Key' => WC_Admin_Settings::get_option('oss_stf_activation_key', ''),
		'Store-Url' => $_SERVER['SERVER_NAME']
	);

	$args = array (
		'body' => $post_data,
		'timeout' => '5',
		'redirection' => '5',
		'httpversion' => '1.0',
		'blocking' => true,
		'headers' => $headers
	);

	$response = wp_remote_post($url, $args);
	$http_code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);

	return array(
		'response' => $body,
		'code' => $http_code
	);

}