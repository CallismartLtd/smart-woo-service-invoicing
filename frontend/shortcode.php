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
function sw_shortcode_init() {

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
	add_shortcode( 'sw_active_service_count', 'smartwoo_active_service_count_shortcode' );
	/**
	 * Display an integer value of unpaid invoices
	 */
	add_shortcode( 'unpaid_invoices_count', 'smartwoo_get_unpaid_invoices_count' );

	/**
	 * Display the service subscriptions of the current user in a mini card
	 */
	add_shortcode( 'sw_service_mini_card', 'smartwoo_service_mini_card' );

	/**
	 * Display all invoices of the current user in a mini card
	 */

	add_shortcode( 'sw_invoice_mini_card', 'smartwoo_invoice_mini_card' );

	/**
	 * Displays an integer value of all invoice payment statuses
	 */

	add_shortcode( 'sw_invoice_status_counts', 'smartwoo_all_user_invoices_count' );

	/**
	 * This line is for All transactions shortcodes
	 */

	add_shortcode( 'pending_transactions_count', 'smartwoo_get_pending_transactions_count' );
	add_shortcode( 'sw_transactions', 'smartwoo_transactions_shortcode' );
	add_shortcode( 'sw_transaction_status', 'smartwoo_transaction_status_shortcode' );
}
add_action( 'init', 'sw_shortcode_init' );
