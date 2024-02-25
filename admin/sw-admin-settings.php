<?php
/**
* File name    :   sw-admin-settings.php
* @author      :   Callistus
* Description  :   settings page for admin submenu
*/

/**
* Handles email settings options when form is submitted
* called directly within the HTML page redering
*/
 function sw_handle_email_options() {
    if ( isset( $_POST['sw_save_email_options'] ) ) {

        // Update billing email
        if ( isset( $_POST['sw_billing_email'] ) ) {
            update_option( 'sw_billing_email', sanitize_email( $_POST['sw_billing_email'] ) );
        }

        // Update sender name
        if ( isset( $_POST['sw_sender_name'] ) ) {
            update_option( 'sw_sender_name', sanitize_text_field( $_POST['sw_sender_name'] ) );
        }

        // Define an array of checkbox names
        $checkboxes = array(
            'sw_cancellation_mail_to_user',
            'sw_service_opt_out_mail',
            'sw_payment_reminder_to_client',
            'sw_service_expiration_mail',
            'sw_new_invoice_mail',
            'sw_send_renewal_mail',
            'sw_reactivation_mail',
            'sw_invoice_paid_mail',
            'sw_service_cancellation_mail_to_admin',
            'sw_service_expiration_mail_to_admin',
        );

        // Update checkbox options
        foreach ( $checkboxes as $checkbox_name ) {
            if ( isset( $_POST[$checkbox_name] ) ) {
                update_option( $checkbox_name, 1 ); // Use 1 to represent checked
            } else {
                update_option($checkbox_name, 0); // Use 0 to represent unchecked
            }
        }
        echo '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>';

    }
}

/**
 * Handles the settings options when the form is submitted
 * called directly within the HTML page rendering
 */
