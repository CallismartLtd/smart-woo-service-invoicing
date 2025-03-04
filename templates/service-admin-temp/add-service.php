<?php
/**
 * File name: add-service.php
 * Template file to add new service
 * 
 * @author Callisus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<h2>Add New Service</h2>
<?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( $success );?>
<?php endif;?>
<br><br>


<p><?php esc_html_e( 'Create new service subscription and setup billing cycle', 'smart-woo-service-invoicing' );?></p>
<div class="sw-form-container">
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <!-- Assets -->
        <div class="sw-product-type-container">
            <label for="is-smartwoo-downloadable"><?php esc_html_e( 'Set Assets:', 'smart-woo-service-invoicing' );?>
                <input type="checkbox" name="is_smartwoo_downloadable" id="is-smartwoo-downloadable"/>
            </label> 
        </div>

        <hr><br> <br>
        <div class="sw-assets-div">
            <div class="sw-product-download-field-container">
                <p><strong><?php esc_html_e( 'Set up downloadable assets', 'smart-woo-service-invoicing' );?></strong></p>
                
                <div class="sw-product-download-fields">
                    <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                    <input type="text" class="fileUrl" name="sw_downloadable_file_urls[]" placeholder="File URL" />
                    <input type="button" class="upload_image_button button" value="Choose file" />
                    <button type="button" class="swremove-field">&times;</button>
                </div>
               
                
                <button type="button" id="add-field"> <?php esc_html_e( 'Add Fields', 'smart-woo-service-invoicing' );?></button>   
                <br><br>
                <div class="sw-form-row">
                <label for="isExternal" class="sw-form-label"><?php esc_html_e( 'External:', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php esc_attr_e( 'Select yes if the url of any downloadable file is external or protected resource', 'smart-woo-service-invoicing' );?>">?</span>
                    <select name="is_external" id="isExternal" class="sw-form-input">
                        <option value="no">No</option>
                        <option value="yes">Yes</option>
                    </select>
                </div>
                
                <div id="auth-token-div" class="smartwoo-hide">
                    <label for="assetKey" class="sw-form-label"><?php echo esc_html__( 'Authorizaton Token:', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php echo esc_attr__( 'If any of the downloadable asset is a protected resource on another server, ypu can optionally provide authorization token.', 'smart-woo-service-invoicing' );?>">?</span>
                    <input type="text" id="assetKey" class="sw-form-input" name="asset_key" placeholder="<?php esc_attr_e( 'Authorization token (optional)', 'smart-woo-service-invoicing' );?>" />
                </div>
                
                <div class="sw-form-row">
                    <label for="access-limit" class="sw-form-label"><?php esc_html_e( 'Access Limit', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php echo esc_attr__( 'Set access limit, leave empty for unlimited', 'smart-woo-service-invoicing' );?>">?</span>
                    <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.', 'smart-woo-service-invoicing' ); ?>">
                </div>
            </div>

            <span class="line"></span>
            <div class="sw-additional-assets" id="additionalAssets">
                <p><strong><?php esc_html_e( 'Additional Asset Types', 'smart-woo-service-invoicing' );?></strong></p>
                <div class="sw-additional-assets-field">
                    <input type="text" name="add_asset_types[]" placeholder="Asset Type" />
                    <input type="text" name="add_asset_names[]" placeholder="Asset Name" />
                    <input type="text" name="add_asset_values[]" placeholder="Asset Value" /> 
                    <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Limit (optional).', 'smart-woo-service-invoicing' ); ?>">

                </div>
                <button id="more-addi-assets"><?php esc_html_e( 'More Fields', 'smart-woo-service-invoicing' );?></button> 
            </div>
        </div>

		<?php wp_nonce_field( 'sw_add_new_service_nonce', 'sw_add_new_service_nonce' );?>
        <input type="hidden" name="action" value="smartwoo_add_service" />

		<!-- Service Name -->
		<div class="sw-form-row">
			<label for="service_name" class="sw-form-label"><?php esc_html_e( 'Service Name *', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service name (required)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_name" class="sw-form-input" id="service_name" required>
		</div>

		<!-- Service Type -->
		<div class="sw-form-row">
			<label for="service_type" class="sw-form-label"><?php esc_html_e( 'Service Type', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_type" class="sw-form-input" id="service_type">
		</div>

		<!-- Service URL -->
		<div class="sw-form-row">
			<label for="service_url" class="sw-form-label"><?php esc_html_e( 'Service URL', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service URL e.g., https:// (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_url" class="sw-form-input" id="service_url">
		</div>

		<!-- Choose a Client -->
		<div class="sw-form-row">
			<label for="user_id" class="sw-form-label"><?php esc_html_e( 'Choose a Client:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose a user from WordPress.(required)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php
			// WordPress User Dropdown.
			wp_dropdown_users(
				array(
					'name'             => 'user_id',
					'show_option_none' => esc_html__( 'Select User', 'smart-woo-service-invoicing' ),
					'class'            => 'sw-form-input',
					'show'             => 'display_name_with_login'
				)
			);
			?>
		</div>

		<!-- Service Products -->
		<div class="sw-form-row">
			<label for="service_products" class="sw-form-label"><?php esc_html_e( 'Service Products', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Select one product. This product price and fees will be used to create the next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php smartwoo_product_dropdown( '', true ); ?>
			
		</div>

		<!-- Start Date -->
		<div class="sw-form-row">
			<label for="start_date" class="sw-form-label"><?php esc_html_e( 'Start Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the start date for the service subscription.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="start_date" id="start_date" class="sw-form-input" required>
		</div>

		<!-- Billing Cycle -->
		<div class="sw-form-row">
			<label for="billing_cycle" class="sw-form-label"><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the billing cycle for the service. Invoices are created toward the end of the billing cycle.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
				<option value=""><?php esc_html_e( 'Select billing cycle', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Monthly"><?php esc_html_e( 'Monthly', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Quarterly"><?php esc_html_e( 'Quarterly', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Six Monthly"><?php esc_html_e( 'Semiannually', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Yearly"><?php esc_html_e( 'Yearly', 'smart-woo-service-invoicing' ); ?></option>
			</select>
		</div>

		<!-- Next Payment Date -->
		<div class="sw-form-row">
			<label for="next_payment_date" class="sw-form-label"><?php esc_html_e( 'Next Payment Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the next payment date. Services will be due and an invoice will be created on this day.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="next_payment_date" id="next_payment_date" class="sw-form-input" required>
		</div>

		<!-- End Date -->
		<div class="sw-form-row">
			<label for="end_date" class="sw-form-label"><?php esc_html_e( 'End Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="end_date" id="end_date" class="sw-form-input" required>
		</div>

		<!-- Set Service Status -->
		<div class="sw-form-row">
			<label for="status" class="sw-form-label"><?php esc_html_e( 'Set Service Status:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: an invoice will be created if the status is set to Due for Renewal.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<select name="status" id="status" class="sw-form-input">
				<option value=""><?php esc_html_e( 'Auto Calculate', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Active"><?php esc_html_e( 'Active', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Active (NR)"><?php esc_html_e( 'Disable Renewal', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Suspended"><?php esc_html_e( 'Suspend Service', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Cancelled"><?php esc_html_e( 'Cancel Service', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Due for Renewal"><?php esc_html_e( 'Due for Renewal', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Expired"><?php esc_html_e( 'Expired', 'smart-woo-service-invoicing' ); ?></option>
			</select>
		</div>


		<!-- Submit Button -->
		<div class="sw-form-row">
			<input type="submit" name="add_new_service_submit" class="sw-blue-button" value="Publish">
		</div>
	</form>
</div>