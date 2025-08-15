<?php
/**
 * User billing address edit form
 */
defined( 'ABSPATH' ) ||  exit;
?>
<form id="smartwoo-billing-form" class="smartwoo-form" method="post">
    <h3 class="smartwoo-section-card__title"><?php echo esc_html__( 'Edit Billing Address', 'smart-woo-service-invoicing' ); ?></h3>
    <?php foreach ( $address_fields as $key => $field ) : ?>
        <?php woocommerce_form_field( $key, $field, $field['value'] ?? '' ); ?>
    <?php endforeach; ?>
    <input type="hidden" name="action" value="smartwoo_save_client_billing_details">
    <button type="submit" class="button sw-blue-button"><?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?></button>
</form>

