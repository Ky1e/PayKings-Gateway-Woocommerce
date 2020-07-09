<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_PayKings class.
 *
 * @extends WC_Payment_Gateway
 */

class WC_Gateway_PayKings extends WC_Payment_Gateway_CC {

	// Supported currencies
	private $currencies = array(
		'AED', 'AMD', 'ANG', 'ARS', 'AUD', 'AWG', 'AZN', 'BBD', 'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BWP', 'BYR', 'BZD', 'CAD', 'CHF', 'CLP', 'CNY', 'COP',
		'CRC', 'CVE', 'CYP', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHC', 'GIP', 'GMD', 'GNF', 'GTQ', 'GWP', 'GYD',
		'HKD', 'HNL', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LTL', 'LVL', 'MAD',
		'MDL', 'MGF', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR', 'MVR', 'MWK', 'MYR', 'MZN', 'MXN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR',
		'PLN', 'PYG', 'QAR', 'RON', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SKK', 'SLL', 'SOS', 'STD', 'SVC', 'SZL', 'THB', 'TOP', 'TRY', 'TTD', 'TWD',
		'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF', 'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMK', 'ZWD',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                    = 'paykings';
		$this->method_title          = __( 'PayKings', 'wc-paykings' );
		$this->method_description    = __( 'PayKings works by adding credit card fields on the checkout and then sending the details to the gateway for processing the transactions.', 'wc-paykings' ) . '<h3>' . __( 'Upgrade to Enterprise', 'wc-paykings' ) . '</h3>' . sprintf( __( 'Enterprise version is a full blown plugin that provides full support for processing subscriptions, pre-orders and refunds directly from your website. The credit card information is saved in your gateway merchant account and is reused to charge future orders, recurring payments or pre-orders at a later time. <a href="%s" target="_blank">Click here</a> to upgrade to Enterprise version or to know more about it.', 'wc-paykings' ), 'https://pledgedplugins.com/products/paykings-payment-gateway-woocommerce/?upgrade=1' );
		$this->has_fields            = true;
		$this->supports              = array( 'products' );
		$this->live_url 			 = 'https://paykings.transactiongateway.com/api/transact.php';
		$this->test_url 			 = '';
		$this->label_login_id 		 = __( 'Gateway Username', 'wc-paykings' );
		$this->label_transaction_key = __( 'Gateway Password', 'wc-paykings' );

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values.
		$this->title       		  = $this->get_option( 'title' );
		$this->description 		  = $this->get_option( 'description' );
		$this->enabled     		  = $this->get_option( 'enabled' );
		$this->testmode    		  = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->capture     		  = $this->get_option( 'capture', 'yes' ) === 'yes' ? true : false;
		$this->login_id	   		  = $this->get_option( 'login_id' );
		$this->transaction_key	  = $this->get_option( 'transaction_key' );
		$this->logging     		  = $this->get_option( 'logging' ) === 'yes' ? true : false;
		$this->debugging   		  = $this->get_option( 'debugging' ) === 'yes' ? true : false;
		$this->allowed_card_types = $this->get_option( 'allowed_card_types' );

		if ( $this->testmode ) {
			$this->description .= ' ' . sprintf( __( '<br /><br /><strong>TEST MODE ENABLED</strong><br /> In test mode, you can use the card number 4111111111111111 with any CVC and a valid expiration date or check the documentation "<a href="%s">PayKings Direct Post API</a>" for more card numbers.', 'wc-paykings' ), 'https://wiki.paykings.com/testing-direct-post-api/' );
			$this->description  = trim( $this->description );
		}

		// Hooks
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

	}

