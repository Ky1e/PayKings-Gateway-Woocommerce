=== PayKings Payment Gateway For WooCommerce (Pro) ===  
Contributors: Pledged Plugins & PayKings

Tags: woocommerce PayKings, PayKings, payment gateway, woocommerce, woocommerce payment gateway  
Plugin URI: https://pledgedplugins.com/products/paykings-payment-gateway-woocommerce/  
Requires at least: 4.0  
Tested up to: 5.2  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

This Payment Gateway For WooCommerce extends the functionality of WooCommerce to accept payments from credit/debit cards using the PayKings payment gateway. Since customers will be entering credit cards directly on your store you should sure that your checkout pages are protected by SSL.

== Description ==

`PayKings Payment Gateway for WooCommerce` allows you to accept credit cards directly on your WooCommerce store by utilizing the PayKings payment gateway.

= Features =

1. Accept Credit Cards directly on your website by using the PayKings gateway.
2. No redirecting your customer back and forth.
3. Very easy to install and configure. Ready in Minutes!
4. Safe and secure method to process credit cards using the PayKings payment gateway.
5. Internally processes credit cards, safer, quicker, and more secure!

If you need any assistance with this or any of our other plugins, please visit our support portal:
https://pledgedplugins.com/support

== Installation ==

Easy steps to install the plugin:

1. Upload `paykings-payment-woocommerce` folder/directory to the `/wp-content/plugins/` directory
2. Activate the plugin (WordPress -> Plugins).
3. Go to the WooCommerce settings page (WordPress -> WooCommerce -> Settings) and select the Payments tab.
4. Under the Payments tab, you will find all the available payment methods. Find the 'PayKings' link in the list and click it.
5. On this page you will find all of the configuration options for this payment gateway.
6. Enable the method by using the checkbox.
7. Enter the PayKings account details (Username, Password)

That's it! You are ready to accept credit cards with your [PayKings payment gateway](https://www.paykings.com/) now connected to WooCommerce.

`Is SSL Required to use this plugin?`  
A valid SSL certificate is required to ensure your customer credit card details are safe and make your site PCI DSS compliant. This plugin does not store the customer credit card numbers or sensitive information on your website.  

`Does the plugin support direct updates from the WP dashboard?`  
Yes. You can navigate to WordPress -> Tools -> WooCommerce PayKings License page and activate the license key you received with your order. Once that is done you will be able to directly update the plugin to the latest version from the WordPress dashboard itself.  

== Changelog ==

1.1.2  
Updated "WC tested up to" header to 3.7  

1.1.1  
Updated "WC tested up to" header to 3.6  
Removed $_POST fields from being sent in gateway requests  
Replaced deprecated function "reduce_order_stock" with "wc_reduce_stock_levels"  

1.1.0  
Fixed PHP notices  
Changed logging method  
Updated post meta saving method  
Removed deprecated script code  
Integrated auto-update API  
Fixed log message and changed logging descriptions  
Fixed false negative on SSL warning notice in admin.  
Added GDPR privacy support  
Added "minimum required" and "tested upto" headers  
Added JCB, Maestro and Diners Club as options for allowed card types  
Made state field default to "NA" to support countries without a state  
Prevented the "state" parameter from being sent in "capture" or "void" transactions  

1.0.0  
Initial Release  
