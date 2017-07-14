<?php
class WCS_WP_Meta_Box{
	
	public static function init(){
		if( !WP_Manager()->woocommerceSubscriptionsActive()){
			if(WP_Manager()->isActive('worldpayus_subscriptions')){
				add_action('woocommerce_product_options_general_product_data', __CLASS__.'::doSubscriptionOutput');
			}
		}
	}
	
	/**
	 * Method that generates the html necessary for the product page visible to the admin.
	 */
	public static function doSubscriptionOutput(){
		echo '<div class="options_group worldpay_plans_options">';
		woocommerce_wp_checkbox(array(
				'label'=>__('Sell As Subscription', 'worldpayus'),
				'name'=>'worldpayus_subscription',
				'id'=>'worldpayus_subscription',
				'cbvalue'=>'yes',
				'desc_tip'=>true,
				'description'=>__('If you want this product to be sold as a subsription, then click the checkbox and select 
						the planId and configure the necessary fields.', 'braintree')
		));
		woocommerce_wp_text_input(array(
				'name'=>'_subscription_price',
				'id'=>'_subscription_price',
				'placeholder'=>__('Price', 'worldpayus'),
				'type'=>'text',
				'label'=>__('Subscription Price', 'worldpayus'),
				'desc_tip'=>true,
				'description'=>__('The subscription price determines the price that is charged for the subscription.', 'worldpayus')
		));
		woocommerce_wp_select(array(
				'name'=>'_subscription_period_interval',
				'id'=>'_subscription_period_interval',
				'class'=>'form-field',
				'label'=>__('Subscription Interval', 'braintree'),
				'options'=>WP_Manager()->getSubscriptionPeriodIntervals(),
				'description'=>__('This option allows you to select the interval that the subscription is to be charged on.', 'braintree'),
				'desc_tip'=>true
		));
		woocommerce_wp_select(array(
				'name'=>'_subscription_period',
				'id'=>'_subscription_period',
				'class'=>'form-field',
				'label'=>__('Subscription Period', 'braintree'),
				'options'=>WP_Manager()->getSubscriptionPeriods(),
				'description'=>__('This option allows you to select the interval that the subscription is to be charged on.', 'braintree'),
				'desc_tip'=>true
		));
		woocommerce_wp_select(array(
				'name'=>'_subscription_length',
				'id'=>'_subscription_length',
				'class'=>'form-field',
				'label'=>__('Subscription Length', 'worldpayus'),
				'options'=>WP_Manager()->getSubscriptionLengths(),
				'description'=>__('The subscription length is how long the subscription will bill.', 'worldpayus'),
				'desc_tip'=>true
		));
		
		echo '</div>';
		//echo '<input name="subscription_period_json" type="hidden" value="'.wps_get_subscription_period_json().'"/>';
		echo '<script>var subscription_period_json = '.wps_get_subscription_period_json().'
				var subscription_intervals_json = '.wps_get_subscription_intervals_json().'
			  </script>';
	}
}
WCS_WP_Meta_Box::init();