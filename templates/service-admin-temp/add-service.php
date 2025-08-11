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
<div class="smart-woo-service-form-page">
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
            <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
        <?php elseif ( $success = smartwoo_get_form_success() ): ?>
            <?php echo wp_kses_post( $success );?>
    <?php endif; ?>

    <div id="response-container"></div>
	<form class="sw-service-form" id="smartwooServiceForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php') ) ?>">
		<?php wp_nonce_field( 'sw_add_new_service_nonce', 'sw_add_new_service_nonce' );?>
		<input type="hidden" name="action" value="smartwoo_add_service" />
		<div id="swloader" style="background-color: #f1f1f100"></div>
		<div class="sw-service-form-left-container">
			<div class="sw-service-form-left-essentials">
				<div class="sw-service-form-row">
					<label for="service_name"><?php esc_html_e( 'Service Name', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="service_name" name="sw_service_name" placeholder="<?php esc_html_e( 'enter service name', 'smart-woo-service-invoicing' ); ?>" field-name="Service name" autocomplete="off" required>
					<label for="service_type"><?php esc_html_e( 'Service Type', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="service_type" name="sw_service_type" placeholder="<?php esc_html_e( 'eg. web service, water billing', 'smart-woo-service-invoicing' ); ?>" autocomplete="off">	
				</div>	

				<div class="sw-service-form-row">
					<label for="service_url"><?php esc_html_e( 'Service URL', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="service_url" name="sw_service_url" placeholder="<?php esc_html_e( 'example.com', 'smart-woo-service-invoicing' ); ?>" autocomplete="off">	
					<label for="service_products"><?php esc_html_e( 'Select Product', 'smart-woo-service-invoicing' ); ?></label>
					<?php smartwoo_product_dropdown( '', true ); ?>

				</div>
			</div>
			
			<div class="sw-service-form-assets-container">
				<h3>Service Assets</h3>
				<hr>
				<label for="is-smartwoo-downloadable"><?php esc_html_e( 'Set Assets:', 'smart-woo-service-invoicing' );?>
					<input type="checkbox" name="has_assets" id="is-smartwoo-downloadable"/>
				</label> 
				<h2 class="sw-no-download-text" style="text-align: center;"><?php esc_html_e( 'No asset set for this subscription', 'smart-woo-service-invoicing' ) ?></h2>
				<div class="sw-service-assets-downloads-container">
					<h3><strong><?php esc_html_e( 'Asset type:', 'smart-woo-service-invoicing' );?></strong> <?php esc_html_e( 'Downloads', 'smart-woo-service-invoicing' );?></h3>
					
					<div class="sw-product-download-fields">
						<input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
						<input type="text" class="fileUrl" name="sw_downloadable_file_urls[]" smartwoo-media-url placeholder="File URL" />
						<button class="smartwooOpenWpMedia button"><?php esc_html_e( 'Choose file', 'smart-woo-service-invoicing' ); ?></button>
					</div>
					
					<button class="button" id="add-field"> <?php esc_html_e( 'Add File', 'smart-woo-service-invoicing' );?></button>   
					
					<div class="sw-service-assets-downloads-meta">
						<div class="sw-service-form-row">
							<label for="isExternal"><?php esc_html_e( 'Is external:', 'smart-woo-service-invoicing' );?></label>
							<select name="is_external" id="isExternal" class="sw-is-external">
								<option value="no">No</option>
								<option value="yes">Yes</option>
							</select>
						</div>
						
						<div class="sw-service-form-row">
							<label for="access-limit"><?php esc_html_e( 'Access Limit', 'smart-woo-service-invoicing' );?></label>
							<input type="number" name="download_limit" id="access-limit" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.', 'smart-woo-service-invoicing' ); ?>">
						</div>
					</div>
					<div id="auth-token-div" class="sw-service-form-row" style="display: none;">
						<input type="text" id="assetKey" name="asset_key" placeholder="<?php esc_attr_e( 'Authorization token (optional)', 'smart-woo-service-invoicing' );?>" autocomplete="off"/>
					</div>
					
				</div>

				<div class="sw-service-additional-assets-container" id="additionalAssets">
					<h3><strong><?php esc_html_e( 'Custom Asset Types', 'smart-woo-service-invoicing' );?></strong></h3>
					<div class="sw-additional-assets-field">
						<h4>
							<?php esc_html_e( 'Asset type:', 'smart-woo-service-invoicing' ) ?>
							<input type="text" name="additional_asset_types[]" placeholder="eg. support service..." />
						</h4>
						<input type="text" name="additiional_asset_names[]" placeholder="Asset Name" />
						<input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Limit (optional).', 'smart-woo-service-invoicing' ); ?>">
						<textarea type="text" class="smartwoo-asset-editor-ui" name="additional_asset_values[]" placeholder="Start building: rich text, immersive audio & video playlists, stunning image galleries, custom HTML, or shortcodes."></textarea>
						
					</div>
					<button id="more-addi-assets" class="button"><?php esc_html_e( 'Add Asset', 'smart-woo-service-invoicing' );?></button> 
				</div>
				
			</div>
				
		</div>

		<div class="sw-service-form-right-container">
			<div class="sw-service-client-info">
				<img src="<?php echo esc_url( smartwoo_get_avatar_placeholder_url() ); ?>" alt="avatar" width="92" height="92">
				<p class="sw-user-fullname"><?php esc_html_e( 'No user selected', 'smart-woo-service-invoicing' ) ?></p>
				<p class="sw-user-email"></p>
				<p id="spinner" style="text-align: center;"></p>
			</div>
			<?php smartwoo_dropdown_users( 
				'', 
				array(
					'class'		=> 'sw-service-user-dropdown',
					'id'		=> 'smartwooServiceUserDropdown',
					'add_guest' => false,
					'name'		=> 'sw_user_id',
					'option_none'	=> 'Choose client',
					'required'		=> true,
					'field_name'	=> 'A client'
				)
			); ?>

			<div class="sw-service-billing-data">
				<div class="sw-service-form-row">
					<label for="sw_start_date"><?php esc_html_e( 'Start Date', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="sw_start_date" name="start_date" autocomplete="off" placeholder="From YYYY-MM-DD" field-name="A start date" required>
				</div>

				<div class="sw-service-form-row">
					<label for="sw_billing_cycle" ><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></label>
					<?php smartwoo_billing_cycle_dropdown( '', array( 'option_none' => 'Choose billing cycle', 'id'	=> 'sw_billing_cycle', 'required' => true ) ); ?>
				</div>

				<div class="sw-service-form-row">
					<label for="sw_next_payment_date"><?php esc_html_e( 'Next Payment Date', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="sw_next_payment_date" name="next_payment_date" autocomplete="off" placeholder="Next Invoice YYYY-MM-DD" field-name="Next payment date field" required>	
				</div>

				<div class="sw-service-form-row">
					<label for="sw_end_date"><?php esc_html_e( 'End Date', 'smart-woo-service-invoicing' ); ?></label>
					<input type="text" id="sw_end_date" name="end_date" autocomplete="off" placeholder="Ends YYYY-MM-DD" field-name="Service end date field" required>	
				</div>

				<div class="sw-service-form-row">
					<label for="status">Status</label>
					<?php smartwoo_service_status_dropdown( '', array( 'class' => 'sw-status' ) ); ?>
				</div>
				
				<div class="sw-service-form-row">
					<label for="publish">Save Service</label>
					<button type="submit" id="publish" class="sw-blue-button button"><span class="dashicons dashicons-cloud-saved"></span> <?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?></button>
				</div>
				
			</div>
		</div>
		
	</form>

</div>
<?php smartwoo_enqueue_media_assets(); ?>