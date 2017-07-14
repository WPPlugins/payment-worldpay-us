<?php

namespace Worldpay;

/**
 *
 * @author Payment Plugins - https://paymentplugins.com
 * @copyright 2015 - Payment Plugins
 *           
 * @property-read bool $success result from the Worldpay request.
 * @property-read array $errors array of error messages if Worldpay request had an error.
 */
class Response extends Base {
	
	/**
	 * Initialize the class with attributes parameter.
	 * 
	 * @see Base::_initialize()
	 */
	protected function _initialize(array $attributes) {
		$this->_attributes = $attributes;
		
		if (! isset ( $attributes ['result'] )) {
			throw new WorldpayUSException ( null, 'Response has no result' );
		}
		if ($attributes ['result'] === 'SUCCESS' || $attributes ['result'] === 'APPROVED') {
			$this->success = true;
		} else {
			$this->success = false;
			$this->errors = array ();
			$this->errors [] = Error::factory ( $attributes );
			$this->message = $attributes ['message'];
			return;
		}
		if (isset ( $attributes ['transaction'] )) {
			$this->_set ( 'transaction', Transaction::factory ( $attributes ['transaction'] ) );
		}
		
		if ($attributes ['requestType'] === Constants::CREATE_PAYMENTMETHOD) {
			$this->_set ( 'paymentMethod', PaymentMethod::factory ( $attributes ) );
		}
		if ($attributes ['requestType'] === Constants::REFUND) {
		}
		if ($attributes ['requestType'] === Constants::GET_PAYMENTACCOUNT) {
			$this->_set ( 'paymentMethod', PaymentMethod::factory ( $attributes ['vaultPaymentMethod'] ) );
		}
		if ($attributes ['requestType'] === Constants::GET_CUSTOMER || $attributes ['requestType'] === Constants::CREATE_CUSTOMER) {
			if (isset ( $attributes ['customerId'] ) && $attributes ['customerId'] === 'ERROR') {
				$this->success = false;
				return;
			}
			if (isset ( $attributes ['vaultCustomer'] )) {
				$this->_set ( 'customer', Customer::factory ( $attributes ['vaultCustomer'] ) );
			}
		}
		if ($attributes ['requestType'] === Constants::DELETE_CUSTOMER && isset ( $attributes ['customerId'] )) {
			$this->_set ( 'customer', Customer::factory ( $attributes ) );
		}
		if ($attributes ['requestType'] === Constants::PAYMENTPLAN) {
			if (isset ( $attributes ['storedVariablePaymentPlan'] )) {
				$this->_set ( 'paymentPlan', VariablePaymentPlan::factory ( $attributes ) );
			} elseif (isset ( $attributes ['storedRecurringPaymentPlan'] )) {
				$this->_set ( 'paymentPlan', PaymentPlan::factory ( $attributes ) );
			} elseif (isset ( $attributes ['storedInstallmentPaymentPlan'] )) {
				$this->_set ( 'paymentPlan', InstallmentPlan::factory ( $attributes ) );
			}
		}
		
		unset ( $attributes ['transaction'] );
		
		foreach ( $attributes as $attribute => $value ) {
			$this->_set ( $attribute, $value );
		}
	}
}