	/**
	 * get_icon function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_icon() {
		$icon = '';
		if( in_array( 'visa', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa.png' ) . '" alt="Visa" />';
		}
		if( in_array( 'mastercard', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard.png' ) . '" alt="Mastercard" />';
		}
		if( in_array( 'amex', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex.png' ) . '" alt="Amex" />';
		}
		if( in_array( 'discover', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover.png' ) . '" alt="Discover" />';
		}
		if( in_array( 'diners-club', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/diners.png' ) . '" alt="Diners Club" />';
		}
		if( in_array( 'jcb', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb.png' ) . '" alt="JCB" />';
		}
		if( in_array( 'maestro', $this->allowed_card_types ) ) {
			$icon .= '<img src="' . WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/maestro.png' ) . '" alt="Maestro" />';
		}
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Check if SSL is enabled and notify the user
	 */
	public function admin_notices() {
		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields
		if ( ! $this->login_id ) {
			echo '<div class="error"><p>' . sprintf( __( 'Gateway error: Please enter your Username <a href="%s">here</a>', 'wc-paykings' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paykings' ) ) . '</p></div>';
			return;

		} elseif ( ! $this->transaction_key ) {
			echo '<div class="error"><p>' . sprintf( __( 'Gateway error: Please enter your Password <a href="%s">here</a>', 'wc-paykings' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paykings' ) ) . '</p></div>';
			return;
		}

		// Simple check for duplicate keys
		if ( $this->login_id == $this->transaction_key ) {
			echo '<div class="error"><p>' . sprintf( __( 'Gateway error: Your Username and Password match. Please check and re-enter.', 'wc-paykings' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paykings' ) ) . '</p></div>';
			return;
		}

		// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
		if ( ! wc_checkout_is_https() ) {
			echo '<div class="notice notice-warning"><p>' . sprintf( __( 'PayKings is enabled, but a SSL certificate is not detected. Your checkout may not be secure! Please ensure your server has a valid <a href="%1$s" target="_blank">SSL certificate</a>.', 'wc-paykings' ), 'https://en.wikipedia.org/wiki/Transport_Layer_Security' ) . '</p></div>';
 		}

		if ( ! $this->currency_is_accepted() ) {
			echo '<div class="error"><p>' . sprintf( __( 'PayKings supports these currencies: %s', 'wc-paykings' ), implode( ', ', $this->currencies ) ) . '</p></div>';
			return;
		}
	}

	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {
		if ( $this->enabled == "yes" ) {
			if ( is_add_payment_method_page() ) {
				return false;
			}
			// Required fields check
			if ( ! $this->login_id || ! $this->transaction_key ) {
				return false;
			}
			if ( ! $this->currency_is_accepted() ) {
				return false;
			}
			return true;
		}
		return parent::is_available();
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters( 'wc_paykings_settings', array(
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'wc-paykings' ),
				'label'       => __( 'Enable PayKings', 'wc-paykings' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			),
			'title' => array(
				'title'       => __( 'Title', 'wc-paykings' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-paykings' ),
				'default'     => __( 'Credit card', 'wc-paykings' )
			),
			'description' => array(
				'title'       => __( 'Description', 'wc-paykings' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'wc-paykings' ),
				'default'     => sprintf( __( 'Pay with your credit card via %s.', 'wc-paykings' ), $this->method_title )
			),
			'testmode' => array(
				'title'       => __( 'Test mode', 'wc-paykings' ),
				'label'       => __( 'Enable Test Mode', 'wc-paykings' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in test mode. This will display test information on the checkout page and enable processing in non HTTPS mode.', 'wc-paykings' ),
				'default'     => 'yes'
			),
			'login_id' => array(
				'title'       => $this->label_login_id,
				'type'        => 'text',
				'description' => sprintf( __( 'Get your %s from your %s account.', 'wc-paykings' ), $this->label_login_id, $this->method_title ),
				'default'     => ''
			),
			'transaction_key' => array(
				'title'       => $this->label_transaction_key,
				'type'        => 'password',
				'description' => sprintf( __( 'Get your %s from your %s account.', 'wc-paykings' ), $this->label_transaction_key, $this->method_title ),
				'default'     => ''
			),
			'capture' => array(
				'title'       => __( 'Capture', 'wc-paykings' ),
				'label'       => __( 'Capture charge immediately', 'wc-paykings' ),
				'type'        => 'checkbox',
				'description' => __( 'Whether or not to immediately capture the charge. When unchecked, the charge issues an authorization and will need to be captured later.', 'wc-paykings' ),
				'default'     => 'yes'
			),
			'logging' => array(
				'title'       => __( 'Logging', 'wc-paykings' ),
				'label'       => __( 'Log debug messages', 'wc-paykings' ),
				'type'        => 'checkbox',
				'description' => sprintf( __( 'Save debug messages to the WooCommerce System Status log file <code>%s</code>.', 'wc-paykings' ), WC_Log_Handler_File::get_log_file_path( 'woocommerce-gateway-paykings' ) ),
				'default'     => 'no'
			),
			'debugging' => array(
				'title'       => __( 'Gateway Debug', 'wc-paykings' ),
				'label'       => __( 'Log gateway requests and response to the WooCommerce System Status log.', 'wc-paykings' ),
				'type'        => 'checkbox',
				'description' => __( '<strong>CAUTION! Enabling this option will write gateway requests including card numbers and CVV to the logs.</strong> Do not turn this on unless you have a problem processing credit cards. You must only ever enable it temporarily for troubleshooting or to send requested information to the plugin author. It must be disabled straight away after the issues are resolved and the plugin logs should be deleted.', 'wc-paykings' ) . sprintf( __( ' <a href="%s">Click here</a> to check and delete the full log file.', 'wc-paykings' ), admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . WC_Log_Handler_File::get_log_file_name( 'woocommerce-gateway-paykings' ) ) ),
				'default'     => 'no'
			),
			'allowed_card_types' => array(
				'title'       => __( 'Allowed Card types', 'wc-paykings' ),
				'class'       => 'wc-enhanced-select',
				'type'        => 'multiselect',
				'description' => __( 'Select the card types you want to allow payments from.', 'wc-paykings' ),
				'default'     => array( 'visa','mastercard','discover','amex' ),
				'options'	  => array(
					'visa'			=> __( 'Visa', 'wc-paykings' ),
					'mastercard'	=> __( 'MasterCard', 'wc-paykings' ),
					'discover'		=> __( 'Discover', 'wc-paykings' ),
					'amex'			=> __( 'American Express', 'wc-paykings' ),
					'diners-club'	=> __( 'Diners Club', 'wc-paykings' ),
					'jcb'			=> __( 'JCB', 'wc-paykings' ),
					'maestro'		=> __( 'Maestro', 'wc-paykings' ),
				),
			),
		) );
	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		$total = WC()->cart->total;

		// If paying from order, we need to get total from order not cart.
		if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) {
			$order = wc_get_order( wc_get_order_id_by_order_key( wc_clean( $_GET['key'] ) ) );
			$total = $order->get_total();
		}

        echo '<div class="paykings_new_card" id="paykings-payment-data">';

		if ( $this->description ) {
			echo apply_filters( 'wc_paykings_description', wpautop( wp_kses_post( $this->description ) ) );
		}
        $this->form();

		echo '</div>';
	}

	/**
	 * Process the payment
	 */
	public function process_payment( $order_id, $retry = true ) {

		$order = wc_get_order( $order_id );

		$this->log( "Info: Beginning processing payment for order $order_id for the amount of {$order->get_total()}" );

		// Use PayKings CURL API for payment
		try {
			$payment_args = array();

			// Check for CC details filled or not
			if( empty( $_POST['paykings-card-number'] ) || empty( $_POST['paykings-card-expiry'] ) || empty( $_POST['paykings-card-cvc'] ) ) {
				throw new Exception( __( 'Credit card details cannot be left incomplete.', 'wc-paykings' ) );
			}

			// Check for card type supported or not
			if( ! in_array( $this->get_card_type( $_POST['paykings-card-number'], 'pattern', 'name' ), $this->allowed_card_types ) ) {
				if( $this->debugging ) {
					$this->log( sprintf( __( 'Card number being used is not one of supported types (%s) in plugin settings: %s', 'wc-paykings' ), implode( ', ', $this->allowed_card_types ), $_POST['paykings-card-number'] ) );
				}
				throw new Exception( __( 'Card Type Not Accepted', 'wc-paykings' ) );
			}

			$expiry = explode( ' / ', $_POST['paykings-card-expiry'] );
			$expiry[1] = substr( $expiry[1], -2 );

			$description = sprintf( __( '%s - Order %s', 'wc-paykings' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );

			$payment_args = array(
				'ccnumber'	 		=> $_POST['paykings-card-number'],
				'ccexp'	 			=> $expiry[0] . $expiry[1],
				'cvv'	 			=> $_POST['paykings-card-cvc'],
				'orderid'	 		=> $order_id,
				'order_description'	=> $description,
				'amount'			=> $order->get_total(),
				'transactionid'		=> $order->get_transaction_id(),
				'type'				=> $this->capture ? 'sale' : 'auth',
				'first_name'		=> $order->get_billing_first_name(),
				'last_name'			=> $order->get_billing_last_name(),
				'address1'			=> $order->get_billing_address_1(),
				'address2'			=> $order->get_billing_address_2(),
				'city'				=> $order->get_billing_city(),
				'state'				=> $order->get_billing_state(),
				'country'			=> $order->get_billing_country(),
				'zip'				=> $order->get_billing_postcode(),
				'email' 			=> $order->get_billing_email(),
				'phone'				=> $order->get_billing_phone(),
				'company'			=> $order->get_billing_company(),
				'currency'			=> $this->get_payment_currency( $order_id ),
			);

			$response = $this->paykings_request( $payment_args );

			if ( $response->error || $response->declined ) {
				throw new Exception( $response->error_message );
			}

			// Store charge ID
			$order->update_meta_data( '_paykings_charge_id', $response->transactionid );

			if ( $response->approved ) {
				$order->set_transaction_id( $response->transactionid );

				if( $payment_args['type'] == 'sale' ) {

					// Store captured value
					$order->update_meta_data( '_paykings_charge_captured', 'yes' );
					$order->update_meta_data( 'PayKings Payment ID', $response->transactionid );

					// Payment complete
					$order->payment_complete( $response->transactionid );

					// Add order note
					$complete_message = sprintf( __( 'PayKings charge complete (Charge ID: %s)', 'wc-paykings' ), $response->transactionid );
					$order->add_order_note( $complete_message );
					$this->log( "Success: $complete_message" );

				} else {

					// Store captured value
					$order->update_meta_data( '_paykings_charge_captured', 'no' );

					if ( $order->has_status( array( 'pending', 'failed' ) ) ) {
						wc_reduce_stock_levels( $order_id );
					}

					// Mark as on-hold
					$authorized_message = sprintf( __( 'PayKings charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', 'wc-paykings' ), $response->transactionid );
					$order->update_status( 'on-hold', $authorized_message );
					$this->log( "Success: $authorized_message" );

				}

				$order->save();

			}

			// Remove cart
			WC()->cart->empty_cart();

			do_action( 'wc_gateway_' . $this->id . '_process_payment', $response, $order );

			// Return thank you page redirect
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);

		} catch ( Exception $e ) {
			wc_add_notice( sprintf( __( 'Gateway Error: %s', 'wc-paykings' ), $e->getMessage() ), 'error' );
			$this->log( sprintf( __( 'Gateway Error: %s', 'wc-paykings' ), $e->getMessage() ) );

			if ( $order->has_status( array( 'pending', 'failed' ) ) ) {
				$this->send_failed_order_email( $order_id );
			}

			do_action( 'wc_gateway_' . $this->id . '_process_payment_error', $e, $order );

			return array(
				'result'   => 'fail',
				'redirect' => ''
			);

		}
	}

	function paykings_request( $args ) {
		if( !class_exists( 'PayKings' ) ) {
			require_once( dirname( __FILE__ ) . '/paykings_sdk/PayKings.php' );
		}
		$gateway_debug = ( $this->logging && $this->debugging );
		$transaction = new PayKings( $this->login_id, $this->transaction_key, $gateway_debug );
		$transaction->setSandbox( $this->testmode );

		if( isset( $args['amount'] ) ) {
			$transaction->amount = $args['amount'];
		}
		if( isset( $args['transactionid'] ) && !empty( $args['transactionid'] ) ) {
			$transaction->transactionid = $args['transactionid'];
		}
		if( isset( $args['ccnumber'] ) ) {
			$transaction->ccnumber = $args['ccnumber'];
		}
		if( isset( $args['ccexp'] ) ) {
			$transaction->ccexp = $args['ccexp'];
		}
		if( isset( $args['cvv'] ) ) {
			$transaction->cvv = $args['cvv'];
		}
		if( isset( $args['first_name'] ) ) {
			$transaction->first_name = $args['first_name'];
		}
		if( isset( $args['last_name'] ) ) {
			$transaction->last_name = $args['last_name'];
		}
		if( isset( $args['address1'] ) ) {
			$transaction->address1 = $args['address1'];
		}
		if( isset( $args['address2'] ) ) {
			$transaction->address2 = $args['address2'];
		}
		if( isset( $args['city'] ) ) {
			$transaction->city = $args['city'];
		}
		if( ! in_array( $args['type'], array( 'capture', 'cancel' ) ) ) {
			if( isset( $args['state'] ) && !empty( $args['state'] ) ) {
				$transaction->state = $args['state'];
			} else {
				$transaction->state = 'NA';
			}
		}
		if( isset( $args['country'] ) ) {
			$transaction->country = $args['country'];
		}
		if( isset( $args['zip'] ) ) {
			$transaction->zip = $args['zip'];
		}
		if( isset( $args['email'] ) ) {
			$transaction->email = $args['email'];
		}
		if( isset( $args['phone'] ) ) {
			$transaction->phone = $args['phone'];
		}
		if( isset( $args['company'] ) ) {
			$transaction->company = $args['company'];
		}
		if( isset( $args['orderid'] ) ) {
			$transaction->orderid = $args['orderid'];
		}
		if( isset( $args['order_description'] ) ) {
			$transaction->order_description = substr( $args['order_description'], 0, 99 );
		}

		$transaction->currency = isset( $args['currency'] ) ? $args['currency'] : get_woocommerce_currency();
		$transaction->customer_receipt = isset( $args['customer_receipt'] ) ? $args['customer_receipt'] : false;
		$transaction->ipaddress = isset( $args['ipaddress'] ) ? $args['ipaddress'] : WC_Geolocation::get_ip_address();

		$response = $transaction->{$args['type']}();

		return $response;
	}

	function get_card_type( $value, $field = 'pattern', $return = 'label' ) {
		$card_types = array(
			array(
				'label' => 'American Express',
				'name' => 'amex',
				'pattern' => '/^3[47]/',
				'valid_length' => '[15]'
			),
			array(
				'label' => 'JCB',
				'name' => 'jcb',
				'pattern' => '/^35(2[89]|[3-8][0-9])/',
				'valid_length' => '[16]'
			),
			array(
				'label' => 'Discover',
				'name' => 'discover',
				'pattern' => '/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/',
				'valid_length' => '[16]'
			),
			array(
				'label' => 'MasterCard',
				'name' => 'mastercard',
				'pattern' => '/^5[1-5]/',
				'valid_length' => '[16]'
			),
			array(
				'label' => 'Visa',
				'name' => 'visa',
				'pattern' => '/^4/',
				'valid_length' => '[16]'
			),
			array(
				'label' => 'Maestro',
				'name' => 'maestro',
				'pattern' => '/^(5018|5020|5038|6304|6759|676[1-3])/',
				'valid_length' => '[12, 13, 14, 15, 16, 17, 18, 19]'
			),
			array(
				'label' => 'Diners Club',
				'name' => 'diners-club',
				'pattern' => '/^3[0689]/',
				'valid_length' => '[14]'
			),
		);

		foreach( $card_types as $type ) {
			$card_type = $type['name'];
			$compare = $type[$field];
			if ( ( $field == 'pattern' && preg_match( $compare, $value, $match ) ) || $compare == $value ) {
				return $type[$return];
			}
		}

	}

	/**
	 * Get payment currency, either from current order or WC settings
	 *
	 * @since 4.1.0
	 * @return string three-letter currency code
	 */
	function get_payment_currency( $order_id = false ) {
 		$currency = get_woocommerce_currency();
		$order_id = ! $order_id ? $this->get_checkout_pay_page_order_id() : $order_id;

 		// Gets currency for the current order, that is about to be paid for
 		if ( $order_id ) {
 			$order    = wc_get_order( $order_id );
 			$currency = $order->get_currency();
 		}
 		return $currency;
 	}

	/**
	 * Returns true if $currency is accepted by this gateway
	 *
	 * @since 2.1.0
	 * @param string $currency optional three-letter currency code, defaults to
	 *        order currency (if available) or currently configured WooCommerce
	 *        currency
	 * @return boolean true if $currency is accepted, false otherwise
	 */
	public function currency_is_accepted( $currency = null ) {
		// accept all currencies
		if ( ! $this->currencies ) {
			return true;
		}
		// default to order/WC currency
		if ( is_null( $currency ) ) {
			$currency = $this->get_payment_currency();
		}
		return in_array( $currency, $this->currencies );
	}

	/**
	 * Returns the order_id if on the checkout pay page
	 *
	 * @since 3.0.0
	 * @return int order identifier
	 */
	public function get_checkout_pay_page_order_id() {
		global $wp;
		return isset( $wp->query_vars['order-pay'] ) ? absint( $wp->query_vars['order-pay'] ) : 0;
	}

	/**
	 * Send the request to PayKings's API
	 *
	 * @since 2.6.10
	 *
	 * @param string $context
	 * @param string $message
	 */
	public function log( $message ) {
		if ( $this->logging ) {
			WC_PayKings_Logger::log( $message );
		}
	}

	/**
	 * Sends the failed order email to admin
	 *
	 * @version 1.0.2
	 * @since 1.0.2
	 * @param int $order_id
	 * @return null
	 */
	public function send_failed_order_email( $order_id ) {
		$emails = WC()->mailer()->get_emails();
		if ( ! empty( $emails ) && ! empty( $order_id ) ) {
			$emails['WC_Email_Failed_Order']->trigger( $order_id );
		}
	}
}
