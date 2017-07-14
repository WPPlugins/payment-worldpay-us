<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 * @copyright 2015 PaymentPlugins
 */
class Error extends Base{
	
	private static $error_messages = array(
			'COMMUNICATION_ERROR',
			'AUTHENTICATION_ERROR',
			'DECLINE',
			'DECLINE_AVS',
			'DECLINE_CVV',
			'UNSUPPORTED_CARD',
			'INVALID_NAME',
			'INVALID_ADDRESS',
			'INVALID_CARD_NUMBER',
			'INVALID_CVV',
			'INVALID_EXPIRATION',
			'GATEWAY_ERROR',
			'BAD_REQUEST',
			'INVALID_ROUTING_NUMBER',
			'INVALID_AVS',
			'APPROVED',
			'INTERNALERROR'
	);
	
	protected function _initialize(array $attributes){
		if(in_array($attributes['result'], self::$error_messages)){
			$this->_set('message', $attributes['message']);
			$this->_set('errorCode', $attributes['responseCode']);
			$this->_set('errorType', $attributes['result']);
		}
	}
}