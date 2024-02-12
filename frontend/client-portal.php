<?php

/**
 * This file contains all the function codes for the shortcodes
 * to be used in the frontend by the admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode for Invoice table
 */

 function display_invoices_table() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return "Hello! It looks like you're not logged in.";
    }

    // Get the current logged-in user's ID
    $current_user_id = get_current_user_id();

    // Start the table markup
    $table_html = "<table>";

    // Query all orders made by the current user
    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array(
            'wc-processing',
            'wc-on-hold',
            'wc-completed',
            'wc-cancelled',
            'wc-refunded',
            'wc-failed',
            'wc-pending',
            'wc-partially-paid',
        ),
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_customer_user',
                'value'   => $current_user_id,
                'compare' => '=',
            ),
        ),
    );

    $orders = get_posts($args);

    if ($orders) {
        foreach ($orders as $order) {
            $order_number = $order->ID;
            $order_date = new DateTime($order->post_date);
            $formatted_date = $order_date->format('F j, Y @ h:i A');
            $order_key = get_post_meta($order->ID, '_order_key', true);

            // Get the service name from the custom field
            $service_name = get_post_meta($order->ID, 'Service Name', true);
            $invoicePrefix = sw_get_invoice_number_prefix();
            // Add a table row for each order
            $table_html .= "<tr>
                <td class='invoice-table-heading'>Invoice Number:</td>
                <td class='invoice-table-value'>$invoicePrefix-$order_number</td>
            </tr>";

            // Check if the service name exists in the custom fields
            if (!empty($service_name)) {
                $table_html .= "<tr>
                    <td class='invoice-table-heading'>Service Name:</td>
                    <td class='invoice-table-value'>$service_name</td>
                </tr>";
            }

            $table_html .= "<tr>
                <td class='invoice-table-heading'>Date:</td>
                <td class='invoice-table-value'>Generated on - $formatted_date</td>
            </tr>";

            // Always show the "View" button
            $invoice_preview_page = get_option( 'sw_invoice_page', 0 );
            $preview_invoice_url = get_permalink($invoice_preview_page) . '?order_id=' . $order_number;
            $table_html .= "<tr>
                <td class='invoice-table-heading'></td>
                <td class='invoice-table-value'><a href='$preview_invoice_url' class='invoice-preview-button'>View</a>";

            // Show the "Pay" button beside the "View" button only if the order is pending
            if ($order->post_status === 'wc-pending') {
                $checkout_url = wc_get_checkout_url();
                $order_pay_url = $checkout_url . 'order-pay/' . $order_number . '/?pay_for_order=true&key=' . $order_key;
                $table_html .= "<a href='$order_pay_url' class='invoice-pay-button'>Pay</a>";
            }

            $table_html .= "</td></tr>";

            // Add an empty row for spacing
            $table_html .= "<tr><td colspan='2'></td></tr>";
        }
    } else {
        // Add a message if no invoice is found
        $table_html .= "<tr><td colspan='2'>All your invoices will appear here.</td></tr>";
    }

    // Close the table markup
    $table_html .= "</table>";

    // Return the table HTML
    return $table_html;
}

/**
 * Function for Pending Transaction Count
 * In this context, pendding transactions here means pending orders
 */
function get_pending_transactions_count() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return "Hello! It looks like you\'re not logged in.";
    } else {
        // Get the current logged-in user's ID
        $current_user_id = get_current_user_id();

        // Count pending transactions (orders with 'pending' status)
        $args = array(
            'post_type'      => 'shop_order',
            'post_status'    => 'wc-pending', // Count only orders with 'pending' status
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_customer_user',
                    'value'   => $current_user_id,
                    'compare' => '=',
                ),
            ),
        );
        $pending_transactions = get_posts($args);

        // Add classes and inline CSS for centering
        return "<h1 class='centered' style='text-align: center; margin: 0 auto; font-size: 45px;'>" . count($pending_transactions) . "</h1><p class='centered' style='text-align: center; font-size: 18px;'>Unpaid Orders</p>";
    }
}

