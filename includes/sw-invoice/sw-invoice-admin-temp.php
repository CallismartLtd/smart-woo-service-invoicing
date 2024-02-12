<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dropdown for Invoice Type with filter for custom options.
 *
 * @param string $invoice_type The selected invoice type (optional).
 *
 * @since 1.0.0
 */
function sw_invoice_type_dropdown($invoice_type = null) {
    // Default options
    $options = array(
        '' => 'Select Invoice Type',
        'New Service Invoice' => 'New Service Invoice',
        'Service Renewal Invoice' => 'Service Renewal Invoice',
        'Service Upgrade Invoice' => 'Service Upgrade Invoice',
        'Service Downgrade Invoice' => 'Service Downgrade Invoice',
    );

    // Allow developers to add custom options
    $custom_options = apply_filters('sw_invoice_type_options', array());

    // Merge default and custom options
    $options = array_merge($options, $custom_options);

    // Output the dropdown HTML
    echo '<select class="sw-form-input" name="invoice_type">';
    foreach ($options as $value => $label) {
        $is_selected = ($value === $invoice_type) ? 'selected="selected"' : '';
        echo "<option value='$value' $is_selected>$label</option>";
    }
    echo '</select>';
}



/**
 * Dropdown for Invoice Payment Status with filter for custom options.
 *
 * @param string $payment_status The selected invoice status (optional).
 *
 * @since 1.0.0
 */
function sw_invoice_payment_payment_status_dropdown($payment_status = null) {
    // Default options
    $options = array(
        'select_invoice_payment_status' => 'Select Payment Status',
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
        'due' => 'Due',
        'cancelled' => 'Cancel',
    );

    // Allow developers to add custom options
    $custom_options = apply_filters( 'sw_invoice_payment_status', array() );

    // Merge default and custom options
    $options = array_merge($options, $custom_options);

    // Output the dropdown HTML
    echo '<select class="sw-form-input" name="payment_status">';
    foreach ($options as $value => $label) {
        $is_selected = ($value === $payment_status) ? 'selected="selected"' : '';
        echo "<option value='$value' $is_selected>$label</option>";
    }
    echo '</select>';
}

/**
 * Dropdown for Smart Woo Product with filter for custom options.
 *
 * @param int $product_id The selected Product ID (optional).
 *
 * @since 1.0.0
 */

 function sw_product_dropdown($selected_product_id = null, $required = false) {
    // Fetch all products of type "sw_service"
    $products = wc_get_products(array(
        'type'   => 'sw_product',
        'status' => 'publish',
    ));

    // Initialize the dropdown HTML
    $dropdown_html = '<select class="sw-form-input" name="product_id" ' . ($required ? 'required' : '') . '>'; 

    // Add the default selection option
    $dropdown_html .= '<option value="">Select Service Product</option>';

    // Loop through each product
    foreach ($products as $product) {
        // Get the product ID and name
        $product_id = $product->get_id();
        $product_name = $product->get_name();

        // Check if the current product is selected
        $selected = ($product_id == $selected_product_id) ? 'selected' : '';

        // Add the option to the dropdown
        $dropdown_html .= '<option value="' . esc_attr($product_id) . '" ' . $selected . '>' . esc_html($product_name) . '</option>';
    }

    // Close the dropdown HTML
    $dropdown_html .= '</select>';

    // Output the dropdown HTML
    return $dropdown_html;
}


/**
 * Invoice creation form
 */
