<?php
/**
 * File name    :   sw-admin-settings.php
 *
 * @author      :   Callistus
 * Description  :   settings page for admin submenu
 */

/**
 * Handles email settings options when form is submitted
 * called directly within the HTML page redering
 */
function smartwoo_save_email_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['sw_save_email_options'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['sw_email_option_nonce'] ) ), 'sw_email_option_nonce') ) {

		// Update billing email.
		if ( isset( $_POST['smartwoo_billing_email'] ) ) {
			update_option( 'smartwoo_billing_email', sanitize_email( $_POST['smartwoo_billing_email'] ) );
		}

		if ( isset( $_POST['smartwoo_email_image_header'] ) ) {
			update_option( 'smartwoo_email_image_header', esc_url_raw( $_POST['smartwoo_email_image_header'] ) );
		}

		// Update sender name.
		if ( isset( $_POST['smartwoo_email_sender_name'] ) ) {
			update_option( 'smartwoo_email_sender_name', sanitize_text_field( wp_unslash( $_POST['smartwoo_email_sender_name'] ) ) );
		}

		// Define an array of checkbox names.
		$checkboxes = array(
			'smartwoo_cancellation_mail_to_user',
			'smartwoo_service_opt_out_mail',
			'smartwoo_payment_reminder_to_client',
			'smartwoo_service_expiration_mail',
			'smartwoo_new_invoice_mail',
			'smartwoo_renewal_mail',
			'smartwoo_reactivation_mail',
			'smartwoo_invoice_paid_mail',
			'smartwoo_service_cancellation_mail_to_admin',
			'smartwoo_service_expiration_mail_to_admin',
		);

		// Update checkbox options
		foreach ( $checkboxes as $checkbox_name ) {
			if ( isset( $_POST[ $checkbox_name ] ) ) {
				update_option( $checkbox_name, 1  ); 
			} else {
				update_option( $checkbox_name, 0 ); 
			}
		}
		echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>' . __( 'Settings saved!','smart-woo-service-invoicing' ) . '</p></div>' );

	}
}

/**
 * Handle advance option submission
 */
