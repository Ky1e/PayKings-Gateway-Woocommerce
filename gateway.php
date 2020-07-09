<?php
/*
Plugin Name: WooCommerce PayKings Gateway (Pro)
Plugin URI: https://pledgedplugins.com/products/paykings-payment-gateway-woocommerce/
Description: A payment gateway for PayKings. A PayKings account and a server with cURL, SSL support, and a valid SSL certificate is required (for security reasons) for this gateway to function. Requires WC 3.0.0+
Version: 1.1.2
Author: Pledged Plugins
Author URI: https://pledgedplugins.com
Text Domain: wc-paykings
Domain Path: /languages
WC requires at least: 3.0.0
WC tested up to: 3.7

	Copyright: © Pledged Plugins.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main PayKings class which sets the gateway up for us
 */
class WC_PayKings {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'WC_PAYKINGS_VERSION', '1.1.2' );
		define( 'WC_PAYKINGS_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
		define( 'WC_PAYKINGS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_PAYKINGS_MAIN_FILE', __FILE__ );

		// required files
		require_once( 'includes/class-wc-gateway-paykings-logger.php' );
		require_once( 'updates/updates.php' );

		// Actions
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed', array( $this, 'capture_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_cancelled', array( $this, 'cancel_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'cancel_payment' ) );
	}

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paykings' ) . '">' . __( 'Settings', 'wc-paykings' ) . '</a>',
			'<a href="https://pledgedplugins.com/support/" target="_blank">' . __( 'Support', 'wc-paykings' ) . '</a>',
			'<a href="https://pledgedplugins.com/products/paykings-payment-gateway-woocommerce/?upgrade=1" target="_blank">' . __( 'Upgrade to Enterprise', 'wc-paykings' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Init localisations and files
	 */
	public function init() {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		// Includes
		if ( is_admin() ) {
			require_once( 'includes/class-wc-paykings-privacy.php' );
		}

		include_once( 'includes/class-wc-gateway-paykings.php' );

		$this->load_plugin_textdomain();
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if
	 * the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wc-paykings/wc-paykings-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wc-paykings-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc-paykings' );
		$dir    = trailingslashit( WP_LANG_DIR );

		load_textdomain( 'wc-paykings', $dir . 'wc-paykings/wc-paykings-' . $locale . '.mo' );
		load_plugin_textdomain( 'wc-paykings', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {
		$methods[] = 'WC_Gateway_PayKings';
		return $methods;
	}

	/**
	 * Capture payment when the order is changed from on-hold to complete or processing
	 *
	 * @param  int $order_id
	 */
	public function capture_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_payment_method() == 'paykings' ) {
			$charge   = $order->get_meta( '_paykings_charge_id' );
			$captured = $order->get_meta( '_paykings_charge_captured' );

			if ( $charge && $captured == 'no' ) {
				$gateway = new WC_Gateway_PayKings();
				$args = array(
					'amount'		=> $order->get_total(),
					'transactionid'	=> $order->get_transaction_id(),
					'type' 			=> 'capture',
					'email' 		=> $order->get_billing_email(),
					'currency'		=> $gateway->get_payment_currency( $order_id ),
				);
				$response = $gateway->paykings_request( $args );

				if ( $response->error || $response->declined ) {
					$order->add_order_note( __( 'Unable to capture charge!', 'wc-paykings' ) . ' ' . $response->error_message );
				} else {
					$complete_message = sprintf( __( 'PayKings charge complete (Charge ID: %s)', 'wc-paykings' ), $response->transactionid );
					$order->add_order_note( $complete_message );

					$order->update_meta_data( '_paykings_charge_captured', 'yes' );
					$order->update_meta_data( 'PayKings Payment ID', $response->transactionid );

					$order->set_transaction_id( $response->transactionid );
					$order->save();
				}
			}
		}
	}

	/**
	 * Cancel pre-auth on refund/cancellation
	 *
	 * @param  int $order_id
	 */
	public function cancel_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_payment_method() == 'paykings' ) {
			$charge   = $order->get_meta( '_paykings_charge_id' );

			if ( $charge ) {
				$gateway = new WC_Gateway_PayKings();
				$args = array(
					'amount'			=> $order->get_total(),
					'transactionid'		=> $order->get_transaction_id(),
					'type' 				=> 'cancel',
					'email' 			=> $order->get_billing_email(),
					'currency'			=> $gateway->get_payment_currency( $order_id ),
				);
				$response = $gateway->paykings_request( $args );

				if ( $response->error || $response->declined ) {
					$order->add_order_note( __( 'Unable to refund charge!', 'wc-paykings' ) . ' ' . $response->error_message );
				} else {
					$cancel_message = sprintf( __( 'PayKings charge refunded (Charge ID: %s)', 'wc-paykings' ), $response->transactionid );
					$order->add_order_note( $cancel_message );

					$order->delete_meta_data( '_paykings_charge_captured' );
					$order->delete_meta_data( '_paykings_charge_id' );
					$order->save();
				}
			}
		}
	}

}
new WC_PayKings();