<?php
/*
 * Plugin Name: Worldpay US For WooCommerce
 * Plugin URI: https://paymentplugins.com
 * Description: Accept credit card, ACH and PayPal payments on your wordpress site using your Worldpay US merchant account. SAQ A compliant.
 * Version: 1.0.6
 * Author: Clayton Rogers, mr.clayton@paymentplugins.com
 * Author URI: https://paymentplugins.com
 * Tested up to: 4.5.1
 */
if (version_compare ( PHP_VERSION, '5.4', '<' )) {
	add_action ( 'admin_notices', function () {
		echo '<div class="notice notice-error">';
		echo '<p>' . __ ( 'In order to use Worldpay US For WooCommerce, you must have PHP version 5.4 or greater. Your version is ' . PHP_VERSION, '' ) . '</p>';
		echo '</div>';
	} );
	return;
}

define ( 'WORLDPAYUS', plugin_dir_path ( __FILE__ ) );
define ( 'WORLDPAYUS_ADMIN', plugin_dir_path ( __FILE__ ) . 'admin/' );
define ( 'WORLDPAYUS_PAYMENTS', plugin_dir_path ( __FILE__ ) . 'payments/' );
define ( 'WORLDPAYUS_PAYMENTS_URL', plugin_dir_url ( __FILE__ ) . 'payments/' );
define ( 'WORLDPAYUS_ASSETS', plugin_dir_url ( __FILE__ ) . 'assets/' );
define ( 'WORLDPAYUS_ADMIN_URL', plugin_dir_url ( __FILE__ ) . 'admin/' );
define ( 'WORLDPAYUS_LICENSE_ACTIVATION_URL', 'https://wordpress.paymentplugins.com/' );
define ( 'WORLDPAYUS_LICENSE_VERIFICATION_KEY', 'gTys$hsjeScg63dDs35JlWqbx7h' );

include_once (WORLDPAYUS . 'worldpay-sdk.php');
include_once (WORLDPAYUS . 'class-loader.php');

?>