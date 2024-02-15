<?php
/**
 * File name    :   sw-invoice-functions.php
 * @author      :   Callistus
 * Description  :   Helper function for invoice datas
 */


/**
 * Checks if an invoice is associated with a service.
 *
 * @param string $invoice_id The ID of the invoice to check.
 *
 * @return bool True if the invoice is associated with a service, false otherwise.
 */
function is_service_invoice( $invoice_id ) {
    // Retrieve the invoice object based on the provided ID
    $the_invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    // Get the service ID from the invoice (or set to null if the invoice is not found)
    $service_id = $the_invoice ? $the_invoice->getServiceId() : null;

    // Return true if the service ID is not null, indicating it's a service invoice; otherwise, return false
    return ! is_null( $service_id );
}


/**
 * Checks if a user has an invoice with a specific type of Invoice and Transaction status.
 *
 * @param int    $user_id            The user ID to check.
 * @param string $invoice_type       The invoice type to check.
 * @param string $transaction_status The desired transaction status.
 *
 * @return string|false The invoice_id if the user has such an invoice, false otherwise.
 *
 * @since 1.0.0
 */
function sw_evaluate_user_invoices( $user_id, $invoice_type, $payment_status ) {
    // Retrieve invoices based on criteria
    $invoices = Sw_Invoice_Database::get_invoices_by_criteria( 'user_id', $user_id );

    // Check if the user has an invoice with the specified attributes
    foreach ( $invoices as $invoice ) {
        if (
            $invoice->getInvoiceType() === $invoice_type &&
            $invoice->getPaymentStatus() === $payment_status
        ) {
            return $invoice->getInvoiceId(); // Return the invoice_id
        }
    }

    return false; // User doesn't have the desired invoice
}


/**
 * Checks if a Service has an invoice with a specific invoice_type and transaction_status.
 *
 * @param string    $service_id            The ID of Service to check.
 * @param string $invoice_type       The invoice type to check.
 * @param string $transaction_status The desired transaction status.
 *
 * @return string|false The invoice_id if the service has such an invoice, false otherwise.
 *
 * @since 1.0.0
 */
function sw_evaluate_service_invoices( $service_id, $invoice_type, $payment_status ) {
    // Retrieve invoices based on criteria
    $invoices = Sw_Invoice_Database::get_invoices_by_criteria( 'service_id', $service_id );

    // Check if the user has an invoice with the specified attributes
    foreach ( $invoices as $invoice ) {
        if (
            $invoice->getInvoiceType() === $invoice_type &&
            $invoice->getPaymentStatus() === $payment_status
        ) {
            return $invoice->getInvoiceId(); // Return the invoice_id
        }
    }

    return false; // User doesn't have the desired invoice
}


/**
 * Update one or more fields of an existing invoice.
 *
 * @param string $invoice_id The ID of the invoice to update.
 * @param array  $fields     An associative array of fields to update and their new values.
 *
 * @return bool True on success, false on failure.
 */
function sw_update_invoice_fields( $invoice_id, $fields ) {
    // Check if the invoice exists
    $existing_invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    if ( ! $existing_invoice ) {
        return false; // Invoice not found
    }

    // Update the specified fields
    return Sw_Invoice_Database::update_invoice_fields( $invoice_id, $fields );
}

/**
 * Generates a pending WooCommerce order for the payment of an unpaid invoice.
 *
 * @param int      $user_id    The user ID associated with the order.
 * @param int      $invoice_id The ID of the unpaid invoice.
 * @param float|null $total     The total for the order. If not provided (null), invoice total will be used.
 *
 * @return int|false The ID of the newly created order or false on failure.
 *
 * @since 1.1.0
 */
