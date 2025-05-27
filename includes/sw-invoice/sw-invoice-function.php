<?php
/**
 * File name  sw-invoice-functions.php
 * Utility function file to interact with invoice related data.
 *
 * @author Callistus
 * @package SmartWoo\functions
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Dropdown for Invoice Type with filter for custom options.
 *
 * @param string $selected The selected invoice type (optional).
 * @param bool $echo Whether to print or return output.
 *
 * @since 1.0.0
 */
function smartwoo_invoice_type_dropdown( $selected = null, $echo = true ) {
	// Default options
	$options = smartwoo_supported_invoice_types();

	$dropdown = '<select class="sw-form-input" name="invoice_type" id="invoice_type">';
	$dropdown .= '<option value="">' . __( 'Select Invoice Type', 'smart-woo-service-invoicing' );
	foreach ( $options as $value => $label ) {
		$is_selected = ( $value === $selected ) ? 'selected="selected"' : '';
		$dropdown   .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $is_selected ) . '>' . esc_html( $label ) . '</option>';
	}
	$dropdown .= '</select>';
	if ( true === $echo ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	}
	return $dropdown;
}

/**
 * Get supported invoice types
 * 
 * @return array $data An associative array of type => title
 */
function smartwoo_supported_invoice_types() {
	return apply_filters( 'smartwoo_supported_invoice_types',
		array(
			'New Service Invoice'       => __( 'New Service Invoice', 'smart-woo-service-invoicing' ),
			'Service Renewal Invoice'   => __( 'Service Renewal Invoice', 'smart-woo-service-invoicing' ),
		)
	);
}

/**
 * Dropdown for Invoice Payment Status with filter for custom options.
 *
 * @param string $selected The selected invoice status (optional).
 * @param bool 	$echo		Whether or not to print to screen (Defaults to true).
 *
 * @since 1.0.0
 */
function smartwoo_invoice_payment_status_dropdown( $selected = null, $echo = true ) {
	
	$options = apply_filters( 'smartwoo_payment_status',
		array(
			''			=> __( 'Select Payment Status', 'smart-woo-service-invoicing' ),
			'paid'		=> __( 'Paid', 'smart-woo-service-invoicing' ),
			'unpaid'	=> __( 'Unpaid', 'smart-woo-service-invoicing' ),
			'due'		=> __( 'Due', 'smart-woo-service-invoicing' ),
			'cancelled'	=> __( 'Cancel', 'smart-woo-service-invoicing' ),
		)
	);

	$dropdown = '<select class="sw-form-input" name="payment_status" id="payment_status">';
	foreach ( $options as $value => $label ) {
		$is_selected = ( $value === $selected ) ? 'selected="selected"' : '';
		$dropdown .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $is_selected ) . '>' . esc_html( $label ) . '</option>';
	}
	$dropdown .= '</select>';
	if ( true === $echo ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	}
	return $dropdown;
}

/**
 * Checks if an invoice is associated with a service.
 *
 * @param string $invoice_id The ID of the invoice to check.
 *
 * @return bool True if the invoice is associated with a service, false otherwise.
 */
function smartwoo_is_service_invoice( $invoice_id ) {
	$the_invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
	$service_id = $the_invoice ? $the_invoice->get_service_id() : null;

	return ! is_null( $service_id );
}

/**
 * Checks if a Service has an invoice with a specific invoice_type and transaction_status.
 *
 * @param string $service_id		The ID of Service to check.
 * @param string $invoice_type		The invoice type to check.
 * @param string $payment_status	The invoice payment status.
 *
 * @return string|false The invoice_id if the service has such an invoice, false otherwise.
 *
 * @since 1.0.0
 */
function smartwoo_evaluate_service_invoices( $service_id, $invoice_type, $payment_status ) {

	$args = array(
		'service_id'	=> $service_id,
		'type'			=> $invoice_type,
		'status'		=> $payment_status
		
	);
	$invoices	= SmartWoo_Invoice_Database::get_service_invoices( $args );

	if ( empty( $invoices ) ) {
		return false;
	}

	return $invoices[0]->get_invoice_id();
}


