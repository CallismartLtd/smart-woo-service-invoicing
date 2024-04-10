<?php
/**
 * File name woo-forms.php
 * 
 * Description Additional form fields for WooCommerce my-account page.
 *
 * @author Callistus
 * @since  1.0.1
 * @package SmartWooServiceInvoicing
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Add User's Bio and url to WooCommerce edit-account form.
 */
function smartwoo_add_user_bio_and_website() {
    $user_id        = get_current_user_id();
    $user           = get_userdata( $user_id );
    $bio            = get_user_meta( $user_id, 'description', true );
    $website        = $user->user_url;
    ?>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="bio"><?php esc_html_e( 'Bio (optional)', 'smart-woo-service-invoicing' ); ?></label>
        <textarea class="woocommerce-Input woocommerce-Input--text input-text" name="bio" id="bio"><?php echo esc_textarea( $bio ); ?></textarea>
    </p>

    <?php wp_nonce_field( 'smart_woo_form_nonce', 'smart_woo_form_nonce' ); ?>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="website"><?php esc_html_e( 'Website', 'smart-woo-service-invoicing' ); ?></label>
        <input type="url" class="woocommerce-Input woocommerce-Input--text input-text" name="website" id="website" value="<?php echo esc_html( $website ); ?>" />
    </p>
    <?php
}
add_action( 'woocommerce_edit_account_form', 'smartwoo_add_user_bio_and_website' );

/**
 * Add Billing Website to WooCommerce edit Billing form
 */
function sw_add_billing_website_to_billing_edit_form() {
    $website = get_user_meta( get_current_user_id(), 'billing_website', true );
    ?>
    <?php wp_nonce_field( 'smart_woo_form_nonce', 'smart_woo_form_nonce' ); ?>

    <p class="form-row form-row-wide">
        <label for="billing_website"><?php esc_html_e( 'Billing Website', 'smart-woo-service-invoicing' ); ?></label>
        <input type="url" class="input-text" name="billing_website" id="billing_website" value="<?php echo esc_html( $website ); ?>" />
    </p>
    <?php
}
// Hook to add custom field to billing address form
add_action( 'woocommerce_after_edit_address_form_billing', 'sw_add_billing_website_to_billing_edit_form' );