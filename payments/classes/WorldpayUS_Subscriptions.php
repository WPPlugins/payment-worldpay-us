<?php
use Worldpay\WorldpayUS;
use Worldpay\PaymentPlan;
use Worldpay\WorldpayUSException;
/**
 * Subscription class.
 *
 * @author Payment Plugins
 *        
 */
class WorldpayUS_Subscriptions extends WorldpayUS_Payments {
	private static $cycleTypes = array (
			'day' => 'WEEKLY',
			'week' => 'WEEKLY',
			'month' => 'MONTHLY',
			'year' => 'ANNUALLY' 
	);
	private static $numericDay = array (
			1 => 2,
			2 => 3,
			3 => 4,
			4 => 5,
			5 => 6,
			6 => 7,
			7 => 1 
	);
	public static function init() {
		if (WP_Manager ()->isActive ( 'worldpayus_subscriptions' ) && !WP_Manager()->woocommerceSubscriptionsActive()) {
			
			add_action ( 'save_post', __CLASS__ . '::saveAdminSubscriptionMeta' );
			
			add_action ( 'woocommerce_checkout_order_processed', __CLASS__ . '::processCheckout', 100, 2 );
			
			add_filter ( 'woocommerce_get_price_html', __CLASS__ . '::getSubscriptionPriceHTML', 10, 2 );
			
			add_filter ( 'woocommerce_cart_product_price', __CLASS__ . '::cartSubscriptionPrice', 10, 2 );
			
			add_filter ( 'woocommerce_cart_product_subtotal', __CLASS__ . '::getProductSubtotal', 10, 4 );
			
			add_filter ( 'woocommerce_order_formatted_line_subtotal', __CLASS__ . '::formatLineSubtotal', 10, 3 );
		}
	}
	
	/**
	 * Save the subscription meta for the given post_id.
	 * This method is used for changes made by the admin to the subscription
	 * product.
	 *
	 * @param int $post_id        	
	 */
	public static function saveAdminSubscriptionMeta($post_id) {
		if (! isset ( $_POST ['product-type'] )) {
			return;
		}
		$fields = array (
				'worldpayus_subscription',
				'_subscription_price',
				'_subscription_period_interval',
				'_subscription_period',
				'_subscription_sign_up_fee',
				'_subscription_length' 
		);
		if (isset ( $_POST ['worldpayus_subscription'] )) {
			foreach ( $fields as $field ) {
				$value = isset ( $_POST [$field] ) ? $_POST [$field] : '';
				update_post_meta ( $post_id, $field, stripslashes ( $value ) );
				if ($field === '_subscription_price') {
					update_post_meta ( $post_id, '_regular_price', $value );
					update_post_meta ( $post_id, '_price', $value );
				}
				if ($field === '_subscription_period') {
					$type = $value === 'day' ? 'variable' : 'recurring';
					update_post_meta ( $post_id, '_subscription_type', $type );
				}
			}
		} else {
			update_post_meta ( $post_id, 'worldpayus_subscription', '' );
		}
	}
	public static function validateCartEntries($is_valid, $product_id, $quantity) {
		if (wps_is_product_subscription ( $product_id )) {
			if ($quantity > 1) {
				$is_valid = false;
				wc_add_notice ( __ ( 'You cannot add more than one subscription to your cart at a time.', 'worldpayus' ), 'error' );
				return $is_valid;
			}
			if (WC ()->cart->get_cart_contents_count () >= 1) {
				$is_valid = false;
				wc_add_notice ( __ ( 'You cannot have more than one item in your cart when it\'s a subscription.', 'worldpayus' ), 'error' );
				return $is_valid;
			}
		}
		$hasProducts = false;
		$hasSubscriptions = false;
		/* Check if the cart contains a product. */
		foreach ( WC ()->cart->get_cart () as $cart => $values ) {
			$_product = $values ['data'];
			if (! wps_is_product_subscription ( $_product->id )) {
				$hasProducts = true;
			} else {
				$hasSubscriptions = true;
			}
		}
		if ($hasProducts) {
			if (wps_is_product_subscription ( $product_id )) {
				$is_valid = false;
				wc_add_notice ( __ ( 'You cannot mix products with subscriptions in your cart. These items must be purchased separately.', 'worldpayus' ), 'error' );
			}
		} elseif ($hasSubscriptions) {
			if (! wps_is_product_subscription ( $product_id )) {
				$is_valid = false;
				wc_add_notice ( __ ( 'You cannot mix products with subscriptions in your cart. These items must be purchased separately.', 'worldpayus' ), 'error' );
			}
		}
		return $is_valid;
	}
	public static function cartSubscriptionPrice($price, WC_Product $product) {
		if (wps_is_product_subscription ( $product->id )) {
			$price = wc_price ( $product->subscription_price );
			$period = $product->subscription_period;
			$frequency = $product->subscription_period_interval;
			$price = sprintf ( '%s %s %s for %s', $price, wps_get_subscription_interval ( $period ) [$frequency], $period, wps_get_subscription_period_string ( $period, $product->subscription_length ) );
		}
		return $price;
	}
	public static function getProductSubtotal($product_subtotal, $_product, $quantity, $cart) {
		if (wps_is_product_subscription ( $_product->id )) {
			$price = wc_price ( $_product->subscription_price );
			$period = $_product->subscription_period;
			$frequency = $_product->subscription_period_interval;
			$product_subtotal = sprintf ( '%s %s %s for %s', $price, wps_get_subscription_interval ( $period ) [$frequency], $period, wps_get_subscription_period_string ( $period, $_product->subscription_length ) );
		}
		return $product_subtotal;
	}
	public static function getSubscriptionPriceHTML($html, WC_Product $product) {
		if (! wps_is_product_subscription ( $product->id )) {
			return $html;
		}
		$price = get_woocommerce_currency_symbol ( get_woocommerce_currency () ) . $product->subscription_price;
		$period = $product->subscription_period;
		$frequency = $product->subscription_period_interval;
		$html = sprintf ( '<span class="amount">%s %s %s for %s</span>', $price, wps_get_subscription_interval ( $period ) [$frequency], $period, wps_get_subscription_period_string ( $period, $product->subscription_length ) );
		return $html;
	}
	
