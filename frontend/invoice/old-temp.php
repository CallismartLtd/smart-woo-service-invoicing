<?php
/**
 * This line is for the part for the shortcode
 */

 function generate_invoice_content() {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        // User is not logged in, show a message to log in
        return 'Please log in to view this content.';
    }

    $current_user_id = get_current_user_id();
    $current_user = wp_get_current_user();
    sw_get_navbar($current_user_id);

    echo do_shortcode('[unpaid_invoices_count]');
    echo '<br>';
    echo do_shortcode('[invoices]');



    

        // Get the order ID from the query parameters
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;



        // Check if the order ID is valid
        if ($order_id <= 0) {
            return 'Invalid order ID.';
        }

        // Get the order details
        $order = wc_get_order($order_id);

        // Check if the order exists
        if (!$order) {
            return 'Invalid order ID.';
        }


        // Retrieve order information
        $order_number = $order->get_order_number();
        $order_date = $order->get_date_created()->format('F d, Y @ h:i A');
        $customer_company_name = $order->get_billing_company();
        $customer_first_name = $order->get_billing_first_name();
        $customer_last_name = $order->get_billing_last_name();
        $customer_email = $order->get_billing_email();
        $customer_phone = $order->get_billing_phone();
        $customer_billing_address = $order->get_billing_address_1();
        $customer_billing_city = $order->get_billing_city();
        $customer_billing_state = $order->get_billing_state();
        $customer_billing_postcode = $order->get_billing_postcode();
        $customer_billing_country = $order->get_billing_country();
        $invoicePrefix = sw_get_invoice_number_prefix();

        // Get Business Name and Logo URL from invoice settings
        $business_name = get_option('sw_business_name', '');
        $invoice_logo_url = get_option('invoice_logo_url');
        $admin_phone_number = get_option('admin_phone_number', '');

        // Determine the order status
        $order_status = $order->get_status();

        // Initialize the invoice status variable
        $invoice_status = '';

       // Set the invoice status based on the order status, including refunded orders
