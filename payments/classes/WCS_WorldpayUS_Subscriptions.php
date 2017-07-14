<?php
use Worldpay\WorldpayUS;
use Worldpay\WorldpayUSException;
use Worldpay\PaymentPlan;
use Worldpay\VariablePaymentPlan;
/**
 * Subscription class used when WooCommerce Subscriptions is active.
 *
 * @author Clayton
 *        
 */
class WCS_WorldpayUS_Subscriptions extends WorldpayUS_Payments {
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
		
		// Version 2.0.0+
		add_filter ( 'woocommerce_my_subscriptions_payment_method', __CLASS__ . '::subscription_payment_method', 10, 2 );
		
		// Version 2.0.0+
		add_action ( 'woocommerce_subscription_pending-cancel_' . self::ID, __CLASS__ . '::cancel_subscription', 10, 1 );
		
		// Version 2.0.0+
		add_action ( 'woocommerce_subscription_cancelled_' . self::ID, __CLASS__ . '::cancel_subscription', 10, 1 );
		
		// Version 2.0.0+
		// add_action ( 'woocommerce_scheduled_subscription_payment_' . self::ID, __CLASS__ . '::process_recurring_subscriptionPayment', 10, 2 );
		
		// Version 2.0.0+
		add_action ( 'woocommerce_subscription_payment_method_updated_to_' . self::ID, __CLASS__ . '::update_subscription_paymentMethod', 10, 2 );
		
		// Version 2.0,0+
		add_action ( 'woocommerce_subscription_payment_method_updated_from_' . self::ID, __CLASS__ . '::cancelSubscriptionForOldPaymentMethod', 10, 2 );
		// Version 2.0.0+
		// add_filter('woocommerce_subscriptions_process_payment_for_change_method_via_pay_shortcode', __CLASS__.'::get_paymentchange_Url', 10, 2);
		
		// Version 2.0.0
		add_filter ( 'woocommerce_subscription_payment_meta', __CLASS__ . '::admin_change_subscription_paymentMethod', 10, 2 );
		
		// Version 2.0.0
		add_action ( 'woocommerce_subscription_validate_payment_meta', __CLASS__ . '::validate_subscription_payment_meta', 10, 2 );
		
		// Version 2.0.0
		add_action ( 'wcs_save_other_payment_meta', __CLASS__ . '::save_admin_payment_meta', 10, 4 );
		
		// Version 2.0.0
		// add_filter('woocommerce_subscription_date_to_display', __CLASS__.'::getFormattedNextPaymentDate', 10, 3);
		
		// Version 2.0.0
		add_filter ( 'woocommerce_subscription_get_next_payment_date', __CLASS__ . '::getNextPaymentDate', 10, 3 );
		
		// Version 2.00
		add_filter ( 'woocommerce_subscription_get_end_date', __CLASS__ . '::getEndDate', 10, 3 );
		
