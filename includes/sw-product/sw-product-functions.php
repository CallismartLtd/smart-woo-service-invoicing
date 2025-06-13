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
		$page = $page. '&tab=' . $action . '&product_id=' . $product_id;
	}

	return $page;
}

/**
 * Dropdown for Smart Woo Product with filter for custom options.
 *
 * @param int $product_id The selected Product ID (optional).
 *
 * @since 1.0.0
 */
function smartwoo_product_dropdown( $selected_product_id = null, $required = false, $echo = true ) {
	
	$products = wc_get_products(
		array(
			'type'   => 'sw_product',
			'status' => 'publish',
		)
	);

	// Initialize the dropdown HTML.
	$dropdown_html = '<select class="sw-form-input" field-name="A service product" name="product_id" ' . ( $required ? 'required' : '' ) . ' id="service_products">';
	$dropdown_html .= '<option value="">Select Service Product</option>';

	foreach ( $products as $product ) {
		// Get the product ID and name
		$product_id   = $product->get_id();
		$product_name = $product->get_name();

		// Check if the current product is selected
		$selected = ( $product_id == $selected_product_id ) ? 'selected' : '';

		// Add the option to the dropdown
		$dropdown_html .= '<option value="' . esc_attr( $product_id ) . '" ' . $selected . '>' . esc_html( $product_name ) . '</option>';
	}

	$dropdown_html .= '</select>';

	if ( true === $echo ) {
		echo wp_kses( $dropdown_html, smartwoo_allowed_form_html() );
	} 
	return $dropdown_html;

}

/**
 * Get the frontend product configuration query variable.
 * 
 * @return string
 * @since 2.4.1
 */
function smartwoo_get_product_config_query_var() {
	$var = null;
	if( is_null( $var ) ) {
		$var = get_option( 'smartwoo_product_config_var', 'product-config' );
	}
	return $var;
}