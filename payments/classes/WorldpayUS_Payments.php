<?php
use PaymentPlugins\Configuration;
use Worldpay\WorldpayUS;
use PaymentPlugins\PaymentPlugins;
use Worldpay\WorldpayUSException;
use Worldpay\Transaction;

/**
 *
 * @author Clayton Rogers
 *        
 */
class WorldpayUS_Payments extends WC_Payment_Gateway {
	const ID = 'worldpay_us';
	public function __construct() {
		if (isset ( $_SERVER ['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER ['HTTP_X_FORWARDED_PROTO'] === 'https')
			$_SERVER ['HTTPS'] = 'on';
		$this->enabled = WP_Manager ()->get_option ( 'enabled' );
		$this->supports = self::getSupports ();
		$this->id = self::ID;
		$this->title = WP_Manager ()->get_option ( 'title_text' );
		$this->method_description = __ ( 'Accept credit card and paypal payments using your Worldpay merchant account.', 'worldpayus' );
		$this->has_fields = true;
	}
	
	/**
	 * Load the scripts used during the checkout process.
	 */
	public static function load_scripts() {
		if (WP_Manager ()->isActive ( 'enabled' )) {
			wp_enqueue_script ( 'paymentplugins-dropin', 'https://js.paymentplugins.com/js/v1/paymentplugins.min.js' );
			// wp_enqueue_script ( 'paymentplugins-dropin', 'https://development.paymentplugins.com/js/v1/paymentplugins-dropin.js' );
			wp_enqueue_style ( 'worldpay-us-checkout', WORLDPAYUS_ASSETS . 'css/worldpay-us-checkout.css' );
			wp_enqueue_script ( 'worldpay-us-checkout-js', WORLDPAYUS_ASSETS . 'js/worldpay-checkout.js', array (
					'jquery',
					'paymentplugins-dropin' 
			), null, true );
		}
	}
	
	/**
	 * Initialize the action hooks used for the payment functionality.
	 */
	public static function init() {
		add_filter ( 'woocommerce_payment_gateways', __CLASS__ . '::add_worldpay_payment_gateway' );
		add_action ( 'wp_enqueue_scripts', __CLASS__ . '::load_scripts' );
	}
	
	/**
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::payment_fields()
	 */
	public function payment_fields() {
		try {
			$paymentPlugins = PaymentPlugins::newInstance ();
			$customerId = self::getWorldpayCustomerId ();
			if ($customerId) {
				$response = $paymentPlugins->getClientToken ( array (
						'customerId' => $customerId 
				) );
			} else {
				if ($user_id = wp_get_current_user ()->ID) {
					self::createWorldpayCustomer ( $user_id );
					$customerId = self::getWorldpayCustomerId ();
					$response = $paymentPlugins->getClientToken ( array (
							'customerId' => $customerId 
					) );
				} else {
					$response = $paymentPlugins->getClientToken ();
				}
			}
			$this->loadDropinForm ( $response );
		} catch ( Exception $e ) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'There was an error retrieving the client token. Message" %s', $e->getMessage () ) );
			wc_add_notice ( $e->getMessage (), 'error' );
		}
	}
	
