<?php

namespace Worldpay;

/**
 * 
 * @author Clayton Rogers
 * @copyright Payment Plugins 2015
 *
 */
class BankAccount extends Base {
	
	private $bankFields = array(
			'paymentId', 'paymentTypeCode', 'customerId', 'check', 'type', 'paymentType', 'vaultData', 'firstName', 'lastName', 'email', 'phone', 'cardNumber',
			'cardHolder_FirstName', 'cardHolder_LastName', 'billAddress', 'accountName', 'accountNumber', 
			'method','address', 'bankName', 'notes', 'userDefinedFields'
	);
	public function _initialize(array $attributes){
			$this->_attributes = $attributes;
			foreach($this->bankFields as $key){
				if(isset($attributes[$key])){
					$this->initializeBankData($key, $attributes[$key]);
				}
			}
			
			$this->_set('paymentType', 'CHECK');
			$this->_set('paymentMethodType', $this->accountType);
			if(isset($attributes['check']['address'])){
				$this->_set('billAddress', BillingAddress::factory($attributes['check']['address']));
			}
			if(isset($attributes['cardNumber'])){
				$this->_set('maskedNumber', $attributes['cardNumber']);
			}
			else {
				$this->_set('maskedNumber', 'XXXXXX'. $this->lastFourDigits);
			}
	}
	
	private function initializeBankData($key = '', $value = ''){
	
		if(is_array($value)){
			foreach($value as $k => $v){
				$this->initializeBankData($k, $v);
			}
		}
		else {
			$this->_set($key, $value);
			if($key === 'paymentId'){
				$this->_set('token', $value);
			}
		}
	}
	
	public static function factory(array $attributes){
		$instance = new self();
		$instance->_initialize($attributes);
	
		return $instance;
	}
}
?>