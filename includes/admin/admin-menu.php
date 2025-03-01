<?php
/**
 * File name   : admin-menu.php
 * Author      : Callistus
 * Description : Function definition file for admin menus
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

require_once SMARTWOO_PATH . 'includes/admin/admin-callback-functions.php';
require_once SMARTWOO_PATH . 'includes/sw-service/sw-service-admin-temp.php';
require_once SMARTWOO_PATH . 'includes/sw-orders/contr.php';
require_once SMARTWOO_PATH . 'includes/sw-service/sw-new-service-processing.php';
require_once SMARTWOO_PATH . 'includes/sw-invoice/sw-invoice-admin-temp.php';
require_once SMARTWOO_PATH . 'includes/sw-product/sw-product-admin-temp.php';
require_once SMARTWOO_PATH . 'includes/admin/sw-admin-settings.php';

/**
 * Defined function callback for admin menus.
 */
function smartwoo_reg_admin_menu() {	
	$dashboard = add_menu_page(
		'Smart Woo',
		'Smart Woo',
		'manage_options',
		'sw-admin',
		'smartwoo_service_admin_page',
		'dashicons-controls-repeat',
		58.5
	);

	$new_order_count = smartwoo_count_unprocessed_orders();
	// Add submenu "Service Orders".
	$service_order = add_submenu_page(
		'sw-admin',
		'Service Orders',
		! empty( $new_order_count ) ? 'Service Orders <span class="awaiting-mod">' . $new_order_count . '</span>': 'Service Orders',
		'manage_options',
		'sw-service-orders',
		array( 'SmartWoo_Order_Controller', 'menu_controller' )
	);

	// Add submenu "Invoices".
	$invoices = add_submenu_page(
		'sw-admin',
		'Invoices',
		'Invoices',
		'manage_options',
		'sw-invoices',
		array( 'SmartWoo_Invoice_Controller', 'menu_controller' ),
	);

	// Add submenu "Service Products".
	$products = add_submenu_page(
		'sw-admin',
		'Service Products',
		'Service Products',
		'manage_options',
		'sw-products',
		'smartwoo_products_page'
	);

	// Add submenu "Settings".
	$options = add_submenu_page(
		'sw-admin',
		'General Settings',
		'Settings',
		'manage_options',
		'sw-options',
		'smartwoo_options_page'
	);

	add_action( 'load-' . $dashboard, 'smartwoo_help_screen' );
	add_action( 'load-' . $invoices, 'smartwoo_help_screen' );
	add_action( 'load-' . $service_order, 'smartwoo_help_screen' );
	add_action( 'load-' . $products, 'smartwoo_help_screen' );
	add_action( 'load-' . $options, 'smartwoo_help_screen' );

}

add_action( 'admin_menu', 'smartwoo_reg_admin_menu' );




/**
 * Submenu navigation button tab function
 *
 * @param array  $tabs         An associative array of tabs (tab_slug => tab_title).
 * @param string $title        The title of the current submenu page.
 * @param string $page_slug    The admin menu/submenu slug.
 * @param string $current_tab  The current tab parameter for the submenu page.
 * @param string $query_var    The query variable.
 */
function smartwoo_sub_menu_nav( $tabs, $title, $page_slug, $current_tab, $query_var ) {
	$output  = '<div class="wrap">';
	$output .= '<h1 class="wp-heading-inline">' . wp_kses_post( $title ) . '</h1>';
	$output .= '<nav class="nav-tab-wrapper">';

	foreach ( $tabs as $tab_slug => $tab_title ) {
		$active_class = ( $current_tab === $tab_slug ) ? 'nav-tab-active' : '';

		if ( '' === $tab_slug ) {
			$output      .= "<a href='" . esc_url( admin_url( 'admin.php?page=' . $page_slug ) ) . "' class='nav-tab $active_class'>$tab_title</a>";

		} else {
			$output      .= "<a href='" . esc_url( add_query_arg( $query_var, $tab_slug, admin_url( 'admin.php?page=' . $page_slug ) ) ) . "' class='nav-tab $active_class'>$tab_title</a>";

		}
	}

	$output .= '</nav>';
	$output .= '</div>';

	return $output;
}
/**
 * Add Help tab.
 */
function smartwoo_help_screen() {

	$screen = get_current_screen();

	$screen->add_help_tab( array(
        'id'	=> 'smartwoo_help',
        'title'	=> __( 'Support', 'smart-woo-service-invoicing' ),
		'callback' => 'smartwoo_help_container',
    ) );

	$screen->add_help_tab( array(
        'id'	=> 'smartwoo_bug_report',
        'title'	=> __('Bug Report', 'smart-woo-service-invoicing' ),
		'callback' => 'smartwoo_bug_report_container',
	) );

	$screen->add_help_tab( array(
        'id'	=> 'smartwoo_support',
        'title'	=> __('Support Our Work', 'smart-woo-service-invoicing' ),
		'callback' => 'smartwoo_support_our_work_container',
	) );
}