function sw_render_create_invoice_form(){

    ?>
    <div class="sw-form-container">
    <form method="post" action="">
        <!-- Choose a Client -->
        <div class="sw-form-row">
        <label for="user_id" class="sw-form-label">Choose a Client *</label>
        <span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
        <?php
        // WordPress User Dropdown
        wp_dropdown_users(array(
            'name' => 'user_id',
            'show_option_none' => 'Select User',
            'class'            => 'sw-form-input',
        ));
        ?>
        </div>

           <!-- Service Products -->
        <div class="sw-form-row">
            <label for="service_products" class="sw-form-label">Add Product *</label>
            <span class="sw-field-description" title="Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>
            <?php
            // Custom Function: Dropdown for Service Products
            echo sw_product_dropdown();
            ?>
        </div>

        <!-- Service Type -->
        <div class="sw-form-row">
            <label for="service_type" class="sw-form-label">Invoice Type *</label>
            <span class="sw-field-description" title="Enter the service type (optional)">?</span>
            <?php
            sw_invoice_type_dropdown();
            ?>
        </div>

        <!-- Service ID-->
        <div class="sw-form-row">
            <label for="service_id" class="sw-form-label">Service ID (optional)</label>
            <span class="sw-field-description" title="associate this invoice with service.">?</span>
            <input type="text" class="sw-form-input" name="service_id" id="service_id">
        </div>

        <!-- Fee -->
        <div class="sw-form-row">
            <label for="fee" class="sw-form-label">Fee (optional)</label>
            <span class="sw-field-description" title="charge a fee for the invoice">?</span>
            <input type="number" class="sw-form-input" name="fee" id="fee" step="0.01">
        </div>

        <!-- Payment status -->
        <div class="sw-form-row">
            <label for="payment_status" class="sw-form-label">Payment Status *</label>
            <span class="sw-field-description" title="Choose a payment status. If the status is unpaid, a new order will be created.">?</span>
            <?php
            sw_invoice_payment_payment_status_dropdown();
            ?>
        </div>

        <!-- Input field for Due Date -->
        <div class="sw-form-row">
        <label for="due_date" class="sw-form-label">Date Due *</label>
        <span class="sw-field-description" title="Choose the date due.">?</span>
        <input type="datetime-local" class="sw-form-input" name="due_date" id="due_date">
        </div>

        <input type="submit" class="sw-blue-button" name ="create_invoice" value="Create Invoice">
    </form>
    </div>
    <?php
}


/**
 * Invoice Edit form
 * 
 */

 function sw_render_edit_invoice_form( $existingInvoice ) {
 
    ?>        
    <div class="sw-form-container">

    <form method="post" action="">
        <!-- Populate existing data in the form -->
        <input type="hidden" name="invoice_id" value="<?php echo esc_attr( $existingInvoice->getInvoiceId() ); ?>">
                
        <!-- Choose a Client -->
        <div class="sw-form-row">
        <label for="user_id" class="sw-form-label">Choose a Client:</label>
        <span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
        <?php
        // WordPress User Dropdown
        wp_dropdown_users(array(
            'name' => 'user_id',
            'show_option_none' => 'Select User',
            'selected' => $existingInvoice->getUserId(),
            'class'   => 'sw-form-input',
        ));
        ?>
        </div>

        <!-- Service Products -->
        <div class="sw-form-row">
        <label for="service_products" class="sw-form-label">Service Products:</label>
        <span class="sw-field-description" title="Edit product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>
        <?php
        // Custom Function: Dropdown for Service Products
        echo sw_product_dropdown($existingInvoice->getProductId());
        ?>
        </div>
      <!-- Service Type -->
      <div class="sw-form-row">
            <label for="service_type" class="sw-form-label">Invoice Type</label>
            <span class="sw-field-description" title="Enter the service type (optional)">?</span>
        <?php
        sw_invoice_type_dropdown($existingInvoice->getInvoiceType());
        ?>
        </div>

        <!-- Service ID-->
        <div class="sw-form-row">
            <label for="service_id" class="sw-form-label">Service ID (optional)</label>
            <span class="sw-field-description" title="associate this invoice with service.">?</span>
            <input type="text" name="service_id" class="sw-form-input" id="service_id" value="<?php echo esc_attr($existingInvoice->getServiceId()); ?>">
        </div>

        <!-- Fee -->
        <div class="sw-form-row">
            <label for="fee" class="sw-form-label">Fee</label>
            <span class="sw-field-description" title="charge a fee for the invoice">?</span>
            <input type="number" name="fee" class="sw-form-input" id="fee" step="0.01" value="<?php echo esc_attr($existingInvoice->getFee()); ?>">
        </div>

        <!-- Payment status -->
        <div class="sw-form-row">
            <label for="payment_status" class="sw-form-label">Payment Status</label>
            <span class="sw-field-description" title="Choose a payment status. If the status is unpaid, a new order will be created.">?</span>
            <?php
            sw_invoice_payment_payment_status_dropdown($existingInvoice->getPaymentStatus());
            ?>
        </div>

        <!-- Input field for Due Date -->
        <div class="sw-form-row">
        <label for="due_date" class="sw-form-label">Date Due</label>
        <span class="sw-field-description" title="Choose the date due.">?</span>
        <input type="datetime-local" class="sw-form-input" name="due_date" id="due_date" value="<?php echo esc_attr( $existingInvoice->getDateDue() );?>">
        </div>

        <input type="submit" class="sw-blue-button" name="sw_update_invoice" value="Update Invoice">
    </form>
    <?php
}