/**
 * Invoice Status Counts for the Currently Logged in user
 */
function get_invoice_status_counts() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return "Hello! It looks like you're not logged in.";
    }

    // Get the current logged-in user's ID
    $current_user_id = get_current_user_id();

    // Define order statuses for counts
    $status_counts = array(
        'paid' => array('wc-completed', 'wc-processing'),
        'unpaid' => array('wc-pending'),
        'cancelled' => array('wc-cancelled'),
        'refunded' => array('wc-refunded')
    );

    // Initialize counts array
    $counts = array();

    // Loop through each status type and get count
    foreach ($status_counts as $status => $statuses) {
        $args = array(
            'post_type'      => 'shop_order',
            'post_status'    => $statuses,
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_customer_user',
                    'value'   => $current_user_id,
                    'compare' => '=',
                ),
            ),
        );

        $orders = get_posts($args);
        $counts[$status] = count($orders);
    }

    // Generate the HTML
    $output = '<div class="invoice-status-counts">';
    $output .= '<div class="status-item">';
    $output .= '<p>Paid (' . $counts['paid'] . ')</p>';
    $output .= '</div>';
    $output .= '<div class="status-item">';
    $output .= '<p>Unpaid (' . $counts['unpaid'] . ')</p>';
    $output .= '</div>';
    $output .= '<div class="status-item">';
    $output .= '<p>Cancelled (' . $counts['cancelled'] . ')</p>';
    $output .= '</div>';
    $output .= '<div class="status-item">';
    $output .= '<p>Refunded (' . $counts['refunded'] . ')</p>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}



/**
 * ShortCode for Unpaid Invoice Count
 */
function get_unpaid_invoices_count() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return "Hello! It looks like you\'re not logged in.";
    } else {
        // Get the current logged-in user's ID
        $current_user_id = get_current_user_id();

        // Count unpaid invoices (orders with 'pending' status)
        $args = array(
            'post_type'      => 'shop_order',
            'post_status'    => 'wc-pending', // Count only orders with 'pending' status
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_customer_user',
                    'value'   => $current_user_id,
                    'compare' => '=',
                ),
            ),
        );
        $unpaid_invoices = get_posts($args);

        // Add classes and inline CSS for centering
        return "<h1 class='centered' style='text-align: center; margin: 0 auto; font-size: 45px;'>" . count($unpaid_invoices) . "</h1><p class='centered' style='text-align: center; font-size: 18px;'>New Invoices</p>";
    }
}




/**
 * ShortCode function for Transactions table
 */

