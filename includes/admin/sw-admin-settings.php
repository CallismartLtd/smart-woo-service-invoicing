<?php
/**
 * File name    :   sw-admin-settings.php
 *
 * @author      :   Callistus
 * Description  :   settings page for admin submenu
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
/**
 * Handles email settings options when form is submitted
 * called directly within the HTML page redering
 */
function smartwoo_save_email_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['sw_save_email_options'], $_POST['sw_email_option_nonce']  ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['sw_email_option_nonce'] ) ), 'sw_email_option_nonce') ) {

		// Update billing email.
		if ( isset( $_POST['smartwoo_billing_email'] ) ) {
			update_option( 'smartwoo_billing_email', sanitize_email( wp_unslash( $_POST['smartwoo_billing_email'] ) ) );
		}

		if ( isset( $_POST['smartwoo_email_image_header'] ) ) {
			update_option( 'smartwoo_email_image_header', sanitize_url( wp_unslash( $_POST['smartwoo_email_image_header'] ), array( 'http', 'https' ) ) );
		}

		// Update sender name.
		if ( isset( $_POST['smartwoo_email_sender_name'] ) ) {
			update_option( 'smartwoo_email_sender_name', sanitize_text_field( wp_unslash( $_POST['smartwoo_email_sender_name'] ) ) );
		}

		// Define an array of checkbox names.
		$checkboxes = apply_filters( 'smartwoo_mail_options', array(
			'smartwoo_cancellation_mail_to_user',
			'smartwoo_service_opt_out_mail',
			'smartwoo_payment_reminder_to_client',
			'smartwoo_service_expiration_mail',
			'smartwoo_new_invoice_mail',
			'smartwoo_renewal_mail',
			'smartwoo_invoice_paid_mail',
			'smartwoo_service_cancellation_mail_to_admin',
			'smartwoo_service_expiration_mail_to_admin',
		) );

		// Update checkbox options
		foreach ( $checkboxes as $checkbox_name ) {
			if ( isset( $_POST[ $checkbox_name ] ) ) {
				update_option( $checkbox_name, 1  ); 
			} else {
				update_option( $checkbox_name, 0 ); 
			}
		}
		echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>' . esc_html__( 'Settings saved!', 'smart-woo-service-invoicing' ) . '</p></div>' );

	}
}

/**
 * Handle advance option submission
 */