/**
 * Dropdown for Invoice ID with filter for custom options.
 *
 * @param string $selected_invoice_id The selected invoice ID (optional).
 *
 * @since 1.0.0
 */
function sw_invoice_id_dropdown($selected_invoice_id = null) {
    // Fetch invoice IDs from your database or any other source
    $invoices = Sw_Invoice_Database::get_all_invoices();

    // Output the dropdown HTML
    echo '<select class="sw-form-input" name="invoice_id">';
    echo '<option value="" selected="selected">Select Invoice ID</option>';

    foreach ($invoices as $invoice) {
        // Check if the method to get the invoice ID exists
        if (method_exists($invoice, 'getInvoiceId')) {
            $invoice_id = $invoice->getInvoiceId(); // Replace with the actual method to get the invoice ID
            $is_selected = ($invoice_id === $selected_invoice_id) ? 'selected="selected"' : '';
            echo "<option value='$invoice_id' $is_selected>$invoice_id</option>";
        }
    }

    echo '</select>';
}


/**
 * Invoice Adnin main page
 */

 // Default function for the "Invoices" dashboard
function sw_invoice_dash() {
    echo '<h2>Invoice Dashboard</h2>';

    // Display the simplified table of all invoices
    echo get_simple_invoices_table();
}

// Function to generate the simplified HTML table for all invoices
function get_simple_invoices_table() {
    // Retrieve all invoices from the database
    $all_invoices = Sw_Invoice_Database::get_all_invoices();

    //Output the count of invoices
    echo '<div style="display: flex; justify-content: space-between; align-items: right;">';
    echo '<div>' . esc_html(sw_invoice_admin_status_nav_button()) . '</div>';
    echo '</div>';

    // Check if there are any invoices
    if (empty($all_invoices)) {
        return '<p>No invoices found.</p>';
    }

    // Start building the simplified HTML table
    $table_html = '<table class="widefat fixed striped">';
    $table_html .= '<thead>';
    $table_html .= '<tr>';
    $table_html .= '<th>Invoice ID</th>';
    $table_html .= '<th>Service ID</th>';
    $table_html .= '<th>Invoice Type</th>';
    $table_html .= '<th>Payment Status</th>';
    $table_html .= '<th>Date Created</th>';
    $table_html .= '<th>Action</th>';
    $table_html .= '</tr>';
    $table_html .= '</thead>';
    $table_html .= '<tbody>';

    // Loop through each invoice and add a row to the table
    foreach ($all_invoices as $invoice) {
        $table_html .= '<tr>';
        $table_html .= '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
        $table_html .= '<td>' . esc_html( $invoice->getServiceId() ) . '</td>';
        $table_html .= '<td>' . esc_html( $invoice->getInvoiceType() ) . '</td>';
        $table_html .= '<td>' . esc_html( ucfirst( $invoice->getPaymentStatus() ) ) . '</td>';
        $table_html .= '<td>' . esc_html( $invoice->getDateCreated() ) . '</td>';
        $table_html .= '<td><a href="?page=sw-invoices&action=view-invoice&invoice_id=' . esc_attr($invoice->getInvoiceId()) . '">View</a></td>';
        $table_html .= '</tr>';
    }

    $table_html .= '</tbody>';
    $table_html .= '</table>';

    echo $table_html;
    echo '<p style="text-align: right;">' . count($all_invoices) . ' items</p>';

}



/**
 * Invoice Details page(Admin)
 */

 // Function to handle viewing a specific invoice
