<?php
/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Controller file for Invoice frontend
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

 /**
  * Callback function for invoice page shortcode
  */
function smartwoo_invoice_shortcode() {

	if ( ! is_user_logged_in() ) {
		return esc_html__( 'You must be logged in to view this page.' );
	}

	$current_user_id  	= get_current_user_id();
	$current_user     	= wp_get_current_user();
	$currentuseremail 	= $current_user->user_email;
	$url_param 			= isset( $_GET['invoice_page'] ) ? sanitize_key( $_GET['invoice_page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Switch based on the validated 'invoice_page' parameter.
	switch ( $url_param ) {
		case 'view_invoice':

			$invoice_page 	= smartwoo_invoice_details();
			$output 		= $invoice_page;
			break;

		case 'invoices_by_status':

			$invoice_by_status_page = smartwoo_invoices_by_status();
			$output = $invoice_by_status_page;
			break;

		default:

			$main_page = smartwoo_invoice_front_temp();
			$output    = $main_page;
			break;
	}

	// Return the output
	return wp_kses_post( $output );
}
