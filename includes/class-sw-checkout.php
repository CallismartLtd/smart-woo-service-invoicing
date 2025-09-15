<?php
/**
 * Smart Woo Checkout class file.
 * 
 * @author Callistus
 * @since 2.3.0
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo Checkout class handles all checkout related to a `SmartWoo_Product` and creates invoices.
 * This class does not extend the `WC_Checkout` class but heavily relies on the `woocommerce_checkout_*` and
 * `woocommerce_store_api_*` filters and actions.
 * 
 * @since 2.3.0
 */
class SmartWoo_Checkout {

    /**
     * Hook runner.
     */
    public static function listen() {
        add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'create_order_line_item' ), 10, 4 );
		add_action( 'woocommerce_checkout_order_created', array( __CLASS__, 'maybe_create_invoice' ), 30, 1 );
        add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'maybe_create_invoice' ), 30, 1 );
	
    }

    /**
     * Configure the order with the data the customer provided during product configuration,
     * and save it to order item meta.
     *
     * This function is hooked into 'woocommerce_checkout_create_order_line_item' to add
     * custom meta data related to the configured product to the order item.
     *
     * @param WC_Order_Item_Product $item The order item.
     * @param string $cart_item_key The key of the cart item.
     * @param array $values The session data for the cart item.
	 * @param WC_Order $order The order object.
     */
    public static function create_order_line_item( $item, $cart_item_key, $values, $order ) {
        $product = isset( $values['data'] ) ? $values['data'] : null;
        if ( ! $product || ! is_a( $product, SmartWoo_Product::class ) )  {
            return;
        }
        
        $item->add_meta_data( '_smartwoo_sign_up_fee', $product->get_sign_up_fee(), true );
		$order->add_meta_data( '_smartwoo_is_service_order', true, true );
        
		if ( isset( $values['service_name'] ) ) {
            $item->add_meta_data( '_smartwoo_service_name', $values['service_name'], true );
        }

        if ( isset( $values['service_url'] ) ) {
            $item->add_meta_data( '_smartwoo_service_url', $values['service_url'], true );
        }
    }


	/**
	 * Creates invoices for service subscription orders created via the WooCommerce checkout.
	 * 
	 * @param WC_Order $order
	 */
	public static function maybe_create_invoice( $order ) {
		$configured = smartwoo_check_if_configured( $order );
	
		if ( ! $configured ) {
			return;
		}

		$order_items	= $order->get_items();
		$index			= 0;

		/**
		 * For new service orders, we create invoices for each item in the 
		 * order that is a service product.
		 */
		foreach ( $order_items as $item_id => $item ) {
			// Handles smart woo products only.
			if ( ! $item->get_product() || ! is_a( $item->get_product(), SmartWoo_Product::class )) {
				$index++;
				continue;
			}

			/**
			 * @var SmartWoo_Invoice $invoice
			 */
			$invoice = new SmartWoo_Invoice();

			$invoice->set_invoice_id( smartwoo_generate_invoice_id() );
			$invoice->set_product_id( $item->get_product_id() );
			$invoice->set_amount( $item->get_product()->get_price() );
			$invoice->set_meta( 'product_quantity', $item->get_quantity() );
			$invoice->set_total( $item->get_total() );
			$invoice->set_status( 'unpaid' );
			$invoice->set_date_created( 'now' );
			$invoice->set_user_id( $order->get_user_id() );

            if ( ! $order->get_user() ) { // We are dealing with a guest order.
                $invoice->set_meta( 'is_guest_invoice', true );
            }

			$invoice->set_billing_address( self::format_order_billing_addresses( $order ) );
			$invoice->set_type( 'New Service Invoice' );
			
			$invoice->set_fee( floatval( $item->get_meta( '_smartwoo_sign_up_fee' ) ) );
			$invoice->set_order_id( $order->get_id() );
			$invoice->set_date_due( 'now' );
			$invoice->set_payment_method( $order->get_payment_method() );
			$invoice->set_currency( $order->get_currency() );

			$new_invoice_id = $invoice->save();

			if ( $new_invoice_id ) {
				// The invoice ID for new service orders is saved to the order item meta.
				$item->update_meta_data( '_sw_invoice_id', $invoice->get_invoice_id() );
				$item->save();
			}
		}
		
	}

	/**
	 * Get formatted billing address for a WooCommerce order.
	 *
	 * @param int|WC_Order $order Order ID or WC_Order object.
	 * @return string Formatted billing address.
	 */
	public static function format_order_billing_addresses( $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order );
		}

		if ( ! $order ) {
			return '';
		}

		$billing_address_1   = $order->get_billing_address_1();
		$billing_address_2   = $order->get_billing_address_2();
		$billing_city        = $order->get_billing_city();
		$billing_state       = $order->get_billing_state();
		$billing_country_code = $order->get_billing_country();
		$billing_country_name = WC()->countries->countries[ $billing_country_code ] ?? '';
		$billing_state_name  = $billing_state;
		$states              = WC()->countries->get_states( $billing_country_code );

		if ( ! empty( $states ) && isset( $states[ $billing_state ] ) ) {
			$billing_state_name = $states[ $billing_state ];
		}

		$address_parts = array_filter(
			array(
				$billing_address_1,
				$billing_address_2,
				$billing_city,
				$billing_state_name,
				$billing_country_name,
			)
		);

		return ! empty( $address_parts ) ? implode( ', ', $address_parts ) : '';
	}
}

SmartWoo_Checkout::listen();