	/**
	 *
	 * @param string $subtotal        	
	 * @param array $item        	
	 * @param WC_Order $order        	
	 */
	public static function formatLineSubtotal($subtotal, $item, $order) {
		if (wps_is_product_subscription ( $item ['product_id'] )) {
			$product = wc_get_product ( $item ['product_id'] );
			$subtotal = sprintf ( '<span class="amount">%s %s %s for %s</span>', $product->get_price (), wps_get_subscription_interval ( $product->subscription_period ) [$product->subscription_period_interval], $product->subscription_period, wps_get_subscription_period_string ( $product->subscription_period, $product->subscription_length ) );
		}
		return $subtotal;
	}
	
	/**
	 * Method that creates the WorldpayUS subscription.
	 *
	 * @param unknown $order_id        	
	 */
	public static function processSubscription($order_id) {
		$subscriptions = wps_get_subscriptions_from_order ( $order_id );
		$order = wc_get_order ( $order_id );
		foreach ( $subscriptions as $subscription ) {
			
			if ($subscription->isRecurring () && ! $subscription->paymentProcessed ()) {
				$result = self::processRecurringSubscription ( $subscription, $order );
				if (! $result) {
					return array (
							'result' => 'failure',
							'redirect' => 'false' 
					);
				}
			} else if ($subscription->isVariable () && ! $subscription->paymentProcessed ()) {
				$result = self::processVariableSubscription ( $subscription );
				if (! $result) {
					return array (
							'result' => 'failure',
							'redirect' => 'false' 
					);
				}
			}
		}
		WC ()->cart->empty_cart ();
		$order->update_status ( WP_Manager ()->get_option ( 'order_status' ) );
		return array (
				'result' => 'success',
				'redirect' => $order->get_checkout_order_received_url () 
		);
	}
	
