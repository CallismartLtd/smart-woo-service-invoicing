<?php
/**
 * File name    :   callback.php
 * Description  callback function file for admin menu pages
 * 
 * @author Callistus.
 * @package SmartWooAdminPages.
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Main plugin admin page controller callback.
 */
function smartwoo_service_admin_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
	}

	$action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	switch ( $action ) {
		case 'process-new-service':
			echo wp_kses( smartwoo_process_new_service_order_page(), smartwoo_allowed_form_html() );
			break;

		case 'view-service':
			echo wp_kses_post( smartwoo_admin_view_service_details() );
			break;

		case 'add-new-service':
			echo wp_kses( smartwoo_new_service_page(), smartwoo_allowed_form_html() );

			break;

		case 'edit-service':
			echo wp_kses( smartwoo_edit_service_form(), smartwoo_allowed_form_html() );
			break;

		default:
			echo wp_kses_post( smartwoo_dashboard_page() );
			break;
	}
}

/**
 * Service order page.
 */
function smartwoo_service_orders() {
	
	$orders = smartwoo_get_configured_orders_for_service();
	echo wp_kses_post( smartwoo_service_order_table( $orders ) );
}

/**
 * Invoice admin invoice page
 */
function smartwoo_invoice_admin_page() {

	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	switch ( $tab ) {
		case 'add-new-invoice':
			echo wp_kses( smartwoo_new_invoice_page(), smartwoo_allowed_form_html() );
			break;

		case 'edit-invoice':
			echo wp_kses( smartwoo_edit_invoice_page(), smartwoo_allowed_form_html() );
			break;

		case 'invoice-by-status':
			echo wp_kses_post ( smartwoo_invoice_by_status_temp() );
			break;

		case 'view-invoice':
			echo wp_kses_post( smartwoo_view_invoice_page() );
			break;

		default:
			echo wp_kses_post( smartwoo_invoice_dashboard() );
			break;
	}
}


/**
 * Callback function for "Product" submenu page
 */
function smartwoo_products_page() {

	$action     = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$tabs = array(
		''        => 'Products',
		'add-new' => 'Add New',

	);

	smartwoo_sub_menu_nav( $tabs, 'Products', 'sw-products', $action, 'action' );

	// Handle different actions.
	switch ( $action ) {
		case 'add-new':
			include_once SMARTWOO_PATH . 'templates/sw-add-product.php';
			break;
		case 'edit':
			include_once SMARTWOO_PATH . 'templates/sw-edit-product.php';
			break;
		default:
			echo wp_kses_post( smartwoo_product_table() );
			break;
	}
}

/**
 * Callback controller for Settings Page
 */
function smartwoo_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// Check for URL parameters
	$action = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$tabs = array(
		''          => 'General',
		'business'  => 'Business',
		'invoicing' => 'Invoicing',
		'emails'    => 'Emails',
		'advanced'  => 'Advanced',

	);

	echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Settings', 'sw-options', $action, 'tab' ) );

	switch ( $action ) {
		case 'business':
			/**
			 * Renders Settings page for Business
			 *
			 * NB: All html returned by the output
			 * function are properly escaped.
			 */
			$output = smartwoo_service_options();
			echo wp_kses( $output, smartwoo_allowed_form_html() );
			break;

		case 'invoicing':
			/**
			 * Renders Settings page for Invoice
			 *
			 * NB: All html returned by the output
			 * function are properly escaped.
			 */
			$output = smartwoo_invoice_options();
			echo wp_kses( $output, smartwoo_allowed_form_html() );
			break;

		case 'emails':
			/**
			 * Renders Settings page for Emails
			 *
			 * NB: All html returned by the output
			 * function are properly escaped.
			 */
			$output = smartwoo_email_options();
			echo wp_kses( $output, smartwoo_allowed_form_html() );
			break;

		case 'advanced':
			/**
			 * Renders Settings page for Advanced
			 *
			 * NB: All html returned by the output
			 * function are properly escaped.
			 */
			$output = smartwoo_advanced_options();
			echo wp_kses( $output, smartwoo_allowed_form_html() );
			break;

		default:
			smartwoo_options_main_page();
			break;
	}
}


/**
 * Register post states for specific pages.
 *
 * This function adds custom post states to pages based on their IDs.
 * It is hooked into the 'display_post_states' filter.
 *
 * @param array   $post_states An array of post states.
 * @param WP_Post $post        The current post object.
 *
 * @return array Modified array of post states.
 */
function smartwoo_register_page_states( $post_states, $post ) {
	$service_page_id = absint( get_option( 'smartwoo_service_page_id' ) );
	$invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id' ) );

	if ( $post->ID === $service_page_id ) {
		$post_states[] = 'Service Subscription Page';
	}

	if ( $post->ID === $invoice_page_id ) {
		$post_states[] = 'Invoice Management Page';
	}

	return $post_states;
}
add_filter( 'display_post_states', 'smartwoo_register_page_states', 30, 2 );