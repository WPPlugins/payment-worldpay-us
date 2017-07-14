<?php
namespace Worldpay;
/**
 * 
 * @author Payment Plugins - https://paymentplugins.com
 * @copyright Payment Plugins 2015
 *
 */
class Customer extends Base {
	
	protected function _initialize(array $attributes){
		$this->_attributes = $attributes;
		if(isset($attributes['token']['customerId'])){
			$this->_set('id', $attributes['token']['customerId']);
		}
		if(isset($attributes['customerId'])){
			$this->_set('id', $attributes['customerId']);
		}
		if(isset($attributes['paymentMethods'])){
			$paymentMethods = array();
			foreach($attributes['paymentMethods'] as $index => $paymentMethod){
				$paymentMethods[] = PaymentMethod::factory($paymentMethod);
			}
			$this->_set('paymentMethods', $paymentMethods);
		}
	}
	
}
?>