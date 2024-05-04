<?php
/**
 * File name sw-edit-product.php
 * Template rendering the admin edit product page
 * 
 * @author Callistus
 * @since 1.0.3
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/* Template Name: Edit product Product */

$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
if ( empty( $product_id ) ) {
    echo wp_kses_post( smartwoo_error_notice( 'Product ID Parameter must not be manipulated' ) );
    return;
}
    
$product_data = wc_get_product( $product_id );

if ( empty( $product_data ) ) {
    echo wp_kses_post( smartwoo_error_notice( 'You are trying to edit a product that doesn\'t exist, maybe it has been deleted' ) );
    return;
}

if ( ! $product_data instanceof SmartWoo_Product ) {
    echo wp_kses_post( smartwoo_error_notice( 'This is not a service product' ) );
    return;
}
?>

<div class="wrap">
    <h2>Edit Service Product</h2>
    <?php smartwoo_process_product_edit( $product_id ); ?>

    <div class="sw-form-container">
        <form method="post" action="" enctype="multipart/form-data">
            <input type="submit" name="update_service_product" class="sw-blue-button" value="Update Product">
            <?php wp_nonce_field( 'sw_edit_product_nonce', 'sw_edit_product_nonce' ); ?>

            <!-- Product Name -->
            <div class="sw-form-row">
                <label for="product_name" class="sw-form-label">Product Name</label>
                <span class="sw-field-description" title="Enter the main name of the product.">?</span>
                <input type="text" name="product_name" class="sw-form-input" value="<?php echo esc_attr( $product_data->get_name() ); ?>" required>
            </div>

            <!-- Product Price -->
            <div class="sw-form-row">
                <label for="product_price" class="sw-form-label">Product Price</label>
                <span class="sw-field-description" title="Enter product price">?</span>
                <input type="number" name="product_price" step="0.01" class="sw-form-input" value="<?php echo esc_attr( $product_data->get_price() ); ?>" required>
            </div>

            <!-- Sign-up Fee -->
            <div class="sw-form-row">
                <label for="sign_up_fee" class="sw-form-label">Sign-up Fee</label>
                <span class="sw-field-description" title="Charge Sign-up fee">?</span>
                <input type="number" name="sign_up_fee" step="0.01" class="sw-form-input" value="<?php echo esc_attr( $product_data->get_sign_up_fee() ); ?>">
            </div>

            <!-- Short Description -->
            <div class="sw-form-row">
                <label for="short_description" class="sw-form-label">Short Description</label>
                <span class="sw-field-description" title="Enter a brief description of the product.">?</span>
                <?php
                wp_editor(
                    wp_kses_post( $product_data->get_short_description() ),
                    'short_description',
                    array(
                        'textarea_name' => 'short_description',
                        'textarea_rows' => 5,
                        'teeny'         => true,
                        'media_buttons' => false,
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

            <!-- Billing Circle -->
            <div class="sw-form-row">
                <label for="billing_cycle" class="sw-form-label">Billing Circle:</label>
                <span class="sw-field-description" title="Set a default billing circle">?</span>
                <select name="billing_cycle" class="sw-form-input">
                    <option value="" <?php selected( '', $product_data->get_billing_cycle(), true ); ?>>Select Billing Cycle</option>
                    <option value="Monthly" <?php selected( 'Monthly', $product_data->get_billing_cycle(), true ); ?>>Monthly</option>
                    <option value="Quarterly" <?php selected( 'Quarterly', $product_data->get_billing_cycle(), true ); ?>>Quarterly</option>
                    <option value="Six Monthly" <?php selected( 'Six Monthly', $product_data->get_billing_cycle(), true ); ?>>Six Monthly</option>
                    <option value="Yearly" <?php selected( 'Yearly', $product_data->get_billing_cycle(), true ); ?>>Yearly</option>
                </select>
            </div>

            <!-- Grace Period -->
            <div class="sw-form-row">
                <label for="grace_period_number" class="sw-form-label">Grace Period</label>
                <div class="sw-form-input">
                    <p class="description-class">A Service with this product expires after.</p>
                    <input type="number" name="grace_period_number" class="grace-period-number input-class" min="1" value="<?php echo esc_attr( $product_data->get_grace_period_number() ); ?>">
                    <select name="grace_period_unit" class="select-class">
                        <option value="" <?php selected( '', $product_data->get_grace_period_unit(), true ); ?>>No Grace Period</option>
                        <option value="days" <?php selected( 'days', $product_data->get_grace_period_unit(), true ); ?>>Days</option>
                        <option value="weeks" <?php selected( 'weeks', $product_data->get_grace_period_unit(), true ); ?>>Weeks</option>
                        <option value="months" <?php selected( 'months', $product_data->get_grace_period_unit(), true ); ?>>Months</option>
                        <option value="years" <?php selected( 'years', $product_data->get_grace_period_unit(), true ); ?>>Years</option>
                    </select>
                </div>
            </div>

            <!-- Long Description -->
            <div class="sw-form-row">
                <label for="long_description" class="sw-form-label">Long Description:</label>
                <span class="sw-field-description" title="Enter detailed description for product">?</span>
                <?php
                wp_editor(
                    wp_kses_post( $product_data->get_description() ),
                    'description',
                    array(
                        'textarea_name' => 'description',
                        'textarea_rows' => 10,
                        'teeny'         => false,
                        'media_buttons' => true,
                        'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,close' ),
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

            <!-- Product Image -->
            <div class="sw-form-row">
                <label for="product_image" class="sw-form-label">Product Image</label>
                <div class="sw-form-input">
                    <input type="hidden" name="product_image_id" id="product_image_id" value="<?php echo absint( $product_data->get_image_id() ); ?>">
                    <div id="image_preview" class="sw-form-image-preview">
                        <img src="<?php echo esc_url( wp_get_attachment_image_url( $product_data->get_image_id(), 'medium' ) ); ?>" style="max-width: 250px;">
                    </div>
                    <input type="button" id="upload_image_button" class="sw-red-button" value="Upload Image">
                </div>
            </div>

            <input type="submit" name="update_service_product" class="sw-blue-button" value="Update Product">
        </form>
    </div>
</div>