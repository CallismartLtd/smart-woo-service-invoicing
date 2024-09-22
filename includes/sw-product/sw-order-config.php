<?php
/**
 * File name    :   sw-order-config.php
 *
 * @author      :   Callistus
 * Description  :   This file defines the checkout flow of Smart Woo Product
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.


/**
 * Register a query variable for the configuration page
 *
 * @param string $vars  The query variable
 */
add_filter( 'query_vars', 'smartwoo_configure_query_var' );

function smartwoo_configure_query_var( $vars ) {

	$vars[] = 'configure';

	return $vars;
}

/**
 * Set up the configuration page template file.
 *
 * This function is a callback for the 'template_include' filter and returns
 * the template file path for the configure page or the original template.
 *
 * @param string $template The original template file path.
 * @return string The template file path for the configure page or the original template.
 */

 function smartwoo_template_for_configure_page( $template ) {
    // Check if the current page is the configure page.
    if ( get_query_var( 'configure' ) ) {
        // Define the path to the configure template file.
        $product_configure_temp = SMARTWOO_PATH . '/templates/configure.php';

        if ( file_exists( $product_configure_temp ) ) {
			function sw_configure_page_title( $title_parts ) {
				$title_parts['title'] = 'Product Configuration';
				return $title_parts;
			}
			add_filter( 'document_title_parts', 'sw_configure_page_title' );
			return $product_configure_temp;
        }
    }
    return $template;
}

/**
 * Ajax configure and add to cart function.
 */
function smartwoo_configure_product_for_checkout() {
	// Verify the nonce.
	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
		wp_send_json_error( array( 'message' => smartwoo_notice( 'Basic authentication failed, please refresh current page.' ) ) );
	}

	$validation_errors = array();
	$service_name	= isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
	if ( empty( $service_name ) ) {
		$validation_errors[] = 'Service Name is required to configure your subscription.';
	}

	$service_url	=	isset( $_POST['service_url'] ) ? sanitize_url(  wp_unslash( $_POST['service_url'] ), array( 'http', 'https' ) ) : '';
	if ( ! empty( $_POST['service_url'] ) && empty( $service_url ) ) {
		$validation_errors[] = 'Enter a valid website URL.';
	}

	$product_id		= isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( empty( $product_id ) ) {
		$validation_errors[] = 'Product ID could not be found.';

	}

	if ( ! empty( $validation_errors ) ) {
		wp_send_json_error( array( 'message' => smartwoo_error_notice( $validation_errors ) ) );

	}

	$cart_item_data = array(
		'service_name' => $service_name,
		'service_url'  => $service_url,
	);

	$cart = new WC_Cart();
	$cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );
	wp_send_json_success( array( 'checkout' => wc_get_checkout_url() ) );
}



/**
 * Display configured product data in cart and checkout.
 *
 * This function is hooked into 'woocommerce_get_item_data' to add custom data related to the
 * configured product for display in the cart and checkout.
 *
 * @param array $cart_data The existing cart item data.
 * @param array $cart_item The cart item being displayed.
 * @return array The modified cart item data with added service_name and service_url.
 */
add_filter( 'woocommerce_get_item_data', 'smartwoo_get_configured_data_from_cart', 10, 2 );

function smartwoo_get_configured_data_from_cart( $cart_data, $cart_item ) {

	if ( isset( $cart_item['service_name'] ) ) {
		$cart_data[] = array(
			'key'    => '<div class="sw-configured-product-container"><strong>' . __( 'Service Name', 'smart-woo-service-invoicing' ) . '</strong>',
			'display'   => '<span class="sw-configured-product">' . esc_html( $cart_item['service_name'] ) . '</span></div>',
		);
	}

	if ( isset( $cart_item['service_url'] ) ) {
		$cart_data[] = array(
			'name'    => '<div class="sw-configured-product-container"><strong>' . __( 'Service URL', 'smart-woo-service-invoicing' ) . '</strong>',
			'value'   => '<span class="sw-configured-product">' . esc_url( $cart_item['service_url'] ) . '</span></div>',
			'display' => '',
		);
	}

	return $cart_data;
}


/**
 * Configure the order with the data the customer provided during product configuration,
 * and save it to order item meta.
 *
 * This function is hooked into 'woocommerce_checkout_create_order_line_item' to add
 * custom meta data related to the configured product to the order item.
 *
 * @param WC_Order_Item_Product $item The order item.
 * @param string $cart_item_key The key of the cart item.
 * @param array $values The session data for the cart item.
 * @param WC_Order $order The order object.
 */
add_action( 'woocommerce_checkout_create_order_line_item', 'smartwoo_configure_the_order', 10, 4 );

function smartwoo_configure_the_order( $item, $cart_item_key, $values, $order ) {

	if ( isset( $values['service_name'] ) ) {
		$item->add_meta_data( 'Service Name', $values['service_name'], true );
	}

	// Check if 'sw_service_url' is set in the cart item data and add it to order item meta
	if ( isset( $values['service_url'] ) ) {
		$item->add_meta_data( 'Service URL', $values['service_url'], true );
	}
}