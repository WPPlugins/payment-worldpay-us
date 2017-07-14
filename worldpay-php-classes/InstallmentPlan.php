<?php
class InstallmentPlan extends Base {
	
	protected function _initialize(array $attributes){
		$this->_attributes = isset($attributes['storedInstallmentPaymentPlan'])
			? $attributes['storedInstallmentPaymentPlan'] : $attributes;
	
		if(isset($attributes['customerId'])){
			$this->_set('customerId', $attributes['customerId']);
		}
	
		if(isset($attributes['planId'])){
			$this->_set('planId', $attributes['planId']);
		}
	}
}