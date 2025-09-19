<?php
/**
 * Smart Woo REST API admin dashboard class file.
 * 
 * @author Callistus Nwachukwu
 * @since 2.5
 * @package SmartWooRESTAPI
 */

namespace SmartWoo_REST_API;
use \WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Admin dashboard rest endpoint handler.
 */
class AdminDashboard {
    /**
     * Permission callback
     * 
     * @param WP_REST_Request $request
     */
    public static function can_view_admin( $request ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'smartwoo_rest_error', __( 'Only admins can access this endpoint: is logged in ' . wp_json_encode( is_user_logged_in() ), 'smart-woo-service-invoicing' ),array( 'status' => 401 ) );
        }

        return true;

    }

    /**
     * Respond to admin dashboard request
     * 
     * @param WP_REST_Request $request
     */
    public static function dispatch( $request ) {

    }
}