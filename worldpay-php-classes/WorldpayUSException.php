<?php
namespace Worldpay;
/**
 * 
 * @author Clayton Rogers
 *
 */
class WorldpayUSException extends \Exception
{
	
	public function __construct($code = null, $message = null){
		$this->code = $code;
		$this->message = $message;
	}
}
?>