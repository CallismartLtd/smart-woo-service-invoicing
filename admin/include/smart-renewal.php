<?php
/**
 * File name    :   smart-renewal.php
 * @author      :   Callistus
 * Description  :   Handles Service Renewal
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'sw_new_service_purchase_complete', 'sw_new_service_invoice_handler');
function sw_new_service_invoice_handler( $invoice_id ) {
    //update the invoice
    sw_update_invoice_on_payment( $invoice_id );

}


/**
 * Update the invoice payment status
 * @param  string   $invoice_id   The ID of the invoice to be updated
 * @do_action @param object $invoice  Triggers "sw_invoice_is_paid" action with the invoice instance
 * @return bool     false if the invoice is already 'Paid' | true if update is successful
 */
function sw_update_invoice_on_payment( $invoice_id ) {
    // Get the invoice associated with the service
    $invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    // Check if the invoice is valid and payment_status is not 'paid'
    if ( $invoice && $invoice->getPaymentStatus() !== 'paid' ) {
        // Get the order associated with the invoice
        $order = wc_get_order( $invoice->getOrderId() );

        // Update additional fields in the invoice
        $fields = array(
            'payment_status'   => 'paid',
            'date_paid'        => current_time( 'Y-m-d H:i:s' ),
            'transaction_id'   => $order->get_transaction_id(), // Use order transaction id
            'payment_gateway'  => $order->get_payment_method(), // Use payment gateway used for the order
        );
        $updated_invoice  = Sw_Invoice_Database::update_invoice_fields( $invoice_id, $fields );
        do_action( 'sw_invoice_is_paid', $updated_invoice );

        return true;

    } else {
        // Invoice is already paid, terminate further execution
        return false;
    }
}


/**
 * Determine invoice payment order, Determine which invoice type is paid
 * @param int       $order_id                The paid invoice order
 */
add_action( 'woocommerce_order_status_completed', 'sw_paid_invoice_order_manager', 10, 3 );
add_action( 'woocommerce_payment_complete', 'sw_paid_invoice_order_manager', 10, 4 );


function sw_paid_invoice_order_manager( $order_id ) {

    //Get the order object
    $order = wc_get_order( $order_id );

    // Get the invoice ID from the order metadata
    $invoice_id = $order->get_meta( 'Invoice ID' );
    if ( empty( $invoice_id ) ) {
        return;
    }

    // Get all datas associated with the invoice
    $invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );
    $service_id = $invoice->getServiceId();
    $invoice_type = $invoice->getInvoiceType();

    if ( $invoice_type === 'New Service Invoice' ) {
        do_action( 'sw_new_service_purchase_complete', $invoice_id );
    }

    // Get the user ID of the user who owns the order
    $user_id = $order->get_user_id();

    // Check if both service ID and user ID are available
    if ( !empty( $service_id ) && !empty( $user_id ) ) {

        // Fetch the service status
        $service_status = sw_service_status( $service_id );
        // Perform actions based on the service status and type of invoice
        if ( $service_status === 'Due for Renewal' || $service_status === 'Grace Period' && $invoice_type === 'Service Renewal Invoice') {

            // Call the function to renew the service
            sw_renew_service( $user_id, $service_id, $invoice_id );

        } elseif ( $service_status === 'Expired' && $invoice_type === 'Service Renewal Invoice' ) {
            // Call the function to activate the expired service
            sw_activate_expired_service( $user_id, $service_id, $invoice_id );
        } elseif ( $service_status === 'Active' && $invoice_type === 'Service Upgrade Invoice' || $invoice_type === 'Service Downgrade Invoice') {
            $service = Sw_Service_Database::get_service_by_id( $service_id );

            sw_migrate_service( $service, $invoice_id );

        } else {
            return false;
        } 
    }
    
}

/**
 * Renew  Due Services
 */