if ($order_status === 'refunded') {
    $invoice_status = 'REFUNDED';
} elseif (in_array($order_status, ['processing', 'completed'])) {
    $invoice_status = 'PAID';
} elseif (in_array($order_status, ['pending', 'on-hold'])) {
    $invoice_status = 'UNPAID';
} elseif ($order_status === 'cancelled') {
    $invoice_status = 'CANCELLED';
}


        // Generate the invoice content
        $invoice_content = '<div class="invoice-container">';
        $invoice_content .= '<div class="invoice-preview">';

        // Header section
        $invoice_content .= '<header class="invoice-header">';
        $invoice_content .= '<div class="logo">';
        $invoice_content .= '<img src="' . esc_url($invoice_logo_url) . '" alt="Site Logo">';
        $invoice_content .= '</div>';
        $invoice_content .= '<div class="invoice-status ' . strtolower($invoice_status) . '">';
        $invoice_content .= '<p>' . esc_html($invoice_status) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</header>';

        // Get the service name from order meta
        $service_name = get_post_meta($order_id, 'Service Name', true);

        // Move the header above the invoice number
        $invoice_content .= '<div class="invoice-number">';
        $invoice_content .= '<p> Invoice ' .esc_html($invoicePrefix). '-' . esc_html($order_number);

        // Check if the service name is available and append it to the same line
        if (!empty($service_name)) {
            $invoice_content .= ' for ' . esc_html($service_name);
        }

        $invoice_content .= '</p>';
        $invoice_content .= '</div>';


        // Invoice Reference (Client Details) section
        $invoice_content .= '<section class="invoice-details-container">';
        $invoice_content .= '<div class="invoice-details-left">';
        $invoice_content .= '<h3>Invoiced To:</h3>';
        $invoice_content .= '<div class="invoice-customer-info">';
        $invoice_content .= '<p>' . esc_html($customer_company_name) . '</p>';
        $invoice_content .= '<p>' . esc_html($customer_first_name) . ' ' . esc_html($customer_last_name) . '</p>';
        $invoice_content .= '<p>Email: ' . esc_html($customer_email) . '</p>';
        $invoice_content .= '<p>Phone: ' . esc_html($customer_phone) . '</p>';
        $invoice_content .= '<p>' . esc_html($customer_billing_address) . '</p>';
        $invoice_content .= '<p>' . esc_html($customer_billing_city) . ', ' . esc_html($customer_billing_state) . ' ' . esc_html($customer_billing_postcode) . '</p>';
        $invoice_content .= '<p>' . esc_html($customer_billing_country) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</div>';
        
        $invoice_content .= '<div class="invoice-details-right">';
        $invoice_content .= '<h3>Pay To:</h3>';
        $invoice_content .= '<div class="invoice-business-info">';
        $invoice_content .= '<p>' . esc_html($business_name) . '</p>';
        $invoice_content .= '<p>' . get_option('woocommerce_store_address') . '</p>';
        $invoice_content .= '<p>' . get_option('woocommerce_store_city') . ', ' . get_option('woocommerce_store_state') . ' ' . get_option('woocommerce_store_postcode') . '</p>';
        $invoice_content .= '<p>' . get_option('woocommerce_default_country') . '</p>';
        $admin_phone_numbers = get_option('admin_phone_numbers', ''); // Get saved admin phone numbers
        $admin_phone_numbers = explode(',', $admin_phone_numbers); // Split phone numbers by comma
        $admin_phone_numbers = implode(' | ', $admin_phone_numbers); // Join phone numbers with a vertical bar
        $invoice_content .= '<p>' . esc_html($admin_phone_numbers) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</div>';
        $invoice_content .= '</section>';

       

        // Invoice Date and Payment Method section
        $invoice_content .= '<section class="invoice-date-payment">';
        $invoice_content .= '<div class="invoice-date">';
        $invoice_content .= '<h4>Invoice Date:</h4>';
        $invoice_content .= '<p>' . esc_html($order_date) . '</p>';
        $invoice_content .= '</div>';

        // Get payment method for the order
        $payment_method = get_post_meta($order_id, '_payment_method_title', true);

        $invoice_content .= '<div class="payment-method">';
        $invoice_content .= '<h4>Payment Method:</h4>';
        $invoice_content .= '<p>' . esc_html($payment_method) . '</p>';
        $invoice_content .= '</div>';
        $invoice_content .= '</section>';

        // Get the URL of the invoice watermark image from the settings
        $invoice_watermark_url = get_option('invoice_watermark_url', '');

        // Check if the watermark URL is not empty
        if (!empty($invoice_watermark_url)) {
            // Add the watermark image to the invoice content with CSS styles
            $invoice_content .= '<div class="invoice-watermark">';
            $invoice_content .= '<img src="' . esc_url($invoice_watermark_url) . '" alt="Invoice Watermark" class="watermark-image">';
            $invoice_content .= '</div>';
        }

        // Invoice Items section
        $invoice_content .= '<section class="invoice-items">';
        $invoice_content .= '<div class="invoice-card">';

        // Invoice Items header with Description and Amount
        $invoice_content .= '<div class="invoice-card-header">';
        $invoice_content .= '<h4 class="description-heading">Description</h4>';
        $invoice_content .= '<h4 class="amount-heading">Amount</h4>';
        $invoice_content .= '</div>';

        // Loop through order items
        $subtotal = 0; // Initialize subtotal
        foreach ($order->get_items() as $item) {
            $product_name = $item->get_name();
            $product_price = $item->get_total();

            // Display product name and amount
            $invoice_content .= '<div class="invoice-item">';
            $invoice_content .= '<p class="description">' . esc_html($product_name) . '</p>';
            $invoice_content .= '<p class="amount">' . wc_price($product_price) . '</p>';
            $invoice_content .= '</div>';

            // Add the product price to the subtotal
            $subtotal += $product_price;
        }

        // Subtotal
        $invoice_content .= '<div class="invoice-item">';
        $invoice_content .= '<p class="description">Subtotal</p>';
        $invoice_content .= '<p class="amount">' . wc_price($subtotal) . '</p>';
        $invoice_content .= '</div>';

        // Coupons
        if ($order->get_coupon_codes()) {
            foreach ($order->get_used_coupons() as $coupon_code) {
                $coupon = new WC_Coupon($coupon_code);
                $coupon_amount = $coupon->get_amount();

                $invoice_content .= '<div class="invoice-item">';
                $invoice_content .= '<p class="description">Coupon: ' . esc_html($coupon_code) . '</p>';
                $invoice_content .= '<p class="amount">' . wc_price(-$coupon_amount) . '</p>';
                $invoice_content .= '</div>';
            }
        }

        // Total
        $order_total = $order->get_total();
        $invoice_content .= '<div class="invoice-item total">';
        $invoice_content .= '<p class="description">Total</p>';
        $invoice_content .= '<p class="amount">' . wc_price($order_total) . '</p>';
        $invoice_content .= '</div>';

        $invoice_content .= '</div>'; // Close .invoice-card
        $invoice_content .= '</section>'; // Close Invoice Items section

        // Footer section
        $invoice_content .= '<section class="invoice-footer">';
        $invoice_content .= '<div class="invoice-footer-content">';

        // Mini card container for other items
        $invoice_content .= '<div class="mini-card-container">';

        // Transaction Date
        $transaction_date = $order->get_date_created()->format('l, F j, Y @ h:i A');
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Transaction Date:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($transaction_date) . '</p>';
        $invoice_content .= '</div>';

        // Payment Gateway
        $payment_gateway = get_post_meta($order_id, '_payment_method_title', true);
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Payment Gateway:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($payment_gateway) . '</p>';
        $invoice_content .= '</div>';

        // Transaction ID
        $transaction_id = get_post_meta($order_id, '_transaction_id', true);
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Transaction ID:</p>';
        $invoice_content .= '<p class="footer-value">' . esc_html($transaction_id) . '</p>';
        $invoice_content .= '</div>';

        // Amount
        $invoice_content .= '<div class="mini-card">';
        $invoice_content .= '<p class="footer-label">Amount:</p>';
        $invoice_content .= '<p class="footer-value">' . wc_price($order_total) . '</p>';
        $invoice_content .= '</div>';

        $invoice_content .= '</div>'; // Close .mini-card-container

        // Thank you message
        $invoice_content .= '<p class="thank-you-message">Thank you for the continued business and support. We value you so much.</p>';
        $invoice_content .= '<p class="regards">Kind Regards,</p>';

        $invoice_content .= '</div>'; // Close .invoice-footer-content
        $invoice_content .= '</section>'; // Close Footer section

        // Return the invoice content as a string
        echo $invoice_content;
   
}
add_shortcode('preview_invoices', 'generate_invoice_content');






