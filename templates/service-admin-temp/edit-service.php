<?php
/**
 * File Name : edit-service.php
 * Service edit template file.
 * 
 * @package SmartWoo\templates.
 * @since 2.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Edit Service','sw-admin', $args, $query_var ) ); ?>

<?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( smartwoo_notice( $success, true ) );?>
<?php endif;?>
<br><br>
<div class="sw-form-container">
    <form action="<?php echo esc_url( admin_url( 'admin-post.php?service_id=' . $url_service_id ) ) ?>" method="post" id="smartwoo-service-form-id">

        <div class="sw-product-type-container">
            <label for="is-smartwoo-downloadable"><?php esc_html_e( 'Set Assets:', 'smart-woo-service-invoicing' );?>
                <input type="checkbox" name="is_smartwoo_downloadable" <?php checked( $is_downloadable ) ?> id="is-smartwoo-downloadable"/>
            </label> 
        </div>
        <hr><br> <br>
        <div class="sw-assets-div<?php echo $is_downloadable ? ' show':'';?>">
            <div class="sw-product-download-field-container<?php echo $is_downloadable ? ' show' : '';?>">
                <p><strong><?php echo ! $is_downloadable ? 'No d': 'D';?>ownloadable asset type in "<?php echo esc_html( $product_name );?>"</strong></p>
                <?php if ( $is_downloadable && ! empty( $downloadables )):?>
                    <?php foreach( $downloadables as $file_name => $url ):?>
                        <div class="sw-product-download-fields">
                            <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" value="<?php echo esc_attr( $file_name );?>" placeholder="File Name"/>
                            <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" value="<?php echo esc_attr( $url );?>" placeholder="File URL" />
                            <input type="button" class="upload_image_button button" value="Choose file" />
                            <button type="button" class="swremove-field">&times;</button>
                        </div>
                    <?php endforeach;?>
                    <input type="hidden" name="asset_ids[]" value="<?php echo absint( $id );?>"/>
                <?php else:?>
                        <div class="sw-product-download-fields">
                            <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                            <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" placeholder="File URL" />
                            <input type="button" class="upload_image_button button" value="Choose file" />
                            <button type="button" class="swremove-field">&times;</button>
                        </div>
                <?php endif;?>
                
                <button type="button" id="add-field" <?php echo $is_downloadable ? 'style="display: block;"': 'style="display: none;"';?>>Add Fields</button>   
                <br><br>
                <div class="sw-form-row">
                <label for="isExternal" class="sw-form-label"><?php echo esc_html__( 'External:', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php echo esc_attr__( 'Select yes if the url of any downloadable file is external or protected resource', 'smart-woo-service-invoicing' );?>">?</span>
                    <select name="is_external" id="isExternal" class="sw-form-input">
                        <option value="no" <?php selected( "no", ! empty( $downloads_a_obj ) ? $downloads_a_obj->is_external() : 'no' ) ?>>No</option>
                        <option value="yes" <?php selected( "yes", ! empty( $downloads_a_obj ) ? $downloads_a_obj->is_external() : '' ) ?>>Yes</option>
                    </select>
                </div>
                
                <div id="auth-token-div" class="<?php echo ( ! empty( $downloads_a_obj ) && 'yes' === $downloads_a_obj->is_external() ) ? 'sw-form-row': 'smartwoo-hide';?>">
                    <label for="assetKey" class="sw-form-label"><?php echo esc_html__( 'Authorizaton Token:', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php echo esc_attr__( 'If any of the downloadable asset is a protected resource on another server, ypu can optionally provide authorization token.', 'smart-woo-service-invoicing' );?>">?</span>
                    <input type="text" id="assetKey" class="sw-form-input" name="asset_key" placeholder="<?php esc_attr_e( 'Authorization token (optional)', 'smart-woo-service-invoicing' );?>" />
                </div>

                <div class="sw-form-row">
                    <label for="access-limit" class="sw-form-label"><?php esc_html_e( 'Access Limit', 'smart-woo-service-invoicing' );?></label>
                    <span class="sw-field-description" title="<?php echo esc_attr__( 'Set access limit, leave empty for unlimited', 'smart-woo-service-invoicing' );?>">?</span>
                    <input type="number" name="access_limits[]" value="<?php echo esc_html( ! empty( $downloads_a_obj ) ? $downloads_a_obj->get_access_limit( 'edit' ): '' ); ?>" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.' ); ?>">
                </div>

            </div>

            <span class="line"></span>
            <div class="sw-additional-assets" id="additionalAssets">
                <p><strong><?php esc_html_e( 'Additional Asset Types', 'smart-woo-service-invoicing' );?></strong></p>
                <span id="smartSpin"></span>
                <?php if ( $is_downloadable && ! empty( $additionals ) ): foreach ( $additionals as $main_asset ):?>
                    <div class="sw-additional-assets-field">
                        <input type="text" name="add_asset_types[]" value="<?php echo esc_attr( $main_asset->get_asset_name() );?>" placeholder="Asset Type" />
                        <?php foreach ( $main_asset->get_asset_data() as $name => $value ):?>
                            
                            <input type="text" name="add_asset_names[]" value="<?php echo esc_attr( $name );?>" placeholder="Asset Name" />
                            <input type="text" name="add_asset_values[]" value="<?php echo esc_attr( $value );?>" placeholder="Asset Value" />
                            <input type="hidden" name="asset_ids[]" value="<?php echo absint( $main_asset->get_id() );?>"/>
                            <input type="number" name="access_limits[]" class="sw-form-input" value="<?php echo esc_html( $main_asset->get_access_limit( 'edit' ) ); ?>" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.' ); ?>">

                            <button class="remove-field" title="<?php esc_attr_e( 'Remove this field', 'smart-woo-service-invoicing' );?>" data-removed-id="<?php echo absint( $main_asset->get_id() );?>">&times;</button>

                        <?php endforeach;?>
                    </div>

                <?php endforeach; else:?>
                    <div class="sw-additional-assets-field">
                        <input type="text" name="add_asset_types[]" placeholder="Asset Type" />
                        <input type="text" name="add_asset_names[]" placeholder="Asset Name" />
                        <input type="text" name="add_asset_values[]" placeholder="Asset Value" /> 
                        <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.' ); ?>">

                    </div>
                <?php endif;?>
                <button id="more-addi-assets"><?php esc_html_e( 'More Fields', 'smart-woo-service-invoicing' );?></button> 
            </div>
        </div>

        <?php wp_nonce_field( 'sw_edit_service_nonce', 'sw_edit_service_nonce' ); ?>
        <input type="hidden" name="action" value="smartwoo_edit_service"/>
        
        <div class="sw-form-row">
            <label for="service_name" class="sw-form-label"><?php esc_html_e( 'Service Name *', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service name (required)', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="text" name="service_name" class="sw-form-input" id="service_name" value="<?php echo esc_attr( $service_name ); ?>" required>
        </div>

        <div class="sw-form-row">
            <label for="service_type" class="sw-form-label"><?php esc_html_e( 'Service Type', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="text" name="service_type" class="sw-form-input" id="service_type" value="<?php echo esc_attr( $service_type ); ?>">
        </div>


        <!-- Service URL -->
        <div class="sw-form-row">
            <label for="service_url" class="sw-form-label"><?php esc_html_e( 'Service URL', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service URL e.g., https:// (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="url" name="service_url" class="sw-form-input" id="service_url" value="<?php echo esc_url( $service_url ); ?>" >
        </div>


        <!-- Choose a Client -->
        <div class="sw-form-row">
            <label for="user_id" class="sw-form-label"><?php esc_html_e( 'Choose a Client', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Choose a user from WordPress. (required)', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php
            $selected_user = ( $user_id ) ? get_user_by( 'ID', $user_id ) : false;
            wp_dropdown_users(
                array(
                    'name'              => 'user_id',
                    'selected'          => $selected_user ? $selected_user->ID : '',
                    'show_option_none'  => esc_html__( 'Select a user', 'smart-woo-service-invoicing' ),
                    'option_none_value' => '',
                    'class'             => 'sw-form-input',
                )
            );
            ?>
        </div>


        <!-- Service Products -->
        <div class="sw-form-row">
            <label for="service_products" class="sw-form-label"><?php esc_html_e( 'Service Products', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Select one product. This product price and fees will be used to create the next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <?php smartwoo_product_dropdown( $product_id, true ); ?>	
        </div>

        <!-- Start Date -->
        <div class="sw-form-row">
            <label for="start_date" class="sw-form-label"><?php esc_html_e( 'Start Date', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Choose the start date for the service subscription.', 'smart-woo-service-invoicing' ); ?>">?</span>
            <input type="date" name="start_date" class="sw-form-input" id="start_date" value="<?php echo esc_attr( $start_date ); ?>" required>
        </div>


        <!-- Billing Cycle -->
        <div class="sw-form-row">
            <label for="billing_cycle" class="sw-form-label"><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Choose the billing cycle for the service, invoices are created toward to the end of the billing cycle', 'smart-woo-service-invoicing' ); ?>">?</span>
            <select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
                <option value="" <?php selected( '', $billing_cycle ); ?>><?php esc_html_e( 'Select billing cycle', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Monthly" <?php selected( 'Monthly', ucfirst( strtolower( $billing_cycle ) ) ); ?>><?php esc_html_e( 'Monthly', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Quarterly" <?php selected( 'Quarterly', ucfirst( strtolower( $billing_cycle ) ) ); ?>><?php esc_html_e( 'Quarterly', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Six Monthly" <?php selected( 'Six Monthly', ucwords( strtolower( $billing_cycle ) ) ); ?>><?php esc_html_e( 'Six Monthly', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Yearly" <?php selected( 'Yearly', ucfirst( strtolower( $billing_cycle ) ) ); ?>><?php esc_html_e( 'Yearly', 'smart-woo-service-invoicing' ); ?></option>
            </select>
        </div>


        <!-- Next Payment Date -->
        <div class="sw-form-row">
            <label for="next_payment_date" class="sw-form-label">Next Payment Date</label>
            <span class="sw-field-description" title="Choose the next payment date, services wil be due and invoice is created on this day.">?</span>
            <input type="date" name="next_payment_date" class="sw-form-input" id="next_payment_date" value="<?php echo esc_attr( $next_payment_date ); ?>" required>
        </div>

        <!-- End Date -->
        <div class="sw-form-row">
            <label for="end_date" class="sw-form-label">End Date</label>
            <span class="sw-field-description" title="Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.">?</span>
            <input type="date" name="end_date" class="sw-form-input" id="end_date" value="<?php echo esc_attr( $end_date ); ?>" required>
        </div>

        <!-- Set Service Status -->
        <div class="sw-form-row">
            <label for="status" class="sw-form-label"><?php esc_html_e( 'Set Service Status', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="<?php esc_attr_e( 'Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal', 'smart-woo-service-invoicing' ); ?>">?</span>
            <select name="status" id="status" class="sw-form-input">
                <option value="" <?php selected( null, $status ); ?>><?php esc_html_e( 'Auto Calculate', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Active" <?php selected( 'Active', $status ); ?>><?php esc_html_e( 'Active', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Active (NR)" <?php selected( 'Active (NR)', $status ); ?>><?php esc_html_e( 'Disable Renewal', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Suspended" <?php selected( 'Suspended', $status ); ?>><?php esc_html_e( 'Suspend Service', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Cancelled" <?php selected( 'Cancelled', $status ); ?>><?php esc_html_e( 'Cancel Service', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Due for Renewal" <?php selected( 'Due for Renewal', $status ); ?>><?php esc_html_e( 'Due for Renewal', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Expired" <?php selected( 'Expired', $status ); ?>><?php esc_html_e( 'Expired', 'smart-woo-service-invoicing' ); ?></option>
            </select>
        </div>

        <!-- Submit Button -->
        <input type="submit" name="edit_service_submit" class="sw-blue-button" value="Update Service">
    </form>
</div>
