<?php
/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Controller file for Sw_Product
 */

/**
 * Controls the new service product creation form submission
 */
function smartwoo_process_new_product() {
	// Handle form submission
	if ( isset( $_POST['create_sw_product'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_add_new_product_nonce'] ) ), 'sw_add_new_product_nonce' ) ) {
		// Validate the product name
		$product_name = sanitize_text_field( $_POST['product_name'] );

		// Validation
		$validation_errors = array();

		if ( empty( $product_name ) ) {
			$validation_errors[] = 'Product Name is required';
		}

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $product_name ) ) {
			$validation_errors[] = 'Product name should only contain letters, and numbers.';
		}

		if ( ! empty( $validation_errors ) ) {
			// Display validation errors using the custom error notice function
			smartwoo_error_notice( $validation_errors );

		} elseif ( empty( $validation_errors ) ) {

			// Create the product
			$product_id = wp_insert_post(
				array(
					'post_title'   => $product_name,
					'post_type'    => 'product',
					'post_status'  => 'publish',
					'post_content' => sanitize_textarea_field( $_POST['long_description'] ),
					'post_excerpt' => sanitize_text_field( $_POST['short_description'] ),
				)
			);

			if ( ! is_wp_error( $product_id ) ) {
				// Set product type
				wp_set_object_terms( $product_id, 'sw_product', 'product_type' );

				// Set regular price (main product price)
				update_post_meta( $product_id, '_regular_price', floatval( $_POST['product_price'] ) );
				update_post_meta( $product_id, '_price', floatval( $_POST['product_price'] ) );

				// Set sign-up fee (product metadata)
				$sign_up_fee = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
				update_post_meta( $product_id, 'sign_up_fee', $sign_up_fee );

				// Set billing circle (product metadata)
				$billing_cycle = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
				update_post_meta( $product_id, 'billing_cycle', $billing_cycle );

				// Set grace period (product metadata)
				$grace_period_number = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : 0;
				$grace_period_unit   = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( $_POST['grace_period_unit'] ) : '';
				update_post_meta( $product_id, 'grace_period_number', $grace_period_number );
				update_post_meta( $product_id, 'grace_period_unit', $grace_period_unit );

				// Set main product image (featured image)
				$product_image_id = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : 0;
				if ( $product_image_id ) {
					// Set the attached image as the featured image
					set_post_thumbnail( $product_id, $product_image_id );
				}

				// Show success message with product links
				$product_link = get_permalink( $product_id );
				$edit_link    = admin_url( 'admin.php?page=sw-products&action=edit&product_id=' . $product_id );
				echo '<div class="updated"><p>New product created successfully! View your product <a href="' . esc_url( $product_link ) . '" target="_blank">here</a>.</p>';
				echo '<p>Edit the product <a href="' . esc_url( $edit_link ) . '">here</a>.</p></div>';
			}
		}
	}
}

function smartwoo_process_product_edit( $product_id ) {
    // Handle form submission for updating the product
    if ( isset( $_POST['update_service_product'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_product_nonce'] ) ), 'sw_edit_product_nonce' ) ) {
        
        $update                 = new SW_Product( $product_id );
        $product_name           = isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '';
        $product_price          = isset( $_POST['product_price'] ) ? floatval( $_POST['product_price'] ) : 0;
        $sign_up_fee            = isset( $_POST['sign_up_fee'] ) ? floatval( $_POST['sign_up_fee'] ) : 0;
        $short_description      = isset( $_POST['short_description'] ) ? wc_sanitize_textarea( $_POST['short_description'] ) : '';
        $description            = isset( $_POST['description'] ) ? wc_sanitize_textarea( $_POST['description'] ) : '';
        $billing_cycle          = isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
        $grace_period_unit      = isset( $_POST['grace_period_unit'] ) ? sanitize_text_field( $_POST['grace_period_unit'] ) : '';
        $grace_period_number    = isset( $_POST['grace_period_number'] ) ? absint( $_POST['grace_period_number'] ) : '';
        $product_image_id       = isset( $_POST['product_image_id'] ) ? absint( $_POST['product_image_id'] ) : '';

        $update->set_name( sanitize_text_field( $product_name ) );
        $update->set_regular_price( floatval( $product_price ) );
        $update->update_sign_up_fee( floatval( $sign_up_fee ) );
        $update->set_short_description( wc_sanitize_textarea( $short_description ) );
        $update->set_description( wc_sanitize_textarea( $description ) );
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

