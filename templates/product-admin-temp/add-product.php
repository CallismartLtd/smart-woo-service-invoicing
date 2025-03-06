<?php
/**
 * File name sw-add-product.php
 * Template rendering the admin add new product page
 * 
 * @author Callistus
 * @since 1.0.3
 * @package SmartWoo\admin\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>

<div class="smart-woo-product-form-page">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Add Product', 'sw-products', $tab, 'tab' ) ); ?>
    <h2>Create service subscription product</h2>
    <?php if ( $form_errors = smartwoo_get_form_error() ): ?>
            <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
        <?php elseif ( $success = smartwoo_get_form_success() ): ?>
            <?php echo wp_kses_post( $success );?>
    <?php endif;?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="sw-product-form">
        <input type="hidden" name="action" value="smartwoo_create_product" />
        <?php wp_nonce_field( 'sw_add_new_product_nonce', 'sw_add_new_product_nonce' ); ?>
        <div class="sw-product-form-product-data">
            <div class="sw-product-name-row">
                <input type="text" name="product_name"  id="product_name" placeholder="Product Name" autocomplete="off" spellcheck="true" value/>
            </div>

            <div class="sw-product-description">
                <p><label for="long_description">Product Description</label></p>
                <hr>
                <?php
                wp_editor(
                    '',
                    'description',
                    array(
                        'textarea_name' => 'description',
                        'textarea_rows' => 20,
                        'teeny'         => false,
                        'media_buttons' => true,
                        'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ),
                        'tinymce'       => array(
                            'resize'                       => true,
                            'browser_spellcheck'           => true,
                            'paste_remove_styles'          => true,
                            'paste_remove_spans'           => true,
                            'paste_strip_class_attributes' => 'all',
                            'paste_text_use_dialog'        => true,
                            'wp_autoresize_on'             => true,
                        ),
                    )
                );
                ?>
            </div>

            <div class="sw-product-data">
                <h4>Product Data</h4>
                <hr>
                <div class="sw-product-data-tabs">
                    <div class="sw-product-data-tabs-menu">
                        <ul>
                            <li class="tabs-general">General</li>
                            <hr>
                            <li class="tabs-sales">Sales</li>
                            <hr>
                            <li class="tabs-linked-products">Linked Products</li>
                        </ul>
                        
                    </div>
                    <div class="sw-product-data-tabs-content">
                        <div class="tabs-general-content smartwoo-hide">
                            <p>
                                Regular price (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>): <span><input type="number" name="regular_price" id="regular_price" step="0.01" ></span>
                            </p>
                            
                            <p>
                                Sign-up fee (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>): <span><input type="number" name="sign_up_fee" id="sign_up_fee" step="0.01"></span>
                            </p>
                        </div>

                        <div class="tabs-sales-content smartwoo-hide">
                            <p>
                                Sale price (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>): <span><input type="number" name="sale_price" id="sale_price" step="0.01" ></span>
                            </p>
                            <p>
                                Sale date from : <span><input type="datetime-local" name="sale_price_date_from" id="sale_price_date_from"></span>
                            </p>
                            <p>
                                Sale date to : <span><input type="datetime-local" name="sale_price_date_to" id="sale_price_date_to"></span>
                            </p>
                        </div>
                        
                    </div>
                </div>
            </div>

            <div class="sw-product-description" style="min-height: 250px;">
                <p><label for="short_description">Short Description</label></p>
                <hr>
                <?php
                wp_editor(
                    '',
                    'short_description',
                    array(
                        'textarea_name' => 'short_description',
                        'textarea_rows' => 5,
                        'teeny'         => true,
                        'media_buttons' => true,
                        'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ),
                        'tinymce'       => array(
                            'resize'                       => true,
                            'browser_spellcheck'           => true,
                            'paste_remove_styles'          => true,
                            'paste_remove_spans'           => true,
                            'paste_strip_class_attributes' => 'all',
                            'paste_text_use_dialog'        => true,
                            'wp_autoresize_on'             => true,
                        ),
                    )
                );
                ?>
            </div>
        </div>

        <div class="sw-product-metadata">
            <div class="sw-product-publish-options">
                <h4>Publish</h4>
                <hr>
                <p>
                    <span class="dashicons dashicons-post-status"></span>Status: 
                    <select name="product_status" id="product_status">
                        <option value="draft"><?php esc_html_e( 'Draft', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="publish"><?php esc_html_e( 'Publish', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="pending"><?php esc_html_e( 'Pending', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="private"><?php esc_html_e( 'Private', 'smart-woo-service-invoiving' ); ?></option>
                    </select>
                </p>
                <p><span class="dashicons dashicons-visibility"></span>Catalog Visibility:
                    <select name="product_status" id="product_status">
                        <option value="shop_and_search"><?php esc_html_e( 'Shop & search', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="shop"><?php esc_html_e( 'Shop Only', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="search_result"><?php esc_html_e( 'Search only', 'smart-woo-service-invoiving' ); ?></option>
                        <option value="hidden"><?php esc_html_e( 'Hidden', 'smart-woo-service-invoiving' ); ?></option>
                    </select>
                </p>
                <button type="submit" name="_smartwoo_product_publish" class="sw-blue-button">Publish</button>
            </div>
            <div class="sw-expiration-option">
                <h4 for="grace_period" class="sw-form-label">Grace Period</h4>
                <hr>
                <div class="sw-form-input">
                    <p class="description-class">A Service with this product expires after:</p>
                    <input type="number" name="grace_period_number" class="grace-period-number" id="grace_period_number" min="1" readonly>
                    <select name="grace_period_unit" class="select-grace period-unit" id="grace_period">
                        <option value="">Grace Period</option>
                        <option value="days">Days</option>
                        <option value="weeks">Weeks</option>
                        <option value="months">Months</option>
                        <option value="years">Years</option>
                    </select>
                </div>
            </div>

            <div class="sw-product-download-options">
                <h4>Downloads</h4>
                <hr>
                <div class="sw-product-type-container">
                    <label for="is-smartwoo-downloadable">Is downloadable?:
                        <input type="checkbox" name="is_smartwoo_downloadable" id="is-smartwoo-downloadable"/>
                    </label> 
                </div>

                <div class="sw-product-download-field-container">
                    <div class="sw-product-download-fields">
                        <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                        <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" placeholder="File URL" />
                        <input type="button" class="upload_image_button button" value="Choose file" />
                        <button type="button" class="swremove-field">x</button>
                    </div>
                    
                    <button type="button" id="add-field" style="display: none;">Add Field</button>
                </div>
            </div>
            <div class="sw-billing-cycle-option">
                <h4>Billing</h4>
                <hr>
                <p>
                    Billing cycle:
                    <?php smartwoo_billing_cycle_dropdown() ?>
                </p>
                
            </div>

            <div class="sw-product-image-option">
                <h4 for="product_image">Product Image</h4>
                <hr>
                <div class="sw-form-input">
                    <input type="hidden" name="product_image_id" id="product_image_id" value="">
                    <div id="image_preview" class="sw-form-image-preview"></div>
                    <br>
                    <input type="button" id="upload_sw_product_image" class="button" value="Upload">
                </div>
            </div>

            <div class="sw-product-gallery-option">
                <h4 for="product_image">Product gallery</h4>
                <hr>
                <div class="sw-product-gallery-container">
                    <div id="sw-product-gallery-preview" class="sw-product-gallery-preview"></div>
                    <br>
                    <input type="button" id="add-product-galleryBtn" class="button" value="Add product gallery images">
                </div>
            </div>
        </div>


    </form>
</div>
