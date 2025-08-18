<?php
/**
 * Advanced Settings Template
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>
<div class="smartwoo-settings-page">
    <h1><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Advanced Settings', 'smart-woo-service-invoicing' ); ?></h1>
    <form method="post" class="smartwoo-settings-form">
        <?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
        <div class="sw-form-row">
            <label for="smartwoo_product_text_on_shop" class="sw-form-label"><?php esc_html_e( 'Product add to cart text', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Set the text that will be shown on each Smart Woo Product on shop page">?</span>
            <input type="type" name="smartwoo_product_text_on_shop" id="smartwoo_product_text_on_shop" value="<?php echo esc_attr( $product_text ); ?>" placeholder="eg, View Product, add to cart, configure" class="sw-form-input">
        </div>
        <div class="sw-form-row">
            <label for="smartwoo_invoice_footer_text" class="sw-form-label"><?php esc_html_e( 'Invoice footer text', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Enter the text shown below the invoice">?</span>
            <textarea type="type" name="smartwoo_invoice_footer_text" id="smartwoo_invoice_footer_text" placeholder="Thanks for subscribing" class="sw-form-input"><?php echo esc_attr( $inv_footer_text ); ?></textarea>
        </div>

        <?php foreach ( $options as $checkbox_name ) : ?>
            <div class="sw-form-row">
                <label for="<?php echo esc_attr( $checkbox_name ); ?>" class="sw-form-checkbox">
                    <?php echo esc_html( ucwords( str_replace( array( '_', 'smartwoo' ), ' ', $checkbox_name ) ) ); ?>
                </label>
                <?php smartwoo_get_switch_toggle( array( 'id' => $checkbox_name, 'name'  => $checkbox_name, 'checked' => boolval( get_option( $checkbox_name, 0 ) ) ) ); ?>

            </div>
            <hr>
        <?php endforeach; ?>

        <h1>Fast checkout settings <a href="#" id="resetFastCheckoutOptions" style="font-size: 12px;"><?php esc_html_e( 'Reset to default', 'smart-woo-service-invoicing' ); ?></a></h1>
        <code><?php esc_html_e( 'Use {{product_name}} to include the product name in title', 'smart-woo-service-invoicing' ); ?></code>
        <div class="sw-admin-fast-checkout-option">
            <?php foreach( (array) $fc_options as $key => $value ) : ?>
                <div class="sw-service-form-row">
                    <label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $key ) ) ); ?></label>
                    <?php if ( in_array( $key, array( 'button_text_color', 'title_color', 'button_background_color', 'modal_background_color' ), true ) ) : ?>
                        <input type="color" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
                    <?php else: ?>
                        <input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <p style="width: 100%"><?php echo wp_kses_post( 'Fast checkout customization is experimental. If you want us extend these options, kindly <a href="https://callismart.com.ng/smart-woo-service-invoicing-release-notes/#request-a-feature">Reach out to us.</a>', 'smart-woo-service-invoicing' ); ?></p>
        </div>

        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save">
    </form>
</div>