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
    }

    /**
     * Run action and filters.
     * 
     * @since 1.0.52 Added support for WP_Consent API.
     */
    public function run_hooks() {
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
        
        add_filter( 'get_edit_post_link', array( 'SmartWoo_Product', 'get_edit_url' ), 100, 2 );
        add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 30, 2 );
        add_action( 'woocommerce_save_account_details', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        add_action( 'woocommerce_customer_save_address', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        
        add_action( 'woocommerce_new_order', array( $this, 'clear_order_cache' ), 20, 2 );
        add_action( 'smartwoo_new_service_purchase_complete', array( $this, 'clear_order_cache' ), 20, 2 );
		add_filter( 'smartwoo_subscription_pages', array( __CLASS__, 'register_service_page_callbacks' ) );
		add_filter( 'smartwoo_invoice_pages', array( __CLASS__, 'register_invoice_page_callbacks' ) );

        // add_filter( 'block_categories_all', array( 'SmartWoo_Blocks', 'register_block_category' ), 20, 2 );
    }

    /**
     * Init hooks
     */
    public function init_hooks() {
        self::add_automations();
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
        require_once SMARTWOO_PATH . 'includes/admin/sw-functions.php';
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
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/new-service-order-mail.php';
        require_once SMARTWOO_PATH . 'includes/emails/service-emails/service-processed-mail.php';
        require_once SMARTWOO_PATH . 'includes/class-smartwoo-blocks.php';

        /** Only load admin menu and subsequent files in admin page. */ 
        if ( is_admin() ) {
            require_once SMARTWOO_PATH . 'includes/admin/admin-menu.php';
            require_once SMARTWOO_PATH . 'includes/sw-service/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-invoice/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-product/contr.php';
            require_once SMARTWOO_PATH . 'includes/class-sw-db-update.php';
            
        }

        /** Load fontend file. */ 
        if ( smartwoo_is_frontend() ) {

            require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/woo-forms.php';
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
        $suffix = self::script_suffix();
        if ( function_exists( 'smartwoo_is_frontend' ) && smartwoo_is_frontend() ) {
            wp_enqueue_style( 'smartwoo-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
        }
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() ) {
            $invoice_style_url  = apply_filters( 'smartwoo_invoice_style_url', SMARTWOO_DIR_URL . 'assets/css/smart-woo-invoice' . $suffix . '.css' );
            wp_enqueue_style( 'smartwoo-invoice-style', $invoice_style_url, array(), SMARTWOO_VER, 'all' );
        }

        if ( is_admin() ) {
            $utm_style_uri  = SMARTWOO_DIR_URL . 'assets/css/sw-admin' . $suffix . '.css';
            $admin_style    = SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css';
            $icon_styles    = SMARTWOO_DIR_URL . 'assets/css/sw-icons' . $suffix . '.css';
            wp_enqueue_style( 'smartwoo-admin-utm-style', $utm_style_uri, array(), SMARTWOO_VER, 'all' );
            wp_enqueue_style( 'smartwoo-admin-style', $admin_style, array(), SMARTWOO_VER, 'all' );
            wp_enqueue_style( 'smartwoo-invoice-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo-invoice' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
            wp_enqueue_style( 'smartwoo-icon-style', $icon_styles, array(), SMARTWOO_VER, 'all' );
        }
        wp_enqueue_style( 'jquery-ui-style' );
        wp_enqueue_style( 'smartwoo-jquery-timepicker', SMARTWOO_DIR_URL . 'assets/css/jquery/time-picker' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );

    }

    /**
     * JavaScript registeration.
     */
    public function load_scripts() {
        $suffix = self::script_suffix();

        $l10n   =   array(
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
            'fast_checkout_config'      => smartwoo_fast_checkout_options()
        );

        wp_enqueue_script( 'smartwoo-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        
        wp_register_script( 'smartwoo-jquery-timepicker', SMARTWOO_DIR_URL . '/assets/js/jquery/jquery-time-picker' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), SMARTWOO_VER, true );
        
        
        wp_localize_script( 'smartwoo-script', 'smart_woo_vars', $l10n );
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() || is_admin() ) {
            wp_enqueue_script( 'smartwoo-invoice-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-invoice' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        }
        if ( get_option( 'smartwoo_allow_fast_checkout', false ) && smartwoo_is_frontend() ) {
            wp_enqueue_script( 'smartwoo-fast-checkout', SMARTWOO_DIR_URL . 'assets/js/smart-woo-fast-checkout' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        }
        if ( is_admin() ) {
            wp_enqueue_script( 'smartwoo-admin-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-admin' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
            wp_localize_script( 'smartwoo-admin-script', 'smartwoo_admin_vars', $l10n );
        }
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
        add_rewrite_rule( '^configure/?$', 'index.php?configure=true', 'top' );
        foreach( $this->get_query_vars() as $key => $var ) {
            add_rewrite_endpoint( $var, EP_PAGES );
        }

        if ( false === get_transient( '_smartwoo_flushed_rewrite_rules', false ) ) {
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
	 * Add automation schedules.
	 */
	private static function add_automations() {

		/**
		 * Schedule the auto-renewal event.
		 */
		if ( ! wp_next_scheduled( 'smartwoo_auto_service_renewal' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_auto_service_renewal' );
		}

        /**
         * Five Hourly schedule
         */
		if ( ! wp_next_scheduled( 'smartwoo_five_hourly' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_five_hourly' );
		}

        /**
         * Schedule to periodically count all services in the database.
         * 
         * @since 2.0.12
         */
        if ( ! wp_next_scheduled( 'smartwoo_service_scan' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_service_scan' );
		}

		/** Daily task automation. */
		if ( ! wp_next_scheduled( 'smartwoo_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_daily', 'smartwoo_daily_task' );
		}

		/** Once in 48hrs( runs one in two days) task */
		if ( ! wp_next_scheduled( 'smartwoo_once_in48hrs_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_once_every_two_days', 'smartwoo_once_in48hrs_task' );
		}

		/** Twice Daily task automation */
		if ( ! wp_next_scheduled( 'smartwoo_twice_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_12_hours', 'smartwoo_twice_daily_task' );
		}

        if ( false === get_option( '__smartwoo_automation_last_scheduled_date', false ) ) {
    		update_option( '__smartwoo_automation_last_scheduled_date', current_time( 'timestamp' ) );
        
        }
	}

    /**
     * Fire some actions hooks for our GET actions.
     * 
     * @since 2.0.0
     */
    private function add_actions() {
        
        if ( isset( $_GET['smartwoo_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            do_action( sanitize_text_field( wp_unslash( $_GET['smartwoo_action'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }
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
     * @param int $order_id Order ID
     * @param WC_Order WooCommerce Order object.
     * @since 2.0.12
     */
    public function clear_order_cache( $order_id, $order ) {
        if ( smartwoo_check_if_configured( $order ) || $order->is_created_via( SMARTWOO ) ) {
            delete_transient( 'smartwoo_count_unprocessed_orders' );
        }
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
        $service_page_id = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id' ) );
        
        if ( ! empty( $service_page_id ) && is_page( $service_page_id ) ) {
            $template = SMARTWOO_PATH . 'includes/frontend/class-smartwoo-client-portal.php';

        } elseif( ! empty( $invoice_page_id ) && is_page( $invoice_page_id ) ) {
            $template = SMARTWOO_PATH . 'includes/frontend/class-smartwoo-client-portal.php';
        } elseif ( get_query_var( 'configure' ) ) {
            smartwoo_set_document_title( __( 'Product Configuration', 'smart-woo-service-invoicing' ) );
            $template = apply_filters( 'smartwoo_product_config_template', SMARTWOO_PATH . 'templates/configure.php' );
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
}