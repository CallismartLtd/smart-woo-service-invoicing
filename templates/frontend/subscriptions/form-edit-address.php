<?php
/**
 * User billing address edit form
 */
defined( 'ABSPATH' ) ||  exit;
?>
<div class="smartwoo-modal-fram">
    <form id="smartwoo-billing-form" class="smartwoo-form" method="post">

        <?php foreach ( $address_fields as $key => $field ) : ?>
            <?php woocommerce_form_field( $key, $field, $field['value'] ?? '' ); ?>
        <?php endforeach; ?>
        <button type="submit" class="button sw-blue-button"><?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?></button>
    </form>
</div>

