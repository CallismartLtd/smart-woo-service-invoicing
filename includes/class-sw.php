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
        add_filter( 'plugin_action_links_' . SMARTWOO_PLUGIN_BASENAME, array( $this, 'options_page' ), 10, 2 );
        add_action( 'admin_post_nopriv_smartwoo_login_form', array( $this, 'login_form' ) );
        add_action( 'admin_post_smartwoo_login_form', array( $this, 'login_form' ) );
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
        $smartwoo_pro_url = apply_filters( 'smartwoopro_purchase_link', 'https://callismart.com.ng/smart-woo' );

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
     * Instance of current class.
     */
    public static function instance() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
    }
}

SmartWoo::instance();