/**
 * This line is the part we used MPDF library to generate our html to pdf invoice
 */

// Function to generate and download PDF using mPDF
function generate_and_download_invoice_pdf() {
    if (isset($_GET['download_invoice']) && $_GET['download_invoice'] === 'true') {
        // Get the order ID from the query parameters
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

        // Check if WooCommerce is active
        if (class_exists('WooCommerce')) {
            // Get the order ID from the query parameters
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

            // Check if the order ID is valid
            if ($order_id <= 0) {
                echo 'Invalid order ID.';
                exit; // Terminate further execution
            }

            // Get the order details
            $order = wc_get_order($order_id);

            // Check if the order exists
            if (!$order) {
                echo 'Invalid order ID.';
                exit; // Terminate further execution 
            }

            // Define the necessary variables for the PDF
            $invoice_logo_url = get_option('invoice_logo_url');
            $business_name = get_option('business_name', '');
            $admin_phone_numbers = get_option('admin_phone_numbers', '');
            $order_number = $order->get_order_number();
            $order_date = $order->get_date_created()->format('F d, Y @ h:i A');
            $customer_company_name = $order->get_billing_company();
            $customer_first_name = $order->get_billing_first_name();
            $customer_last_name = $order->get_billing_last_name();
            $customer_email = $order->get_billing_email();
            $customer_phone = $order->get_billing_phone();
            $customer_billing_address = $order->get_billing_address_1();
            $customer_billing_city = $order->get_billing_city();
            $customer_billing_state = $order->get_billing_state();
            $customer_billing_postcode = $order->get_billing_postcode();
            $customer_billing_country = $order->get_billing_country();
            $invoicePrefix = sw_get_invoice_number_prefix();

            // Determine the order status
            $order_status = $order->get_status();

            // Initialize the invoice status variable
            $invoice_status = '';

            // Set the invoice status based on the order status, including refunded orders
         if ($order_status === 'refunded') {
         $invoice_status = 'REFUNDED';
        } elseif (in_array($order_status, ['processing', 'completed'])) {
        $invoice_status = 'PAID';
        } elseif (in_array($order_status, ['pending', 'on-hold'])) {
        $invoice_status = 'UNPAID';
       } elseif ($order_status === 'cancelled') {
       $invoice_status = 'CANCELLED';
       }


            // You also need to determine $subtotal and $order_total based on your logic
            $subtotal = 0; // Initialize subtotal
            foreach ($order->get_items() as $item) {
                $product = $item->get_product(); // Define the missing $product variable
                $product_name = $product->get_name(); // Get the product name
                $product_price = $item->get_total();
                $subtotal += $product_price;
            }
            $order_total = $order->get_total();

            // Initialize variables
            $sub_total = 0; // Initialize subtotal
            $total_coupon_amount = 0; // Initialize total coupon amount

            // Generate the invoice content (HTML)
            $invoice_html = '
            <!DOCTYPE html>
            <html>
            <head></head>
            <body>
         
            <!-- Header Section -->
<div class="invoice-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin: 5px; position: relative;">
<div class="invoice-status" style="position: absolute; top: 0; right: 0; text-align: right;">
    <span style="background-color: red; color: white; font-size: 20px; font-weight: bold; padding: 5px;">' . esc_html($invoice_status) . '</span>
</div>


    <div class="logo">
        <img src="' . esc_url($invoice_logo_url) . '" alt="Site Logo" width="300">
    </div>
</div>


            

            <!-- Business Information Section -->
            <div class="business-info" style="text-align: right; font-size: 12px;">
                <p style="font-weight: bold;">' . esc_html($business_name) . '</p>
                <p>' . esc_html(get_option('woocommerce_store_address')) . '</p>
                <p>' . esc_html(get_option('woocommerce_store_city')) . ', ' . esc_html(get_option('woocommerce_store_state')) . ' ' . esc_html(get_option('woocommerce_store_postcode')) . '</p>
                <p>' . esc_html(get_option('woocommerce_default_country')) . '</p>
                <p>' . esc_html($admin_phone_numbers) . '</p>
            </div>
            

            <!-- Invoice Number and Date Section -->
            <div class="invoice-number-container" style="background-color: #f2f2f2; padding: 10px; text-align: center; margin: 10px;">
                <div class="invoice-number" style="font-size: 12px; font-weight: bold;">Invoice ' . esc_html($invoicePrefix). '-' . esc_html($order_number) . '</div>
                <div class="invoice-date" style="font-size: 9px;">Transaction Date: ' . esc_html($order_date) . '</div>
            </div>

            <!-- Invoiced To Section -->
            <div class="invoiced-to" style="text-align: left; font-size: 12px;">
                <h4>Invoiced To:</h4>
                <p style="font-weight: bold;">' . esc_html($customer_company_name) . '</p>
                <p>' . esc_html($customer_first_name) . ' ' . esc_html($customer_last_name) . '</p>
                <p>Email: ' . esc_html($customer_email) . '</p>
                <p>Phone: ' . esc_html($customer_phone) . '</p>
                <p>' . esc_html($customer_billing_address) . ', ' . esc_html($customer_billing_city) . '</p>
                <p>' . esc_html($customer_billing_state) . ' ' . esc_html($customer_billing_postcode) . ', ' . esc_html($customer_billing_country) . '</p>
            </div>
            

            <!-- Invoice Item section -->
            <div class="invoice-items" style="text-align: center; margin: 10px;">
                <div class="invoice-card" style="border: 1px solid #ccc; padding: 3px; margin: 0 auto; max-width: 70%; background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <!-- Invoice Items Header -->
                    <div class="invoice-card-header" style="display: flex; justify-content: space-between; border-bottom: 1px solid #ccc; padding: 3px;">
                        <h4 class="description-heading" style="flex: 1; text-align: left;">Description</h4>
                        <h4 class="amount-heading" style="flex: 1; text-align: right;">Amount</h4>
                    </div>';

            // Loop through order items
            if ($order->get_items()) {
                foreach ($order->get_items() as $item) {
                    $product = $item->get_product(); 
                    $product_name = $product->get_name(); 
                    $product_price = $item->get_total();
            // Get the service name from order meta
            $service_name = get_post_meta($order_id, 'Service Name', true);
            $service_id = get_post_meta($order_id, 'Service ID', true);


                    // Add the product name and amount under description and amount respectively
            $invoice_html .= '
            <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ccc;">
                <p class="pdf-description" style="flex: 1; text-align: left;">' . esc_html($product_name) . ' - ' . esc_html($service_name) . '</p>
                <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price($product_price) . '</p>
            </div>';

                    
                    // Increment subtotal
                    $sub_total += $product_price;
                }
            }

            // Check for coupon codes
            if ($order->get_used_coupons()) {
                foreach ($order->get_used_coupons() as $coupon_code) {
                    $coupon = new WC_Coupon($coupon_code);
                    $coupon_amount = $coupon->get_amount();

                    // Append coupon items to invoice HTML
                    $invoice_html .= '
                    <!-- Coupon -->
                    <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 10px; border-bottom: 1px solid #ccc;">
                        <p class="pdf-description" style="flex: 1; text-align: left;">Coupon: ' . esc_html($coupon_code) . '</p>
                        <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price(-$coupon_amount) . '</p>
                    </div>';
                    
                    // Deduct coupon amount from subtotal
                    $sub_total -= $coupon_amount;
                    // Add coupon amount to the total coupon amount
                    $total_coupon_amount += $coupon_amount;
                }
            }

            // Append Subtotal section
            $invoice_html .= '
            <!-- Subtotal -->
            <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 5px; border-bottom: 1px solid #ccc;">
                <p class="pdf-description" style="flex: 1; text-align: left;">Subtotal:</p>
                <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price($sub_total) . '</p>
            </div>';

            // Append Total section
            $invoice_html .= '
            <!-- Total -->
            <div class="pdf-invoice-item" style="display: flex; justify-content: space-between; padding: 5px; border-bottom: 1px solid #ccc;">
                <p class="pdf-description" style="flex: 1; text-align: left;">Total:</p>
                <p class="pdf-amount" style="flex: 1; text-align: right;">' . wc_price($sub_total - $total_coupon_amount) . '</p>
            </div>

            <!-- Date Generated Section -->
            <div class="pdf-date-generated" style="text-align: center; margin: 10px;">
               This Invoice is automatically generated on ' .current_time('l, F j, Y g:i a', 0).' for this service name: '. esc_html($service_name) . ' with ID: '.esc_html($service_id) . '
            </div>
            </div>
            </div>
            </body>
            </html>';

            // Include mPDF library using the WordPress ABSPATH
              include_once ABSPATH . 'wp-content/plugins/smart-woo-invoice/vendor/autoload.php';


              $invoicePrefix = sw_get_invoice_number_prefix();
            // Create a new mPDF instance
            $pdf = new \Mpdf\Mpdf();

            // Add a page
            $pdf->AddPage();

            // Load the HTML content into mPDF
            $pdf->WriteHTML($invoice_html);

            // Output the PDF as a download with a custom file name
            $file_name = $invoicePrefix . '-' . $order_number . '.pdf';
            $pdf->Output($file_name, 'D');
            exit;
        }
    }
}

// Hook to generate and download PDF when needed
add_action('init', 'generate_and_download_invoice_pdf');