function smartwoo_save_advanced_options(){

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['sw_save_options'], $_POST['sw_option_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {

		if ( isset( $_POST['smartwoo_product_text_on_shop'] ) ) {
			$value =  ! empty( $_POST['smartwoo_product_text_on_shop'] ) ?  sanitize_text_field( wp_unslash( $_POST['smartwoo_product_text_on_shop'] ) ) : 'Configure';
			update_option( 'smartwoo_product_text_on_shop', $value );
		}

		$checkboxes = apply_filters( 'smartwoo_advanced_options',
			array(
				'smartwoo_allow_invoice_tracking',
				'smartwoo_remove_plugin_data_during_uninstall'
			)
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

	if ( isset( $_POST['sw_save_options'], $_POST['sw_option_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {

		if ( isset( $_POST['smartwoo_invoice_page_id'] ) ) {
			update_option( 'smartwoo_invoice_page_id', absint( $_POST['smartwoo_invoice_page_id'] ) );
		}

		if ( isset( $_POST['smartwoo_invoice_logo_url'] ) ) {
			update_option( 'smartwoo_invoice_logo_url', sanitize_url( wp_unslash( $_POST['smartwoo_invoice_logo_url'] ), array( 'http', 'https' ) ) );
		}

		if ( isset( $_POST['smartwoo_invoice_watermark_url'] ) ) {
			update_option( 'smartwoo_invoice_watermark_url', sanitize_url( wp_unslash( $_POST['smartwoo_invoice_watermark_url'] ), array( 'http', 'https' ) ) );
		}

		if ( isset( $_POST['smartwoo_business_name'] ) ) {
			$business_name = isset( $_POST['smartwoo_business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smartwoo_business_name'] ) ) : get_bloginfo( 'name' );
			update_option( 'smartwoo_business_name', sanitize_text_field( $business_name ) );
		}

		if ( isset( $_POST['smartwoo_admin_phone_numbers'] ) ) {
			// Remove any characters except numbers and commas.
			$phone_numbers       = preg_replace( '/[^0-9+,]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_admin_phone_numbers'] ) ) );
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
			$smartwoo_prorate_value = ( 'Enable' === sanitize_text_field( wp_unslash( $_POST['smartwoo_prorate'] ) ) ) ? 'Enable': 'Disable';
			update_option( 'smartwoo_prorate', $smartwoo_prorate_value );
		}

		if ( isset( $_POST['smartwoo_invoice_id_prefix'] ) ) {
			$invoice_number_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_invoice_id_prefix'] ) ) );
			update_option( 'smartwoo_invoice_id_prefix', $invoice_number_prefix );
		}

		if ( isset( $_POST['smartwoo_service_id_prefix'] ) ) {
			$service_id_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_service_id_prefix'] ) ) );
			update_option( 'smartwoo_service_id_prefix', $service_id_prefix );
		}

		if ( isset( $_POST['smartwoo_allow_migration'] ) ) {
			$smartwoo_allow_migration = ( 'Enable' === $_POST['smartwoo_allow_migration'] ) ? 'Enable' : 'Disable';
			update_option( 'smartwoo_allow_migration', $smartwoo_allow_migration );
		}

		if ( isset( $_POST['smartwoo_upgrade_product_cat'] ) ) {
			$category_id = absint( $_POST['smartwoo_upgrade_product_cat'] );
			update_option( 'smartwoo_upgrade_product_cat', $category_id );
		}

		if ( isset( $_POST['smartwoo_downgrade_product_cat'] ) ) {
			$category_id = absint( $_POST['smartwoo_downgrade_product_cat'] );
			update_option( 'smartwoo_downgrade_product_cat', $category_id );
		}

		echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>' . esc_html( 'Settings saved!', 'smart-woo-service-invoicing' ) . '</p></div>' );
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
					<li><a class="settings-nav" href="#general-concept">General</a></li>
					<li><a class="settings-nav" href="#step1">Step 1</a></li>
					<li><a class="settings-nav" href="#step2">Step 2</a></li>
					<li><a class="settings-nav" href="#step3">Step 3</a></li>
				</ul>
			</div>

			<div class="sw-right-column">
				<div id="first-display" class="image-section">
					<h3> Smart Woo Service Invoicing</h3>
					<img src="<?php echo esc_url( SMARTWOO_DIR_URL . 'assets/images/smart-woo-img.png' ); ?>" alt="plugin screenshot" style="width: 50%;">
					<p>Here you will find useful information to get you started.</p>
			    </div>
			
				<div id="general-concept" class="instruction">
					<h3>Introduction</h3>
					<p><strong>Smart Woo Service Invoicing integrates powerful service subscription capabilities into your website. This includes automatic invoice creation for services that are due, prompt reminders, and a host of other interesting features.</strong></p>
					<p>To get started, there are basically three steps needed to get your subscriptions up and running.</p>
				</div>


				<div id="step1" class="instruction">
				<h3>Basic Set-up</h3>
					<p><strong>Set up your business details on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ); ?>" target="_blank">business settings page</a>, and invoicing preferences on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ); ?>" target="_blank">invoicing settings page</a>.</strong></p>
					<p>You may need to create two dedicated pages to allow your clients to fully manage their services and invoices. Usually, these pages should be automatically created for you during installation. If not, create them manually and ensure that each page contains the following shortcodes: <strong>[smartwoo_service_page]</strong> for the service page and <strong>[smartwoo_invoice_page]</strong> for the invoice page.</p>
				</div>


				<div id="step2" class="instruction">
					<h3>Create Product</h3>
					<p><strong>Create a <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ); ?>" target="_blank">Service Product</a> specifically dedicated to service subscriptions, and set up the necessary fields.</strong></p>
					<p>Create and publish your services as products. When a client purchases your service, an invoice will be automatically created for them. You'll also have options to manage and set up the subscription for them.</p>
				</div>

				<div id="step3" class="instruction">
					<h3>All Done 🎉🎉</h3>
					<p><strong>Your service product is now listed on the WooCommerce product page. You can view all service orders <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ); ?>">here</a> and process them as needed.</strong></p>
					<?php 	echo wp_kses_post( smartwoo_pro_feature() ); ?>
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
	smartwoo_set_document_title( 'Business Settings' );
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();

	smartwoo_save_options();
	$site_name             = get_bloginfo( 'name' );
	$business_name         = get_option( 'smartwoo_business_name', $site_name );
	$admin_phone_numbers   = get_option( 'smartwoo_admin_phone_numbers', '' );
	$service_page          = get_option( 'smartwoo_service_page_id', 0 );
	$pages                 = get_pages();
	$service_id_prefix     = get_option( 'smartwoo_service_id_prefix', 'SID' );
	?>
	<h1><span class="dashicons dashicons-tagcloud"></span> Business Info</h1>

		<div class="wrap">
		<form method="post" class="inv-settings-form">
		
		<?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
		<?php do_action( 'smartwoo_before_service_options' ) ?>

		
		<!-- Business Name -->
		<div class="sw-form-row">
		<label for="smartwoo_business_name" class="sw-form-label"><?php esc_html_e( 'Business Name', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter your business name">?</span>
		<input type="text" name="smartwoo_business_name" id="smartwoo_business_name" value="<?php echo esc_attr( $business_name ); ?>" placeholder="Enter business name" class="sw-form-input">
		</div>

		<!--Business Phone -->
		<div class="sw-form-row">
		<label for="smartwoo_admin_phone_numbers" class="sw-form-label"><?php esc_html_e( 'Phone Numbers', 'smart-woo-service-invoicing');?></label>
		<span class="sw-field-description" title="Enter admin phone numbers separated by commas (e.g., +123456789, +987654321).">?</span>
		<input type="text" name="smartwoo_admin_phone_numbers" id="smartwoo_admin_phone_numbers" value="<?php echo esc_attr( $admin_phone_numbers ); ?>" placeholder="Enter business phone numbers" class="sw-form-input">
		</div>

		<!--Service Page -->
		<div class="sw-form-row">
			<label for="smartwoo_service_page_id" class="sw-form-label"><?php esc_html_e( 'Service Page', 'smart-woo-service-invoicing' );?></label>
			<span class="sw-field-description" title="This page should have this shortcode [smartwoo_service_page] ">?</span>
			<select name="smartwoo_service_page_id" id="smartwoo_service_page_id" class="sw-form-input">
				<option value="0"><?php esc_html_e( 'Select a Service page', 'smart-woo-service-invoicing' ); ?></option>
				<?php foreach ( $pages as $page ) : ?>
				<option value="<?php echo esc_attr( $page->ID );?>"<?php selected( $service_page, $page->ID );?>><?php echo esc_html( $page->post_title );?> </option>
				<?php endforeach;?>
			</select>
		</div>

		<!-- Form field for service_id_prefix -->
		<div class="sw-form-row">
			<label for="smartwoo_service_id_prefix" class="sw-form-label"><?php esc_html_e( 'Service ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="Enter a text to prifix your service IDs">?</span>
			<input class="sw-form-input" type="text" name="smartwoo_service_id_prefix" id="smartwoo_service_id_prefix" value="<?php echo esc_attr( $service_id_prefix ); ?>" placeholder="eg, SMWSI">
		</div>

		<?php echo wp_kses_post( smartwoo_pro_feature( 'migration-options' ) ) ;?>

		<?php do_action( 'smartwoo_after_service_options' ) ?>
		
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
	smartwoo_set_document_title( 'Invoice Settings' );
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
	?>
		<h1><span class="dashicons dashicons-media-spreadsheet"></span> Invoice</h1>

		<div class="wrap">
		<form method="post" class="inv-settings-form">

		<?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
		<?php do_action( 'smartwoo_before_invoice_options' ) ?>

		<!--Invoice Page -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_page_id" class="sw-form-label"><?php esc_html_e( 'Invoice Page', 'smart-woo-service-invoicing' ) ?></label>
		<span class="sw-field-description" title="This page should have this shortcode [smartwoo_invoice_page]">?</span>
		<select name="smartwoo_invoice_page_id" id="smartwoo_invoice_page_id" class="sw-form-input">
		<option value="0"><?php esc_html_e( 'Select an invoice page', 'smart-woo-service-invoicing' ); ?></option>
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
		<label for="smartwoo_invoice_id_prefix" class="sw-form-label"><?php esc_html_e( 'Invoice ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="Enter a text to prifix your invoice IDs">?</span>
		<input class="sw-form-input" type="text" name="smartwoo_invoice_id_prefix" id="smartwoo_invoice_id_prefix" value="<?php echo esc_attr( $invoice_prefix ); ?>" placeholder="eg, INV">
		</div>

		<!-- Invoice Logo URL -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_logo_url" class="sw-form-label">Logo URL</label>
		<span class="sw-field-description" title="Paste the link to your logo url, size 512x512 pixels recommended">?</span>
		<input type="text" name="smartwoo_invoice_logo_url" id="smartwoo_invoice_logo_url" value="<?php echo esc_attr( $invoice_logo_url ); ?>" placeholder=" eg. www.example/image.png" class="sw-form-input">
		</div> 
		       
		<?php do_action( 'smartwoo_after_invoice_options' ) ?>

		<!-- Invoice Watermark URL -->
		<div class="sw-form-row">
		<label for="smartwoo_invoice_watermark_url" class="sw-form-label"><?php esc_html_e( 'Watermark URL', 'smart-woo-service-invoicing' ); ?></label>
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
	smartwoo_set_document_title( 'Email Settings' );
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$action = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ): '';
	$action = 'smartwoo_email_option_' . $action . '_section';

	if ( has_action( $action ) ) {
		do_action( $action );
		return;
	}

	smartwoo_save_email_options();
	$billing_email = get_option( 'smartwoo_billing_email' );
	$sender_name   = get_option( 'smartwoo_email_sender_name' );
	$email_image   = get_option( 'smartwoo_email_image_header' );
	$pro_installed = class_exists( 'SmartWooPro', false );


	// Define an array of checkbox names
	$checkboxes = apply_filters( 'smartwoo_mail_options', array(
		'smartwoo_new_invoice_mail',
		'smartwoo_payment_reminder_to_client',
		'smartwoo_invoice_paid_mail',
		'smartwoo_renewal_mail',
		'smartwoo_service_opt_out_mail',
		'smartwoo_cancellation_mail_to_user',
		'smartwoo_service_cancellation_mail_to_admin',
		'smartwoo_service_expiration_mail',
		'smartwoo_service_expiration_mail_to_admin',
	) );

	$not_editables = apply_filters( 'smartwoo_temp_option_uneditables', array( 'smartwoo_service_expiration_mail_to_admin') );


	?>
	<div class="wrap">
		<h1><span class="dashicons dashicons-email-alt"></span> Emails</h1>
		<p><span style="color: red;" class="dashicons dashicons-warning"></span><?php esc_html_e( 'If you notice emails are not being sent, consider setting up SMTP for your site.', 'smart-woo-service-invoicing' );?></p>
		<?php do_action( 'smartwoo_before_email_options' ) ?>
		
		<form method="post" class="inv-settings-form">
		<?php wp_nonce_field( 'sw_email_option_nonce', 'sw_email_option_nonce' ); ?>
			<!-- Sender Name -->
			<div class="sw-form-row">
				<label for="smartwoo_email_sender_name" class="sw-form-label"><?php esc_html_e( 'Sender Name','smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="This will be the sender name on the mail header">?</span>
				<input type="text" name="smartwoo_email_sender_name" id="smartwoo_email_sender_name" value="<?php echo esc_attr( $sender_name ); ?>" placeholder="eg, Billing Team" class="sw-form-input">
			</div>

			<!-- Email Image header -->
			<div class="sw-form-row">
				<label for="smartwoo_email_image_header" class="sw-form-label"><?php esc_html_e( 'Email Header Image','smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="Paste the URL of the image you want to show in the email header">?</span>
				<input type="url" name="smartwoo_email_image_header" id="smartwoo_email_image_header" value="<?php echo esc_attr( $email_image ); ?>" placeholder="eg example.com/image" class="sw-form-input">
			</div>

			<!-- Billing Email -->
			<div class="sw-form-row">
				<label for="smartwoo_billing_email" class="sw-form-label"><?php esc_html_e( 'Billing Email', 'smart-woo-service-invoicing' ) ?></label>
				<span class="sw-field-description" title="This email will be used to send emails to the clients">?</span>
				<input type="email" name="smartwoo_billing_email" id="smartwoo_billing_email" value="<?php echo esc_attr( $billing_email ); ?>" placeholder="eg, billing@domain.com" class="sw-form-input">
			</div>

			<h3 style="text-align: center;"><?php esc_html_e( 'Configure which Email is sent', 'smart-woo-service-invoicing' )?></h3>
			<!-- Checkboxes -->
			<?php foreach ( $checkboxes as $checkbox_name ) : ?>
				<div class="sw-form-row">
					<label for="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-checkbox">
						<?php echo esc_html( ucwords( str_replace( array( '_', 'smartwoo' ), ' ', $checkbox_name ) ) ); ?>
					</label>
					<input type="checkbox" id="<?php echo esc_attr( $checkbox_name ); ?>" name="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-input sw-checkboxes" <?php checked( get_option( $checkbox_name, 0 ), 1 ); ?>>
					<?php if ( ! in_array( $checkbox_name, $not_editables, true ) ): ?>
						<span style="margin-left: 20px;"></span><a href="<?php echo esc_attr( SmartWoo_Mail::get_preview_url( $checkbox_name ) ); ?>" class="sw-icon-button-admin" title="Preview" target="_blank"><span class="dashicons dashicons-visibility"></span></a>
						<a tempname="<?php echo esc_attr( $checkbox_name ); ?>" title="Edit template" class="sw-icon-button-admin <?php echo ( $pro_installed ) ? 'sw-edit-mail' : 'sw-edit-mail-nopro' ?>"><span class="dashicons dashicons-edit"></span></a>
					<?php elseif ( 'smartwoo_service_expiration_mail_to_admin' === $checkbox_name ): ?>
						<span style="margin-left: 20px;"></span><a href="<?php echo esc_attr( SmartWoo_Mail::get_preview_url( $checkbox_name ) ); ?>" class="sw-icon-button-admin" title="Preview" target="_blank"><span class="dashicons dashicons-visibility"></span></a>

					<?php endif; ?>
				</div>
				<hr>
			<?php endforeach; ?>
			<?php echo wp_kses_post( smartwoo_pro_feature( 'more-email-options' ) ) ;?>

			<?php do_action( 'smartwoo_after_email_options' ) ?>

			<input type="submit" class="sw-blue-button" name="sw_save_email_options" value="Save Changes">
		</form>
	</div>
	<?php
}

function smartwoo_advanced_options() {
	smartwoo_set_document_title( 'Advanced Settings' );
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	ob_start();
	smartwoo_save_advanced_options();
	$product_text = get_option( 'smartwoo_product_text_on_shop', 'Configure' );
    $checkboxes = apply_filters( 'smartwoo_advanced_options',
		array(
			'smartwoo_allow_invoice_tracking',
        	'smartwoo_remove_plugin_data_during_uninstall'
		)
    );
	
    ?>
    <div class="wrap">
		<h1><span class="dashicons dashicons-screenoptions"></span> Advanced Settings</h1>

        <form method="post" class="inv-settings-form">
            <?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
			<div class="sw-form-row">
				<label for="smartwoo_product_text_on_shop" class="sw-form-label"><?php esc_html_e( 'Product Button Text', 'smart-woo-service-invoicing' ); ?></label>
				<span class="sw-field-description" title="Set the text that will be shown on each Smart Woo Product on shop page">?</span>
				<input type="type" name="smartwoo_product_text_on_shop" id="smartwoo_product_text_on_shop" value="<?php echo esc_attr( $product_text ); ?>" placeholder="eg, View Product, add to cart, configure" class="sw-form-input">
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
            /** If forced, things may break. This checkboxe can only be handled by designated function */
            if ( function_exists( 'smartwoo_terawallet_int' ) ) : ?>
			<!-- TeraWallet integration option -->
			<h3 style="text-align: center;"><?php esc_html_e( 'Tera Wallet Integration', 'smart-woo-service-invoicing' ); ?></h3>
			<!-- Refund Via TeraWallet -->
			<div class="sw-form-row">
				<label for="smartwoo_refund_to_wallet" class="sw-form-checkbox"><?php esc_html_e( 'Refund Through Wallet', 'smart-woo-service-invoicing' ); ?></label>
				<input type="checkbox" class="sw-form-input" name="smartwoo_refund_to_wallet" id="smartwoo_refund_to_wallet" <?php echo  checked( get_option( 'smartwoo_refund_to_wallet', 0 ), 1, false ) ?>>
			</div>
			
			<!-- Pay Via TeraWallet -->
			<div class="sw-form-row">
				<label for="smartwoo_pay_pending_invoice_with_wallet" class="sw-form-checkbox"><?php esc_html_e( 'Pay Pending Invoices with Wallet', 'smart-woo-service-invoicing' ); ?></label>
				<input type="checkbox" class="sw-form-input" name="smartwoo_pay_pending_invoice_with_wallet" id="smartwoo_pay_pending_invoice_with_wallet" <?php echo checked( get_option( 'smartwoo_pay_pending_invoice_with_wallet', 0 ), 1, false ); ?>>
			</div>
            
            <?php endif ?>
            <!-- Second submit button -->
            <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">
        </form>
    </div>
    <?php
	echo wp_kses_post( smartwoo_pro_feature() );
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
            <p>' . esc_html__( 'If you find Smart Woo Service Invoicing Plugin valuable and would like to support our team in providing technical support, continuous improvement, and keeping the plugin free for everyone, you can contribute by making a financial donation.', 'smart-woo-service-invoicing' ) .'</p>
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
 * @param bool $echo		Whether to print or return content.
 * @return string $content	HTML content.
 */
function smartwoo_bug_report_container( $echo = true) {
	$content = '<div class="sw-upsell-accordion">
        <button class="sw-accordion-btn">Report a Bug 🐞</button>
        <div class="sw-upsell-panel">
            <p>' . esc_html__( 'If you encounter any bugs or issues while using Smart Woo Service Invoicing Plugin, please report them to help us improve the plugin. Your feedback is valuable in enhancing the plugin\'s functionality and stability.', 'smart-woo-service-invoicing' ) . '</p>
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
            <a href="' . esc_url( 'https://callismart.com.ng/smart-woo-service-invoicing' ) . '" target="_blank" class="sw-red-button">Get Help</a>
        </div>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	} else {
		return $content;
	}
}