function smartwoo_save_advanced_options(){

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['sw_save_options'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {
		
		if ( isset( $_POST['smartwoo_pay_pending_invoice_with_wallet'] ) ) {
			update_option( 'smartwoo_pay_pending_invoice_with_wallet', 1 );
		} elseif ( ! isset( $_POST['smartwoo_pay_pending_invoice_with_wallet'] ) && function_exists( 'woo_wallet' ) ) {
			update_option( 'smartwoo_pay_pending_invoice_with_wallet', 0 );
		}

		if ( isset( $_POST['smartwoo_refund_with_wallet'] ) ) {
			update_option( 'smartwoo_refund_with_wallet', 1 );
		} elseif ( ! isset( $_POST['smartwoo_refund_with_wallet'] ) && function_exists( 'woo_wallet' ) ) {
			update_option( 'smartwoo_refund_with_wallet', 0 );
		}

		if ( isset( $_POST['smartwoo_product_text_on_shop'] ) ) {
			$value =  ! empty( $_POST['smartwoo_product_text_on_shop'] ) ?  sanitize_text_field( wp_unslash( $_POST['smartwoo_product_text_on_shop'] ) ) : 'View Product';
			update_option( 'smartwoo_product_text_on_shop', $value );
		}

		$checkboxes = array(
			'smartwoo_enable_api_feature',
			'smartwoo_allow_guest_invoicing',
			'smartwoo_remove_plugin_data_during_uninstall'	
		);

		// Update checkbox options.
		foreach ( $checkboxes as $checkbox_name ) {
			if ( isset( $_POST[ $checkbox_name ] ) ) {
				update_option( $checkbox_name, 1  ); 
			} else {
				update_option( $checkbox_name, 0 ); 
			}
		}
		echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>' );

	}
}

/**
 * Handles the settings options when the form is submitted
 * called directly within the HTML page rendering
 */
function smartwoo_save_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['sw_save_options'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {

		if ( isset( $_POST['smartwoo_invoice_page_id'] ) ) {
			update_option( 'smartwoo_invoice_page_id', absint( $_POST['smartwoo_invoice_page_id'] ) );
		}

		if ( isset( $_POST['smartwoo_invoice_logo_url'] ) ) {
			update_option( 'smartwoo_invoice_logo_url', esc_url_raw( $_POST['smartwoo_invoice_logo_url'] ) );
		}

		if ( isset( $_POST['smartwoo_invoice_watermark_url'] ) ) {
			update_option( 'smartwoo_invoice_watermark_url', esc_url_raw( $_POST['smartwoo_invoice_watermark_url'] ) );
		}

		if ( isset( $_POST['smartwoo_business_name'] ) ) {
			$business_name = isset( $_POST['smartwoo_business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smartwoo_business_name'] ) ) : get_bloginfo( 'name' );
			update_option( 'smartwoo_business_name', sanitize_text_field( $business_name ) );
		}

		if ( isset( $_POST['smartwoo_admin_phone_numbers'] ) ) {
			// Remove any characters except numbers and commas.
			$phone_numbers       = preg_replace( '/[^0-9+,]/', '', $_POST['smartwoo_admin_phone_numbers'] );
			$phone_numbers_array = explode( ',', $phone_numbers );
			$phone_numbers_array = array_filter( $phone_numbers_array );
			
			// Rearrange the phone numbers into a valid format.
			$formatted_phone_numbers = implode( ', ', $phone_numbers_array );
			
			// Update the option with the rearranged phone numbers.
			update_option( 'smartwoo_admin_phone_numbers', sanitize_text_field( $formatted_phone_numbers ) );
		}
		

		if ( isset( $_POST['smartwoo_service_page_id'] ) ) {
			update_option( 'smartwoo_service_page_id', absint( $_POST['smartwoo_service_page_id'] ) );
		}

		if ( isset( $_POST['smartwoo_prorate'] ) ) {
			$smartwoo_prorate_value = ( 'Enable' === $_POST['smartwoo_prorate'] ) ? 'Enable': 'Disable';
			update_option( 'smartwoo_prorate', sanitize_text_field( $smartwoo_prorate_value ) );
		}

		if ( isset( $_POST['smartwoo_invoice_id_prefix'] ) ) {
			$invoice_number_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['smartwoo_invoice_id_prefix'] );
			update_option( 'smartwoo_invoice_id_prefix', sanitize_text_field( $invoice_number_prefix ) );
		}

		if ( isset( $_POST['smartwoo_service_id_prefix'] ) ) {
			$service_id_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', $_POST['smartwoo_service_id_prefix'] );
			update_option( 'smartwoo_service_id_prefix', sanitize_text_field( $service_id_prefix ) );
		}

		if ( isset( $_POST['smartwoo_allow_migration'] ) ) {
			$smartwoo_allow_migration = ( 'Enable' === $_POST['smartwoo_allow_migration'] ) ? 'Enable' : 'Disable';
			update_option( 'smartwoo_allow_migration', sanitize_text_field( $smartwoo_allow_migration ) );
		}

		if ( isset( $_POST['smartwoo_upgrade_product_cat'] ) ) {
			$category_id = absint( $_POST['smartwoo_upgrade_product_cat'] );
			update_option( 'smartwoo_upgrade_product_cat', absint( $category_id ) );
		}

		if ( isset( $_POST['smartwoo_downgrade_product_cat'] ) ) {
			$category_id = absint( $_POST['smartwoo_downgrade_product_cat'] );
			update_option( 'smartwoo_downgrade_product_cat', absint( $category_id ) );
		}

		echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>' . __( 'Settings saved!', 'smart-woo-service-invoicing' ) . '</p></div>' );
	}
}

/**
 * Admin Settings Main page
 */
