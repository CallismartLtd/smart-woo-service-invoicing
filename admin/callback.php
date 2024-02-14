<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function smart_woo_service() {
    // Check if the current user has the required capability to access this page
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get the 'action' parameter from the URL
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

    // Call the appropriate function based on the action
    switch ($action) {
        case 'process-new-service':
            sw_process_new_service_order_page();
            break;

        case 'service_details':
            // Call the function for handling service details
            sw_admin_view_service_details();
            break;

        case 'add-new-service':
            sw_handle_new_service_page();
             break;

        case 'edit-service':

            sw_handle_edit_service_page();
            break;

        default:
            // Call the default function
            sw_main_page();
            break;
    }
}



// Callback function for the "Invoices" submenu page
function sw_invoices() {
    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'dashboard';

    $tabs = array(
        '' => 'Invoices',
        'add-new-invoice' => 'Add New',

    );


      echo sw_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $action, 'action' );

    
    switch ( $action ) {
        case 'add-new-invoice':
            sw_create_new_invoice_form();
            break;

        case 'edit-invoice':
            
            sw_edit_invoice_page();
          
            break;

        case 'invoice-by-status':
            sw_handle_admin_invoice_by_status();
            
            break;

        case 'view-invoice':
            
            sw_view_invoice_page();

            break;

        default:
            sw_invoice_dash();
            break;
    }
}


/**
* Callback function for "Product" submenu page
*/
function sw_products_page() {
    // Check for URL parameters
    $action = isset( $_GET[ 'action' ] ) ? sanitize_text_field( $_GET[ 'action' ] ) : '';
    $product_id = isset($_GET[ 'product_id' ]) ? intval($_GET[ 'product_id' ]) : 0;

    $tabs = array(
        '' => 'Products',
        'add-new' => 'Add New',

    );


      echo sw_sub_menu_nav( $tabs, 'Products', 'sw-products', $action, 'action' );

    // Handle different actions
    switch ($action) {
        case 'add-new':
            sw_render_new_product_form();
            break;
        case 'edit':
            display_edit_form($product_id);
            break;
        default:
            display_product_details_table();
            break;
    }
}



