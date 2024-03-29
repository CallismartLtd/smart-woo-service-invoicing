<?php
/**
 * File name   : admin-menu.php
 * Author      : Callistus
 * Description : Function definition file for admin menus
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access // Prevent direct access // Prevent direct access

require_once SW_ABSPATH . 'admin/admin-callback-functions.php';
require_once SW_ABSPATH . 'includes/sw-service/sw-service-admin-temp.php';
require_once SW_ABSPATH . 'includes/sw-service/sw-new-service-processing.php';
require_once SW_ABSPATH . 'includes/sw-invoice/sw-invoice-admin-temp.php';
require_once SW_ABSPATH . 'includes/sw-product/sw-product-admin-temp.php';
require_once SW_ABSPATH . 'admin/sw-admin-settings.php';

/**
 * Defined function callback for admin menus.
 */
function sw_reg_admin_menu() {
	global $menu;
	
	add_menu_page(
		'Smart Woo',
		'Dashboard',
		'manage_options',
		'sw-admin',
		'smart_woo_service',
		'dashicons-controls-repeat',
		58.5
	);

	// Add submenu "Service Orders".
	add_submenu_page(
		'sw-admin',
		'Service Orders',
		'Service Orders',
		'manage_options',
		'sw-service-orders',
		'sw_render_order_for_sw_products'
	);

	// Add submenu "Invoices".
	add_submenu_page(
		'sw-admin',
		'Invoices',
		'Invoices',
		'manage_options',
		'sw-invoices',
		'sw_invoices',
	);

	// Add submenu "Service Products".
	add_submenu_page(
		'sw-admin',
		'Service Products',
		'Service Products',
		'manage_options',
		'sw-products',
		'sw_products_page'
	);

	// Add submenu "Settings".
	add_submenu_page(
		'sw-admin',
		'Settings',
		'Settings',
		'manage_options',
		'sw-options',
		'sw_options_page'
	);

    foreach ( $menu as $index => $data ) {
        if ( $data[2] === 'sw-admin' ) {
            $menu[$index][0] = 'Smart Woo';
            break;
        }
    }
}

add_action( 'admin_menu', 'sw_reg_admin_menu' );




/**
 * Submenu navigation button tab function
 *
 * @param array  $tabs         An associative array of tabs (tab_slug => tab_title).
 * @param string $title        The title of the current submenu page.
 * @param string $page_slug    The url slug of the current page.
 * @param string $current_tab  The current tab parameter for the submenu page.
 * @param string $query_var    The query variable.
 */
function sw_sub_menu_nav( $tabs, $title, $page_slug, $current_tab, $query_var ) {
	$output  = '<div class="wrap">';
	$output .= '<h1 class="wp-heading-inline">' . esc_attr( $title ) . '</h1>';
	$output .= '<nav class="nav-tab-wrapper">';

	foreach ( $tabs as $tab_slug => $tab_title ) {
		
		$active_class = ( $current_tab === $tab_slug ) ? 'nav-tab-active' : '';
		$output      .= "<a href='" . esc_url( add_query_arg( $query_var, $tab_slug, admin_url( 'admin.php?page=' . $page_slug ) ) ) . "' class='nav-tab $active_class'>$tab_title</a>";
	}

	$output .= '</nav>';
	$output .= '</div>';

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Upsell Container card
 */
function sw_generate_upsell_card() {
	echo '<div id="help" class="sw-accordion-container">';
	echo sw_support_our_work_container();
	echo sw_bug_report_container();
	echo sw_help_container();
	echo '</div>';
}