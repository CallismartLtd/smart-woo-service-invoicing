<?php
/**
 * The Cart handling Class file
 * 
 * @author Callistus Nwachukwu
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo Cart class handles all cart related functionalities.
 * This class does not extend the `WC_Cart` class but heavily relies on the `woocommerce_cart_*`
 * filters and actions API.
 * 
 * @since 2.3.0
 */
class SmartWoo_Cart {

    /**
     * Hook runner.
     */
    public static function listen() {
        add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'cart_items' ), 10, 3 );
        add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'calculate_sign_up_fee_cart_totals' ) );

        add_action( 'wp_ajax_smartwoo_configure_product', array( __CLASS__, 'configure_and_add_to_cart' ) );
        add_action( 'wp_ajax_nopriv_smartwoo_configure_product', array( __CLASS__, 'configure_and_add_to_cart' ) );
    }

    /**
     * Display configured product data in cart and checkout.
     *
     * This function is hooked into 'woocommerce_get_item_data' to add custom data related to the
     * configured product for display in the cart and checkout.
     *
     * @param array $cart_data The existing cart item data.
     * @param array $cart_item The cart item being displayed.
     * @return array The modified cart item data with added service_name and service_url.
     */
    public static function cart_items( $product_name, $cart_data, $cart_item ) {
        $container = $product_name;

        if( isset( $cart_data['service_name'] ) ) {
            $product    = wc_get_product( $cart_data['product_id'] );

            $container  = '<div class="smartwoo-cart-container">';
            $container .= '<p class="sw-product-name">' . $product_name . '</p>';
            $container .= '<div class="smartwoo-cart-items">';
            $container .= '<h5>' . apply_filters( 'smartwoo_cart_item_header', 'Subscription Items' ). '</h5>';
            $container .= '<p>Service Name: <span>' . esc_html( $cart_data['service_name'] ) . '</span></p>';
        
            $has_product = $product && ( $product instanceof SmartWoo_Product ) && apply_filters( 'smartwoo_show_product_meta', true, $product );
            if ( $has_product ) {
                $container .= '<p>Billing Cycle: <span>' . esc_html( $product->get_billing_cycle() ) . '</span></p>';
            }

            if( ! empty( $cart_data['service_url'] ) ) {
                $container .= '<p>Service URL: <span>' . esc_html( $cart_data['service_url'] ) . '</span></p>';
            }
            
            if ( $has_product ) {
                $container .= '<p>Sign-Up Fee: <span>' . esc_html( smartwoo_price( $product->get_sign_up_fee() ) ) . '</span></p>';
            }


            $container .= '</div>';
            $container .= '</div>';
        }

        return $container;
    }

	/**
	 * Calculate the sum of sign-up fee and product price and set as cart subtotal
	 *
	 * @param object $cart the woocommerce cart object
	 * @return null stops execution when cart is empty
	 */
	public static function calculate_sign_up_fee_cart_totals( $cart ) {		
		if ( $cart->is_empty() ) {
			return;
		}
	
		$total_sign_up_fee = 0;
		$add_fee = false;
	
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];

			if ( $product && 'sw_product' === $product->get_type() ) {
				$quantity		= $cart_item['quantity'];
				$sign_up_fee	= (float) $product->get_sign_up_fee() * $quantity;
				$total_sign_up_fee += $sign_up_fee;
				$add_fee		= true;
			}
			
		}
	
		if ( $add_fee ) {
			$cart->add_fee( 'Sign-up Fee', $total_sign_up_fee );
		}
	}

    /**
     * Ajax product configuration and add to cart callback.
     */
    public static function configure_and_add_to_cart() {
        // Verify the nonce.
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => self::is_fast_checkout() ? '<p>Basic authentication failed, please refresh current page.</p>' : smartwoo_notice( 'Basic authentication failed, please refresh current page.' ) ) );
        }

        $validation_errors = array();
        $service_name   = isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
        if ( empty( $service_name ) ) {
            $validation_errors[] = 'Service Name is required to configure your subscription.';
        }

        $service_url    =	isset( $_POST['service_url'] ) ? sanitize_url(  wp_unslash( $_POST['service_url'] ), array( 'http', 'https' ) ) : '';
        if ( ! empty( $_POST['service_url'] ) && empty( $service_url ) ) {
            $validation_errors[] = 'Enter a valid website URL.';
        }

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

        if ( empty( $product_id ) ) {
            $validation_errors[] = 'Product ID could not be found.';

        }

        if ( ! empty( $validation_errors ) ) {
            wp_send_json_error( array( 'message' => self::is_fast_checkout() ? implode( '<br>', $validation_errors ) : smartwoo_error_notice( $validation_errors ) ) );

        }

        $cart_item_data = array(
            'service_name' => $service_name,
            'service_url'  => $service_url,
        );

        $cart = WC()->cart ? WC()->cart : new WC_Cart();
        $cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data );
        wp_send_json_success( array( 'checkout' => wc_get_checkout_url() ) );
    }

    /**
     * Check whether fast checkout is enabled
     * 
     * @return boolean
     */
    public static function is_fast_checkout() {
        return get_option( 'smartwoo_allow_fast_checkout', false );
    }
}

SmartWoo_Cart::listen();