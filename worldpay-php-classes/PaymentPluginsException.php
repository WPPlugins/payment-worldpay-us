<?php
namespace PaymentPlugins;

/**
 * 
 * @author Clayton Rogers
 *
 */
class PaymentPluginsException extends \Exception {
	public function __construct($code, $message)
	{
		parent::__construct($message, $code, $previous = null);
	}
}