/**
 * Update one or more fields of an existing invoice.
 *
 * @param string $invoice_id The ID of the invoice to update.
 * @param array  $fields     An associative array of fields to update and their new values.
 *
 * @return bool True on success, false on failure.
 */
function smartwoo_update_invoice_fields( $invoice_id, $fields ) {
	$existing_invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( ! $existing_invoice ) {
		return false;
	}

	return SmartWoo_Invoice_Database::update_invoice_fields( $invoice_id, $fields );
}

/**
 * Generates a pending WooCommerce order for the payment of an unpaid invoice.
 *
 * @param SmartWoo_Invoice|string        $invoice The ID of the unpaid invoice.
 * @param float|null $total     The total for the order. If not provided (null), invoice total will be used.
 *
 * @return int|false The ID of the newly created order or false on failure.
 *
 * @since 1.1.0
 */
function smartwoo_generate_pending_order( $invoice, $total = null ) {
	$invoice = ( $invoice instanceof SmartWoo_Invoice ) ? $invoice : SmartWoo_Invoice_Database::get_invoice_by_id( $invoice );

	if ( ! $invoice ) {
		return false;
	}

	$order = wc_create_order( array( 'customer_id' => $invoice->get_user_id() ) );

	// Add fees from invoice as a line item.
	if ( $invoice->get_fee() > 0 || $invoice->get_fee() < 0 ) {
		$fee_amount = $invoice->get_fee();
		$fee_name   = 'Invoice Fee';
		$fee        = new WC_Order_Item_Fee();
		$fee->set_props(
			array(
				'name'      => $fee_name,
				'tax_class' => '',
				'total'     => $fee_amount,
			)
		);
		$order->add_item( $fee );
	}


	// Use line item with pseudo product name, and use real price to prevents SKU deduction.
	if ( $invoice->get_product() ) {
		$product_name         = $invoice->get_product()->get_name();
		$pseudo_product_name  = $product_name;
		$pseudo_product_price = $invoice->get_amount();
	
		$product = new WC_Order_Item_Product();
		$product->set_props(
			array(
				'name'     => $pseudo_product_name,
				'quantity' => 1,
				'subtotal' => $pseudo_product_price,
				'total'    => $pseudo_product_price,
			)
		);
		$order->add_item( $product );
	}

	// Set the order total based on the provided parameter or use the invoice total.
	$order_total = ( ! is_null( $total ) ) ? $total : $invoice->get_total();
	$order->set_total( $order_total );
	$order->update_status( 'pending' );

	// Set order signatures.
	$order->set_created_via( SMARTWOO );
	$order->update_meta_data( '_sw_invoice_id', $invoice->get_invoice_id() );
	$order->update_meta_data( '_wc_order_attribution_utm_source', SMARTWOO );
	$order->update_meta_data( '_wc_order_attribution_source_type', 'utm' );

	// Set client billing addresses.
	$customer = $invoice->get_user();
	$order->set_billing_first_name( $customer->get_billing_first_name() );
	$order->set_billing_last_name( $customer->get_billing_last_name() );
	$order->set_billing_company( $customer->get_billing_company() );
	$order->set_billing_address_1( $customer->get_billing_address_1() );
	$order->set_billing_address_2( $customer->get_billing_address_2() );
	$order->set_billing_city( $customer->get_billing_city() );
	$order->set_billing_postcode( $customer->get_billing_postcode() );
	$order->set_billing_country( $customer->get_billing_country() );
	$order->set_billing_state( $customer->get_billing_state() );
	$order->set_billing_email( $customer->get_billing_email() );
	$order->set_billing_phone( $customer->get_billing_phone() );
	// Save order.
	$order->save();

	/**
	 * Fires after we create new invoice orders.
	 * 
	 * @param WC_Order $order
	 */
	do_action( 'smartwoo_new_invoice_order', $order );

	// Return the ID of the newly created order.
	return $order->get_id();
}


/**
 * Generate invoice ID
 *
 * @return string $invoice_id   The new Generated Invoice ID.
 * @since 2.2.3 Deprecated the use of uniqid() function for generating invoice ID.
 */
