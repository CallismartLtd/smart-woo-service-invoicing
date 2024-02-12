<?php
/**
 * File name    :   sw-service-admin-temp.php
 * @author      :   Callistus
 * Description  :   Template file for admin Service management
 */

function sw_admin_view_service_details() {
    $service_id = isset($_GET['service_id']) ? sanitize_text_field($_GET['service_id']) : '';
    $service = Sw_Service_Database::get_service_by_id($service_id);

    if ($service) {

        echo '<div class="serv-wrap">';
        echo '<h1>Service Informations</h1>';

        // Retrieve service usage metrics
        echo sw_get_usage_metrics ( $service_id);
        // Display customer details
        display_customer_details($service);
        $access_client_service_url = sw_client_service_url_button( $service );

        // Display service details
        display_service_details($service);
        echo '</div>';

    } else {
        echo '<div class="serv-wrap">';
        echo '<h1>Service Not Found</h1>';
        echo '<p>The requested service does not exist.</p>';
        echo '</div>';
    }
}


function display_customer_details($service) {
    // Fetch customer details
    $user_id = $service->getUserId();
    $user_info = get_userdata($user_id);

    // Set default values using null coalescing operator
    $customer_name = $user_info->first_name . ' ' . $user_info->last_name ?? 'Customer Name Not Found';
    $customer_email = $user_info->user_email ?? 'Email Not Found';
    $customer_billing_address = sw_get_user_billing_address($user_id) ?? 'Billing Address Not Found';
    $customer_phone = get_user_meta($user_id, 'billing_phone', true) ?? 'Phone Number Not Found';

    // Display the customer details in a separate container
    echo '<div class="serv-details-card">';
    echo '<div class="user-service-card">';
    echo '<h2>Client Details</h2>';
    echo '<p><h3>' . esc_html( $customer_name ) . '</h3></p>';
    echo '<p>Email Address: ' . esc_html( $customer_email ) . '</p>';
    echo '<p>Billing Address: ' . esc_html( $customer_billing_address ) . '</p>';
    echo '<p>Phone Number: ' . esc_html( $customer_phone ) . '</p>';
    echo '</div>';
    echo '</div>';
}

function display_service_details($service) {
    // Helper Function to format the date
    function format_readable_date($date) {
        return date('l jS F Y', strtotime($date));
    }

    $main_service_status = sw_service_status( $service->getServiceId() );

    $product_id = $service->getProductId();

    // Get product details from WooCommerce
    $product = wc_get_product($product_id);

    // Set default values using null coalescing operator
    $product_name = $product ? $product->get_name() : 'Product Name Not Found';
    $product_price = $product ? $product->get_price() : 0;
    $currency_symbol = get_woocommerce_currency_symbol();
    $price_with_currency = $currency_symbol . $product_price;

    // Display the formatted date in the service details
    echo '<div class="serv-details-card">';
    echo '<div class="de-service-details-card">';
    echo '<span style="display: inline-block; text-align: right; color: white; background-color: red; padding: 10px; border-radius: 5px; font-weight: bold;">' . $main_service_status . '</span>';
    echo '<h2>Service Details</h2>';
    echo '<h3>' . esc_html($service->getServiceName()) . '</h3>';
    echo '<p>Service ID: ' . esc_html($service->getServiceId()) . '</p>';
    echo '<p>Service Type: ' . esc_html($service->getServiceType()) . '</p>';
    echo '<p>Service URL: ' . esc_html($service->getServiceUrl()) . '</p>';
    echo '<p>Amount: '. esc_html($price_with_currency) .'</p>';
    echo '<p>Billing Cycle: ' . esc_html($service->getBillingCycle()) . '</p>';
    echo '<p>Start Date: ' . format_readable_date($service->getStartDate()) . '</p>';
    echo '<p>Next Payment Date: ' . format_readable_date($service->getNextPaymentDate()) . '</p>';
    echo '<p>End Date: ' . format_readable_date($service->getEndDate()) . '</p>';
    echo '<a href="' . admin_url('admin.php?page=sw-admin&action=edit-service&service_id=' . $service->getServiceId()) . '" class="sw-blue-button">Edit this Service</a>';
    echo sw_delete_service_button( $service->getServiceId() );
    echo '</div>';
    echo '</div>'; // Close wrap
}