function sw_handle_options_submission() {
    // Handle form submission for all settings
    if ( isset( $_POST['sw_save_options'] ) ) {
        // Handle form submission for existing settings
        if ( isset( $_POST['sw_invoice_page'] ) ) {
            update_option( 'sw_invoice_page', intval( $_POST['sw_invoice_page'] ) );
        }

        if ( isset( $_POST['sw_invoice_logo_url'] ) ) {
            update_option( 'sw_invoice_logo_url', sanitize_text_field( $_POST['sw_invoice_logo_url'] ) );
        }

        if ( isset( $_POST['sw_invoice_watermark_url'] ) ) {
            update_option( 'sw_invoice_watermark_url', sanitize_text_field( $_POST['sw_invoice_watermark_url'] ) );
        }

        if ( isset( $_POST['sw_business_name'] ) ) {
            $business_name = sanitize_text_field( $_POST['sw_business_name']) ? sanitize_text_field( $_POST['sw_business_name'] ) : get_bloginfo( 'name' );
            update_option('sw_business_name', $business_name );
        }

        if ( isset( $_POST['sw_admin_phone_numbers'] ) ) {
            update_option( 'sw_admin_phone_numbers', sanitize_text_field( $_POST['sw_admin_phone_numbers'] ) );
        }


        if ( isset( $_POST['sw_service_page'] ) ) {
            update_option( 'sw_service_page', intval( $_POST['sw_service_page'] ) );
        }


        if ( isset( $_POST['sw_prorate'] ) ) {
            $sw_prorate_value = sanitize_text_field( $_POST['sw_prorate'] );
            update_option( 'sw_prorate', $sw_prorate_value );
        }

        if ( isset( $_POST['sw_invoice_id_prefix'] ) ) {
            $invoice_number_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['sw_invoice_id_prefix'] );
            update_option( 'sw_invoice_id_prefix', $invoice_number_prefix );
        }

        if ( isset( $_POST['sw_service_id_prefix'] ) ) {
            $service_id_prefix = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['sw_service_id_prefix'] );
            update_option( 'sw_service_id_prefix', $service_id_prefix );
        }
        

        // Handle form submission for existing settings
        if ( isset( $_POST['sw_allow_migration'] ) ) {
            $sw_allow_migration = sanitize_text_field( $_POST['sw_allow_migration'] );
            update_option( 'sw_allow_migration', $sw_allow_migration );
        }
       // Handle form submission for existing settings
        if ( isset( $_POST['sw_upgrade_product_cat'] ) ) {
            $selected_upgrade_category = sanitize_text_field( $_POST['sw_upgrade_product_cat'] );
            update_option( 'sw_upgrade_product_cat', $selected_upgrade_category );
        }

        if ( isset( $_POST['sw_downgrade_product_cat'] ) ) {
            $selected_downgrade_category = sanitize_text_field( $_POST['sw_downgrade_product_cat'] );
            update_option( 'sw_downgrade_product_cat', $selected_downgrade_category );
        }

        echo '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>';
    }
}


 function sw_options_dash_page() {
    echo '<div class="wrap">';
    
    echo '<h2>Smart Woo Settings and Knowledgebase</h2>';

    echo '<div class="sw-container">';

    // Left column (Topics)
    echo '<div class="sw-left-column">';
    echo '<h3>Quick Set-up Guides</h3>';
    echo '<ul>';
    // Heading for general concept
    echo '<h4><a href="#general-concept">General Concept</a></h4>';
    // Step one
    echo '<h4><a href="#step1">Step 1</a></h4>';
    echo '<h4><a href="#step2">Step 2</a></h4>';
    echo '<h4><a href="#step3">Step 3</a></h4>';
    echo '</ul>';
    echo '</div>';

    // Right column (Instructions)
    echo '<div class="sw-right-column">';
    // Instruction for general concept
    echo '<div id="general-concept" class="instruction">';
    echo '<h3>Introduction</h3>';
    echo '<p><strong>The concept behind this plugin is to allow admins(store owners) to issue service-subscription based invoices and accept
    payments through them. For you to achieve this you need to carefully folow the outlined steps in this quick guide.</strong></p>';
    echo '</div>';

    // Instruction for step one
    echo '<div id="step1" class="instruction">';
    echo '<h3>Basic Set-up</h3>';
    echo '<p><strong>Set up your business details in the <a href="' . esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ) . '" target="_blank">business settings page</a>,';
    echo 'and Invoicing preferences in the <a href="' . esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ) .'" target="_blank">invoicing settings page</a></strong></p>';
    echo '</div>';


    // Instruction for step two
    echo '<div id="step2" class="instruction">';
    echo '<h3>Create Product</h3>';
    echo '<p><strong>Create a <a href="' . admin_url( 'admin.php?page=sw-products&action=add-new' ).'" target="_blank">Service Product</a> specially dedicated to service subscription, set up the necessary fields.</strong></p>';
    echo '</div>';

    // Instruction for step three
    echo '<div id="step3" class="instruction">';
    echo '<h3>All Done 🎉🎉</h3>';
    echo '<p><strong>Your product is now listed in the WooCommerce product page, you can sale your service subscription from there or via a custom made tables.<br>
    When a user configures the product to their choice, they can add it to cart and checkout. All orders are in the <a href="' . admin_url( 'admin.php?page=sw-service-orders' ) .'">Service Orders</a> page, from there you can process them.</p>';
    echo '<p>For help, Support or Bug report please visit our dedicated <a href="https://callismart.com.ng/smart-woo">Smart Woo</a> page</strong></p>';
    echo '</div>';
    echo '</div>';

    echo '</div>';

    echo '</div>'; 

}



