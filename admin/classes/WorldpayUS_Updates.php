<?php
/**
 * Update class.
 * @author Payment Plugins
 * @since 1.0.5
 *
 */
class WorldpayUS_Updates {
	
	public static function init() {
		add_action ( 'admin_init', __CLASS__ . '::performUpdates' );
		add_action ( 'in_plugin_update_message-payment-worldpay-us/worldpay.php', array(__CLASS__, 'getUpdateNotice' ) );
	}
	public static function performUpdates() {
		$current_version = get_option ( 'worldpayus_version' );
		if (! $current_version) {
			$current_version = 0;
		}
		if ($current_version < WP_Manager ()->version) {
			foreach ( self::getUpdates () as $version => $update ) {
				if (version_compare ( $current_version, $version, '<' )) {
					include_once $update;
					self::updateVersion ( $version );
				}
			}
			WP_Manager ()->addAdminNotice ( array (
					'type' => 'success',
					'text' => sprintf ( __ ( 'Worldpay US: Thank you for updating to version %s', 'worldpayus' ), WP_Manager ()->version ) 
			) );
		self::updateVersion ( WP_Manager ()->version );
		}
	}
	/**
	 * Return an array of updates to be applied.
	 */
	private static function getUpdates() {
		return array (
				'1.0.5' => WORLDPAYUS_ADMIN . '/updates/update-1.0.5.php' 
		);
	}
	private static function updateVersion($version) {
		update_option ( 'worldpayus_version', $version );
	}
	
	/**
	 * Retrieve any update notices that should be displayed on the plugin update page.
	 */
	public static function getUpdateNotice($args) {
		
		$response = wp_safe_remote_get ( 'https://plugins.svn.wordpress.org/payment-worldpay-us/trunk/readme.txt' );
		if ($response instanceof WP_Error) {
			WP_Manager ()->log->writeToLog ( sprintf ( 'There was an error retrieving the update notices. %s', print_r ( $response, true ) ) );
		} else {
			$content = ! empty ( $response ['body'] ) ? $response ['body'] : '';
			self::parseUpdateNoticeContent ( $content );
		}
	}
	
	/**
	 * Parse the content for the update notice.
	 *
	 * @param string $content
	 *        	The content retrieved from the readme.txt file.
	 */
	public static function parseUpdateNoticeContent($content) {
		$pattern = '/==\s*Upgrade Notice\s*==\s*=\s*([0-9.]*)\s*=\s*(.*)/';
		if (preg_match ( $pattern, $content, $matches )) {
			$version = $matches [1];
			$notice = $matches [2];
			if (version_compare ( $version, WP_Manager ()->version, '>' )) {
				echo '<div class="wc_plugin_upgrade_notice">' . $notice . '</div>';
			}
		}
	}
}
WorldpayUS_Updates::init ();