/**
 * Plugin Admin Dashboard Page
 */

function sw_main_page(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'sw_service';
    $auto_renew_table_name = $wpdb->prefix . 'sw_invoice_auto_renew';

      
    // Get service counts by name
    $all_services = Sw_Service_Database::get_all_services();

    // Get total services due for renewal
    $due_for_renewal_count = count_due_for_renewal_services();

    // Get total expired services count
    $expired_services_count = count_expired_services();

    $grace_period_services   = count_grace_period_services();

    // Get active services count
    $active_services_count = count_active_services();

    // Get suspended service count
    $suspended_service_acount = count_suspended_services();

    // Get renewed services count
    $renewed_services_count = get_renewed_services_count();

    //Get Active but No Renewal Services count
    $nr_services = count_nr_services();

    // Get user's full name and service IDs for renewed services
    $renewed_services_info = get_renewed_services_info();


       

    
    // Output the content for the admin page
    echo '<div class="serv-wrap">';
    echo '<h1>Smart Woo Service & Invoice Dashboard</h1>';
    echo '<div class="sw-button">';
    echo '<a href="' . admin_url('admin.php?page=sw-admin&action=add-new-service') . '" class="sw-blue-button">Add New</a>';
    echo '<a href="' . admin_url('admin.php?page=sw-mail') . '" class="sw-blue-button">Send Mail</a>';
    echo '<a href="' . admin_url('admin.php?page=sw-options') . '" class="sw-blue-button">Settings</a>';
    echo '</div>';

    echo '<div class="dashboard-container">';

    // Total Counts of Services
    echo '<div class="dashboard-card">';
    echo '<h2>All Services</h2>';
    echo '<p class="count">' . count( $all_services ) . '</p>';
    echo '</div>';

    // Count for active 
    echo '<div class="dashboard-card">';
    echo '<h2>Active</h2>';
    echo '<p class="count">' . $active_services_count . '</p>';
    echo '</div>';

    // Count for active no renewal
    echo '<div class="dashboard-card">';
    echo '<h2>Active NR</h2>';
    echo '<p class="count">' . $nr_services . '</p>';
    echo '</div>';
   
    // Total Service Due for Renewal
    echo '<div class="dashboard-card">';
    echo '<h2>Due</h2>';
    echo '<p class="count">' . $due_for_renewal_count . '</p>';
    echo '</div>';

    // Count for Grace Period
    echo '<div class="dashboard-card">';
    echo '<h2>Grace Period</h2>';
    echo '<p class="count">' . $grace_period_services . '</p>';
    echo '</div>';
 

    // Count for expired
    echo '<div class="dashboard-card">';
    echo '<h2>Expired</h2>';
    echo '<p class="count">' . $expired_services_count . '</p>';
    echo '</div>';

    // Count for Grace Period
    echo '<div class="dashboard-card">';
    echo '<h2>Suspended</h2>';
    echo '<p class="count">' . $suspended_service_acount . '</p>';
    echo '</div>';
    echo '</div>';


    
    // Display child card for Active Services List
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Active Services</h2>';
    echo '<ul>';

    $users_with_services = Sw_Service_Database::get_all_services();
    $active_services_found = false; // Initialize a flag

    if (!empty( $users_with_services ) ) {
        foreach ( $users_with_services as $user ) {
            // Check if the user's service is Active
            $service_status = sw_service_status( $user->getServiceId() );

            if ($service_status == 'Active') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url( 'admin.php?page=sw-admin&action=service_details&service_id=' . $user->getServiceId() );
                echo '<li><a href="' . esc_url( $service_link ) . '">' . esc_html($user->getServiceName() ) . ' - ' . esc_html( $user->getServiceId() ) . '</a></li>';
                $active_services_found = true; // Set the flag to true
            }
        }
    }
     
    if (!$active_services_found) {
        echo '<p>No service is active.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';
        


   // Display child card for Active (NR) Services List
   echo '<div class="dashboard-list-container">';
   echo '<div class="dashboard-list-card">';
   echo '<h2>Non Renewal Services</h2>';
   echo '<p>Active but will not renew when they expire</p>';
   echo '<ul>';

   $users_with_services = sw_get_service();
    $nr_services_found = false; // Initialize a flag

    if (!empty($users_with_services)) {
        foreach ($users_with_services as $user) {
            // Check if the user's service is Active (NR)
            $service_status = sw_service_status( $user->service_id);

            if ($service_status == 'Active (NR)') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url('admin.php?page=sw-admin&action=service_details&service_id=' . $user->service_id);
                echo '<li><a href="' . esc_url($service_link) . '">' . esc_html($user->service_name) . ' - ' . esc_html($user->service_id) . '</a></li>';
                $nr_services_found = true; // Set the flag to true
            }
        }
    }
     
    if (!$nr_services_found) {
        echo '<p>No service is suspended.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';


    // Display names of services due for renewal
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Services Due</h2>';
    echo '<ul>';

    $users_with_services = sw_get_service();
    $due_services_found = false; // Initialize a flag

    if (!empty($users_with_services)) {
        foreach ($users_with_services as $user) {
            // Check if the user's service is expired
            $service_status = sw_service_status( $user->service_id );

            if ($service_status == 'Due for Renewal') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url('admin.php?page=sw-admin&action=service_details&service_id=' . $user->service_id);
                echo '<li><a href="' . esc_url($service_link) . '">' . esc_html($user->service_name) . ' - ' . esc_html($user->service_id) . '</a></li>';
                $due_services_found = true; // Set the flag to true
            }
        }
    }
    // Display a message inside the same <div> if no due for renewal services were found
    if (!$due_services_found) {
        echo '<p>No services have are due.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    // Display child card for Grace Period Services List
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Grace Period</h2>';
    echo '<ul>';

    $users_with_services = Sw_Service_Database::get_all_services();
    $active_services_found = false; // Initialize a flag

    if (!empty( $users_with_services ) ) {
        foreach ( $users_with_services as $user ) {
            // Check if the user's service is Active
            $service_status = sw_service_status( $user->getServiceId() );

            if ($service_status == 'Grace Period') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url( 'admin.php?page=sw-admin&action=service_details&service_id=' . $user->getServiceId() );
                echo '<li><a href="' . esc_url( $service_link ) . '">' . esc_html($user->getServiceName() ) . ' - ' . esc_html( $user->getServiceId() ) . '</a></li>';
                $active_services_found = true; // Set the flag to true
            }
        }
    }
     
    if (!$active_services_found) {
        echo '<p>No service is on grace period.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';




    // Display child card for Cancelled Services List
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Cancelled Services</h2>';
    echo '<ul>';

    $users_with_services = sw_get_service();
    $cancelled_services_found = false; // Initialize a flag

    if (!empty($users_with_services)) {
        foreach ($users_with_services as $user) {
            // Check if the user's service is cancelled
            $service_status = sw_service_status( $user->service_id );

            if ($service_status == 'Cancelled') {
                // Append '=cancelled' to the service link URL using the new structure
                $service_link = admin_url('admin.php?page=sw-admin&action=service_details&service_id=' . $user->service_id . '&servicetype=cancelled');
                echo '<li><a href="' . esc_url($service_link) . '">' . esc_html($user->service_name) . ' - ' . esc_html($user->service_id) . '</a></li>';
                $cancelled_services_found = true; // Set the flag to true
            }
        }
    }
    // Display a message inside the same <div> if no cancelled services were found
    if (!$cancelled_services_found) {
        echo '<p>No services have been Cancelled.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';




        // Display names of expired services
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Expired Services</h2>';
    echo '<ul>';

    $users_with_services = sw_get_service();
    $expired_services_found = false; // Initialize a flag

    if (!empty($users_with_services)) {
        foreach ($users_with_services as $user) {
            // Check if the user's service is expired
            $service_status = sw_service_status( $user->service_id );

            if ($service_status == 'Expired') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url('admin.php?page=sw-admin&action=service_details&service_id=' . $user->service_id);
                echo '<li><a href="' . esc_url($service_link) . '">' . esc_html($user->service_name) . ' - ' . esc_html($user->service_id) . '</a></li>';
                $expired_services_found = true; // Set the flag to true
            }
        }
    }
     
    if (!$expired_services_found) {
        echo '<p>No services have expired.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    // Display child card for Suspended Services List
    echo '<div class="dashboard-list-container">';
    echo '<div class="dashboard-list-card">';
    echo '<h2>Suspended Services</h2>';
    echo '<ul>';

    $users_with_services = Sw_Service_Database::get_all_services();
    $active_services_found = false; // Initialize a flag

    if (!empty( $users_with_services ) ) {
        foreach ( $users_with_services as $user ) {
            // Check if the user's service is Active
            $service_status = sw_service_status( $user->getServiceId() );

            if ($service_status == 'Suspended') {
                // Create the link with the service name's ID as a parameter
                $service_link = admin_url( 'admin.php?page=sw-admin&action=service_details&service_id=' . $user->getServiceId() );
                echo '<li><a href="' . esc_url( $service_link ) . '">' . esc_html($user->getServiceName() ) . ' - ' . esc_html( $user->getServiceId() ) . '</a></li>';
                $active_services_found = true; // Set the flag to true
            }
        }
    }
     
    if (!$active_services_found) {
        echo '<p>No service is suspended.</p>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';


    echo '</div>'; // Close wrap
}



/**
 * Outputs the form fields for adding a new service.
 *
 * @since 1.0.0
 */
function sw_render_add_new_service_form() {
    ?>    
    <form action="" method="post">


        <?php
        // Add nonce for added security
        wp_nonce_field('sw_add_new_service_nonce', 'sw_add_new_service_nonce');
        ?>

        <!-- Service Name -->
        <div class="sw-form-row">
            <label for="service_name" class="sw-form-label">Service Name *</label>
            <span class="sw-field-description" title="Enter the service name (required)">?</span>
            <input type="text" name="service_name" class="sw-form-input" id="service_name" required>
        </div>

        <!-- Service Type -->
        <div class="sw-form-row">
            <label for="service_type" class="sw-form-label">Service Type</label>
            <span class="sw-field-description" title="Enter the service type (optional)">?</span>
            <input type="text" name="service_type" class="sw-form-input" id="service_type">
        </div>

        <!-- Service URL -->
        <div class="sw-form-row">
            <label for="service_url" class="sw-form-label">Service URL</label>
            <span class="sw-field-description" title="Enter the service URL e.g., https:// (optional)">?</span>
            <input type="url" name="service_url" class="sw-form-input" id="service_url">
        </div>

        <!-- Choose a Client -->
        <div class="sw-form-row">
            <label for="user_id" class="sw-form-label">Choose a Client:</label>
            <span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
            <?php
            // WordPress User Dropdown
            wp_dropdown_users(array(
                'name' => 'user_id',
                'show_option_none' => 'Select User',
                'class' => 'sw-form-input', 
            ));
            ?>
        </div>

        <!-- Service Products -->
        <div class="sw-form-row">
            <label for="service_products" class="sw-form-label">Service Products</label>
            <span class="sw-field-description" title="Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>
            <?php
            // Custom Function: Dropdown for Service Products
            echo sw_product_dropdown('', true); // Required dropdown with no preselected product
            ?>
        </div>

        <!-- Start Date -->
        <div class="sw-form-row">
            <label for="start_date" class="sw-form-label">Start Date</label>
            <span class="sw-field-description" title="Choose the start date for the service subscription.">?</span>
            <input type="date" name="start_date" id="start_date" class="sw-form-input" required>
        </div>

        <!-- Billing Cycle -->
        <div class="sw-form-row">
            <label for="billing_cycle" class="sw-form-label">Billing Cycle</label>
            <span class="sw-field-description" title="Choose the billing cycle for the service, invoices are created toward to the end of the billing cycle">?</span>
            <select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
                <option value="">Select billing cycle</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Six Monthly">Six Monthly</option>
                <option value="Yearly">Yearly</option>
            </select>
        </div>

        <!-- Next Payment Date -->
        <div class="sw-form-row">
            <label for="next_payment_date" class="sw-form-label">Next Payment Date</label>
            <span class="sw-field-description" title="Choose the next payment date, services wil be due and invoice is created on this day.">?</span>
            <input type="date" name="next_payment_date" id="next_payment_date" class="sw-form-input" required>
        </div>

        <!-- End Date -->
        <div class="sw-form-row">
            <label for="end_date" class="sw-form-label">End Date</label>
            <span class="sw-field-description" title="Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.">?</span>
            <input type="date" name="end_date" id="end_date" class="sw-form-input" required>
        </div>

        <!-- Set Service Status -->
        <div class="sw-form-row">
            <label for="status" class="sw-form-label">Set Service Status:</label>
            <span class="sw-field-description" title="Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal">?</span>
            <select name="status" id="status" class="sw-form-input">
                <option value="">Auto Calculate</option>
                <option value="Active">Active</option>
                <option value="Active (NR)">Disable Renewal</option>
                <option value="Suspended">Suspend Service</option>
                <option value="Cancelled">Cancel Service</option>
                <option value="Due for Renewal">Due for Renewal</option>
                <option value="Expired">Expired</option>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="sw-form-row">
            <input type="submit" name="add_new_service_submit" class="sw-blue-button" value="Publish">
        </div>
    </form>
    </div>
<?php
}


function sw_render_edit_service_form(){

    // Check if service_id is present in the URL
    if (isset($_GET['service_id'])) {
        $url_service_id = sanitize_text_field($_GET['service_id']);
        $service = Sw_Service_Database::get_service_by_id($url_service_id);

        // Check if the service exists
        if ($service) {
            // Populate the form fields with the retrieved data
            $service_name = $service->getServiceName();
            $service_url = $service->getServiceUrl();
            $service_type =$service->getServiceType();
            $product_id = $service->getProductId();
            $user_id = $service->getUserId();
            $invoice_id = $service->getInvoiceId();
            $start_date = $service->getStartDate();
            $end_date = $service->getEndDate();
            $next_payment_date = $service->getNextPaymentDate();
            $billing_cycle = $service->getBillingCycle();
            $status = $service->getStatus();
        } else {
            // Service with the provided service_id does not exist
            echo '<div class="error"><p>Service not found.</p></div>';
            return;
        }
        
        ?>
        <div class="sw-form-container">

        <form action="" method="post">
            <?php
            // Add nonce for added security
            wp_nonce_field('sw_edit_service_nonce', 'sw_edit_service_nonce');
            ?>
            <div class="sw-form-row">
                <label for="service_name" class="sw-form-label">Service Name *</label>
                <span class="sw-field-description" title="Enter the service name (required)">?</span>
                <input type="text" name="service_name" class="sw-form-input" id="service_name" value="<?php echo esc_attr($service_name); ?>" required>
            </div>
    
            <!-- Service Type -->
            <div class="sw-form-row">
                <label for="service_type" class="sw-form-label">Service Type</label>
                <span class="sw-field-description" title="Enter the service type (optional)">?</span>
                <input type="text" name="service_type" class="sw-form-input" id="service_type" value="<?php echo esc_attr($service_type);?>">
            </div>
    
             <!-- Service URL -->
             <div class="sw-form-row">
                <label for="service_url" class="sw-form-label">Service URL</label>
                <span class="sw-field-description" title="Enter the service URL e.g., https:// (optional)">?</span>
                <input type="url" name="service_url" class="sw-form-input" id="service_url" value="<?php echo esc_url($service_url); ?>" >
            </div>
    
            <!-- Choose a Client -->
            <div class="sw-form-row">
                <label for="user_id" class="sw-form-label">Choose a Client</label>
                <span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
                <?php
                $selected_user = ($user_id) ? get_user_by('ID', $user_id) : false;
                wp_dropdown_users(array(
                    'name' => 'user_id',
                    'selected' => $selected_user ? $selected_user->ID : '',
                    'show_option_none' => 'Select a user',
                    'option_none_value' => '', 
                    'class'    => 'sw-form-input',
                ));
                ?>
            </div>
    
           <!-- Service Products -->
            <div class="sw-form-row">
                <label for="service_products" class="sw-form-label">Service Products</label>
                <span class="sw-field-description" title="Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>

                <?php
                // Custom Function: Dropdown for Service Products
                echo sw_product_dropdown($product_id, true);
                ?>
            </div>
    
            <!-- Invoice ID -->
        <div class="sw-form-row">
            <label for="invoice_id" class="sw-form-label">Invoice ID (optional)</label>
            <span class="sw-field-description" title="associate this service with already created invoice.">?</span>
            <?php echo sw_invoice_id_dropdown($invoice_id); ?>
        </div>
    
        <!-- Start Date -->
        <div class="sw-form-row">
            <label for="start_date" class="sw-form-label">Start Date</label>
            <span class="sw-field-description" title="Choose the start date for the service subscription.">?</span>
            <input type="date" name="start_date" class="sw-form-input" id="start_date" value="<?php echo esc_attr($start_date);?>" required>
        </div>
    
        <!-- Billing Cycle -->
        <div class="sw-form-row">
            <label for="billing_cycle" class="sw-form-label">Billing Cycle</label>
            <span class="sw-field-description" title="Choose the billing cycle for the service, invoices are created toward to the end of the billing cycle">?</span>
            <select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
                <option value="" <?php selected('', $billing_cycle); ?>>Select billing cycle</option>
                <option value="Monthly" <?php selected('Monthly', ucfirst(strtolower($billing_cycle))); ?>>Monthly</option>
                <option value="Quarterly" <?php selected('Quarterly', ucfirst(strtolower($billing_cycle))); ?>>Quarterly</option>
                <option value="Six Monthtly" <?php selected('Six Monthtly', ucwords(strtolower($billing_cycle))); ?>>Six Monthtly</option>
                <option value="Yearly" <?php selected('Yearly', ucfirst(strtolower($billing_cycle))); ?>>Yearly</option>
            </select>
        </div>

    
        <!-- Next Payment Date -->
        <div class="sw-form-row">
            <label for="next_payment_date" class="sw-form-label">Next Payment Date</label>
            <span class="sw-field-description" title="Choose the next payment date, services wil be due and invoice is created on this day.">?</span>
            <input type="date" name="next_payment_date" class="sw-form-input" id="next_payment_date" value="<?php echo esc_attr($next_payment_date); ?>" required>
            </div>
    
        <!-- End Date -->
        <div class="sw-form-row">
            <label for="end_date" class="sw-form-label">End Date</label>
            <span class="sw-field-description" title="Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.">?</span>
            <input type="date" name="end_date" class="sw-form-input" id="end_date" value="<?php echo esc_attr($end_date); ?>" required>
        </div>
        <!-- Set Service Status -->
        <div class="sw-form-row">
            <label for="status" class="sw-form-label">Set Service Status</label>
            <span class="sw-field-description" title="Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal">?</span>
            <select name="status" id="status" class="sw-form-input">
                <option value="" <?php selected(null, $status); ?>>Auto Calculate</option>
                <option value="Active" <?php selected('Active', $status); ?>>Active</option>
                <option value="Active (NR)" <?php selected('Active (NR)', $status); ?>>Disable Renewal</option>
                <option value="Suspended" <?php selected('Suspended', $status); ?>>Suspend Service</option>
                <option value="Suspended" <?php selected('Cancelled', $status); ?>>Cancel Service</option>
                <option value="Due for Renewal" <?php selected('Due for Renewal', $status); ?>>Due for Renewal</option>
                <option value="Expired" <?php selected('Expired', $status); ?>>Expired</option>
            </select>
        </div>
    
            <!-- Submit Button -->
            <input type="submit" name="edit_service_submit" class="sw-blue-button" value="Update Service">
        </form>
        </div>
        <?php

    }
}




/**
 * Render the service ID generator input.
 *
 * @param string|null $service_name Optional. The service name to pre-fill the input.
 * @param bool        $editing      Optional. Whether the input is used for editing. Default is false.
 * @param bool        $required     Optional. Whether the input is required. Default is true.
 */
function sw_render_service_id_generator_input($service_name = null, $editing = false, $required = true) {
    ?>
    <div class="sw-form-row">
        <label for="service-name" class="sw-form-label">Service Name:</label>
        <span class="sw-field-description" title="Service name here">?</span>
        <input type="text" class="sw-form-input" id="service-name" name="service_name" <?php echo $required ? 'required' : ''; ?> value="<?php echo esc_attr($service_name); ?>">
    </div>
    <!-- Add an animated loader element -->
    <div id="swloader">Generating...</div>
    <div class="sw-form-row">
        <label for="generated-service-id" class="sw-form-label">Generated Service ID *</label>
        <span class="sw-field-description" title="Click the button to generate a unique service ID">?</span>
        <input type="text" class="sw-form-input" id="generated-service-id" name="service_id" readonly>
        </div>
    <div class="sw-form-row">
        <label for="button" class="sw-form-label"></label>
        <button id="generate-service-id-btn" type="button" class="sw-red-button">Generate Service ID</button>
    </div>
   
    <script src="<?php echo plugin_dir_url(__FILE__) . '../../assets/js/fetch.js'; ?>"></script>
    <?php
}




/**
 * Retrieve and display service usage metrics.
 *
 * @param int $user_id    User ID.
 * @param int $service_id Service ID.
 */
function sw_get_usage_metrics( $service_id ) {
    $service = Sw_Service_Database::get_service_by_id( $service_id );
    
    $service_name = $service->getServiceName();

    $usage_metrics = sw_check_service_usage( $service_id );

    // Check if metrics are available
    if ( $usage_metrics !== false ) {
        // Extract metrics
        $used_amount           = wc_price( $usage_metrics['used_amount'] );
        $unused_amount         = wc_price( $usage_metrics['unused_amount'] );
        $total_service_cost    = wc_price( $usage_metrics['service_cost'] );
        $average_daily_cost    = wc_price( $usage_metrics['average_daily_cost'] );
        $product_costs         = $usage_metrics['product_costs'];
        $percentage_used       = $usage_metrics['percentage_used'];
        $percentage_unused     = $usage_metrics['percentage_unused'];
        $days_remaining        = $usage_metrics['days_remaining'];
        $total_days            = $usage_metrics['total_days'];
        $total_used_days       = $usage_metrics['total_used_days'];
        $remaining_days        = $usage_metrics['remaining_days'];
        $current_date_time     = $usage_metrics['current_date_time'];
        $average_hourly_usage  = $usage_metrics['average_hourly_usage'];

        // Display metrics in HTML div with inline CSS and WordPress comments
        $metrics = '<div class="serv-details-card">';
        
        $metrics .= "<h3>Service Usage Metrics</h3>";
        $metrics .= "<p class='service-name'> $service_name - $service_id</p>";
        $metrics .= "<ul class='product-costs'>";
        foreach ( $product_costs as $product_name => $product_cost ) {
            $metrics .= '<li>Product: ' . $product_name . ': ' . wc_price( $product_cost ) . '</li>';
        }
        $metrics .= "</ul>";

        // Cost details
        $metrics .= "<h4>Cost Details:</h4>";

        $metrics .= "<p class='total-service-cost'><strong>Total Service Cost:</strong> $total_service_cost</p>";
        $metrics .= "<p class='average-daily-cost'><strong>Average Daily Cost:</strong> $average_daily_cost</p>";

        // Usage breakdown
        $metrics .= "<h4>Usage Breakdown:</h4>";
        $metrics .= "<p class='used-amount'><strong>Used Amount:</strong> $used_amount</p>";
        $metrics .= "<p class='unused-amount'><strong>Unused Amount:</strong> $unused_amount</p>";
        $metrics .= "<p class='percentage-used'><strong>Percentage Used:</strong> $percentage_used%</p>";
        $metrics .= "<p class='percentage-unused'><strong>Percentage Unused:</strong> $percentage_unused%</p>";

        // Days information
        $metrics .= "<h4>Days Information:</h4>";
        $metrics .= "<p class='total-days'><strong>Total Days:</strong> $total_days</p>";
        $metrics .= "<p class='total-used-days'><strong>Total Used Days:</strong> $total_used_days</p>";
        $metrics .= "<p class='remaining-days'><strong>Remaining Days:</strong> $remaining_days</p>";
        $metrics .= "<p class='days-remaining'><strong>Days Remaining:</strong> $days_remaining</p>";

        // Additional information
        $metrics .= "<h4>Additional Information:</h4>";
        $metrics .= "<p class='current-date-time'><strong>Current Date and Time:</strong> $current_date_time</p>";
        $metrics .= "<p class='average-hourly-usage'><strong>Average Hourly Usage:</strong> $average_hourly_usage</p>";
        $metrics .= '</div>';

    } else {
        // Handle the case where service details are not available
        $metrics .= '<div class="sw-no-service-details">';
        $metrics .= "<p class='no-service-details'><strong>Service details not available.</strong></p>";
        $metrics .= '</div>';
    }
    return $metrics;
}