function sw_renew_service( $user_id, $service_id, $invoice_id ) {

    // 1. Identify the Service and Invoice
    $service         = Sw_Service_Database::get_service_by_id( $service_id );
    $invoice         = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    // 2. Add Action Hook Before Updating Service Information
    do_action( 'sw_before_service_renew', $service );

    //Check and update invoice status before service update
    $invoice_status = sw_update_invoice_on_payment( $invoice_id );

    if (  $invoice_status === false) {
        // Invoice is already paid, or something went wrong
        return; // Terminate further execution 
    }

    if ($service) {
        // 3. Move (Copy) Old Service Information to Log
        sw_move_service_to_log( $service_id );

        // 4. Calculate Renewal Dates based on Billing Cycle
        $billing_cycle = $service->getBillingCycle();
        $old_end_date = strtotime( $service->getEndDate() );

        switch ($billing_cycle) {
            case 'Monthly':
                $interval = '+1 month';
                break;
            case 'Quarterly':
                $interval = '+3 months';
                break;
            case 'Six Monthly':
                $interval = '+6 months';
                break;
            case 'Yearly':
                $interval = '+1 year';
                break;
            default:
                // Handle other cases if needed
                break;
        }

        // Calculate new dates
        $new_start_date     =    date( 'Y-m-d', $old_end_date );
        $new_end_date       =    date( 'Y-m-d', strtotime($interval, $old_end_date ) );
        $new_next_payment_date = date( 'Y-m-d', strtotime( '-7 days', strtotime( $new_end_date ) ) );

        // 6.  Update the service using the update_service method
        $service->setStartDate( $new_start_date );
        $service->setNextPaymentDate( $new_next_payment_date );
        $service->setEndDate( $new_end_date );
        $service->setStatus( null ); // Renewed service will be automatically calculated

        // Perform the update
        $updated = Sw_Service_Database::update_service( $service );
        //send email notification
        sw_renewal_sucess_email( $service );
        // 6. Add Action Hook After Service Renewal
        do_action( 'sw_service_renewed', $service );

    }
}



/**
 * Activate Expired Services
 */

function sw_activate_expired_service( $user_id, $service_id, $invoice_id ) {
    // 1. Identify the Expired Service
    $expired_service = Sw_Service_Database::get_service_by_id( $service_id );
    $invoice         = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

    //Check and update invoice status before service update
    $invoice_status  = sw_update_invoice_on_payment( $invoice_id );

    if ( $invoice_status === false ) {
        // Invoice is already paid or something went wrong
        return; // Terminate further execution if needed
    }

    if ( $expired_service ) {
        // 2. Move (Copy) Old Service Information to Log
        sw_move_service_to_log( $service_id );

        // 3. Get the Order Paid Date
        $order_id = $invoice->getOrderId();
        $order = wc_get_order( $order_id );
        $order_paid_date = $order->get_date_paid()->format( 'Y-m-d H:i:s' );

        // 4. Calculate Activation Dates based on Billing Cycle
        $billing_cycle = $expired_service->getBillingCycle();

        switch ( $billing_cycle ) {
            case 'Monthly':
                $interval = '+1 month';
                break;
            case 'Quarterly':
                $interval = '+3 months';
                break;
            case 'Six Monthtly':
                $interval = '+6 months';
                break;
            case 'Yearly':
                $interval = '+1 year';
                break;
            default:
                // Handle other cases if needed
                break;
        }

        // Calculate new dates
        $new_start_date = $order_paid_date;
        $new_end_date = date( 'Y-m-d', strtotime( $interval, strtotime($new_start_date ) ) );
        $new_next_payment_date = date( 'Y-m-d', strtotime('-7 days', strtotime( $new_end_date ) ) );

        // 5. Add Action Hook Before Updating Service Information
        do_action( 'sw_before_activate_expired_service', $expired_service );

        // 6.  Update the service using the update_service method
        $expired_service->setStartDate( $new_start_date );
        $expired_service->setNextPaymentDate( $new_next_payment_date );
        $expired_service->setEndDate( $new_end_date );
        $expired_service->setStatus( null );

        // Perform the update
        $updated = Sw_Service_Database::update_service( $expired_service );

        //send email notification
        sw_renewal_sucess_email( $expired_service );
        
        // 7. Add Action Hook After Service Activation
        do_action( 'sw_expired_service_activated',  $expired_service );


        // Return true for successful activation
        return true;
    }

    // Return false if activation failed
    return false;
}



/**
 * Helper function to handle customer billing details
 */