// Callback function for the "Send Mail" page
function send_mail_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Define descriptions for each form field
    $field_descriptions = array(
        'sender_name' => 'Do not use names that are not associated with your business',
        'sender_email' => 'Make sure your website is able to send mails from this email',
    );

    // Define an empty message
    $message = '';

    // Handle form submission
    if (isset($_POST['send_mail']) && check_admin_referer('send-mail-form')) {
        // Collect form data and prevent automatic escaping
        $sender_name = wp_unslash($_POST['sender_name']);
        $sender_email = wp_unslash($_POST['sender_email']);
        $recipient_email = wp_unslash($_POST['recipient_email']);
        $subject = wp_unslash($_POST['subject']);
        $message = wp_unslash($_POST['message']);

        // Get the selected user's data based on the email
        $selected_user = get_user_by('email', $recipient_email);

        if ($selected_user) {
            // Get the user's ID
            $recipient_user_id = $selected_user->ID;

            // Get the first name and full name of the selected user
            $first_name = get_user_meta($recipient_user_id, 'first_name', true);
            $last_name = get_user_meta($recipient_user_id, 'last_name', true);

            // Combine the first name and last name to create the full name
            $full_name = $first_name . ' ' . $last_name;

            // Replace shortcodes with user's name
            $message = str_replace('[client_firstname]', $first_name, $message);
            $message = str_replace('[client_fullname]', $full_name, $message);

            // Send mail logic
            $headers = array(
                'From: ' . $sender_name . ' <' . $sender_email . '>',
                'Content-Type: text/html; charset=UTF-8'
            );

            // Process file uploads
            $attachments = array();
            if (!empty($_FILES['attachments']['name'])) {
                $upload_dir = wp_upload_dir();

                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    $attachment_name = sanitize_file_name($_FILES['attachments']['name'][$key]);
                    $attachment_size = $_FILES['attachments']['size'][$key];
                    $attachment_type = $_FILES['attachments']['type'][$key];

                    if ($attachment_size <= 4 * 1024 * 1024) {
                        $attachment_path = $upload_dir['path'] . '/' . $attachment_name;
                        if (move_uploaded_file($tmp_name, $attachment_path)) {
                            $attachments[] = $attachment_path;
                        }
                    } else {
                        echo '<div class="error"><p>File size exceeds the limit (4MB).</p></div>';
                    }
                }
            }

            // Send the email with attachments
            $result = wp_mail($recipient_email, $subject, $message, $headers, $attachments);

            // Delete temporary attachments
            foreach ($attachments as $attachment) {
                unlink($attachment);
            }

            if ($result) {
                echo '<div class="updated"><p>Mail sent successfully.</p></div>';
            } else {
                echo '<div class="error"><p>Mail sending failed.</p></div>';
            }
        } else {
            echo '<div class="error"><p>Selected user not found.</p></div>';
        }
    }

    // Display the Send Mail form
    ?>
    <div class="inv invmail">
        <h2 style="text-align: center; color: blue; font-weight: bold; font-size:24px;">Send Service Mails to Users</h2>
        <a href="<?php echo admin_url('admin.php?page=sw-admin'); ?>" class="sw-blue-button">Dashboard </a>
        <a href="<?php echo admin_url('admin.php?page=sw-admin&action=add-new-service'); ?>" class="sw-blue-button">Add New</a>

        <a href="<?php echo admin_url('admin.php?page=invoice-options'); ?>" class="sw-blue-button">Settings</a>
       

        <form method="post" enctype="multipart/form-data">
            <?php
            // Add a nonce field for security
            wp_nonce_field('send-mail-form');
            ?>

            <label for="sender_name" class="sender-name-label">Sender Name:</label>
            <input type="text" name="sender_name" id="sender_name" class="sender-name" required>
            <p class="description"><?php echo esc_html($field_descriptions['sender_name']); ?></p>

            <label for="sender_email" class="sender-email-label">Sender Email:</label>
            <input type="email" name="sender_email" id="sender_email" class="sender-email" required>
            <p class="description"><?php echo esc_html($field_descriptions['sender_email']); ?></p>

            <label for="recipient_email" class="recipient-email-label">Recipient Email:</label>
            <select name="recipient_email" id="recipient_email" class="recipient-email" required>
                <option value="" selected>Select User</option>
                <?php
                // Populate the dropdown with WordPress users' email addresses
                $users = get_users();
                foreach ($users as $user) {
                    echo '<option value="' . esc_attr($user->user_email) . '">' . esc_html($user->user_email) . '</option>';
                }
                ?>
            </select>

            <label for="subject" class="subject-label">Subject:</label>
            <input type="text" name="subject" id="subject" class="subject" required>

            <label for="message" class="message-label">Message:</label>
            <?php
            // Display the Classic Editor for the "Message" field
            $editor_id = 'message'; // Use the same ID as your textarea
            $settings = array(
                'textarea_name' => 'message', // Use the name of your textarea
                'textarea_rows' => 10,
            );
            wp_editor($message, $editor_id, $settings);
            ?>

            <!-- File input container -->
            <div class="file-input-container">
                <!-- File input field (visible) -->
                <input type="file" name="attachments[]" id="attachments" class="file-input-field" multiple>
                   <p class="file-size-description">Max file size: 4MB per file</p>
                </div>
            </div>

            <p class="user-info-shortcode">Use these shortcodes to insert user's info: [client_fullname] or [client_firstname] for client's full name or first name</p>

            <p><input type="submit" class="sw-blue-button send-button" name="send_mail" value="Send Mail"></p>
        </form>
    </div>
    <?php
}

//Callback for Settings Page
function sw_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
     // Check for URL parameters
     $action = isset( $_GET[ 'tab' ] ) ? sanitize_text_field( $_GET[ 'tab' ] ) : '';

     $tabs = array(
        '' => 'General',
        'business' => 'Business',
        'invoicing' => 'Invoicing',
        'emails' => 'Emails',

    );


    echo sw_sub_menu_nav( $tabs, 'Settings', 'sw-options', $action, 'tab' );
 
     // Handle different actions
     switch ($action) {
         case 'business':
             sw_render_service_options_page();
             break;
         case 'invoicing':
             sw_render_invoice_options_page();
             break;
        case 'emails':
            sw_render_email_options_page();
            break;
         default:
         sw_options_dash_page();
             break;
     }
}

/**
 * Central callback init function to load shortcodes
 */

 function sw_shortcode_init() {
    // Add the invoice page shortcode
    add_shortcode('sw_invoice_page', 'sw_invoice_shortcode');
    // Register the service page shortcode
    add_shortcode('sw_service_page', 'sw_service_shortcode');

    // Add shortcodes for displaying counts
    add_shortcode('sw_active_service_count', 'sw_active_service_count_shortcode');
    add_shortcode('unpaid_invoices_count', 'get_unpaid_invoices_count');

    
    // minicard shortcodes
    add_shortcode('sw_service_mini_card', 'sw_service_mini_card_loader');

 }
 add_action( 'init', 'sw_shortcode_init' );
