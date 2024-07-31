<?php
/**
 * File name    :   sw-product-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file for SmartWoo_Product
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Deletes or moves the sw_product type to trash.
 */
add_action( 'wp_ajax_smartwoo_delete_product', 'smartwoo_delete_product' );

function smartwoo_delete_product() {
	// Verify the nonce.
	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security' ) ) {
		wp_die( -1, 403 );
	}
	// Check if the user is logged in and has the necessary capability.
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Permission denied.' );
	}

	// Get the product ID from the AJAX request.
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( 0 === $product_id ) {
		wp_send_json_error( array( 'message' => 'Error deleting the product.' ) );
		wp_die( -1, 404 );
	}

	$result = wp_trash_post( $product_id );

	// Check if the product is successfully moved to trash.
	if ( $result ) {
		wp_send_json_success( array( 'message' => 'Product deleted successfully.' ) );
	} else {
		wp_send_json_error( array( 'message' => 'Error deleting the product.' ) );
	}

	wp_die();
}

/**
 * The text for SmartWoo_Product type in the shop page
 */

function smartwoo_product_text_on_shop() {	
	$pruduct_text = get_option( 'smartwoo_product_text_on_shop', 'Configure' );
	return apply_filters( 'smartwoo_product_text_on_shop', $pruduct_text );
}

/**
 * Get url to the admin product page endpoints.
 * 
 * @param $action The action that determines which page to load.
 * @param $product_id	The ID of product the action is being performed on.
 * @return string $page	URL of the matching page, defaults to the product dashboard.
 * @since 2.0.0
 */
function smartwoo_admin_product_url( $action = '', $product_id = 0 ) {
	$allowed_actions 	= array( 'add-new', 'edit' );
	$page				= admin_url( 'admin.php?page=sw-products' );

	if ( ! in_array( $action, $allowed_actions, true ) ) {
		return $page;
	}

	if ( 'add-new' === $action ) {
		$page = $page. '&action=' . $action;
	} elseif ( 'edit' === $action && ! empty( $product_id ) ) {
		$page = $page. '&action=' . $action . '&product_id=' . $product_id;
	}

	return $page;
}