function sw_generate_pending_order( $user_id, $invoice_id, $total = null ) {
    $invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    if ( ! $invoice ) {
        return false; // Return false if the invoice is not found
    }

    // Create a new WooCommerce order
    $order = wc_create_order(array( 'customer_id' => $user_id  ));

    // Add fees from invoice as a line item
    $fee_amount = $invoice->getFee();
    $fee_name = 'Invoice Fee';
    $fee = new WC_Order_Item_Fee();
    $fee->set_props(array(
        'name'      => $fee_name,
        'tax_class' => '', 
        'total'     => $fee_amount,
    ));
    $order->add_item( $fee );

    // Use line item with pseudo product name and price to prevent SKU deduction
    $product_name         = wc_get_product( $invoice->getProductId() )->get_name();
    $pseudo_product_name  = $product_name;
    $pseudo_product_price = $invoice->getAmount();

    $product = new WC_Order_Item_Product();
    $product->set_props( array(
        'name'      => $pseudo_product_name,
        'quantity'  => 1,
        'subtotal'  => $pseudo_product_price,
        'total'     => $pseudo_product_price,
    ));
    $order->add_item( $product );

    // Set the order total based on the provided parameter or use the invoice total
    $order_total = ( $total !== null ) ? $total : $invoice->getTotal();
    $order->set_total( $order_total );

    // Set order status to 'pending'
    $order->update_status( 'pending' );
    $order->update_meta_data( 'Order Type', 'Invoice Payment' );
    $order->update_meta_data( 'Invoice ID', $invoice_id );

    // Save order
    $order->save();
    
    // Trigger an action for new invoice orders
    do_action( 'new_invoice_order', $order->get_id() );

    // Return the ID of the newly created order
    return $order->get_id();
}

/**
 * Generate invoice ID
 * @return string $invoice_id   The new Generated Invoice ID
 */
function sw_generate_invoice_id(){
    $invoice_id = sw_get_invoice_number_prefix() . '-' . uniqid();
    if ( $invoice_id ){
        return $invoice_id;
    }
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
function sw_generate_new_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id = null, $fee = null, $date_due = null ) {
    // Generate a unique invoice ID
    $invoice_id = sw_generate_invoice_id();

    // Get the user's billing address
    $billing_address = sw_get_user_billing_address( $user_id );

    // Get the product price dynamically from WooCommerce
    $amount = wc_get_product( $product_id )->get_price();

    // Calculate the total by adding the fee (if provided)
    $total = $amount + ( $fee ?? 0 );

    // Create a new Sw_Invoice instance
    $newInvoice = new Sw_Invoice(
        $invoice_id,
        $product_id,
        $amount,
        $total,
        $payment_status,
        null, // Date Created will be set to the current date in the constructor
        $user_id,
        $billing_address,
        $invoice_type,
        $service_id,
        $fee
    );

    // Set the due date if provided
    if ( $date_due ) {
        $newInvoice->setDateDue( $date_due );
    }
    if ( $payment_status === 'paid' ) {
        $newInvoice->setDatePaid( current_time( 'mysql' ) );
    }

    // Call the sw_create_invoice method to save the invoice to the database
    $invoice_id = Sw_Invoice_Database::sw_create_invoice( $newInvoice );

    // Check if payment status is 'unpaid' and generate a pending order
    if (strtolower($payment_status) === 'unpaid') {
        $order_id = sw_generate_pending_order( $user_id, $invoice_id );

        // Set the order ID for the new invoice
        $fields = array(
            'order_id' => $order_id,
        );
        sw_update_invoice_fields( $invoice_id, $fields ) ;
    }

    return $invoice_id;
}


/**
 * Generates a service migration invoice based on form submission.
 */

add_action( 'template_redirect', 'sw_generate_service_migration_invoice' );

