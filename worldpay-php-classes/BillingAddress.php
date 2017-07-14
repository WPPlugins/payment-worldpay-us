<?php
namespace Worldpay;
/**
 * 
 * @author Payment Plugins - https://paymentplugins.com
 * @copyright PaymentPlugins, 2015.
 *
 */
class BillingAddress extends Base{
	
	protected function _initialize(array $attributes){
		$this->_attributes = $attributes;
		
		foreach($attributes as $attribute => $value){
			$this->_set($attribute, $value);
		}
	}
}