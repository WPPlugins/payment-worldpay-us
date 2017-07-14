<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 * @copyright 2015 PaymentPlugins
 *
 */
class VariablePaymentPlan extends PaymentPlan {
	
	protected function _initialize(array $attributes){
		$this->_attributes = isset($attributes['storedVariablePaymentPlan'])
			? $attributes['storedVariablePaymentPlan'] : $attributes;
	
		if(isset($attributes['customerId'])){
			$this->_set('customerId', $attributes['customerId']);
		}
	
		if(isset($attributes['planId'])){
			$this->_set('planId', $attributes['planId']);
		}
		
		if(isset($attributes['storedVariablePaymentPlan']['scheduledPayments'])){
			$scheduledPayments = array();
			foreach($attributes['storedVariablePaymentPlan']['scheduledPayments'] as $payment => $value){
				$scheduledPayments[] = ScheduledPayment::factory($value);
			}
			$this->_set('scheduledPayments', $scheduledPayments);
		}
	}
}