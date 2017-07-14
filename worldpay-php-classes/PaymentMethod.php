<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 *
 */
class PaymentMethod extends Base {
	
	protected function _initialize(array $attributes){
		$this->_attributes = array();
		$paymentMethod = null;
		if(isset($attributes['paymentTypeResult'])){
			if($attributes['paymentTypeResult'] === 'CREDIT_CARD'){
				return CreditCard::factory($attributes);
			}
			if($attributes['paymentTypeResult'] === 'CHECK'){
				return BankAccount::factory($attributes);
			}
		}
		if(isset($attributes['vaultPaymentMethod'])){
			if($attributes['vaultPaymentMethod']['method'] === 'CC'){
				return CreditCard::factory($attributes['vaultPaymentMethod']);
			}
		}
		if(isset($attributes['method'])){
			if($attributes['method'] === 'CC'){
				return CreditCard::factory($attributes);
			}
			elseif ($attributes['method'] === 'ECHECK'){
				return BankAccount::factory($attributes);
			}
		}
	}
	
	public function isCard(){
		return $this->paymentType === 'CREDIT_CARD';
	}
	
	public function isPrimary(){
		return $this->primary == true;
	}
	
	public function isBank(){
		return $this->paymentType === 'ECHECK';
	}
	
	public static function factory(array $attributes){
		$instance = new static();
		$instance = $instance->_initialize($attributes);
		
		return $instance;
	}
	
}
?>