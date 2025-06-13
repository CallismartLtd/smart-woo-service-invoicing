<?php
/**
 * Template Name: Product configuration
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/* Template Name: Product Configuration */

get_header();

$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : get_query_var( smartwoo_get_product_config_query_var(), 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$product      = wc_get_product( $product_id );
$product_name = $product ? $product->get_name() : 'Product Name not found';
?>


<div class="smartwoo_template" id="smartwooTemplate">
    <?php if ( ! $product ) : ?>
        <p><?php echo wp_kses_post( smartwoo_error_notice( 'Cannot configure product at this time, contact us if you nee further assistance.' ) ); ?></p>
    <?php else : ?>

        <h2>Configure</h2>
        <?php do_action( 'smartwoo_before_product_config_container', $product );?>
        <div id="error-container"></div>

        <div class="sw-configure-container">
            <?php SmartWoo_Product::sub_info(); ?>
            <p>Configure your desired options for <strong>"<?php echo esc_html( $product_name ); ?>"</strong> and continue checkout.</p>
            
            <form id="smartwooConfigureProduct" method="post">
                <?php wp_nonce_field( 'sw_product_configuration_nonce', 'sw_product_configuration_nonce' ); ?>
                
                <!-- Service Name -->
                <div class="sw-form-row">
                    <label for="service_name" class="sw-form-label">Service Name *</label>
                    <span class="sw-field-description" title="Enter the service name (required)">?</span>
                    <input type="text" name="service_name" class="sw-form-input" id="service_name" required>
                </div>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ) ?>">
                <!-- Service URL -->
                <div class="sw-form-row">
                    <label for="service_url" class="sw-form-label">Service URL (optional)</label>
                    <span class="sw-field-description" title="Enter the service URL e.g., https:// (optional)">?</span>
                    <input type="text" name="service_url" id="service_url" class="sw-form-input" id="service_url">
                </div>

                <!-- Submit Button -->
                <button type="submit" name="sw_add_configured_product_to_cart" value="Proceed" class="sw-blue-button">Configure and Proceed</button>

            </form>
        </div>
        <?php do_action( 'smartwoo_after_product_config_container', $product );?>

    <?php endif; ?>
</div>


<?php get_footer(); ?>
