<?php
/**
 * File name class-sw.php.
 * Main Smart Woo Service Invoicing class file
 * 
 * @author Callistus
 * @package SmartWoo
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Smart Woo Class
 * Represents this plugin.
 * 
 * @since 1.0.2
 * @author callistus.
 * @package SmartWoo\classes
 */
final class SmartWoo {
    /**
     * @var SmartWoo Single instance of this class
     */
    protected static $instance = null;

    /**
     * @var SmartWoo_Service instance of service subscription class.
     */
    protected $service;

    /**
     * @var SmartWoo_Invoice instance of invoice class.
     */
    protected $invoice;

    /**
     * @var SmartWoo_Product instance of Smart Woo Product
     */
    protected $product;

    /**
     * @var SmartWoo_Orders instance of Smart Woo Orders.
     */
    protected $orders;

    /**
     * Class constructor
     */
    public function __construct() {
        add_filter( 'plugin_row_meta', array( __CLASS__, 'smartwoo_row_meta' ), 10, 2 );
        add_action( 'smartwoo_download', array( $this, 'download_handler' ) );
        add_filter( 'plugin_action_links_' . SMARTWOO_PLUGIN_BASENAME, array( $this, 'options_page' ), 10, 2 );
        add_action( 'admin_post_nopriv_smartwoo_login_form', array( $this, 'login_form' ) );
        add_action( 'admin_post_smartwoo_login_form', array( $this, 'login_form' ) );
        add_action( 'admin_post_smartwoo_service_from_order', array( $this, 'new_service_from_order' ) );
        add_action( 'admin_post_smartwoo_add_service', 'smartwoo_process_new_service_form' );
        add_action( 'admin_post_smartwoo_edit_service', 'smartwoo_process_edit_service_form' );
        add_action( 'woocommerce_order_details_before_order_table', array( $this, 'before_order_table' ) );
        add_action( 'smartwoo_service_scan', array( __CLASS__, 'count_all_services' ) );

        // Add Ajax actions
        add_action( 'wp_ajax_smartwoo_asset_delete', array( 'SmartWoo_Service_Assets', 'ajax_delete' ) );
        add_action( 'wp_ajax_smartwoo_delete_service', 'smartwoo_delete_service' );
        add_action( 'wp_ajax_nopriv_smartwoo_delete_service', 'smartwoo_delete_service' );
        add_action( 'wp_ajax_smartwoo_dashboard', array( $this, 'dashboard_ajax' ) );
    }

    /** Service Subscription */
    public function service() {}

    /** Invoice */
    public function invoice() {}

    /**
     * Add useful links to our plugin row meta
     */
    public static function smartwoo_row_meta( $links, $file ) {

        if ( SMARTWOO_PLUGIN_BASENAME !== $file ) {
            return $links;
        }

        /**
         * Smart Woo Pro URL
         */
        $smartwoo_pro_url = apply_filters( 'smartwoopro_purchase_link', 'https://callismart.com.ng/smart-woo-service-invoicing' );

        /**
         * Plugin support link.
         */
        $support_url = apply_filters( 'smartwoo_support_url', 'https://callismart.com.ng/support-portal' );

        /**
         * Our github repository
         */
        $source_code    = apply_filters( 'smartwoo_source_code', 'https://github.com/CallismartLtd/smart-woo-service-invoicing' );

        /**
         * Other Products URL
         */
        $other_products = apply_filters( 'smartwoo_other_products', 'https://callismart.com.ng/pricing' );

        $smartwoo_row_meta = array(
            'smartwoo_pro'      => '<a href="' . esc_url( $smartwoo_pro_url ) . '" title="' . esc_attr__( 'Get Pro Version', 'smart-woo-service-invoicing' ) . '">' . esc_html__( 'Smart Woo Pro', 'smart-woo-service-invoicing' ) . '</a>',
            'smartwoo_support'  => '<a href="' . esc_url( $support_url ) . '" title="' . esc_attr__( 'Contact Support', 'smart-woo-service-invoicing' ) . '">' . esc_html__( 'Support', 'smart-woo-service-invoicing' ) . '</a>',
            'smartwoo_api'      => '<a href="' . esc_url( $source_code ) . '" aria-label="' . esc_attr__( 'View Source Code', 'smart-woo-service-invoicing' ) . '">' . esc_html__( 'API Documentation', 'smart-woo-service-invoicing' ) . '</a>',

        );

        return array_merge( $links, $smartwoo_row_meta );
    }

