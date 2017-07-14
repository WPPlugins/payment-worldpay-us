<?php
namespace Worldpay;
/**
 * 
 * @author Payment Plugins - https://paymentplugins.com
 * @copyright 2015 Payment Plugins
 *
 */
class CreditCard extends PaymentMethod{

	private $cardFields = array(
			'cardType',
			'paymentTypeResult',
			'creditCardType',
			'cardNumber',
			'paymentType',
			'vaultData',
			'paymentId',
			'customerId',
			'billAddress',
			'lastAccessDate',
			'primary',
			'card',
			'check',
			'notes',
			'method',
			'userDefinedFields',
			'paymentTypeCode',
			'cardHolder_FirstName',
			'cardHolder_LastName',
			'firstName',
			'lastName',
			'email',
			'expirationDate',
			'expirationDate',
			'maskedNumber',
			'lastFourDigits',
	);
	
	protected function _initialize(array $attributes){
		$this->_attributes = $attributes;
		
		foreach($this->cardFields as $field){
			if(isset($attributes[$field])){
				$this->initializeCCData($field, $attributes[$field]);
			}
		}
		$this->_set('paymentType', 'CREDIT_CARD');
		
		if(isset($attributes['card']['address'])){
			$this->_set('billAddress', BillingAddress::factory($attributes['card']['address']));
		}
		if(isset($attributes['cardNumber'])){
			$this->_set('maskedNumber', $attributes['cardNumber']);
		}
		$this->_set('paymentMethodType', $this->creditCardType);

	}
	
	public static function factory(array $attributes){
		$instance = new self();
		$instance->_initialize($attributes);
		
		return $instance;
	}
	
	private function initializeCCData($field, $value){
		if(is_array($value)){
			foreach($value as $k => $v){
				$this->initializeCCData($k, $v);
			}
		}
		else {
			$this->_set($field, $value);
		}
	}
	
}