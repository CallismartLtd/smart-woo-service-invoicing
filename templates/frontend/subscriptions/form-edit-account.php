<?php
/**
 * User account details edit form
 *
 * @package SmartWoo\templates
 */
defined( 'ABSPATH' ) || exit;
?>
<form id="smartwoo-account-form" class="smartwoo-form" method="post">
    <h3 class="smartwoo-section-card__title">
        <?php echo esc_html__( 'Edit My Details', 'smart-woo-service-invoicing' ); ?>
    </h3>

    <?php foreach ( $account_fields as $key => $field ) : ?>
        <?php woocommerce_form_field( $key, $field, $field['value'] ?? '' ); ?>
    <?php endforeach; ?>

    <h3 class="smartwoo-section-card__title">
        <?php echo esc_html__( 'Change Password', 'smart-woo-service-invoicing' ); ?>
    </h3>

    <?php
    woocommerce_form_field(
        'password_current',
        array(
            'type'        => 'password',
            'label'       => __( 'Current password (leave blank to keep unchanged)', 'smart-woo-service-invoicing' ),
            'required'    => false,
            'class'       => array( 'form-row-wide' ),
            'autocomplete'=> 'current-password',
        )
    );

    woocommerce_form_field(
        'password_1',
        array(
            'type'        => 'password',
            'label'       => __( 'New password (leave blank to keep unchanged)', 'smart-woo-service-invoicing' ),
            'required'    => false,
            'class'       => array( 'form-row-first' ),
            'autocomplete'=> 'new-password',
        )
    );

    woocommerce_form_field(
        'password_2',
        array(
            'type'        => 'password',
            'label'       => __( 'Confirm new password', 'smart-woo-service-invoicing' ),
            'required'    => false,
            'class'       => array( 'form-row-last' ),
            'autocomplete'=> 'new-password',
        )
    );
    ?>

    <button type="submit" class="button sw-blue-button">
        <?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?>
    </button>
</form>
