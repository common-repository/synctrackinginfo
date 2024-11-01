=== Synctrackinginfo for WooCommerce ===
Contributors:  teqniatech
Tags: paypal, shipment tracking, shipping, tracking, woocommerce
Requires at least: 4.4
Tested up to: 5.6
Requires PHP: 7.0
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WC requires at least: 3.0.0
WC tested up to: 4.0.1
 
Add tracking information such as tracking number, shipping carrier, and tracking link to your WooCommerce orders. Auto Sync shipping info to your Paypal account.
 
== Description ==

This plugin will make it possible for you to add tracking information (order tracking number, shipping carrier name, tracking url) to your WooCommerce orders.

We provide a pre-defined list of known carriers, but you can also define your own carrier name.

== Key Features ==

* Add tracking number to orders
* Add carrier name (shipping company name) to orders
* Add tracking URL to orders
* Well integrated to your orders' view and easy to use

== PREMIUM VERSION ==

If you wish to automatically synchronize your tracking information between your store and your Paypal Account, you can use this addon functionality.

This will automatically and instantly upload your added tracking information to your Paypal account so you no longer need to do it manually for each order you receive.

Having this functionnality is key to earn more of PayPal's trust ensuring your business being safe and secure.

== 3rd Party Service ==

This plugin is partly relying on our API at: https://api.synctrackinginfo.com

All your tracking data is saved locally in your site, but a copy of this data is sent to the API.

For free users, we only keep the store name and URL for statistical reasons.

For users who activated PAYPAL AUTO SYNC, we also keep orders' tracking information to be able to send them to Paypal and ensure they are synchronized.

Please read about our privacy policy here: https://teqniatech.com/en/privacy-policy

== Translations ==

Synctrackinginfo is available in the following languages:

* English
 
== Installation ==
 
1. Upload the folder `woo-synctrackinginfo` to the `/wp-content/plugins/` folder
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to your order's page and add your tracking information

== Screenshots ==

1. Synctrackinginfo's embedded view.
 
== Changelog ==

= 2.0 =
* Integration with AST (Advanced Shipment Tracking)
* Auto detect order's status change and trigger Paypal synchronization
* User can choose on which order status to trigger synchronization
 
= 1.0 =
* Initial version.