	/**
	 *
	 * @param WorldpayUS_Subscription $subscription        	
	 * @return bool
	 */
	public static function processRecurringSubscription(WorldpayUS_Subscription $subscription, WC_Order $order) {
		if (WP_Manager ()->getRequestParameter ( 'payment_method_type' ) === 'token') {
			wc_add_notice ( __ ( 'You must use a saved payment method or check Save Card when purchasing a subscription.', 'worldpayus' ), 'error' );
			return false;
		}
		$params = array ();
		$result = null;
		$plan = array (
				'primaryPaymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ),
				'amount' => $subscription->getSubscriptionPrice (),
				'frequency' => $subscription->getSubscriptionFrequency (),
				'cycleType' => self::$cycleTypes [$subscription->getSubscriptionPeriod ()],
				'startDate' => date ( 'm/d/Y', $subscription->getStartDate () ),
				'endDate' => date ( 'm/d/Y', $subscription->getEndDate () ),
				'notes' => self::getSubscriptionNotes ( $subscription ),
				'maxRetries' => WP_Manager ()->get_option ( 'max_retries' ),
				'active' => true 
		);
		$plan = self::populateBillingDay ( $plan, $subscription );
		$params ['plan'] = $plan;
		$params ['customerId'] = WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID );
		try {
			$response = WP_Manager ()->worldpayUS->recurringPaymentPlan ( $params );
			$result = $response->success;
			if ($response->success) {
				WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription success: %s', print_r ( $response, true ) ) );
				self::saveSubscriptionMeta ( $subscription, $response->paymentPlan );
				$subscription->update_status ( WP_Manager ()->get_option ( 'subscription_status' ) );
				$subscription->add_order_note ( sprintf ( __ ( 'The subscription has been created. PlanId %s', 'worldpayus' ), $response->paymentPlan->planId ) );
			} else {
				wc_add_notice ( $response->message, 'error' );
				WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Subscription Failed. %s', print_r ( $response, true ) ) );
			}
		} catch ( \Worldpay\WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Subscription Exception: %s', print_r ( $e, true ) ) );
			wc_add_notice ( sprintf ( 'There was an error processing your subscription. Message: %s', $e->getMessage () ), 'error' );
			$result = false;
		}
		return $result;
	}
	public static function processVariableSubscription(WorldpayUS_Subscription $subscription) {
		if (WP_Manager ()->getRequestParameter ( 'payment_method_type' ) === 'token') {
			wc_add_notice ( __ ( 'You must use a saved payment method or check Save Card when purchasing a subscription.', 'worldpayus' ), 'error' );
			return false;
		}
		$worldpayUS = WorldpayUS::instance ();
		$params = array (
				'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
		);
		$plan = array (
				'primaryPaymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ),
				'planStartDate' => date ( 'm/d/Y', $subscription->getStartDate () ),
				'planEndDate' => date ( 'm/d/Y', $subscription->getEndDate () ),
				'notes' => self::getSubscriptionNotes ( $subscription ),
				'maxRetries' => WP_Manager ()->get_option ( 'max_retries' ),
				'scheduledPayments' => self::getScheduledPayments ( $subscription ),
				'active' => true 
		);
		$params ['plan'] = $plan;
		try {
			$response = $worldpayUS->variablePaymentPlan ( $params );
			$result = $response->success;
			if ($response->success) {
				WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription %s was processed successfully. PlanId = %s', $response->paymentPlan->planId, $subscription->id ) );
				self::saveSubscriptionMeta ( $subscription, $response->paymentPlan );
				$subscription->update_status ( WP_Manager ()->get_option ( 'subscription_status' ) );
				$subscription->add_order_note ( sprintf ( __ ( 'The subscription has been created. PlanId %s', 'worldpayus' ), $response->paymentPlan->planId ) );
			} else {
				wc_add_notice ( $response->errors [0]->message, 'error' );
				WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Method: processRecurringSubscription. Message: %s', $response->errors [0]->message ) );
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Method: processRecurringSubscription. There was an error creating subscription for user %s. Message: %s', wp_get_current_user ()->ID ), $e->getMessage () );
			wc_add_notice ( sprintf ( 'There was an error processing your subscription. Message: %s', $e->getMessage () ), 'error' );
			$result = false;
		}
		return $result;
	}
	
	/**
	 * Process the subscription orders associated with the shopping cart.
	 *
	 * @param int $order_id        	
	 * @param array $posted_data        	
	 */
	public static function processCheckout($order_id, $posted_data) {
		if (! wps_cart_contains_subscription ( $order_id )) { // Order does not contain any subscription products.
			return;
		}
		if (wps_subscriptions_already_created ( $order_id )) { // Check if subscription orders were already created for the order.
			return;
		}
		$result = wps_create_subscriptions ( $order_id );
		
		if (is_wp_error ( $result )) {
			throw new Exception ( $result->get_error_message () );
		}
	}
	public static function getScheduledPayments(WorldpayUS_Subscription $subscription) {
		$scheduledPayments = array ();
		$startDate = new DateTime ( $startDate );
		$startDate->setTimestamp ( $startDate );
		$numberOfCyces = $subscription->product->subscription_length / $subscription->product->subscription_period_interval;
		for($i = 0; $i < $numberOfCyces; $i ++) {
			$scheduleDate = $startDate->add ( new DateInterval ( 'P' . $subscription->getSubscriptionFrequency () . wps_get_date_time_interval ( $subscription->getSubscriptionPeriod () ) ) );
			$schedulePayment = array (
					'amount' => $subscription->getSubscriptionPrice (),
					'scheduledDate' => $scheduleDate->format ( 'm/d/Y' ),
					'paymentDate' => $scheduleDate->format ( 'm/d/Y' ),
					'numberOfRetries' => WP_Manager ()->get_option ( 'max_retries' ),
					'paid' => false,
					'processed' => false 
			);
			$scheduledPayments [] = $schedulePayment;
		}
		return $scheduledPayments;
	}
	public static function saveSubscriptionMeta(WorldpayUS_Subscription $subscription, PaymentPlan $paymentPlan) {
		update_post_meta ( $subscription->id, '_payment_method_token', $paymentPlan->primaryPaymentMethodId );
		update_post_meta ( $subscription->order->id, '_payment_method_token', $paymentPlan->primaryPaymentMethodId );
		update_post_meta ( $subscription->id, '_subscription_plan_id', $paymentPlan->planId );
		update_post_meta ( $subscription->id, 'worldpayus_customer_id', $paymentPlan->customerId );
		update_post_meta ( $subscription->id, '_payment_processed', 'yes' );
		update_post_meta ( $subscription->id, '_payment_method', 'worldpay_us' );
		if ($paymentMethod = WP_Manager ()->getPaymentMethod ( $paymentPlan->primaryPaymentMethodId )) {
			update_post_meta ( $subscription->id, '_payment_method_title', $paymentMethod->paymentMethodType . ' - ' . $paymentMethod->maskedNumber );
			update_post_meta ( $subscription->order->id, '_payment_method_title', $paymentMethod->paymentMethodType . ' - ' . $paymentMethod->maskedNumber );
		}
	}
	public static function removeItemFromCart(WorldpayUS_Subscription $subscription) {
		foreach ( WC ()->cart->get_cart () as $cart_key => $item ) {
			$product_id = $item ['product_id'];
			foreach ( $subscription->get_items () as $item ) {
				if ($item ['product_id'] === $product_id) {
					WC ()->cart->remove_cart_item ( $cart_key );
					return;
				}
			}
		}
	}
	public static function populateBillingDay($plan, WorldpayUS_Subscription $subscription) {
		switch ($subscription->getSubscriptionPeriod ()) {
			case 'week' :
				$plan ['dayOfTheWeek'] = self::$numericDay [date ( 'N', $subscription->getStartDate () )];
				break;
			case 'month' :
				$plan ['dayOfTheMonth'] = date ( 'd', self::getMonthlySubscriptionDayOfMonth ( $subscription->getStartDate () ) ); // Ensure day of month is between 1 - 28.
				break;
			case 'year' :
				$timestamp = self::getMonthlySubscriptionDayOfMonth ( $subscription->getStartDate () );
				$plan ['dayOfTheMonth'] = date ( 'd', $timestamp );
				$plan ['month'] = date ( 'm', $timestamp );
		}
		return $plan;
	}
	
	/**
	 *
	 * @param unixtimestamp $timestamp        	
	 * @return unixtimestamp
	 */
	public static function getMonthlySubscriptionDayOfMonth($timestamp) {
		$date = new DateTime ();
		$date->setTimestamp ( $timestamp );
		if (($day = absint ( $date->format ( 'd' ) )) > 28) { // If day is greater than 28, roll date up to the next month.
			while ( ($day = absint ( $date->format ( 'd' ) )) > 28 ) {
				$date->add ( new DateInterval ( 'P1D' ) );
			}
		}
		return $date->getTimestamp ();
	}
	public static function getSubscriptionNotes(WorldpayUS_Subscription $subscription) {
		$notes = WP_Manager ()->get_option ( 'subscription_notes' );
		if (empty ( $notes )) {
			$notes = sprintf ( __ ( 'Product ID: %s. Product Description: %s. Shipping Cost: %s', 'worldpayus' ), $subscription->product->id, get_post ( $subscription->product->id )->post_content, $subscription->getShippingPrice () );
		}
		return $notes;
	}
}
WorldpayUS_Subscriptions::init ();
?>