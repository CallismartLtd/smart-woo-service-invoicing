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