		add_filter ( 'woocommerce_subscription_calculated_end_of_prepaid_term_date', __CLASS__ . '::getEndDate', 10, 2 );
	}
	
	/**
	 * Process the subscription payment.
	 *
	 * @param WC_Order $order        	
	 * @return multitype:string
	 */
	public static function process_subscription(WC_Order $order, $user_id) {
		$params = null;
		if (WP_Manager ()->getRequestParameter ( 'payment_method_type' ) === 'token') {
			wc_add_notice ( __ ( 'You must use a saved payment method or check Save Card when purchasing a subscription.', 'worldpayus' ), 'error' );
			return false;
		}
		if (! $params = WorldpayUS_Payments::getPaymentMethodFromRequest ()) {
			wc_add_notice ( 'There was an issue processing your payment.', 'error' );
			WP_Manager ()->log->writeErrorToLog ( 'Method: process_subscription. The payment method could not be found in the request.' );
			return self::returnFailure ();
		}
		$transactionResponse = false;
		$subscriptions = wcs_get_subscriptions_for_order ( $order );
		if (self::getOrderTotal ( $order ) && ! self::signupFee_Processed ( $order )) {
			$params ['amount'] = $order->get_total ();
			if (! $transactionResponse = self::process_transaction ( $params, $order )) {
				wc_add_notice ( 'Your subscription could not be processed at this time.', 'error' );
				return self::returnFailure ();
			}
		}
		self::savePaymentMethodTitle ( $order );
		$primaryPaymentMethodId = $params ['paymentVaultToken'] ['paymentMethodId'];
		$customerId = $params ['paymentVaultToken'] ['customerId'];
		foreach ( $subscriptions as $subscription ) {
			$args = array (
					'customerId' => $customerId 
			);
			$plan = array (
					'active' => true,
					'primaryPaymentMethodId' => $primaryPaymentMethodId 
			);
			$plan ['notes'] = self::getSubscriptionNotes ( $subscription );
			$plan ['maxRetries'] = WP_Manager ()->get_option ( 'max_retries' );
			$args ['plan'] = $plan;
			
			if ($subscription->billing_period !== 'day') {
				$response = self::process_standardPaymentPlan ( $args, $subscription );
			} else {
				$response = self::process_variablePaymentPlan ( $args, $subscription );
			}
			if ($response->success) {
				$subscription->update_status ( WP_Manager ()->get_option ( 'subscription_status' ) );
				self::saveSubscriptionMeta ( $subscription, $response->paymentPlan );
				self::update_cart ( $subscription );
				WP_Manager ()->log->writeToLog ( sprintf ( __ ( 'A subscription was created for user %s. Plan Id %s', 'worldpayus' ), $user_id, $response->paymentPlan->planId ) );
				WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription Success: %s', print_r ( $response, true ) ) );
				//self::updateSubscriptionDates ( $subscription, $response->paymentPlan );
			} elseif (! $response->success) {
				wc_add_notice ( $response->message, 'error' );
				WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription Failure: %s', print_r ( $response, true ) ) );
				return self::returnFailure ();
			}
		}
		$order->update_status ( WP_Manager ()->get_option ( 'order_status' ) );
		WC ()->cart->empty_cart ();
		return array (
				'result' => 'success',
				'redirect' => $order->get_checkout_order_received_url () 
		);
	}
	private static function process_standardPaymentPlan($args, WC_Subscription $subscription) {
		$result = self::getSubscriptionInterval ( $subscription );
		$interval = $result ['interval'];
		$startDate = self::getSubscriptionStartDate ( $subscription );
		$endDate = self::getSubscriptionEndDate ( $subscription );
		if ($subscription->billing_period === 'day') {
			$args ['plan'] ['amount'] = ( int ) $subscription->get_total () * 7;
		} else
			$args ['plan'] ['amount'] = $subscription->get_total ();
		$args ['plan'] ['frequency'] = $interval;
		$args ['plan'] ['cycleType'] = self::$cycleTypes [$subscription->billing_period];
		$args ['plan'] ['endDate'] = $endDate;
		$args ['plan'] ['startDate'] = $startDate->format ( 'm/d/Y' );
		$args ['plan'] ['dayOfTheMonth'] = $startDate->format ( 'd' );
		if ($subscription->billing_period === 'day' || $subscription->billing_period === 'week') {
			$day = ( int ) $startDate->format ( 'N' );
			$args ['plan'] ['dayOfTheWeek'] = self::$numericDay [$day];
		}
		$args ['plan'] ['month'] = $startDate->format ( 'm' );
		try {
			return WP_Manager ()->worldpayUS->recurringPaymentPlan ( $args );
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription Failure: %s', print_r ( $e, true ) ) );
			wc_add_notice ( 'There was an error while processing your subscription.', 'error' );
			return false;
		}
	}
	
	/**
	 * Creates the variable payment plan.
	 *
	 * @param array $plan        	
	 * @param WC_Subscription $subscription        	
	 */
	private static function process_variablePaymentPlan(array $args, WC_Subscription $subscription) {
		$args ['plan'] = self::calculateVariableSubscriptionDates ( $args ['plan'], $subscription );
		try {
			return WP_Manager ()->worldpayUS->variablePaymentPlan ( $args );
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription Failure: %s', print_r ( $e, true ) ) );
			return false;
		}
	}
	private static function process_transaction($params, WC_Order $subscription) {
		try {
			$response = WP_Manager ()->worldpayUS->transaction ( $params );
			if ($response->success) {
				self::save_transactionMeta ( $response->transaction, $subscription, $params );
				// self::removeCartItem($subscription);
				return $response;
			} else {
				foreach ( $response->errors as $error ) {
					wc_add_notice ( $error->message, 'error' );
					WP_Manager ()->log->writeErrorToLog ( $error->message );
				}
				return false;
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( $e->getMessage () );
			return false;
		}
	}
	
	/**
	 * Calculate the subscription start date.
	 * Valid days of the month for the start date are 1-28. If the start date is greater than
	 * 28, the method will set the start date to the first of the next month.
	 *
	 * @param WC_Subscription $subscription        	
	 * @return
	 *
	 */
	private static function getSubscriptionStartDate(WC_Subscription $subscription) {
		if (! $timeStamp = $subscription->get_time ( 'trial_end' )) {
			$timeStamp = $subscription->get_time ( 'start' );
		}
		$startDate = new DateTime ();
		$startDate->setTimestamp ( $timeStamp );
		$subscriptionDate = new DateTime ();
		$subscriptionDate->setTimestamp ( time () );
		$subscriptionDate->add ( new DateInterval ( 'P1D' ) );
		// Add time to the startDate until it is greater than the minimum subscription start date.
		while ( $startDate->getTimestamp () < $subscriptionDate->getTimestamp () ) {
			$startDate->add ( new DateInterval ( 'PT1H' ) );
		}
		// Add time to the startDate if it is greater than 28. Worldpay requires that start date to be 1-28.
		while ( ( int ) $startDate->format ( 'd' ) > 28 ) {
			$startDate->add ( new DateInterval ( 'PT1H' ) );
		}
		return $startDate;
	}
	private static function getSubscriptionEndDate(WC_Subscription $subscription) {
		if ($endDate = $subscription->get_time ( 'end' )) {
			$date = new DateTime ();
			$date->setTimestamp ( $endDate );
			while ( ( int ) $date->format ( 'd' ) > 28 ) {
				$date->add ( new DateInterval ( 'P1D' ) );
			}
			return $date->format ( 'm/d/Y' );
		}
		return '';
	}
	
	/**
	 * Get the start date for the variable subscription.
	 *
	 * @param WC_Subscription $subscription        	
	 * @return DateTime
	 */
	private static function getVariableSubscriptionStartDate(WC_Subscription $subscription) {
		if (! $startDate = $subscription->get_time ( 'trial_end' )) {
			$startDate = $subscription->get_time ( 'start' );
		}
		$date = new DateTime ();
		$date->setTimestamp ( $startDate );
		$subscriptionTime = new DateTime ( null );
		$subscriptionTime->add ( new DateInterval ( 'P1D' ) );
		while ( $date->getTimestamp () < $subscriptionTime->getTimestamp () ) {
			$date->add ( new DateInterval ( 'PT1H' ) );
		}
		return $date;
	}
	
	/**
	 * Get the end date for the variable subscription.
	 *
	 * @param WC_Subscription $subscription        	
	 * @return DateTime
	 */
	private static function getVariableSubscriptionEndDate(WC_Subscription $subscription) {
		$start_date = new DateTime ( $subscription->get_date ( 'start' ) );
		$end_date = new DateTime ( $subscription->get_date ( 'end' ) );
		$difference = $end_date ? $end_date->diff ( $start_date )->days : 90;
		$start_date = self::getVariableSubscriptionStartDate ( $subscription );
		$start_date->add ( new DateInterval ( 'P' . $difference . 'D' ) );
		return $start_date;
	}
	private static function getDayOfTheMonth(DateTime $date) {
		$dayOfMonth = ( int ) $date->format ( 'd' );
		if ($dayOfMonth > 28) {
			$dayOfMonth = 1;
		}
		return $dayOfMonth;
	}
	private static function saveSubscriptionMeta(WC_Subscription $subscription, $paymentPlan, $paymentMethodTitle = false) {
		update_post_meta ( $subscription->id, '_payment_method_token', $paymentPlan->primaryPaymentMethodId );
		update_post_meta ( $subscription->id, '_subscription_plan_id', $paymentPlan->planId );
		update_post_meta ( $subscription->id, 'worldpayus_customer_id', $paymentPlan->customerId );
		if ($paymentPlan->isVariablePlan ()) {
			update_post_meta ( $subscription->id, '_payment_plan_type', 'variable' );
		} elseif ($paymentPlan->isRecurringPlan ()) {
			update_post_meta ( $subscription->id, '_payment_plan_type', 'recurring' );
		}
		if ($subscription->order->get_transaction_id ()) {
			update_post_meta ( $subscription->id, '_transaction_id', $subscription->order->get_transaction_id () );
		}
		if ($subscription->order->payment_method_title && ! $paymentMethodTitle) {
			update_post_meta ( $subscription->id, '_payment_method_title', $subscription->order->payment_method_title );
		} elseif ($paymentMethodTitle) {
			update_post_meta ( $subscription->id, '_payment_method_title', $paymentMethodTitle );
		}
	}
	private static function getSubscriptionInterval(WC_Subscription $subscription) {
		$items = $subscription->get_items ();
		foreach ( $items as $item => $data ) {
			$product = $subscription->get_product_from_item ( $data );
			$result ['interval'] = get_post_meta ( $product->id, '_subscription_period_interval', true );
			$result ['length'] = get_post_meta ( $product->id, '_subscription_length', true );
			return $result;
		}
	}
	
	/**
	 * Calculates the subscription payment dates for a variable subscription.
	 *
	 * @param unknown $plan        	
	 * @param WC_Subscription $subscription        	
	 * @return array
	 */
	private static function calculateVariableSubscriptionDates($plan, WC_Subscription $subscription) {
		$result = self::getSubscriptionInterval ( $subscription );
		$interval = $result ['interval'];
		$startDate = self::getVariableSubscriptionStartDate ( $subscription );
		$endDate = self::getVariableSubscriptionEndDate ( $subscription );
		$length = $startDate->diff ( $endDate, true )->format ( '%a' );
		$date = $startDate;
		$plan ['planEndDate'] = $endDate->format ( 'm/d/Y' );
		$plan ['planStartDate'] = $startDate->format ( 'm/d/Y' );
		for($i = 0; $i < $length; $i ++) {
			$plan ['scheduledPayments'] [] = array (
					'amount' => $subscription->get_total (),
					'scheduledDate' => $date->format ( 'm/d/Y' ),
					'paymentDate' => $date->format ( 'm/d/Y' ),
					'numberOfRetries' => WP_Manager ()->get_option ( 'max_retries' ),
					'paid' => false,
					'processed' => false 
			);
			switch ($subscription->billing_period) {
				
				case 'day' :
					$date->add ( new DateInterval ( 'P' . $result ['interval'] . 'D' ) );
					break;
				case 'week' :
					$date->add ( new DateInterval ( 'P' . $result ['interval'] . 'W' ) );
					break;
				case 'month' :
					$date->add ( new DateInterval ( 'P' . $result ['interval'] . 'M' ) );
					break;
				case 'year' :
					$date->add ( new DateInterval ( 'P' . $result ['interval'] . 'Y' ) );
					break;
			}
		}
		return $plan;
	}
	public static function cancel_subscription(WC_Subscription $subscription) {
		if (WorldpayUS_Payments::is_paymentChangeRequest ()) {
			return false;
		}
		$planId = self::getPlanId ( $subscription );
		if (empty ( $planId )) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'There is no payment plan Id saved for
						subscription %s', $subscription->id ) );
			wc_add_notice ( sprintf ( 'There is no payment plan Id saved for
						subscription %s', $subscription->id ), 'error' );
			return false;
		}
		$params = array (
				'customerId' => self::getCustomerId ( $subscription ),
				'planId' => self::getPlanId ( $subscription ) 
		);
		try {
			$response = WP_Manager ()->worldpayUS->deletePaymentPlan ( $params );
			if ($response->success) {
				$subscription->update_status ( 'wc-cancelled' );
				$subscription->add_order_note ( __ ( 'The subscription has been cancelled.', 'worldpayus' ) );
				WP_Manager ()->log->writeToLog ( sprintf ( 'Subscription %s was cancelled for customerId %s.', $subscription->id, $params ['customerId'] ) );
				return true;
			} else {
				WP_Manager ()->log->writeToLog ( sprintf ( 'There was an error while cancelling subscription %s. Message: %s', $subscription->id, $response->errors [0]->message ) );
				wc_add_notice ( sprintf ( __ ( 'There was an error while cancelling subscription %s. Reason: %s', 'worldpayus' ), $subscription->id, $response->errors [0]->message ), 'error' );
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Method: cancel_subscription(). Message: %s', $e->getMessage () ) );
			wc_add_notice ( sprintf ( __ ( 'Your subscription could not be cancelled at this time. Please email support. Reason: %s', 'worldpayus' ), $e->getMessage () ), 'error' );
		}
	}
	
	/**
	 * Updates the payment method assigned to the subscription object.
	 * First, a check is made to see if there is a plan Id.
	 * If there is no plan Id then the subscription was created using another payment gateway. Because tokenization is used
	 * in all credit card transactions, you cannot simply change the payment method of the existing Worldpay subscription. A new
	 * subscription will have to be created using the new payment method.
	 *
	 * @param WC_Subscription $subscription        	
	 * @param string $old_payment_method        	
	 * @return boolean
	 */
	public static function update_subscription_paymentMethod(WC_Subscription $subscription, $old_payment_method) {
		if (! $params = self::getPaymentMethodFromRequest ()) {
			wc_add_notice ( __ ( 'There was not a valid payment method in the request. Please try again.', 'worldpayus' ), 'error' );
			return false;
		}
		if (! self::isWorldpaySubscription ( $subscription )) {
			if (! WP_Manager ()->isActive ( 'subsciptions_allow_new' )) {
				wc_add_notice ( __ ( 'Your subscription was created using a different payment gateway. The selected payment gateway does not allow for a 
						payment method change when the subscription was not created using this gateway', 'worldpayus' ), 'error' );
				return false;
			}
			// Remove the maybe_zero_total function so that the $subscription->get_total() method does not return zero for
			// a payment method change.
			remove_filter ( 'woocommerce_order_amount_total', 'WC_Subscriptions_Change_Payment_Gateway::maybe_zero_total', 11 );
			$args = array (
					'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
			);
			$plan = array (
					'active' => true,
					'primaryPaymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ) 
			);
			$plan ['notes'] = self::getSubscriptionNotes ( $subscription );
			$plan ['maxRetries'] = WP_Manager ()->get_option ( 'max_retries' );
			$args ['plan'] = $plan;
			if ($subscription->billing_period !== 'day') {
				$response = self::process_standardPaymentPlan ( $args, $subscription );
			} else {
				$response = self::process_variablePaymentPlan ( $args, $subscription );
			}
			if ($response->success) {
				wc_add_notice ( __ ( 'The payment method for your subscription was updated', 'worldpayus' ), 'success' );
				self::saveSubscriptionMeta ( $subscription, $response->paymentPlan );
				self::savePaymentMethodTitle ( $subscription );
				$subscription->add_order_note ( sprintf ( __ ( 'Payment method updated to %s', 'worldpayus' ), $paymentTitle ) );
			} else {
				BT_Manager ()->log->writeToLog ( sprintf ( 'Subscription Failed: %s', print_r ( $response, true ) ) );
				wc_add_notice ( $response->message, 'error' );
			}
		} else {
			try {
				$worldpaySubscription = WP_Manager ()->worldpayUS->getPaymentPlan ( array (
						'planId' => self::getPlanId ( $subscription ),
						'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
				) );
				$args = array (
						'planId' => self::getPlanId ( $subscription ),
						'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ),
						'plan' => array (
								'primaryPaymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ) 
						) 
				);
				if (self::get_paymentPlanType ( $subscription ) === 'recurring') {
					$args ['plan'] ['dayOfTheMonth'] = $worldpaySubscription->paymentPlan->dayOfTheMonth;
					$response = WP_Manager ()->worldpayUS->updateRecurringPlan ( $args );
				} else {
					$response = WP_Manager ()->worldpayUS->updateVariablePlan ( $args );
				}
				if ($response->success) {
					wc_add_notice ( __ ( 'The payment method was updated successfully.', 'worldpayus' ), 'success' );
					try {
						$response = WP_Manager ()->worldpayUS->getPaymentMethod ( array (
								'paymentMethodId' => WP_Manager ()->getRequestParameter ( 'payment_method_id' ),
								'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
						) );
						$paymentMethod = $response->paymentMethod;
						update_post_meta ( $subscription->id, '_payment_method_title', $paymentMethod->paymentMethodType . ' - ' . $paymentMethod->maskedNumber );
					} catch ( WorldpayUSException $e ) {
						WP_Manager ()->log->writeErrorToLog ( 'Method: update_subscription_paymentMethod.' . $e->getMessage () );
					}
				} else {
					foreach ( $response->errors as $error ) {
						WP_Manager ()->log->writeErrorToLog ( $error->message );
						wc_add_notice ( $error->message, 'error' );
					}
				}
			} catch ( WorldpayUSException $e ) {
				wc_add_notice ( sprintf ( __ ( 'There was an error while updating your subscription. Message: %s', 'worldpayus' ), $e->getMessage () ), 'error' );
			}
		}
	}
	
	/**
	 * Function that fetches the subscription payment method and displays it on the customer's my account page.
	 *
	 * @param string $payment_method        	
	 * @param WC_Subscription $subscription        	
	 */
	public static function subscription_payment_method($payment_method, WC_Subscription $subscription) {
		$paymentMethod = get_post_meta ( $subscription->id, '_payment_method_title', true );
		if (! empty ( $payment_method )) {
			$payment_method = $paymentMethod;
		}
		return $payment_method;
	}
	public static function cancelSubscriptionForOldPaymentMethod(WC_Order $subscription, $new_payment_method) {
		if ($new_payment_method === self::ID) {
			return;
		}
		try {
			$response = WP_Manager ()->worldpayUS->deletePaymentPlan ( array (
					'customerId' => self::getCustomerId ( $subscription ),
					'planId' => self::getPlanId ( $subscription ) 
			) );
			if ($response->success) {
				$subscription->add_order_note ( sprintf ( __ ( 'The recurring plan within Worldpay has been cancelled due to a 
						payment method change. The payment method was changed to %s' ), $new_payment_method ) );
			} else {
				$subscription->add_order_note ( sprintf ( __ ( 'The subscription within Worldpay was not cancelled successfully. 
						You will need to delete it manually. PlanId: %s', 'worldpayus' ), self::getPlanId ( $subscription ) ) );
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( 'Method: cancelSubscriptionForOldPaymentMethod' );
		}
	}
	private static function getPlanId($subscription) {
		return get_post_meta ( $subscription->id, '_subscription_plan_id', true );
	}
	private static function getCustomerId($subscription) {
		return get_post_meta ( $subscription->id, 'worldpayus_customer_id', true );
	}
	private static function returnFailure() {
		return array (
				'result' => 'failure',
				'redirect' => '' 
		);
	}
	private static function signupFee_Processed($order) {
		return get_post_meta ( $order->id, '_transaction_processed', true ) === 'yes';
	}
	private static function update_cart($subscription) {
		$cart = WC ()->cart->get_cart ();
		
		foreach ( $cart as $cart_key => $item ) {
			$product_id = $item ['product_id'];
			foreach ( $subscription->get_items () as $item ) {
				if ($item ['product_id'] == $product_id) {
					WC ()->cart->remove_cart_item ( $cart_key );
				}
			}
		}
	}
	
	/**
	 * Removes all product items from the cart that are not subscriptions.
	 *
	 * @param WC_Order $order        	
	 */
	private static function removeCartItem($order) {
		$cart = WC ()->cart->get_cart ();
		
		foreach ( $cart as $cart_key => $item ) {
			$product_id = $item ['product_id'];
			if (! WC_Subscriptions_Product::is_subscription ( $product_id )) {
				WC ()->cart->remove_cart_item ( $cart_key );
			}
		}
	}
	
	/**
	 * Return the nextPaymentDate of the subscription in mysql format.
	 *
	 * @param string $date        	
	 * @param WC_Order $subscription        	
	 * @param string $timezone        	
	 */
	public static function getNextPaymentDate($date, WC_Order $subscription, $timezone = null) {
		if (wps_is_worldpay_subscription ( $subscription->id )) {
			$paymentPlan = WP_Manager ()->getWorldpaySubscription ( array (
					'customerId' => self::getCustomerId ( $subscription ),
					'planId' => self::getPlanId ( $subscription ) 
			) );
			if ($paymentPlan instanceof PaymentPlan) {
				$date = $paymentPlan->nextPaymentDate;
				if ($index = strpos ( $date, 'T' )) {
					$date = str_replace ( 'T', ' ', $date );
				}
			}
		}
		return $date;
	}
	public static function getEndDate($date, WC_Order $subscription, $timezone = null) {
		if (wps_is_worldpay_subscription ( $subscription->id )) {
			$paymentPlan = WP_Manager ()->getWorldpaySubscription ( array (
					'customerId' => self::getCustomerId ( $subscription ),
					'planId' => self::getPlanId ( $subscription ) 
			) );
			if ($paymentPlan instanceof PaymentPlan) {
				$date = $paymentPlan->endDate;
				if (! $date) { // If date is null for variable subscription, use the last scheduledPayment date.
					$size = count ( $paymentPlan->scheduledPayments );
					$date = $paymentPlan->scheduledPayments [$size - 1]->scheduledDate;
				}
				if ($index = strpos ( $date, 'T' )) {
					$date = str_replace ( 'T', ' ', $date );
				}
			}
		}
		return $date;
	}
	
	/**
	 *
	 * @param unknown $date_to_display        	
	 * @param unknown $date_type        	
	 * @param WC_Order $subscription        	
	 */
	public static function getFormattedNextPaymentDate($date_to_display, $date_type, WC_Order $subscription) {
		if (wps_is_worldpay_subscription ( $subscription->id )) {
			$date_to_display = self::getNextPaymentDate ( $date_to_display, $subscription, null );
		}
		return $date_to_display;
	}
	/**
	 * Calculate the order total, subtracting the subscription price since the subscription must start
	 * 24 hrs after the current time.
	 * This is a requirement of Worldpay.
	 *
	 * @param WC_Order $order        	
	 * @return int amount
	 */
	private static function getOrderTotal(WC_Order $order) {
		$amount = $order->get_total ();
		$subscriptions = wcs_get_subscriptions_for_order ( $order );
		foreach ( $subscriptions as $subscription ) {
			$amount = $amount - $subscription->get_total ();
		}
		return $amount;
	}
	private static function get_paymentPlanType($subscription) {
		return get_post_meta ( $subscription->id, '_payment_plan_type', true );
	}
	public static function isWorldpaySubscription(WC_Order $subcription) {
		$type = get_post_meta ( $subcription->id, '_payment_plan_type', true );
		return $type === 'variable' || $type === 'recurring';
	}
	public static function getSubscriptionNotes(WC_Subscription $subscription) {
		$notes = WP_Manager ()->get_option ( 'subscription_notes' );
		if (empty ( $notes )) {
			$notes = sprintf ( __ ( 'Subscription ID: %s. Subscription Cost: %s%s. Shipping Cost: %s%s', 'worldpayus' ), $subscription->id, $subscription->order_currency, $subscription->get_total (), $subscription->order_currency, $subscription->get_total_shipping () );
		}
		return $notes;
	}
	public static function updateSubscriptionDates(WC_Subscription $subscription, PaymentPlan $plan) {
		$start = new DateTime ( $plan->startDate );
		$end = new DateTime ( $plan->endDate );
		$next = new DateTime ( $plan->nextPaymentDate );
		$dates = array (
				'start' => $start->format ( 'Y-m-d H:i:s' ),
				'next_payment' => $next->format ( 'Y-m-d H:i:s' ),
				'end' => $end->format ( 'Y-m-d H:i:s' ),
		);
		$subscription->update_dates ( $dates );
	}
}
WCS_WorldpayUS_Subscriptions::init ();