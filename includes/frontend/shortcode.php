<?php
/**
 * File name    :   shortcode.php
 *
 * @author      :   Callistus
 * Description  :   Shortcode handler file
 */
 defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Central callback init function to load shortcodes
 */
function smartwoo_shortcodes_init() {

	/**
	 * Short code for invoice page, should be on
	 * the page where you choose as invoice page
	 */

	add_shortcode( 'smartwoo_invoice_page', 'smartwoo_invoice_shortcode' );

	/**
	 * Short code for Service Subscription page, should be on
	 * the page where you choose as Service page
	 */
	add_shortcode( 'smartwoo_service_page', 'smartwoo_service_shortcode' );

	/**
	 * Display an integer value of active Services
	 */
	add_shortcode( 'smartwoo_active_service_count', 'smartwoo_active_service_count_shortcode' );
	/**
	 * Display an integer value of unpaid invoices
	 */
	add_shortcode( 'smartwoo_unpaid_invoices_count', 'smartwoo_get_unpaid_invoices_count' );

	/**
	 * Display the service subscriptions of the current user in a mini card
	 */
	add_shortcode( 'smartwoo_service_mini_card', 'smartwoo_service_mini_card' );

	/**
	 * Display all invoices of the current user in a mini card
	 */

	add_shortcode( 'smartwoo_invoice_mini_card', 'smartwoo_invoice_mini_card' );

	/**
	 * Displays an integer value of all invoice payment statuses
	 */

	add_shortcode( 'smartwoo_invoice_status_counts', 'smartwoo_all_user_invoices_count' );

	/**
	 * This line is for All transactions shortcodes
	 */

	add_shortcode( 'smartwoo_pending_transactions_count', 'smartwoo_get_pending_transactions_count' );
	add_shortcode( 'smartwoo_transactions', 'smartwoo_transactions_shortcode' );
	add_shortcode( 'smartwoo_transaction_status', 'smartwoo_transaction_status_shortcode' );

	/**
	 * @since 2.0.14 Added new shortcode for login form
	 */
	add_shortcode( 'smartwoo_login_form', 'smartwoo_render_login_form' );

}
add_action( 'init', 'smartwoo_shortcodes_init' );

/**
 * Callback function for login form shortcode.
 */
function smartwoo_render_login_form( $atts ) {
	$atts = shortcode_atts( array(
		'notice'		=> '',                     
		'redirect_url' 	=> get_permalink(),
	), $atts, 'smartwoo_login_form' );

	// Form is hidded when user is logged in
	if ( is_user_logged_in() ) {
		return '';
	}

	// Prepare options for the login form
	$options = array(
		'notice'   => ! empty( $atts['notice'] ) ? smartwoo_notice( esc_html( $atts['notice'] ) ): esc_html( $atts['notice'] ),
		'redirect' => esc_url( $atts['redirect_url'] ),
	);

	// Return the login form with the provided options
	return smartwoo_login_form( $options );
}