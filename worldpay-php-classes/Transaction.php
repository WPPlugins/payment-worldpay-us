<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers - mr.clayton@paymentplugins.com
 *
 * @property-read PaymentMethod $paymentMethod
 */
class Transaction extends Base {
	
	
	public function _initialize(array $attributes){
		
		$this->_attributes = $attributes;
		
		$this->_set('paymentMethod', PaymentMethod::factory($attributes));
		
		if(isset($attributes['vaultData'])){
			$this->_set('customer', Customer::factory($attributes['vaultData']));
		}
		
		foreach($attributes as $attribute => $value){
			$this->_set($attribute, $value);
		}
	}

}
?>