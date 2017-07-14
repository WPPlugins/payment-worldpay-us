<?php
use Worldpay\WorldpayUS;
use Worldpay\WorldpayUSException;
use PaymentPlugins\Configuration;
/**
 * Main helper class.
 *
 * @author Clayton
 *        
 */
class WP_Manager {
	public $debug;
	public $transaction_log;
	public $log;
	public $settings;
	public $required_settings;
	public $achEnabled;
	public $worldpayUS;
	public static $_instance = null;
	public $version = '1.0.6';
	public $plugin_name = 'payment-worldpay-us/worldpay.php';
	public $developerApplication = array (
			'developerId' => 10000605,
			'version' => '1.0' 
	);
	public function update_payments_config($option, $value) {
		$option = base64_encode ( $option );
		update_option ( $option, base64_encode ( maybe_serialize ( $value ) ) );
	}
	public function get_payments_config($option) {
		$option = base64_encode ( $option );
		return maybe_unserialize ( base64_decode ( get_option ( $option ) ) );
	}
	public static function instance() {
		if (self::$_instance == null) {
			return new WP_Manager ();
		} else
			return self::$_instance;
	}
	
	/**
	 * Initialize a new instance of WP_Manager.
	 */
	public function __construct() {
		$this->init ();
		WP_Manager::$_instance = $this;
	}
	
	/**
	 * Initialize all configuration settings for admin screens.
	 */
	public function init() {
		$this->required_settings = include (WORLDPAYUS_ADMIN . 'includes/worldpay-settings.php');
		$this->settings = $this->init_settings ();
		$this->log = new Worldpay_DebugLog ();
		$this->achEnabled = $this->get_option ( 'enable_ach' ) === 'yes' ? true : false;
		$this->debug = $this->get_option ( 'enable_debug' ) === 'yes' ? true : false;
		$this->initializeWorldpay ();
	}
	
	/**
	 * Initialize the Worldpay configuration.
	 */
	public function initializeWorldpay() {
		Configuration::environment ( $this->getEnvironment () );
		//Configuration::environment("development");
		Configuration::publicKey ( $this->getSecureNetId () );
		Configuration::privateKey ( $this->getSecureKey () );
		Configuration::worldpayPublicKey ( $this->getPublicKey () );
		try {
			WorldpayUS::configuration ( $this->getEnvironment (), $this->getSecureNetId (), $this->getSecureKey () );
			$this->worldpayUS = WorldpayUS::instance ();
		} catch ( WorldpayUSException $e ) {
		}
	}
	public function init_settings() {
		return $this->get_payments_config ( 'worldpayus_settings' );
	}
	
	public function get_option($key) {
		if ($this->settings == null) {
			$this->init_settings ();
		}
		if (! isset ( $this->settings [$key] )) {
			$this->settings [$key] = isset ( $this->required_settings [$key] ['default'] ) ? $this->required_settings [$key] ['default'] : '';
		}
		return $this->settings [$key];
	}
	public function set_option($key, $value = '') {
		$this->settings [$key] = $value;
	}
	public function update_settings($settings = null) {
		if ($this->settings == null) {
			$this->init_settings ();
		}
		if ($settings != null) {
			$this->settings = $settings;
		}
		$this->update_payments_config ( 'worldpayus_settings', $this->settings );
	}
	public static function displayAdminMessage() {
		$message = 'Your License Key has been activated.';
		echo '<div class="updated"><p>' . $message . '</p></div>';
	}
	public static function displayAdminErrorMessage() {
		$message = 'Your License Key could not be activated at this time. Check the debug log for error specifics. If
				the problem persists, please contact support@paymentplugins.com';
		echo '<div class="error"><p>' . $message . '</p></div>';
	}

	public function get_payment_pluginsUrl() {
		$license = $this->get_option ( 'license_key' );
		$license_status = $this->get_option ( 'license_status' );
		if (empty ( $license ) || $license_status === 'inactive') {
			return '<div class="license-activation-url"><span>
			<a href="https://paymentplugins.com/product-category/worldpay/" target="_blank">Click Here To Purchase a License</a></span>
			</div>';
		} else if (! empty ( $license ) && $license_status === 'active') {
			return '<div class="next-license-activation"><span>Your License is Active</span>
				</div>';
		}
	}

