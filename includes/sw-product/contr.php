<?php
/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Controller file for SmartWoo_Product
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.
 
/**
 * Controls the new service product creation form submission
 */
function smartwoo_process_new_product() {

    if ( isset( $_POST['create_sw_product'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_add_new_product_nonce'] ) ), 'sw_add_new_product_nonce' ) ) {
		
		$new_product            = new SmartWoo_Product();
        $product_name           = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $product_price          = isset( $_POST['product_price'] ) ? floatval( $_POST['product_price'] ) : 0;
        $sign_up_fee            = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $short_description      = isset( $_POST['short_description'] ) ? wp_kses_post( $_POST['short_description'] ) : '';
        $description            = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';
        $billing_cycle          = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
        $grace_period_unit      = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( $_POST['grace_period_unit'] ) : '';
        $grace_period_number    = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
        $product_image_id       = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;

		// Validation.
		$validation_errors = array();

		if ( empty( $product_name ) ) {
			$validation_errors[] = 'Product Name is required';
		}

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $product_name ) ) {
			$validation_errors[] = 'Product name should only contain letters, and numbers.';
		}

		if ( ! empty( $validation_errors ) ) {
			// Display validation errors using the custom error notice function
			return smartwoo_error_notice( $validation_errors );

		}

		$new_product->set_name( sanitize_text_field( $product_name ) );
        $new_product->set_regular_price( floatval( $product_price ) );
        $new_product->add_sign_up_fee( floatval( $sign_up_fee ) );
        $new_product->set_short_description( wp_kses_post( $short_description ) );
        $new_product->set_description( wp_kses_post( $description ) );
        $new_product->add_billing_cycle( sanitize_text_field( $billing_cycle ) );
        $new_product->add_grace_period_unit( sanitize_text_field( $grace_period_unit ) );
        $new_product->add_grace_period_number( absint( $grace_period_number ) );
        $new_product->set_image_id( $product_image_id );

        $result = $new_product->save();

		if ( is_wp_error( $result ) ) {
			return smartwoo_error_notice( $result->get_error_message() );
		}

		// Show success message with product links
		$product_link = get_permalink( $result->get_id() );
		$edit_link    = admin_url( 'admin.php?page=sw-products&action=edit&product_id=' . $result->get_id() );
		$success = '<div class="notice notice-success is-dismissible"><p>New product created successfully! View your product <a href="' . esc_url( $product_link ) . '" target="_blank">here</a>.</p>
		<p>Edit the product <a href="' . esc_url( $edit_link ) . '">here</a>.</p></div>';
		echo wp_kses_post( $success );
		
	}
}

function smartwoo_process_product_edit( $product_id ) {
    // Handle form submission for updating the product
    if ( isset( $_POST['update_service_product'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_product_nonce'] ) ), 'sw_edit_product_nonce' ) ) {
        
        $update                 = new SmartWoo_Product( $product_id );
        $product_name           = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $product_price          = isset( $_POST['product_price'] ) ? floatval( $_POST['product_price'] ) : 0;
        $sign_up_fee            = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $short_description      = isset( $_POST['short_description'] ) ? wp_kses_post( $_POST['short_description'] ) : '';
        $description            = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : '';
        $billing_cycle          = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
        $grace_period_unit      = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( $_POST['grace_period_unit'] ) : '';
        $grace_period_number    = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
        $product_image_id       = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;

		$validation_errors = array();

		if ( empty( $product_name ) ) {
			$validation_errors[] = 'Product Name is required';
		}

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $product_name ) ) {
			$validation_errors[] = 'Product name should only contain letters, and numbers.';
		}

		if ( ! empty( $validation_errors ) ) {

			return smartwoo_error_notice( $validation_errors );

		}

        $update->set_name( sanitize_text_field( $product_name ) );
        $update->set_regular_price( floatval( $product_price ) );
        $update->update_sign_up_fee( floatval( $sign_up_fee ) );
        $update->set_short_description( wp_kses_post( $short_description ) );
        $update->set_description( wp_kses_post( $description ) );
        $update->update_billing_cycle( sanitize_text_field( $billing_cycle ) );
        $update->update_grace_period_unit( sanitize_text_field( $grace_period_unit ) );
        $update->update_grace_period_number( absint( $grace_period_number ) );
        $update->set_image_id( $product_image_id );

        $result = $update->save();

        if ( ! is_wp_error( $result ) ) {
            return smartwoo_notice( 'Product updated successfully!', true );
        } else {
            return smartwoo_error_notice( 'Error updating product: ' . $result->get_error_message() );
        }
    }
}

