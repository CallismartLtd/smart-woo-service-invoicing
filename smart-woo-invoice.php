<?php
/**
 * Plugin Name: Smart Woo Service Invoicing
 * Description: Integrate powerful service subscriptions and invoicing directly into your online store.
 * Version: 1.0.2
 * Author: Callistus Nwachukwu
 * Author URI: https://callismart.com.ng/callistus
 * Plugin URI: https://callismart.com.ng/smart-woo
 * Requires at least: 6.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.3
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: smart-woo-service-invoicing
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

// Plugin name as constant.
if ( ! defined( 'SMARTWOO' ) ) {

	define( 'SMARTWOO', 'Smart Woo Service Invoicing' );

}

if ( defined( 'SMARTWOO' ) ) {

	// Define The Smart Woo absolute path.
	if ( ! defined( 'SW_ABSPATH' ) ) {

		define( 'SW_ABSPATH', __DIR__ . '/' );
	}

	// Define the Smart Woo Directory URL
	if ( ! defined( 'SMARTWOO_DIR_URL' ) ) {
		define( 'SMARTWOO_DIR_URL', plugin_dir_url( __FILE__ ) );
	}
	
	// Define the database table names as constants.
	global $wpdb;
	define( 'SW_SERVICE_TABLE', $wpdb->prefix . 'sw_service' );
	define( 'SW_INVOICE_TABLE', $wpdb->prefix . 'sw_invoice' );
	define( 'SW_SERVICE_LOG_TABLE', $wpdb->prefix . 'sw_service_logs' );
	define( 'SW_INVOICE_LOG_TABLE', $wpdb->prefix . 'sw_invoice_logs' );

	// Load scource file
	require_once SW_ABSPATH . '/admin/include/src.php';
	add_action( 'admin_init', 'smartwoo_check_woocommerce' );


	/**
	 * Load woocommerce before loading plugin dependencies
	 */

	add_action( 'woocommerce_loaded', 'sw_initialization' );

	if ( ! function_exists( 'sw_initialization' ) ) {

		function sw_initialization() {

			if ( class_exists( 'WooCommerce' ) ) {
				/**
				 * WooComerce is active, action hook to load plugin files
				 */
				
				add_action( 'before_woocommerce_init', function() {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
					}
				} );
				do_action( 'smartwoo_init' );

			}
		}
	}

	/**
	 * The activation function
	 */
	if ( ! function_exists( 'sw_activation' ) ) {

		function sw_activation() {

			// Load the db table file to have access to the properties
			include_once SW_ABSPATH . 'admin/include/sw-db.php';
			
			// Trigger action hook to allow us perform extra actions
			do_action( 'smart_woo_activation' );

			// Creates Database table
			smartwoo_db_schema();
			// Reset and recreate rewrite rules
			flush_rewrite_rules();
			

		}
	}

	// Activation hook for the plugin
	register_activation_hook( __FILE__, 'sw_activation' );

	/**
	 * Function to run when deactivating plugin
	 */
	if ( ! function_exists( 'sw_deactivation' ) ) {
		function sw_deactivation() {
			
		}
	}
	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	
}
