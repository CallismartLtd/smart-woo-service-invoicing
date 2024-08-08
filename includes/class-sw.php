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
        add_action( 'woocommerce_order_details_before_order_table', array( $this, 'before_order_table' ) );
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
        if ( smartwoo_check_if_configured( $order ) ) {
            echo '<a href="' . esc_url( smartwoo_service_page_url() ) .'" class="sw-blue-button">Dashbaord</a>';
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
        if ( isset( $_POST['user_login'] ) && isset( $_POST['password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $credentials = array(
                'user_login'    => sanitize_text_field( wp_unslash( $_POST['user_login'] ) ),
                'user_password' => sanitize_text_field( $_POST['password'] ),
                'remember'      => true,
            );


            $user = wp_signon( $credentials, false );

            if ( is_wp_error( $user ) ) {
                set_transient( 'smartwoo_login_error', $user->get_error_message(), 5 );
                wp_redirect( esc_url_raw( wp_get_referer() ) );
                exit;

            } else {
                wp_redirect( esc_url_raw( $_POST['redirect'] ) );
                exit;
            }
        }
    }

    /**
     * Handle the processing of new service orders.
     */
    public function new_service_from_order() {

        if ( isset( $_POST['smartwoo_process_new_service'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_process_new_service_nonce'] ) ), 'sw_process_new_service_nonce' ) ) {

            $product_id        	= isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $order_id          	= isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
            $service_url       	= isset( $_POST['service_url'] ) ? sanitize_url( $_POST['service_url'], array( 'http', 'https' ) ) : '';
            $service_type      	= isset( $_POST['service_type'] ) ? sanitize_text_field( $_POST['service_type'] ) : '';
            $user_id           	= isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : '';
            $start_date        	= isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
            $billing_cycle     	= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( $_POST['billing_cycle'] ) : '';
            $next_payment_date 	= isset( $_POST['next_payment_date'] ) ? sanitize_text_field( $_POST['next_payment_date'] ) : '';
            $end_date          	= isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';
            $status            	= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
            $service_name 		= isset( $_POST['service_name'] ) ? sanitize_text_field( $_POST['service_name'] ) : '';
            $service_id 		= isset( $_POST['service_id'] ) ? sanitize_text_field( $_POST['service_id'] ) : '';
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
                $new_service->save_assets();
                $order = wc_get_order( $order_id );
                
                if ( 'processing' === $order->get_status()  ) {
                    $order->update_status( 'completed' );
                }

                do_action( 'smartwoo_new_service_is_processed' . $saved_service_id );
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
    
        $asset_id       = ! empty( $_GET['asset_id'] ) ? absint( $_GET['asset_id'] ) : 0;
        $resource_id    = ! empty( $_GET['resource_id'] ) ? absint( rawurldecode( wp_unslash( $_GET['resource_id'] ) ) ) : '';
        $asset_key      = ! empty( $_GET['key'] ) ? sanitize_key( wp_unslash( $_GET['key'] ) ): '';
        
        if ( empty( $resource_id ) || ! SmartWoo_Service_Assets::verify_key( $asset_key, $resource_id ) ) {
            wp_die( 'Unable to validate requested resource.' );
        }

        $asset_data = SmartWoo_Service_Assets::return_data( $asset_id, $asset_key );

        if ( ! is_array( $asset_data ) || empty( $asset_data ) ) {
            wp_die( 'Invalid data format returned', 403 );
        }

        $re_indexed_data    = array_values( (array) $asset_data );
        $resource_url       = array_key_exists( $resource_id - 1, $re_indexed_data ) ? $re_indexed_data[$resource_id - 1]: wp_die( 'File URL not found.', 404 );
        
        $this->serve_file( $resource_url );
    }
    
    /**
     * Serve file for download.
     */
    private function serve_file( $resource_url ) {
        
        $resource_url   = sanitize_url( $resource_url, array( 'http', 'https' ) );
        $file_headers   = get_headers( $resource_url, 1 );
    
        if ( ! $file_headers || strpos( $file_headers[0], '200' ) === false ) {
            wp_die( 'File not found.' );
        }
    
        $content_type   = $file_headers['Content-Type'] ?? 'application/octet-stream';
        $content_length = $file_headers['Content-Length'] ?? 0;
        $filename       = basename( parse_url( $resource_url, PHP_URL_PATH ) );
    
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
        header( 'Expires: 0' );
        header( 'Cache-Control: must-revalidate' );
        header( 'Pragma: public' );
        header( 'Content-Length: ' . $content_length );
    
        // Open the file and stream it to the browser
        $handle = fopen( $resource_url, 'rb' );
        if ( $handle ) {
            while ( ! feof( $handle ) ) {
                echo fread( $handle, 8192 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ob_flush();
                flush();
            }
            fclose( $handle );
        } else {
            wp_die( 'Unable to read the file.' );
        }
    
        exit;
    }
    
}

SmartWoo::instance();
