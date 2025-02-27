<?php
/**
 * The Smart Woo Order Controller file
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo Order controller class handles all admin functionalities for
 * a Smart Woo Order management.
 */
class SmartWoo_Order_Controller {

    /**
     * Admin Service Order menu contrlloer
     */
    public static function menu_controller() {
        $section = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : '';
        switch( $section ) {
            case 'process-order':
                self::process_order_form();
                break;
            default :
            self::dashboard();
        }
    }

    /**
     * The Dashboard page
     */
    private static function dashboard() {
        $orders = SmartWoo_Order::get_all();
        $process_btn    = ( function( $status, $order_id ) {
            if ( 'awaiting processing' === $status ) {
                return '<a href="' . esc_url( admin_url( 'admin.php?page=sw-service-orders&section=process-order&order_id=' . $order_id ) ) . '"><button class="sw-icon-button-admin" title="' .__( 'Process Now', 'smart-woo-service-invoicing' ). '"><span class="dashicons dashicons-ellipsis"></span></button></a>';
            }elseif( 'processed' === $status ) {
                return '<button class="sw-icon-button-admin sw-not-allowed" title="' .__( 'Order has been processed', 'smart-woo-service-invoicing' ). '"><span class="dashicons dashicons-cloud-saved"></span></button>';
            } else {
                return 'Cannot be processed';
            }
        });
        include_once SMARTWOO_PATH . 'templates/order-admin/dashboard.php';
    }

    /**
     * The order processing form
     */
    private static function process_order_form() {
        smartwoo_set_document_title( 'Process Orders' );
        $order_id   = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $order      = SmartWoo_Order::get_order( $order_id );

        if ( $order ) {
            $product            = $order->get_product();
            $GLOBALS['product']	= $product;

            $is_downloadable	= $product->is_downloadable();
            $product_name       = $product ? $product->get_name() : 'N/A';
            $service_name       = $order->get_service_name();
            $product_id         = $product ? $product->get_id() : '';
            $service_url        = $order->get_service_url();
            $user_full_name     = $order->get_user() ? $order->get_user()->get_billing_first_name() . ' ' . $order->get_user()->get_billing_last_name() : '';
            $user_id            = $order->get_user() ? $order->get_user()->get_id() : 0;
            $start_date         = $order->get_date_paid( 'date_format' );
            $billing_cycle     	= $order->get_billing_cycle();
            $next_payment_date 	= '';
            $end_date          	= '';
            $status            	= '';
        
            // Set next payment date and end date based on billing cycle.
            switch ( $billing_cycle ) {
                case 'Monthly':
                    $end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 month' ) );
                    $next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
                    break;

                case 'Quarterly':
                    $end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +3 months' ) );
                    $next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
                    break;

                case 'Six Monthly':
                    $end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +6 months' ) );
                    $next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
                    break;

                case 'Yearly':
                    $end_date          = date_i18n( 'Y-m-d', strtotime( $start_date . ' +1 year' ) );
                    $next_payment_date = date_i18n( 'Y-m-d', strtotime( $end_date . ' -7 days' ) );
                    break;
                default:
                    break;
            }

            $status_options 	= array(
                ''					=> esc_html__( 'Auto Calculate', 'smart-woo-service-invoicing' ),
                'Pending'			=> esc_html__( 'Pending', 'smart-woo-service-invoicing' ),
                'Active (NR)'		=> esc_html__( 'Active (NR)', 'smart-woo-service-invoicing' ),
                'Suspended'			=> esc_html__( 'Suspended', 'smart-woo-service-invoicing' ),
                'Due for Renewal'	=> esc_html__( 'Due for Renewal', 'smart-woo-service-invoicing' ),
                'Expired'			=> esc_html__( 'Expired', 'smart-woo-service-invoicing' ),
            );
        }
        
        include_once SMARTWOO_PATH . 'templates/order-admin/new-service-order-form.php';
    }
}