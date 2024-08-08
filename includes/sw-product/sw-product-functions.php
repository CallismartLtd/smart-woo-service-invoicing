<?php
/**
 * File name    :   sw-product-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file for SmartWoo_Product
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

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
