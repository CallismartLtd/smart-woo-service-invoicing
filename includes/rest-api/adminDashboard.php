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
use \WP_REST_Request;
use \WP_REST_Response;
use \SmartWoo_Service_Database;
use \SmartWoo_Invoice_Database;
use \SmartWoo_Service;

defined( 'ABSPATH' ) || exit;

/**
 * Admin dashboard rest endpoint handler.
 */
class AdminDashboard {
    /**
     * The current request handler callback
     * 
     * @var callable|\WP_Error $callback_handler
     */
    private static $callback_handler;

    /**
     * Permission callback for admin dashboard routes.
     *
     * Performs two checks before allowing dispatch:
     *  1. User capability (`manage_options`)
     *  2. Request handler resolution via set_handler()
     *
     * Returns true on success or a WP_Error if not permitted.
     *
     * @param WP_REST_Request $request The REST request.
     * @return true|WP_Error True if authorized, WP_Error otherwise.
     */
    public static function authorize_request( WP_REST_Request $request ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error(
                'smartwoo_rest_error',
                __( 'Only admins can access this endpoint', 'smart-woo-service-invoicing' ),
                array( 'status' => 401 )
            );
        }

        self::$callback_handler = self::set_handler( $request );

        if ( is_wp_error( self::$callback_handler ) ) {
            return self::$callback_handler;
        }

