<?php

/**
 * Returns an array of WorldpayUS_Subscription_Product objects associated with the shopping cart.
 * @return WorldpayUS_Subscription_Product[]
 */
function wps_get_subscription_products_from_cart() {
	$products = array ();
	foreach ( WC ()->cart->get_cart () as $cart => $values ) {
		$_product = $values ['data'];
		if (wps_is_product_subscription ( $_product->id )) {
			$products [] = new WorldpayUS_Subscription_Product ( $_product->id );
		}
	}
	return $products;
}

/**
 * Return true of the post_id is a worldpay subscription product.
 * @param unknown $post_id
 * @return boolean
 */
function wps_is_product_subscription($post_id) {
	return get_post_meta ( $post_id, 'worldpayus_subscription', true ) === 'yes';
}

/**
 * Returns true if the shopping cart contains a Worldpay Subscription product.
 * 
 * @return boolean
 */
function wps_cart_contains_subscription() {
	$subscriptions = wps_get_subscription_products_from_cart ();
	return ! empty ( $subscriptions );
}

/**
 * Returns an array of WorldpayUS_Subscription objects associated with the order.
 * 
 * @param int $post_id        	
 * @return WorldpayUS_Subscription[]
 */
function wps_get_subscriptions_from_order($order_id) {
	global $wpdb;
	$subscriptions = array ();
	$posts = get_posts ( array (
			'posts_per_page' => - 1,
			'post_parent' => $order_id,
			'post_type' => 'shop_subscription',
			'post_status' => 'any' 
	) );
	foreach ( $posts as $post ) {
		$subscriptions [] = new WorldpayUS_Subscription ( $post->ID );
	}
	return $subscriptions;
}

/**
 * Returns an array of subsriptino length options based on the $period.
 * 
 * @param string $period        	
 * @return string[]|NULL[]
 */
function wps_get_subscription_lengths($period = 'month', $interval = 1) {
	$array = array (
			'day',
			'week',
			'month',
			'year' 
	);
	$period_options = array ();
	switch ($period) {
		case 'day' :
			$range = range ( 2, 365 );
			if (1 % $interval === 0) {
				$period_options [1] = __ ( '1 Day', 'worldpayus' );
			}
			break;
		case 'week' :
			$range = range ( 2, 52 );
			if (1 % $interval === 0) {
				$period_options [1] = __ ( '1 Week', 'worldpayus' );
			}
			break;
		case 'month' :
			$range = range ( 2, 24 );
			if (1 % $interval === 0) {
				$period_options [1] = __ ( '1 Month', 'worldpayus' );
			}
			break;
		case 'year' :
			$range = range ( 2, 6 );
			if (1 % $interval === 0) {
				$period_options [1] = __ ( '1 Year', 'worldpayus' );
			}
			break;
	}
	
	foreach ( $range as $number ) {
		if ($number % $interval === 0) {
			$period_options [$number] = wps_get_subscription_period_string ( $period, $number );
		}
	}
	return $period_options;
}
function wps_get_subscription_interval($period = 'month') {
	$array = array (
			'day' => array (
					'1' => __ ( 'every', 'worldpay' ),
					'2' => __ ( 'every 2nd', 'worldpayus' ),
					'3' => __ ( 'every 3rd', 'worldpayus' ),
					'4' => __ ( 'every 4th', 'worldpayus' ),
					'5' => __ ( 'every 5th', 'worldpayus' ),
					'6' => __ ( 'every 6th', 'worldpayus' ) 
			),
			'week' => array (
					'1' => __ ( 'every', 'worldpay' ),
					'2' => __ ( 'every 2nd', 'worldpayus' ),
					'3' => __ ( 'every 3rd', 'worldpayus' ),
					'4' => __ ( 'every 4th', 'worldpayus' ) 
			),
			'month' => array (
					'1' => __ ( 'every', 'worldpay' ),
					'2' => __ ( 'every 2nd', 'worldpayus' ),
					'3' => __ ( 'every 3rd', 'worldpayus' ),
					'4' => __ ( 'every 4th', 'worldpayus' ),
					'5' => __ ( 'every 5th', 'worldpayus' ),
					'6' => __ ( 'every 6th', 'worldpayus' ),
					'7' => __ ( 'every 7th', 'worldpayus' ),
					'8' => __ ( 'every 8th', 'worldpayus' ),
					'9' => __ ( 'every 9th', 'worldpayus' ),
					'10' => __ ( 'every 10th', 'worldpayus' ),
					'11' => __ ( 'every 11th', 'worldpayus' ) 
			),
			'year' => array (
					'1' => __ ( 'every', 'worldpayus' ),
					'2' => __ ( 'every 2nd', 'worldpayus' ),
					'3' => __ ( 'every 3rd', 'worldpayus' ),
					'4' => __ ( 'every 4th', 'worldpayus' ),
					'5' => __ ( 'every 5th', 'worldpayus' ),
					'6' => __ ( 'every 6th', 'worldpayus' ) 
			) 
	);
	return $array [$period];
}

/**
 * Return a formatted string of the interval.
 *
 * @param string $period        	
 * @param int $number        	
 * @return string
 */
function wps_get_subscription_period_string($period, $number) {
	$strings = array (
			'day' => sprintf ( __ ( '%s days', 'worldpayus' ), $number ),
			'week' => sprintf ( __ ( '%s weeks', 'worldpayus' ), $number ),
			'month' => sprintf ( __ ( '%s months', 'worldpayus' ), $number ),
			'year' => sprintf ( __ ( '%s years', 'worldpayus' ), $number ) 
	);
	return $strings [$period];
}

/**
 * Return a json encoded array consisting of the subscription periods.
 */