function sw_generate_service_migration_invoice() {

    if ( isset($_POST['sw_migrate_service']) && ( $_POST['sw_migrate_service'] === 'smart_woo_upgrade' || $_POST['sw_migrate_service'] === 'smart_woo_downgrade' ) ) {
        // Get and sanitize form data
        $user_id                = sanitize_text_field( $_POST['user_id'] );
        $service_id             = sanitize_text_field( $_POST['service_id'] );
        $new_service_product_id = sanitize_text_field( $_POST['new_service_product_id'] );
        $amount                 = sanitize_text_field( $_POST['amount'] );
        $order_total            = sanitize_text_field( $_POST['order_total'] );
        $refund_amount          = sanitize_text_field( $_POST['refund_amount'] );
        $payment_status         = ( max( 0, $order_total ) === 0) ? 'paid' : 'unpaid';
        $fee                    = sanitize_text_field( $_POST['fee'] );
        $date_due               =  current_time( 'mysql' );

        $invoice_type = null;

        if ( $_POST[ 'sw_migrate_service'] === 'smart_woo_upgrade' ) {
            $invoice_type = 'Service Upgrade Invoice';
        } elseif ( $_POST['sw_migrate_service'] === 'smart_woo_downgrade' ) {
            $invoice_type = 'Service Downgrade Invoice';
        }

        // Check if there is Service Upgrade or Downgrade Invoice for the service
        $existing_invoice_id = sw_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );
        if ( $existing_invoice_id ) {
            //Get the invoice for payment instead of creating another
            sw_redirect_to_invoice_preview( $existing_invoice_id );
        }

        // Generate a unique invoice ID
        $invoice_id             = sw_get_invoice_number_prefix() . '-' . uniqid();
        // Get the user's billing address
        $billing_address        = sw_get_user_billing_address( $user_id );
        // Calculate the total by adding the fee (if provided)
        $new_order_total = $order_total + ( $fee ?? 0 );
        $invoice_total = $amount + ( $fee ?? 0 );

        // Create a new Sw_Invoice instance
        $newInvoice = new Sw_Invoice(
            $invoice_id,
            $new_service_product_id,
            $amount,
            $order_total,
            $payment_status,
            null, // Date Created will be set to the current date in the constructor
            $user_id,
            $billing_address,
            $invoice_type,
            $service_id,
            $fee
        );
        // Set the due date if provided
        if ($date_due) {
            $newInvoice->setDateDue( $date_due );
        }

        // Call the sw_create_invoice method to save the invoice to the database
        $invoice_id = Sw_Invoice_Database::sw_create_invoice( $newInvoice );

        // Check if payment status is 'unpaid' and generate a pending order
        if ( strtolower( $payment_status ) === 'unpaid' ) {
            $order_id = sw_generate_pending_order( $user_id, $invoice_id, $order_total );

            // Set the order ID for the new invoice
            $fields = array(
                'order_id' => $order_id,
            );
            sw_update_invoice_fields( $invoice_id, $fields ) ;
        }
        if(strtolower( $payment_status ) === 'paid'){
             // Set the order ID for the new invoice
             $fields = array(
                'product_id' => $new_service_product_id,
            );
            $migrated_service = Sw_Service_Database::update_service_fields( $service_id, $fields );
            do_action( 'sw_service_migrated', $migrated_service );
        }
        
        if( sw_Is_prorate() === 'Enabled' ){
            smart_woo_log( $user_id, $service_id, $refund_amount, 'Pending Refund', 'Migration balance for ' . $invoice_id . '.' );
        }

        if ( $newInvoice ){
            sw_redirect_to_invoice_preview( $newInvoice->getInvoiceId() );
        }

        
    }
}



/**
 * Redirects to the invoice preview page based on the provided invoice ID.
 *
 * @param int $invoice_id The ID of the invoice.
 */
function sw_redirect_to_invoice_preview( $invoice_id ) {
    $invoice_page = get_option( 'sw_invoice_page', 0 );
    $redirect_url = get_permalink($invoice_page) . '?invoice_page=view_invoice&invoice_id=' . $invoice_id;
    wp_safe_redirect( $redirect_url );
    exit();
}



/**
 * Calculate the migration order total.
 *
 * @param float $product_price      The price of the selected product.
 * @param float $unused_service_price The unused amount for the selected service.
 *
 * @return float The calculated order total.
 */