function smartwoo_generate_invoice_id() {
	// $invoice_id		= uniqid( smartwoo_get_invoice_id_prefix() . '-' );
	return smartwoo_get_invoice_id_prefix() . '-' . bin2hex( random_bytes(4) ) . dechex( time() );
}

/**
 * Generates a new invoice and saves it to the database.
 *
 * @param int    $user_id        The user ID associated with the invoice.
 * @param int    $product_id     The product ID for the invoice.
 * @param string $payment_status The payment status of the invoice.
 * @param string $invoice_type   The type of the invoice.
 * @param string $service_id     (Optional) The service ID associated with the invoice.
 * @param float  $fee            (Optional) The fee associated with the invoice.
 * @param string $date_due       (Optional) The due date for the invoice in YYYY-MM-DD format.
 *
 * @return int|false The ID of the newly created invoice or false on failure.
 *
 * @since 1.0.0
 */
function smartwoo_create_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id = null, $fee = null, $date_due = null ) {
	$invoice_id 		= smartwoo_generate_invoice_id();
	$billing_address 	= smartwoo_get_user_billing_address( $user_id );
	$amount 			= wc_get_product( $product_id )->get_price();
	$total 				= $amount + ( $fee ?? 0 );

	$newInvoice = new SmartWoo_Invoice();
	$newInvoice->set_invoice_id( $invoice_id );
	$newInvoice->set_product_id( $product_id );
	$newInvoice->set_amount( $amount );
	$newInvoice->set_total( $total );
	$newInvoice->set_status( $payment_status );
	$newInvoice->set_date_created( current_time( 'mysql' ) );
	$newInvoice->set_user_id( $user_id );
	$newInvoice->set_billing_address( $billing_address );
	$newInvoice->set_type( $invoice_type );
	$newInvoice->set_service_id( $service_id );
	$newInvoice->set_fee( $fee );

	if ( $date_due ) {
		$newInvoice->set_date_due( $date_due );
	}

	if ( 'paid' === $payment_status ) {
		$newInvoice->set_date_paid( 'now' );
	}

	if ( 'unpaid' === strtolower( $payment_status ) ) {
		$newInvoice->save(); // Persist changes before order creation.
		$order_id = smartwoo_generate_pending_order( $invoice_id );
		$newInvoice->set_order_id( $order_id );
	}

	return $newInvoice->save();
}

/**
 * Retrieves a user's WooCommerce billing address parts and compile them
 * into a readable address.
 *
 * @param int $user_id  The ID of the user
 * @return string Readable address format.
 */
function smartwoo_get_user_billing_address( $user_id ) {
	$customer				= new WC_Customer( $user_id );
    $billing_address_1		= $customer->get_billing_address_1();
    $billing_address_2		= $customer->get_billing_address_2();
    $billing_city			= $customer->get_billing_city();
    $billing_state			= $customer->get_billing_state();
    $billing_country_code	= $customer->get_billing_country();
    $billing_country_name 	= smartwoo_get_country_name( $billing_country_code );
    $billing_state_name 	= $billing_state;
	$states 				= WC()->countries->get_states( $billing_country_code );

	if ( ! empty( $states ) && isset( $states[$billing_state] ) ) {
		$billing_state_name = $states[$billing_state];
	}

    $address_parts = array_filter(
        array(
            $billing_address_1,
            $billing_address_2,
            $billing_city,
            $billing_state_name,
            $billing_country_name,
        )
    );

    if ( ! empty( $address_parts ) ) {
        $user_billing_address = implode( ', ', $address_parts );
        return $user_billing_address;
    }

    return '';
}

/**
 * Get the full country name from a country code.
 * 
 * @param string $cc Two-letter country code (e.g., 'US', 'GB').
 * @return string The country name if found, otherwise the provided country code.
 *
 * @since 2.3.0
 */
function smartwoo_get_country_name( $cc ) {
    $countries = WC()->countries->get_countries();
    return isset( $countries[ $cc ] ) ? $countries[ $cc ] : $cc;
}

/**
 * Get the full state name from a country and state code.
 * 
 * @param string $cc      Two-letter country code (e.g., 'US', 'GB').
 * @param string $stcode  State code (e.g., 'CA' for California, 'ON' for Ontario).
 * @return string The state name if found, otherwise the provided state code.
 *
 * @since 2.3.0
 */
