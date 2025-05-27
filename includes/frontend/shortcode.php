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
	 * Short code for invoice page.
	 */

	add_shortcode( 'smartwoo_invoice_page', array( 'SmartWoo_Invoice_Frontend_Template', 'shortcode_handler' ) );

	/**
	 * Short code for Service Subscription page.
	 */
	add_shortcode( 'smartwoo_service_page', array( 'SmartWoo_Service_Frontend_Template', 'shortcode_handler' ) );

	/**
	 * Display the service subscriptions of the current user in a mini card
	 */
	add_shortcode( 'smartwoo_service_mini_card', array( 'SmartWoo_Service_Frontend_Template', 'mini_card' ) );

	/**
	 * Display all invoices of the current user in a mini card
	 */

	add_shortcode( 'smartwoo_invoice_mini_card', 'smartwoo_invoice_mini_card' );

	/**
	 * Displays an integer value of all invoice payment statuses
	 */

	add_shortcode( 'smartwoo_invoice_status_counts', 'smartwoo_all_user_invoices_count' );

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
	$atts = shortcode_atts( 
		array(
			'notice'		=> '',                     
			'redirect_url' 	=> get_permalink(),
		),
		$atts, 
		'smartwoo_login_form'
	);

	if ( is_user_logged_in() ) {
		return '';
	}

	$options = array(
		'notice'   => ! empty( $atts['notice'] ) ? smartwoo_notice( esc_html( $atts['notice'] ) ): esc_html( $atts['notice'] ),
		'redirect' => esc_url( $atts['redirect_url'] ),
	);

	return smartwoo_login_form( $options );
}