function sw_calculate_migration_order_total( $product_price, $unused_service_price ) {

    // Check if pro-rata refunds are enabled
    $prorate_status = sw_Is_prorate();
    if ( $prorate_status === 'Enabled' ) {
        // If the unused service amount can cover the entire product price
        if ( $unused_service_price >= $product_price ) {
            // Calculate the remaining unused balance
            $remaining_unused_balance = $unused_service_price - $product_price;

            // Set the order total to 0
            return ['order_total' => 0, 'remaining_unused_balance' => $remaining_unused_balance];
        } else {
            /**
             * If the unused service amount is less than the product price, 
             * this means the service balance cannot fully cover the purchase.
             *  Set the remaining unused balance to 0.
             */
            $remaining_unused_balance = 0;

            // Subtract the unused service amount from the product price
            $order_total = $product_price - $unused_service_price;

            return ['order_total' => $order_total, 'remaining_unused_balance' => $remaining_unused_balance];
        }
    } else {
        // If pro-rata is not enabled, use the product price as the order total
        return ['order_total' => $product_price, 'remaining_unused_balance' => 0];
    }
}



/**
 * Retrieves a user's WooCommerce billing address parts and compile them
 * into a readable address.
 * 
 * @param int $user_id  The ID of the user
 * @return string readable address format.
 */

function sw_get_user_billing_address( $user_id ){ 
   
    // Get user's billing address details
    $billing_address_1 = get_user_meta( $user_id, 'billing_address_1', true );
    $billing_address_2 = get_user_meta( $user_id, 'billing_address_2', true );
    $billing_city      = get_user_meta( $user_id, 'billing_city', true );
    $billing_state     = get_user_meta( $user_id, 'billing_state', true );
    $billing_country   = get_user_meta( $user_id, 'billing_country', true );

    $address_parts = array_filter( array(
        $billing_address_1,
        $billing_address_2,
        $billing_city,
        $billing_state,
        $billing_country,
    ));

    if ( ! empty( $address_parts ) ) {
        $customer_billing_address = implode( ', ', $address_parts );
        return $customer_billing_address;
    }
    
    // Return an empty string if there's no billing address
    return '';
}


/**
 * Retrieve billing Address from the store and options
 *
 * @return stdClass Object with billing details.
 */
function sw_biller_details() {
    // Retrieve plugin and WooCommerce settings
    $business_name       = get_option( 'sw_business_name', '' );
    $invoice_logo_url    = get_option( 'sw_invoice_logo_url' );
    $admin_phone_numbers = get_option( 'sw_admin_phone_numbers', '' );
    $store_address       = get_option( 'woocommerce_store_address' );
    $store_city          = get_option( 'woocommerce_store_city' );
    $default_country     = get_option( 'woocommerce_default_country' );

    // Create and populate object with billing details
    $biller_details = new stdClass();
    $biller_details->business_name = $business_name;
    $biller_details->invoice_logo_url = $invoice_logo_url;
    $biller_details->admin_phone_number = $admin_phone_numbers;
    $biller_details->store_address = $store_address;
    $biller_details->store_city = $store_city;
    $biller_details->default_country = $default_country;

    // Return the object
    return $biller_details;
}



function sw_delete_invoice_button( $invoice_id ) {
    // Output the delete button with data-invoice-id attribute
    return '<button class="delete-invoice-button" data-invoice-id="' . esc_attr( $invoice_id ) . '">Delete Invoice</button>';
}

// Add Ajax actions
add_action( 'wp_ajax_delete_invoice', 'sw_delete_invoice_callback' );
add_action( 'wp_ajax_nopriv_delete_invoice', 'sw_delete_invoice_callback' );

function sw_delete_invoice_callback() {
    // Verify the nonce for security
    check_ajax_referer('smart_woo_nonce', 'security');

    // Get the invoice ID from the Ajax request
    $invoice_id = isset($_POST['invoice_id']) ? sanitize_text_field( $_POST['invoice_id'] ) : '';

    // Validate the invoice ID
    if ( empty( $invoice_id ) ) {
        wp_send_json_error( 'Invalid invoice ID.' );
    }

    // Attempt to delete the invoice
    $delete_result = Sw_Invoice_Database::delete_invoice( $invoice_id );

    // Check the result and send appropriate response
    if ( is_string( $delete_result ) ) {
        // An error occurred
        wp_send_json_error( $delete_result );
    } else {
        // Success
        wp_send_json_success( $delete_result );
        exit();
    }
}
