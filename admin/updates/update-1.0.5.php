<?php
/**
 * @author Payment Plugins
 * @since 1.0.5
 */
WP_Manager ()->addAdminNotice ( array (
		'type' => 'info',
		'text' => '<span class="worldpayus-info important">' . __ ( '<strong>Worldpay US</strong> - Version 1.0.5 is a major version update. The solution now consists of a hosted payment form provided by Payment Plugins which is SAQ A compliant. 
				If you wish to contiue using version 1.0.4 of the plugin, you can download it <a target="_blank" href="https://downloads.wordpress.org/plugin/payment-worldpay-us.1.0.4.zip">here</a>', 'worldpayus' ) . '</span>' 
) );
WP_Manager ()->addAdminNotice ( array (
		'type' => 'info',
		'text' => '<span class="worldpayus-info important">' . __ ( '<strong>Worldpay US</strong> - If you wish to use the SAQ A compliant version, there are several steps you must perform. You need to sign up for a <a target="_blank" href="https://paymentplugins.com/signup">Payment Plugins</a> account and
				a <a target="_blank" href="https://securenet.secure.force.com/UMA?partnerCode=9280">Worldpay US</a> account if you haven\'t already. Once signed up for Payment Plugins, you 
				will need to enter your Worldpay API keys in the Payment Plugins site. For detailed instructions, please visit the <a target="_blank" href="' . admin_url () . 'admin.php?page=worldpayus-payments-instructions">Tutorials Page</a>', 'worldpayus' ) . '</span>' 
) );
$license_status = WP_Manager ()->get_option ( 'license_status' );
$license_key = WP_Manager ()->get_option ( 'license_status' );
if (! empty ( $license_status ) && strtolower ( $license_status ) === 'active') {
	WP_Manager ()->addAdminNotice ( array (
			'type' => 'info',
			'text' => '<span class="worldpayus-info important">' . __ ( 'You have already purchased a license from Payment Plugins. If you choose to use the new version of the plugin, contact <a target="_blank" href="mailto:support@paymentplugins.com">support@paymentplugins.com</a>
					and we will activate your account for Payment Plugins with no charge in order to honor your license purchase. Please reach out to us if you have any questions.', 'worldpayus' ) . '</span>' 
	) );
}