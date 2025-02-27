<?php
/**
 * File name sw-new-service-processing.php
 * Handles new service processing from the admin area
 * 
 * @author	Callistus.
 * @package	SmartWooService
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
 


/**
 * Conversion of WooCommerce Order to Smart Woo Service subscription
 *
 * IMPORTANT NOTE: Before calling this function, ensure that the order is of the 'sw_product' type
 * Failure to verify the order type may lead to unexpected behavior
 *
 * @see Helper Function: smartwoo_check_if_configured()
 * This function can be used to check if the order contains configured 'sw_product' types.
 *
 * @param int $order_id Order ID of the new service order.
 * @deprecated This function has been deprecated @since 2.3.0.
 */
function smartwoo_convert_wc_order_to_smartwoo_service( $order_id ) {
	smartwoo_set_document_title( 'Process Orders' );
	// Get order details and user data.
	$order          = wc_get_order( $order_id );
	$user_id        = $order->get_user_id();
	$user_info      = get_userdata( $user_id );
	$user_full_name = $order ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : 'Not Found';

	// Get the configured data from order item meta data.
	$items = $order->get_items();
	foreach ( $items as $item_id => $item ) {
		$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
		$service_url  = wc_get_order_item_meta( $item_id, 'Service URL', true );
	}

	// Convert order paid date to Service subscription start date.
	$start_date 		= $order->get_date_paid() ? date_i18n( 'Y-m-d', strtotime( $order->get_date_paid() ) ) : date_i18n( 'Y-m-d' );
	$billing_cycle     	= '';
	$next_payment_date 	= '';
	$end_date          	= '';
	$status            	= 'Pending';

	if ( ! empty( $items ) ) {

		// Retrieve billing_cycle from the product in the order.
		$first_item = reset( $items ); // Get the first item( Will be extended).
		$product_id = $first_item->get_product_id();

		// Fetch the billing cycle from product metadata.
		$billing_cycle = get_post_meta( $product_id, '_smartwoo_billing_cycle', true );

		// Set next payment date and end date based on billing cycle.
		switch ( $billing_cycle ) {
			case 'Monthly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 month' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Quarterly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +3 months' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Six Monthly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +6 months' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;

			case 'Yearly':
				$end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 year' ) );
				$next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
				break;
			default:
				break;
		}
	}

	$product			= wc_get_product( $product_id );
	$GLOBALS['product']	= $product;
	$product_name		= $product->get_name();
	$status_options 	= array(
		''					=> esc_html__( 'Auto Calculate', 'smart-woo-service-invoicing' ),
		'Pending'			=> esc_html__( 'Pending', 'smart-woo-service-invoicing' ),
		'Active (NR)'		=> esc_html__( 'Active (NR)', 'smart-woo-service-invoicing' ),
		'Suspended'			=> esc_html__( 'Suspended', 'smart-woo-service-invoicing' ),
		'Due for Renewal'	=> esc_html__( 'Due for Renewal', 'smart-woo-service-invoicing' ),
		'Expired'			=> esc_html__( 'Expired', 'smart-woo-service-invoicing' ),
	);

	$is_downloadable	= $product->is_downloadable();

	// The form.
	ob_start();
	include_once SMARTWOO_PATH . 'templates/service-admin-temp/new-service-order-form.php';
	return ob_get_clean();
}
