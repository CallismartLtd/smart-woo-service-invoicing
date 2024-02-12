<?php
/**
 * This file contains codes for wallet debit for
 * services renewal
 */

 
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




// Hook to run the process_service_renewals function when the cron event is triggered
add_action('process_service_renewals_event', 'sw_process_service_renewals_via_Tera_Wallet');


// The main function to process service renewals
function sw_process_service_renewals_via_Tera_Wallet() {
    // Get a list of pending order IDs for the services that are due for renewal
    $pending_order_ids = get_pending_orders_by_service_id();

    foreach ($pending_order_ids as $order_id) {
        // Get the order instance
        $order = wc_get_order($order_id);

        // Get the user ID associated with the order
        $user_id = $order->get_user_id();

        // Get the service ID and name from order custom fields
        $service_id = get_post_meta($order_id, 'Service ID', true);
        $service_name = get_post_meta($order_id, 'Service Name', true);

        // Construct payment details
        $payment_details = "Payment for renewal of '$service_name' with the service ID: $service_id";

        // Get the order total price
        $order_total = $order->get_total();

        // Get the user's wallet balance
        $wallet_balance = woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

        if ($wallet_balance >= $order_total) {
            // Attempt to debit the wallet
            woo_wallet()->wallet->debit($user_id, $order_total, $payment_details);
            
            // Log Payment successful
            smart_woo_log($user_id, $service_id, $order_total, 'successful');
            
            // Update the order status to 'processing'
            $order->update_status('processing');
        } else {
            // Insufficient wallet balance
            smart_woo_log($user_id, $service_id, $order_total, 'failed', 'insufficient funds');
        }
    }
}






add_action('process_pending_refund_event', 'process_pending_refund_services');

function process_pending_refund_services($service_id_to_refund = null) {
    // Get pending refund services
    $pending_refund_services = sw_REFUND_handler();
    foreach ($pending_refund_services as $service) {
        // If a specific service ID is provided, only process refunds for that service
        if ($service_id_to_refund !== null && $service->service_id !== $service_id_to_refund) {
            continue; // Skip this service if it doesn't match the provided service ID
        }
        
        $user_id = $service->user_id;
        $service_id = $service->service_id;
        $amount = $service->amount;
        $transaction_status = $service->transaction_status;

        // Get the user's wallet balance
        $wallet_balance = woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

        // Credit the user with the refund amount
        $refund_details = "Refund for service ID: $service_id";
        woo_wallet()->wallet->credit($user_id, $amount, $refund_details);

        // Check the updated wallet balance
        $updated_wallet_balance = woo_wallet()->wallet->get_wallet_balance($user_id, 'edit');

        // Update the database with the refund status and details
        $update_result = sw_REFUND_handler($user_id, $service_id, $amount, 'Refunded', 'successfully credited user\'s wallet');
    }
}










function get_pending_orders_by_service_id() {
    // Use get_due_for_renewal_services to filter services
    $due_services = false;

    $matching_orders = array(); // An array to store matching order IDs

    if (!empty($due_services)) {
        foreach ($due_services as $service) {
            // Extract service_id
            $service_id = $service['service_id'];

            // Query WooCommerce orders for a custom field with the service_id
            $args = array(
                'post_type'      => 'shop_order',
                'post_status'    => 'wc-pending',
                'meta_query'     => array(
                    array(
                        'key'   => 'Service ID', // Replace with the actual custom field key
                        'value' => $service_id,
                    ),
                ),
            );

            $orders = get_posts($args);

            if (!empty($orders)) {
                // Add the order IDs to the matching_orders array
                foreach ($orders as $order) {
                    $matching_orders[] = $order->ID;
                }
            }
        }
    }

    return $matching_orders;
}