function smartwoo_options_main_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<h2>Smart Woo Settings and Knowledgebase</h2>

		<div class="sw-container">
			<div class="sw-left-column">
				<h3>Quick Set-up Guides</h3>
				<ul>
					<li><a class="sw-red-button" href="#general-concept">General</a></li>
					<li><a class="sw-red-button" href="#step1">Step 1</a></li>
					<li><a class="sw-red-button" href="#step2">Step 2</a></li>
					<li><a class="sw-red-button" href="#step3">Step 3</a></li>
				</ul>
			</div>

			<div class="sw-right-column">
				<div id="first-display" class="image-section">
					<h3> Smart Woo Service Invoicing</h3>
					<p><img src="<?php echo esc_url( plugins_url( 'assets/image/smart-woo-img.png', dirname(__FILE__) ) ); ?>" alt="plugin screenshot" style="width: 50%;"></p>
					<p>Here you will find useful information to get you started</p>
			</div>
				<div id="general-concept" class="instruction">
					<h3>Introduction</h3>
					<p><strong>Smart Woo Service invoicing integrates powerful service subscription on your website, this includes automatic invoice creation for services that are due.<br>
					.</strong></p>
				</div>

				<div id="step1" class="instruction">
					<h3>Basic Set-up</h3>
					<p><strong>Set up your business details in the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ); ?>" target="_blank">business settings page</a>, and Invoicing preferences in the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ); ?>" target="_blank">invoicing settings page</a>.</strong></p>
				</div>

				<div id="step2" class="instruction">
					<h3>Create Product</h3>
					<p><strong>Create a <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ); ?>" target="_blank">Service Product</a> specially dedicated to service subscription, set up the necessary fields.</strong></p>
				</div>

				<div id="step3" class="instruction">
					<h3>All Done 🎉🎉</h3>
					<p><strong>Your product is now listed in the WooCommerce product page. You can sell your service subscription from there or via custom-made tables.<br>When a user configures the product to their choice, they can add it to cart and checkout. All orders are in the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ); ?>">Service Orders</a> page, from there you can process them.</strong></p>
					<p>For help, support, or bug reports, please visit our dedicated <a href="<?php echo esc_url( 'https://callismart.com.ng/smart-woo' ); ?>">Smart Woo</a> page</p>
				</div>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Admin Service Settings Page
 */