function invoice_billing_details() {
    // Get all orders that don't have a billing address.
    $args = array(
        'post_type'      => 'shop_order',
        'posts_per_page' => -1,
        'post_status'    => array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed' ),
        'meta_query'     => array(
            array(
                'key'     => '_billing_address_1',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

    $orders = new WP_Query( $args );

    if ( $orders->have_posts() ) {
        while ($orders->have_posts()) {
            $orders->the_post();
            $order_id = get_the_ID();

            // Get the customer ID for the order.
            $customer_id = get_post_meta( $order_id, '_customer_user', true );

            // Get the customer's data from their user profile.
            $customer_data = get_userdata( $customer_id );
            if ( $customer_data ) {
                // Customer Name
                $customer_name = $customer_data->first_name . ' ' . $customer_data->last_name;

                // Billing Address
                $billing_address = array(
                    'billing_address_1' => $customer_data->billing_address_1,
                    'billing_address_2' => $customer_data->billing_address_2,
                    'billing_city'      => $customer_data->billing_city,
                    'billing_state'     => $customer_data->billing_state,
                    'billing_postcode'  => $customer_data->billing_postcode,
                    'billing_country'   => $customer_data->billing_country,
                );

                // Customer Website
                $customer_website = $customer_data->user_url;

                // Customer Phone Number
                $customer_phone = $customer_data->billing_phone;

                // Update the order with the customer's information.
                update_post_meta( $order_id, '_billing_address_1', $billing_address['billing_address_1'] );
                update_post_meta( $order_id, '_billing_address_2', $billing_address['billing_address_2'] );
                update_post_meta( $order_id, '_billing_city', $billing_address['billing_city'] );
                update_post_meta( $order_id, '_billing_state', $billing_address['billing_state'] );
                update_post_meta( $order_id, '_billing_postcode', $billing_address['billing_postcode'] );
                update_post_meta( $order_id, '_billing_country', $billing_address['billing_country'] );
                update_post_meta( $order_id, '_billing_first_name', $customer_data->first_name );
                update_post_meta( $order_id, '_billing_last_name', $customer_data->last_name );
                update_post_meta( $order_id, '_billing_email', $customer_data->user_email );
                update_post_meta( $order_id, '_billing_phone', $customer_phone );
                update_post_meta( $order_id, '_billing_website', $customer_website );
            }
        }
    }
    wp_reset_postdata();
}
 add_action( 'new_invoice_order', 'invoice_billing_details' );



/**
 * Paid Invoice order status is updated to completed
 */
function auto_update_paid_services_callback() {
    // Get processing orders with 'Invoice Payment' in custom field 'Order Type'
    $args   = array(
        'status' => 'processing',
    );
    $orders = wc_get_orders( $args );

    // Loop through each order
    foreach ( $orders as $order ) {
        // Get The Order type to update 
        $order_meta_key = $order->get_meta( 'Order Type' );
        $order_meta_value = $order->get_meta( 'Invoice Order' );
        if ( empty( $order_meta_key ) && empty( $order_meta_value ) ) {
            continue; // Move to the next iteration if the meta fields are not found 
        }
        $order_id = $order->get_id();
        //We need to get the invoice associated witht the order
        $invoices = Sw_Invoice_Database::get_invoices_by_order_id( $order_id );
        if ( !empty( $invoices ) ){
            // loop through all invoices and complete the order
            foreach ( $invoices as $invoice ) {
                $invoice_type = $invoice->getInvoiceType();
                if ( $invoice_type === 'Service Renewal Invoice' || $invoice_type === 'Service Downgrade Invoice' || $invoice_type === 'Service Upgrade Invoice') {
                    // Update the order status to 'completed'
                    $order->update_status( 'completed' );
                }
            }
        }
    }
}

add_action( 'auto_update_paid_services', 'auto_update_paid_services_callback' );



// Hook the move_old_renewal_orders_to_trash function to the scheduled event
add_action('move_old_renewal_orders_to_trash_event', 'move_old_renewal_orders_to_trash');

// Function to move old Service Renewals to trash
function move_old_renewal_orders_to_trash() {
    $two_weeks_ago = strtotime('-2 weeks', current_time('timestamp'));

    // Get Service Renewals that were created 2 weeks ago
    $args = array(
        'post_type' => 'shop_order',
        'post_status' => 'wc-pending',
        'posts_per_page' => -1,
        'date_query' => array(
            array(
                'before' => date('Y-m-d H:i:s', $two_weeks_ago),
                'inclusive' => true,
            ),
        ),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'Order Type',
                'value' => 'Service Renewal',
                'compare' => '=',
            ),
        ),
    );

    $renewal_orders = get_posts($args);

    foreach ($renewal_orders as $order) {
        // Move the order to trash
        wp_trash_post($order->ID);
    }
}



/**
 * Handle Quick Action button on Service Details page(frontend)
 */

function handleCancelOrSuspendService() {
    // Check if the 'action' parameter is in the URL
    if (isset($_GET['action'])) {
        $action = sanitize_text_field($_GET['action']);

        if ($action === 'sw_cancel_service' || $action === 'sw_cancel_billing') {
            // Get the current user's ID and service ID
            $user_id = get_current_user_id();
            $service_id = isset($_GET['service_id']) ? sanitize_text_field($_GET['service_id']) : '';

            // Check if the service_id is valid
            if (!empty($service_id)) {
                // Get the service status
                $service_status = sw_service_status( $service_id );

                // Check if the service is active
                if ($service_status === 'Active') {
                    // Define the status based on the action
                    $status = ($action === 'sw_cancel_service') ? 'Cancelled' : 'Active (NR)';
                        // Update the service with the new product ID.
                    $field = array(
                        'status' => $status,
                    );

                    //Change the Service Status
                    Sw_Service_Database::update_service_fields( $service_id, $field );

                    // Perform additional actions based on the action
                    if ($action === 'sw_cancel_service') {
                        // Send a service cancellation email
                        sw_user_service_cancelled_mail($user_id, $service_id);
                        sw_service_cancelled_mail_to_admin( $service_id );
                        
                        // Check if pro-rata refunds are enabled
                        $prorate_status = sw_Is_prorate();

                        if ($prorate_status === 'Enabled') {
                            // Perform pro-rata refund
                            performProRataRefund($user_id, $service_id);
                        }
                    } elseif ($action === 'sw_cancel_billing') {
                        // Send a billing automation cancellation email
                        sw_user_service_optout($user_id, $service_id);
                    }

                   // Redirect to the service details page
                    $service_details_url = add_query_arg(array('service_page' => 'service_details', 'service_id' => $service_id), get_permalink());
                    wp_redirect($service_details_url);
                    exit();

                } else {
                    // The service is not 'Active', do not proceed with cancellation or suspension
                    $service_details_url = add_query_arg(array('service_page' => 'service_details', 'service_id' => $service_id), get_permalink());
                    wp_redirect($service_details_url);
                    exit();
                }
            }
        }
    }
}

// Hook into WordPress to handle the action
add_action('template_redirect', 'handleCancelOrSuspendService');

/**
 * Perform Prorata Refund for unused service due to cancellation
 * @param int      $user_id        ID of the user to refund
 * @param string      $service_id     ID of the cancelled service
 */
function performProRataRefund( $user_id, $service_id ) {
    // Check if pro-rata refunds are enabled
    $prorate_status = sw_Is_prorate();

    if ( $prorate_status !== 'Enabled' ) {
        // Pro-rata refunds are disabled, do not proceed with the refund
        return false;
    }

    // Get service details using sw_get_service function
    $service_details = sw_get_service( $user_id, $service_id );

    if ( !$service_details ) {
        // Service not found
        return false;
    }

    $usage_metrics = sw_check_service_usage( $service_id );
    if ( $usage_metrics !== false ) {
        // Calculate the refund amount based on the order amount and percentage
        $refund_amount =  $usage_metrics['unused_amount'];

        // Log the refund details into your database
        $transaction_status = 'Pending Refund';
        $details = 'This service is cancelled, and a Refund has been scheduled';
        smart_woo_log( $user_id, $service_id, $refund_amount, $transaction_status, $details );
        return $refund_amount;
    }
}







/**
 * Handle the payment link, verify the token, log in the user, and process the payment.
 */

add_action( 'init', 'swsi_handle_payment_link' );

function swsi_handle_payment_link() {
    // Check if the pay-invoice action is set in the URL
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'sw_invoice_payment' ) {
        // Get and sanitize the parameters from the URL
        $token = sanitize_text_field( $_GET['token'] );

        // Verify the token
        $payment_info = swsi_verify_token( $token );

        if ( $payment_info ) {
            // Extract relevant information
            $invoice_id = $payment_info['invoice_id'];
            $user_email = $payment_info['user_email'];

            // Retrieve the user
            $user = get_user_by( 'email', $user_email );

            if ( $user ) {
                // Make sure the Sw_Invoice_Database class is defined and loaded
                if ( class_exists( 'Sw_Invoice_Database' ) ) {
                    $invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

                    if ( $invoice ) {
                        // Additional validation for service status
                        $user_id            = $user->ID;
                        $invoice_status     = $invoice->getPaymentStatus();
                        $invoice_user_id    = $invoice->getUserId();

                        if ($invoice_user_id === $user_id && $invoice_status === 'unpaid') {

                            $order_id = $invoice->getOrderId();
                            // Get the order object
                            $order = wc_get_order( $order_id );

                            if ( $order && $order->get_status() === 'pending' ) {
                                // Log in the user
                                wp_set_current_user( $user->ID, $user->user_login );
                                wp_set_auth_cookie( $user->ID );
                                do_action( 'wp_login', $user->user_login, $user );

                                // Redirect to the order pay page
                                $order_key    = $order->get_order_key();
                                $checkout_url = wc_get_checkout_url() . 'order-pay/' . $order->get_id() . '/?pay_for_order=true&key=' . $order_key;
                                wp_safe_redirect( $checkout_url );
                                exit();
                            } else {
                                // Show wp_die with backlink
                                wp_die( 'Sorry, we cannot process payments for this invoice. Please contact us if you need further assistance', 'Error', array( 'response' => 400, 'back_link' => true ) );
                            }
                        } else {
                            // Show wp_die with backlink
                            wp_die( 'This invoice cannot be paid for. Please contact us if you need further assistance', 'Error', array( 'response' => 400, 'back_link' => true ) );
                        }
                    } else {
                        // Show wp_die with backlink
                        wp_die( 'Invoice not found', 'Error', array( 'response' => 400, 'back_link' => true ) );
                    }
                } else {
                    // Show wp_die with backlink
                    wp_die( 'Sw_Invoice_Database class not found', 'Error', array( 'response' => 400, 'back_link' => true ) );
                }
            } else {
                // Show wp_die with backlink
                wp_die( 'User not found', 'Error', array( 'response' => 400, 'back_link' => true ) );
            }
        } else {
            // Token is invalid or expired; handle accordingly
            wp_die( 'Invalid or expired link', 'Error', array( 'response' => 400, 'back_link' => true ) );
        }
    }
}




