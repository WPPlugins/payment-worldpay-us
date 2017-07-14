<?php
use PaymentPlugins\Configuration;
use PaymentPlugins\PaymentPlugins;
use PaymentPlugins\PaymentPluginsException;
/**
 *
 * @author Clayton
 *        
 */
class WorldpayUS_Admin {
	public static $woocommerce_settings = array (
			'woocommerce_title',
			'enabled',
			'enable_ach',
			'enable_paypal',
			'payment_methods',
			'title_text',
			'invoice_prefix',
			'order_status',
			'order_notes',
			'paypal_button',
			'checkout_form' 
	);
	public static $woocommerce_subscription_settings = array (
			'subscription_title',
			'enable_woosubscriptions',
			'max_retries',
			'subscription_notes',
			'enable_payment_change',
			'subsciptions_allow_new',
			'subscription_status',
			'subscription_prefix' 
	);
	public static $subscription_settings = array (
			'subscription_worldpay_title',
			'worldpayus_subscriptions',
			'max_retries',
			'subscription_notes' 
	);
	public static $account_screen = array (
			'account_status' 
	);
	public static $api_settings = array (
			'api_settings',
			'account_status',
			'production_environment',
			'production_securenet_id',
			'production_secure_key',
			'production_public_key',
			'sandbox_environment',
			'sandbox_securenet_id',
			'sandbox_secure_key',
			'sandbox_public_key',
	);
	public static $debug_settings = array (
			'debug_title',
			'enable_debug' 
	);
	public static function init() {
		add_action ( 'admin_init', __CLASS__ . '::load_admin_scripts' );
		add_action ( 'admin_menu', __CLASS__ . '::worldpay_admin_menu' );
		add_action ( 'admin_init', __CLASS__ . '::save_worldpay_configuration' );
		add_action ( 'admin_notices', __CLASS__ . '::displayAdminNotices' );
		add_action ( 'activate_' . WP_Manager ()->plugin_name, __CLASS__ . '::pluginActivationNotice' );
	}
	public static function save_worldpay_configuration() {
		if (isset ( $_POST ['save_worldpayus_apisettings'] )) {
			self::saveSettings ( self::$api_settings, 'worldpayus-payment-settings' );
		} elseif (isset ( $_POST ['save_paymentplugins_apikeys'] )) {
			self::saveSettings ( self::$paymentplugins_settings, 'worldpayus-payment-settings' );
		} elseif (isset ( $_POST ['save_worldpayus_woocommerce_settings'] )) {
			self::saveSettings ( self::$woocommerce_settings, 'worldpayus-woocommerce-settings' );
		} elseif (isset ( $_POST ['save_worldpayus_woocommercesubscription_settings'] )) {
			self::saveSettings ( self::$woocommerce_subscription_settings, 'worldpayus-subscription-config' );
		} elseif (isset ( $_POST ['save_worldpayus_subscription_settings'] )) {
			self::saveSettings ( self::$subscription_settings, 'worldpayus-subscription-config' );
		} elseif (isset ( $_POST ['worldpayus_save_debug_settings'] )) {
			self::saveSettings ( self::$debug_settings, 'worldpayus-debug-log' );
		} elseif (isset ( $_POST ['worldpayus_delete_debug_log'] )) {
			WP_Manager ()->deleteDebugLog ();
		}
	}
	public static function saveSettings($fields, $page) {
		$defaults = array (
				'title' => '',
				'type' => '',
				'value' => '',
				'type' => '',
				'class' => array (),
				'default' => '' 
		);
		$settings = WP_Manager ()->settings;
		$required_settings = WP_Manager ()->required_settings;
		foreach ( $fields as $field ) {
			$value = isset ( $required_settings [$field] ) ? $required_settings [$field] : $defaults;
			$value = wp_parse_args ( $value, $defaults );
			if (is_array ( $value ['value'] ) && $value ['type'] === 'checkbox') {
				foreach ( $value ['value'] as $k => $v ) {
					$settings [$field] [$k] = isset ( $_POST [$k] ) ? $_POST [$k] : '';
				}
			} else {
				$settings [$field] = isset ( $_POST [$field] ) ? trim ( $_POST [$field] ) : '';
			}
		}
		WP_Manager ()->update_settings ( $settings );
		wp_redirect ( get_admin_url () . 'admin.php?page=' . $page );
	}
	public static function load_admin_scripts() {
		wp_enqueue_style ( 'worldpay-us-admin', WORLDPAYUS_ADMIN_URL . 'assets/css/worldpay-payments-admin.css' );
		wp_enqueue_script ( 'worldpayus-admin-js', WORLDPAYUS_ADMIN_URL . 'assets/js/worldpayus-admin.js' );
	}
	public static function worldpay_admin_menu() {
		add_menu_page ( 'Worldpay Payments', 'Worldpay US Payments', 'manage_options', 'worldpayus-payments-menu', null, null, '9.234' );
		add_submenu_page ( 'worldpayus-payments-menu', 'API KEYS', 'Worldpay Settings', 'manage_options', 'worldpayus-payment-settings', 'WorldpayUS_Admin::show_worldpay_payments_settings' );
		add_submenu_page ( 'worldpayus-payments-menu', 'Woocommerce Payments', 'Woocommerce Payments', 'manage_options', 'worldpayus-woocommerce-settings', 'WorldpayUS_Admin::show_woocommerce_config' );
		add_submenu_page ( 'worldpayus-payments-menu', 'Subscriptions', 'Subscriptions', 'manage_options', 'worldpayus-subscription-config', 'WorldpayUS_Admin::show_subscription_config' );
		add_submenu_page ( 'worldpayus-payments-menu', 'Tutorials', 'Tutorials', 'manage_options', 'worldpayus-payments-instructions', 'WorldpayUS_Admin::show_worldpay_instructions' );
		add_submenu_page ( 'worldpayus-payments-menu', 'Debug Log', 'Debug Log', 'manage_options', 'worldpayus-debug-log', 'WorldpayUS_Admin::show_debug_log' );
		remove_submenu_page ( 'worldpayus-payments-menu', 'worldpayus-payments-menu' );
	}
	public static function show_worldpay_payments_settings() {
		self::get_admin_header ();
		WP_Manager ()->display_settingsPage ( self::$api_settings, 'worldpayus-payment-settings', 'save_worldpayus_apisettings' );
	}
	public static function show_woocommerce_config() {
		self::get_admin_header ();
		WP_Manager ()->display_settingsPage ( self::$woocommerce_settings, 'worldpayus-woocommerce-settings', 'save_worldpayus_woocommerce_settings' );
	}
	public static function show_debug_log() {
		self::get_admin_header ();
		WP_Manager ()->display_settingsPage ( self::$debug_settings, 'worldpayus-debug-log', 'worldpayus_save_debug_settings' );
		?>
<form class="worldpay-deletelog-form" name="worldpay_woocommerce_form"
	method="post"
	action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'].'?page=worldpayus-debug-log') ?>">
	<button name="worldpayus_delete_debug_log"
		class="worldpay-payments-save" type="submit">Delete Log</button>
</form>
<div class="config-separator"></div>
<div class="worldpay-debug-log-container">
				<?php echo WP_Manager()->display_debugLog()?>
			</div>
<?php
	}
	public static function show_subscription_config() {
		self::get_admin_header ();
		if (WP_Manager ()->woocommerceSubscriptionsActive ()) {
			WP_Manager ()->display_settingsPage ( self::$woocommerce_subscription_settings, 'worldpayus-subscription-config', 'save_worldpayus_woocommercesubscription_settings' );
		} else
			WP_Manager ()->display_settingsPage ( self::$subscription_settings, 'worldpayus-subscription-config', 'save_worldpayus_subscription_settings' );
	}
	public static function showAccountStatusPage() {
		self::get_admin_header ();
		WP_Manager ()->display_settingsPage ( self::$account_screen, 'worldpayus-payments-accountstatus', null );
	}
	public static function show_worldpay_instructions() {
		WC_Admin_Tutorials::showTutorialsView ();
	}
	public static function get_admin_header() {
		?>
<div class="worldpay-header">
	<div class="worldpay-logo-inner">
		<a><img
			src="<?php echo WORLDPAYUS_ADMIN_URL.'assets/images/worldpay-logo.png'?>"
			class="worldpay-logo-header" /></a>
	</div>
	<ul>
		<li><a href="?page=worldpayus-payment-settings"><?php echo __('API Settings', 'worldpayus')?></a></li>
		<li><a href="?page=worldpayus-woocommerce-settings"><?php echo __('WooCommerce Settings', 'worldpay')?></a></li>
		<li><a href="?page=worldpayus-subscription-config"><?php echo __('Subscriptions', 'worldpayus')?></a></li>
		<li><a href="?page=worldpayus-payments-instructions"><?php echo __('Tutorials', 'worldpayus')?></a></li>
		<li><a href="?page=worldpayus-debug-log"><?php echo __('Debug Log', 'worldpayus')?></a></li>

	</ul>
				<?php //echo WP_Manager()->get_payment_pluginsUrl()?>
				</div>
<?php
	}
	public static function getAccountStatus($values = array()) {
		// Call the Payment Plugins API and fetch the account status for each environment.
		$publicKey = WP_Manager ()->get_option ( 'production_securenet_id' );
		$privateKey = WP_Manager ()->get_option ( 'production_secure_key' );
		Configuration::environment ( 'production' );
		Configuration::publicKey ( $publicKey );
		Configuration::privateKey ( $privateKey );
		$paymentplugins = PaymentPlugins::newInstance ();
		$html = '';
		try {
			$response = $paymentplugins->getAccount ();
			$status = isset ( $response ['account'] ['active'] ) ? $response ['account'] ['active'] : 'false';
			$result = $status === 'true' ? 'Active' : 'Inactive';
			$html = '<div class="account' . $result . '"><span>' . $result . '</span></div>';
		} catch ( PaymentPluginsException $e ) {
			WP_Manager ()->log->writeErrorToLog ( 'Error fetching account status. ' . $e->getMessage () );
			$html = '<div><span>There was an error retrieving your account status. 
					Please check your api keys. Error Message: '. $e->getMessage () . '</span></div>';
		}
		return $html;
	}
	
	public static function displayAdminNotices() {
		$messages = WP_Manager ()->getAdminNotices ();
		if (! empty ( $messages )) {
			foreach ( $messages as $message ) {
				?>
				<div class="notice notice-<?php echo $message['type']?>">
					<p><?php echo $message['text']?></p>
				</div>
				<?php
			}
		}
		WP_Manager ()->deleteAdminNotices ();
	}
	
	public static function pluginActivationNotice() {
		WP_Manager ()->addAdminNotice ( array (
				'type' => 'success',
				'text' => __ ( 'Worlday US - Thank you for activating Worldpay for US. To get started, please follow our <a target="_blank" href="">tutorials</a> which guides you through how to 
						create  your <a target="_blank" href="https://paymentplugins.com/signup">Payment Plugins</a> and <a target="_blank" href="https://securenet.secure.force.com/UMA?partnerCode=9280">Worldpay US</a> accounts.', 'worldpayus' ) 
		) );
	}
}
WorldpayUS_Admin::init ();
?>