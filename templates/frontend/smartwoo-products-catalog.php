<?php
/**
 * Smart Woo Product catalog page template
 * 
 * @author Callistus
 * @since 2.4.0
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="smartwoo-page">
    <?php echo wp_kses_post( smartwoo_get_navbar( 'Buy New Service', get_permalink( wc_get_page_id( 'shop' ) ) ) ); ?>
    <?php if ( empty( $smartwoo_products ) ) : ?>
        <div class="main-page-card">
            <p><?php esc_html_e( 'No subscripton product found, please contact us if you help.', 'smart-woo-service-invoicing' ); ?></p>
            <a href="<?php echo esc_url( $shop_page_url ); ?>" class="sw-blue-button"><?php echo esc_html__( 'Shop Page', 'smart-woo-service-invoicing' ); ?></a>
            <a href="<?php echo esc_attr( get_permalink() ); ?>" class="sw-blue-button"><?php echo esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ); ?></a>
        </div>
    <?php else: ?>
        <div class="sw-products-container">
            <?php foreach( $smartwoo_products as $product ) : $GLOBALS['product'] = $product; ?>
                <div class="sw-product-container">
                    <img src="<?php echo esc_url( $product->get_image_id() ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?> image">
                    <h3><?php echo esc_html( $product->get_name() ); ?></h3>
                	<p>Price: <?php echo esc_html( smartwoo_price( $product->get_regular_price() ) ); ?></p>
                    <p>Sign-Up Fee: <?php echo esc_html( smartwoo_price( $product->get_sign_up_fee() ) ); ?></p>
                    <p>Billed: <strong><?php echo esc_html( $product->get_billing_cycle() ); ?></strong></p>
                    <div class="sw-description">
                        <?php echo wp_kses_post( $product->get_short_description() ); ?>
                    </div>
                    <a href="<?php echo esc_url( smartwoo_configure_page( $product->get_id() ) ); ?>" class="sw-blue-button product_type_<?php echo esc_attr( $product->get_type() ); ?> add_to_cart_button" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_name="<?php echo esc_attr( $product->get_name() ); ?>"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
                    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="sw-blue-button" ><?php echo esc_html__( 'View', 'smart-woo-service-invoicing' ); ?></a>
                
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