/**
 * Automatic invoice creation for services on due date
 * 
 * @Do_action "sw_auto_invoice_created" triggers after successful invoice creation
 * @action @param object $newInvoice  The instance of the newly created invoice
 * @action @param object $service     The instance of the service being renewed
 * @return bool False if no service is due | True otherwise
 */

// Function to check and renew services with due next payment dates
function sw_auto_renew_services() {
    // Get all services
    $all_services = SW_Service_Database::get_all_services();
    if ( empty( $all_services ) ) {
        return;
    }

    foreach ( $all_services as $service ) {
        $user_id         = $service->getUserId();
        $service_id      = $service->getServiceId();
        $service_name    = $service->getServiceName();
        $product_id      = $service->getProductId();

        // Check the status of the service using the sw_service_status function
        $service_status  = sw_service_status( $service_id );

        // Check if the service is 'Due for Renewal'
        if ( $service_status === 'Due for Renewal' ) {

            // Check if there is Service Renewal Invoice for each service
            $existing_invoice_id = sw_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid' );
            if ( $existing_invoice_id ) {
                continue; //proceeds with the next iteration
            } else{
                //New Invoice Data
                $payment_status      =  'unpaid';
                $invoice_type        =  'Service Renewal Invoice';
                $date_due            =  current_time('mysql');

                //Generate Unpaid invoice
                $new_invoice_id = sw_generate_new_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );
                if ( $new_invoice_id ) {
                    // Get the invoice object
                    $newInvoice = Sw_Invoice_Database::get_invoice_by_id( $new_invoice_id );
                    do_action( 'sw_auto_invoice_created', $newInvoice, $service );
                }
            }
        }
    }
}                
            