function smartwoo_get_state_name( $cc, $stcode ) {
    $states = WC()->countries->get_states( $cc );
    return isset( $states[ $stcode ] ) ? $states[ $stcode ] : $stcode;
}

/**
 * Retrieve billing Address from the store and options
 *
 * @return stdClass Object with billing details.
 */
function smartwoo_biller_details() {
	// Retrieve plugin and WooCommerce settings
	$business_name			= get_option( 'smartwoo_business_name', '' );
	$invoice_logo_url		= get_option( 'smartwoo_invoice_logo_url' );
	$admin_phone_numbers	= get_option( 'smartwoo_admin_phone_numbers', '' );
	$store_address			= get_option( 'woocommerce_store_address' );
	$store_city				= get_option( 'woocommerce_store_city' );
	$default_country		= get_option( 'woocommerce_default_country' );

	// Create and populate object with billing details
	$biller_details                     = new stdClass();
	$biller_details->business_name      = $business_name;
	$biller_details->invoice_logo_url   = $invoice_logo_url;
	$biller_details->admin_phone_number	= $admin_phone_numbers;
	$biller_details->store_address      = $store_address;
	$biller_details->store_city         = $store_city;
	$biller_details->default_country    = $default_country;

	// Return the object
	return $biller_details;
}

/**
 * Retrieves and formats the WooCommerce store address.
 *
 * @return string Formatted store address.
 */
function smartwoo_get_formatted_biller_address() {
    $store_address_1	= get_option( 'woocommerce_store_address' );
    $store_address_2	= get_option( 'woocommerce_store_address_2' );
    $store_city			= get_option( 'woocommerce_store_city' );
    $country_base		= wc_get_base_location();
    $store_country_name	= smartwoo_get_country_name( $country_base['country'] );
    $store_state_name	= smartwoo_get_state_name( $country_base['country'], $country_base['state'] );

    $address_parts = array_filter(
        array(
            $store_address_1,
            $store_address_2,
            $store_city,
            $store_state_name,
            $store_country_name,
        )
    );

    if ( ! empty( $address_parts ) ) {
        $store_address = implode( ', ', $address_parts );
        return $store_address;
    }

    // Return an empty string if there's no store address
    return '';
}


/**
 * Get the total amount spent by a user.
 * 
 * @param int $user_id		The user's ID.
 */
function smartwoo_client_total_spent( $user_id ) {
	$customer = new WC_Customer( $user_id );

	return  $customer->get_total_spent();
}

/**
 * Retrieves client's billing email, when billing email is not available
 * the client's login email is used.
 *
 * @param WC_Customer|int $user_id The user's ID 
 * @since 2.0.15
 */
function smartwoo_get_client_billing_email( $user_id ) {
	$user	= ( $user_id instanceof WC_Customer ) ? $user_id : new WC_Customer( $user_id );
	$billing_email	= $user->get_billing_email();

	if ( empty( $billing_email ) ) {
		// Fallback to user's login email address.
		$billing_email = $user->get_email();
	}


	return $billing_email;
}

/**
 * Invoice order Payment URL, specifically for the service invoices.
 * 
 * @param int $order_id WooCommerce order ID
 * @return string The generated order-pay URL
 */
function smartwoo_invoice_pay_url( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( $order && $order->get_meta( '_sw_invoice_id' ) ) {

		return $order->get_checkout_payment_url() ;
	}
	return "";
}

/**
 * Get invoice preview URL
 *
 * @param int|string $invoice_id Invoice ID
 * @return string|null Escaped URL or null if parameters are empty
 */
function smartwoo_invoice_preview_url( $invoice_id = '' ) {
    $preview_url = '#';

	if ( is_account_page() ) {
        $preview_url = wc_get_endpoint_url( 'smartwoo-invoice', $invoice_id );

    } elseif( ! smartwoo_is_frontend() && is_admin() ) {
		$preview_url = add_query_arg( 
			array( 
				'page' 			=> 'sw-invoices', 
				'tab' 			=> 'view-invoice', 
				'invoice_id'	=> $invoice_id 
			), 
			admin_url( 'admin.php' ) 
		);

	} else {
        $invoice_page_id	= get_option( 'smartwoo_invoice_page_id', 0 );
        $invoice_page_url	= get_permalink( $invoice_page_id );
        $preview_url		= smartwoo_get_endpoint_url( 'view-invoice', $invoice_id, $invoice_page_url );
    }
	return $preview_url;

}