        return true;
    }

    /**
     * Respond to admin dashboard request
     * 
     * @param WP_REST_Request $request
     */
    public static function dispatch( WP_REST_Request $request ) {
        
        $limit  = $request->get_param( 'limit' );
        $page   = $request->get_param( 'page' );

        $results        = call_user_func( self::$callback_handler, $page, $limit );
        $pagination     = self::response_pagination( $request );
        $section        = $request->get_param( 'section' );
        $current_filter = $request->get_param( 'filter' );

        $response_data  = self::prepare_section_response( $section, $results );
        
        
        $response_data['current_filter']    = $current_filter;
        $response_data['title']    = self::get_response_title( $current_filter );
        $response_data['pagination']        = $pagination;
        
        $response   = new WP_REST_Response( $response_data, 200 );

        return $response;
    }

    /**
     * Parse and set the request handler.
     *
     * @param WP_REST_Request $request The REST request object.
     * @return callable|\WP_Error Callback handler or WP_Error on failure.
     */
    private static function set_handler( WP_REST_Request $request ) {
        $filter = $request->get_param( 'filter' );

        switch ( $filter ) {
            case 'allServices':
                return array( SmartWoo_Service_Database::class, 'get_all' );

            case 'allActiveServices':
                return array( SmartWoo_Service_Database::class, 'get_all_active' );

            case 'allActiveNRServices':
                return function( $page, $limit ) {
                    return SmartWoo_Service_Database::get_( ['status' => 'Active (NR)', 'page' => $page, 'limit' => $limit] );
                };

            case 'allCancelledServices':
                return function( $page, $limit ) {
                    return SmartWoo_Service_Database::get_( ['status' => 'Cancelled', 'page' => $page, 'limit' => $limit] );
                };

            case 'allSuspendedServices':
                return function( $page, $limit ) {
                    return SmartWoo_Service_Database::get_( ['status' => 'Suspended', 'page' => $page, 'limit' => $limit] );
                };

            case 'allExpiredServices':
                return array( SmartWoo_Service_Database::class, 'get_all_expired' );

            case 'allDueServices':
                return array( SmartWoo_Service_Database::class, 'get_all_due' );

            case 'allGracePeriodServices':
                return array( SmartWoo_Service_Database::class, 'get_all_on_grace' );

            default:
                return new WP_Error(
                    'smartwoo_rest_no_handler',
                    __( 'Invalid request handler', 'smart-woo-service-invoicing' )
                );
        }
    }

    /**
     * Get the pagination callback
     * 
     * @param WP_REST_Request $request  REST API Request object.
     * @return array                    The pagination array
     */
    private static function response_pagination( WP_REST_Request $request ) {

        $count_callback_map = array(
            'allServices'            => 'SmartWoo_Service_Database::get_total_records',
            'allActiveServices'      => 'SmartWoo_Service_Database::count_active',
            'allActiveNRServices'    => function () {
                return SmartWoo_Service_Database::count_by_status( 'Active (NR)' );
            },
            'allExpiredServices'     => 'SmartWoo_Service_Database::count_expired',
            'allCancelledServices'   => function () {
                return SmartWoo_Service_Database::count_by_status( 'Cancelled' );
            },
            'allSuspendedServices'   => function () {
                return SmartWoo_Service_Database::count_by_status( 'Suspended' );
            },
            'allUnPaidInvoice'       => '',
            'allNewOrders'           => '',
            'allDueServices'         => 'SmartWoo_Service_Database::count_due',
            'allGracePeriodServices' => 'SmartWoo_Service_Database::count_on_grace',
        );

        $filter       = $request->get_param( 'filter' );
        $current_page = max( 1, intval( $request->get_param( 'page' ) ) );
        $limit        = max( 1, intval( $request->get_param( 'limit' ) ) );

        $callback     = $count_callback_map[ $filter ] ?? null;

        if ( is_callable( $callback ) ) {
            $total_items = call_user_func( $callback );
        } else {
            $total_items = 0;
        }

        $total_pages = max( 1, ceil( $total_items / $limit ) );

        return array(
            'current_page'  => $current_page,
            'limit'         => $limit,
            'total_items'   => $total_items,
            'total_pages'   => $total_pages,
            'prev_page'     => ( $current_page > 1 ) ? $current_page - 1 : null,
            'next_page'     => ( $current_page < $total_pages ) ? $current_page + 1 : null,
        );
    }

    /**
     * Get the request response title.
     * 
     * @param string $filter
     * @return string
     */
    private static function get_response_title( string $filter ) {
        $titles = array(
            'allServices'            => __( 'All Subscriptions', 'smart-woo-service-invoicing' ),
            'allActiveServices'      => __( 'All Active Services', 'smart-woo-service-invoicing' ),
            'allActiveNRServices'    => __( 'All Active Non-Renewing Services', 'smart-woo-service-invoicing' ),
            'allExpiredServices'     => __( 'All Expired Services', 'smart-woo-service-invoicing' ),
            'allCancelledServices'   => __( 'All Cancelled Services', 'smart-woo-service-invoicing' ),
            'allSuspendedServices'   => __( 'All Suspended Services', 'smart-woo-service-invoicing' ),
            'allUnPaidInvoice'       => __( 'All Unpaid Invoices', 'smart-woo-service-invoicing' ),
            'allNewOrders'           => __( 'All New Orders', 'smart-woo-service-invoicing' ),
            'allDueServices'         => __( 'All Due Services', 'smart-woo-service-invoicing' ),
            'allGracePeriodServices' => __( 'All Services in Grace Period', 'smart-woo-service-invoicing' ),
        );

        return $titles[$filter] ?? '';

    }


    /**
     * Check if an array is a collection of a specific object type.
     *
     * Iterates through all array items and validates that each
     * is an instance of the provided class/interface.
     *
     * @since 2.5
     *
     * @param array  $items Array of items to validate.
     * @param string $class Fully qualified class or interface name.
     * @return bool True if all items are instances of $class, false otherwise.
     */
    protected static function is_collection_of( array $items, $class ) {

        if ( empty( $items ) ) {
            return false;
        }

        foreach ( $items as $item ) {
            if ( ! $item instanceof $class ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Prepares the response data for the given dashboard section
     * 
     * @param string $section The dashboard section.
     * @param SmartWoo_Service[]|SmartWoo_Invoice[]|object[] $results   The result to prepare.
     * @return array
     */
    private static function prepare_section_response( $section, $results ) {
        $method = "prepare_{$section}_data";

        if ( method_exists( __CLASS__, $method ) ) {
            return self::$method( $results );
        }

        return array();
    }

    /**
     * Prepare response data for `subscriptionList` section of the dashbaord.
     * 
     * @param \SmartWoo_Service[] $results The result to prepare.
     * @return string[] Array of formatted html string for the Subscription List section.
     */
    private static function prepare_subscriptionList_data( $services ) {
        $table_rows = [];

        if ( ! self::is_collection_of( $services, SmartWoo_Service::class ) ) {
            $table_rows[] = sprintf( '<tr><td class="sw-not-found" colspan="5">%s</td></tr>',
                esc_html__( 'No service subscriptions found.', 'smart-woo-service-invoicing' )
            );

            return [ 'table_rows' => $table_rows];
        }

        foreach ( $services as $service ) {
            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td><input type="checkbox" id="%3$d"></td>
                    <td>%3$d</td>
                    <td>%4$s</td>
                    <td>%5$s</td>
                    <td>%6$s</td>
                                            
                </tr>',
                esc_url( $service->preview_url() ),
                esc_html__( 'View', 'smart-woo-service-invoicing' ),
                absint( $service->get_id() ),
                esc_html( $service->get_name() ),
                esc_html( $service->get_service_id() ),
                self::capture_output( 'smartwoo_print_service_status', $service, ['dashboard-status'] )

            );
        }

        return [ 'table_rows' => $table_rows];
    }

    /**
     * Helper function to capture and return the output of a given callback
     * 
     * @param callable $callback Function name or [class, method] or closure.
     * @param mixed    ...$args  Arguments to pass to the function.
     * @return string The captured output.
    */
    private static function capture_output( callable $callback, ...$args ) {
        ob_start();
        $result = $callback( ...$args );
        $output = ob_get_clean();

        return $output !== '' ? $output : (string) $result;
    }
}