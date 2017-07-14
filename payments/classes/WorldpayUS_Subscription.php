<?php
/**
 * 
 * @author Clayton Rogers
 * @property	string $billing_period
 * @property	string $billing_interval
 * @property	string $subscription_type
 * @property	string $payment_processed
 * @property	int $product_id
 * @property	WC_Order $order
 * @property	WorldpayUS_Subscription_Product $product
 *
 */
class WorldpayUS_Subscription extends WC_order {
	public $_attributes = array ();
	public function __construct($post_id) {
		$this->id = $post_id;
		$attributes = get_post_meta ( $post_id );
		$this->populateAttributes ( $attributes );
		$this->_set ( 'id', $post_id );
		
		$post_parent = get_post ( $post_id )->post_parent;
		
		$this->order = wc_get_order ( $post_parent );
		
		$this->product = new WorldpayUS_Subscription_Product ( absint ( $this->product_id ) );
	}
	public function _set($key, $value) {
		$this->_attributes [$key] = $value;
	}
	public function __get($key) {
		if (array_key_exists ( $key, $this->_attributes )) {
			return $this->_attributes [$key];
		} else {
			return get_post_meta ( $this->id, '_' . $key, true );
		}
	}
	private function populateAttributes($attributes = array()) {
		foreach ( $attributes as $attribute => $value ) {
			if (substr ( $attribute, 0, 1 ) === '_') {
				$attribute = substr ( $attribute, 1 );
			}
			$this->_set ( $attribute, $value [0] );
		}
	}
	
	/**
	 * Update the post_meta value for the given key.
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 */
	public function updateAttribute($key, $value) {
		update_post_meta ( $this->id, '_' . $key, $value );
	}
	
	/**
	 * Update the post_meta for the given attributes array.
	 *
	 * @param array $attributes        	
	 */
	public function updateAttributes($attributes = array()) {
		foreach ( $attributes as $key => $value ) {
			$this->updateAttribute ( $key, $value );
		}
	}
	public function isRecurring() {
		return $this->product->subscription_type === 'recurring';
	}
	public function isVariable() {
		return $this->product->subscription_type === 'variable';
	}
	
	/**
	 * Update the subscription attributes using the Order.
	 *
	 * @param WC_Order $order        	
	 */
	public function updateOrderAttributes(WC_Order $order) {
		$order_attributes = array (
				'price_include_tax',
				'created_via',
				'shipping_first_name',
				'shipping_last_name',
				'shipping_company',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_city',
				'shipping_state',
				'shipping_postcode',
				'shipping_country',
				'billing_first_name',
				'billing_last_name',
				'billing_company',
				'billing_address_1',
				'billing_address_2',
				'billing_city',
				'billing_state',
				'billing_postcode',
				'billing_country',
				'billing_email',
				'billing_phone' 
		);
		
		foreach ( $order_attributes as $attribute ) {
			$this->updateAttribute ( $attribute, $order->{$attribute} );
		}
	}
	
	/**
	 * Return the subscription price for the product.
	 */
	public function getSubscriptionPrice() {
		return $this->product->subscription_price + $this->getShippingPrice ();
	}
	public function getShippingPrice() {
		$price = 0;
		$items = $this->order->get_items ( 'shipping' );
		foreach ( $items as $item ) {
			$price = $item ['cost'];
			break;
		}
		return $price;
	}
	public function getSubscriptionFrequency() {
		return $this->product->subscription_period_interval;
	}
	public function getSubscriptionPeriod() {
		return $this->product->subscription_period;
	}
	public function getStartDate() {
		return $this->product->getStartDate ();
	}
	public function getEndDate() {
		return $this->product->getEndDate ();
	}
	
	/**
	 * Return true if the payment for this subscription has been processed.
	 */
	public function paymentProcessed() {
		return $this->payment_processed === 'yes';
	}
}