<?php
/**
 * 
 * @author Clayton Rogers
 * @property	int $id
 * @property	string $subscription_period
 * @property	string $subscription_period_interval
 * @property	string $subscription_price
 * @property	string $subscription_trial_length
 * @property	string $subscription_sign_up_fee
 * @property	string $subscription_length
 * @property	string $subscription_trial_period
 * @property	string $subscription_type
 */
class WorldpayUS_Subscription_Product {
	
	private $_attributes = array();
	
	/**
	 * Initializes a worldpay subscription object based on the id that was passed. 
	 * @param int $id
	 */
	public function __construct($post_id){
		$attributes = get_post_meta($post_id);
		foreach($attributes as $attribute=>$value){
			if(substr($attribute, 0, 1) === '_'){
				$attribute = substr($attribute, 1);
			}
			$this->_set($attribute, $value[0]);
		}
		
		$this->_set('id', $post_id);
		$post = get_post($post_id);
		
		$this->_set('post_title', $post->post_title);
	}
	
	public function _set($key, $value){
		$this->_attributes[$key] = $value;
	}
	
	public function __get($key){
		if(array_key_exists($key, $this->_attributes)){
			return $this->_attributes[$key];
		}
		else {
			return '';
		}
	}
	
	/**
	 * Returns the unix timestamp for the subscription start date. The start date cannot be between 28 - 31
	 * because of Worldpay's rules. Therefore, any subscription that is that falls on those days must automatically start 
	 * on the first of the next month.
	 */
	public function getStartDate(){
		$startDate = new DateTime();
		$startDate->setTimestamp(time());
		$startDate->add(new DateInterval('P1D'));
		return $startDate->getTimestamp();
	}
	
	/**
	 * Returns the unix timestamp for the subscription end date.
	 */
	public function getEndDate(){
		$interval = wps_get_date_time_interval($this->subscription_period);
		$endDate = new DateTime();
		$endDate->setTimestamp($this->getStartDate());
		$endDate->add(new DateInterval('P'.$this->subscription_length.$interval));
		return $endDate->getTimestamp();
	}
	
	public function isRecurring(){
		return $this->subscription_type === 'recurring';
	}
	
	public function isVariable(){
		return $this->subscription_type === 'variable';
	}
	
	public function isInstallment(){
		return $this->subscription_type === 'installment';
	}
}