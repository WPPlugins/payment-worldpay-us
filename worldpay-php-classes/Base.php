<?php
namespace Worldpay;
/**
 * Abstract class the all other classes are built upon. Implements magic methods __get() and 
 * _set() to store class property values
 * @author Clayton Rogers - mr.clayton@paymentplugins.com 
 *
 */
abstract class Base {
	
	public function __construct(){
		$this->_attributes = array();
	}
	
	/**
	 * Magic method that sets a name value pair in the property $_attributes
	 * @param string $key
	 * @param string $value
	 */
	public function _set($key, $value){
		$this->_attributes[$key] = $value;
	}
	
	/**
	 * Returns the objects attribute specified by the $key parameter. If the property is not 
	 * set then an error is triggered.
	 * @param string $key
	 * @return NULL
	 */
	public function __get($key){
		if(array_key_exists($key, $this->_attributes)){
			return $this->_attributes[$key];
		}
		/* else {
			trigger_error(sprintf('Property %s does not exist in class %s.', $key, get_class($this)), E_USER_NOTICE);
			return null;
		} */
		
	}
	
	public static function factory(array $attributes){
		$instance = new static();
		$instance->_initialize($attributes);
		
		return $instance;
	}
	
	protected function _initialize(array $attributes){
		
	}
}