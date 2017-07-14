<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

spl_autoload_register(function($className){
	if( strpos( $className, 'Worldpay' ) === false && strpos( $className, 'PaymentPlugins') === false){
		return;
	}
	$path = plugin_dir_path(__FILE__).'worldpay-php-classes/';
	if($indexOf = strpos($className, '\\')){
		$className = substr($className, $indexOf + 1);
	}
	$file = $path . $className . '.php';
	
	if(is_file( $file )){
		require_once $file;
	}
});
?>