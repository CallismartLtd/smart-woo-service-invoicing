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
			include_once SMARTWOO_PATH . 'templates/service-admin-temp/add-service.php';
			break;

		case 'edit-service':
			echo wp_kses( smartwoo_edit_service_form(), smartwoo_allowed_form_html() );
			break;

		default:
			smartwoo_dashboard_page();
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

	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	switch ( $tab ) {
		case 'add-new-invoice':
			include_once SMARTWOO_PATH . 'templates/invoice-admin-temp/add-invoice.php';
			break;

		case 'edit-invoice':
			SmartWoo_Invoice_Form_Controller::edit_form();
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

	echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Products', 'sw-products', $action, 'action' ) );

	// Handle different actions.
	switch ( $action ) {
		case 'add-new':
			include_once SMARTWOO_PATH . 'templates/product-admin-temp/sw-add-product.php';
			break;
		case 'edit':

			$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				
			if ( empty( $product_id ) ) {
				echo wp_kses_post( smartwoo_error_notice( 'Product ID Parameter must not be manipulated' ) );
				return;
			}
				
			$product_data = wc_get_product( $product_id );

			if ( empty( $product_data ) ) {
				echo wp_kses_post( smartwoo_error_notice( 'You are trying to edit a product that doesn\'t exist, maybe it has been deleted' ) );
				return;
			}

			if ( ! $product_data instanceof SmartWoo_Product ) {
				echo wp_kses_post( smartwoo_error_notice( 'This is not a service product' ) );
				return;
			}

			$is_downloadable = $product_data->is_downloadable();
			include_once SMARTWOO_PATH . 'templates/product-admin-temp/sw-edit-product.php';
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
	$default_tabs = array(
		''          => 'General',
		'business'  => 'Business',
		'invoicing' => 'Invoicing',
		'emails'    => 'Emails',
		'advanced'  => 'Advanced',

	);

	$more = apply_filters( 'smartwoo_options_tab', array() );
	$tabs = array_merge( $default_tabs, $more );
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
			smartwoo_email_options();
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
		if ( empty( $action ) ) {
			smartwoo_options_main_page();
		}else {
			do_action( 'smartwoo_options_' . $action . '_content') ;

		}
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