/**
 * Invoice URL.
 */
function smartwoo_invoice_page_url() {
	$invoice_page = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

	if ( is_account_page () ){
		return wc_get_account_endpoint_url( 'smartwoo-invoice' );
	}
	return get_permalink( $invoice_page );
}

 /**
  * Product deletion button.
  */
function smartwoo_delete_invoice_button( $invoice_id ) {
	return '<button title="Delete Invoice" class="delete-invoice-button" data-invoice-id="' . esc_attr( $invoice_id ) . '"><span class="dashicons dashicons-trash"></span></button>';
}

// Add Ajax actions
add_action( 'wp_ajax_delete_invoice', 'smartwoo_delete_invoice_ajax_callback' );
add_action( 'wp_ajax_nopriv_delete_invoice', 'smartwoo_delete_invoice_ajax_callback' );

function smartwoo_delete_invoice_ajax_callback() {

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
		wp_send_json_error( array( 'message' => 'Action did not pass security check.' ) );
	}

	$invoice_id = isset( $_POST['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_id'] ) ) : '';

	if ( empty( $invoice_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid invoice ID.' ) );
	}

	$delete_result = SmartWoo_Invoice_Database::delete_invoice( $invoice_id );

	if ( ! $delete_result ) {
		wp_send_json_error( array( 'message' => 'Unable to delete invoice' ) );
	} else {
		wp_send_json_success( array( 'message' => 'Invoice deleted' ) );
	}
}

/**
 * Marks invoice as paid.
 *
 * @param  string $invoice_id   The ID of the invoice to be updated
 * @return bool     false if the invoice is already 'Paid' | true if update is successful
 */
function smartwoo_mark_invoice_as_paid( $invoice_id ) {
	$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( $invoice && 'paid' !== $invoice->get_status() ) {
		$order = $invoice->get_order();
		if ( ! $order ) {
			return false;
		}

		// Update additional fields in the invoice
		$fields	= array(
			'payment_status'  => 'paid',
			'date_paid'       => current_time( 'mysql' ),
			'transaction_id'  => $order->get_transaction_id(),
			'payment_gateway' => $order->get_payment_method_title(),
		);
		$updated_invoice = SmartWoo_Invoice_Database::update_invoice_fields( $invoice_id, $fields );
		/**
		 * Fires after an invoice is marked as paid.
		 * @param SmartWoo_Invoice $updated_invoice The updated invoice object.
		 */
		do_action( 'smartwoo_invoice_is_paid', $updated_invoice );

		return true;

	}
	// Invoice is already paid, terminate further execution.
	return false;
}

/**
 * Counts and renders payment status counts of all invoice for the current user
 */
function smartwoo_all_user_invoices_count() {

	$user_id = get_current_user_id();

	// Get counts for each payment status for the current user.
	$counts = array(
		'paid'      => SmartWoo_Invoice_Database::count_payment_status( $user_id, 'paid' ),
		'unpaid'    => SmartWoo_Invoice_Database::count_payment_status( $user_id, 'unpaid' ),
		'cancelled' => SmartWoo_Invoice_Database::count_payment_status( $user_id, 'cancelled' ),
		'due'       => SmartWoo_Invoice_Database::count_payment_status( $user_id, 'due' ),
	);

	// Generate the HTML.
	$output = '<div class="sw-invoice-status-counts">';
	foreach ( $counts as $status => $count ) {
		$nav_url = smartwoo_get_endpoint_url( 'status', $status );
		$output .= '<div class="sw-user-status-item' . ( ( get_query_var( 'status' ) === $status ) ? ' active' : '' ) . '">';
		$output .= '<p><a href="' . esc_url( $nav_url ) .'">' . ucfirst( $status ) . '</a> <small>' . $count . '</small></p>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}
