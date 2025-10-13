<?php
/**
 * Admin Support Page Template.
 *
 * @author  Callistus Nwachukwu
 * @package SmartWoo\Support
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-admin-page-content">
    <em class="description"><?php esc_html_e( 'Choose a support package that fits your needs.', 'smart-woo-service-invoicing' ); ?></em>   

    <?php if ( empty( $support_packages ) || is_wp_error( $support_packages ) ) : ?>
        <?php echo wp_kses_post( smartwoo_notice( __( 'No support packages are available at the moment. Please check back later.', 'smart-woo-service-invoicing' ) ) ); ?>
    
    <?php else : ?>

        <div class="smartwoo-support-layout">
            <div class="smartwoo-support-list">
                <?php foreach ( $support_packages as $index => $product ) : ?>
                    <label class="smartwoo-support-item" data-product="<?php echo esc_attr( smartwoo_json_encode_attr( $product ) ) ?>">
                        <input type="radio" name="smartwoo_support_choice" id="<?php echo absint( $index ); ?>" value="<?php echo esc_attr( $product['id'] ); ?>" <?php checked( 0 === $index ); ?> />
                        <div class="smartwoo-support-content">
                            <h3 class="smartwoo-support-name"><?php echo esc_html( $product['name'] ); ?></h3>
                            <p class="smartwoo-support-price">
                                <?php echo wp_kses_post( smartwoo_price( $product['price'], array( 'currency' => $product['currency'] ) ) ); ?>
                            </p>
                            <p class="smartwoo-support-short"><?php echo wp_kses_post( $product['short_description'] ); ?></p>
                            <hr>
                            <p class="smartwoo-support-desc"><?php echo wp_kses_post( $product['description'] ); ?></p>

                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="smartwoo-support-summary">
                <div class="smartwoo-support-summary-inner">
                    <h2><?php esc_html_e( 'Summary', 'smart-woo-service-invoicing' ); ?></h2>
                    <div class="smartwoo-support-summary-data">
                        <table class="widefat striped">
                            <tr>
                                <th><?php esc_html_e( 'Product', 'smart-woo-service-invoicing' ); ?></th>
                                <td >
                                    <div class="smartwoo-support-summary_product-data">
                                        <h3 id="smartwoo-support-title"><?php echo esc_html( $support_packages[0]['name'] ); ?></h3>
                                        <div id="smartwoo-support-short"><?php echo wp_kses_post( $support_packages[0]['short_description'] ); ?></div>
                                    </div>
                                    
                                </td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Pricing', 'smart-woo-service-invoicing' ); ?></th>
                                <td>
                                    <p id="smartwoo-support-price">
                                        <?php echo wp_kses_post( smartwoo_price( $support_packages[0]['price'], array( 'currency' => $support_packages[0]['currency'] ) ) ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                
                    <button data-url="<?php echo esc_url( $support_packages[0]['checkout_url'] ); ?>" target="_blank" id="smartwoo-support-checkout-btn" class="button">
                        <?php esc_html_e( 'Next', 'smart-woo-service-invoicing' ); ?>
                </button>
                </div>
            </div>
        </div>

        <div class="smartwoo-modal-frame" data-section="modal">
            <div class="smartwoo-modal-content">
                <button class="smartwoo-modal-close-btn dashicons dashicons-dismiss" title="<?php esc_html_e( 'Close', 'smart-woo-service-invoicing' ); ?>"></button>
                <div class="smartwoo-modal-heading">
                    <h2><?php esc_html_e( 'Support Checkout', 'smart-woo-service-invoicing' ); ?></h2>
                </div>
                <div class="smartwoo-modal-body"></div>
            </div>

        </div>
    <?php endif; ?>
</div>