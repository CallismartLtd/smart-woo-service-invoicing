<?php

/**
 * Smart Woo Service Invoicing
 *
 * @package     PluginPackage
 * @author      Callistus Nwachukwu
 * @copyright   2023 Callismart Tech
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Smart Woo Service Invoicing
 * Description: Integrate powerful service subscriptions and invoicing directly into your online store.
 * Version: 1.0.0
 * Author: Callistus Nwachukwu
 * Author URI: https://callismart.com.ng/callistus
 * Plugin URI: https://callismart.com.ng/smart-woo
 * Requires at least: 6.3.2
 * Requires PHP: 7.0
 * Tested up to: 6.4.3
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: smart-woo-invoice
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define Plugin Constants
 */

// Plugin name as constant
define( 'SW_PLUGIN_NAME', 'Smart Woo Service Invoicing' );

if ( defined( 'SW_PLUGIN_NAME' ) ) {

	// SW_ABSPATH definition
	define( 'SW_ABSPATH', __DIR__ . '/' );

	// SW_DIR_URL definition
	$this_file  = SW_ABSPATH . 'smart-woo-invoice.php';
	$plugin_url = plugin_dir_url( $this_file );
	define( 'SW_DIR_URL', $plugin_url );

	// Load scource file
	require_once SW_ABSPATH . '/admin/include/src.php';
	add_action( 'admin_init', 'sw_check_woocommerce' );


	/**
	 * Load woocommerce before loading plugin dependencies
	 */

	add_action( 'woocommerce_loaded', 'sw_initialization' );

	if ( ! function_exists( 'sw_initialization' ) ) {

		function sw_initialization() {

			if ( class_exists( 'woocommerce' ) ) {
				/**
				 * WooComerce is active, action hook to load plugin files
				 */

				do_action( 'smart_woo_init' );

			}
		}
	}

	if ( ! function_exists( 'sw_activation' ) ) {

		function sw_activation() {
			// Flush permalink structure to load ours
			flush_rewrite_rules();

			// Load the db table file to have access to the properties
			include_once SW_ABSPATH . '/admin/include/sw-db.php';
			sw_plugin_db_schema();
		}
	}

	// Activation hook for the plugin
	register_activation_hook( __FILE__, 'sw_activation' );
}