	public static function register_postType() {
		register_post_type ( 'worldpayus_log', array (
				'public' => false,
				'has_archive' => true,
				'show_ui' => false 
		) );
		if (WP_Manager ()->isActive ( 'worldpayus_subscriptions' )) {
			wc_register_order_type ( 'shop_subscription', apply_filters ( 'woocommerce_register_post_type_subscription', array (
					// register_post_type() params
					'labels' => array (
							'name' => _x ( 'Subscriptions', 'custom post type setting', 'woocommerce-subscriptions' ),
							'singular_name' => _x ( 'Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'add_new' => _x ( 'Add Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'add_new_item' => _x ( 'Add New Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'edit' => _x ( 'Edit', 'custom post type setting', 'woocommerce-subscriptions' ),
							'edit_item' => _x ( 'Edit Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'new_item' => _x ( 'New Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'view' => _x ( 'View Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'view_item' => _x ( 'View Subscription', 'custom post type setting', 'woocommerce-subscriptions' ),
							'search_items' => _x ( 'Search Subscriptions', 'custom post type setting', 'woocommerce-subscriptions' ),
							'not_found' => 'not found',
							'not_found_in_trash' => _x ( 'No Subscriptions found in trash', 'custom post type setting', 'woocommerce-subscriptions' ),
							'parent' => _x ( 'Parent Subscriptions', 'custom post type setting', 'woocommerce-subscriptions' ),
							'menu_name' => _x ( 'Subscriptions', 'Admin menu name', 'woocommerce-subscriptions' ) 
					),
					'description' => __ ( 'This is where subscriptions are stored.', 'woocommerce-subscriptions' ),
					'public' => false,
					'show_ui' => true,
					'capability_type' => 'shop_order',
					'map_meta_cap' => true,
					'publicly_queryable' => false,
					'exclude_from_search' => true,
					'show_in_menu' => current_user_can ( 'manage_woocommerce' ) ? 'woocommerce' : true,
					'hierarchical' => false,
					'show_in_nav_menus' => false,
					'rewrite' => false,
					'query_var' => false,
					'supports' => array (
							'title',
							'comments',
							'custom-fields' 
					),
					'has_archive' => false,
					
					// wc_register_order_type() params
					'exclude_from_orders_screen' => true,
					'add_order_meta_boxes' => true,
					'exclude_from_order_count' => true,
					'exclude_from_order_views' => true,
					'exclude_from_order_webhooks' => true,
					'class_name' => 'WorldpayUS_Subscription' 
			) ) );
		}
	}
	public function display_debugLog() {
		$log = new Worldpay_DebugLog ();
		return $log->display_debugLog ();
	}
	
	/**
	 * Deletes the debug log entries.
	 */
	public function deleteDebugLog() {
		$log = new Worldpay_DebugLog ();
		$log->delete_log ();
	}
	public function save_customerId($customerId, $user_id, $responseTime = false) {
		$customerIds = $this->get_customerIds ( $user_id );
		if (empty ( $customerIds )) {
			$customerIds = array ();
		}
		// Prevent duplicates from being added.
		foreach ( $customerIds as $index => $id ) {
			if ($customerId == $id) {
				return false;
			}
		}
		$customerIds [] = $customerId;
		
		if (update_user_meta ( $user_id, 'worldpayus_customer_ids', $customerIds )) {
			$this->log->writeToLog ( sprintf ( 'New customerId created in SecureNet vault. CustomerId: %s, User ID: %s. Server creation time: %s', $customerId, $user_id, $responseTime ) );
		} else {
			$this->log->writeErrorToLog ( sprintf ( 'CustomerId %s was not saved to the Wordpress database. User ID: %s.', $customerId, $user_id ) );
		}
	}
	
	/**
	 * Method that returns the customerId for the Worldpay Customer.
	 *
	 * @param number $user_id        	
	 */
	public function getWorldpayCustomerId($user_id = 0) {
		$customerId = null;
		if ($user_id) {
			$customerId = get_user_meta ( $user_id, 'worldpayus_' . $this->getEnvironment () . '_customer_id', true );
		}
		return $customerId;
	}
	public function createWorldpayCustomer($params = array(), $user_id) {
		try {
			$worldpay = WorldpayUS::instance ();
			$response = $worldpay->createCustomer ( $params );
			if (! $response->success) {
				foreach ( $response->errors as $error ) {
					wc_add_notice ( $error->message, 'error' );
					WP_Manager ()->log->writeToLog ( $error->message );
				}
			} else {
				$customerId = $response->customer->customerId;
				update_user_meta ( $user_id, 'worldpayus_' . $this->getEnvironment () . '_customer_id', $customerId );
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( $e->getMessage () );
		}
	}
	public function subscription_config($setting = null) {
		if (isset ( $this->subscription_config [$setting] )) {
			$value = $this->subscription_config [$setting];
		} else
			$value = '';
		return $value;
	}
	public function worldpay_subscriptions($setting = null) {
		if (isset ( $this->worldpay_subscriptions [$setting] )) {
			return $this->worldpay_subscriptions [$setting];
		}
		return '';
	}
	public function paypal_config($setting, $environment = false) {
		if (! $environment) {
			$environment = 'sandbox';
			if (isset ( $this->api_config ['paypal'] ['environment'] )) {
				$environment = $this->api_config ['paypal'] ['environment'];
			}
		}
		if (isset ( $this->api_config ['paypal'] [$environment] [$setting] )) {
			return $this->api_config ['paypal'] [$environment] [$setting];
		} elseif ($this->api_config ['paypal'] [$setting]) {
			return $this->api_config ['paypal'] [$setting];
		}
		return '';
	}
	public function get_productOptionsHTML() {
		$html = '';
		$args = array (
				'post_type' => 'product',
				'posts_per_page' => - 1 
		);
		$posts = get_posts ( $args );
		foreach ( $posts as $post ) {
			$html .= '<option id="post_' . $post->ID . '" value="' . $post->ID . '">' . $post->post_title . '</option>';
		}
		return $html;
	}
	public function display_settingsPage($fields_to_display, $page, $button) {
		$form_fields = $this->required_settings;
		$html = '<form method="POST" action="' . get_admin_url () . '/admin.php?page=' . $page . '">';
		$html .= '<table class="worldpay-woocommerce-settings"><tbody>';
		foreach ( $fields_to_display as $key ) {
			$value = isset ( $this->required_settings [$key] ) ? $this->required_settings [$key] : array ();
			$html .= Worldpay_HtmlHelper::buildSettings ( $key, $value, $this->settings );
		}
		$html .= '</tbody></table>';
		if ($button != null) {
			$html .= '<div><input name="' . $button . '" class="worldpay-payments-save" type="submit" value="Save"></div>';
		}
		$html .= '</form>';
		echo $html;
	}
	public static function pluginActivationRegistration() {
		$status = get_option ( 'worldpayus_plugin_activation', true );
		if ($status === 'true') {
			return true;
		}
		$email = get_option ( 'admin_email', true );
		$url = get_site_url ();
		$api_params = array (
				'paymentplugin_action' => 'plugin_activation',
				'site_url' => $url,
				'admin_email' => $email,
				'plugin_name' => 'worldpayus_for_woocommerce' 
		);
		
		$response = wp_remote_get ( add_query_arg ( $api_params, WORLDPAYUS_LICENSE_ACTIVATION_URL ), array (
				'timeout' => 20 
		) );
		
		if (! is_wp_error ( $response )) {
			if (isset ( $response ['body'] )) {
				$body = json_decode ( $response ['body'], true );
				if ($body ['result'] === 'success') {
					update_option ( 'worldpayus_plugin_activation', 'true' );
				} else
					update_option ( 'worldpayus_plugin_activation', 'false' );
			}
		} else
			update_option ( 'worldpayus_plugin_activation', 'false' );
	}
	public function getEnvironment() {
		$environment = $this->get_option ( 'production_environment' ) === 'yes' ? 'production' : 'sandbox';
		return $environment;
	}
	public function getSecureNetId() {
		return $this->get_option ( $this->getEnvironment () . '_securenet_id' );
	}
	public function getSecureKey() {
		return $this->get_option ( $this->getEnvironment () . '_secure_key' );
	}
	public function getPublicKey() {
		return $this->get_option ( $this->getEnvironment () . '_public_key' );
	}
	public function isActive($option) {
		return $this->get_option ( $option ) === 'yes';
	}
	public function woocommerceActive() {
		$plugins = get_option ( 'active_plugins', true );
		return in_array ( 'woocommerce/woocommerce.php', $plugins ) || array_key_exists ( 'woocommerce/woocommerce.php', $plugins );
	}
	
	/**
	 * Return true if the woocommerce-subscriptions plugin is active.
	 */
	public function woocommerceSubscriptionsActive() {
		$plugins = get_option ( 'active_plugins', true );
		return in_array ( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $plugins ) || array_key_exists ( 'woocommerce-subscriptions/woocommercesubscriptions.php', $plugins );
	}
	public static function getWooCommerceStatuses() {
		return array (
				'wc-pending' => _x ( 'Pending Payment', 'Order status', 'woocommerce' ),
				'wc-processing' => _x ( 'Processing', 'Order status', 'woocommerce' ),
				'wc-on-hold' => _x ( 'On Hold', 'Order status', 'woocommerce' ),
				'wc-completed' => _x ( 'Completed', 'Order status', 'woocommerce' ),
				'wc-cancelled' => _x ( 'Cancelled', 'Order status', 'woocommerce' ),
				'wc-refunded' => _x ( 'Refunded', 'Order status', 'woocommerce' ),
				'wc-failed' => _x ( 'Failed', 'Order status', 'woocommerce' ) 
		);
	}
	public static function getSubscriptionStatuses() {
		return array (
				'wc-pending' => _x ( 'Pending', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-active' => _x ( 'Active', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-on-hold' => _x ( 'On hold', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-cancelled' => _x ( 'Cancelled', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-switched' => _x ( 'Switched', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-expired' => _x ( 'Expired', 'Subscription status', 'woocommerce-subscriptions' ),
				'wc-pending-cancel' => _x ( 'Pending Cancellation', 'Subscription status', 'woocommerce-subscriptions' ) 
		);
	}
	public function getSubscriptionPeriods() {
		return array (
				'day' => __ ( 'Day', 'worldpayus' ),
				'week' => __ ( 'Week', 'worldpayus' ),
				'month' => __ ( 'Month', 'worldpayus' ),
				'year' => __ ( 'Year', 'worldpayus' ) 
		);
	}
	public function getSubscriptionPeriodIntervals() {
		global $thepostid, $post;
		$period = get_post_meta ( $post->ID, '_subscription_period', true );
		return wps_get_subscription_interval ( $period );
	}
	public function getSubscriptionLengths() {
		global $post;
		$period = get_post_meta ( $post->ID, '_subscription_period', true );
		return wps_get_subscription_lengths ( $period, get_post_meta ( $post->ID, '_subscription_period_interval', true ) );
	}
	public function getPaymentMethod($paymentId) {
		$paymentMethod = null;
		
		try {
			$response = $this->worldpayUS->getPaymentMethod ( array (
					'paymentMethodId' => $paymentId,
					'customerId' => WP_Manager ()->getWorldpayCustomerId ( wp_get_current_user ()->ID ) 
			) );
			$paymentMethod = $response->paymentMethod;
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( 'Method: update_subscription_paymentMethod.' . $e->getMessage () );
			$paymentMethod = false;
		}
		return $paymentMethod;
	}
	public function getWorldpaySubscription($params) {
		try {
			$response = $this->worldpayUS->getPaymentPlan ( $params );
			if ($response->success) {
				return $response->paymentPlan;
			} else {
				return false;
			}
		} catch ( WorldpayUSException $e ) {
			WP_Manager ()->log->writeErrorToLog ( sprintf ( 'Method: getWorldpaySubscription(). Message: %s', $e->getMessage () ) );
			return false;
		}
	}
	
	/**
	 * Get the request parameter from the $_POST, $_GET, or $_REQUEST
	 *
	 * @param string $string        	
	 * @return string
	 */
	public function getRequestParameter($string = '') {
		$param = null;
		if (isset ( $_POST [$string] )) {
			$param = $_POST [$string];
		} else if (isset ( $_GET [$string] )) {
			$param = $_GET [$string];
		} else if (isset ( $_REQUEST [$string] )) {
			$param = $_REQUEST [$string];
		}
		return $param;
	}
	
	/**
	 * Add an admin notice.
	 *
	 * @param unknown $message        	
	 */
	public function addAdminNotice($message) {
		$messages = $this->getAdminNotices ();
		if (! $messages) {
			$messages = array ();
		}
		$messages [] = $message;
		set_transient ( 'worldpayus_admin_notices', $messages );
	}
	
	/**
	 * Method that retrieves the admin notices stored as transients.
	 */
	public function getAdminNotices() {
		return get_transient ( 'worldpayus_admin_notices' );
	}
	/**
	 * Delete the transient value for the admin messages.
	 */
	public function deleteAdminNotices() {
		delete_transient ( 'worldpayus_admin_notices' );
	}
}

/**
 * Helper Function that returns an instance of WP_Manager.
 */
function WP_Manager() {
	return WP_Manager::instance ();
}

add_action ( 'init', 'WP_Manager::register_postType', 99 );
?>