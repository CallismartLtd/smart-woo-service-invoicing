<?php

/**
 * File name    :   sw-product-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file for Sw_Product
 */

/**
 * Dynamically assigns 'Sw_Product' class for 'sw_product' type in WooCommerce.
 *
 * @param string $classname   The current class name for the product.
 * @param string $product_type The type of the product being processed.
 * @return string             The updated class name based on the product type.
 */
add_filter( 'woocommerce_product_class', 'sw_product', 10, 2 );

function sw_product( $classname, $product_type ) {
	if ( $product_type === 'sw_product' ) {
		$classname = 'Sw_Product';
	}
	return $classname;
}


/**
 * Deletes or moves the sw_product type to trash
 */
// Add AJAX action for deleting service product
add_action( 'wp_ajax_delete_sw_product', 'delete_sw_product' );

function delete_sw_product() {
	// Verify the nonce
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'delete_service_product_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Invalid nonce.' ) );
	}

	// Check if the user is logged in and has the necessary capability
	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Permission denied.' ) );
	}

	// Get the product ID from the AJAX request
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	// Check if the product ID is valid
	if ( $product_id ) {
		// Move the product to trash
		$result = wp_trash_post( $product_id );

		// Check if the product is successfully moved to trash
		if ( $result ) {
			wp_send_json_success( array( 'message' => 'Product deleted successfully.' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Error deleting the product.' ) );
		}
	} else {
		wp_send_json_error( array( 'message' => 'Invalid product ID.' ) );
	}
	// Prevent further output
	wp_die();
}



/**
 * Display sign-up fee and billing cycle on single product page
 */

// Hook to display details under the main product price
add_action( 'woocommerce_single_product_summary', 'display_sw_service_product_details', 8 );

function display_sw_service_product_details() {
	global $product;

	// Check if the product is of type 'sw_product'
	if ( $product && $product->get_type() === 'sw_product' ) {
		// Get the sign-up fee and billing cycle
		$sign_up_fee   = get_post_meta( $product->get_id(), 'sign_up_fee', true );
		$billing_cycle = get_post_meta( $product->get_id(), 'billing_cycle', true );

		// Display main product price with billing cycle
		echo '<h4 class="main-price"> You will be charged  ' . wc_price( $product->get_price() ) . ' ' . ucfirst( $billing_cycle ) . '</h4>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Display sign-up fee if applicable
		if ( $sign_up_fee > 0 ) {
			echo '<h5 class="sign-up-fee">and one-time sign-up fee of ' . wc_price( $sign_up_fee ) . '</h5>';

			// Calculate the total price (product price + sign-up fee)
			$total_price = $product->get_price() + $sign_up_fee;

			// Temporarily set the display price for HTML without changing the internal product price
			$product->set_price( $total_price );
		}
	}
}


/**
 * Calculate the sum of sign-up fee and product price and set as cart subtotal
 *
 * @param object $cart the woocommerce cart object
 * @return null stops execution when cart is empty
 */
add_action( 'woocommerce_cart_calculate_fees', 'sw_calculate_sign_up_fee_cart_totals' );

function sw_calculate_sign_up_fee_cart_totals( $cart ) {
	if ( $cart->is_empty() ) {
		return;
	}

	// Initialize total sign-up fee
	$total_sign_up_fee = 0;

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		// Check if the product is of type 'sw_product'
		$product = $cart_item['data'];

		if ( $product && 'sw_product' === $product->get_type() ) {
			// Get the sign-up fee
			$sign_up_fee = get_post_meta( $product->get_id(), 'sign_up_fee', true );

			// Accumulate sign-up fee
			$total_sign_up_fee += $sign_up_fee;

			// Set the sum of sign-up fee and product price as cart subtotal
			$cart->set_subtotal( $cart->get_subtotal() + $total_sign_up_fee );

			// Add total sign-up fee to cart total
			$cart->add_fee( 'Sign-up Fee', $total_sign_up_fee );
		}
	}
}

/**
 * Make sw_product type to be purchasable
 */

add_filter( 'woocommerce_is_purchasable', 'make_sw_product_purchasable', 10, 2 );

function make_sw_product_purchasable( $purchasable, $product ) {
	// Check if the product type is 'sw_product'
	if ( $product->is_type( 'sw_product' ) ) {
		$purchasable = true; // Set the product as purchasable
	}
	return $purchasable;
}

/**
 * The text for SW_Product type in the shop page
 */
add_filter( 'woocommerce_product_add_to_cart_text', 'sw_product_text_on_shop', 10, 2 );

function sw_product_text_on_shop( $text, $product ) {
	// Check if the product is of a specific type
	if ( 'sw_product' === $product->get_type() ) {
		$pruduct_text = get_option( 'sw_product_text_on_shop', 'View Product' );
		$text = __( $pruduct_text, 'smart-woo-invoice' );
	}

	return $text;
}

/**
 * Retrieve data for a sw_service product.
 *
 * @param int|null $product_id           The ID of the sw_service product. Default is null.
 * @param string   $get_name             Optional. Retrieve the product name.
 * @param string   $get_price            Optional. Retrieve the product price.
 * @param float    $get_sign_up_fee      Optional. Retrieve the sign-up fee.
 * @param string   $get_short_description Optional. Retrieve the short description.
 * @param string   $get_billing_cycle   Optional. Retrieve the billing circle.
 * @param int      $get_grace_period_number Optional. Retrieve the grace period number.
 * @param string   $get_grace_period_unit Optional. Retrieve the grace period unit.
 * @param string   $get_long_description Optional. Retrieve the long description.
 * @param string   $get_product_image    Optional. Retrieve the product image URL.
 *
 * @return array|false Array of product data or false if product not found.
 */
