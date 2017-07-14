<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 *
 */
class PaymentPlan extends Base {
	 
	protected function _initialize(array $attributes){
		$this->_attributes = isset($attributes['storedRecurringPaymentPlan'])
			 ? $attributes['storedRecurringPaymentPlan'] : $attributes;
		
		if(isset($attributes['customerId'])){
			$this->_set('customerId', $attributes['customerId']);
		}
		
		if(isset($attributes['planId'])){
			$this->_set('planId', $attributes['planId']);
		}
	}
	
	/**
	 * Check if the payment plan object is an instance of teh variable payment plan.
	 * @return boolean
	 */
	public function isVariablePlan(){
		if($this instanceof VariablePaymentPlan){
			return true;
		}
		return false;
	}
	
	public function isRecurringPlan(){
		if($this instanceof PaymentPlan){
			return true;
		}
		return false;
	}
}