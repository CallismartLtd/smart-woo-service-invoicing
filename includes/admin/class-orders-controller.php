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
class SmartWoo_Orders_Controller {

    /**
     * Admin Service Order page contrlloer
     */
    public static function menu_controller() {
        $section = smartwoo_get_query_param( 'section' );

        if ( 'process-order' === $section ) {
            $title = 'Process Order';
            $wc_order = false;

            try {
                $wc_order = wc_get_order( wc_get_order_id_by_order_item_id( smartwoo_get_query_param( 'order_id' ) ) );
            } catch ( \Exception $e ) {}
            $menu_tabs = array(
                'Orders'	=> array(
                    'href'		=> admin_url( 'admin.php?page=sw-service-orders' ),
                    'active'	=> ''
                ),
                'Process Order'	=> array(
                    'href'		=> admin_url( 'admin.php?page=sw-service-orders&section=process-order&order_id=' . smartwoo_get_query_param( 'order_id' ) ),
                    'active'	=> 'process-order'
                ),
            );

            if ( $wc_order ) {
                $menu_tabs['Edit parent order'] = array(
                    'href'      => $wc_order->get_edit_order_url(),
                    'active'    => ''
                );
            }

            SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $menu_tabs, 'section' );

        } else {
            SmartWoo_Admin_Menu::print_mordern_submenu_nav( 'Service Subscription Orders', array() );
        }
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
        $paged      = smartwoo_get_query_param( 'paged', 1 );
        $limit      = smartwoo_get_query_param( 'limit', 20 );
        $all_orders = SmartWoo_Order::count_all();
        $total      = ceil( $all_orders / $limit );
        $prev       = $paged > 1 ? $paged - 1 : 1;
        $next       = $paged < $total ? $paged + 1 : $total;
        $orders     = SmartWoo_Order::get_all( $paged, $limit );
        $btn_text   = ( function( $status, $order_id ) {
            if ( 'awaiting processing' === $status ) {
                return '<a href="' . esc_url( admin_url( 'admin.php?page=sw-service-orders&section=process-order&order_id=' . $order_id ) ) . '"><button class="sw-icon-button-admin" title="' .__( 'Process Now', 'smart-woo-service-invoicing' ). '"><span class="dashicons dashicons-ellipsis"></span></button></a>';
            }elseif( 'processed' === $status ) {
                return '<button class="sw-icon-button-admin sw-not-allowed" title="' . __( 'Order has been processed', 'smart-woo-service-invoicing' ) . '"><span style="color:#f0a607" class="dashicons dashicons-cloud-saved"></span></button>';
            }elseif( 'awaiting payment' === $status ) {
                return '<button class="sw-icon-button-admin sw-not-allowed" title="' . __( 'The order has been received, but no payment has been made', 'smart-woo-service-invoicing' ) . '"><span style="color: #2217ee" class="dashicons dashicons-info"></span></button>';
            } else {
                return '<button class="sw-icon-button-admin sw-not-allowed" title="' . __( 'Cannot be processed', 'smart-woo-service-invoicing' ) . '"><span style="color: #2217ee" class="dashicons dashicons-info"></span></button>';
            }
        });
        include_once SMARTWOO_PATH . 'templates/admin/service-orders/dashboard.php';
    }

    /**
     * The order processing form page
     */
    private static function process_order_form() {
        smartwoo_set_document_title( 'Process Orders' );
        $order_id   = smartwoo_get_query_param( 'order_id' );
        $order      = SmartWoo_Order::get_order( $order_id );

        if ( $order ) {
            $product            = $order->get_product();
            $GLOBALS['product']	= $product;

            $has_asset          = $product->is_downloadable();
            $product_name       = $product ? $product->get_name() : 'N/A';
            $service_name       = $order->get_service_name();
            $product_id         = $product ? $product->get_id() : '';
            $service_url        = $order->get_service_url();
            $start_date         = $order->get_date_paid()->format( 'Y-m-d' );
            $billing_cycle     	= $order->get_billing_cycle();
            $end_date          	= date_i18n( 'Y-m-d', strtotime( $start_date . ' ' . SmartWoo_Date_Helper::get_billing_cycle_interval( $billing_cycle ) ) );
            $next_payment_date 	= date_i18n( 'Y-m-d', strtotime( $end_date . ' ' . smartwoo_get_global_nextpay() ) );
            $status            	= '';
            $user               = $order->get_user();
            $user_full_name     = $user ? $user->get_billing_first_name() . ' ' . $user->get_billing_last_name() : 'N/A';
            $downloadables      = $product->get_smartwoo_downloads();

            $status_options 	= array(
                ''					=> esc_html__( 'Auto Calculate', 'smart-woo-service-invoicing' ),
                'Pending'			=> esc_html__( 'Pending', 'smart-woo-service-invoicing' ),
                'Active (NR)'		=> esc_html__( 'Active (NR)', 'smart-woo-service-invoicing' ),
                'Suspended'			=> esc_html__( 'Suspended', 'smart-woo-service-invoicing' ),
                'Due for Renewal'	=> esc_html__( 'Due for Renewal', 'smart-woo-service-invoicing' ),
                'Expired'			=> esc_html__( 'Expired', 'smart-woo-service-invoicing' ),
            );
        }
        
        include_once SMARTWOO_PATH . 'templates/admin/service-orders/process-order-form.php';
    }
}