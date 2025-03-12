<?php
/**
 * The form that renders the new service processing form
 * 
 * @author Callistus
 * @package SmartWoo\templates
 * @since 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Process New Service Order</h1>
    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders') ); ?>" class="nav-tab">Dashboard</a>
        <button class="nav-tab nav-tab-active">Process Order</button>
        <a href="<?php echo esc_url( $order->get_parent_order()->get_edit_order_url() ); ?>" class="nav-tab">Edit parent order</a>
    </nav>
</div>
<?php if ( ! $order ) : ?>
    <?php echo wp_kses_post( smartwoo_error_notice( 'Invalid Order, please check whether the item exists. <a href="' . admin_url( 'admin.php?page=sw-service-orders' ) .'">Back</a>' ) ); ?>
<?php elseif ( $order->is_processed() ): ?>
    <?php echo wp_kses_post( smartwoo_error_notice( 'This order can no longer be processed. <a href="' . admin_url( 'admin.php?page=sw-service-orders' ) .'">Back</a>' ) ); ?>
<?php else: ?>
    <?php do_action( 'smartwoo_process_new_order_form_header', $order, $product ); ?>
    <p>After processing, this order will be marked as completed.</p>
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
            <?php echo wp_kses_post( smartwoo_error_notice( $form_errors, true ) );?>
        <?php elseif ( $success = smartwoo_get_form_success() ): ?>
            <?php echo wp_kses_post( $success );?>
    <?php endif;?>

    <div class="sw-form-container">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) );?>">

            <div class="sw-product-type-container">
                <label for="is-smartwoo-downloadable">Order Has Asset:
                    <input type="checkbox" name="is_smartwoo_downloadable" <?php checked( $is_downloadable ) ?> id="is-smartwoo-downloadable"/>
                </label> 
            </div>
            <hr><br> <br>
            <div class="sw-assets-div<?php echo $is_downloadable ? ' show':'';?>">
                <div class="sw-product-download-field-container<?php echo $is_downloadable ? ' show' : '';?>">
                <p><strong><?php echo ! $is_downloadable ? 'No d': 'D';?>ownloadable asset type in "<?php echo esc_html( $product_name );?>"</strong></p>
                    <?php if ( $is_downloadable ): $downloads = $product->get_smartwoo_downloads();?>
                        <?php foreach( $downloads as $file_name => $url ):?>
                            <div class="sw-product-download-fields">
                                <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" value="<?php echo esc_attr( $file_name );?>" placeholder="File Name"/>
                                <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" value="<?php echo esc_attr( $url );?>" placeholder="File URL" smartwoo-media-url />
                                <input type="button" class="smartwooOpenWpMedia button" value="Choose file" />
                                <span class="dashicons dashicons-dismiss swremove-field"></span>
                            </div>
                        <?php endforeach;?>
                    <?php else:?>
                            <div class="sw-product-download-fields">
                                <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                                <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" placeholder="File URL" smartwoo-media-url />
                                <input type="button" class="smartwooOpenWpMedia button" value="Choose file" />
                                <span class="dashicons dashicons-dismiss swremove-field"></span>
                            </div>
                    <?php endif;?>
                    
                    <button type="button" id="add-field" <?php echo $is_downloadable ? 'style="display: block;"': 'style="display: none;"';?>>Add More Assets</button>   
                    <br><br>
                    <div class="sw-form-row">
                    <label for="order_id" class="sw-form-label"><?php echo esc_html__( 'External:', 'smart-woo-service-invoicing' );?></label>
                        <span class="sw-field-description" title="<?php echo esc_attr__( 'Select yes if the url of any downloadable file is external or protected resource', 'smart-woo-service-invoicing' );?>">?</span>
                        <select name="is_external" id="isExternal" class="sw-form-input">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>
                    
                    <div id="auth-token-div" class="smartwoo-hide">
                        <label for="assetKey" class="sw-form-label"><?php echo esc_html__( 'Authorizaton Token:', 'smart-woo-service-invoicing' );?></label>
                        <span class="sw-field-description" title="<?php echo esc_attr__( 'If any of the downloadable asset is a protected resource on another server, you can optionally provide authorization token.', 'smart-woo-service-invoicing' );?>">?</span>
                        <input type="text" id="assetKey" class="sw-form-input" autocomplete="off" name="asset_key" placeholder="<?php esc_attr_e( 'Authorization token (optional)', 'smart-woo-service-invoicing' );?>" />
                    </div>

                    <div class="sw-form-row">
                        <label for="access-limit" class="sw-form-label"><?php esc_html_e( 'Access Limit', 'smart-woo-service-invoicing' );?></label>
                        <span class="sw-field-description" title="<?php echo esc_attr__( 'Set access limit, leave empty for unlimited', 'smart-woo-service-invoicing' );?>">?</span>
                        <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.', 'smart-woo-service-invoicing' ); ?>">
                    </div>

                </div>
                <span class="line"></span>
                <div class="sw-additional-assets" id="additionalAssets">
                    <p><strong>Additional asset types</strong></p>
                    <div class="sw-additional-assets-field">
                        <input type="text" name="add_asset_types[]" placeholder="Asset Type" />
                        <input type="text" name="add_asset_names[]" placeholder="Asset Name" />
                        <textarea type="text" name="add_asset_values[]" placeholder="Asset Value (html allowed)" style="width: 90%; min-height: 100px"></textarea>
                        
                        
                        <input type="number" name="access_limits[]" min="-1" placeholder="<?php esc_attr_e( 'Limit (optional).', 'smart-woo-service-invoicing' ); ?>">
                    </div>
                    <button id="more-addi-assets">More Fields</button> 
                </div>
            </div>
                
            <div class="sw-form-row">
                <label for="order_id" class="sw-form-label"><?php echo esc_html__( 'Order:', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="The order ID and Product Name">?</span>
                <input type="text" name="order_id" id="order_id" class="sw-form-input" value="<?php echo esc_attr( $order_id ) . ' - ' . esc_html( $product_name );?>" readonly>
            </div>
        
            <?php smartwoo_service_ID_generator_form( $service_name, true, true );?>
        
            <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id );?>">
            <input type="hidden" name="action" value="smartwoo_service_from_order">
            <?php wp_nonce_field( 'sw_process_new_service_nonce', 'sw_process_new_service_nonce' );?>
            <!-- Service URL. -->
            <div class="sw-form-row">
                <label for="service_url" class="sw-form-label"><?php echo esc_html__( 'Service URL:', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'Enter the service URL e.g., https:// (optional)', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="url" name="service_url" class="sw-form-input" id="service_url" value="<?php echo esc_url( $service_url );?>" >
            </div>
            <!-- Service Type -->
            <div class="sw-form-row">
                <label for="service_type" class="sw-form-label"><?php echo esc_html__( 'Service Type', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'Enter the service type (optional)', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="text" name="service_type" class="sw-form-input" id="service_type">
            </div>
            <!-- Client's Name. -->
            <div class="sw-form-row">
                <label for="user_id" class="sw-form-label"><?php echo esc_html__( 'Client\'s Name', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'The user whose ID is associated with the order', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="text" class="sw-form-input" name="user_id" id="user_id" value="<?php echo esc_attr( $user_full_name );?>" readonly>
            </div>
            <input type="hidden" name="user_id" value="<?php echo esc_attr( $user_id );?>">
            <!-- Sart date. -->
            <div class="sw-form-row">
                <label for="start_date" class="sw-form-label"><?php echo esc_html__( 'Start Date:', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'Choose the start date for the service subscription, service was ordered on this date.', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="date" name="start_date" class="sw-form-input" id="start_date" value="<?php echo esc_attr( $start_date );?>" required>
            </div>

            <!-- Billing Cycle -->
            <div class="sw-form-row">
                <label for="billing_cycle" class="sw-form-label"><?php echo esc_html__( 'Billing Cycle', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'This billing cycle was set from the product, you may edit it, invoices are created toward to the end of the billing cycle.', 'smart-woo-service-invoicing' );?>">?</span>
                <select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
                <option value=""><?php echo esc_html__( 'Select billing cycle', 'smart-woo-service-invoicing' );?></option>
                <option value="Monthly" <?php selected( 'Monthly', $billing_cycle ); ?>><?php echo esc_html__( 'Monthly', 'smart-woo-service-invoicing' );?></option>
                <option value="Quarterly" <?php selected( 'Quarterly', $billing_cycle ); ?>><?php echo esc_html__( 'Quarterly', 'smart-woo-service-invoicing' ); ?></option>
                <option value="Six Monthly" <?php selected( 'Six Monthly', $billing_cycle ); ?>><?php echo esc_html__( 'Semiannually', 'smart-woo-service-invoicing' );?></option>
                <option value="Yearly" <?php selected( 'Yearly', $billing_cycle ); ?>><?php echo esc_html__( 'Yearly', 'smart-woo-service-invoicing' );?></option>
                </select>
            </div>
            <!-- Next Payment Date. -->
            <div class="sw-form-row">
                <label for="next_payment_date" class="sw-form-label"><?php echo esc_html__( 'Next Payment Date', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="'<?php echo esc_attr__( 'Choose the next payment date, services will be due and invoice is created on this day.', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="date" class="sw-form-input" name="next_payment_date" id="next_payment_date" value="<?php echo esc_attr( $next_payment_date );?>" required>
            </div>
            <!-- End Date. -->
            <div class="sw-form-row">
                <label for="end_date" class="sw-form-label"><?php echo esc_html__( 'End Date', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.', 'smart-woo-service-invoicing' );?>">?</span>
                <input type="date" class="sw-form-input" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date );?>" required>
            </div>
            <!-- Status. -->
            <div class="sw-form-row">
                <label for="status" class="sw-form-label"><?php echo esc_html__( 'Set Service Status:', 'smart-woo-service-invoicing' );?></label>
                <span class="sw-field-description" title="<?php echo esc_attr__( 'Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal', 'smart-woo-service-invoicing' );?>">?</span>
                <select name="status" class="sw-form-input" id="status">
        
                <?php foreach ( $status_options as $value => $label ):?>
                    <option value="<?php echo esc_attr( $value );?>"<?php selected( $value, $status );?>><?php echo esc_html( $label );?></option>
                <?php endforeach;?>
                </select>
            </div>
        
            <button type="submit" class="sw-blue-button" id="create_new_service">Finish Processing<span class="dashicons dashicons-yes"></span></button>

        </form>
    </div>
<?php endif; ?>