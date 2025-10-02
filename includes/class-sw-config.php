<?php
/**
 * File name class-sw-config.php
 * Smart Woo Invironment set up class file.
 * 
 * @author Callistus
 * @package SmartWoo\classes
 * @since 1.0.2
 */
defined( 'ABSPATH' ) && ( defined ( 'SMARTWOO' ) || defined( 'SMARTWOO_FILE' ) ) || exit; // Prevent direct access.

/**
 * SmartWoo_Config
 * Environment configuration file
 * 
 * @since 1.0.2
 * @package SmartWoo
 */
class SmartWoo_Config{
    /**
     * @var $woocommerce_loaded if WooCommerce is loaded.
     */
    private $woocommerce_loaded = false;

    /**
     * @var $instance SmartWoo.
     */
    private static $instance = null;


    /**
     * @var $woocommerce_compatibility Declare compatibility with WooCommerce features.
     */
    private $woocommerce_compatibility;


    /**
     * Initialize.
     */
    public function __construct() {
        // Define the database table names as constants.
        global $wpdb;
        define( 'SMARTWOO_SERVICE_TABLE', $wpdb->prefix . 'sw_service' );
        define( 'SMARTWOO_SERVICE_META_TABLE', $wpdb->prefix . 'sw_service_meta' );
        define( 'SMARTWOO_INVOICE_TABLE', $wpdb->prefix . 'sw_invoice' );
        define( 'SMARTWOO_INVOICE_META_TABLE', $wpdb->prefix . 'sw_invoice_meta' );
        define( 'SMARTWOO_ASSETS_TABLE', $wpdb->prefix . 'sw_assets' );
        define( 'SMARTWOO_PLUGIN_BASENAME', plugin_basename( SMARTWOO_FILE ) );
        define( 'SMARTWOO_UPLOAD_DIR', trailingslashit( wp_upload_dir()['basedir'] ) . 'smartwoo-uploads' );
        
        register_activation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'install' ) );
        register_deactivation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'deactivate' ) );
        
        $this->initialize();
    }

    /**
     * The Smart Woo Initialization process.
     */
    private function initialize() {
        add_action( 'woocommerce_loaded', array( $this, 'check_woocommerce' ) );
        add_action( 'smartwoo_init', array( $this, 'load_dependencies' ) );
        add_action( 'smartwoo_loaded', array( $this, 'run_hooks' ) );
        add_action( 'rest_api_init', [$this, 'register_rest_routes'] );
        add_filter( 'rest_pre_dispatch', array( $this, 'rest_pre_dispatch' ), 10, 3 );
    }

    /**
     * Run action and filters.
     * 
     * @since 1.0.52 Added support for WP_Consent API.
     */
    public function run_hooks() {
        if ( get_option( 'smartwoo_activated', false ) && is_admin() && current_user_can( 'install_plugins' ) ) {
            delete_option( 'smartwoo_activated' );
            wp_safe_redirect( admin_url( 'admin.php?page=sw-options' ) );
            exit;
        }
        
        if ( class_exists( WP_CONSENT_API::class ) ) {
            add_filter( 'wp_consent_api_registered_' . SMARTWOO_PLUGIN_BASENAME, '__return_true' );
            add_action( 'wp_consent_api_consent_changed', array( __CLASS__, 'revoke_tracking' ) ); 
        }

        add_action( 'admin_init', array( $this, 'woocommerce_dependency_nag' ) );
        add_action( 'before_woocommerce_init', array( $this, 'woocommerce_custom_order_compatibility' ) );
        add_action( 'woocommerce_order_details_before_order_table', array( $this, 'remove_order_again_button' ) );
        add_action( 'admin_menu', array( __CLASS__, 'modify_sw_menu' ), 999 );
        // add_action( 'template_redirect', array( $this, 'protect_endpoints' ), 10 );
        add_action( 'init', array( $this, 'init_hooks' ) );
        add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'register_woocommerce_account_menus' ), 99 );
        
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'add_myaccount_vars' ) );

        add_filter( 'woocommerce_account_smartwoo-invoice_endpoint', array( 'SmartWoo_Invoice_Frontend_Template', 'woocommerce_myaccount_invoices_page' ) );
        add_filter( 'woocommerce_account_smartwoo-service_endpoint', array( 'SmartWoo_Service_Frontend_Template', 'woocommerce_myaccount_services_page' ) );
        add_filter( 'woocommerce_endpoint_smartwoo-service_title', function( $title ) { return 'Subscriptions'; });
        add_filter( 'woocommerce_endpoint_smartwoo-invoice_title', function( $title ) { return 'Invoices'; });

        add_filter( 'template_include', array( __CLASS__, 'template_include' ) );

        /** Register our crons */
        add_filter( 'cron_schedules', array( $this, 'register_cron' ) );
        
        add_filter( 'get_edit_post_link', array( SmartWoo_Product::class, 'get_edit_url' ), 100, 2 );
        add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 30, 2 );
        add_action( 'woocommerce_save_account_details', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        add_action( 'woocommerce_customer_save_address', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        
        add_action( 'woocommerce_new_order', array( $this, 'clear_order_cache' ), 20 );
        add_action( 'smartwoo_new_service_purchase_complete', array( $this, 'clear_order_cache' ), 20 );
		add_filter( 'smartwoo_subscription_pages', array( __CLASS__, 'register_service_page_callbacks' ) );
		add_filter( 'smartwoo_invoice_pages', array( __CLASS__, 'register_invoice_page_callbacks' ) );

        // add_filter( 'block_categories_all', array( 'SmartWoo_Blocks', 'register_block_category' ), 20, 2 );

    }

    /**
     * Init hooks
     */
    public function init_hooks() {
        SmartWoo_Automation::init();
        $this->add_rules();
        $this->add_actions();
        // SmartWoo_Blocks::instance();
    }

    /**
     * Check whether WooCommerce is loaded.
     */
    public function check_woocommerce() {
        if ( class_exists( 'WooCommerce' ) ) {
            $this->woocommerce_loaded = true;
            do_action( 'smartwoo_init' );
        }

        return $this->woocommerce_loaded;
    }

    /**
     * Load files.
     */
    public function load_dependencies() {
        if ( true === $this->woocommerce_loaded ) {
            $this->include();

            // trigger action after loading plugin files.
            do_action( 'smartwoo_loaded' );
        }
    }

    /**
     * Include files.
     */
    public function include() {

        require_once SMARTWOO_PATH . 'includes/class-smartwoo-date-helper.php';
        require_once SMARTWOO_PATH . 'includes/sw-functions.php';
        require_once SMARTWOO_PATH . 'includes/class-smartwoo.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/invoice.downloadable.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/sw-invoice-function.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/sw-service-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/class-sw-product.php';
        require_once SMARTWOO_PATH . 'includes/class-sw-cart.php';
        require_once SMARTWOO_PATH . 'includes/class-sw-checkout.php';
        require_once SMARTWOO_PATH . 'includes/sw-orders/class-sw-order.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-product-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-utm.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-assets.php';
        require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/contr.php';
        require_once SMARTWOO_PATH . 'includes/emails/class-smart-woo-mails.php';
        require_once SMARTWOO_PATH . 'includes/emails/invoice-emails/class-invoice-mails.php';
        require_once SMARTWOO_PATH . 'includes/emails/invoice-emails/new-invoice-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/invoice-emails/invoice-paid-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/invoice-emails/invoice-payment-reminder.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/class-service-mails.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/cancelled-service-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/service-opt-out-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/service-expiration-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/service-reactivation-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/new-order-email.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/service-processed-mail.php';
        require_once SMARTWOO_PATH . 'includes/class-smartwoo-blocks.php';
        require_once SMARTWOO_PATH . 'includes/class-automation.php';
        require_once SMARTWOO_PATH . 'includes/rest-api/class-sanitize.php';
        require_once SMARTWOO_PATH . 'includes/rest-api/class-validate.php';
        require_once SMARTWOO_PATH . 'includes/rest-api/adminDashboard.php';

        /** Only load admin menu and subsequent files in admin page. */ 
        if ( is_admin() ) {
            require_once SMARTWOO_PATH . 'includes/admin/admin-menu.php';
            require_once SMARTWOO_PATH . 'includes/admin/class-dashboard-controller.php';
            require_once SMARTWOO_PATH . 'includes/admin/class-orders-controller.php';
            require_once SMARTWOO_PATH . 'includes/admin/class-invoice-controller.php';
            require_once SMARTWOO_PATH . 'includes/admin/class-product-controller.php';
            require_once SMARTWOO_PATH . 'includes/admin/class-settings-controller.php';
            require_once SMARTWOO_PATH . 'includes/class-sw-db-update.php';
            require_once SMARTWOO_PATH . 'includes/class-setup-wizard.php';
            
        }

        /** Load fontend file. */ 
        if ( smartwoo_is_frontend() ) {
            require_once SMARTWOO_PATH . 'includes/frontend/invoice/template.php';
            require_once SMARTWOO_PATH . 'includes/frontend/shortcode.php';
            require_once SMARTWOO_PATH . 'includes/frontend/service/template.php';
        }
        add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ), 22 );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ), 22 );

    }

    /**
     * Instance.
     * 
     * @return self
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_styles() {
        wp_register_style( 'smartwoo-inline', false ); // phpcs:ignore
        
        $suffix = self::script_suffix();
        $utm_style_uri  = SMARTWOO_DIR_URL . 'assets/css/sw-admin' . $suffix . '.css';
        $admin_style    = SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css';
        $icon_styles    = SMARTWOO_DIR_URL . 'assets/css/sw-icons' . $suffix . '.css';
        $editor_ui      = SMARTWOO_DIR_URL . 'assets/editor/css/smartwoo-editor-ui' . $suffix . '.css';
        $sub_assets     = SMARTWOO_DIR_URL . 'assets/css/subscription-assets' . $suffix . '.css';

        wp_register_style( 'smartwoo-jquery-timepicker', SMARTWOO_DIR_URL . 'assets/css/jquery/time-picker' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-admin-utm-style', $utm_style_uri, array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-admin-style', $admin_style, array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-invoice-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo-invoice' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-icon-style', $icon_styles, array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-editor-ui', $editor_ui, array(), SMARTWOO_VER, 'all' );
        wp_register_style( 'smartwoo-service-asset-style', $sub_assets, array(), SMARTWOO_VER, 'all' );
        
        if ( function_exists( 'smartwoo_is_frontend' ) && smartwoo_is_frontend() ) {
            wp_enqueue_style( 'smartwoo-style' );
        }
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() ) {
            $invoice_style_url  = apply_filters( 'smartwoo_invoice_style_url', SMARTWOO_DIR_URL . 'assets/css/smart-woo-invoice' . $suffix . '.css' );
            wp_enqueue_style( 'smartwoo-invoice-style', $invoice_style_url, array(), SMARTWOO_VER, 'all' );
        }

        if ( is_admin() ) {           
            if ( self::in_admin_page() ) {
                wp_add_inline_style( 'smartwoo-inline', '#wpcontent { padding-left: 0 !important; } #screen-meta { z-index: 99 !important; }' );
                wp_enqueue_style( 'woocommerce_admin_styles' );
                wp_enqueue_style( 'jquery-ui-style' );
                wp_enqueue_style( 'smartwoo-jquery-timepicker' );
            }

            wp_enqueue_style( 'smartwoo-admin-utm-style' );
            wp_enqueue_style( 'smartwoo-admin-style' );
            wp_enqueue_style( 'smartwoo-invoice-style' );
            wp_enqueue_style( 'smartwoo-icon-style' );
            wp_enqueue_style( 'smartwoo-inline' );

        }
        wp_enqueue_style( 'smartwoo-service-asset-style' );
    }

    /**
     * JavaScript registeration.
     */
    public function load_scripts() {
        $suffix = self::script_suffix();

        $utils   =   array(
            'smartwoo_plugin_url'       => SMARTWOO_DIR_URL,
            'smartwoo_assets_url'       => SMARTWOO_DIR_URL . 'assets/',
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'woo_my_account_edit'       => wc_get_account_endpoint_url( 'edit-account' ),
            'woo_payment_method_edit'   => wc_get_account_endpoint_url( 'payment-methods' ),
            'woo_billing_eddress_edit'  => wc_get_account_endpoint_url( 'edit-address/billing' ),
            'sw_admin_page'             => esc_url_raw( admin_url( 'admin.php?page=sw-admin' ) ),
            'new_service_page'          => esc_url_raw( admin_url( 'admin.php?page=sw-admin&tab=add-new-service')),
            'admin_invoice_page'        => esc_url_raw( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) ),
            'admin_order_page'          => esc_url_raw( admin_url( 'admin.php?page=sw-service-orders' ) ),
            'sw_product_page'           => esc_url_raw( admin_url( 'admin.php?page=sw-products' ) ),
            'sw_options_page'           => esc_url_raw( admin_url( 'admin.php?page=sw-options' ) ),
            'security'                  => wp_create_nonce( 'smart_woo_nonce' ),
            'home_url'                  => home_url( '/' ),
            'is_account_page'           => is_account_page(),
            'cart_is_configured'        => apply_filters( 'cart_is_configured', false ),
            'never_expire_value'        => apply_filters( 'smartwoo_never_expire_value', '' ),
            'wp_spinner_gif'            => admin_url('images/spinner.gif'),
            'wp_spinner_gif_2x'         => admin_url('images/spinner-2x.gif'),
            'smartwoo_plugin_page'      => apply_filters( 'smartwoo_plugin_url', 'https://callismart.com.ng/smart-woo-service-invoicing' ),
            'smartwoo_pro_page'         => apply_filters( 'smartwoo_pro_purchase_page', 'https://callismart.com.ng/smart-woo-service-invoicing/#go-pro' ),
            'currentScreen'             => self::get_current_screen(),
            'get_user_data'             => admin_url( 'admin-ajax.php?action=smartwoo_get_user_data' ),
            'global_nextpay_date'       => smartwoo_get_global_nextpay( 'edit' ),
            'default_avatar_url'        => smartwoo_get_avatar_placeholder_url(),
            'fast_checkout_config'      => smartwoo_fast_checkout_options(),
            'dashicons_asset_url'       => includes_url( 'css/dashicons.min.css' ),
            'editor_css_url'            => SMARTWOO_DIR_URL . 'assets/editor/css/smartwoo-editor-ui.css',
            'subscription_asset_url'    => SMARTWOO_DIR_URL . 'assets/css/subscription-assets' . $suffix . '.css',
            'charset'                   => get_bloginfo( 'charset' ),
            'smartwoo_pro_is_installed' => SmartWoo::pro_is_installed()
        );

        wp_register_script( 'smartwoo-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        wp_register_script( 'smartwoo-admin-dashboard', SMARTWOO_DIR_URL . 'assets/js/smartwoo-admin-dashboard' . $suffix . '.js', array( 'jquery', 'smartwoo-script' ), SMARTWOO_VER, true );
        wp_register_script( 'smartwoo-jquery-timepicker', SMARTWOO_DIR_URL . '/assets/js/jquery/jquery-time-picker' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), SMARTWOO_VER, true );
        wp_register_script( 'smartwoo-invoice-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-invoice' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        wp_register_script( 'smartwoo-fast-checkout', SMARTWOO_DIR_URL . 'assets/js/smart-woo-fast-checkout' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        
        wp_register_script( 'smartwoo-editor-ui', SMARTWOO_DIR_URL . 'assets/editor/js/editor-ui' . $suffix . '.js', array(), SMARTWOO_VER, array( 'in_footer' => true, 'strategy' => 'defer' ) );
        wp_register_script( 'smartwoo-service-asset-sript', SMARTWOO_DIR_URL . 'assets/js/smartwoo-service-asset-script' . $suffix . '.js', array(), SMARTWOO_VER, true );

        wp_enqueue_script( 'smartwoo-script' );
        wp_enqueue_script( 'smartwoo-service-asset-sript' );
        wp_enqueue_style( 'smartwoo-editor-ui' );
        
        wp_localize_script( 'smartwoo-script', 'smart_woo_vars', $utils );
        $invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() || is_admin() ) {
            wp_enqueue_script( 'smartwoo-invoice-script' );
        }

        if ( is_page( get_option( 'smartwoo_service_page_id', 0 ) ) || is_account_page() ) {
            wp_enqueue_script( 'selectWoo' );
            wp_enqueue_style( 'select2' );
            wp_enqueue_script( 'wc-country-select' );
        }

        if ( get_option( 'smartwoo_allow_fast_checkout', false ) && smartwoo_is_frontend() ) {
            wp_enqueue_script( 'smartwoo-fast-checkout' );
        }

        if ( is_admin() ) {
            wp_register_script( 'smartwoo-admin-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-admin' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
            
            $utils['restApi'] = [
                'admin_url'     => rest_url( 'smartwoo-admin/v1/' ),
                'WP_API_nonce'  => wp_create_nonce( 'wp_rest' )
            ];

            if ( self::in_admin_page() ) {
                wp_enqueue_script( 'wc-enhanced-select' );
                wp_enqueue_script( 'smartwoo-jquery-timepicker' );
                wp_enqueue_media();
                wp_enqueue_editor();
            }
            wp_localize_script( 'smartwoo-admin-script', 'smartwoo_admin_vars', $utils );
            wp_enqueue_script( 'smartwoo-admin-script' );
        }
    }

    /**
     * Enqueue our asset editor script.
     * 
     * @since 2.4.3
     */
    public static function enqueue_asset_editor() {
        wp_enqueue_media();        
        wp_enqueue_script( 'smartwoo-editor-ui' );
    }

    /**
     * Script suffix
     */
    public static function script_suffix() {
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            return '';
        } 
        
        if ( defined( 'SMARTWOO_SCRIPT_DEBUG' ) && SMARTWOO_SCRIPT_DEBUG ) {
            return '';
        }

        return '-min';
    }

    /**
     * Show notice when WooCommerce is not active.
     */
    public function woocommerce_dependency_nag() {
        if ( ! $this->woocommerce_loaded ) {
            $woo_plugin_url = 'https://wordpress.org/plugins/woocommerce/';
            $notice         = sprintf(
                'Smart Woo Service Invoicing requires WooCommerce to be active. Please <a href="%s" class="activate-link" target="_blank">activate WooCommerce</a> or deactivate the plugin to avoid a fatal error.',
                esc_url( $woo_plugin_url )
            );
            add_action(
                'admin_notices',
                function () use ( $notice ) {
                    echo '<div class="notice notice-error"><p>' . wp_kses( $notice, 
                        array(
                            'a' => array(
                                'href' => array(),
                                'class' => array()
                            )
                        ) 
                    ) . '</p></div>';
                }
            );
        }
   }

   /**
    * Declare Custom order table compatibility.
    */
    public function woocommerce_custom_order_compatibility() {
      $this->woocommerce_compatibility = \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SMARTWOO_FILE, true );

    }

    /**
     * Our query vars
     */
    public function get_query_vars() {
        return apply_filters(
            'smartwoo_query_vars',
            array(
                'buy-new',
                'view-subscription',
                'status',
                'upgrade',
                'downgrade',
                'view-invoice'
            )
        );
    }

    /** Smart Woo page rewrite rules */
    public function add_rules() {
        /** Product configuration page */
        $new_config_var = smartwoo_get_product_config_query_var();
        add_rewrite_rule( "^{$new_config_var}/([^/]+)?$", 'index.php?' . $new_config_var . '=$matches[1]', 'top' );
        add_rewrite_rule( '^configure/?$', 'index.php?configure=true', 'top' );
        foreach( $this->get_query_vars() as $key => $var ) {
            add_rewrite_endpoint( $var, EP_PAGES );
        }

        if ( false === get_option( '_smartwoo_flushed_rewrite_rules', false ) ) {
            flush_rewrite_rules();
            set_transient( '_smartwoo_flushed_rewrite_rules', true, WEEK_IN_SECONDS );
        }
    }

    /**
     * Register WooCommerce myaccount page query_vars
     */
    public static function add_myaccount_vars( $vars ) {
        $vars['smartwoo-invoice'] = 'smartwoo-invoice';
        $vars['smartwoo-service'] = 'smartwoo-service';

        return $vars;
    }

    /**
     * Register query vars
     */
    public static function add_query_vars( $vars ) {
        $vars[] = 'configure';
        $vars[] = 'product-config';
        return $vars;
    }

    /**
     * Trigger 404 if accessing our endpoint on wrong page.
     */
    public function protect_endpoints() {
        global $wp_query;
        $service_page_id        = absint( get_option( 'smartwoo_service_page_id', 0 ) );
        $protected_endpoints    = array(
            'buy-new',
            'upgrade',
            'downgrade',
            'view-subscription',
            'status'
        );
    
        if ( is_page( $service_page_id ) ) {
            return;
        }

        foreach ( $protected_endpoints as $query ) {
            if ( isset( $wp_query->query_vars[ $query ]  ) ) {
                $wp_query->set_404();
                status_header( 404 );
                include( get_query_template( '404' ) );
                exit;
       
            }
        }

    }
    
    /** Cron registeration method */
    public function register_cron(  $schedules ) {
        /**Define a cron interval for 12 hours. */
        $schedules['smartwoo_12_hours'] = array(
            'interval' => 12 * 60 * 60, // 12 hours in seconds
            'display'  => __( 'SmartWoo twice Daily', 'smart-woo-service-invoicing' ),
        );

        /** Add a new cron schedule interval for once every two days (48 hours). */
        $schedules['smartwoo_once_every_two_days'] = array(
            'interval' => 2 * 24 * 60 * 60,
            'display'  => __( 'SmartWoo Once Every Two Days', 'smart-woo-service-invoicing' ),
        );

        /** Add a new cron schedule interval for once a day (every 24 hours). */
        $schedules['smartwoo_daily'] = array(
            'interval' => 24 * 60 * 60,
            'display'  => __( 'SmartWoo Daily', 'smart-woo-service-invoicing' ),
        );
        /** Add a new cron schedule interval for every 5 minutes. */
        $schedules['smartwoo_5_minutes'] = array(
            'interval' => 5 * 60,
            'display'  => __( 'SmartWoo Every 5 Minutes', 'smart-woo-service-invoicing' ),
        );

        /** Define a Smart Woo cron interval for every 5 hours. */
        $schedules['smartwoo_5_hours'] = array(
            'interval' => 5 * 60 * 60,
            'display'  => __( 'SmartWoo Every 5 Hours', 'smart-woo-service-invoicing' ),
        );

        return  $schedules;
    }

    /**
     * Fire some actions hooks for our GET actions.
     * 
     * @since 2.0.0
     */
    private function add_actions() {
        if ( ! smartwoo_get_query_param( 'smartwoo_action', false ) ) {
            return;
        }

        do_action( smartwoo_get_query_param( 'smartwoo_action' ) );
    }

    /**
     * Remove order again button button for renewed service orders.
     * 
     * @param WC_Order $order WooComerce Order
     * @return null
     * @since 2.0.12
     */
    public function remove_order_again_button( $order ) {
        if ( $order->is_created_via( SMARTWOO ) ) {
            remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );
        }
    }

    /**
     * Flush smartwoo_order cache.
     * 
     *
     * @since 2.0.12
     */
    public function clear_order_cache() {
        delete_transient( 'smartwoo_count_unprocessed_orders' );
    }

    /**
     * Clear cache
     */
    public static function clear_cache() {
        $cache_keys = [];
    }

    /**
     * Revoke cookie tracking for form submission when user withdraws their conscent.
     * 
     */
    public static function revoke_tracking( $consent_status ) {
        $cookie_name = 'smartwoo_user_tracker';
    
        // If consent is revoked we, delete the cookie.
        if ( isset( $consent_status['functional'] ) && ! $consent_status['functional'] ) {
            setcookie( $cookie_name, '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
        }
    }

    /**
     * Rename First menu item to Dashboard.
     */
    public static function modify_sw_menu() {
        global $submenu;

        if ( isset( $submenu['sw-admin'] ) ) {
            $submenu['sw-admin'][0][0] = 'Dashboard';
        }
    }

    /**
     * Get the current Smart Woo Admin page.
     */
    public static function get_current_screen() {
        if ( ! is_admin() ) {
            return '';
        }
        $id = 'smart-woo';
        $our_pages = array(
            'toplevel_page_sw-admin'        => 'Dashboard',
            $id . '_page_sw-invoices'       => 'Invoices',
            $id . '_page_sw-service-orders' => 'Service Orders',
            $id . '_page_sw-products'       => 'Service Products',
            $id . '_page_sw-options'        => 'Settings',
        );
        
        $screen = get_current_screen();
        $current_screen = $screen->id;

        if ( array_key_exists( $current_screen, $our_pages ) ) {
            return $our_pages[ $current_screen ];
        }

        return '';
    }

    /**
     * Determine whether we are on any of our admin page
     * 
     * @return bool
     */
    public static function in_admin_page() {
        return ! empty( self::get_current_screen() );
    }

    /**
     * Register post states for specific pages.
     *
     * This function adds custom post states to pages based on their IDs.
     * It is hooked into the 'display_post_states' filter.
     *
     * @param array   $post_states An array of post states.
     * @param WP_Post $post        The current post object.
     *
     * @return array Modified array of post states.
     */
    public static function post_states( $post_states, $post ) {
        $service_page_id = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id' ) );

        if ( $post->ID === $service_page_id ) {
            $post_states[] = 'Service Subscription Page';
        }

        if ( $post->ID === $invoice_page_id ) {
            $post_states[] = 'Invoice Management Page';
        }

        return $post_states;
    }

    /**
     * Handle template loading
     */
    public static function template_include( $template ) {
        $service_page_id    = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id' ) );
        $new_config_var     = smartwoo_get_product_config_query_var();
        
        if ( ! empty( $service_page_id ) && is_page( $service_page_id ) ) {
            $template = SMARTWOO_PATH . 'includes/frontend/class-smartwoo-client-portal.php';

        } elseif( ! empty( $invoice_page_id ) && is_page( $invoice_page_id ) ) {
            $template = SMARTWOO_PATH . 'includes/frontend/class-smartwoo-client-portal.php';
        } elseif ( get_query_var( 'configure' ) || get_query_var( $new_config_var ) ) {
            smartwoo_set_document_title( __( 'Product Configuration', 'smart-woo-service-invoicing' ) );
            $template = apply_filters( 'smartwoo_product_config_template', SMARTWOO_PATH . 'templates/frontend/configure.php' );
        }

        return $template;
    }

    /**
     * Register WooCommerce my account page content
     */
    public static function register_woocommerce_account_menus( $items ) {
        $first_three = array_slice( $items, 0, 3, true );
        $first_three['smartwoo-service'] = __( 'Services', 'smart-woo-service-invoicing' );
        $first_three['smartwoo-invoice'] = __( 'Invoices', 'smart-woo-service-invoicing' );
        $items = array_merge( $first_three, $items );
                
        return $items;
    }
    	
    /**
	 * Register pages and their callback handler.
	 * 
	 * @param array $handlers An associative array of pages and handlers.
	 */
	public static function register_service_page_callbacks( $handlers ) {
		$handlers['view-subscription']	= array( 'SmartWoo_Service_Frontend_Template', 'sub_info' );
		$handlers['buy-new']			= array( 'SmartWoo_Service_Frontend_Template', 'product_catalog' );

		return $handlers;
	}

    /**
	 * Register pages and their callback handler.
	 * 
	 * @param array $handlers An associative array of pages and handlers.
	 */
	public static function register_invoice_page_callbacks( $handlers ) {
		$handlers['view-invoice']       = array( 'SmartWoo_Invoice_Frontend_Template', 'invoice_info' );

		return $handlers;
	}

    /**
     * Get supported REST API Routes.
     *
     * @return array
     */
    private function get_rest_routes() {
        $routes = array(
            'smartwoo-admin/v1' => array(
                'routes' => array(
                    '/dashboard' => array(
                        array(
                            'methods'             => WP_REST_Server::ALLMETHODS,
                            'callback'            => array( \SmartWoo_REST_API\AdminDashboard::class, 'dispatch' ),
                            'permission_callback' => [\SmartWoo_REST_API\AdminDashboard::class, 'authorize_request'],
                            'args'  => array(
                                'filter' => array(
                                    'required'    => true,
                                    'type'        => 'string',
                                    'enum'        => array(
                                        'allServices',
                                        'allActiveServices',
                                        'allActiveNRServices',
                                        'allExpiredServices',
                                        'allCancelledServices',
                                        'allSuspendedServices',
                                        'allUnPaidInvoice',
                                        'allNewOrders',
                                        'allDueServices',
                                        'markInvoicePaid',
                                        'bulkActions'
                                    ),
                                    'description' => 'The dataset filter to apply. Valid values include:
                                        - allServices: All services
                                        - allActiveServices: Active services
                                        - allActiveNRServices: Active non-recurring services
                                        - allExpiredServices: Expired services
                                        - allCancelledServices: Cancelled services
                                        - allSuspendedServices: Suspended services
                                        - allUnPaidInvoice: Unpaid invoices
                                        - allNewOrders: New orders
                                        - allDueServices: Due services
                                        - markInvoicePaid: Mark invoice as paid
                                        - bulkActions: Bulk action to perform on the selected table.',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'string' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'string' ),
                                ),

                                'page' => array(
                                    'required'          => false,
                                    'type'              => 'integer',
                                    'default'           => 1,
                                    'description'       => 'The current pagination number',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'integer' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'integer' ),
                                ),
                                'limit' => array(
                                    'required'          => false,
                                    'type'              => 'integer',
                                    'default'           => 25,
                                    'description'       => 'The number of results to return',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'integer' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'integer' ),
                                ),

                                'section'   => array(
                                    'required'  => true,
                                    'type'      => 'string',
                                    'enum'      => array( 'subscriptionList', 'subscriptionList_bulk_action', 'subscribersList', 'needsAttention', 'activities', 'needsAttention_options' ),
                                    'description'       => 'The current admin dashboard section where the response is being rendered',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'string' ),
                                    'validate_callback' => function( $value ) {
                                        return \SmartWoo_REST_API\VALIDATE::enum( $value, \SmartWoo_REST_API\AdminDashboard::allowed_sections_params() );
                                    },
                                ),

                                'invoice_id'            => array(
                                    'required'          => false,
                                    'type'              => 'string',
                                    'description'       => 'The public invoice ID of the invoice in context.',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'string' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'string' ),

                                ),

                                'user_email'    => array(
                                    'required' => false,
                                    'type'          => 'String',
                                    'description'   => 'User email',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'email' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'email' ),
                                ),
                                'email_subject'    => array(
                                    'required' => false,
                                    'type'          => 'String',
                                    'description'   => 'Email subject',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'string' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'string' ),
                                ),
                                'email_body'    => array(
                                    'required' => false,
                                    'type'          => 'String',
                                    'description'   => 'User email',
                                    'sanitize_callback' => array( \SmartWoo_REST_API\SANITIZE::class, 'html' ),
                                    'validate_callback' => array( \SmartWoo_REST_API\VALIDATE::class, 'not_empty' ),
                                ),

                            )
                        ),
                    )
                    
                ),
            ),
        );

        /**
         * Filter the SmartWoo REST API routes.
         *
         * @param array $routes
         */
        return apply_filters( 'smartwoo_rest_routes', $routes );
    }

    /**
     * Register the REST API Routes.
     */
    public function register_rest_routes() {
        $routesets = $this->get_rest_routes();

        foreach ( $routesets as $namespace => $set ) {
            if ( empty( $set['routes'] ) || ! is_array( $set['routes'] ) ) {
                continue;
            }

            foreach ( $set['routes'] as $route => $args ) {
                register_rest_route( $namespace, $route, $args );
            }
        }
    }

    /**
     * Ensures HTTPS/TLS for REST API endpoints within the plugin's namespace.
     *
     * Checks if the current REST API request belongs to the plugin's namespace
     * and enforces HTTPS/TLS requirements if the environment is production.
     *
     * @return WP_Error|null WP_Error object if HTTPS/TLS requirement is not met, null otherwise.
     */
    public function rest_pre_dispatch( $result, $server, $request ) {
        // Check if current request belongs to the plugin's namespace.
        if ( ! str_contains( $request->get_route(), '/smartwoo-' ) ) {
            return;
        }

        // Check if environment is production and request is not over HTTPS.
        if ( 'production' === wp_get_environment_type() && ! is_ssl() ) {
            // Create WP_Error object to indicate insecure SSL.
            $error = new WP_Error( 'connection_not_secure', 'HTTPS/TLS is required for secure communication.', array( 'status' => 400, ) );
            
            // Return the WP_Error object.
            return $error;
        }

        if ( str_contains( $request->get_route(), '/smartwoo-admin/' ) ) {
            add_filter( 'smartwoo_is_frontend', '__return_false' );
        }

    }
}