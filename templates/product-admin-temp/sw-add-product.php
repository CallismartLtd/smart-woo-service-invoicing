<?php
/**
 * File name sw-add-product.php
 * Template rendering the admin add new product page
 * 
 * @author Callistus
 * @since 1.0.3
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/* Template Name: Add New Product */
?>

<div class="wrap">
<h2>Add New Service Product</h2>
<?php if ( $form_errors = smartwoo_get_form_error() ): ?>
        <?php echo wp_kses_post( smartwoo_error_notice( $form_errors ) );?>
    <?php elseif ( $success = smartwoo_get_form_success() ): ?>
        <?php echo wp_kses_post( $success );?>
<?php endif;?>

<div class="sw-form-container">
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="sw-product-form-class">
        <div class="sw-product-type-container">
            <label for="is-smartwoo-downloadable">Downloadable Product:
                <input type="checkbox" name="is_smartwoo_downloadable" id="is-smartwoo-downloadable"/>
            </label> 
       </div>
       <hr><br> <br>
        <div class="sw-product-download-field-container">
            <div class="sw-product-download-fields">
                <input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" placeholder="File URL" />
                <input type="button" class="upload_image_button button" value="Choose file" />
                <button type="button" class="swremove-field">x</button>
            </div>
            
            <button type="button" id="add-field" style="display: none;">Add Field</button>
        </div>
        
        <input type="hidden" name="action" value="smartwoo_create_product" />
        <?php wp_nonce_field( 'sw_add_new_product_nonce', 'sw_add_new_product_nonce' ); ?>

        <div class="sw-form-row">
            <label for="product_name"  class="sw-form-label">Product Name</label>
            <span class="sw-field-description" title="Enter Product Name">?</span>
            <input type="text" name="product_name" class="sw-form-input" id="product_name" autocomplete="false" >
        </div>

        <div class="sw-form-row">
            <label for="product_price" class="sw-form-label">Product Price</label>
            <span class="sw-field-description" title="Enter Product Price">?</span>
            <input type="number" name="product_price" class="sw-form-input" id="product_price" step="0.01" >
        </div>

        <div class="sw-form-row">
            <label for="sign_up_fee" class="sw-form-label">Sign-Up Fee:</label>
            <span class="sw-field-description" title="Charge Sign-up fee (optional)">?</span>
            <input type="number" name="sign_up_fee" class="sw-form-input" id="sign_up_fee" step="0.01">
        </div>

        <div class="sw-form-row">
            <label for="short_description" class="sw-form-label">Short Description</label>
            <span class="sw-field-description" title="Enter short description for product">?</span>
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

        <div class="sw-form-row">
            <label for="billing_cycle" class="sw-form-label">Billing Cycle:</label>
            <span class="sw-field-description" title="Set a default billing cycle">?</span>
            <select name="billing_cycle" class="sw-form-input" id="billing_cycle">
                <option value="" selected>Select Billing Cycle</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Six Monthly">Semiannually</option>
                <option value="Yearly">Yearly</option>
            </select>
        </div>

        <div class="sw-form-row">
            <label for="grace_period" class="sw-form-label">Grace Period</label>
            <div class="sw-form-input">
                <p class="description-class">A Service with this product expires after</p>
                <input type="number" name="grace_period_number" class="grace-period-number" id="grace_period_number" min="1" >
                <select name="grace_period_unit" class="select-grace period-unit" id="grace_period">
                    <option value="">Select Grace Period</option>
                    <option value="days">Days</option>
                    <option value="weeks">Weeks</option>
                    <option value="months">Months</option>
                    <option value="years">Years</option>
                </select>
            </div>
        </div>

        <div class="sw-form-row">
            <label for="long_description" class="sw-form-label">Product Description</label>
            <span class="sw-field-description" title="Enter detailed description for product">?</span>
            <?php
            wp_editor(
                '',
                'description',
                array(
                    'textarea_name' => 'description',
                    'textarea_rows' => 10,
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

        <div class="sw-form-row">
            <label for="product_image" class="sw-form-label">Product Image</label>
            <div class="sw-form-input">
                <input type="hidden" name="product_image_id" id="product_image_id" value="" class="sw-form-input">
                <div id="image_preview" class="sw-form-image-preview"></div>
                <input type="button" id="upload_sw_product_image" class="sw-red-button" value="Upload Image">
            </div>
        </div>

        <input type="submit" name="create_sw_product" value="Create Product" class="sw-blue-button">
    </form>
</div>
</div>