function get_sw_service_product( $product_id = null, $get_name = null, $get_price = null, $get_sign_up_fee = null, $get_short_description = null, $get_billing_cycle = null, $get_grace_period_number = null, $get_grace_period_unit = null, $get_long_description = null, $get_product_image = null ) {
	// Check if $product_id is not provided or set to null
	if ( $product_id === null ) {

		// Fetch all products of type "sw_service and sw_service_product"
		$products = wc_get_products(
			array(
				'type'   => 'sw_product',
				'status' => 'publish',
			)
		);

		// Initialize an array to store product data
		$all_products_data = array();

		// Loop through each product
		foreach ( $products as $product ) {
			// Get the product ID
			$current_product_id = $product->get_id();

			// Retrieve and store product data
			$current_product_data                     = get_sw_service_product( $current_product_id, $get_name, $get_price, $get_sign_up_fee, $get_short_description, $get_billing_cycle, $get_grace_period_number, $get_grace_period_unit, $get_long_description, $get_product_image );
			$all_products_data[ $current_product_id ] = $current_product_data;
		}

		return $all_products_data;
	}
	// Get the product
	$product = wc_get_product( $product_id );

	// Check if the product is valid
	if ( ! $product ) {
		return false;
	}

	// Initialize an array to store the product data
	$product_data = array();

	// Retrieve specific fields if requested
	if ( $get_name !== null ) {
		$product_data['name'] = $product->get_name();
	}

	if ( $get_price !== null ) {
		$product_data['price'] = $product->get_price();
	}

	if ( $get_sign_up_fee !== null ) {
		$product_data['sign_up_fee'] = get_post_meta( $product_id, 'sign_up_fee', true );
	}

	if ( $get_short_description !== null ) {
		$product_data['short_description'] = $product->get_short_description();
	}

	if ( $get_billing_cycle !== null ) {
		$product_data['billing_cycle'] = get_post_meta( $product_id, 'billing_cycle', true );
	}

	if ( $get_grace_period_number !== null ) {
		$product_data['grace_period_number'] = get_post_meta( $product_id, 'grace_period_number', true );
	}

	if ( $get_grace_period_unit !== null ) {
		$product_data['grace_period_unit'] = get_post_meta( $product_id, 'grace_period_unit', true );
	}

	if ( $get_long_description !== null ) {
		$product_data['long_description'] = $product->get_description();
	}

	if ( $get_product_image !== null ) {
		$product_data['product_image_id']  = get_post_thumbnail_id( $product_id );
		$product_data['product_image_url'] = $product_data['product_image_id'] ? wp_get_attachment_url( $product_data['product_image_id'] ) : '';
	}

	// If no specific fields are requested, return all product data
	if ( $get_name === null && $get_price === null && $get_sign_up_fee === null && $get_short_description === null && $get_billing_cycle === null && $get_grace_period_number === null && $get_grace_period_unit === null && $get_long_description === null && $get_product_image === null ) {
		$product_data['name']                = $product->get_name();
		$product_data['price']               = $product->get_price();
		$product_data['sign_up_fee']         = get_post_meta( $product_id, 'sign_up_fee', true );
		$product_data['short_description']   = $product->get_short_description();
		$product_data['billing_cycle']       = get_post_meta( $product_id, 'billing_cycle', true );
		$product_data['grace_period_number'] = get_post_meta( $product_id, 'grace_period_number', true );
		$product_data['grace_period_unit']   = get_post_meta( $product_id, 'grace_period_unit', true );
		$product_data['long_description']    = $product->get_description();
		$product_data['product_image_id']    = get_post_thumbnail_id( $product_id );
		$product_data['product_image_url']   = $product_data['product_image_id'] ? wp_get_attachment_url( $product_data['product_image_id'] ) : '';
	}
	return $product_data;
}




/**
 * Update sw_service product based on the submitted form data.
 *
 * @param int $product_id The ID of the product to update.
 *
 * @return bool True if the product is updated successfully, false otherwise.
 */
function update_sw_service_product( $product_id ) {
	// Additional validation and sanitization can be added here

	// Update the product data
	$updated = wp_update_post(
		array(
			'ID'           => $product_id,
			'post_title'   => sanitize_text_field( $_POST['product_name'] ),
			'post_status'  => 'publish',
			'post_content' => sanitize_textarea_field( $_POST['long_description'] ),
			'post_excerpt' => sanitize_text_field( $_POST['short_description'] ),
		)
	);

	wp_set_object_terms( $product_id, 'sw_product', 'product_type' );

	if ( $updated ) {
		// Update product meta data
		update_post_meta( $product_id, '_regular_price', floatval( $_POST['product_price'] ) );
		update_post_meta( $product_id, '_price', floatval( $_POST['product_price'] ) );
		update_post_meta( $product_id, 'sign_up_fee', floatval( $_POST['sign_up_fee'] ) );
		update_post_meta( $product_id, 'billing_cycle', sanitize_text_field( $_POST['billing_cycle'] ) );
		update_post_meta( $product_id, 'grace_period_number', absint( $_POST['grace_period_number'] ) );
		update_post_meta( $product_id, 'grace_period_unit', sanitize_text_field( $_POST['grace_period_unit'] ) );

		// Update the featured image
		$product_image_id = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;

		if ( $product_image_id ) {
			// Set the selected image as the featured image
			set_post_thumbnail( $product_id, $product_image_id );
		} else {
			// Remove the featured image if no image ID is provided
			delete_post_thumbnail( $product_id );
		}

		return true;
	}

	return false;
}
