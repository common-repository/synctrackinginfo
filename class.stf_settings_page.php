<?php
defined('ABSPATH') || die('Access Denied!');

if (class_exists('Stf_Settings_Page', false) ) {
	return new Stf_Settings_Page();
}

/**
 * WC_Admin_Settings_General.
 */
class Stf_Settings_Page extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'synctrackinginfo';
		$this->label = 'Synctrackinginfo';
		parent::__construct();
	}

	public function get_settings() {
		global $teq_stf_version;

		$settings = apply_filters(
			'oss_sti_settings',

			array(

				array(
					'title' => __('Tracking & Sync Settings', 'synctrackinginfo'),
					'type'  => 'title',
					'desc'  => 'You can either choose Synctrackinginfo as your main tracking app, or you can choose another app you already use for tracking.<br>Synctrackinginfo will integrate to selected app and Autosync your orders to Paypal.<br><b>NB:</b> If you choose an app other than Synctrackinginfo, the Synctrackinginfo\'s integrated view will be minimized to show only Paypal Syncing status.',
					'id'    => 'tracking_settings',
				),

				array(
					'title'		=> __('Tracking app', 'synctrackinginfo'),
					'desc'		=> __('Shipment tracking app you use for your orders.', 'synctrackinginfo'),
					'id'		=> 'teq_stf_tracking_app',
					'class'		=> 'wc-enhanced-select',
					'default'	=> 'synctrackinginfo',
					'type'		=>	'select',
					'options'	=> array(
						'synctrackinginfo'	=> __('Synctrackinginfo', 'synctrackinginfo'),
						'ast'				=> __('Advanced Shipment Tracking', 'synctrackinginfo')
					),
					'desc_tip' => true,
				),

				array(
					'title'		=> __('Sync on status (Pro Only)', 'synctrackinginfo'),
					'desc'		=> __('On which order status you want to auto submit your tracking info to Paypal?', 'synctrackinginfo'),
					'id'		=> 'teq_stf_sync_on_status',
					'class'		=> 'wc-enhanced-select',
					'default'	=> 'completed',
					'type'		=>	'select',
					'options'	=> array(
						'completed'		=> __('Completed', 'synctrackinginfo'),
						'processing'	=> __('Processing', 'synctrackinginfo')
					),
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'tracking_settings',
				),

				array(
					'title' => __('Paypal Settings (Pro Only)', 'synctrackinginfo'),
					'type'  => 'title',
					'desc'  => 'Enter your Paypal client ID and Secret bellow and click Save (these are NOT your Paypal username and password). <a href="https://youtu.be/DjKHuIB5-ro" target="_blank">Watch how</a>.',
					'id'    => 'paypal_settings',
				),

				array(
					'title'    => __('Paypal Client ID', 'synctrackinginfo'),
					'desc'     => __('Your Paypal API Client ID', 'synctrackinginfo'),
					'id'       => 'paypal_client_id',
					'default'  => '',
					'type'     => 'text',
					'desc_tip' => true,
				),

				array(
					'title'    => __('Paypal Secret', 'synctrackinginfo'),
					'desc'     => __('Your Paypal API Secret Key.', 'synctrackinginfo'),
					'id'       => 'paypal_secret',
					'default'  => '',
					'type'     => 'password',
					'desc_tip' => true,
				),

				array(
					'type' => 'sectionend',
					'id'   => 'paypal_settings',
				),

				array(
					'title' => __('ACTIVATION', 'synctrackinginfo'),
					'type'  => 'title',
					'desc'  => $teq_stf_version ? 'Premium version, you are now enjoying auto sync to your Paypal account.' : 'Enter your activation key to be able to automatically synchronize your tracking info to Paypal. <a href="https://synctrackinginfo.com/license.html" target="_blank">Get it here</a>. <br>(You may need to reload this page after activation)',
					'id'    => 'pro_settings',
				),

				array(
					'title'    => __('Activation Key', 'synctrackinginfo'),
					'desc'     => __('Your Activation Key', 'synctrackinginfo'),
					'id'       => 'oss_stf_activation_key',
					'default'  => '',
					'type'     => 'text',
					'desc_tip' => true
				),

				array(
					'type' => 'sectionend',
					'id'   => 'pro_settings',
				)

			)

		);

		return apply_filters('woocommerce_get_settings_' . $this->id, $settings);

	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();
		WC_Admin_Settings::output_fields($settings);
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();
		WC_Admin_Settings::save_fields($settings);

		// save settings to web service
		$url = TEQ_STF_SERVER . '/stores/settings';

		$paypal_client_id = WC_Admin_Settings::get_option('paypal_client_id', '');
		$paypal_secret = WC_Admin_Settings::get_option('paypal_secret', '');
		$activation_key = WC_Admin_Settings::get_option('oss_stf_activation_key', '');

		$post = array(

			'settings' => array(

				array(
					'type' => 'paypal_client_id',
					'value' => $paypal_client_id
				),

				array(
					'type' => 'paypal_secret',
					'value' => $paypal_secret
				),

				array(
					'type' => 'activation_key',
					'value' => $activation_key
				)

			)

		);

		$ret = teq_stf_curl_json_post($url, json_encode($post));

		if (200 != $ret['code']) {
			WC_Admin_Settings::add_error(print_r($ret, true));
		}

	}

}

return new Stf_Settings_Page();