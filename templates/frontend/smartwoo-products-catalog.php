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
    <div class="sw-products-container">
        <?php if ( empty( $smartwoo_products ) ) : ?>
            <div class="main-page-card">
                <?php
                    $notice = __( 'No subscripton product found, please contact us if you help.', 'smart-woo-service-invoicing' );
                    echo wp_kses_post( smartwoo_notice( $notice ) )
                    
                ?>
                <a href="<?php echo esc_url( $shop_page_url ); ?>" class="sw-blue-button"><?php echo esc_html__( 'Shop Page', 'smart-woo-service-invoicing' ); ?></a>
                <a href="<?php echo esc_attr( get_permalink() ); ?>" class="sw-blue-button"><?php echo esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ); ?></a>
            </div>
        <?php else: ?>
            <?php foreach( $smartwoo_products as $product ) : $GLOBALS['product'] = $product; ?>
                <div class="sw-product-container">
                    <img src="<?php echo esc_url( $product->get_image_id() ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src() ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?> image">
                    <h3><?php echo esc_html( $product->get_name() ); ?></h3>
                    <p>Price: <?php echo wp_kses_post( $product->get_price_html() ); ?></p>
                    <p>Sign-Up Fee: <?php echo esc_html( smartwoo_price( $product->get_sign_up_fee() ) ); ?></p>
                    <p>Billed: <strong><?php echo esc_html( $product->get_billing_cycle() ); ?></strong></p>
                    <div class="sw-description">
                        <?php echo wp_kses_post( $product->get_short_description() ); ?>
                    </div>
                    <div class="sw-button-container">
                        <?php $product->get_add_to_cart_button(); ?>
                        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="sw-blue-button" ><?php echo esc_html__( 'View', 'smart-woo-service-invoicing' ); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
            
        <?php endif; ?>
    </div>
</div>