// Register the [transactions] shortcode
function transactions_shortcode_output() {
    ob_start();
    ?>
    <div class="trans-card">
        <div class="trans-card-column trans-card-left">
            <?php
            $current_user = wp_get_current_user();

            // Check if the user is logged in
            if ($current_user->ID !== 0) {
                $customer_id = $current_user->ID;

                $args = array(
                    'numberposts' => 3, // Limit the number of orders to 3
                    'post_type'   => 'shop_order',
                    'post_status' => array(
                        'wc-processing',
                        'wc-on-hold',
                        'wc-completed',
                        'wc-cancelled',
                        'wc-refunded',
                        'wc-failed',
                        'wc-pending',
                        'wc-partially-paid',
                    ),
                    'meta_key'    => '_customer_user',
                    'meta_value'  => $customer_id,
                );

                $orders = get_posts($args);

                if ($orders) {
                    echo '<h2 class="trans-card-heading">Recent Transactions</h2>';
                    echo '<table class="trans-card-table">';
                    echo '<tbody>';

                    foreach ($orders as $order) {
                        $order_id = $order->ID;
                        $order_status = str_replace('wc-', '', $order->post_status);
                        $order_date = get_the_date('F j, Y', $order_id);
                        $order_time = get_the_time('g:i A', $order_id);

                        // Initialize variables
                        $amount_to_be_paid = 0;
                        $payment_method = '';
                        $product_names = array();

                        // Check if order status is 'wc-partially-paid'
                        if ($order_status === 'partially-paid') {
                            $order = wc_get_order($order_id);

                            if ($order) {
                                // Retrieve the order total
                                $order_total = $order->get_total();

                                // Calculate 50% of the order total
                                $amount_paid = $order_total * 0.5;

                                // The amount to be paid is the same as the amount paid for partially paid order status
                                $amount_to_be_paid = $amount_paid;

                                // Set the payment method as 'Wallet Payment'
                                $payment_method = 'Wallet Payment';

                                // Retrieve the product names from the order items
                                $order_items = $order->get_items();
                                foreach ($order_items as $item_id => $item) {
                                    $product = $item->get_product();
                                    if ($product) {
                                        $product_names[] = $product->get_name();
                                    }
                                }
                            }
                        } else {
                            // For other order statuses, retrieve the original order object
                            $original_order = wc_get_order($order_id);

                            if ($original_order) {
                                // Retrieve the amount paid
                                $amount_paid = $original_order->get_total();

                                // The amount to be paid is the same as the amount paid for other order statuses
                                $amount_to_be_paid = $amount_paid;

                                $payment_method = $original_order->get_payment_method_title();

                                // Retrieve the product names from the order items
                                $order_items = $original_order->get_items();
                                foreach ($order_items as $item_id => $item) {
                                    $product = $item->get_product();
                                    if ($product) {
                                        $product_names[] = $product->get_name();
                                    }
                                }
                            }
                        }

                        // Output the data inside table rows
                        echo '<tr>';
                        echo '<th class="trans-card-th">Transaction ID</th>';
                        echo '<td class="trans-card-td">' . $order_id . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Transaction Status</th>';
                        echo '<td class="trans-card-td">' . $order_status . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Amount</th>';
                        echo '<td class="trans-card-td">' . wc_price($amount_to_be_paid) . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Date</th>';
                        echo '<td class="trans-card-td">' . $order_date . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Time</th>';
                        echo '<td class="trans-card-td">' . $order_time . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Payment Method</th>';
                        echo '<td class="trans-card-td">' . $payment_method . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Transaction Detail</th>';
                        echo '<td class="trans-card-td">' . implode(', ', $product_names) . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<th class="trans-card-th">Action</th>';
                        echo '<td class="trans-card-td">';
                        $view_order_url = wc_get_account_endpoint_url('view-order') . '/' . $order_id;
                        echo '<a href="' . $view_order_url . '" class="trans-card-button">View</a>';
                        echo '</td>';
                        echo '</tr>';
                        echo '<tr class="trans-card-separator"><td colspan="2"></td></tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';

                    // Add the "View Older Transactions" button
                    $view_all_orders_url = wc_get_account_endpoint_url('orders');
                    echo '<p><a href="' . $view_all_orders_url . '" class="trans-card-button">View Older Transactions</a></p>';
                } else {
                    echo '<p>All transaction history will appear here.</p>';
                }
            } else {
                echo '<p>Please log in to view your transaction history.</p>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('transactions', 'transactions_shortcode_output');




function transaction_status_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $user_id = $user->ID;

        // Manually define the order statuses you want to display
        $order_statuses_to_display = array(
            'completed' => 'Complete',
            'pending' => 'Pending',
            'processing' => 'Processing',
            'on-hold' => 'On Hold',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
            'failed' => 'Failed',
        );

        // Initialize an array to store status counts
        $status_counts = array();

        // Loop through the manually defined order statuses and count orders for the current user
        foreach ($order_statuses_to_display as $status => $label) {
            $count = wc_get_orders(array(
                'status' => $status,
                'customer' => $user_id,
            ));
            $status_counts[$label] = count($count);
        }

        // Create the HTML output
        $output = '<div class="invoice-status-counts">';
        foreach ($status_counts as $label => $count) {
            $output .= '<div class="status-item">
                <span class="status-label">' . $label . '</span>
                <span class="status-count">(' . $count . ')</span>
            </div>';
        }
        $output .= '</div>';

        return $output;
    }

    return 'Please log in to view transaction status.';
}
add_shortcode('transaction_status', 'transaction_status_shortcode');