// Hook to  scheduled event
add_action( 'auto_renew_services_event', 'sw_auto_renew_services' );


/**
 * Manual invoice creation
 */
add_action( 'init', 'sw_manual_service_renewal' );

function sw_manual_service_renewal() {
    // Check if the renewal action is set in the URL
    if ( isset($_GET['action']) && $_GET['action'] === 'renew-service' ) {

        // Verify the nonce
        $nonce_action = 'renew_service_nonce';
        if (isset($_GET['renew_nonce']) && wp_verify_nonce($_GET['renew_nonce'], $nonce_action ) ) {
        
            // Get and sanitize the service ID
            $service_id = sanitize_text_field( $_GET['service_id'] );
            $service  = Sw_Service_Database::get_service_by_id( $service_id );
            $product_id = $service->getProductId();

            if ( !$service ) {
                wp_die( 'Error: Service does not exist.' );
            }
            
             // Check the status of the service using the sw_service_status function
            $service_status  = sw_service_status( $service_id );

            // Check if the service is 'Due for Renewal'
            if ( $service_status === 'Due for Renewal' || $service_status === 'Expired' || $service_status === 'Grace Period' ) {


                // Check if there is Service Renewal Invoice for the service
                $existing_invoice_id = sw_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid');
                if ( $existing_invoice_id ) {
                    
                    sw_redirect_to_invoice_preview( $existing_invoice_id );
        
                } else{
                    //New Invoice Data
                    $payment_status      =  'unpaid';
                    $invoice_type        =  'Service Renewal Invoice';
                    $date_due            =  current_time('Y-m-d H:i:s');

                    //Generate Unpaid invoice
                    $NewInvoiceID = sw_generate_new_invoice( get_current_user_id(), $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );

                    if ( $NewInvoiceID ) {
                        $NewInvoice = Sw_Invoice_Database::get_invoice_by_id( $NewInvoiceID );
                        $new_order_id = $NewInvoice->getOrderId();
                        $new_order    = wc_get_order( $new_order_id );
                        $order_key = $new_order->get_order_key();
                        sw_send_user_generated_invoice_mail( $NewInvoice, $service );
                        $checkout_url = wc_get_checkout_url() . 'order-pay/' . $new_order_id . '/?pay_for_order=true&key=' . $order_key;
                        wp_safe_redirect( $checkout_url );
                        exit();
                    }
                
                }
            }

        } else {
            //If nonce verification fails
            wp_die( 'Error: Service Renewal Action failed authentication.' );
        }
    }
}


/**
 * Perform Migration of service.
 *
 * @param object $service      The Service to be migrated.
 * @param string $invoice_id   The ID of the invoice used for migration payment.
 * @return bool  True if migrated, false if not migrated.
 */
function sw_migrate_service( $service, $invoice_id ) {
    $service_id = $service->getServiceId();
    $product_id = Sw_Invoice_Database::get_invoice_by_id( $invoice_id )->getProductId();
    $invoice_status = sw_update_invoice_on_payment( $invoice_id );

    if ( $invoice_status === false ) {
        // Invoice is already paid, or something went wrong.
        error_log( 'Service migration failed for service ID: ' . $service_id .', Invoice Check returned false. Invoice ID: ' . $invoice_id );

        return false; // Indicate failure.
    }

    // Update the service with the new product ID.
    $fields = array(
        'product_id' => $product_id,
    );

    $migrated = Sw_Service_Database::update_service_fields( $service_id, $fields );

    // Log or handle any errors during the update if needed.
    if ( ! $migrated ) {
        error_log( 'Service migration failed for service ID: ' . $service_id );
    }

    return $migrated; // Return the result of the migration.
}



          