function wps_get_subscription_period_json() {
	$array = array (
			'day' => wps_get_subscription_lengths ( 'day' ),
			'week' => wps_get_subscription_lengths ( 'week' ),
			'month' => wps_get_subscription_lengths ( 'month' ),
			'year' => wps_get_subscription_lengths ( 'year' ) 
	);
	return json_encode ( $array );
}
function wps_get_subscription_intervals_json() {
	$array = array (
			'day' => wps_get_subscription_interval ( 'day' ),
			'week' => wps_get_subscription_interval ( 'week' ),
			'month' => wps_get_subscription_interval ( 'month' ),
			'year' => wps_get_subscription_interval ( 'year' ) 
	);
	return json_encode ( $array );
}

/**
 * Return the character representation of the period provided.
 * <strong>day</strong>: D
 * <strong>week</strong>: W
 * <strong>month</strong>: M
 * <strong>year</strong>: Y
 * 
 * @param string $period        	
 * @return string
 */
function wps_get_date_time_interval($period = 'day') {
	switch ($period) {
		case 'day' :
			$interval = 'D';
			break;
		case 'week' :
			$interval = 'W';
			break;
		case 'month' :
			$interval = 'M';
			break;
		case 'year' :
			$interval = 'Y';
			break;
	}
	return $interval;
}

/**
 * Returns true if the order contains subscription items.
 * 
 * @param int $order_id        	
 * @return boolean
 */
function wps_order_contains_subscription($order_id) {
	$subscriptions = wps_get_subscriptions_from_order ( $order_id );
	return ! empty ( $subscriptions );
}

/**
 * Create the subscription order and save the post to the database.
 *
 * @param unknown $order_id        	
 * @return WP_Error|array
 */
function wps_create_subscriptions($order_id = null) {
	if (! $order_id) {
		return new WP_Error ( 'invalid_order_id', __ ( 'The provided order_id is invalid', 'worldpayus' ) );
	}
	$subscription_data = array ();
	
	$order = wc_get_order ( $order_id );
	$items = $order->get_items ();
	
	$subscriptionProducts = array ();
	
	foreach ( $items as $item ) {
		$product_id = $item ['product_id'];
		if (wps_is_product_subscription ( $product_id )) {
			for($i = 0; $i < $item ['qty']; $i ++) {
				$subscriptionProducts [] = new WorldpayUS_Subscription_Product ( $product_id );
			}
		}
	}
	
	if (empty ( $subscriptionProducts )) {
		return new WP_Error ( 'no_subscriptions', __ ( 'There are not subscriptions in the cart', 'worldpayus' ) );
	}
	
	$args = array (
			'_customer_user' => $order->get_user_id (),
			'_order_version' => $order->order_version,
			'_order_key' => 'wc_' . uniqid ( 'order_' ),
			'_order_currency' => $order->get_order_currency (),
			'_created_via' => $order->created_via 
	)
	;
	
	$subscriptions = array ();
	
	foreach ( $subscriptionProducts as $subscriptionProduct ) {
		$subscription_data ['post_parent'] = $order->id;
		$subscription_data ['post_type'] = 'shop_subscription';
		$subscription_data ['post_status'] = 'wc-pending';
		$subscription_data ['post_author'] = 1;
		
		$subscription_id = wp_insert_post ( $subscription_data, true ); // Insert the new post into the posts database.
		
		if (is_wp_error ( $subscription_id )) {
			return $subscription_id;
		}
		
		$subscriptions [] = $subscription_id;
		
		update_post_meta ( $subscription_id, '_billing_period', $subscriptionProduct->subscription_period );
		update_post_meta ( $subscription_id, '_billing_interval', $subscriptionProduct->subscription_period_interval );
		update_post_meta ( $subscription_id, '_billing_length', $subscriptionProduct->subscription_length );
		update_post_meta ( $subscription_id, '_product_id', $subscriptionProduct->id );
		update_post_meta ( $subscription_id, '_requires_manual_renewal', 'false' );
		update_post_meta ( $subscription_id, '_order_total', $subscriptionProduct->subscription_price );
		update_post_meta ( $subscription_id, 'worldpayus_subscription', 'yes' );
		
		foreach ( $args as $arg => $value ) {
			update_post_meta ( $subscription_id, $arg, $value );
		}
		
		$subscription = new WorldpayUS_Subscription ( $subscription_id );
		
		$subscription->updateOrderAttributes ( $order );
		
		$order_item_id = wc_add_order_item ( $subscription->id, array (
				'order_item_name' => $subscriptionProduct->post_title,
				'order_item_type' => 'line_item' 
		) );
		
		$meta_keys = array (
				'qty' => 1,
				'tax_class' => $subscriptionProduct->tax_class,
				'product_id' => $subscriptionProduct->id,
				'variation_id' => $subscriptionProduct->variation_id,
				'line_subtotal' => $subscriptionProduct->subscription_price,
				'line_subtotal_tax' => $subscriptionProduct->line_subtotal_tax,
				'line_tax' => $subscriptionProduct->line_tax,
				'line_tax_data' => $subscriptionProduct->line_tax_data 
		);
		
		foreach ( $meta_keys as $meta_key => $meta_value ) {
			wc_add_order_item_meta ( $order_item_id, '_' . $meta_key, $meta_value );
		}
	}
	
	return $subscriptions;
}
function wps_subscriptions_already_created($order_id) {
	$subscriptions = wps_get_subscriptions_from_order ( $order_id );
	return ! empty ( $subscriptions );
}
function wps_is_worldpay_subscription($post_id) {
	return get_post_meta ( $post_id, 'worldpayus_subscription', true ) === 'yes';
}
?>