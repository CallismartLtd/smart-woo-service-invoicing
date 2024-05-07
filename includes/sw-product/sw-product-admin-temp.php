<?php
/**
 * File name sw-product-admin-temp.php
 * Description  Admin Template file
 *
 * @author  Callistus
 * @package SmartWooTemplates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Display a table of  products.
 */
function smartwoo_product_table() {

	$products_data 	= SmartWoo_Product::get_all_products();
	$page_html 		= '<div class="wrap"><h2>Service Products</h2>';

	// Check if there are any products
	if ( ! $products_data ) {
		$page_html .= smartwoo_notice( 'No Service product found.' );
		return $page_html;
	}

	/**
	 * Start table markep.
	 */
	$page_html .= '</div>';

	$page_html .= '<table class="sw-table">';
	$page_html .= '<thead><tr>';
	$page_html .= '<th>Product</th>';
	$page_html .= '<th>Product Price</th>';
	$page_html .= '<th>Sign Up Fee</th>';
	$page_html .= '<th>Billing Circle</th>';
	$page_html .= '<th>Action</th>';
	$page_html .= '</tr></thead>';
	$page_html .= '<tbody>';

	foreach ( $products_data as $product ) {
		$page_html .= '<tr>';
		$page_html .= '<td>' . esc_html( $product->get_name() ) . '</td>';
		$page_html .= '<td>' . wc_price( $product->get_price() ) . '</td>';
		$page_html .= '<td>' . wc_price( $product->get_sign_up_fee() ) . '</td>';
		$page_html .= '<td>' . esc_html( $product->get_billing_cycle() ) . '</td>';
		$page_html .= '<td>';
		$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-products&action=edit&product_id=' . $product->get_id() ) ) . '" class="button">Edit</a>';
		$page_html .= '<button class="sw-delete-product" data-product-id="'. esc_attr( $product->get_id() ) . '">' . __( 'Delete', 'smart-woo-service-invoicing' ) . '</button>';
		$page_html .= '</td>';
		$page_html .= '</tr>';
	}

	$page_html .= '</tbody></table>';
	$page_html .= '<p style="text-align: right;">' . count( $products_data ) . ' items</p>';

	return $page_html;
}