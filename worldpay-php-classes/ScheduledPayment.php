<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 * @copyright 2015 PaymentPlugins
 */
class ScheduledPayment extends Base {
	
	protected function _initialize(array $attributes){
		$this->_attributes = $attributes;
	}
}