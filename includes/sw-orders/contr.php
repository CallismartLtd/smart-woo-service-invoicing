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
            case 'process-new-service':
                echo wp_kses( smartwoo_process_new_service_order_page(), smartwoo_allowed_form_html() );
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
        include_once SMARTWOO_PATH . 'templates/order-admin/dashboard.php';
    }

}