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
<?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, __( 'Edit Service Subscription', 'smart-woo-service-invoicing' ),'sw-admin','edit-service', $query_var ) ); ?>
<?php if ( empty( $service ) ) : ?>
    <?php echo wp_kses_post( smartwoo_notice( 'Invalid or deleted service subscription <a href="' . esc_url( admin_url( 'admin.php?page=sw-admin' ) ) . '">Back</a>' ) ) ?>
<?php else : ?>
    <div class="smart-woo-service-form-page">

        <div id="response-container"></div>

        <form class="sw-service-form" id="smartwooServiceForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php') ) ?>">
            <?php wp_nonce_field( 'sw_edit_service_nonce', 'sw_edit_service_nonce' ); ?>
            <input type="hidden" name="action" value="smartwoo_edit_service"/>
            <input type="hidden" name="sw_service_id" value="<?php echo esc_html( $service->get_service_id() ); ?>">
            <div id="swloader" style="background-color: #f1f1f100"></div>
            <div class="sw-service-form-left-container">
                <div class="sw-service-form-left-essentials">
                    <div class="sw-service-form-row">
                        <label for="service_name"><?php esc_html_e( 'Service Name', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="service_name" name="sw_service_name" value="<?php echo esc_html( $service->get_name() ); ?>" placeholder="<?php esc_html_e( 'enter service name', 'smart-woo-service-invoicing' ); ?>" field-name="Service name" autocomplete="off" required>
                        <label for="service_type"><?php esc_html_e( 'Service Type', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="service_type" name="sw_service_type" value="<?php echo esc_html( $service->get_type() ); ?>" placeholder="<?php esc_html_e( 'eg. web service, water billing', 'smart-woo-service-invoicing' ); ?>" autocomplete="off">	
                    </div>	

                    <div class="sw-service-form-row">
                        <label for="service_url"><?php esc_html_e( 'Service URL', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="service_url" name="sw_service_url" value="<?php echo esc_html( $service->get_service_url() ); ?>" placeholder="<?php esc_html_e( 'example.com', 'smart-woo-service-invoicing' ); ?>" autocomplete="off">	
                        <label for="service_products"><?php esc_html_e( 'Select Product', 'smart-woo-service-invoicing' ); ?></label>
                        <?php smartwoo_product_dropdown( $service->get_product_id(), true ); ?>
                    </div>
                </div>
                
                <div class="sw-service-form-assets-container">
                    <h3>Service Assets</h3>
                    <hr>
                    <label for="is-smartwoo-downloadable"><?php esc_html_e( 'Set Assets:', 'smart-woo-service-invoicing' );?>
                        <input type="checkbox" name="has_assets" id="is-smartwoo-downloadable" <?php echo esc_html( $has_asset ? 'checked' : '' ); ?>/>
                    </label> 
                    <h2 class="sw-no-download-text" style="text-align: center;<?php echo esc_attr( $has_asset ? 'display: none' : '' ); ?>"><?php esc_html_e( 'No asset set for this subscription' ) ?></h2>
                    <div class="sw-service-assets-downloads-container" style="<?php echo esc_html( $has_asset ? 'display: block;' : '' ); ?>">
                        <h3><strong><?php esc_html_e( 'Asset type:', 'smart-woo-service-invoicing' );?></strong> <?php esc_html_e( 'Downloads', 'smart-woo-service-invoicing' );?></h3>
                        
                        <?php if ( $has_asset && ! empty( $downloadables ) ) : ?>
                            <?php foreach ( $downloadables as $file_name => $url ) : ?>
                                <div class="sw-product-download-fields">
                                    <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" value="<?php echo esc_html( $file_name ); ?>" placeholder="File Name"/>
                                    <input type="text" class="fileUrl" name="sw_downloadable_file_urls[]" value="<?php echo esc_html( $url ); ?>" smartwoo-media-url placeholder="File URL" />
                                    <button class="smartwooOpenWpMedia button"><?php esc_html_e( 'Choose file', 'smart-woo-service-invoicing' ); ?></button>
                                    <span type="button" class="dashicons dashicons-dismiss swremove-field"></span>
                                </div>
                            <?php endforeach; ?>
                            <input type="hidden" name="download_asset_type_id" value="<?php echo absint( $download_asset_type_id );?>"/>
                        <?php else: ?>
                            <div class="sw-product-download-fields">
                                <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                                <input type="text" class="fileUrl" name="sw_downloadable_file_urls[]" smartwoo-media-url placeholder="File URL" />
                                <button class="smartwooOpenWpMedia button"><?php esc_html_e( 'Choose file', 'smart-woo-service-invoicing' ); ?></button>
                            </div>
                        <?php endif; ?>

                        <button class="button" id="add-field"> <?php esc_html_e( 'More Fields', 'smart-woo-service-invoicing' );?></button>   
                        
                        <div class="sw-service-assets-downloads-meta">
                            <div class="sw-service-form-row">
                                <label for="isExternal"><?php esc_html_e( 'Is external:', 'smart-woo-service-invoicing' );?></label>
                                <select name="is_external" id="isExternal" class="sw-is-external">
                                    <option value="no" <?php selected( "no", ! empty( $download_asset_object ) ? $download_asset_object->is_external() : 'no' ) ?>>No</option>
                                    <option value="yes" <?php selected( "yes", ! empty( $download_asset_object ) ? $download_asset_object->is_external() : '' ) ?>>Yes</option>
                                </select>
                            </div>
                            
                            <div class="sw-service-form-row">
                                <label for="access-limit"><?php esc_html_e( 'Access Limit', 'smart-woo-service-invoicing' );?></label>
                                <input type="number" name="download_limit" value="<?php echo esc_html( ! empty( $download_asset_object ) ? $download_asset_object->get_access_limit( 'edit' ): '' ); ?>" id="access-limit" min="-1" placeholder="<?php esc_attr_e( 'Leave empty for unlimited access.', 'smart-woo-service-invoicing' ); ?>">
                            </div>
                        </div>
                        <div id="auth-token-div" class="sw-service-form-row" style="display: <?php echo esc_attr( ( ! empty( $download_asset_object ) && 'yes' === $download_asset_object->is_external() )  ? 'flex': 'none' ); ?>;">
                            <input type="text" id="assetKey" name="asset_key" placeholder="<?php esc_attr_e( 'Authorization token (optional)', 'smart-woo-service-invoicing' );?>" autocomplete="off"/>
                        </div>
                        
                    </div>

                    <div class="sw-service-additional-assets-container" id="additionalAssets" style="display: <?php echo esc_attr( $has_asset ? 'block': 'none' ); ?>">
                        <h3><strong><?php esc_html_e( 'Custom Asset Types', 'smart-woo-service-invoicing' );?></strong></h3>
                        
                        <?php if ( $has_asset && ! empty( $additionals ) ) : ?>
                            <?php foreach( $additionals as $asset ) : ?>
                                <div class="sw-additional-assets-field">
                                    <h4>
                                        <?php esc_html_e( 'Asset type:') ?>
                                        <input type="text" name="additional_asset_types[]" value="<?php echo esc_html( $asset->get_asset_name() ); ?>" placeholder="eg. support service..." />
                                    </h4>
                                    <input type="text" name="additiional_asset_names[]" value="<?php echo esc_html( array_key_first( $asset->get_asset_data() ) ); ?>" placeholder="Asset Name" />
                                    <input type="number" name="access_limits[]" value="<?php echo esc_html( $asset->get_access_limit( 'edit' ) ); ?>" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Limit (optional).', 'smart-woo-service-invoicing' ); ?>">
                                    <textarea type="text" name="additional_asset_values[]" placeholder="Asset Value (also supports html and shortcodes)" style="width: 90%; min-height: 100px"><?php echo wp_kses_post( $asset->get_data( array_key_first( $asset->get_asset_data() ) ) ); ?></textarea>
                                    <input type="hidden" name="asset_type_ids[]" value="<?php echo absint( $asset->get_id() );?>"/>
                                    <span class="dashicons dashicons-trash remove-field" title="<?php esc_attr_e( 'Delete permanently', 'smart-woo-service-invoicing' );?>" data-removed-id="<?php echo absint( $asset->get_id() );?>"></span>

                                </div>
                            <?php endforeach; ?>
                        
                        <?php else: ?>
                            <div class="sw-additional-assets-field">
                                <h4>
                                    <?php esc_html_e( 'Asset type:') ?>
                                    <input type="text" name="additional_asset_types[]" placeholder="eg. support service..." />
                                </h4>
                                <input type="text" name="additiional_asset_names[]" placeholder="Asset Name" />
                                <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="<?php esc_attr_e( 'Limit (optional).', 'smart-woo-service-invoicing' ); ?>">
                                <textarea type="text" name="additional_asset_values[]" placeholder="Asset Value (also supports html and shortcodes)" style="width: 90%; min-height: 100px"></textarea>
                            </div>
                        <?php endif;?>
                        
                        <button id="more-addi-assets" class="button"><?php esc_html_e( 'Another Asset', 'smart-woo-service-invoicing' );?></button> 
                    </div>
                    
                </div>
                    
            </div>

            <div class="sw-service-form-right-container">
                <div class="sw-service-client-info">
                    <img src="<?php echo esc_url( get_avatar_url( $service->get_user_id() ) ); ?>" loading="lazy" alt="avatar" width="92" height="92">
                    <p class="sw-user-fullname"><strong>Full name</strong>: <?php echo esc_html( $user_fullname ) ?></p>
                    <p class="sw-user-email"><strong>Email</strong>: <?php echo esc_html( $user->user_email ); ?></p>
                    <p id="spinner" style="text-align: center;"></p>
                </div>
                <?php smartwoo_dropdown_users( 
                    $user->ID . '|' . $user->user_email, 
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
                        <input type="text" id="sw_start_date" name="start_date" value="<?php echo esc_html( $service->get_start_date() ); ?>" autocomplete="off" placeholder="From YYYY-MM-DD" field-name="A start date" required>
                    </div>

                    <div class="sw-service-form-row">
                        <label for="sw_billing_cycle" ><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></label>
                        <?php smartwoo_billing_cycle_dropdown( $service->get_billing_cycle(), array( 'option_none' => 'Choose billing cycle', 'id'	=> 'sw_billing_cycle', 'required' => true ) ); ?>
                    </div>

                    <div class="sw-service-form-row">
                        <label for="sw_next_payment_date"><?php esc_html_e( 'Next Payment Date', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="sw_next_payment_date" name="next_payment_date" value="<?php echo esc_html( $service->get_next_payment_date() ); ?>" autocomplete="off" placeholder="Next Invoice YYYY-MM-DD" field-name="Next payment date field" required>	
                    </div>

                    <div class="sw-service-form-row">
                        <label for="sw_end_date"><?php esc_html_e( 'End Date', 'smart-woo-service-invoicing' ); ?></label>
                        <input type="text" id="sw_end_date" name="end_date" value="<?php echo esc_html( $service->get_end_date() ); ?>" autocomplete="off" placeholder="Ends YYYY-MM-DD" field-name="Service end date field" required>	
                    </div>

                    <div class="sw-service-form-row">
                        <label for="status">Status</label>
                        <?php smartwoo_service_status_dropdown( $service->get_status(), array( 'class' => 'sw-status' ) ); ?>
                    </div>
                    
                    <div class="sw-service-form-row">
                        <label for="publish">Save Service</label>
                        <button type="submit" class="sw-blue-button button"><span class="dashicons dashicons-cloud-saved"></span> <?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?></button>
                    </div>
                    
                </div>
            </div>
            
        </form>

    </div>
<?php endif; ?>