	/**
	 * Process the Woocommerce Order.
	 *
	 * @see WC_Payment_Gateway::process_payment()
	 */
	public function process_payment($order_id) {
		if (self::is_paymentChangeRequest ()) {
			return array (
					'result' => 'success',
					'redirect' => wc_get_order ( $order_id )->get_checkout_order_received_url () 
			);
		}
		$order = wc_get_order ( $order_id );
		$user_id = wp_get_current_user ()->ID;
		if (WP_Manager ()->woocommerceSubscriptionsActive () && wcs_order_contains_subscription ( $order )) {
			return WCS_WorldpayUS_Subscriptions::process_subscription ( $order, $user_id );
		}
		if (WP_Manager ()->isActive ( 'worldpayus_subscriptions' ) && wps_order_contains_subscription ( $order_id )) {
			return WorldpayUS_Subscriptions::processSubscription ( $order_id );
		}
		$args = array (
				'amount' => $order->get_total (),
				'orderId' => WP_Manager ()->get_option ( 'invoice_prefix' ) . $order->id 
		);
		if (! $args = self::getPaymentMethodFromRequest ( $args )) {
			wc_add_notice ( __ ( 'The transactions requires a valid payment method Id.', 'worldpayus' ), 'error' );
			return array (
					'result' => 'failure',
					'redirect' => '' 
			);
		}
		self::addOrderAttributes ( $args, $order );
		try {
			$response = WP_Manager ()->worldpayUS->transaction ( $args );
			if ($response->success) {
				self::save_transactionMeta ( $response->transaction, $order, $args );
				$order->update_status ( WP_Manager ()->get_option ( 'order_status' ) );
				update_post_meta ( $order->id, '_transaction_id', $response->transaction->transactionId );
				WC ()->cart->empty_cart ();
				WP_Manager ()->log->writeToLog ( sprintf ( 'Payment Success: %s', print_r ( $response, true ) ) );
				return array (
						'result' => 'success',
						'redirect' => $this->get_return_url ( $order ) 
				);
			} else if (! $response->success) {
				if ($response->errors) {
					WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Error: %s', print_r ( $response, true ) ) );
					wc_add_notice ( $response->message, 'error' );
				} else {
					wc_add_notice ( sprintf ( __ ( 'There was an issue processing your payment. Reason: %s' ), $response->message, 'error' ) );
					WP_Manager ()->log->writeToLog ( sprintf ( 'There was an error processing the payment. Message: %s', $response->message ), 'error' );
				}
				return array (
						'result' => 'failure',
						'redirect' => '' 
				);
			} else {
				throw new WorldpayUSException ( null, 'There was an issue processing your payment. ' );
			}
		} catch ( WorldpayUSException $e ) {
			wc_add_notice ( sprintf ( '%s', $e->getMessage () ), 'error' );
			WP_Manager ()->log->writeToLog ( $e->getMessage () );
			return array (
					'result' => 'failure',
					'redirect' => '' 
			);
		} catch ( Exception $e ) {
			wc_add_notice ( sprintf ( '%s', $e->getMessage () ), 'error' );
			WP_Manager ()->log->writeToLog ( $e->message );
			return array (
					'result' => 'failure',
					'redirect' => '' 
			);
		}
	}
	public function process_refund($order_id, $amount = null, $reason = '') {
		$order = wc_get_order ( $order_id );
		if (! self::can_processRefund ( $order )) {
			WP_Manager ()->log->writeToLog ( sprintf ( 'Order %s cannot be refunded. There is no transaction id saved.' ), $order->id );
			return new WP_Error ( 'refund', sprintf ( __ ( 'Order %s cannot be refunded. There is no transaction id saved.', 'worldpayus' ), $order->id ) );
		}
		if (self::isPaypalPayment ( $order_id )) {
			if ($result = Worldpay_Paypal::process_refund ( $order_id, $amount )) {
				return true;
			} else {
				return new WP_Error ( 'paypal_refund', 'The refund for order ' . $order_id . ' could not be processed.' );
			}
		}
		$args = array (
				'transactionId' => $order->get_transaction_id (),
				'amount' => $amount 
		);
		try {
			$response = WP_Manager ()->worldpayUS->refund ( $args );
			if ($response->success) {
				WP_Manager ()->log->writeToLog ( sprintf ( __ ( 'Order %s was refunded in the amount of %s%s', 'worldpayus' ), $order_id, get_woocommerce_currency_symbol ( $order->order_currency ), $amount ) );
				WP_Manager ()->log->writeToLog ( sprintf ( 'Refund Success: %s', print_r ( $response, true ) ) );
				$order->add_order_note ( sprintf ( __ ( 'Order was refunded in the amout of %s%s', 'worldpayus' ), get_woocommerce_currency_symbol ( $order->order_currency ), $amount ) );
				return true;
			} else {
				WP_Manager ()->log->writeToLog ( sprintf ( 'Refund Error: %s', print_r ( $response, true ) ) );
				return new WP_Error ( 'refund_error', $response->message );
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeToLog ( sprintf ( 'Refund Exception: %s', print_r ( $e, true ) ) );
			return new WP_Error ( 'refund_error', sprintf ( __ ( 'The refund for order %s could not be completed. Reason: %s', 'worldpayus' ), $order_id, $response->message ) );
		}
	}
	public static function can_processRefund(WC_Order $order) {
		return $order->get_transaction_id ();
	}
	public static function isPaypalPayment($orderId) {
		return (get_post_meta ( $orderId, '_payment_type', true ) === 'paypal');
	}
	public static function save_transactionMeta(Transaction $transaction, WC_Order $order, $args) {
		$paymentMethod = $transaction->paymentMethod;
		$paymentTitle = sprintf ( '%s - %s', $paymentMethod->paymentMethodType, $paymentMethod->maskedNumber );
		update_post_meta ( $order->id, '_payment_method_token', $args ['paymentVaultToken'] ['paymentMethodId'] );
		update_post_meta ( $order->id, '_payment_method_title', $paymentTitle );
		update_post_meta ( $order->id, '_transaction_id', $transaction->transactionId );
		update_post_meta ( $order->id, '_transaction_processed', 'yes' );
		WP_Manager ()->log->writeToLog ( sprintf ( 'Transaction %s processed using payment method %s. Payment Token = %s.
							 Total amount = %s. Worldpay CustomerId: %s. User ID: %s', $transaction->transactionId, $paymentTitle, $args ['paymentVaultToken'] ['paymentMethodId'], $order->get_total (), $paymentMethod->customerId, wp_get_current_user ()->ID ) );
	}
	public static function add_worldpay_payment_gateway($methods) {
		$methods [] = 'WorldpayUS_Payments';
		return $methods;
	}
	public function get_accepted_paymentMethods() {
		$html = null;
		foreach ( WP_Manager ()->get_option ( 'payment_methods' ) as $paymentMethod => $value ) {
			if (! empty ( $value )) {
				$src = WORLDPAYUS_ASSETS . 'images/' . $paymentMethod . '.png';
				$html .= sprintf ( '<div class="payment-method-image"><img src="%s"></div>', $src );
			}
		}
		if ($html) {
			$html = sprintf ( '%s%s%s', '<div class="accepted-payment-forms">', $html, '</div>' );
		}
		return $html;
	}
	private static function cart_containsSubscription() {
		$subscriptions = false;
		if (self::$subscriptions_active && WC_Subscriptions_Cart::cart_contains_subscription ()) {
			$subscriptions = true;
		} elseif (self::is_paymentChangeRequest ()) {
			$subscriptions = true;
		}
		return $subscriptions;
	}
	public static function delete_customerPaymentMethod($user_id = 0) {
	}
	
	/**
	 * Returns the active environment.
	 */
	public function getEnvironment() {
		return WP_Manager ()->getEnvironment ();
	}
	
	/**
	 *
	 * @param array $args        	
	 */
	public static function getPaymentMethodFromRequest($args = array()) {
		$paymentVaultToken = array ();
		if (self::isTokenPaymentMethod ()) {
			$paymentVaultToken ['paymentMethodId'] = self::getRequestParameter ( 'payment_method_id' );
			$paymentVaultToken ['publicKey'] = WP_Manager ()->getPublicKey ();
			$args ['paymentVaultToken'] = $paymentVaultToken;
		} else if (self::isVaultPaymentMethod ()) {
			$paymentVaultToken ['paymentMethodId'] = self::getRequestParameter ( 'payment_method_id' );
			$paymentVaultToken ['customerId'] = self::getWorldpayCustomerId ();
			$args ['paymentVaultToken'] = $paymentVaultToken;
		} else {
			$args = false;
		}
		return $args;
	}
	
	/**
	 * Determines if the payment method being used is a payment token.
	 *
	 * @return bool
	 */
	public static function isTokenPaymentMethod() {
		$isToken = false;
		if (self::getRequestParameter ( 'payment_method_type' ) === 'token') {
			$isToken = true;
		}
		return $isToken;
	}
	
	/**
	 * Determines if the payment method being used is a vaulted payment method.
	 *
	 * @return bool
	 */
	public static function isVaultPaymentMethod() {
		$isVault = false;
		if (self::getRequestParameter ( 'payment_method_type' ) === 'vault') {
			$isVault = true;
		}
		return $isVault;
	}
	
	/**
	 * Get the request parameter from the $_POST, $_GET, or $_REQUEST
	 *
	 * @param string $string        	
	 * @return string
	 */
	public static function getRequestParameter($string = '') {
		$param = null;
		if (isset ( $_POST [$string] )) {
			$param = $_POST [$string];
		} else if (isset ( $_GET [$string] )) {
			$param = $_GET [$string];
		} else if (isset ( $_REQUEST [$string] )) {
			$param = $_REQUEST [$string];
		}
		return $param;
	}
	public function admin_options() {
		?>
<div class="worldpay-admin-options">
	<ul>
		<li><a
			href="<?php echo admin_url().'admin.php?page=worldpayus-payment-settings'?>">API
				Settings</a></li>
		<li><a
			href="<?php echo admin_url().'admin.php?page=page=worldpayus-woocommerce-settings'?>">WooCommerce
				Settings</a></li>
		<li><a
			href="<?php echo admin_url().'admin.php?page=page=worldpayus-subscription-config'?>">WooCommerce
				Subscriptions</a></li>
		<li><a
			href="<?php echo admin_url().'admin.php?page=page=worldpayus-debug-log'?>">Debug
				Log</a></li>
		<li><a
			href="<?php echo admin_url().'admin.php?page=worldpayus-payments-instructions'?>">Configuration
				Instructions</a></li>
	</ul>
</div>
<?php
	}
	
	/**
	 * Fetch the customerId from the database.
	 * If no customer id exists for the user, then create
	 * the user in Worldpay. If the customer is not logged in, do nothing.
	 *
	 * @return $customerId
	 */
	public static function getWorldpayCustomerId() {
		return WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID );
	}
	
	/**
	 * Create the Worldpay Customer using the specified wordpress user_id;
	 */
	public static function createWorldpayCustomer($userId = 0) {
		$userMeta = array_map ( function ($user_meta) {
			return $user_meta [0];
		}, get_user_meta ( $userId ) );
		$firstName = ! empty ( $userMeta ['first_name'] ) ? $userMeta ['first_name'] : $userMeta ['billing_first_name'];
		$lastName = ! empty ( $userMeta ['last_name'] ) ? $userMeta ['last_name'] : $userMeta ['billing_last_name'];
		$email = ! empty ( $userMeta ['billing_email'] ) ? $userMeta ['billing_email'] : '';
		$phone = ! empty ( $userMeta ['billing_phone'] ) ? $userMeta ['billing_phone'] : '';
		$line1 = ! empty ( $userMeta ['billing_address_1'] ) ? $userMeta ['billing_address_1'] : '';
		$city = ! empty ( $userMeta ['billing_city'] ) ? $userMeta ['billing_city'] : '';
		$state = ! empty ( $userMeta ['billing_state'] ) ? $userMeta ['billing_state'] : '';
		$zip = ! empty ( $userMeta ['billing_postcode'] ) ? $userMeta ['billing_postcode'] : '';
		$params = array (
				'firstName' => $firstName,
				'lastName' => $lastName,
				'emailAddress' => $email,
				'phoneNumber' => $phone,
				'address' => array (
						'line1' => $line1,
						'city' => $city,
						'state' => $state,
						'zip' => $zip 
				) 
		);
		return WP_Manager ()->createWorldpayCustomer ( $params, $userId );
	}
	private function loadDropinForm($clientToken) {
		if (WP_Manager ()->getEnvironment () === 'sandbox') {
			?>
<div class="div--environment">
	<span><?php echo __('Sandbox', 'worldpayus')?></span>
</div>
<?php
		}
		$paymentMethods = WP_Manager ()->get_option ( 'payment_methods' );
		if (! empty ( $paymentMethods )) {
			?>
<div class="div--paymentMethods">
			<?php
			foreach ( $paymentMethods as $method => $name ) {
				if (! empty ( WP_Manager ()->settings ['payment_methods'] [$method] )) {
					?>
				<span class="paymentMethod"><img
		src="<?php echo WorldpayUS_PaymentMethods::payment_methods()[$method]['src']?>" /></span>
				<?php
				}
			}
			?></div><?php
		}
		?>
<div id="paymentplugins-container"></div>
<input type="hidden" id="worldpay_client_token"
	value="<?php echo $clientToken?>" />
<script>
  		jQuery(document).ready(function(){
  	  		jQuery(document.body).trigger('worldpay_form_ready')
  	  	});
		</script>
<?php
	}
	public static function savePaymentMethodTitle(WC_Order $order) {
		try {
			$response = WP_Manager()->worldpayUS->getPaymentMethod ( array (
					'paymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ),
					'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
			) );
			$paymentMethod = $response->paymentMethod;
			$paymentTitle = $paymentMethod->paymentMethodType . ' - ' . $paymentMethod->maskedNumber;
			update_post_meta ( $order->id, '_payment_method_title', $paymentTitle );
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( 'Method: update_subscription_paymentMethod.' . $e->getMessage () );
		}
	}
	public static function is_paymentChangeRequest() {
		return (isset ( $_REQUEST ['woocommerce_change_payment'] ) || isset ( $_REQUEST ['change_payment_method'] ));
	}
	public static function payWithPaypal() {
		return (isset ( $_POST ['worldpay_paywith_paypal'] ) && ! empty ( $_POST ['worldpay_paywith_paypal'] ));
	}
	private function getSecureNetId() {
		return WP_Manager ()->getSecureNetId ();
	}
	private function getSecureKey() {
		return WP_Manager ()->getSecureKey ();
	}
	private function getPublickKey() {
		return WP_Manager ()->getPublicKey ();
	}
	public static function getSupports() {
		$array = array (
				'subscriptions',
				'products',
				'subscription_cancellation',
				'multiple_subscriptions',
				'subscription_amount_changes',
				'subscription_date_changes',
				'default_credit_card_form',
				'refunds',
				'pre-orders',
				'subscription_payment_method_change_admin',
				'gateway_scheduled_payments',
				'subscription_payment_method_change_customer' 
		);
		if (! WP_Manager ()->isActive ( 'enable_payment_change' )) {
			if ($key = array_search ( 'subscription_payment_method_change_customer', $array )) {
				unset ( $array [$key] );
			}
		}
		return $array;
	}
	
	/**
	 * Add the order attributes.
	 *
	 * @param unknown $args        	
	 */
	public static function addOrderAttributes(&$args, WC_Order $order) {
		self::addExtendedInformation ( $args, $order );
		// self::addUserDefinedFields ( $args, $order );commented 5/2/16. Functionality not in use currently.
	}
	public static function addExtendedInformation(&$args, WC_Order $order) {
		$args ['extendedInformation'] = array (
				'levelThreeData' => array (
						'destinationAddress' => array (
								'line1' => $order->shipping_address_1,
								'city' => $order->shipping_city,
								'state' => $order->shipping_state,
								'zip' => $order->shipping_postcode,
								'country' => $order->shipping_country,
								'company' => $order->shipping_company 
						) 
				),
				'levelTwoData' => array (
						'orderDate' => $order->order_date,
						'taxAmount' => $order->get_total_tax (),
						'purchaseOrder' => $order->id 
				),
				'typeOfGoods' => 'PHYSICAL' 
		);
		self::addOrderNotes ( $args, $order );
	}
	public static function addOrderNotes(&$args, WC_Order $order) {
		$notes = WP_Manager ()->get_option ( 'order_notes' );
		if (empty ( $notes )) {
			$notes = self::getOrderNotes ( $order );
		}
		$args ['extendedInformation'] ['notes'] = $notes;
	}
	public static function addUserDefinedFields(&$args, WC_Order $order) {
		$args ['userDefinedFields'] = array (
				array (
						'udfname' => 'customer_ip_address',
						'udfvalue' => $order->customer_ip_address 
				),
				array (
						'udfname' => 'website',
						'udfvalue' => get_site_url () 
				),
				array (
						'udfname' => 'woocommerce_order_total',
						'udfvalue' => $order->get_total () 
				),
				array (
						'udfname' => 'woocommerce_order_id',
						'udfvalue' => $order->id 
				),
				array (
						'udfname' => 'woocommerce_version',
						'udfvalue' => WC ()->version 
				),
				array (
						'udfname' => 'user_id',
						'udfvlaue' => wp_get_current_user ()->ID 
				) 
		);
	}
	public static function getOrderNotes(WC_Order $order) {
		$items = $order->get_items ();
		$notes = '';
		foreach ( $items as $item ) {
			$_product = wc_get_product ( $item ['product_id'] );
			$notes .= sprintf ( __ ( 'Product ID: %s. Product Description: %s. Quantity: %s', 'worldpayus' ), $_product->id, get_post ( $_product->id )->post_content, $item ['qty'] );
		}
		return $notes;
	}
}
WorldpayUS_Payments::init ();
?>