function sw_view_invoice_page() {

        echo '<h2>Invoice Details</h2>';

    echo '<div class="invoice-details">';

    // Assuming the invoice ID is passed in the URL as 'invoice_id'
    $invoice_id = isset($_GET['invoice_id']) ? sanitize_text_field($_GET['invoice_id']) : null;

    // Fetch the invoice data based on the provided invoice_id
    $invoice = Sw_Invoice_Database::get_invoice_by_id($invoice_id);

    if ($invoice) {
        // Get user's full name using WordPress function
        $user_full_name = get_user_meta($invoice->getUserId(), 'first_name', true) . ' ' . get_user_meta($invoice->getUserId(), 'last_name', true);

        // Get product name using WooCommerce function
        $product_name = wc_get_product($invoice->getProductId())->get_name();
        $paymentStatus = esc_html($invoice->getPaymentStatus());
       // Assuming these properties return date strings or null, replace them with the actual properties or methods from your Invoice class.
        $dateCreated = $invoice->getDateCreated();
        $datePaid = $invoice->getDatePaid();
        $dateDue = $invoice->getDateDue();
        // Format the dates or display 'Not Available'
        $formattedDateCreated = sw_check_and_format($dateCreated);
        $formattedDatePaid = sw_check_and_format($datePaid);
        $formattedDateDue = sw_check_and_format($dateDue);
        echo '<h2>' . esc_html($invoice->getInvoiceType()) . '</h2>';
        // Display detailed information about the invoice
        echo '<p class="invoice-details-item"><span>Payment Status:</span> <span style="background-color: red; color: white; font-weight: bold; padding: 4px; border-radius: 4px;">' . ucfirst( $paymentStatus ) . '</span></p>';
        echo '<p class="invoice-details-item"><span>Invoice ID:</span>' . esc_html($invoice->getInvoiceId()) . '</p>';
        echo '<p class="invoice-details-item"><span>User Name:</span>' . esc_html($user_full_name) . '</p>';
        echo '<p class="invoice-details-item"><span>Product Name:</span>' . esc_html($product_name) . '</p>';
        echo '<p class="invoice-details-item"><span>Amount:</span>' . wc_price($invoice->getAmount()) . '</p>';
        echo '<p class="invoice-details-item"><span>Invoice Type:</span>' . esc_html($invoice->getInvoiceType()) . '</p>';
        echo '<p class="invoice-details-item"><span>Service ID:</span>' . esc_html($invoice->getServiceId()) . '</p>';
        echo '<p class="invoice-details-item"><span>Fee:</span>' . wc_price($invoice->getFee()) . '</p>';
        echo '<p class="invoice-details-item"><span>Order ID:</span>' . esc_html($invoice->getOrderId()) . '</p>';
        echo '<p class="invoice-details-item"><span>Payment Gateway:</span>' . esc_html($invoice->getPaymentGateway()) . '</p>';
        echo '<p class="invoice-details-item"><span>Transaction ID:</span>' . esc_html($invoice->getTransactionId()) . '</p>';
        // Display the formatted dates
        echo '<p class="invoice-details-item"><span>Date Created:</span>' . esc_html($formattedDateCreated) . '</p>';
        echo '<p class="invoice-details-item"><span>Date Paid:</span>' . esc_html($formattedDatePaid) . '</p>';
        echo '<p class="invoice-details-item"><span>Date Due:</span>' . esc_html($formattedDateDue) . '</p>';
        echo '<p class="invoice-details-item"><span>Total:</span>' . wc_price($invoice->getTotal()) . '</p>';

        // Show billing address
        echo '<p class="invoice-details-item"><span>Billing Address:</span>' . esc_html($invoice->getBillingAddress()) . '</p>';

        // Display an "Edit Invoice" button with a link to the edit page
        echo '<a class="button" href="?page=sw-invoices&action=edit-invoice&invoice_id=' . $invoice_id . '">Edit Invoice</a>';
        echo sw_delete_invoice_button( $invoice_id );
 
    } else {
        wp_die( '<p class="invoice-details-item">Invoice not found.</p>' );
    }

    echo '</div>'; // Close the container div


    // Check if there is a service ID associated with the invoice
    if ( $invoice->getServiceId() ) {
        // Get additional details about the service using sw_get_service function
        $service_details = sw_get_service(null, $service_id = $invoice->getServiceId());

        // Display service details
        if ($service_details) {
            $service_name = $service_details->service_name;
            $billing_cycle = $service_details->billing_cycle;
            $end_date = date('l, F jS Y', strtotime($service_details->end_date));
            $service_id = $invoice->getServiceId();
            echo '<div class="serv-details-card">';

            echo '<h3> Related Service Details</h3>';
            echo '<p class="invoice-details-item"><span> Service Name:</span>' . esc_html($service_name) . '</p>';
            echo '<p class="invoice-details-item"><span>Billing Cycle:</span>' . esc_html($billing_cycle) . '</p>';
            echo '<p class="invoice-details-item"><span>End Date:</span>' . esc_html($end_date) . '</p>';
            echo '<a class="button" href="admin.php?page=sw-admin&action=service_details&service_id=' . $service_id . '">More about Service</a>';

         
        } else {
            echo '<p class="invoice-detail"><span> </span>No details found for the service ID associated with this invoice.</p>';
        }
     echo '</div>';
    }
}




