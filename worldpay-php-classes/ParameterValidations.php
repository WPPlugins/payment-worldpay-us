<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 * @copyright 2015 Payment Plugins
 */
class ParameterValidations {
	
	public static function checkRefundParams(array $params){
		if(! isset($params['transactionId']) || ! isset($params['developerApplication'])){
			WorldpayUS::throwError('ip');
		}
	}
	
	public static function checkAuthorizationCaptureParams(array $params){
		if(isset($params['paymentVaultToken'])){
			$required_params = array('amount','paymentVaultToken', 'developerApplication' );
		}
		else {
			$required_params = array('amount', 'card', 'developerApplication' );
		}
		if(empty($params)){
			WorldpayUS::throwError('Array for transaction cannot be empty');
		}
		foreach($required_params as $required){
			if(! isset($params[$required]) || empty($params[$required])){
				WorldpayUS::throwError('ip', sprintf('%s is a required parameter', $required));
			}
		}
		if(! is_array($params)){
			WorldpayUS::throwError('na');
		}
	
	}
	
	public static function checkCustomerCreateParams(array $params){
		$required_params = array('firstName', 'lastName', 'developerApplication' );
		foreach($required_params as $required){
			if(! isset($params[$required]) || empty($params[$required])){
				WorldpayUS::throwError('ip', sprintf('%s is a required parameter', $required));
			}
		}
	}
	
	public static function checkPaymentMethodCreateParams(array $params){
		$required_params = array('customerId', 'developerApplication');
		foreach($required_params as $required){
			if(! isset($params[$required])){
				WorldpayUS::throwError('ip', sprintf('%s is a required field.',$required));
			}
		}
	}
	
	public static function checkPaymentMethodDelete(array $params){
		$required_params = array('customerId', 'paymentMethodId', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkCustomerSearch($params){
		$required_params = array('customerId', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkRecurringBillingParams(array $params){
		$required_params = array('customerId', 'plan', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkGetPaymentPlanParams(array $params){
		$required_params = array('customerId', 'planId', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkPaymentPlanDeleteParams(array $params){
		$required_params = array('customerId', 'planId', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkPaymentPlanUpdateParams(array $params){
		$required_params = array('customerId', 'planId', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip');
			}
		}
	}
	
	public static function checkInstallmentPlan(array $params){
		$required_params = array('customerId', 'plan', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip', sprintf('Required parameter %s is missing.', $required));
			}
		}
	}
	
	public static function checkInstallmentPlanUpdate(array $params){
		$required_params = array('customerId', 'plan', 'developerApplication');
		foreach($required_params as $required){
			if(!isset($params[$required])){
				WorldpayUS::throwError('ip', sprintf('Required parameter %s is missing.', $required));
			}
		}
	}
}
?>