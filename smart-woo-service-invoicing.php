<?php
/**
 * Plugin Name: Smart Woo Service Invoicing
 * Description: Integrate powerful service subscriptions and invoicing directly into your online store.
 * Version: 1.0.3
 * Author: Callistus Nwachukwu
 * Author URI: https://callismart.com.ng/callistus
 * Plugin URI: https://callismart.com.ng/smart-woo
 * Requires at least: 6.0
 * Tested up to: 6.5.2
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.8.2
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: smart-woo-service-invoicing
 * 
 * @package SmartWoo
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

if ( ! defined( 'SMARTWOO' ) ) {
	define( 'SMARTWOO', 'Smart Woo Service Invoicing' );
}

if ( defined( 'SMARTWOO' ) ) {

	if ( ! defined( 'SMARTWOO_PATH' ) ) {
		/**
		 * Define The Smart Woo absolute path.
		 */
		define( 'SMARTWOO_PATH', __DIR__ . '/' );
	}	
	
	if ( ! defined( 'SMARTWOO_FILE' ) ) {
		/**
		 * Define Main plugin file.
		 */
		define( 'SMARTWOO_FILE', __FILE__ );
	}

	// Define the Smart Woo Directory URL
	if ( ! defined( 'SMARTWOO_DIR_URL' ) ) {
		define( 'SMARTWOO_DIR_URL', plugin_dir_url( __FILE__ ) );
	}	
	
	// Define the Smart Woo Directory URL
	if ( ! defined( 'SMARTWOO_VER' ) ) {
		define( 'SMARTWOO_VER', '1.0.2' );
	}

	if ( ! defined( 'SMARTWOO_DB_VER' ) ) {
		define( 'SMARTWOO_DB_VER', '1.0.2' );
	}
	
	// Define the database table names as constants.
	global $wpdb;
	define( 'SW_SERVICE_TABLE', $wpdb->prefix . 'sw_service' );
	define( 'SW_INVOICE_TABLE', $wpdb->prefix . 'sw_invoice' );
	define( 'SW_SERVICE_LOG_TABLE', $wpdb->prefix . 'sw_service_logs' );
	define( 'SW_INVOICE_LOG_TABLE', $wpdb->prefix . 'sw_invoice_logs' );

	// Load core and config files.
	require_once SMARTWOO_PATH . 'includes/class-sw-config.php';
	require_once SMARTWOO_PATH . 'includes/class-sw-install.php';
	SmartWoo_Config::instance();
}
