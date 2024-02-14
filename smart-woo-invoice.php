<?php
/*
Smart Woo Service and Invoice is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Smart Woo Service and Invoice is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Smart Woo Service and Invoice. If not, see http://www.gnu.org/licenses/gpl-2.0.html.

*/
/**
 * Smart Woo Service and Invoice
 * @package     PluginPackage
 * @author      Callistus Nwachukwu
 * @copyright   2023 Callismart Tech
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Smart Woo Service and Invoice
 * Description: Revolutionise your WooCommerce experience with Smart Woo Service and Invoice! Seamlessly integrate powerful service subscriptions and invoicing directly into your online store.
 * Version: 1.0.0
 * Author: Callistus Nwachukwu
 * Author URI: https://callismart.com.ng/callistus
 * Plugin URI: https://callismart.com.ng/smart-woo
 * Requires at least: 6.3.2
 * Requires PHP: 7.0
 * Tested up to: 6.4.3
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-woo
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define Plugin Constants
 */

// Plugin name as constant
define( 'SW_PLUGIN_NAME', 'Smart Woo Service and Invoice' );

if ( defined( 'SW_PLUGIN_NAME' )  ) {
    
    // Define plugin path
    define( 'SW_ABSPATH', dirname( __FILE__ ) . '/' );
    
    // Define the plugin url
    $main_plugin_file = SW_ABSPATH . 'smart-woo-invoice.php';
    $plugin_url = plugin_dir_url( $main_plugin_file );
    define( 'SW_DIR_URL', $plugin_url );

    /**
     * Load woocommerce before loading plugin dependencies
     */

    add_action( 'woocommerce_loaded', 'sw_initialization' );

    if ( ! function_exists( 'sw_initialization' ) ) {

        function sw_initialization(){

            if ( class_exists( 'woocommerce' ) ) {
                /**
                 * WooComerce is active, action hook to load plugin files
                 */
                
                 require_once SW_ABSPATH . '/admin/include/src.php';

                do_action( 'smart_woo_init' );
                
            }
        }
    }

    if ( ! function_exists( 'sw_activation' ) ) {

        function sw_activation(){
            // Load the db table file to have access to the properties
            include_once SW_ABSPATH . '/admin/include/sw-db.php';
            sw_plugin_db_schema();
        }
    }
    
    // Activation hook for the plugin
    register_activation_hook( __FILE__, 'sw_activation' );
}