function sw_render_service_options_page() {
    sw_handle_options_submission();
    $site_name              = get_bloginfo( 'name' );
    $business_name          = get_option( 'sw_business_name', $site_name );
    $admin_phone_numbers    = get_option( 'sw_admin_phone_numbers', '' );
    $service_page           = get_option( 'sw_service_page', 0 );
    $upgrade_product_cat    = get_option( 'sw_upgrade_product_cat', '0' );
    $downgrade_product_cat  = get_option( 'sw_downgrade_product_cat', '0' );
    $product_categories     = get_terms( 'product_cat', array( 'hide_empty' => false ) ); // Get all product categories
    $pages                  = get_pages();
    $sw_prorate             = get_option( 'sw_prorate', 'Select option' );
    $migration_option       = get_option( 'sw_allow_migration', 'Disable' );
    $service_id_prefix      = get_option( 'sw_service_id_prefix', 'SID' );
    echo '<h1>Business Info 🧊</h1>';

    
    ?>
        <div class="wrap">
        <form method="post" class="inv-settings-form">

        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">
        
        <!-- Business Name -->
        <div class="sw-form-row">
        <label for="sw_business_name" class="sw-form-label">Business Name</label>
        <span class="sw-field-description" title="Enter your business name">?</span>
        <input type="text" name="sw_business_name" id="sw_business_name" value="<?php echo esc_attr( $business_name ); ?>" placeholder="Enter business name" class="sw-form-input">
        </div>

        <!--Business Phone -->
        <div class="sw-form-row">
        <label for="sw_admin_phone_numbers" class="sw-form-label">Phone Numbers</label>
        <span class="sw-field-description" title="Enter admin phone numbers separated by commas (e.g., +123456789, +987654321).">?</span>
        <input type="text" name="sw_admin_phone_numbers" id="sw_admin_phone_numbers" value="<?php echo esc_attr( $admin_phone_numbers ); ?>" placeholder="Enter business phone numbers" class="sw-form-input">
        </div>

        <!--Service Page -->
        <div class="sw-form-row">
        <label for="sw_service_page" class="sw-form-label">Service Page</label>
        <span class="sw-field-description" title="This page should have this shortcode [sw_service_page] ">?</span>
        <select name="sw_service_page" id="sw_service_page" class="sw-form-input">
        <option value="0">Select a service page</option>
        <?php
        foreach ($pages as $page) {
            $selected = ($service_page == $page->ID) ? 'selected' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        ?>
        </select>
        </div>

           <!-- Form field for service_id_prefix -->
        <div class="sw-form-row">
        <label for="sw_service_id_prefix" class="sw-form-label">Service ID Prefix</label>
        <span class="sw-field-description" title="Enter a text to prifix your service IDs">?</span>
        <input class="sw-form-input" type="text" name="sw_service_id_prefix" id="sw_service_id_prefix" value="<?php echo esc_attr( $service_id_prefix ); ?>" placeholder="eg, SMWSI">
        </div>
 
        <!-- Form field for Proration -->
        <div class="sw-form-row">
        <label for="sw_prorate" class="sw-form-label">Allow Proration</label>
        <span class="sw-field-description" title="Choose to allow users switch from their current service to another">?</span>
        <select name="sw_prorate" id="sw_prorate" class="sw-form-input">
        <option value="Enable" <?php  selected( 'Enable', $sw_prorate ); ?>>Yes</option>
        <option value="Disable" <?php  selected( 'Disable', $sw_prorate ); ?>>No</option>
        </select>
        </div>

        <!-- Form field for service migration -->
        <div class="sw-form-row">
        <label for="sw_allow_migration" class="sw-form-label">Allow Service Migration</label>
        <span class="sw-field-description" title="Choose to allow users switch from their current service to another">?</span>
        <select name="sw_allow_migration" id="sw_allow_migration" class="sw-form-input">
        <option value="Enable" <?php  selected( 'Enable', $migration_option ); ?>>Yes</option>
        <option value="Disable" <?php  selected( 'Disable', $migration_option ); ?>>No</option>
        </select>
        </div>

        <!-- Service Upgrade Categories -->
        <div class="sw-form-row">
        <label for="sw_upgrade_product_cat" class="sw-form-label">Product Category for Upgrade</label>
        <span class="sw-field-description" title="Select the category of products to mark as products for service upgrades.">?</span>
        <select name="sw_upgrade_product_cat" class="sw-form-input" id="sw_upgrade_product_cat">
        <option value="0" <?php selected( '0', $upgrade_product_cat ); ?>>None</option>
        <?php
        foreach ($product_categories as $category) {
            $selected = ( $category->term_id == $upgrade_product_cat ) ? 'selected' : '';
            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
        }
        ?>
        </select>
        </div>

        <!-- Service Downdgrade Categories -->
        <div class="sw-form-row">
        <label for="sw_downgrade_product_cat" class="sw-form-label">Product Category for Downgrade</label>
        <span class="sw-field-description" title="Select the category of products to mark as products for service downgrades.">?</span>
        <select name="sw_downgrade_product_cat" class="sw-form-input" id="sw_downgrade_product_cat">
        <option value="0" <?php selected('0', $downgrade_product_cat); ?>>None</option>
        <?php
        foreach ( $product_categories as $category ) {
            $selected = ( $category->term_id == $downgrade_product_cat ) ? 'selected' : '';
            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
        }
        ?>
        </select>
        </div>

        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">

        </form>
        </div>

    <?php
}

function sw_render_invoice_options_page() {

    sw_handle_options_submission();
    $invoice_prefix         = get_option( 'sw_invoice_id_prefix', 'CINV' );
    $invoice_page           = get_option( 'sw_invoice_page', 0 );
    $pages                  = get_pages();
    $invoice_logo_url       = get_option( 'sw_invoice_logo_url' );
    $invoice_watermark_url  = get_option( 'sw_invoice_watermark_url' );
    echo '<h1> Invoice 🧾</h1>';
    ?>
        <div class="wrap">
        <form method="post" class="inv-settings-form">

        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">

        <!--Service Page -->
        <div class="sw-form-row">
        <label for="sw_invoice_page" class="sw-form-label">Invoice Page</label>
        <span class="sw-field-description" title="This page should have this shortcode [sw_service_page]">?</span>
        <select name="sw_invoice_page" id="sw_invoice_page" class="sw-form-input">
        <option value="0">Select an invoice page</option>
        <?php
        foreach ( $pages as $page ) {
            $selected = ( $invoice_page == $page->ID ) ? 'selected' : '';
            echo '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
        }
        ?>
        </select>
        </div>

        <!-- Invoice ID Prefix -->
        <div class="sw-form-row">
        <label for="sw_invoice_id_prefix" class="sw-form-label">Invoice ID Prefix</label>
        <span class="sw-field-description" title="Enter a text to prifix your invoice IDs">?</span>
        <input class="sw-form-input" type="text" name="sw_invoice_id_prefix" id="sw_invoice_id_prefix" value="<?php echo esc_attr( $invoice_prefix ); ?>" placeholder="eg, INV">
        </div>

        <!-- Invoice Logo URL -->
        <div class="sw-form-row">
        <label for="sw_invoice_logo_url" class="sw-form-label">Logo URL</label>
        <span class="sw-field-description" title="Paste the link to your logo url">?</span>
        <input type="text" name="sw_invoice_logo_url" id="sw_invoice_logo_url" value="<?php echo esc_attr( $invoice_logo_url ); ?>" placeholder="eg https://pic.com/logo.png" class="sw-form-input">
        </div>        
        
        
        <!-- Invoice Watermark URL -->
        <div class="sw-form-row">
        <label for="sw_invoice_watermark_url" class="sw-form-label">Watermark URL </label>
        <span class="sw-field-description" title="Enter your business name">?</span>
        <input type="text" name="sw_invoice_watermark_url" id="sw_invoice_watermark_url" value="<?php echo esc_attr( $invoice_watermark_url ); ?>" placeholder="eg https://pic.com/img.png" class="sw-form-input">
        </div>

        </form>
        </div>
    <?php
}

function sw_render_email_options_page() {
    sw_handle_email_options();
    $billing_email = get_option('sw_billing_email', '');
    $sender_name = get_option('sw_sender_name', '');

    // Define an array of checkbox names
    $checkboxes = array(
        'sw_cancellation_mail_to_user',
        'sw_service_opt_out_mail',
        'sw_payment_reminder_to_client',
        'sw_service_expiration_mail',
        'sw_new_invoice_mail',
        'sw_send_renewal_mail',
        'sw_reactivation_mail',
        'sw_invoice_paid_mail',
        'sw_service_cancellation_mail_to_admin',
        'sw_service_expiration_mail_to_admin',
    );

    ?>
    <div class="wrap">
        <h1>Emails</h1>
        <p>If you notice emails are not being sent, consider setting up SMTP for your site.</p>
        <form method="post" class="inv-settings-form">

            <!-- Sender Name -->
            <div class="sw-form-row">
                <label for="sw_sender_name" class="sw-form-label">Sender Name</label>
                <span class="sw-field-description" title="This will be the sender name in the email">?</span>
                <input type="text" name="sw_sender_name" id="sw_sender_name" value="<?php echo esc_attr( $sender_name ); ?>" placeholder="eg, Billing Team" class="sw-form-input">
            </div>

            <!-- Billing Email -->
            <div class="sw-form-row">
                <label for="sw_billing_email" class="sw-form-label">Billing Email</label>
                <span class="sw-field-description" title="This email will be used to send emails to the client">?</span>
                <input type="email" name="sw_billing_email" id="sw_billing_email" value="<?php echo esc_attr( $billing_email ); ?>" placeholder="eg, billing@domain.com" class="sw-form-input">
            </div>

            <h3 style="text-align: center;"> Choose which mail is sent by checking the boxes</h3>
            <!-- Checkboxes -->
            <?php foreach ( $checkboxes as $checkbox_name ) : ?>
                <div class="sw-form-row">
                    <label for="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-checkbox">
                        <?php echo esc_html( ucwords( str_replace ( array( '_', 'sw'), ' ', $checkbox_name ) ) ); ?>
                    </label>
                    <input type="checkbox" id="<?php echo esc_attr( $checkbox_name ); ?>" name="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-input" <?php checked( get_option( $checkbox_name, 0 ), 1 ); ?>>
                </div>
                <hr>
            <?php endforeach; ?>

            <input type="submit" class="sw-blue-button" name="sw_save_email_options" value="Save Changes">
        </form>
    </div>
    <?php
}


