<?php
/**
 * Template rendering the admin edit product page
 * 
 * @author Callistus
 * @since 1.0.3
 * @package SmartWoo\admin\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>

<div class="smart-woo-product-form-page">
    <?php echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Edit Product', 'sw-products&product_id='. $product_id, $tab, 'tab' ) ); ?>
    <h2>Edit service subscription product</h2>
    <?php if ( ! $product || ! is_a( $product, 'SmartWoo_Product' ) ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( 'You are trying to edit a product that doesn\'t exist, maybe it has been deleted' ) ); return; ?>

    <?php endif; ?>

    <div id="response-container"></div>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" id="sw-product-form">
        <input type="hidden" name="action" value="smartwoo_edit_product" />
        <input type="hidden" name="smartwoo_product_id" value="<?php echo absint( $product->get_id() ) ?>">
        <?php wp_nonce_field( 'sw_add_new_product_nonce', 'sw_edit_product_nonce' ); ?>
        <div class="sw-product-form-product-data">
            <div class="sw-product-name-row">
                <input type="text" name="product_name"  id="product_name" placeholder="Product Name" autocomplete="off" spellcheck="true" value="<?php echo esc_html( $product->get_name() ); ?>"/>
                <p><strong>Slug</strong>: <code><?php echo esc_html( $product_page ); ?></code><input type="text"  name="product_slug" value="<?php echo esc_html( $product->get_slug() ); ?>" ><code>/</code></p>
            </div>
            
            <div class="sw-product-description">
                <p><label for="long_description">Product Description</label></p>
                <hr>
                <?php
                wp_editor(
                    wp_kses_post( $product->get_description() ),
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
            <div id="swloader" style="background-color: #f1f1f100"></div>
            <div class="sw-product-data">
                <h4>Product Data</h4>
                <hr>
                <div class="sw-product-data-tabs">
                    <div class="sw-product-data-tabs-menu">
                        <ul>
                            <li class="active">General</li>
                            <hr>
                            <li class="tabs-sales">Sales</li>
                            <hr>
                            <li class="tabs-linked-products">Linked Products</li>
                            <?php if ( $add_extra_tabs ) : ?>
                                <?php foreach( $menus as $menu ) : ?>
                                    <hr>
                                    <li><?php echo esc_html( $menu ); ?></li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        
                    </div>
                    <div class="sw-product-data-tabs-content">
                        <div>
                            <p>
                                <label for="regular_price">Regular price (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>):</label> <span><input type="number" name="regular_price" id="regular_price" step="0.01" value="<?php echo esc_html( $product->get_regular_price() ); ?>" /></span>
                            </p>
                            
                            <p>
                                <label for="sign_up_fee">Sign-up Fee (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>):</label> <span><input type="number" name="sign_up_fee" id="sign_up_fee" step="0.01" value="<?php echo esc_html( $product->get_sign_up_fee() ); ?>"/></span>
                            </p>
                            
                        </div>

                        <div class="smartwoo-hide">
                            <p>
                                <label for="sale_price">Sale price (<?php echo esc_html( get_woocommerce_currency_symbol() ) ?>):</label> <span><input type="number" name="sale_price" id="sale_price" step="0.01" value="<?php echo esc_html( $product->get_sale_price() ); ?>" /></span>
                            </p>
                            <p>
                                <label for="date_on_sale_from">Sale date from :</label> <span><input type="text" name="date_on_sale_from" id="date_on_sale_from" value="<?php echo esc_html( $product->get_date_on_sale_from() ? $product->get_date_on_sale_from()->date( 'Y-m-d' ) : '' ); ?>" placeholder="FROM... YYYY-MM-DD"/></span>
                            </p>
                            <p>
                                <label for="date_on_sale_to">Sale date to:</label> <span><input type="text" name="date_on_sale_to" id="date_on_sale_to" value="<?php echo esc_html( $product->get_date_on_sale_to() ? $product->get_date_on_sale_to()->date( 'Y-m-d' ) : '' ); ?>" placeholder="TO... YYYY-MM-DD"/></span>
                            </p>
                        </div>

                        <div class="smartwoo-hide">
                            <p class="smartwoo-select-2">
                                <label for="upsell_ids">Upsells:</label>
                                <select class="wc-product-search" multiple="multiple" id="upsell_ids" name="upsell_ids[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-woo-service-invoicing' ); ?>" data-action="smartwoo_json_search_sw_products" data-exclude="<?php echo esc_attr( $product ? $product->get_id() : '' ); ?>">
                                    <?php foreach ( $product->get_upsell_ids() as $up_id ) {
                                        $up_product = wc_get_product( $up_id );
                                        if ( is_object( $up_product ) ) {
                                            echo '<option value="' . esc_attr( $up_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $up_product->get_formatted_name() ) ) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                            <p class="smartwoo-select-2">
                                <label for="cross_sell_ids">Cross-sells:</label>
                                <select class="wc-product-search" multiple="multiple" id="cross_sell_ids" name="cross_sell_ids[]" data-sortable="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'smart-woo-service-invoicing' ); ?>" data-action="smartwoo_json_search_sw_products" data-exclude="<?php echo esc_attr( $product ? $product->get_id() : '' ); ?>">
                                    <?php foreach ( $product->get_cross_sell_ids() as $cross_id ) {
                                        $cross_product = wc_get_product( $cross_id );
                                        if ( is_object( $cross_product ) ) {
                                            echo '<option value="' . esc_attr( $cross_id ) . '"' . selected( true, true, false ) . '>' . esc_html( wp_strip_all_tags( $cross_product->get_formatted_name() ) ) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </p>
                        </div>
                        <?php if ( $add_extra_tabs ) : ?>
                            <?php foreach( $callbacks as $function ) : ?>
                                <div class="smartwoo-hide">
                                    <?php call_user_func( $function ); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="sw-product-description" style="min-height: 250px;">
                <p><label for="short_description">Short Description</label></p>
                <hr>
                <?php
                wp_editor(
                    wp_kses_post( $product->get_short_description() ),
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
                    <span class="dashicons dashicons-post-status"></span>
                    <label for="product_status">Status:</label> 
                    <select name="product_status" id="product_status">
                        <option value="draft" <?php selected( 'draft', $product->get_status() ) ?>><?php esc_html_e( 'Draft', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="publish" <?php selected( 'publish', $product->get_status() ) ?>><?php esc_html_e( 'Publish', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="pending" <?php selected( 'pending', $product->get_status() ) ?>><?php esc_html_e( 'Pending', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="private" <?php selected( 'private', $product->get_status() ) ?>><?php esc_html_e( 'Private', 'smart-woo-service-invoicing' ); ?></option>
                    </select>
                </p>
                <p><span class="dashicons dashicons-visibility"></span>
                    <label for="catalog_visibility">Catalog Visibility:</label>
                    <select name="visibility" id="catalog_visibility">
                        <option value="visible" <?php selected( 'visible', $product->get_catalog_visibility() ) ?>><?php esc_html_e( 'Shop & search', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="catalog" <?php selected( 'catalog', $product->get_catalog_visibility() ) ?>><?php esc_html_e( 'Shop Only', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="search" <?php selected( 'search', $product->get_catalog_visibility() ) ?>><?php esc_html_e( 'Search only', 'smart-woo-service-invoicing' ); ?></option>
                        <option value="hidden" <?php selected( 'hidden', $product->get_catalog_visibility() ) ?>><?php esc_html_e( 'Hidden', 'smart-woo-service-invoicing' ); ?></option>
                    </select>
                </p>

                <p><span class="dashicons dashicons-category"></span>
                    Categories: 
                    <?php if ( ! empty( $product_categories ) ) : ?>
                        <?php foreach( $product_categories as $category ) : ?>
                            <div class="sw-product-category">
                                <label for="cat_<?php echo absint( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?>:</label>
                                <input type="checkbox" name="product_category_ids[]" value="<?php echo absint( $category->term_id ); ?>" id="cat_<?php echo absint( $category->term_id ); ?>" <?php echo esc_attr( in_array( $category->term_id, $product->get_category_ids(), true ) ? 'checked=checked' : '' ); ?>>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </p>

                <p>
                    <span class="dashicons dashicons-sticky"></span>
                    <label for="is_featured">This is a featured product:</label>
                    <input type="checkbox" name="_is_featured" id="is_featured" value="is_featured" <?php checked( $product->get_featured() ) ?>/>
                </p>
                <p>
                    <span class="dashicons dashicons-cart"></span>
                    <label for="is_sold_individually" title="Whether this product should be sold individually">Sold individually:</label>
                    <input type="checkbox" name="is_sold_individually" id="is_sold_individually" value="is_sold_individually" <?php checked( $product->get_sold_individually() ) ?>>

                </p>
                <button type="submit" name="_smartwoo_product_publish" class="sw-blue-button">Publish</button>
                <button href="<?php echo esc_attr( $product->get_permalink() ); ?>" class="sw-blue-button smartwoo-prevent-default">Preview</button>
            </div>
            <div class="sw-expiration-option">
                <h4  class="sw-form-label"><label for="grace_period">Grace Period</label></h4>
                <hr>
                <div class="sw-form-input">
                    <p class="description-class">A subscription with this product expires<span><?php echo esc_html( $product->get_grace_period_number() ? ' after': '' ); ?></span>:</p>
                    <input type="number" name="grace_period_number" class="grace-period-number" id="grace_period_number" min="1" value="<?php echo esc_html( $product->get_grace_period_number() ? $product->get_grace_period_number() : '' ); ?>" <?php echo esc_attr( $product->get_grace_period_number() ?: 'readonly' ); ?>>
                    <select name="grace_period_unit" class="select-grace period-unit" id="grace_period">
                        <option value="" <?php selected( '', $product->get_grace_period_unit() ) ?>>Immediately</option>
                        <option value="days" <?php selected( 'days', $product->get_grace_period_unit() ) ?>>Days</option>
                        <option value="weeks" <?php selected( 'weeks', $product->get_grace_period_unit() ) ?>>Weeks</option>
                        <option value="months" <?php selected( 'months', $product->get_grace_period_unit() ) ?>>Months</option>
                        <option value="years" <?php selected( 'years', $product->get_grace_period_unit() ) ?>>Years</option>
                    </select>
                </div>
            </div>

            <div class="sw-product-download-options">
                <h4>Downloads</h4>
                <hr>
                <div class="sw-product-type-container">
                    <label for="is-smartwoo-downloadable">Is downloadable?:
                        <input type="checkbox" name="is_smartwoo_downloadable" id="is-smartwoo-downloadable" <?php checked( 'on', ( $product->is_downloadable() ? 'on' : '' ) ) ?>/>
                    </label> 
                </div>

                <div class="sw-product-download-field-container"<?php echo $product->is_downloadable() ? 'style="display:block"' : '';?>>
                    <?php if ( $product->is_downloadable() ): $downloads = $product->get_smartwoo_downloads(); ?>
                        <?php foreach( $downloads as $file => $url ): ?>
                            <div class="sw-product-download-fields">
                            <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name" value="<?php echo esc_html( $file ); ?>"/>
                            <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" smartwoo-media-url placeholder="File URL" value="<?php echo esc_html( $url ); ?>"/>
                            <input type="button" class="smartwooOpenWpMedia button"  value="Change file" />
                            <button type="button" class="swremove-field">x</button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="sw-product-download-fields">
                            <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                            <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" smartwoo-media-url placeholder="File URL" />
                            <input type="button" class="smartwooOpenWpMedia button" value="Choose file" />
                        </div>
                    <?php endif; ?>
                
                    <button type="button" id="add-field" <?php (  $product->is_downloadable() ) ?: 'style="display: none;"'; ?>>Add Field</button>
                </div>
            </div>
            <div class="sw-billing-cycle-option">
                <h4>Billing</h4>
                <hr>
                <p>
                    <label for="sw_billing_cycle">Billing Cycle</label>
                    <?php smartwoo_billing_cycle_dropdown( $product->get_billing_cycle() ); ?>
                </p>
                
            </div>

            <div class="sw-product-image-option">
                <h4>Product Image</h4>
                <hr>
                <div class="sw-form-input">
                    <input type="hidden" name="product_image_id" id="product_image_id" value="<?php echo absint( $product->get_image_id() ); ?>">
                    <div id="image_preview" class="sw-form-image-preview">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="product-image" style="max-width: 100%">
                    </div>
                    <br>
                    <input type="button" id="upload_sw_product_image" class="button" value="remove">
                </div>
            </div>

            <div class="sw-product-gallery-option">
                <h4 for="product_image">Product gallery</h4>
                <hr>
                <div class="sw-product-gallery-container">
                    <div id="sw-product-gallery-preview" class="sw-product-gallery-preview">
                        <?php if( ! empty( $ids = $product->get_gallery_image_ids() ) ) : ?>

                            <?php foreach( $ids as $id ) : ?>
                                <div class="sw-image-img">
                                    <span class="dashicons dashicons-dismiss"></span>
                                    <input type="hidden" name="product_gallery_ids[]" value="<?php echo absint( $id ); ?>"/>
                                    <img src="<?php echo esc_url( wp_get_attachment_url( $id ) ); ?>" alt="image"/>
                                </div>
                            <?php endforeach; ?>
                            
                        <?php endif; ?>

                    </div>
                    <br>
                    <input type="button" id="add-product-galleryBtn" class="button" value="Add product gallery images">
                </div>
            </div>
        </div>


    </form>
</div>
