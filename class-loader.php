<?php
if (! defined ( 'ABSPATH' )) {
	exit (); // Exit if accessed directly
}

spl_autoload_register ( function ($className) {
	if (is_file ( WORLDPAYUS_ADMIN . 'classes/' . $className . '.php' )) {
		require_once (WORLDPAYUS_ADMIN . 'classes/' . $className . '.php');
	} elseif (is_file ( WORLDPAYUS_PAYMENTS . 'classes/' . $className . '.php' )) {
		require_once (WORLDPAYUS_PAYMENTS . 'classes/' . $className . '.php');
	}
} );

include_once (WORLDPAYUS_ADMIN . 'classes/WP_Manager.php');
include_once (WORLDPAYUS_ADMIN . 'classes/WorldpayUS_Admin.php');
include_once (WORLDPAYUS_ADMIN . 'classes/Worldpay_DebugLog.php');
include_once (WORLDPAYUS_ADMIN . 'classes/WCS_WP_Meta_Box.php');
include_once (WORLDPAYUS_ADMIN . 'classes/WorldpayUS_Updates.php');

add_action ( 'plugins_loaded', function () {
	if (WP_Manager ()->woocommerceActive ()) {
		include_once (WORLDPAYUS_PAYMENTS . 'classes/WorldpayUS_Payments.php');
		include_once (WORLDPAYUS_PAYMENTS . 'classes/WorldpayUS_Subscriptions.php');
		include_once (WORLDPAYUS_PAYMENTS . 'functions/wps-functions.php');
	}
	if (WP_Manager ()->woocommerceSubscriptionsActive ()) {
		include_once (WORLDPAYUS_PAYMENTS . 'classes/WCS_WorldpayUS_Subscriptions.php');
	}
} );