function smartwoo_service_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();

	smartwoo_save_options();
	$site_name             = get_bloginfo( 'name' );
	$business_name         = get_option( 'smartwoo_business_name', $site_name );
	$admin_phone_numbers   = get_option( 'smartwoo_admin_phone_numbers', '' );
	$service_page          = get_option( 'smartwoo_service_page_id', 0 );
	$upgrade_product_cat   = get_option( 'smartwoo_upgrade_product_cat', '0' );
	$downgrade_product_cat = get_option( 'smartwoo_downgrade_product_cat', '0' );
	$product_categories    = get_terms( 'product_cat' );
	$pages                 = get_pages();
	$smartwoo_prorate      = get_option( 'smartwoo_prorate', 'Disable' );
	$migration_option      = get_option( 'smartwoo_allow_migration', 'Disable' );
	$service_id_prefix     = get_option( 'smartwoo_service_id_prefix', 'SID' );
	echo '<h1>' . __( 'Business Info 🧊', 'smart-woo-service-invoicing' ) . '</h1>';

	?>
		<div class="wrap">
		<form method="post" class="inv-settings-form">
		
		<?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
		
		<!-- Business Name -->
		<div class="sw-form-row">
		<label for="smartwoo_business_name" class="sw-form-label"><?php echo __( 'Business Name', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter your business name">?</span>
		<input type="text" name="smartwoo_business_name" id="smartwoo_business_name" value="<?php echo esc_attr( $business_name ); ?>" placeholder="Enter business name" class="sw-form-input">
		</div>

		<!--Business Phone -->
		<div class="sw-form-row">
		<label for="smartwoo_admin_phone_numbers" class="sw-form-label"><?php echo __( 'Phone Numbers', 'smart-woo-service-invoicing');?></label>
		<span class="sw-field-description" title="Enter admin phone numbers separated by commas (e.g., +123456789, +987654321).">?</span>
		<input type="text" name="smartwoo_admin_phone_numbers" id="smartwoo_admin_phone_numbers" value="<?php echo esc_attr( $admin_phone_numbers ); ?>" placeholder="Enter business phone numbers" class="sw-form-input">
		</div>

		<!--Service Page -->
		<div class="sw-form-row">
		<label for="smartwoo_service_page_id" class="sw-form-label"><?php echo __( 'Service Page', 'smart-woo-service-invoicing' );?></label>
		<span class="sw-field-description" title="This page should have this shortcode [smartwoo_service_page] ">?</span>
		<select name="smartwoo_service_page_id" id="smartwoo_service_page_id" class="sw-form-input">
		<option value="0"><?php echo __( 'Select a Service page', 'smart-woo-service-invoicing' ); ?></option>
		<?php
		foreach ( $pages as $page ) {
			$selected = ( $service_page == $page->ID ) ? 'selected' : '';
			echo '<option value="' . esc_attr( $page->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_attr( $page->post_title ) . '</option>';
		}
		?>
		</select>
		</div>

			<!-- Form field for service_id_prefix -->
		<div class="sw-form-row">
		<label for="smartwoo_service_id_prefix" class="sw-form-label"><?php echo __( 'Service ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter a text to prifix your service IDs">?</span>
		<input class="sw-form-input" type="text" name="smartwoo_service_id_prefix" id="smartwoo_service_id_prefix" value="<?php echo esc_attr( $service_id_prefix ); ?>" placeholder="eg, SMWSI">
		</div>
 
		<!-- Form field for Proration -->
		<div class="sw-form-row">
		<label for="smartwoo_prorate" class="sw-form-label"><?php echo __( 'Allow Proration', 'smart-woo-service-invoicing' );?></label>
		<span class="sw-field-description" title="Choose to allow users switch from their current service to another">?</span>
		<select name="smartwoo_prorate" id="smartwoo_prorate" class="sw-form-input">
		<option value="Enable" <?php selected( 'Enable', esc_attr( $smartwoo_prorate ) ); ?>>Yes</option>
		<option value="Disable" <?php selected( 'Disable', esc_attr( $smartwoo_prorate ) ); ?>>No</option>
		</select>
		</div>

		<!-- Form field for service migration -->
		<div class="sw-form-row">
		<label for="smartwoo_allow_migration" class="sw-form-label"><?php echo __( 'Allow Service Migration', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Choose to allow users switch from their current service to another">?</span>
		<select name="smartwoo_allow_migration" id="smartwoo_allow_migration" class="sw-form-input">
		<option value="Enable" <?php selected( 'Enable', esc_attr( $migration_option ) ); ?>>Yes</option>
		<option value="Disable" <?php selected( 'Disable', esc_attr( $migration_option ) ); ?>>No</option>
		</select>
		</div>

		<!-- Service Upgrade Categories -->
		<div class="sw-form-row">
		<label for="smartwoo_upgrade_product_cat" class="sw-form-label"><?php echo __( 'Product Category for Upgrade', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Select the product category to mark as products for service upgrades.">?</span>
		<select name="smartwoo_upgrade_product_cat" class="sw-form-input" id="smartwoo_upgrade_product_cat">
		<option value="0" <?php selected( '0', esc_attr( $upgrade_product_cat ) ); ?>>None</option>
		<?php
		foreach ( $product_categories as $category ) {
			$selected = ( $category->term_id == $upgrade_product_cat ) ? 'selected' : '';
			echo '<option value="' . esc_attr( $category->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category->name ) . '</option>';
		}
		?>
		</select>
		</div>

		<!-- Service Downdgrade Categories -->
		<div class="sw-form-row">
		<label for="smartwoo_downgrade_product_cat" class="sw-form-label">Product Category for Downgrade</label>
		<span class="sw-field-description" title="Select the category of products to mark as products for service downgrades.">?</span>
		<select name="smartwoo_downgrade_product_cat" class="sw-form-input" id="smartwoo_downgrade_product_cat">
		<option value="0" <?php selected( '0', $downgrade_product_cat ); ?>>None</option>
		<?php
		foreach ( $product_categories as $category ) {
			$selected = ( $category->term_id == $downgrade_product_cat ) ? 'selected' : '';
			echo '<option value="' . $category->term_id . '" ' . esc_attr( $selected ) . '>' . esc_attr( $category->name ) . '</option>';
		}
		?>
		</select>
		</div>

		<input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">

		</form>
		</div>

	<?php
	
	return ob_get_clean();
}

/**
 * Admin Invoice Settings page.
 */
function smartwoo_invoice_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();

	smartwoo_save_options();
	$invoice_prefix        = get_option( 'smartwoo_invoice_id_prefix', 'CINV' );
	$invoice_page          = get_option( 'smartwoo_invoice_page_id', 0 );
	$pages                 = get_pages();
	$invoice_logo_url      = get_option( 'smartwoo_invoice_logo_url' );
	$invoice_watermark_url = get_option( 'smartwoo_invoice_watermark_url' );
	echo '<h1>'. __( 'Invoice 🧾', 'smart-woo-service-invoicing' ) . '</h1>';
	?>
		<div class="wrap">
		<form method="post" class="inv-settings-form">

		<?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>

		<!--Service Page -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_page_id" class="sw-form-label"><?php echo __( 'Invoice Page', 'smart-woo-service-invoicing' ) ?></label>
		<span class="sw-field-description" title="This page should have this shortcode [smartwoo_invoice_page]">?</span>
		<select name="smartwoo_invoice_page_id" id="smartwoo_invoice_page_id" class="sw-form-input">
		<option value="0"><?php echo __( 'Select an invoice page', 'smart-woo-service-invoicing' ); ?></option>
		<?php
		foreach ( $pages as $page ) {
			$selected = ( $invoice_page == $page->ID ) ? 'selected' : '';
			echo '<option value="' . esc_attr( $page->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $page->post_title ) . '</option>';
		}
		?>
		</select>
		</div>

		<!-- Invoice ID Prefix -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_id_prefix" class="sw-form-label"><?php echo __( 'Invoice ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter a text to prifix your invoice IDs">?</span>
		<input class="sw-form-input" type="text" name="smartwoo_invoice_id_prefix" id="smartwoo_invoice_id_prefix" value="<?php echo esc_attr( $invoice_prefix ); ?>" placeholder="eg, INV">
		</div>

		<!-- Invoice Logo URL -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_logo_url" class="sw-form-label">Logo URL</label>
		<span class="sw-field-description" title="Paste the link to your logo url">?</span>
		<input type="text" name="smartwoo_invoice_logo_url" id="smartwoo_invoice_logo_url" value="<?php echo esc_attr( $invoice_logo_url ); ?>" placeholder=" eg. www.example/image.png" class="sw-form-input">
		</div>        
		
		
		<!-- Invoice Watermark URL -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_watermark_url" class="sw-form-label"><?php echo __( 'Watermark URL', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter your business name">?</span>
		<input type="text" name="smartwoo_invoice_watermark_url" id="smartwoo_invoice_watermark_url" value="<?php echo esc_attr( $invoice_watermark_url ); ?>" placeholder="eg www.example/image.png" class="sw-form-input">
		</div>
		<input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">
		</form>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Admin Email Settings page
 */
function smartwoo_email_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();
	smartwoo_save_email_options();
	$billing_email = get_option( 'smartwoo_billing_email' );
	$sender_name   = get_option( 'smartwoo_email_sender_name' );
	$email_image   = get_option( 'smartwoo_email_image_header' );

	// Define an array of checkbox names
	$checkboxes = array(
		'smartwoo_cancellation_mail_to_user',
		'smartwoo_service_opt_out_mail',
		'smartwoo_payment_reminder_to_client',
		'smartwoo_service_expiration_mail',
		'smartwoo_new_invoice_mail',
		'smartwoo_renewal_mail',
		'smartwoo_reactivation_mail',
		'smartwoo_invoice_paid_mail',
		'smartwoo_service_cancellation_mail_to_admin',
		'smartwoo_service_expiration_mail_to_admin',
	);

	?>
	<div class="wrap">
		<h1>Emails 📧</h1>
		<p><?php echo __( 'If you notice emails are not being sent, consider setting up SMTP for your site.', 'smart-woo-service-invoicing' );?></p>
		<form method="post" class="inv-settings-form">

		<?php wp_nonce_field( 'sw_email_option_nonce', 'sw_email_option_nonce' ); ?>

			<!-- Sender Name -->
			<div class="sw-form-row">
				<label for="smartwoo_email_sender_name" class="sw-form-label"><?php echo __( 'Sender Name','smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="This will be the sender name on the mail header">?</span>
				<input type="text" name="smartwoo_email_sender_name" id="smartwoo_email_sender_name" value="<?php echo esc_attr( $sender_name ); ?>" placeholder="eg, Billing Team" class="sw-form-input">
			</div>

			<!-- Email Image header -->
			<div class="sw-form-row">
				<label for="smartwoo_email_image_header" class="sw-form-label"><?php echo __( 'Email Header Image','smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="Paste the URL of the image you want to show in the email header">?</span>
				<input type="url" name="smartwoo_email_image_header" id="smartwoo_email_image_header" value="<?php echo esc_attr( $email_image ); ?>" placeholder="eg example.com/image" class="sw-form-input">
			</div>

			<!-- Billing Email -->
			<div class="sw-form-row">
				<label for="smartwoo_billing_email" class="sw-form-label"><?php echo __( 'Billing Email', 'smart-woo-service-invoicing' ) ?></label>
				<span class="sw-field-description" title="This email will be used to send emails to the clients">?</span>
				<input type="email" name="smartwoo_billing_email" id="smartwoo_billing_email" value="<?php echo esc_attr( $billing_email ); ?>" placeholder="eg, billing@domain.com" class="sw-form-input">
			</div>

			<h3 style="text-align: center;"><?php echo __( 'Configure which Email is sent', 'smart-woo-service-invoicing' )?></h3>
			<!-- Checkboxes -->
			<?php foreach ( $checkboxes as $checkbox_name ) : ?>
				<div class="sw-form-row">
					<label for="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-checkbox">
						<?php echo esc_html( ucwords( str_replace( array( '_', 'smartwoo' ), ' ', $checkbox_name ) ) ); ?>
					</label>
					<input type="checkbox" id="<?php echo esc_attr( $checkbox_name ); ?>" name="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-input" <?php checked( get_option( $checkbox_name, 0 ), 1 ); ?>>
				</div>
				<hr>
			<?php endforeach; ?>

			<input type="submit" class="sw-blue-button" name="sw_save_email_options" value="Save Changes">
		</form>
	</div>
	<?php
	return ob_get_clean();
}

function smartwoo_advanced_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();
	smartwoo_save_advanced_options();
	$product_text = get_option( 'smartwoo_product_text_on_shop', 'View Product' );
    $checkboxes = array(
        'smartwoo_enable_api_feature',
        'smartwoo_allow_guest_invoicing',
        'smartwoo_remove_plugin_data_during_uninstall'
    );
	
    ?>
    <div class="wrap">
		<h1><?php echo __( 'Advanced Settings ⚙', 'smart-woo-service-invoicing' ); ?></h1>

        <form method="post" class="inv-settings-form">
            <?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
			<div class="sw-form-row">
				<label for="smartwoo_product_text_on_shop" class="sw-form-label"><?php echo __( 'Product Button Text', 'smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="Set the text that will be shown on each Smart Woo Product on shop page">?</span>
				<input type="type" name="smartwoo_product_text_on_shop" id="smartwoo_product_text_on_shop" value="<?php echo esc_attr( $product_text ); ?>" placeholder="eg, View Product" class="sw-form-input">
			</div>
            <?php foreach ( $checkboxes as $checkbox_name ) : ?>
                <div class="sw-form-row">
                    <label for="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-checkbox">
                        <?php echo esc_html( ucwords( str_replace( array( '_', 'smartwoo' ), ' ', $checkbox_name ) ) ); ?>
                    </label>
					<input type="checkbox" id="<?php echo esc_attr( $checkbox_name ); ?>" name="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-input" <?php checked( get_option( $checkbox_name, 0 ), 1 ); ?>>
                </div>
                <hr>
            <?php endforeach; ?>

            <?php
            // Check if the WooCommerce wallet plugin is active
            if ( function_exists( 'woo_wallet' ) ) : ?>
			<!-- TeraWallet integration option -->
			<h3 style="text-align: center;"><?php echo __( 'Tera Wallet Integration', 'smart-woo-service-invoicing' ); ?></h3>
			<!-- Refund Via TeraWallet -->
			<div class="sw-form-row">
				<label for="smartwoo_refund_with_wallet" class="sw-form-checkbox"><?php echo __( 'Refund Through Wallet', 'smart-woo-service-invoicing' ); ?></label>
				<input type="checkbox" class="sw-form-input" name="smartwoo_refund_with_wallet" id="smartwoo_refund_with_wallet" <?php echo  checked( get_option( 'smartwoo_refund_with_wallet', 0 ), 1, false ) ?>>
			</div>
			
			<!-- Pay Via TeraWallet -->
			<div class="sw-form-row">
				<label for="smartwoo_pay_pending_invoice_with_wallet" class="sw-form-checkbox"><?php echo __( 'Pay Pending Invoices with Wallet', 'smart-woo-service-invoicing' ); ?></label>
				<input type="checkbox" class="sw-form-input" name="smartwoo_pay_pending_invoice_with_wallet" id="smartwoo_pay_pending_invoice_with_wallet" <?php echo checked( get_option( 'smartwoo_pay_pending_invoice_with_wallet', 0 ), 1, false ); ?>>
			</div>
            
            <?php endif ?>
            <!-- Second submit button -->
            <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">
        </form>
    </div>
    <?php
	smartwoo_plugin_support();
	return ob_get_clean();
}

/**
 * Generate HTML content for upsell accordion for encouragement.
 *
 * @param bool $echo	 Whether to print or return content.
 * @return string HTML content.
 */
function smartwoo_support_our_work_container( $echo = true ) {
    $content = '<div class="sw-upsell-accordion">
        <button class="sw-accordion-btn">Support Our Work ♥♥♥</button>
        <div class="sw-upsell-panel">
            <p>' . __( 'If you find Smart Woo Service Invoicing Plugin valuable and would like to support our team in providing technical support, continuous improvement, and keeping the plugin free for everyone, you can contribute by making a financial donation.', 'smart-woo-service-invoicing' ) .'</p>
            <a href="' . esc_url( 'https://paystack.com/pay/support-smart-woo-dev' ) .'" target="_blank" class="sw-red-button">Donate with ♥</a>
        </div>
    </div>';

	if ( $echo ) {
		echo wp_kses_post( $content );
	}
	return $content;
}

/**
 * Generate HTML content for upsell accordion for Bug Report.
 *
 * @param bool $echo	 Whether to print or return content.
 * @return string HTML content.
 */
function smartwoo_bug_report_container( $echo = true) {
	$content = '<div class="sw-upsell-accordion">
        <button class="sw-accordion-btn">Report a Bug 🐞</button>
        <div class="sw-upsell-panel">
            <p>' . __( 'If you encounter any bugs or issues while using Smart Woo Service Invoicing Plugin, please report them to help us improve the plugin. Your feedback is valuable in enhancing the plugin\'s functionality and stability.', 'smart-woo-service-invoicing' ) . '</p>
            <a href="' . esc_url( 'https://wordpress.org/support/plugin/smart-woo-service-invoicing' ) . '" target="_blank" class="sw-red-button">Report a Bug</a>
        </div>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	} 

	return $content;
}

/**
 * Generate HTML content for upsell accordion for User Help.
 *
 * @param bool $echo	 Whether to print or return content.
 * @return string HTML content.
 */
function smartwoo_help_container( $echo = true) {
	    $content = '<div class="sw-upsell-accordion">
        <button class="sw-accordion-btn">Get Help 🏷</button>
        <div class="sw-upsell-panel">
            <p>Need assistance with using Smart Woo Service Invoicing Plugin? Check out our documentation or contact our support team for help. We are here to assist you in getting the most out of the plugin.</p>
            <a href="' . esc_url( 'https://callismart.com.ng/smart-woo' ) . '" target="_blank" class="sw-red-button">Get Help</a>
        </div>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	} else {
		return $content;
	}
}
