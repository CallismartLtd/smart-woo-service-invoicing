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
use \SmartWoo_Invoice;
use \SmartWoo_Order;
use \SmartWoo_Dashboard_Controller;

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

        if ( ! class_exists( SmartWoo_Dashboard_Controller::class, false ) ) {
            require_once SMARTWOO_PATH . 'includes/admin/class-dashboard-controller.php';
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

        $results        = call_user_func( self::$callback_handler, $page, $limit, $request );
        $pagination     = self::response_pagination( $request );
        $section        = $request->get_param( 'section' );
        $current_filter = $request->get_param( 'filter' );

        $response_data  = self::prepare_section_response( $section, $results );
        
        
        $response_data['current_filter']    = $current_filter;
        $response_data['title']             = self::get_response_title( $current_filter );
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
        /**
         * Prempt a REST API handler for the admin dashboard.
         * 
         * @param callable|null $handler The callback function that handles the request.
         * @param WP_REST_Request $request The REST API request object.
         * @param string The fully qualified class name of the AdminDashboard REST class.
         */
        $handler = apply_filters( 'smartwoo_pre_admin_rest_handler', null, $request, __CLASS__ );
        if ( is_callable( $handler ) ) {
            return $handler;
        }

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

            case 'subscribersList':
                return array( SmartWoo_Service_Database::class, 'get_active_subscribers' );
            case 'allOnExpiryThreshold':
                return array( SmartWoo_Service_Database::class, 'get_on_expiry_threshold' );
            
            case 'allUnPaidInvoice':
                return function( $page, $limit ) {
                    return SmartWoo_Invoice_Database::get_invoices_by_payment_status( 'unpaid', ['page' => $page, 'limit' => $limit] );
                };

            case 'allNewOrders':
                return array( SmartWoo_Order::class, 'get_awaiting_processing_orders' );

            case 'markInvoicePaid':
            case 'sendPaymentReminder':
                return array( __CLASS__, 'handle_invoice_actions' );
            case 'bulkActions':
                return array( __CLASS__, 'handle_bulk_actions' );
            case 'search':
                return function() use( $request ) {
                    return self::perform_search( $request );
                };
            default:

                /**
                 * Filters admin REST API handler to allow plugins handle additional filter params.
                 * 
                 * @param callable|null $handler The callback function that handles the request.
                 * @param WP_REST_Request $request The REST API request object.
                 * @param string The fully qualified class name of the AdminDashboard REST class.
                 */
                $handler = apply_filters( 'smartwoo_admin_rest_handler', null, $request, __CLASS__ );

                if ( is_callable( $handler ) ) {
                    return $handler;
                }

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
            'allServices'               => 'SmartWoo_Service_Database::get_total_records',
            'allActiveServices'         => 'SmartWoo_Service_Database::count_active',
            'allActiveNRServices'       => function () {
                return SmartWoo_Service_Database::count_by_status( 'Active (NR)' );
            },
            'allExpiredServices'        => 'SmartWoo_Service_Database::count_expired',
            'allCancelledServices'      => function () {
                return SmartWoo_Service_Database::count_by_status( 'Cancelled' );
            },
            'allSuspendedServices'      => function () {
                return SmartWoo_Service_Database::count_by_status( 'Suspended' );
            },
            'allUnPaidInvoice'          => function(){
                return SmartWoo_Invoice_Database::count_this_status( 'unpaid' );
            },
            'allNewOrders'              => 'smartwoo_count_unprocessed_orders',
            'allDueServices'            => 'SmartWoo_Service_Database::count_due',
            'subscribersList'           => 'SmartWoo_Service_Database::get_total_active_subscribers',
            'allOnExpiryThreshold'      => 'SmartWoo_Service_Database::count_on_expiry_threshold',
        );

        $filter         = $request->get_param( 'filter' );
        $current_page   = max( 1, intval( $request->get_param( 'page' ) ) );
        $limit          = max( 1, intval( $request->get_param( 'limit' ) ) );

        $callback       = $count_callback_map[ $filter ] ?? null;

        /**
         * Filters the dashboard REST API pagination callback to get total count for a given request filter.
         * 
         * @param callable|null $callback The pagination callback function.
         * @param string $filter The REST API request filter.
         * @param WP_REST_Request $request The REST API request object.
         */
        $callback = apply_filters( 'smartwoo_AdminDashboard_response_pagination_total', $callback, $filter, $request );

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
            'allOnExpiryThreshold'   => __( 'All Expiring Soon', 'smart-woo-service-invoicing' ),
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

        /**
         * Add a callback function that prepares response data for a given admin dashboard section.
         * 
         * @param string $section The dynamic variable in this hookname refers to the section param of
         *                        the REST request.
         * @param string Fully qualified name of the admin REST API class.
         */
        $response_handler = apply_filters( "smartwoo_AdminDashboard_prepare_{$section}_data", null, $results, __CLASS__ );

        if ( is_callable( $response_handler ) ) {
            return call_user_func( $response_handler, $results );
        }

        return self::message_response( $results );
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
                __( 'No service subscriptions found.', 'smart-woo-service-invoicing' )
            );

            return [ 'table_rows' => $table_rows];
        }

        foreach ( $services as $service ) {
            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td><input type="checkbox" id="%3$d" class="serviceListCheckbox" data-value="%5$s"></td>
                    <td>%3$d</td>
                    <td>%4$s</td>
                    <td>%5$s</td>
                    <td>%6$s</td>
                                            
                </tr>',
                esc_url( $service->preview_url() ),
                esc_html__( 'View subscription', 'smart-woo-service-invoicing' ),
                absint( $service->get_id() ),
                esc_html( $service->get_name() ),
                esc_html( $service->get_service_id() ),
                self::capture_output( 'smartwoo_print_service_status', $service, ['dashboard-status'] )

            );
        }

        return [ 'table_rows' => $table_rows];
    }

    /**
     * Prepare response data for `subscribersList` section of the dashboard.
     * 
     * @param object[] $subscribers
     * @return string[] Array of formatted html string for the Subscribers List section.
     */
    private static function prepare_subscribersList_data( $subscribers ) {
        $table_rows = [];

        if ( ! self::is_collection_of( $subscribers, \stdClass::class ) ) {
            $table_rows[] = sprintf(
                '<tr><td class="sw-not-found" colspan="4">%s</td></tr>',
                __( 'No active subscribers found.', 'smart-woo-service-invoicing' )
            );

            return [ 'table_rows' => $table_rows ];
        }

        foreach ( $subscribers as $subscriber ) {
            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td><img class="sw-table-avatar" src="%3$s" alt="%7$s" width="48" height="48"></td>
                    <td>%4$s</td>
                    <td>%5$s</td>
                    <td>%6$s</td>
                </tr>',
                esc_url( get_edit_user_link( $subscriber->id ) ),
                esc_html__( 'View subscriber', 'smart-woo-service-invoicing' ),
                esc_url( $subscriber->avatar_url ),
                esc_html( $subscriber->name ),
                esc_html( smartwoo_check_and_format( $subscriber->member_since, true ) ),
                esc_html( $subscriber->last_seen ),
                // translators: %s is the subscriber's name, used in the image alt text.
                esc_attr( sprintf( __( '%s photo', 'smart-woo-service-invoicing' ), $subscriber->name ) )
            );
        }

        return [ 'table_rows' => $table_rows ];
    }

    /**
     * Prepare response data for `needsAttention` section of the dashboard.
     *  
     * @param SmartWoo_Invoice[]|SmartWoo_Order[]|SmartWoo_Service $collection An array of either invoice, order or service subscription object.
     * @return string[] Array of formatted html string for the Needs Attention section.
     */
    private static function prepare_needsAttention_data( $collection ) {

        if ( self::is_collection_of( $collection, SmartWoo_Invoice::class ) ) {
            return self::prepare_needsAttention_invoice_data( $collection );
        }

        if ( self::is_collection_of( $collection, SmartWoo_Order::class ) ) {
            return self::prepare_needsAttention_order_data( $collection );
        }

        if ( self::is_collection_of( $collection, SmartWoo_Service::class ) ) {
            return self::prepare_needsAttention_subscription_data( $collection );
        }

        return array(
            'table_rows' => array(
                sprintf(
                    '<tr>
                        <td colspan="3" class="sw-not-found">%s</td>
                    </tr>',
                    __( 'No rocord found matching this filter.', 'smart-woo-service-invoicing' )
                )
            )
        );
        
    }

    /**
     * Prepare invoice data for the `needsAttention` section of the dasboard.
     * 
     * @param SmartWoo_Invoice[] $invoices An array of Invoice objects.
     * @return string[] Array of formatted html string of invoice data for the Needs Attention section.
     */
    private static function prepare_needsAttention_invoice_data( $invoices ) {
        $table_rows = [];
        foreach ( $invoices as $invoice ) {
            $body = sprintf(
                '<object data="%1$s" type="application/pdf" class="smartwoo-invoice-pdf-viewer">
                    <p>%2$s</p>
                </object>',
                esc_url( $invoice->print_url() ),
                smartwoo_error_notice( __( 'It appears your browser cannot display PDF files. Please use the buttons below to manage, print, or download the invoice.', 'smart-woo-service-invoicing' ) )
            );

            $footer = sprintf(
                '<div class="sw-button-container">
                    <a href="%1$s" class="sw-blue-button button">%2$s</a>
                    <a href="%3$s" class="sw-blue-button button" download>%4$s</a>
                    <a href="%5$s" class="sw-blue-button button" target="_blank">%6$s</a>
                </div>',
                esc_url( $invoice->preview_url( 'admin' ) ),
                __( 'Manage Invoice', 'smart-woo-service-invoicing' ),
                esc_url( $invoice->download_url( 'admin' ) ),
                __( 'Download PDF', 'smart-woo-service-invoicing' ),
                esc_url( $invoice->print_url() ),
                __( 'Print Invoice', 'smart-woo-service-invoicing' )
            
            );

            $invoice_details = array(
                /* translators: %s is the invoice public ID */
                'heading'   => sprintf( __( '<h2>Invoice #%s</h2>', 'smart-woo-service-invoicing' ), $invoice->get_invoice_id() ),
                'body'      => $body,
                'footer'    => $footer
            );
            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td>%3$s</td>
                    <td>%4$s</td>
                    <td>
                        <div class="smartwoo-options-dots" tabindex="0">
                            <ul class="smartwoo-options-dots-items" title="">
                                <li data-action="composeEmail" data-args="%5$s">%6$s</li>
                                <li data-action="markAsPaid" data-args="%7$s">%8$s</li>
                                <li data-action="sendPaymentReminder" data-args="%9$s">%10$s</li>
                                <li data-action="viewInvoiceDetails" data-args="%11$s">%12$s</li>
                            </ul>
                            <span class="dashicons dashicons-ellipsis" title="%13$s"></span>
                        </div>
                    </td>  
                </tr>',
                esc_url( $invoice->preview_url( 'admin' ) ),
                __( 'View invoice', 'smart-woo-service-invoicing' ),
                __( 'Invoice', 'smart-woo-service-invoicing' ),
                esc_html( $invoice->get_invoice_id() ),
                esc_attr( smartwoo_json_encode_attr( SmartWoo_Dashboard_Controller::prepare_modal_mail_data( $invoice ) ) ),
                __( 'Compose Email', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'invoice_id' => $invoice->get_invoice_id(), 'filter' => 'markInvoicePaid'] ) ),
                __( 'Mark as Paid', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'invoice_id' => $invoice->get_invoice_id(), 'filter' => 'sendPaymentReminder'] ) ),
                __( 'Send payment reminder', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'invoice_details' => $invoice_details, 'filter' => 'viewInvoiceDetails'] ) ),
                __( 'View Invoice Details', 'smart-woo-service-invoicing' ),
                __( 'Options', 'smart-woo-service-invoicing' )

            );
        }

        return [ 'table_rows' => $table_rows];
    }

    /**
     * Prepare order data for the `needsAttention` section of the dashboard.
     * 
     * @param SmartWoo_Order[] $orders An array of Smart Woo Order objects.
     * @return string[] Array of formatted html string of order data for the Needs Attention section.
     */
    private static function prepare_needsAttention_order_data( $orders ) {
        $table_rows = [];
        foreach ( $orders as $order ) {
            $details_body = sprintf(
                '<div class="sw-order-details">
                    <div class="sw-order-details_content">
                        <h3>%1$s</h3>
                        <p>%2$s</p>
                    </div>

                    <div class="sw-order-details_content">
                        <h3>%3$s</h3>
                        <p><a href="mailto:%4$s">%4$s</a></p>
                    </div>

                    <div class="sw-order-details_content">
                        <h3>%5$s</h3>
                        <p><a href="tel:%6$s">%6$s</a></p>
                    </div>

                    <div class="sw-order-details_content">
                        <h3>%7$s</h3>
                        <p>%8$s (%9$s)</p>
                    </div>

                    <div class="sw-order-details_content">
                        <h3>%10$s</h3>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>%11$s</th>
                                    <th>%12$s</th>
                                    <th>%13$s</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="sw-order-product-data">
                                            <strong>%14$s</strong> <br>
                                            <strong>%15$s</strong> %16$s <br>
                                            <strong>%17$s</strong> %18$s <br>
                                        </div>
                                    </td>
                                    <td>%19$s</td>
                                    <td>%20$s</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>',
                esc_html__( 'Billing Details', 'smart-woo-service-invoicing' ),
                wp_kses_post( smartwoo_get_user_billing_address( $order->get_user_id() ) ),
                esc_html__( 'Email', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_billing_email() ),
                esc_html__( 'Phone', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_parent_order() ? $order->get_parent_order()->get_billing_phone() : $order->get_billing_phone() ),
                esc_html__( 'Payment Method', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_payment_method_title() ),
                esc_html( $order->get_transaction_id() ),
                esc_html__( 'Items', 'smart-woo-service-invoicing' ),
                esc_html__( 'Product', 'smart-woo-service-invoicing' ),
                esc_html__( 'Quantity', 'smart-woo-service-invoicing' ),
                esc_html__( 'Total', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_product_name() ),
                esc_html__( 'Service Name:', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_service_name() ),
                esc_html__( 'Sign Up Fee:', 'smart-woo-service-invoicing' ),
                esc_html( smartwoo_price( $order->get_sign_up_fee(), array( 'currency' => $order->get_currency() ) ) ),
                esc_html( $order->get_quantity() ),
                smartwoo_price( $order->get_total(), array( 'currency' => $order->get_currency() ) )
            );

            $details_footer = sprintf(
                '<div class="sw-button-container">
                    <a href="%s" class="sw-blue-button button">%s</a>
                    <a href="%s" class="sw-blue-button button">%s</a>
                </div>',
                esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ),
                __( 'Manage Orders', 'smart-woo-service-invoicing' ),
                esc_url( admin_url( 'admin.php?page=sw-service-orders&section=process-order&order_id='. $order->get_id() ) ),
                __( 'View Order', 'smart-woo-service-invoicing' )
            );

            // Normalize whitespace without breaking tags
            $details_body = preg_replace( "/\r|\n/", '', $details_body );
            $order_details = array(
                'heading'   => sprintf( '<h2>Order #%d</h2>', $order->get_id() ),
                'body'      => $details_body,
                'footer'    => $details_footer
            );

            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td>%3$s</td>
                    <td>%4$s</td>
                    <td>
                        <div class="smartwoo-options-dots" tabindex="0">
                            <ul class="smartwoo-options-dots-items" title="">
                                <li data-action="composeEmail" data-args="%5$s">%6$s</li>
                                <li data-action="autoProcessOrder" data-args="%7$s">%8$s</li>
                                <li data-action="viewOrderDetails" data-args="%9$s">%10$s</li>
                            </ul>
                            <span class="dashicons dashicons-ellipsis" title="%11$s"></span>
                        </div>
                    </td>  
                </tr>',
                esc_url( admin_url( 'admin.php?page=sw-service-orders&section=process-order&order_id=' . $order->get_id()  ) ),
                __( 'View order', 'smart-woo-service-invoicing' ),
                __( 'Order', 'smart-woo-service-invoicing' ),
                esc_html( $order->get_id() ),
                esc_attr( smartwoo_json_encode_attr( SmartWoo_Dashboard_Controller::prepare_modal_mail_data( $order ) ) ),
                __( 'Compose Email', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'order_id' => $order->get_id(), 'filter' => 'autoProcessOrder'] ) ),
                __( 'Auto Process Order', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'order_details' => $order_details, 'filter' => 'viewOrderDetails'] ) ),
                __( 'View Order Details', 'smart-woo-service-invoicing' ),
                __( 'Options', 'smart-woo-service-invoicing' )

            );
        }

        return [ 'table_rows' => $table_rows];
    }

    /**
     * Prepare subscription data for the `needsAttention` section of the dashboard.
     * 
     * @param SmartWoo_Service[] $services An array of subscription objects.
     * @return string[] Array of formatted html string of service subscription data for the Needs Attention section.
     */
    private static function prepare_needsAttention_subscription_data( $services ) {
        $table_rows = [];
        foreach ( $services as $service ) {

            $related_invoice = SmartWoo_Invoice_Database::get_service_invoices( ['limit' => 1, 'status' => 'unpaid', 'service_id' => $service->get_service_id(), 'type' => 'Service Renewal Invoice'] );
            $related_invoice = ! empty( $related_invoice ) ? $related_invoice[0] : null;
            
            if ( $related_invoice ) {
                $inv_body = sprintf(
                    '<object data="%1$s" type="application/pdf" class="smartwoo-invoice-pdf-viewer">
                        <p>%2$s</p>
                    </object>',
                    esc_url( $related_invoice->print_url() ),
                    smartwoo_error_notice( __( 'It appears your browser cannot display PDF files. Please use the buttons below to manage, print, or download the invoice.', 'smart-woo-service-invoicing' ) )
                );

                $inv_footer = sprintf(
                    '<div class="sw-button-container">
                        <a href="%1$s" class="sw-blue-button button">%2$s</a>
                        <a href="%3$s" class="sw-blue-button button" download>%4$s</a>
                        <a href="%5$s" class="sw-blue-button button" target="_blank">%6$s</a>
                        <a href="%7$s" class="sw-blue-button button">%8$s</a>
                    </div>',
                    esc_url( $related_invoice->preview_url( 'admin' ) ),
                    __( 'Manage Invoice', 'smart-woo-service-invoicing' ),
                    esc_url( $related_invoice->download_url( 'admin' ) ),
                    __( 'Download PDF', 'smart-woo-service-invoicing' ),
                    esc_url( $related_invoice->print_url() ),
                    __( 'Print Invoice', 'smart-woo-service-invoicing' ),
                    esc_url( $service->preview_url() ),
                    __( 'View Subscription', 'smart-woo-service-invoicing' )
                
                );

                $invoice_details = array(
                    /* translators: %s is the invoice public ID */
                    'heading'   => sprintf( __( '<h2>Invoice #%s</h2>', 'smart-woo-service-invoicing' ), $related_invoice->get_invoice_id() ),
                    'body'      => $inv_body,
                    'footer'    => $inv_footer
                );
            } else {
                $invoice_details = array(
                    'heading'   => sprintf( '<h2>%s</h2>', __( 'Invoice', 'smart-woo-service-invoicing' ) ),
                    'body'      => smartwoo_notice( __( 'This subscription does not have a pending renewal invoice.', 'smart-woo-service-invoicing' ) ),
                    'footer'    => sprintf(
                        '<div class="sw-button-container">
                            <a href="%1$s" class="sw-blue-button button">%2$s</a>
                        </div>',
                        esc_url( $service->preview_url() ),
                        __( 'View Subscription', 'smart-woo-service-invoicing' )
                    )
                );
            }

            // Service details
            $service_body = sprintf(
                '<div class="sw-admin-subinfo">
                    %1$s
                    <h3>%2$s</h3>
                    <hr>
                    <div>
                        <p class="smartwoo-container-item"><span>%3$s:</span> %4$s</p>
                        <p class="smartwoo-container-item"><span>%5$s:</span> %6$s</p>
                        <p class="smartwoo-container-item"><span>%7$s:</span> %8$s</p>
                        <p class="smartwoo-container-item"><span>%9$s:</span> %10$s</p>
                        <p class="smartwoo-container-item"><span>%11$s:</span> %12$s</p>
                        <p class="smartwoo-container-item"><span>%13$s:</span> %14$s</p>
                        <p class="smartwoo-container-item"><span>%15$s:</span> %15$s</p>
                        <p class="smartwoo-container-item"><span>%17$s:</span> %18$s</p>
                        <p class="smartwoo-container-item"><span>%19$s:</span> %20$s</p>
                    </div>
                </div>',
                self::capture_output( 'smartwoo_print_service_status', $service, ['modal-status'] ),
                esc_html( $service->get_name() ),
                __( 'ID', 'smart-woo-service-invoicing' ),
                esc_html( $service->get_id() ),                    
                __( 'Service ID', 'smart-woo-service-invoicing' ),
                esc_html( $service->get_service_id() ),            
                __( 'Type', 'smart-woo-service-invoicing' ),
                esc_html( $service->get_type() ? $service->get_type() : 'N/A' ),
                __( 'Billing Cycle', 'smart-woo-service-invoicing' ),   // %9$s
                esc_html( $service->get_billing_cycle() ),
                __( 'URL', 'smart-woo-service-invoicing' ),
                esc_html( $service->get_service_url() ),
                __( 'Start Date', 'smart-woo-service-invoicing' ),
                esc_html( smartwoo_check_and_format( $service->get_start_date(), true ) ),
                __( 'Nex Payment Date', 'smart-woo-service-invoicing' ),
                esc_html( smartwoo_check_and_format( $service->get_next_payment_date(), true ) ),
                __( 'End Date', 'smart-woo-service-invoicing' ),
                esc_html( smartwoo_check_and_format( $service->get_end_date(), true ) ),
                __( 'Expiry Date', 'smart-woo-service-invoicing' ),
                esc_html( smartwoo_check_and_format( $service->get_expiry_date(), true ) )

            );

            $service_footer = sprintf(
                '<div class="sw-button-container">
                    <a href="%s" class="sw-blue-button button">%s</a>
                    <a href="%s" class="sw-blue-button button">%s</a>
                    <a href="%s" class="sw-blue-button button">%s</a>
                    <a href="%s" class="sw-blue-button button">%s</a>
                </div>',(
                esc_url( $service->preview_url() ) ),
                __( 'View Subscription', 'smart-woo-service-invoicing' ),
                esc_url( add_query_arg( array( 'tab' => 'client' ), $service->preview_url() ) ),
                __( 'View Client', 'smart-woo-service-invoicing' ),
                esc_url( add_query_arg( array( 'tab' => 'edit-service' ), $service->preview_url() ) ),
                __( 'Edit Subscription', 'smart-woo-service-invoicing' ),
                esc_url( add_query_arg( array( 'tab' => 'stats' ), $service->preview_url() ) ),
                __( 'View Statistics', 'smart-woo-service-invoicing' )
            );

            $service_heading = sprintf( '<h2>%1$s (#%2$s)</h2>', $service->get_name(), $service->get_service_id() );

            $service_details = array(
                'heading'   => $service_heading,
                'body'      => $service_body,
                'footer'    => $service_footer
            );


            $table_rows[] = sprintf(
                '<tr class="smartwoo-linked-table-row" data-url="%1$s" title="%2$s">
                    <td>%3$s</td>
                    <td>%4$s</td>
                    <td>
                        <div class="smartwoo-options-dots" tabindex="0">
                            <ul class="smartwoo-options-dots-items" title="">
                                <li data-action="composeEmail" data-args="%5$s">%6$s</li>
                                <li data-action="autoRenewService" data-args="%7$s">%8$s</li>
                                <li data-action="viewRelatedInvoice" data-args="%9$s">%10$s</li>
                                <li data-action="previewServiceDetails" data-args="%11$s">%12$s</li>
                            </ul>
                            <span class="dashicons dashicons-ellipsis" title="%13$s"></span>
                        </div>
                    </td>  
                </tr>',
                esc_url( $service->preview_url() ),
                __( 'View subscription', 'smart-woo-service-invoicing' ),
                __( 'Subscription', 'smart-woo-service-invoicing' ),
                esc_html( $service->get_service_id() ),
                esc_attr( smartwoo_json_encode_attr( SmartWoo_Dashboard_Controller::prepare_modal_mail_data( $service ) ) ),
                __( 'Compose Email', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'service_id' => $service->get_service_id(), 'filter' => 'autoRenewService'] ) ),
                __( 'Auto Renew Subscription', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'invoice_details' => $invoice_details, 'filter' => 'viewRelatedInvoice'] ) ),
                __( 'View Related Invoice', 'smart-woo-service-invoicing' ),
                esc_attr( smartwoo_json_encode_attr( [ 'service_details' => $service_details, 'filter' => 'previewServiceDetails'] ) ),
                __( 'View Service Details', 'smart-woo-service-invoicing' ),
                __( 'Options', 'smart-woo-service-invoicing' )
            );
        }

        return [ 'table_rows' => $table_rows];
    }

    /**
     * Handles invoice actions in the dashboard
     */
    private static function handle_invoice_actions() {
        $request    = func_get_arg( 2 );
        $invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $request->get_param( 'invoice_id' ) );

        if ( ! $invoice ) {
            return array( $invoice,  __( 'Invalid or deleted invoice', 'smart-woo-service-invoicing' ) );
        }

        $filter = $request->get_param( 'filter' );
        $result = array( false, __( 'Unsupported invoice action', 'smart-woo-service-invoicing' ) );

        if ( 'markInvoicePaid' === $filter ) {
            $paid = smartwoo_mark_invoice_as_paid( $invoice );
            if ( $paid ) {
                $invoice->set_status( 'paid' );
                $message = sprintf( __( 'Invoice status changed to "%s"', 'smart-woo-service-invoicing' ), $invoice->get_status() );
            } else{
                $message = __( 'unable to mark invoice as paid', 'smart-woo-service-invoicing' );
            }

            $result = array( $paid, $message );
        } elseif( 'sendPaymentReminder' === $filter ) {
            $sent = \SmartWoo_Invoice_Payment_Reminder::send_mail( $invoice );
            if ( $sent ) {
                $message = __( 'Payment reminder email sent!', 'smart-woo-service-invoicing' );
            } else {
                $message = __( 'Unable to sent payment reminder email', 'smart-woo-service-invoicing' );
            }
        
            $result = array( $sent, $message );
        }

        return $result;
    }

    /**
     * Handles bulk actions in the dashboard
     */
    private static function handle_bulk_actions() {
        $request    = func_get_arg( 2 );
        $section    = $request->get_param( 'section' );

        switch ( $section ) {
            case 'subscriptionList_bulk_action':
                return self::perform_subscription_bulk_action( $request );
            default:
            return array( false, __( 'Unsupported bulk action', 'smart-woo-service-invoicing' ) );  
        }
              
    }

    /**
     * Perform search on for either a service subscription or an invoice or a Smart Woo Order
     * 
     * @param WP_REST_Request $request
     */
    private static function perform_search( $request ) {
        $search_term    = $request->get_param( 'search_term' );
        $search_type    = $request->get_param( 'search_type' );
        $page           = $request->get_param( 'page' ) ?? 1;
        $limit          = $request->get_param( 'limit' ) ?? 20;

        $table_rows     = [];

        if ( 'service' === $search_type ) {
            $services = SmartWoo_Service_Database::search( compact( 'search_term', 'page', 'limit' ) );
        }


        elseif ( 'invoice' === $search_type ) {
            
        }
    }

    /**
     * Get a message-based REST response.
     * 
     * @param array $args
     */
    private static function message_response( array $args ) {
        list( $result, $message ) = $args;
        $messages = array(
            'success' => $message,
            'error'   => $message ?? __( 'Something went wrong', 'smart-woo-service-invoicing' ),
        );

        $message = $result ? $messages['success'] : $messages['error'];

        return array(
            'success' => boolval( $result ),
            'message' => $message
        );
    }

    /**
     * Perform subscription bulk actions.
     * 
     * @param WP_REST_Request $request
     * @return array
     */
    private static function perform_subscription_bulk_action( WP_REST_Request $request ) {
        $service_ids    = $request->get_param( 'service_ids' );
                
        if ( empty( $service_ids ) || ! is_array( $service_ids ) ) {
            return array( false, __( 'No services selected', 'smart-woo-service-invoicing' ) );
        }

        // Instatiate all selected services.
        $services = array_map( 'SmartWoo_Service_Database::get_service_by_id', $service_ids );
        $services = array_filter( $services ); // Remove any null values.

        $action = $request->get_param( 'action' );

        // Delete services.
        if ( 'delete' === $action ) {
            $deleted_count = 0;
            foreach ( $services as $service ) {
                if ( $service->delete() ) {
                    $deleted_count++;
                }
            }

            // translators: %d is the number of deleted services.
            $message = sprintf( _n( '%d service deleted', '%d services deleted', $deleted_count, 'smart-woo-service-invoicing' ), $deleted_count );
            return array( $deleted_count > 0, $message );
        }

        // This is a status bulk action.
        $status         = smartwoo_interprete_service_status( $action );
        $updated_count  = 0;
        foreach ( $services as $service ) {
            $service->set_status( $status );
            if ( $service->save() ) {
                $updated_count++;
            }

        }

        $status_labels  = array_values( smartwoo_supported_service_status() );
        $status_labels  = array_combine( $status_labels, smartwoo_supported_service_status() );

        $status = is_null( $status ) ? __( 'Automatic Cancellation', 'smart-woo-service-invoicing' ) : ( $status_labels[ $status ] ?? $status );
        // translators: %d: is the number of updated services: %s is the status label.
        $message = sprintf( _n( '%d service updated to "%s"', '%d services updated to "%s"', $updated_count, 'smart-woo-service-invoicing' ), $updated_count, $status );
        
        return array( $updated_count > 0, $message );
    }


    /**
     * Helper function to capture and return the output of a given callback
     * 
     * @param callable $callback Function name or [class, method] or closure.
     * @param mixed    ...$args  Arguments to pass to the function.
     * @return string The captured output.
    */
    public static function capture_output( callable $callback, ...$args ) {
        ob_start();
        $result = $callback( ...$args );
        $output = ob_get_clean();

        return $output !== '' ? $output : (string) $result;
    }

    /**
     * Allowed dashboard sections parameter
     * 
     * @return array
     */
    public static function allowed_sections_params() {
        $sections = array(
            'search',
            'subscriptionList',
            'subscriptionList_bulk_action',
            'subscribersList',
            'needsAttention',
            'activities',
            'needsAttention_options'
        );

        return apply_filters( 'smartwoo_AdminDashboard_allowed_sections_params', $sections );
    }
}