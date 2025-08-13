<?php
/**
 * Edit Payment Method Form (Frontend)
 *
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit;
?>
<form id="smartwoo-payment-edit-form" class="smartwoo-form" method="post">
    <h3 class="smartwoo-section-card__title">
        <?php echo esc_html( $form_title ); ?>
    </h3>

    <div class="smartwoo-payment-methods-grid">
        <?php if ( ! empty( $gateways ) ) : ?>
            <div class="smartwoo-payment-methods">
                <?php foreach ( $gateways as $id => $gateway ) : ?>
                    <div class="smartwoo-payment-method">
                        <label for="payment-method-<?php echo esc_attr( $id ); ?>" class="smartwoo-payment-method__label">
                            <input type="radio"
                                name="payment_method"
                                id="payment-method-<?php echo esc_attr( $id ); ?>"
                                value="<?php echo esc_attr( $id ); ?>"
                                <?php checked( $id, $user_option ); ?>>
                            <span class="smartwoo-payment-method__title">
                                <?php echo esc_html( $gateway->get_title() ); ?>
                            </span>
                            <span class="smartwoo-payment-method__icon">
                                <?php echo wp_kses_post( $gateway->get_icon() ); ?>
                            </span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="action" value="smartwoo_save_payment_method">
            <input type="hidden" name="payment_option_type" value="<?php echo esc_attr( $type ) ?>">
            <button type="submit" class="button sw-blue-button">
                <?php esc_html_e( 'Save', 'smart-woo-service-invoicing' ); ?>
            </button>
        <?php else : ?>
            <p class="smartwoo-payment-methods__empty">
                <?php esc_html_e( 'No available payment methods found.', 'smart-woo-service-invoicing' ); ?>
            </p>
        <?php endif; ?>
    </div>
</form>
