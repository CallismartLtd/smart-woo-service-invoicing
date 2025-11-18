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

        add_action( 'smartwoo_download', array( $this, 'asset_download' ) );
        add_action( 'smartwoo_admin_view_service_button_area', array( __CLASS__, 'sell_renewal_button' ) );

        add_filter( 'plugin_action_links_' . SMARTWOO_PLUGIN_BASENAME, array( $this, 'options_page' ), 10, 2 );

        add_action( 'admin_post_nopriv_smartwoo_login_form', array( $this, 'login_form' ) );
        add_action( 'admin_post_smartwoo_login_form', array( $this, 'login_form' ) );
        add_action( 'admin_post_smartwoo_admin_download_invoice', array( __CLASS__, 'admin_download_invoice' ) );
        add_action( 'admin_post_smartwoo_mail_preview', array( __CLASS__, 'mail_preview' ) );
        add_action( 'admin_post_smartwoo_print_invoice', array( __CLASS__, 'print_invoice' ) );

        add_action( 'woocommerce_order_details_before_order_table', array( $this, 'before_order_table' ) );
        
        add_action( 'template_redirect', array( __CLASS__, 'payment_link_handler' ) );

        // Service renewal action hooks.
        add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'paid_invoice_order_manager' ), 50, 1 );
        add_action( 'woocommerce_payment_complete', array( __CLASS__, 'paid_invoice_order_manager' ), 55, 1 );

        // Add Ajax actions.
        add_action( 'wp_ajax_smartwoo_cancel_or_optout', array( __CLASS__, 'cancel_or_optout' ) );
        add_action( 'wp_ajax_smartwoo_asset_delete', array( 'SmartWoo_Service_Assets', 'ajax_delete' ) );
        add_action( 'wp_ajax_smartwoo_delete_service', 'smartwoo_delete_service' );
        add_action( 'wp_ajax_smartwoo_dashboard', array( $this, 'dashboard_ajax' ) );
        add_action( 'wp_ajax_smartwoo_dashboard_bulk_action', array( $this, 'dashboard_ajax_bulk_action' ) );
        add_action( 'wp_ajax_smartwoo_ajax_logout', array( __CLASS__, 'ajax_logout' ) );
        add_action( 'wp_ajax_smartwoo_table_bulk_action', array( __CLASS__, 'table_bulk_action' ) );
        add_action( 'wp_ajax_smartwoo_service_id_ajax', array( __CLASS__, 'ajax_generate_service_id' ) );
        add_action( 'wp_ajax_smartwoo_pro_button_action', array( __CLASS__, 'pro_button_action' ) );
        add_action( 'wp_ajax_nopriv_smartwoo_password_reset', array( __CLASS__, 'ajax_password_reset' ) );
        add_action( 'wp_ajax_smartwoo_admin_invoice_action', array( __CLASS__, 'admin_invoice_ajax_actions' ) );
        add_action( 'wp_ajax_smartwoo_reset_fast_checkout', array( __CLASS__, 'reset_fast_checkout' ) );
        add_action( 'wp_ajax_smartwoo_get_user_data', array( __CLASS__, 'ajax_get_user_data' ) );
        add_action( 'wp_ajax_smartwoo_manual_renew', array( __CLASS__, 'manual_renew_due' ) );

        add_action( 'wp_ajax_get_billing_details', array( __CLASS__, 'get_billing_details' ) );
        add_action( 'wp_ajax_get_client_details', array( __CLASS__, 'get_client_details' ) );
        add_action( 'wp_ajax_get_payment_details', array( __CLASS__, 'get_payment_details' ) );
        add_action( 'wp_ajax_get_account_logs', array( __CLASS__, 'get_account_logs' ) );
        add_action( 'wp_ajax_smartwoo_get_order_history', array( __CLASS__, 'get_order_history' ) );  
        add_action( 'wp_ajax_get_subscriptions', array( __CLASS__, 'fetch_user_subscriptions' ) );
        add_action( 'wp_ajax_get_edit_billing_form', array( __CLASS__, 'get_edit_billing_form' ) );
        add_action( 'wp_ajax_get_edit_client_form', array( __CLASS__, 'get_edit_client_form' ) );
        add_action( 'wp_ajax_get_edit_primary_payment_form', array( __CLASS__, 'get_edit_payment_form' ) );
        add_action( 'wp_ajax_get_edit_backup_payment_form', array( __CLASS__, 'get_edit_payment_form' ) );
        add_action( 'wp_ajax_smartwoo_save_payment_method', array( __CLASS__, 'save_payment_method' ) );
        add_action( 'wp_ajax_smartwoo_save_client_billing_details', array( __CLASS__, 'save_client_billing_details' ) );
        add_action( 'wp_ajax_smartwoo_save_client_details', array( __CLASS__, 'save_client_details' ) );

        add_action( 'smartwoo_admin_dash_footer', array( __CLASS__, 'sell_pro' ) );
        add_action( 'admin_notices', array( __CLASS__, 'notice_manager' ) );
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
        $smartwoo_pro_url = apply_filters( 'smartwoopro_purchase_link', smartwoo_get_callismart_tech_url( 'smart-woo-service-invoicing/' ) );

        /**
         * Plugin support link.
         */
        $support_url = apply_filters( 'smartwoo_support_url', 'https://support.callismart.com.ng/' );

        /**
         * Our github repository
         */
        $source_code    = apply_filters( 'smartwoo_source_code', 'https://github.com/CallismartLtd/smart-woo-service-invoicing/' );

        /**
         * Other Products URL
         */
        $other_products = apply_filters( 'smartwoo_other_products', 'https://apps.callismart.com.ng/' );

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
     * @param WC_Order $order
     */
    public function before_order_table( WC_Order $order ) {
        if ( is_account_page() ) {
            return;
        }
        $our_order  = apply_filters( 'smartwoo_order_details_buttons', smartwoo_check_if_configured( $order ) || $order->is_created_via( SMARTWOO ) );

        if ( $our_order ) {
            echo '<a href="' . esc_url( smartwoo_service_page_url() ) .'" class="sw-blue-button">Dashbaord</a>';
            $invoice_id       = $order->get_meta( '_sw_invoice_id' );

            if ( $invoice_id ) {
                echo '<a href="' . esc_url( smartwoo_invoice_preview_url( $invoice_id ) ) .'" class="sw-blue-button">Invoice</a>';

            }
            
            $smartwoo_orders    = SmartWoo_Order::extract_items( $order );
            $total_orders       = count( $smartwoo_orders );

            if ( ! empty( $total_orders ) ) {
                if ( $total_orders > 1 ) {
                    $numb = 1;
                    foreach ( $smartwoo_orders as $smartwoo_order ) {
                        echo '<a href="' . esc_url( smartwoo_invoice_preview_url( $smartwoo_order->get_invoice_id() ) ) .'" class="sw-blue-button">Invoice ' . esc_html( $numb ).'</a>';
                        $numb++;
                    }
                } else {
                    echo '<a href="' . esc_url( smartwoo_invoice_preview_url( $smartwoo_orders[0]->get_invoice_id() ) ) .'" class="sw-blue-button">Invoice</a>';
    
                }
                
            }

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


            $user = wp_signon( $credentials, true );

            if ( is_wp_error( $user ) ) {
                if ( 'incorrect_password' === $user->get_error_code() ) {
                    $message = 'Error: The password you entered for the username ' . sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) . ' is incorrect. <a id="sw-forgot-pwd-btn">Forgot password?</a>';
                    smartwoo_set_form_error( $message );
                } else {
                    smartwoo_set_form_error( $user->get_error_message() );
                }

                wp_redirect( esc_url_raw( wp_get_referer() ) );
                exit;

            } else {
                wp_redirect( esc_url_raw( isset( $_POST['redirect'] ) ? wp_unslash( $_POST['redirect'] ): smartwoo_service_page_url() ) );
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

        return self::$instance;
    }

    /**
     * Client asset download handler
     */
    public function asset_download() {
        if ( smartwoo_get_query_param( 'smartwoo_action' ) !== 'smartwoo_download' ) {
            return;
        }

        if ( ! wp_verify_nonce( smartwoo_get_query_param( 'token' ), 'smartwoo_download_nonce' ) ) {
            wp_die( esc_html__( 'Authentication failed', 'smart-woo-service-invoicing' ), 'Unathorized', 401 );
        }
    
        if ( ! is_user_logged_in() ) {
            return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'You must be logged in to access this page.' ), 'redirect' => smartwoo_service_page_url() ) );
        }
        
        $asset_id       = smartwoo_get_query_param( 'asset_id', 0 );
        $resource_id    = smartwoo_get_query_param( 'resource_id' );
        $service_id     = smartwoo_get_query_param( 'service_id' );
        if ( empty( $resource_id ) || empty( $service_id ) || empty( $asset_id ) ) {
            wp_die( esc_html__( 'URL construct is missing a required paramter.', 'smart-woo-service-invoicing' ), 'Missing Parameter', 400 );
        }

        // Check Asset validity via parent service.
        $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        if ( ! $service || ! $service->current_user_can_access() ) {
            wp_die( esc_html__( 'Invalid service subscription.', 'smart-woo-service-invoicing' ), 'Invalid Subscription', 404 );
        }

        if ( ! $service->owns_asset( $asset_id ) ) {
            wp_die( esc_html__( 'You do not have the required permission to access this asset', 'smart-woo-service-invoicing' ), 'Permission Failed', 403 );
        }

        // Check service status.
        $status = smartwoo_service_status( $service );
        if ( ! in_array( $status, smartwoo_active_service_statuses(), true ) ) {
            wp_die( esc_html( 'Service is not active, please renew it.', 'smart-woo-service-invoicing' ), 'Renewal Required', 403 );
        }

        $asset_data = SmartWoo_Service_Assets::return_data( $asset_id, $obj );

        if ( ! is_array( $asset_data ) || empty( $asset_data ) ) {
            wp_die( __( 'Malformed asset data, please contact us if you need further assistance', 'smart-woo-service-invoicing' ), 'Invalid Asset', 403 );
        }

        $resource_url   = array_key_exists( $resource_id, $asset_data ) ? $asset_data[$resource_id]: wp_die( 'File URL not found.', 404 );
        $asset_key      = $obj->get_key();
        $is_external    = $obj->is_external();
        $this->serve_file( $resource_url, wc_string_to_bool( $is_external ), $asset_key );
    }
    
    /**
     * Serve file for download.
     */
    private function serve_file( $resource_url, $is_external = false, $asset_key = '' ) {
        $resource_url   = sanitize_url( $resource_url, array( 'http', 'https' ) );
        $filename       = basename( wp_parse_url( $resource_url, PHP_URL_PATH ) );

        $cache_key  = 'smartwoo_file_sizes';
        $file_sizes = get_transient( $cache_key );

        // Ensure cache is an array
        if ( ! is_array( $file_sizes ) ) {
            $file_sizes = [];
        }

        if ( ! $is_external ) {
            
            $file_headers   = @get_headers( $resource_url, 1 );
        
            if ( ! $file_headers || strpos( $file_headers[0], '200' ) === false ) {
                wp_die( 'File not found.', 404 );
            }
        
            $content_type   = $file_headers['Content-Type'] ?? 'application/octet-stream';
            $content_length = $file_headers['Content-Length'] ?? 0;
            
            // Cache file size.
            $file_sizes[$resource_url] = SmartWoo_Service_Assets::format_file_size( $content_length );
            set_transient( $cache_key, $file_sizes );
        
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: ' . $content_type );
            header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . $content_length );
        
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

            $this->set_http_authorization( $asset_key, $resource_url );
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
            $file_size  = $wp_filesystem->size( $file );

            // Cache file size
            $file_sizes[$resource_url] = SmartWoo_Service_Assets::format_file_size( $file_size );
            set_transient( $cache_key, $file_sizes );

            if ( ! str_contains( $filename, '.' ) ) {
                $filename = basename( $file );
            }

            
            

            // Set headers
            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: ' . $mime_type );
            header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
            header( 'Content-Length: ' . $file_size );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Pragma: public' );
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

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
    private function set_http_authorization( $token = '', $resource_url = '' ) {
        add_filter( 'http_request_args', function( $args, $url ) use ( $token, $resource_url ) {
            if ( $resource_url === $url ) {
                // wp_die( $resource_url );
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

        $action = smartwoo_get_query_param( 'real_action' ) ?: wp_die();

        if ( ! in_array( $action, $allowed_actions, true ) ){
            wp_send_json_error( array( 'message' => 'action is not allowed.' ) );
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

        $limit  = smartwoo_get_query_param( 'limit', 10 );
        $paged  = smartwoo_get_query_param( 'paged', 1 );

        /**
         * Send json data for table structures.
         */
        if ( 'sw_search' === $action ) {
            $all_services   = SmartWoo_Service_Database::search();
            $total_services = count( $all_services );
            
        } elseif ( 'all_services_table' === $action ) {    
            $all_services   = SmartWoo_Service_Database::get_all( $paged, $limit );
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

        if ( ! empty( $all_services ) ) {
            foreach ( $all_services as $service ) {
                $data[] = array( $service->get_name(), $service->get_service_id(), smartwoo_service_status( $service ) );
                $row_names[] = $service->get_service_id();
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
     * Dashboard bulk action handler.
     */
    public function dashboard_ajax_bulk_action() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have the required permission to perform this action' ) );

        }

        add_filter( 'smartwoo_is_frontend', '__return_false' );
        $allowed_actions    = array(
            'auto_calc',
            'Active',
            'Active (NR)',
            'Suspended',
            'Cancelled',
            'Due for Renewal',
            'Expired',
            'delete'
        );
        
        $action = isset( $_POST['real_action'] ) ? sanitize_text_field( wp_unslash( $_POST['real_action'] ) ) : false;

        if ( ! $action ) {
            wp_send_json_error( array('message' => 'Real action missing.' ) );
        }
        
        if ( ! in_array($action, $allowed_actions, true ) ) {
            wp_send_json_error( array( 'message' => 'Action is not allowed' ) );
        }
        $service_ids    = isset( $_POST['service_ids'] ) && is_array( $_POST['service_ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['service_ids'] ) ) : array();
        
        if ( empty( $service_ids ) ) {
            wp_send_json_error( array( 'message' => 'No service ID was provided.' ) );
        }

        $service_noun = ( count( $service_ids ) > 1 ) ? "Services": "Service";
        if ( 'auto_calc' === $action ) {
            $message        = "Automatic calculation applied to the selected " . $service_noun;
            $field_value    = null;

        } elseif ( 'Active' === $action ) {
            $message        = $service_noun . " has been activated.";
            $field_value    = 'Active';
        } elseif ( 'Active (NR)' === $action ) {
            $message        = $service_nounce . " has been activated but will not renew on next payment date";
            $field_value    = 'Active (NR)';
        } elseif ( 'Suspended' === $action ) {
            $message        = $service_noun . " has been suspended.";
            $field_value    = 'Suspended';
        } elseif ( 'Cancelled' === $action ) {
            $message        = $service_noun . " has been cancelled";
            $field_value    = 'Cancelled';
        } elseif ( 'Due for Renewal' === $action ) {
            $message        = $service_noun . " now Due for renewal";
            $field_value    = 'Due for Renewal';
        } elseif ( 'Expired' === $action ) {
            $message        = $service_noun . " has been expired";
            $field_value    = 'Expired';
        } elseif ( 'delete' === $action ) {
            $message        = $service_noun . " has been deleted";
        }

        
        foreach ( $service_ids as $service_id ) {
            if ( 'delete' !== $action ) {
                SmartWoo_Service_Database::update_service_fields( $service_id, array('status' => $field_value ) );
                continue;
            }
            
            if ( 'delete' === $action ) {
                SmartWoo_Service_Database::delete_service( $service_id );
            }
        }

        wp_send_json_success( array('message' => $message ) );
    }

    /**
     * Relay Smart Woo Table Ajax action to appropriate handler.
     */
    public static function table_bulk_action() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have the required permission to perform this action' ) );
        }

        $selected_action = isset( $_POST['selected_action'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_action'] ) ) : false;
        $allowed_table_action = apply_filters( 'smartwoo_allowed_table_actions',
            array()
        );

        if ( ! in_array( $selected_action, $allowed_table_action, true ) ) {
            wp_send_json_error( array( 'message' => 'Action is not allowed' ), 400 );
        }

        $action     = isset( $_POST['real_action'] ) ? sanitize_text_field( wp_unslash( $_POST['real_action'] ) ) : false;
        $payload    = isset( $_POST['payload'] ) ? sanitize_text_field( wp_unslash( $_POST['payload'] ) ) : array();
        
        if ( has_action( 'smartwoo_' . $action ) ) {
            do_action( 'smartwoo_' . $action, $selected_action, explode( ',', $payload ) );
            wp_die();
        }

        wp_send_json_error( array( 'message' => 'Invalid Action Handler' ) );

    }

    /**
     * Admin invoice ajax action handler.
     * 
     * @since 2.2.3
     */
    public static function admin_invoice_ajax_actions() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have the required permission to perform this action' ) );

        }

        add_filter( 'smartwoo_is_frontend', '__return_false' );
        $allowed_actions    = array(
            'mark_paid',
            'mark_unpaid',
            'mark_cancelled',
            'delete',
            'checkout_order_pay',
            'paymen_url',
            'send_new_email',
            'send_payment_reminder'
        );

        $real_action = smartwoo_get_query_param( 'real_action' );
        
        if ( ! in_array( $real_action, $allowed_actions, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid action' ) );
        }

        $invoice_id = smartwoo_get_query_param( 'invoice_id', false ) ?: wp_send_json_error( array( 'message' => 'Missing Invoice ID' ) );
        $invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
        if ( ! $invoice ) {
            wp_send_json_error( array( 'message' => 'This invoice does not exist.' ) );
        }

        switch( $real_action ) {
            case 'send_new_email':
                $mail_sent  = SmartWoo_New_Invoice_Mail::send_mail( $invoice );
                $response   = 'Email not sent <span class="dashicons dashicons-no" style="color: red;"></span>';

                if ( $mail_sent ) {
                    $response = 'Email is has been sent <span class="dashicons dashicons-yes-alt" style="color: red;"></span>';
                }
                break;
            case 'paymen_url':
                $response = esc_url_raw( $invoice->payment_link() );
                break;
            case 'checkout_order_pay':
                $response = 'This invoice does not have any pending order';
                if ( ( $invoice->get_order() && 'pending' === $invoice->get_order()->get_status() ) || 'unpaid' === $invoice->get_status() ){
                    $response = esc_url_raw( $invoice->pay_url() );
                }
                break;
            case 'send_payment_reminder':
                $mail_sent = SmartWoo_Invoice_Payment_Reminder::send_mail( $invoice );
                $response   = 'Email not sent <span class="dashicons dashicons-no" style="color: red;"></span>';

                if ( $mail_sent ) {
                    $response = 'Email is has been sent <span class="dashicons dashicons-yes-alt" style="color: red;"></span>';
                }
                break;
        }

        if ( ! empty( $response ) ) {
            wp_send_json_success( array( 'message' => $response ), 200 );
        }

        wp_send_json_error( array( 'message' => 'Unable to handle to request at the moment.' ) );
    }

    /**
     * Ajax get user data.
     */
    public static function ajax_get_user_data() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'You do not have the required permission to perform this action' ) );
        }

        $user_id    = smartwoo_get_query_param( 'user_id', false ) ?: wp_send_json_error( array( 'message' => 'Missing user ID' ) );
        $user_data  = new WC_Customer( $user_id );
        if ( ! $user_data->get_id() ) {
            wp_send_json_error( array( 'message' => 'User not found' ) );
        }

        $response = array(
            'full_name'     => $user_data->get_billing_first_name() . ' ' . $user_data->get_billing_last_name(),
            'first_name'    => $user_data->get_billing_first_name(),
            'last_name'     => $user_data->get_billing_last_name(),
            'email'         => $user_data->get_email(),
            'billing_email' => smartwoo_get_client_billing_email( $user_data->get_id() ),
            'billing_company'   => $user_data->get_billing_company(),
            'billing_phone'     => $user_data->get_billing_phone(),
            'formated_address'  => smartwoo_get_user_billing_address( $user_data->get_id() ),
            'avatar_url'        => get_avatar_url( $user_data->get_id() )
        );
        wp_send_json_success( array( 'user' => $response ), 200 );
    }

    /**
     * Logout ajax handler
     * 
     * @since 2.0.13
     */
    public static function ajax_logout() {
        check_ajax_referer( 'smart_woo_nonce', 'security' );
        wp_logout();
        wp_send_json_success();
    }

    /**
     * Handles service renewal when the client clicks the renew button on
     * Service Details page
     */
    public static function manual_renew_due() {
        if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }            
        $service_id = smartwoo_get_query_param( 'service_id' );
        $service    = SmartWoo_Service_Database::get_service_by_id( $service_id );

        if ( ! $service || ! $service->current_user_can_access() ) {
            wp_send_json_error( array( 'message' => 'Error: Service does not exist.' ) );
        }

        $service_status = smartwoo_service_status( $service );
        if ( 'Due for Renewal' === $service_status || 'Expired' === $service_status || 'Grace Period' === $service_status ) {
            $invoice_type   = 'Service Renewal Invoice';
            $unpaid_invoice = SmartWoo_Invoice_Database::get_outstanding_invoice( $service_id );
            
            if ( $unpaid_invoice ) {
                wp_send_json_success( array( 'message' => __( 'Please pay outstanding invoice', 'smart-woo-service-invoicing' ),'redirect_url' => smartwoo_invoice_preview_url( $unpaid_invoice->get_invoice_id() ) ) );
            }

            $date_due       = ( 'Expired' === $service_status ) ? SmartWoo_Date_Helper::create_from( $service->get_end_date() ): SmartWoo_Date_Helper::create_from( $service->get_next_payment_date() );
            $date_due       = $date_due->format( 'Y-m-d H:i:s' );
            $inv_args	= array(
                'user_id'		=> $service->get_user_id(), 
                'product_id'	=> $service->get_product_id(), 
                'status'		=> 'unpaid',
                'type'			=> 'Service Renewal Invoice', 
                'service_id'	=>  $service->get_service_id(),
                'fee' 			=> 0,
                'date_due'		=> $date_due,
            );

            $invoice = smartwoo_create_invoice( $inv_args );

            if ( $invoice ) {
                $client_payment_options = smartwoo_get_user_payment_options( $service->get_user_id() );
                if ( $client_payment_options['primary'] ) {
                    $invoice->set_payment_method( $client_payment_options['primary'] );
                    if ( $order = $invoice->get_order() ) {
                        $order->set_payment_method( $client_payment_options['primary'] );
                        $order->save();

                    }
                    $invoice->save();
                } else if ( $client_payment_options['backup'] ) {
                    $invoice->set_payment_method( $client_payment_options['backup'] );
                    if ( $order = $invoice->get_order() ) {
                        $order->set_payment_method( $client_payment_options['backup'] );
                        $order->save();

                    }
                    $invoice->save();
                }
                /**
                 * Fires when the client generates a renewal invoice from the portal.
                 * 
                 * @param SmartWoo_Invoice $invoice The invoice object
                 * @param SmartWoo_Service $service The service subscription object
                 */
                do_action( 'smartwoo_manual_invoice_created', $invoice, $service );
                wp_send_json_success( array( 'message' => 'Invoice created, redirecting to pay...','redirect_url' => $invoice->pay_url() ) );
            }
        }
    }

    /**
     * Handle the payment link, verify the token, log in the user, and process the payment.
     */
    public static function payment_link_handler() {
        
        if ( smartwoo_get_query_param( 'action' ) !== 'sw_invoice_payment' ) {
            return; // Bail early.
        }
        $token          = smartwoo_get_query_param( 'token', false ) ?: wp_die( 'Missing token', 'Payment Error', array( 'response' => 400 ) );
        $invoice_id     = smartwoo_get_query_param( 'invoice_id' ) ?: wp_die( 'Missing Invoice ID', 'Payment Error', array( 'response' => 400 ) );
        $user_email     = smartwoo_get_query_param( 'user_email' ) ?: wp_die( 'Missing Email', 'Payment Error', array( 'response' => 400 ) );
        $payment_info   = smartwoo_verify_token( $token );

        if ( ! $payment_info || ! is_array( $payment_info ) ) {
            wp_die( 'Invalid or expired link', 'Invalid Link', array( 'response' => 401 ) );
        }
        
        // Extract relevant information.
        $known_invoice_id = sanitize_text_field( wp_unslash( $payment_info['invoice_id'] ) );
        $known_user_email = sanitize_text_field( wp_unslash( $payment_info['user_email'] ) );

        if ( ! hash_equals( $known_invoice_id, $invoice_id ) || ! hash_equals( $known_user_email, $user_email ) ) {
            wp_die( 'Invalid Credentials', 'Payment Error', array( 'response' => 401 ) );
        }
        
        $invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

        if ( ! $invoice ) {
            wp_die( 'Invalid or deleted invoice.', 'Invalid Invoice', array( 'response' => 404 ) );
        }

        $redirect_url  = $invoice->preview_url( 'frontend' );

        // Process guest invoice at this point.
        if ( $invoice->is_guest_invoice() ) {

            if ( $invoice->needs_payment() ) {
                $redirect_url = $invoice->pay_url();
            }

            wp_safe_redirect( $redirect_url );
            exit;
        }
        
        $user   = get_user_by( 'email', $user_email );

        if ( ! $user ) {
            wp_die( 'User not found', 403 );
        }

        $invoice_status  = $invoice->get_status();

        if ( ! hash_equals( strval( $invoice->get_user_id() ), strval( $user->ID ) ) ) {
            wp_die( 'You don\'t have the required permission to pay for this invoice, contact us if you need help', 403 );
        }

        if ( $invoice->needs_payment() ) {
            $redirect_url = $invoice->pay_url();
        }
        
        // Conditions has been met, user should be logged in.
        wp_set_current_user( $user->ID, $user->user_login );
        wp_set_auth_cookie( $user->ID );
        do_action( 'wp_login', $user->user_login, $user );
        
        wp_safe_redirect( $redirect_url );
        exit;
        
    }

    /**
     * Handle Quick Action button on the Service Details page (frontend).
     *
     * This function is hooked into WordPress template redirection to handle actions related
     * to service cancellation or billing cancellation based on the 'action' parameter in the URL.
     */

    public static function cancel_or_optout() {

        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
            wp_die( -1, 401 );
        }

        $action             = isset( $_POST['selected_action'] ) ? sanitize_key( $_POST['selected_action'] ) : '';
        $ajax_service_id    = isset( $_POST['service_id'] ) ? sanitize_key( $_POST['service_id'] ) : '';
        
        if ( empty( $action) && empty( $ajax_service_id ) ) {
            wp_die( -1, 400 );

        }

        $service	= SmartWoo_Service_Database::get_service_by_id( sanitize_text_field( $ajax_service_id ) );

        if ( ! $service || ! $service->current_user_can_access() ) {
            wp_die( -1, 404 );
        }
        
        $user_id  				= get_current_user_id();
        $service_id				= $service->get_service_id();
        $next_service_status	= null;
        $user_cancelled_service	= false;
        $user_opted_out			= false;

        if ( 'sw_cancel_service' === $action ) {
            $next_service_status ='Cancelled';
            $user_cancelled_service = true;
        } elseif ( 'sw_cancel_billing' === $action ) {
            $next_service_status ='Active (NR)';
            $user_opted_out = true;

        }

        SmartWoo_Service_Database::update_service_fields( $service_id, array( 'status' => $next_service_status ) );

        if ( $user_cancelled_service ) {

            /**
             * @action_hook smartwoo_user_cancelled_service Fires When service is cancelled.
             *              @param string $service_id
             *              @param SmartWoo_Service $service @since 2.2.0
             * @action_hook smartwoo_service_deactivated Fires when Service is deactivated.
             *              @param SmartWoo_Service $service
             *                                           
             */
            do_action( 'smartwoo_user_cancelled_service', $service_id, $service );
            do_action( 'smartwoo_service_deactivated', $service );

        } elseif ( $user_opted_out ) {
            do_action( 'smartwoo_user_opted_out', $service_id ); 
        }
    }

    /**
     * Invoice order payment handler.
     *
     * @param int $order_id    The paid invoice order.
     */
    public static function paid_invoice_order_manager( $order_id ) {
       
        $order                  = wc_get_order( $order_id );
        $invoice_id             = $order->get_meta( '_sw_invoice_id' );
        $is_new_service_order   = $order->get_meta( '_smartwoo_is_service_order' );

        // Stop if not our order.
        if ( ! $is_new_service_order && empty( $invoice_id ) ) {
            return;
        }
        // Prevent multiple function execution on single load.
        if ( defined( 'SMARTWOO_PAID_INVOICE_MANAGER' ) && SMARTWOO_PAID_INVOICE_MANAGER ) {
            return;
        }
        define( 'SMARTWOO_PAID_INVOICE_MANAGER', true );

        /**
         * Handle new service order differently.
         */
        if ( $is_new_service_order ) {
            self::new_service_order_paid( $order );
            return;
        }

        $invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

        // Bail early when invoice doesn't exists.
        if ( empty( $invoice ) ) {
            return;
        }

        $invoice_type = $invoice->get_type();
        if ( ! in_array( $invoice_type, smartwoo_supported_invoice_types(), true ) ) {
            smartwoo_mark_invoice_as_paid( $invoice_id );
            return;
        }

        $service_id = $invoice->get_service_id();

        // If Service ID is available, this indicates an invoice for existing service.
        if ( ! empty( $service_id ) ) {
            $service_status = smartwoo_service_status( $service_id );
            /**
             * Determine if the invoice is for the renewal of a Due service.
             * Only invoices for services on this status are considered to be for renewal.
             */
            if ( 'Due for Renewal' === $service_status || 'Grace Period' === $service_status && 'Service Renewal Invoice' === $invoice_type ) {

                self::renew_service( $service_id, $invoice_id );
                
                /**
                 * Determine if the invoice is for the reactivation of an Expired service.
                 * Only invoices for services on this status are considered to be for reactivation.
                 */
            } elseif ( $service_status === 'Expired' && $invoice_type === 'Service Renewal Invoice' ) {
                
                self::activate_expired_service( $service_id, $invoice_id );
                
            }

            /**
             * Fires when existing service has a paid invoice which is not handled here.
             * 
             * @since 1.0.4
             */
            do_action( 'smartwoo_invoice_for_existing_service_paid', $service_id, $invoice_id, $invoice_type  );
        }
    }

    /**
     * Renew an active service.
     *
     * This performs service renewal, relying on the confirmation that
     * the invoice is paid. If the invoice is
     * not paid, the function will return early.
     *
     * @param string $service_id ID of the service to be renewed.
     * @param string $invoice_id ID of the invoice related.
     * @param bool $strict Whether invoice status should be strictly checked.
     * 
     * @return bool True on success, false otherwise.
     */
    public static function renew_service( $service_id, $invoice_id, $strict = true ) {
        $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        $invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
        // Mark the invoice as paid before renewing the service.
        $invoice_is_paid = smartwoo_mark_invoice_as_paid( $invoice_id );

        if ( ! $service ) {
            /**
             * Fires when service renewal fails
             * 
             * @param string $reason Reason for the failure.
             * @param SmartWoo_Service|false The service subscription object or false
             */
            do_action( 'smartwoo_service_renewal_failed', 'Service subscription does not exists.', $service  );
            return false;
        }

        if ( $strict && ! $invoice_is_paid ) {
            /**
             * Fires when service renewal fails
             * 
             * @param string $reason Reason for the failure.
             * @param SmartWoo_Service The service subscription object
             */
            do_action( 'smartwoo_service_renewal_failed', 'Manual review required! The system could not mark the invoice "' . $invoice_id . '" associated with this renewal as paid.', $expired_service  );
            return false;
        }

        /**
         * Add Action Hook Before Updating Service Information.
         * 
         * @param SmartWoo_Service $service
         */
        do_action( 'smartwoo_before_service_renew', $service );

        $old_end_date_ts    = SmartWoo_Date_Helper::create_from( $service->get_end_date() )->get_timestamp();
        $interval           = SmartWoo_Date_Helper::get_billing_cycle_interval( $service->get_billing_cycle() );

        $new_start_date         = SmartWoo_Date_Helper::create_from_timestamp( $old_end_date_ts );
        $new_end_date           = SmartWoo_Date_Helper::create_from_timestamp( strtotime( $interval, $old_end_date_ts ) );
        $new_next_payment_date  = SmartWoo_Date_Helper::calculate_next_payment_date( 
            $service->get_next_payment_date(),
            $service->get_end_date(),
            $new_end_date->format( 'Y-m-d')
        );
        
        $service->set_start_date( $new_start_date->format( 'Y-m-d') );
        $service->set_next_payment_date( $new_next_payment_date->format( 'Y-m-d') );
        $service->set_end_date( $new_end_date->format( 'Y-m-d') );
        $service->set_status( null ); // Renewed service is calculated automatically.
        $updated = SmartWoo_Service_Database::update_service( $service );

        if ( $updated ) {

            /**
             * Fires when a service subscription is renewed.
             */
            do_action( 'smartwoo_service_renewed', $service );

            /**
             * Fires when the status of a subscription becomes active
             * 
             * @param SmartWoo_Service $service the service subscription object.
             */
            do_action( 'smartwoo_service_activated', $service );
            return true;            
        }

        /**
         * Fires when service renewal fails
         * 
         * @param string $reason Reason for the failure.
         * @param SmartWoo_Service|false The service subscription object or false
         */
        do_action( 'smartwoo_service_renewal_failed', 'Unable to update records in the database.', $service  );
            
        
    }

    /**
     * Activate an expired service.
     *
     * This performs service renewal, relying on the confirmation that
     * the invoice ID provided in the third parameter is paid. If the invoice is
     * not paid, the function will return early.
     *
     * @param string $service_id ID of the service to be renewed.
     * @param string $invoice_id ID of the invoice related to the service renewal.
     * @param bool $strict Whether invoice status should be strictly checked.
     * 
     * @return bool True on success, false otherwise.
     */
    public static function activate_expired_service( $service_id, $invoice_id, $strict = true ) {
        $expired_service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        $invoice         = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
        $invoice_is_paid = smartwoo_mark_invoice_as_paid( $invoice_id );

        if ( ! $expired_service ) {
            /**
             * Fires when service renewal fails
             * 
             * @param string $reason Reason for the failure.
             * @param SmartWoo_Service|false The service subscription object or false
             */
            do_action( 'smartwoo_service_renewal_failed', 'Service subscription does not exists.', $expired_service  );
            return false;
        }

        if ( $strict && ! $invoice_is_paid ) {
            /**
             * Fires when service renewal fails
             * 
             * @param string $reason Reason for the failure.
             * @param SmartWoo_Service The service subscription object
             */
            do_action( 'smartwoo_service_renewal_failed', 'Manual review required! The system could not mark the invoice "' . $invoice_id . '" associated with this renewal as paid.', $expired_service  );
            return false;
        }

        /**
         * Fires before an expired service is reactivated.
         * 
         * @param SmartWoo_Service $expired_service
         */ 
        do_action( 'smartwoo_before_activate_expired_service', $expired_service );

        $order  = $invoice ? $invoice->get_order() : false;
        if ( ! $order && $strict ) {
            /**
             * Fires when service renewal fails
             * 
             * @param string $reason Reason for the failure.
             * @param SmartWoo_Service The service subscription object
             */
            do_action( 'smartwoo_service_renewal_failed', 'Order associated with the renewal invoice does not exists.', $expired_service  );
            return false;
        }

        $interval       = SmartWoo_Date_Helper::get_billing_cycle_interval( $expired_service->get_billing_cycle() );
        $new_start_date =  $order ? $order->get_date_paid() : SmartWoo_Date_Helper::create_from_timestamp( time() );
        
        $new_end_date           = SmartWoo_Date_Helper::create_from_timestamp( strtotime( $interval, $new_start_date->getTimestamp() ) );
        $new_next_payment_date  = SmartWoo_Date_Helper::calculate_next_payment_date( 
            $expired_service->get_next_payment_date(),
            $expired_service->get_end_date(),
            $new_end_date->format( 'Y-m-d')
        );

        $expired_service->set_start_date( $new_start_date->format( 'Y-m-d' ) );
        $expired_service->set_next_payment_date( $new_next_payment_date->format( 'Y-m-d' ) );
        $expired_service->set_end_date( $new_end_date->format( 'Y-m-d' ) );
        $expired_service->set_status( null );
        $updated = SmartWoo_Service_Database::update_service( $expired_service );

        if ( $updated ) {
            /**
             * Fires after an expired service is renewed.
             * 
             * @param SmartWoo_Service $expired_service.
             */
            do_action( 'smartwoo_expired_service_activated', $expired_service );

            /**
             * Fires when the status of a subscription becomes active
             * 
             * @param SmartWoo_Service $expired_service the service subscription object.
             */
            do_action( 'smartwoo_service_activated', $expired_service );
            return true;
        }

        /**
         * Fires when service renewal fails
         * 
         * @param string $reason Reason for the failure.
         * @param SmartWoo_Service The service subscription object
         */
        do_action( 'smartwoo_service_renewal_failed', 'Unable to update records in the database.', $expired_service  );

        return false;  
    }

    /**
     * Perform action when a new service purchase is complete
     *
     * @param WC_Order $order The order object.
     */
    public static function new_service_order_paid( WC_Order $order ) {
        $smartwoo_orders = SmartWoo_Order::extract_items( $order );

        foreach( $smartwoo_orders as $sw_order ) {
            $invoice_id = $sw_order->get_invoice_id();
            smartwoo_mark_invoice_as_paid( $invoice_id );
        }

        /**
         * Fires when a new service purchase is complete.
         * 
         * @param SmartWoo_Order[] $smartwoo_orders An array of Smart Woo Order Objects added @since 2.4.2
         */
        do_action( 'smartwoo_new_service_purchase_complete', $smartwoo_orders );
    }

    /**
     * Handle admin invoice download
     */
    public static function admin_download_invoice() {
        $token = smartwoo_get_query_param( '_sw_download_token', false );
        if ( $token && wp_verify_nonce( $token, '_sw_download_token' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( 'You do not have the required permision to download this invoice' );
            }

            $invoice_id = smartwoo_get_query_param( 'invoice_id' ) ?: wp_die( 'Missing Invoice ID' );
            $invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

            if ( empty( $invoice ) ) {
                wp_die( 'Invalid or deleted invoice.' );
            }

            smartwoo_pdf_invoice_template( $invoice_id );
            exit;
        }

    }

    /**
     * Check if the cart is configured
     */
    public function is_configured_cart() {
        $cart_items = WC()->cart->get_cart();
        $check = false;

        foreach( $cart_items as $item ) {
            if ( isset( $item['service_name'] ) || isset( $item['service_url'] ) ){
                $check = true;
                break;
            }
        }

        return $check;
    }

    /**
     * Mail template preview
     */
    public static function mail_preview() {
        $nonce = smartwoo_get_query_param( '_wpnonce', false );
        if ( ! $nonce || ! wp_verify_nonce( $nonce ) ) {
            wp_die( 'Action failed basic authentication', 'Permission Denied', array( 'response' => 401 ) );
        }
        $template = smartwoo_get_query_param( 'temp' ) ?: wp_die( 'Please provide template' );
        $doing_invoice = false;
        $doing_service = false;
        switch( $template ) {
            case 'smartwoo_payment_reminder_to_client':
                $temp_class_name    = 'SmartWoo_Invoice_Payment_Reminder';
                $obj_class_name     = 'SmartWoo_Invoice';
                $doing_invoice      = true;
                break;
            case 'smartwoo_new_invoice_mail':
                $temp_class_name    = 'SmartWoo_New_Invoice_Mail';
                $obj_class_name     = 'SmartWoo_Invoice';
                $doing_invoice      = true;
                break;
            case 'smartwoo_invoice_paid_mail':
                $temp_class_name    = 'SmartWoo_Invoice_Paid_Mail';
                $obj_class_name     = 'SmartWoo_Invoice';
                $doing_invoice      = true;
                break;
            case 'smartwoo_cancellation_mail_to_user':
            case 'smartwoo_service_cancellation_mail_to_admin':
                $temp_class_name    = 'SmartWoo_Cancelled_Service_Mail';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
            case 'smartwoo_service_opt_out_mail':
                $temp_class_name    = 'SmartWoo_Service_Optout_Mail';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
            case 'smartwoo_service_expiration_mail':
            case 'smartwoo_service_expiration_mail_to_admin':
                $temp_class_name    = 'SmartWoo_Service_Expiration_Mail';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
            case 'smartwoo_renewal_mail':
                $temp_class_name    = 'SmartWoo_Service_Reactivation_Mail';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
            case 'smartwoo_new_service_order':
                $temp_class_name    = 'Smartwoo_New_Service_Order';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
            case 'smartwoo_service_processed_mail':
                $temp_class_name    = 'SmartWoo_Service_Processed_Mail';
                $obj_class_name     = 'SmartWoo_Service';
                $doing_service      = true;
                break;
        }

        if ( $doing_invoice ) {
            $invoice = new $obj_class_name();
            $invoice->set_invoice_id( smartwoo_generate_invoice_id() );
            $invoice->set_user_id( get_current_user_id() );
            $invoice->set_product_id( $temp_class_name::get_random_product_id() );
            $invoice->set_amount( wp_rand( 200, 500 ) );
            $invoice->set_total( wp_rand( 200, 500 ) );
            $invoice->set_service_id( smartwoo_generate_service_id( 'Awesome Service' ) );
            $invoice->set_status( 'unpaid' );
            $invoice->set_date_created( 'now' );
            $invoice->set_date_paid( 'now' );
            $invoice->set_billing_address( smartwoo_get_client_billing_email( get_current_user_id() ) );
            $invoice->set_type( 'Billing' );
            $invoice->set_fee( wp_rand( 200, 500 ) );
            $invoice->set_date_due( 'now' );
            $temp   = new $temp_class_name( $invoice );
            $temp->preview_template();
        } elseif ( $doing_service ) {
            $service    = new $obj_class_name();
            $service->set_user_id( get_current_user_id() );
            $service->set_product_id( $temp_class_name::get_random_product_id() );
            $service->set_service_id( smartwoo_generate_service_id( 'Awesome Service' ) );
            $service->set_name( 'Awesome Service' );
            $service->set_service_url( site_url() );
            $service->set_type( 'Web Service' );
            $service->set_start_date( current_time( 'mysql' ) );
            $service->set_end_date( wp_date( 'Y-m-d', time() + MONTH_IN_SECONDS ) );
            $service->set_next_payment_date( wp_date( 'Y-m-d', strtotime( 'tomorrow' ) ) );
            $service->set_billing_cycle( 'Monthly' );
            $service->set_status( 'Active' );
            if ( 'smartwoo_cancellation_mail_to_user' === $template ) {
                $service->set_status( 'Cancelled' );
                $temp   = new $temp_class_name( $service, 'user' );
            } elseif( 'smartwoo_service_cancellation_mail_to_admin' === $template ) {
                $temp   = new $temp_class_name( $service );
                $service->set_status( 'Cancelled' );
            } elseif( 'smartwoo_service_opt_out_mail' === $template ) {
                $service->set_status( 'Active(NR)' );
                $temp   = new $temp_class_name( $service );
            } elseif( 'smartwoo_service_expiration_mail' === $template ) {
                $service->set_status( 'Expired' );
                $temp   = new $temp_class_name( $service, 'user' );
            } elseif( 'smartwoo_service_expiration_mail_to_admin' === $template ) {
                $service->set_status( 'Expired' );
                $services = array( $service );
                $service2    = new $obj_class_name();
                $service2->set_user_id( get_current_user_id() );
                $service2->set_product_id( $temp_class_name::get_random_product_id() );
                $service2->set_service_id( smartwoo_generate_service_id( 'Awesome Service 2' ) );
                $service2->set_name( 'Awesome Service 2' );
                $service2->set_service_url( site_url() );
                $service2->set_type( 'Web Service' );
                $service2->set_start_date( current_time( 'mysql' ) );
                $service2->set_end_date( wp_date( 'Y-m-d', time() + MONTH_IN_SECONDS ) );
                $service2->set_next_payment_date( wp_date( 'Y-m-d', strtotime( 'tomorrow' ) ) );
                $service2->set_billing_cycle( 'Monthly' );
                $service2->set_status( 'Expired' );
                $services[] = $service2;
                $temp   = new $temp_class_name( $services, 'admin' );
            } elseif( 'smartwoo_new_service_order' === $template ) {
                $orders = array(
                    $temp_class_name::create_pseudo_order(),
                    // $temp_class_name::create_pseudo_order(),
                    // $temp_class_name::create_pseudo_order()
                );
                
                $temp   = new $temp_class_name( $orders );

            } else {
                $temp   = new $temp_class_name( $service );

            }

            $temp->preview_template();
        } else {
            has_action( 'smartwoo_' . $template  . '_preview' ) ? do_action( 'smartwoo_' . $template  . '_preview' ) : wp_die( 'Email template does not exists', 'Template not found' );
        }
    
    }
    /**
     * Generarte service ID via ajax.
     */
    public static function ajax_generate_service_id() { 
        check_ajax_referer(  'smart_woo_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( -1, 401 );
        }
        $service_name = isset( $_POST['service_name']) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ): '';
        $id = smartwoo_generate_service_id( $service_name );
        wp_send_json( $id );
    }

    /**
     * Ajax Password Reset handler
     */
    public static function ajax_password_reset() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication' ) );
        }

        $user_login = smartwoo_get_query_param( 'user_login', false ) ?: wp_die( -1, 400 );
        if ( is_wp_error( $user = retrieve_password( $user_login ) ) ) {
            wp_send_json_error( array( 'message' => $user->get_error_message() ) );
        }

        wp_send_json_success( array( 'message' => __( 'Password Reset email sent', 'smart-woo-service-invoicing' ) ), 200 );
    }

    /**
     * Sell Smart Woo Pro with 80% discount promo (limited to first 30 sign-ups).
     */
    public static function sell_pro() {
        if ( self::pro_is_installed() ) {
            return;
        }

        $show_notification = true;
        $user_options      = get_option( 'smartwoo_pro_sell_intrest' );

        if ( is_array( $user_options ) ) {
            $value = wp_unslash( $user_options['user_option'] );
            $time  = intval( $user_options['time'] );

            if ( 'dismiss_fornow' === $value && $time + ( 3 * DAY_IN_SECONDS ) > time() ) {
                $show_notification = false;
            } elseif ( 'remind_later' === $value && $time + DAY_IN_SECONDS > time() ) {
                $show_notification = false;
            }
        }

        // Stop showing after September 6, 2025
        $today = current_time( 'timestamp' );
        $end   = strtotime( '2025-09-06 00:00:00' );

        if ( $today >= $end || ! $show_notification ) {
            return;
        }
        ?>
            <div class="sw-dash-pro-sell-bg">
                <div class="sw-pro-sell-content">
                    <h2>🔥 Launch Promo: Smart Woo Pro at 80% OFF</h2>
                    <p>We’re celebrating the launch of Smart Woo Pro with an exclusive early-bird offer. The first <strong>30 sign-ups</strong> get a massive <strong>80% discount</strong>.</p>
                    <p>Hurry — once the 30 spots are gone or after <strong>September 6</strong>, this deal disappears!</p>

                    <div class="sw-pro-sell-buttons">
                        <button id="smartwoo-pro-dismiss-fornow" style="border: solid .5px red;">Dismiss</button>
                        <button id="smartwoo-pro-remind-later" style="border: solid .5px blue;">Remind me later</button>
                        <button class="sw-upgrade-to-pro" style="border: solid .5px green;">Upgrade Now</button>
                    </div>

                    <div id="sw-loader"></div>
                </div>
            </div>
        <?php
    }



    /**
     * Handle pro upsell button actions
     */
    public static function pro_button_action() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
        }
        $allowed_actions    = array( 'remind_later', 'dismiss_fornow' );
        $action             = smartwoo_get_query_param( 'real_action' );
        
        if ( ! in_array( $action, $allowed_actions, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid request.' ) );

        }
        $message        = ( 'dismiss_fornow' === $action ) ? 'Dismissed' : 'Reminder has been set.';
        $user_option    = ( 'dismiss_fornow' === $action ) ? 'dismiss_fornow' : 'remind_later';
        update_option( 'smartwoo_pro_sell_intrest', array( 'user_option' => $user_option, 'time' => time() ) );

        wp_send_json_success( array( 'message' => $message ) );
    }

    /**
     * Check whether pro version is installed.
     * 
     * @return bool True if Smart Woo Pro is installed and activated, false otherwise.
     */
    public static function pro_is_installed() {
        return class_exists( 'SmartWooPro', false );
    }

    /**
     * Ajax callback for user billing details component in the frontend.
     */

    public static function get_billing_details() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        $user_id 	= get_current_user_id();
        $user		= new WC_Customer( $user_id );
        
        // Get additional customer details
        $billingFirstName = $user->get_billing_first_name();
        $billingLastName  = $user->get_billing_last_name();
        $company_name     = $user->get_billing_company();
        $email            = $user->get_billing_email();
        $phone            = $user->get_billing_phone();
        $billingAddress   = smartwoo_get_user_billing_address( $user_id );
    
        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/view-client-billing.php';
        die();
    }

    /**
     * Ajax callback to get the client billing form
     */
    public static function get_edit_billing_form() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        smartwoo_get_edit_billing_form();
        die();
    }

    /**
     * Save client's billing details
     */
    public static function save_client_billing_details() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        $customer = new WC_Customer( get_current_user_id() );

        // Define mapping of POST keys to WC_Customer setter methods.
        $billing_fields = array(
            'billing_first_name',
            'billing_last_name',
            'billing_company',
            'billing_country',
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_phone',
            'billing_email',
        );

        foreach ( $billing_fields as $field ) {
            if ( $value = smartwoo_get_post_param( $field ) ) {
                $setter = 'set_' . $field;
                if ( is_callable( array( $customer, $setter ) ) ) {
                    $customer->{$setter}( $value );
                }
            }
        }

        $customer->save();
        self::get_billing_details();
    }
    /**
     * Ajax callback for user details in frontend.
     */
    public static function get_client_details() {

        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die();
        }

        $user_id        = get_current_user_id();
        $user           = new WC_Customer( $user_id );
        $full_name      = $user->get_first_name() . ' ' . $user->get_last_name();
        $email          = $user->get_email();
        $user_name      = $user->get_username();
        $display_name   = $user->get_display_name();
        $user_role      = $user->get_role();

        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/view-client-details.php';
        
        die();
    }

    /**
     * Ajax callback to get client edit form.
     */
    public static function get_edit_client_form() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        smartwoo_get_edit_account_form();
        die();

    }

    /**
     *  Save client details
     */
    public static function save_client_details() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }
        $user_id   = get_current_user_id();
        $user      = get_user_by( 'id', $user_id );

        if ( ! $user ) {
            wp_die( '<div class="sw-error-notice"><p>' . esc_html__( 'User not found.', 'smart-woo-service-invoicing' ) . '</p></div>' );
        }

        $first_name    = smartwoo_get_post_param( 'account_first_name' );
        $last_name     = smartwoo_get_post_param( 'account_last_name' );
        $display_name  = smartwoo_get_post_param( 'account_display_name' );
        $email         = smartwoo_get_post_param( 'account_email' );
        $pass_current  = $_POST['password_current']; // phpcs:disable -- Don't tamper with raw password.
        $pass_1        = $_POST['password_1']; // phpcs:disable -- Don't tamper with raw password.
        $pass_2        = $_POST['password_2']; // phpcs:disable -- Don't tamper with raw password.

        $userdata = array(
            'ID'           => $user_id,
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => $display_name,
        );

        // Validate email change.
        if ( ! empty( $email ) && $email !== $user->user_email ) {
            if ( ! is_email( $email ) ) {
                wp_die( '<div class="sw-error-notice"><p>' . esc_html__( 'Invalid email address.', 'smart-woo-service-invoicing' ) . '</p></div>' );
            }
            if ( email_exists( $email ) ) {
                wp_die( '<div class="sw-error-notice"><p>' . esc_html__( 'Email address is already registered.', 'smart-woo-service-invoicing' ) . '</p></div>' );
            }
            $userdata['user_email'] = $email;
        }

        // Validate password change.
        if ( ! empty( $pass_1 ) || ! empty( $pass_2 ) ) {
            if ( empty( $pass_current ) || ! wp_check_password( $pass_current, $user->user_pass, $user_id ) ) {
                wp_die( '<div class="sw-error-notice"><p>' . esc_html__( 'Your current password is incorrect.', 'smart-woo-service-invoicing' ) . '</p></div>' );
            }
            if ( $pass_1 !== $pass_2 ) {
                wp_die( '<div class="sw-error-notice"><p>' . esc_html__( 'New passwords do not match.', 'smart-woo-service-invoicing' ) . '</p></div>' );
            }
            $userdata['user_pass'] = $pass_1;
        }

        // Update the user.
        $updated = wp_update_user( $userdata );

        if ( is_wp_error( $updated ) ) {
            wp_die( '<div class="sw-error-notice"><p>' . esc_html( $updated->get_error_message() ) . '</p></div>' );
        }

        self::get_client_details();
    }

    /**
     * Ajax function callback for account logs in frontend
     */
    public static function get_account_logs() {

        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die();
        }

        $current_user 		= wp_get_current_user();
        $user_id      		= $current_user->ID;
        $current_login_time = smartwoo_get_current_login_date( $user_id );
        $last_active		= smartwoo_get_last_login_date( $user_id );
        $registration_date 	= smartwoo_check_and_format( $current_user->user_registered, true );
        $total_spent 		= smartwoo_client_total_spent( $user_id );
        $user_agent			= smartwoo_parse_user_agent( wc_get_user_agent() );
        $ip_address         = WC_Geolocation::get_ip_address();
        $location_data      = WC_Geolocation::geolocate_ip( $ip_address );
        $user_location      = $location_data['country'] ?? 'Unknown';

        $log_data = array(
            esc_html__( 'Total Amount Spent', 'smart-woo-service-invoicing' ) => smartwoo_price( $total_spent ), // Assumes smartwoo_price handles escaping
            esc_html__( 'User Agent', 'smart-woo-service-invoicing' )        => $user_agent,
            esc_html__( 'Current Login Time', 'smart-woo-service-invoicing' ) => $current_login_time,
            esc_html__( 'Last Logged In', 'smart-woo-service-invoicing' )     => $last_active,
            esc_html__( 'Registration Date', 'smart-woo-service-invoicing' )  => $registration_date,
            esc_html__( 'IP Address', 'smart-woo-service-invoicing' )        => $ip_address,
            esc_html__( 'Location', 'smart-woo-service-invoicing' )          => $user_location,
        );

        /**
         * Filters the account log data displayed to the client.
         *
         * @since 2.4.3
         * @param array $log_data An associative array of log detail titles and their values.
         */
        $filtered_log_data = apply_filters( 'smartwoo_client_log', $log_data );
        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/view-client-account-logs.php';
        die();
    }

    /**
     * Ajax callback to get client payment methods
     */
    public static function get_payment_details() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        $user_id         = get_current_user_id();
        $payment_details = smartwoo_get_user_payment_options( $user_id );
        $gateways        = WC()->payment_gateways()->get_available_payment_gateways();

        // Pre-build display-ready data
        $payment_display = array(
            'primary' => '',
            'backup'  => '',
        );

        foreach ( array( 'primary', 'backup' ) as $type ) {
            $gateway_id = isset( $payment_details[ $type ] ) ? $payment_details[ $type ] : '';

            if ( $gateway_id && isset( $gateways[ $gateway_id ] ) ) {
                $gateway = $gateways[ $gateway_id ];
                $icon    = $gateway->get_icon();
                $title   = $gateway->get_title();

                $payment_display[ $type ] = $icon 
                    ? sprintf( '<span class="smartwoo-gateway-title">%s</span> <span class="smartwoo-gateway-icon">%s</span>', esc_html( $title ), $icon )
                    : esc_html( $title );
            } else {
                $payment_display[ $type ] = esc_html__( 'Not set', 'smart-woo-service-invoicing' );
            }
        }

        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/view-client-payment.php';
        die();
    }

    /**
     * Get client's edit payment method form.
     * Serves for both primary and backup payment method depending on the action query variable.
     */
    public static function get_edit_payment_form() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        $action = smartwoo_get_query_param( 'action' );
        $action_map = array(
            'get_edit_primary_payment_form' => 'primary',
            'get_edit_backup_payment_form'  => 'backup'
        );

        $type = $action_map[$action] ?? false;

        if ( ! $type ) {
            $message = __( 'Invalid payment form request, please contact us if you need further assistance', 'smart-woo-service-invoicing' );
            wp_die( '<div class="sw-error-notice"><p> ' . esc_html( $message ) . '</p></div>' );
        }

        /* translators: %s: Payment method type */
        $form_title     = sprintf( __( 'Edit %s Payment Method' ), ucfirst( $type ) );
        $gateways       = WC()->payment_gateways()->get_available_payment_gateways();
        $user_option    = smartwoo_get_user_payment_options( get_current_user_id() )[$type];
        
        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/form-edit-payment.php';
        die();
    }

    /**
     * Save the client's payment method options
     */
    public static function save_payment_method() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }
        
        $type = smartwoo_get_post_param( 'payment_option_type' );
        if ( ! in_array( $type, array( 'primary', 'backup' ), true ) ) {
            $message = __( 'Invalid payment method type, please contact us if you need further assistance', 'smart-woo-service-invoicing' );
            wp_die( '<div class="sw-error-notice"><p> ' . esc_html( $message ) . '</p></div>' );
        }

        $value = smartwoo_get_post_param( 'payment_method' );

        // Only validate if a value was provided.
        if ( ! empty( $value ) ) {
            $gateways = WC()->payment_gateways->get_available_payment_gateways();

            if ( empty( $gateways[ $value ] ) ) {
                $message = __( 'Invalid payment method selected. Please choose from the available options.', 'smart-woo-service-invoicing' );
                wp_die( '<div class="sw-error-notice"><p> ' . esc_html( $message ) . '</p></div>' );
            }
        }

        $user_id = get_current_user_id();
        $options = smartwoo_get_user_payment_options( $user_id );

        $options[ $type ] = $value;
        update_user_meta( $user_id, '_smartwoo_payment_options', $options );

        self::get_payment_details(); // Show updated payment details.
    }

    /**
     * Ajax callback for user order history in the frontend
     */
    public static function get_order_history() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) || ! is_user_logged_in() ) {
            wp_die( 'Refresh current page!' );
        }

        $limit  = smartwoo_get_post_param( 'limit', 10 );
        $page   = smartwoo_get_post_param( 'page', 1 );

        $args = array(
            'page'  => $page,
            'limit' => $limit
        );

        $orders         = SmartWoo_Order::get_user_orders( $args );
        $orders_count   = SmartWoo_Order::count_user_orders();
        $total_pages    =  (int) ceil( $orders_count / $limit );

        include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/view-client-order-history.php';
        die();
    }

    /**
     * Fetch client subscriptions via ajax
     */
    public static function fetch_user_subscriptions() {
        if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication' ) );
        }

        $context    = smartwoo_get_query_param( 'context' );

        if ( 'any' === $context ) {
            add_filter( 'smartwoo_is_frontend', '__return_true' );
        } elseif ( 'myaccount' === $context ) {
            add_filter( 'woocommerce_is_account_page', '__return_true' );
        }

        $page   = smartwoo_get_query_param( 'page', 1 );
        $limit  = smartwoo_get_query_param( 'limit', 10 );

        $response   = array(
            'message'       => 'No subscription found.',
            'subscriptions' => [],
            'pagination'    => array(
                'total_pages'   => 0,
                'total_items'   => 0,

            )
        );

        $services       = [];
        $user_id        = get_current_user_id();
        $all_services   = SmartWoo_Service_Database::get_services_by_user( $user_id, $page, $limit );
        
        if ( ! empty( $all_services ) ) {
            foreach( $all_services as $service ) {
                $services[] = array(
                    'view_url' => smartwoo_service_preview_url( $service->get_service_id() ),
                    'status'    => smartwoo_service_status( $service ),
                    'name'      => $service->get_name()
                
                );
            }

            $all_services_count		= SmartWoo_Service_Database::count_user_services( $user_id );
            $total_items_count		= count( $all_services );
            $total_pages			= ceil( $all_services_count / $limit );
            $response['subscriptions']  = $services;
            $response['message']        = 'Services found';
            $response['pagination']     = array(
                'total_pages'   => $total_pages,
                'total_items'   => count( $all_services  ),
                'all_items'     => $all_services_count
            );
        }
        
        wp_send_json_success( $response );

    }

    /**
     * Ajax reset fast checkout options
     */
    public static function reset_fast_checkout() {
        check_ajax_referer( 'smart_woo_nonce', 'security' );
        if ( delete_option( 'smartwoo_fast_checkout_options' ) ) {
            wp_send_json_success();
        }

        wp_send_json_error( '', 401 );

    }

    /**
     * Sell auto service renewal button, a feature only available in Smart Woo Pro
     * 
     * @param SmartWoo_Service $service
     */
    public static function sell_renewal_button( SmartWoo_Service $service ) {
        if ( ! self::$instance->pro_is_installed() ) {
            $renewal_statuses = [ 'Due for Renewal', 'Expired', 'Grace Period' ];

            if ( in_array( smartwoo_service_status( $service ), $renewal_statuses, true  ) ) {
                ?>
                    <button id="auto-renew-btn" title="Renew Now"><span class="dashicons dashicons-controls-repeat"></span></button>
                    <span id="pro-target"></span>
                <?php
            }
        }
        
    }

    /**
     * Print user invoice
     */
    public static function print_invoice() {
        if ( ! wp_verify_nonce( smartwoo_get_query_param( '_wpnonce' ) ) ) {
            /* translators: %s: Invoice ID */
            wp_die( sprintf( __( 'Expired link, please return to <a href="%s">invoice page</a>', 'smart-woo-service-invoicing' ), esc_html( smartwoo_invoice_preview_url( smartwoo_get_query_param( 'invoice_id' ) ) ) ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have the required permission to print invoices', 'smart-woo-service-invoicing' ) );
        }

        smartwoo_pdf_invoice_template( smartwoo_get_query_param( 'invoice_id' ), 'I' );
        
    }

    /**
     * Manage admin notices
     */
    public static function notice_manager() {
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        $target_screens = array(
            'dashboard',
            'plugins',
            'themes',
            'edit-product',
            'edit-shop_order',
        );
        
        if ( ! in_array( $screen->id, $target_screens, true ) && ! SmartWoo_Config::in_admin_page() ) {
            return;
        }

        self::sell_pro();

    }

}

SmartWoo::instance();