    /**
     * Add settings page URL to plugin action link
     */
    public static function options_page( $links ) {
        $setting_url = array(
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=sw-options' ) ) . '" aria-label="' . esc_attr__( 'View Smart Woo options', 'smart-woo-service-invoicing' ) . '">' . esc_html__( 'Settings', 'smart-woo-service-invoicing' ) . '</a>',
        );

        return array_merge( $setting_url, $links );
    }

    /**
     * Display Dashboard nav button when a configured order is checked out.
     * 
     * @param WC_Order
     */
    public function before_order_table( $order ) {
        $our_order  = apply_filters( 'smartwoo_order_details_buttons', smartwoo_check_if_configured( $order ) || $order->is_created_via( SMARTWOO ) );
        if ( $our_order ) {
            echo '<a href="' . esc_url( smartwoo_service_page_url() ) .'" class="sw-blue-button">Dashbaord</a>';
            echo '<a href="' . esc_url( smartwoo_invoice_preview_url( $order->get_meta( '_sw_invoice_id' ) ) ) .'" class="sw-blue-button">Invoice</a>';
        }
    
    }

    /*
    |------------------------------------
    | FORM POST HANDLERS
    |------------------------------------
    */

    /**
     * Login form handler
     */
    public function login_form() {
        if ( isset( $_POST['user_login'], $_POST['password'], $_POST['smartwoo_login_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smartwoo_login_nonce'] ) ), 'smartwoo_login_nonce') ) {
            $credentials = array(
                'user_login'    => sanitize_text_field( wp_unslash( $_POST['user_login'] ) ),
                'user_password' => $_POST['password'], // phpcs:disable -- Passwords shouldn't be mutilated
                'remember'      => isset( $_POST['remember_me'] )
            );


            $user = wp_signon( $credentials, false );

            if ( is_wp_error( $user ) ) {
                set_transient( 'smartwoo_login_error', $user->get_error_message(), 5 );
                wp_redirect( esc_url_raw( wp_get_referer() ) );
                exit;

            } else {
                wp_redirect( esc_url_raw( isset( $_POST['redirect'] ) ? wp_unslash( $_POST['redirect'] ): smartwoo_service_page_url() ) );
                exit;
            }
        }
    }

    /**
     * Handle the processing of new service orders.
     * 
     * @since 2.0.0
     * @since 2.0.0 Added support for service assets.
     */
    public function new_service_from_order() {
    
        if ( isset( $_POST['smartwoo_process_new_service'], $_POST['sw_process_new_service_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_process_new_service_nonce'] ) ), 'sw_process_new_service_nonce' ) ) {

            $product_id        	= isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $order_id          	= isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
            $service_url       	= isset( $_POST['service_url'] ) ? sanitize_url( wp_unslash( $_POST['service_url'] ), array( 'http', 'https' ) ) : '';
            $service_type      	= isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : '';
            $user_id           	= isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : '';
            $start_date        	= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
            $billing_cycle     	= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
            $next_payment_date 	= isset( $_POST['next_payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date'] ) ) : '';
            $end_date          	= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
            $status            	= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
            $service_name 		= isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name']) ) : '';
            $service_id 		= isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
            $process_downloadable   = ! empty( $_POST['sw_downloadable_file_urls'][0] ) && ! empty( $_POST['sw_downloadable_file_names'][0] );
            $process_more_assets    = ! empty( $_POST['add_asset_types'][0] ) && ! empty( $_POST['add_asset_names'][0] ) && ! empty( $_POST['add_asset_values'][0] );

            // Validation.
            $validation_errors 	= array();

            if ( ! preg_match( '/^[A-Za-z0-9 ]+$/', $service_name ) ) {
                $validation_errors[] = 'Service name should only contain letters, and numbers.';
            }

            if ( empty( $product_id ) ) {
                $validation_errors[] = 'Product ID is missing';
            }

            if ( ! empty( $service_type ) && ! preg_match( '/^[A-Za-z0-9 ]+$/', $service_type ) ) {
                $validation_errors[] = 'Service type should only contain letters, numbers, and spaces.';
            }

            if ( ! empty( $service_url ) && filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
                $validation_errors[] = 'Invalid service URL format.';
            }

            if ( empty( $service_id ) ) {
                $validation_errors[] = 'Service ID is required.';
            }

            if ( empty( $start_date ) || empty( $end_date ) || empty( $next_payment_date ) || empty( $billing_cycle ) ) {
                $validation_errors[] = 'All Dates must correspond to the billing circle';
            }

            if ( ! empty( $validation_errors ) ) {
                smartwoo_set_form_error( $validation_errors );
                wp_redirect( esc_url_raw( admin_url( 'admin.php?page=sw-admin&action=process-new-service&order_id=' . $order_id ) ) );
                exit;
            }

            $new_service = new SmartWoo_Service(
                $user_id,
                $product_id,
                $service_id,
                $service_name,
                $service_url,
                $service_type,
                null, // Invoice ID is null.
                $start_date,
                $end_date,
                $next_payment_date,
                $billing_cycle,
                $status
            );

                $saved_service_id = $new_service->save();

            if ( $saved_service_id ) {

                // Process downloadable assets first.
                if ( $process_downloadable ) {
                    $file_names     = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) );
                    $file_urls      = array_map( 'sanitize_url', wp_unslash( $_POST['sw_downloadable_file_urls'] ) );
                    $is_external    = isset( $_POST['is_external'] ) ? sanitize_text_field( wp_unslash( $_POST['is_external'] ) ) : 'no';
                    $asset_key      = isset( $_POST['asset_key'] ) ? sanitize_text_field( wp_unslash( $_POST['asset_key'] ) ) : '';
                    $access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['access_limits'] ) ) : array();

                    $downloadables  = array();
                    if ( count( $file_names ) === count( $file_urls ) ) {
                        $downloadables  = array_combine( $file_names, $file_urls );
                    }
                    
                    foreach ( $downloadables as $k => $v ) {
                        if ( empty( $k ) || empty( $v ) ) {
                            unset( $downloadables[$k] );
                        }
                    }

                    if ( ! empty( $downloadables ) ) {
                        $raw_assets = array(
                            'asset_name'    => 'downloads',
                            'service_id'    => $saved_service_id,
                            'asset_data'    => $downloadables,
                            'access_limit'  => isset( $access_limit[0] ) && '' !== $access_limit[0] ? intval( $access_limit[$index] ) : -1,
                            'is_external'   => $is_external,
                            'asset_key'     => $asset_key,
                            'expiry'        => $end_date,
                        );

                        $obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
                        $obj->save();

                    } 
                }
                    
                if ( $process_more_assets ) {
                    /**
                     * Additional assets are grouped by their asset types, this is to say that
                     * an asset type will be stored with each asset data.
                     * 
                     * Asset data will be an extraction of a combination of each asset name and value
                     * in the form.
                     */
                    $asset_tpes     = array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_types'] ) );
                    $the_keys       = array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_names'] ) );
                    $the_values     = array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_values'] ) );
                    $access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['access_limits'] ) ) : array();

                    $asset_data = array();

                    // Attempt to pair asset names and values.
                    if ( count( $the_keys ) === count( $the_values ) ) {
                        $asset_data = array_combine( $the_keys, $the_values );
                    }

                    // If this pairing was successful.
                    if ( ! empty( $asset_data ) ) {
                        // The assets types are numerically indexed.
                        $index      = 0;
                        array_shift( $access_limit ); // Remove limit for downloadables which is already proceesed.

                        /**
                         * We loop through each part of the combined asset data to
                         * save it with an asset type in the database.
                         */
                        foreach ( $asset_data as $k => $v ) {
                            // Empty asset name or value will not be saved.
                            if ( empty( $k ) || empty( $v ) || empty( $asset_tpes[$index] ) ) {
                                unset( $asset_data[$k] );
                                unset( $asset_tpes[$index] );
                                unset( $access_limit[$index] );

                                $index++;
                                continue;
                                
                            }
    
                            // Proper asset data structure where asset name is used to identify the asset type.
                            $raw_assets = array(
                                'asset_data'    => array_map( 'sanitize_text_field', wp_unslash( array( $k => $v ) ) ),
                                'asset_name'    => $asset_tpes[$index],
                                'expiry'        => $end_date,
                                'service_id'    => $saved_service_id,
                                'access_limit'  => isset( $access_limit[$index] ) && '' !== $access_limit[$index] ? intval( $access_limit[$index] ) : -1,
                            );

                            // Instantiation of SmartWoo_Service_Asset using the convert_array method.
                            $obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
                            $obj->save();
                            $index++;
                        }
                    }
                }
                
                $order = wc_get_order( $order_id );
                
                if ( $order && 'processing' === $order->get_status()  ) {
                    $order->update_status( 'completed' );
                    $invoice_id = $order->get_meta( '_sw_invoice_id' );
                    SmartWoo_Invoice_Database::update_invoice_fields( $invoice_id, array( 'service_id' => $saved_service_id ) );
                }

                do_action( 'smartwoo_new_service_is_processed', $saved_service_id );
                wp_safe_redirect( esc_url_raw( smartwoo_service_preview_url( $saved_service_id ) ) );
                exit;
            }
        }
    }

    /**
     * Instance of current class.
     */
    public static function instance() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
    }

    /**
     * File download handler
     */
    public function download_handler() {
        if ( ! isset( $_GET['smartwoo_action'] )  || $_GET['smartwoo_action'] !== 'smartwoo_download' ) {
            return;
        }

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) ), 'smartwoo_download_nonce' ) ) {
            wp_die( 'Authentication failed', 401 );
        }
    
        if ( ! is_user_logged_in() ) {
            return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'You must be logged in to access this page.' ), 'redirect' => add_query_arg( array_map( 'rawurlencode', $_GET ) ) ) );
        }
        
        $asset_id       = ! empty( $_GET['asset_id'] ) ? absint( $_GET['asset_id'] ) : 0;
        $resource_id    = ! empty( $_GET['resource_id'] ) ? absint( wp_unslash( $_GET['resource_id'] ) ) : '';
        $asset_key      = ! empty( $_GET['key'] ) ? sanitize_key( wp_unslash( $_GET['key'] ) ): '';
        $service_id     = ! empty( $_GET['service_id'] ) ? sanitize_key( wp_unslash( $_GET['service_id'] ) ) : '';
        if ( empty( $resource_id ) || empty( $service_id ) || ! SmartWoo_Service_Assets::verify_key( $asset_key, $resource_id ) ) {
            wp_die( 'Unable to validate requested resource.', 403 );
        }

        // Check Asset validity via parent service.
        $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        if ( ! $service || ! $service->current_user_can_access() ) {
            wp_die( 'Invalid service subscription.', 404 );
        }

        // Check service status.
        $status = smartwoo_service_status( $service );
        if ( ! in_array( $status, smartwoo_active_service_statuses(), true ) ) {
            wp_die( 'Service is not active, please renew it.', 403 );
        }

        $asset_data = SmartWoo_Service_Assets::return_data( $asset_id, $asset_key, $obj );

        if ( ! is_array( $asset_data ) || empty( $asset_data ) ) {
            wp_die( 'Invalid data format returned', 403 );
        }

        $re_indexed_data    = array_values( (array) $asset_data );
        $resource_url       = array_key_exists( $resource_id - 1, $re_indexed_data ) ? $re_indexed_data[$resource_id - 1]: wp_die( 'File URL not found.', 404 );
        $is_external        = $obj->is_external();
        $this->serve_file( $resource_url, wc_string_to_bool( $is_external ), $asset_key );
    }
    
    /**
     * Serve file for download.
     */
    private function serve_file( $resource_url, $is_external = false, $asset_key = '' ) {
        
        // Serve files within the current site.
        if ( ! $is_external ) {
            $resource_url   = sanitize_url( $resource_url, array( 'http', 'https' ) );
            $file_headers   = @get_headers( $resource_url, 1 );
        
            if ( ! $file_headers || strpos( $file_headers[0], '200' ) === false ) {
                wp_die( 'File not found.', 404 );
            }
        
            $content_type   = $file_headers['Content-Type'] ?? 'application/octet-stream';
            $content_length = $file_headers['Content-Length'] ?? 0;
            $filename       = basename( wp_parse_url( $resource_url, PHP_URL_PATH ) );
        
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: ' . $content_type );
            header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . $content_length );
        
            // Open the file and stream it to the browser.
            // phpcs:disable
            $handle = fopen( $resource_url, 'rb' );
            if ( $handle ) {
                while ( ! feof( $handle ) ) {
                    echo fread( $handle, 8192 );
                    ob_flush();
                    flush();
                }
                fclose( $handle );
                // phpcs:enable
            } else {
                wp_die( 'Unable to read the file.' );
            }
        
            exit;
        }
        
        if ( true === $is_external ) {

            // Serve download for remote URLs.
            if ( empty( $asset_key ) ) {
                wp_die( 'Asset key not found.' );
            }

            if ( ! function_exists( 'download_url' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }

            $this->append_http_authorization( $asset_key, $resource_url );
            $file = download_url( $resource_url );

            if ( is_wp_error( $file ) ) {
                wp_die( wp_kses_post( $file->get_error_message() ) );
            }

            global $wp_filesystem;

            if ( ! WP_Filesystem() ) {
                wp_die( 'Could not initialize WP Filesystem.' );
            }

            // Check if the file exists and is accessible via WP_Filesystem
            if ( ! $wp_filesystem->exists( $file ) ) {
                wp_die( 'File not found or inaccessible.' );
            }
            
            // Determine the content type of the file
            $mime_type = 'application/octet-stream'; // Default MIME type.

            if ( function_exists( 'finfo_open' ) ) {
                $finfo     = finfo_open( FILEINFO_MIME_TYPE );
                $mime_type = finfo_file( $finfo, $file );
                finfo_close( $finfo );
            } elseif ( function_exists( 'mime_content_type' ) ) {
                $mime_type = mime_content_type( $file );
            }

            // Get the file size
            $file_size = $wp_filesystem->size( $file );
            $filename       = basename( wp_parse_url( $resource_url, PHP_URL_PATH ) );

            // Set headers
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: ' . $mime_type );
            header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
            header( 'Content-Length: ' . $file_size );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Pragma: public' );
            header( 'Expires: 0' );

            // Output the file content using WP_Filesystem
            $file_content = $wp_filesystem->get_contents( $file );
            if ( false === $file_content ) {
                wp_die( 'Could not read the file, maybe corrupted.' );
            }

            echo $file_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            $wp_filesystem->delete( $file );

        }

        exit;
        
    }

    /**
     * Set Authorization header for outgoing HTTP remote requests.
     * 
     * @param string $token The authorization bearer token.
     * @param string $resource_url The URL of the outgoing HTTP request.
     */
    private function append_http_authorization( $token = '', $resource_url = '' ) {
        add_filter( 'http_request_args', function( $args, $url ) use ( $token, $resource_url ) {
            if ( $resource_url === $url ) {
                $args['headers']['Authorization'] = 'Bearer ' . $token;
            }
            return $args;
        }, 10, 2 );
    }

    /**
     * Dashboard page Ajax handler
     * 
     * @since 2.0.12
     */
    public function dashboard_ajax() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have the required permission to perform this action' ) );

        }

        add_filter( 'smartwoo_is_frontend', '__return_false' );

        $allowed_actions   = apply_filters( 'smartwoo_allowed_dashboard_actions',
            array(
                'sw_search',
                'total_services',
                'total_pending_services',
                'total_active_services',
                'total_active_nr_services',
                'total_due_services',
                'total_on_grace_services',
                'total_expired_services',
                'total_cancelled_services',
                'total_suspended_services',
                'all_services_table',
                'all_pending_services_table',
                'all_active_services_table',
                'all_active_nr_services_table',
                'all_due_services_table',
                'all_on_grace_services_table',
                'all_expired_services_table',
                'all_cancelled_services_table',
                'all_suspended_services_table',
            )
        );

        $action = isset( $_GET['real_action'] ) ? sanitize_text_field( wp_unslash( $_GET['real_action'] ) ) : wp_die();

        if ( ! in_array( $action, $allowed_actions, true ) ){
            wp_send_json_error( array( 'message' => 'action is not allowed' ) );
        }
        
        if ( 'total_services' === $action ) {
            $total  = get_option( 'smartwoo_all_services_count', 0 );
            wp_send_json_success( array( 'total_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_pending_services' === $action ) {
            $total  = smartwoo_count_unprocessed_orders();
            wp_send_json_success( array( 'total_pending_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_active_services' === $action ) {
            $total  = smartwoo_count_active_services();
            wp_send_json_success( array( 'total_active_services' =>  absint( $total ) ) );
        } 
        
        if ( 'total_active_nr_services' === $action ) {
            $total = smartwoo_count_nr_services();
            wp_send_json_success( array( 'total_active_nr_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_due_services' === $action ) {
            $total = smartwoo_count_due_for_renewal_services();
            wp_send_json_success( array( 'total_due_services' =>  absint( $total ) ) );

        } 
        
        if ( 'total_on_grace_services' === $action ) {
            $total = smartwoo_count_grace_period_services();
            wp_send_json_success( array( 'total_on_grace_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_expired_services' === $action ) {
            $total = smartwoo_count_expired_services();
            wp_send_json_success( array( 'total_expired_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_cancelled_services' === $action ) {
            $total = smartwoo_count_cancelled_services();
            wp_send_json_success( array( 'total_cancelled_services' =>  absint( $total ) ) );

        }
        
        if ( 'total_suspended_services' === $action ) {
            $total = smartwoo_count_suspended_services();
            wp_send_json_success( array( 'total_suspended_services' =>  absint( $total ) ) );

        }

        if ( 'all_pending_services_table' === $action ) {
            wp_safe_redirect( admin_url( 'admin.php?page=sw-service-orders') );
            exit;
        }

        $limit  = isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 10;
        $paged  = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;

        /**
         * Send json data for table structures.
         */
        if ( 'sw_search' === $action ) {
            $all_services   = SmartWoo_Service_Database::search();
            $total_services = count( $all_services );
            
        } elseif ( 'all_services_table' === $action ) {
            $all_services   = SmartWoo_Service_Database::get_all();
            $total_services = absint( get_option( 'smartwoo_all_services_count', 0 ) );

        } elseif ( 'all_active_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_all_active( $paged, $limit );
            $total_services = smartwoo_count_active_services();

        } elseif ( 'all_active_nr_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_( array( 'status' => 'Active (NR)', 'page' => $paged, 'limit' => $limit ) );
            $total_services = smartwoo_count_nr_services();
        } elseif ( 'all_due_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_all_due( $paged, $limit );
            $total_services = smartwoo_count_due_for_renewal_services();
        } elseif ( 'all_on_grace_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_all_on_grace( $paged, $limit );
            $total_services = smartwoo_count_grace_period_services();
        } elseif ( 'all_expired_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_all_expired( $paged, $limit );
            $total_services = smartwoo_count_expired_services();
        } elseif ( 'all_cancelled_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_( array( 'status' => 'Cancelled', 'page' => $paged, 'limit' => $limit ) );
            $total_services = smartwoo_count_cancelled_services();
        } elseif ( 'all_suspended_services_table' === $action ) {
            $all_services = SmartWoo_Service_Database::get_( array( 'status' => 'Suspended', 'page' => $paged, 'limit' => $limit ) );
            $total_services = smartwoo_count_suspended_services();
        }

        $total_pages    = ceil( $total_services / $limit );
        $data           = array();
        $row_names      = array();

        // wp_die( var_dump( $all_services ) );
        if ( ! empty( $all_services ) ) {
            foreach ( $all_services as $service ) {
                $data[] = array( $service->getServiceName(), $service->getServiceId(), smartwoo_service_status( $service ) );
                $row_names[] = $service->getServiceId();
            }
            
        }

        $response   = array(
            'table_header'  => array(
                'Service Name',
                'Service ID',
                'Status',
            ),

            'table_body'    => $data,
            'row_names'     => $row_names,
            'total_pages'   => $total_pages,
            'current_page'  => $paged,
        );

        wp_send_json_success( array( 'all_services_table' => $response ) );
        

        
    }

    /**
     * Count all services in the database every five hours.
     * 
     * @since 2.0.12.
     */
    public static function count_all_services() {
        $count  = SmartWoo_Service_Database::count_all();
        update_option( 'smartwoo_all_services_count', $count );
    }
    
}

SmartWoo::instance();