// Function to display buttons for different invoice statuses
function sw_invoice_admin_status_nav_button() {
    // Define an array of invoice statuses
    $invoice_statuses = array(
        'Paid' => 'paid',
        'Unpaid' => 'unpaid',
        'Due'=> 'Due',
        'Cancelled' => 'cancelled',
        // Change 'future_status' to the desired value for future inclusions
    );

    echo '<div class="invoice-status-buttons" style="margin-top: 10px; margin-bottom: 10px;">';
    echo '<h2>Filter by Invoice Status:</h2>';

    // Get the current status (payment_status)
    $current_status = isset($_GET['payment_status']) ? sanitize_text_field($_GET['payment_status']) : '';

    // Display buttons for each invoice status
    foreach ($invoice_statuses as $label => $status) {
        // Generate the URL for the Invoice by Status page with the current status
        $url = admin_url('admin.php?page=manage-invoices&action=invoice-by-status&payment_status=' . $status);

        // Determine the button style based on the current status
        $button_style = ($status === $current_status) ? 'background-color: #C21E56; color: #fff;' : 'background-color: #0000ff; color: #fff;';

        // Output the button with inline styles
        echo '<a href="' . esc_url($url) . '" class="button" style="' . esc_attr($button_style) . '">' . esc_html($label) . '</a>';
    }

    echo '</div>';
}


/**
 * Invoice by status template
 */

 // Function to handle displaying invoices based on payment status
function sw_handle_admin_invoice_by_status() {
    $payment_status = isset($_GET['payment_status']) ? sanitize_text_field($_GET['payment_status']) : 'pending';

    // Get invoices based on payment status
    $invoices = Sw_Invoice_Database::get_invoices_by_payment_status($payment_status);

    echo '<div class="wrap"><h1>Invoices by Payment Status</h1>';

    // Display the selected payment status
    echo '<h2>Payment Status: ' . esc_html($payment_status) . '</h2>';
    sw_invoice_admin_status_nav_button();
    // Check if there are any invoices
    if (!empty($invoices)) {
        // Display the table of invoices
        echo '<table class="widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Invoice ID</th>';
        echo '<th>Invoice Type</th>';
        echo '<th>Service ID</th>';
        echo '<th>Amount</th>';
        echo '<th>Fee</th>';
        echo '<th>Total</th>';
        echo '<th>Action</th>';

        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through each invoice and add a row to the table
        foreach ($invoices as $invoice) {
            echo '<tr>';
            echo '<td>' . esc_html($invoice->getInvoiceId()) . '</td>';
            echo '<td>' . esc_html($invoice->getInvoiceType()) . '</td>';
            echo '<td>' . esc_html($invoice->getServiceId()) . '</td>';
            echo '<td>' . esc_html($invoice->getAmount()) . '</td>';
            echo '<td>' . wc_price($invoice->getFee()) . '</td>';
            echo '<td>' . wc_price($invoice->getTotal()) . '</td>';
            echo '<td><a href="?page=sw-invoices&action=view-invoice&invoice_id=' . esc_attr($invoice->getInvoiceId()) . '">View</a></td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '<p style="text-align: right;">' . count($invoices) . ' items</p>';

    } else {
        echo '<p>No invoices found for the selected payment status.</p>';
    }

    echo '</div>';
}