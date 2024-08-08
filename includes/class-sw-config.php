<?php
/**
 * File name class-sw-config.php
 * Smart Woo Invironment set up class file.
 * 
 * @author Callistus
 * @package SmartWoo\classes
 * @since 1.0.2
 */
defined( 'ABSPATH' ) || exit; // Prevent direct access.

defined ( 'SMARTWOO' ) || defined( 'SMARTWOO_FILE' ) || exit; // Must be accessed through the main file.

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
        define( 'SMARTWOO_SERVICE_LOG_TABLE', $wpdb->prefix . 'sw_service_logs' );
        define( 'SMARTWOO_INVOICE_LOG_TABLE', $wpdb->prefix . 'sw_invoice_logs' );
        define( 'SMARTWOO_ASSETS_TABLE', $wpdb->prefix . 'sw_assets' );
        define( 'SMARTWOO_PLUGIN_BASENAME', plugin_basename( SMARTWOO_FILE ) );
        $this->init();
    }

    /**
     * Init.
     */
    private function init() {
        add_action( 'woocommerce_loaded', array( $this, 'check_woocommerce' ) );
        add_action( 'smartwoo_init', array( $this, 'load_dependencies' ) );
        add_action( 'admin_init', array( $this, 'woocommerce_dependency_nag' ) );
        add_action( 'smartwoo_loaded', array( $this, 'before_init' ) );
        add_action( 'before_woocommerce_init', array( $this, 'woocommerce_custom_order_compatibility' ) );
        register_activation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'install' ) );
        register_deactivation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'deactivate' ) );
    }

    /**
     * Before init hooks.
     * 
     * @since 1.0.52 Added support for WP_Consent API.
     */
    public function before_init() {
        if ( class_exists( WP_CONSENT_API::class ) ) {
            add_filter( 'wp_consent_api_registered_' . SMARTWOO_PLUGIN_BASENAME, '__return_true' );            
        }

        add_action( 'template_redirect', array( $this, 'protect_endpoints' ), 10 );
        add_action( 'init', array( $this, 'init_hooks' ) );
        add_filter( 'woocommerce_account_menu_items', 'smartwoo_register_woocommerce_account_menu', 40 );
        add_filter( 'the_title', 'smartwoo_myaccount_titles', 10, 3 );
        add_filter( 'woocommerce_account_smartwoo-invoice_endpoint', 'smartwoo_invoice_myaccount_content' );
        add_filter( 'woocommerce_account_smartwoo-service_endpoint', 'smartwoo_service_myaccount_content' );

        /** Register our crons */
        add_filter( 'cron_schedules', array( $this, 'register_cron' ) );
        
        add_filter( 'get_edit_post_link', array( 'SmartWoo_Product', 'get_edit_url' ), 100, 2 );
        add_action( 'smartwoo_user_cancelled_service', 'smartwoo_user_service_cancelled_mail', 100 );
        add_action( 'smartwoo_user_cancelled_service', 'smartwoo_service_cancelled_mail_to_admin', 100 );
        add_action( 'smartwoo_user_opted_out', 'smartwoo_user_service_optout_mail', 100 );
        add_action( 'smartwoo_once_in48hrs_task', 'smartwoo_payment_reminder' );
        add_action( 'smartwoo_service_expired', 'smartwoo_send_service_expiration_email' );
        add_action( 'smartwoo_daily_task', 'smartwoo_send_expiry_mail_to_admin' );
        add_action( 'smartwoo_service_renewed', 'smartwoo_renewal_sucess_email' );
        add_action( 'smartwoo_expired_service_activated', 'smartwoo_renewal_sucess_email' );
        add_action( 'smartwoo_auto_invoice_created', 'smartwoo_send_auto_renewal_email', 10, 2 );
        add_action( 'smartwoo_invoice_is_paid', 'smartwoo_invoice_paid_mail' );  
        add_action( 'woocommerce_save_account_details', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        add_action( 'woocommerce_customer_save_address', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        
        add_action( 'admin_post_smartwoo_create_product', 'smartwoo_process_new_product' );
        add_action( 'admin_post_smartwoo_edit_product', 'smartwoo_process_product_edit' );

    }

    /**
     * Init hooks
     */
    public function init_hooks() {
        self::add_automations();
        $this->add_rules();
        $this->add_actions();
    }

    /**
     * Check if WooCommerce is loaded
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
     * load files.
     */
    public function include() {

        require_once SMARTWOO_PATH . 'includes/admin/sw-functions.php';
        require_once SMARTWOO_PATH . 'includes/class-sw.php';
        require_once SMARTWOO_PATH . 'includes/admin/include/smart-woo-manager.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/invoice.downloadable.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/sw-invoice-function.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/sw-service-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/class-sw-product.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-product-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-order-config.php';
        require_once SMARTWOO_PATH . 'includes/sw-utm.php';
        require_once SMARTWOO_PATH . 'templates/email-templates.php';
        require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/my-account.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-assets.php';


        /** Only load admin menu and subsequent files in admin page. */ 
        if ( is_admin() ) {
            require_once SMARTWOO_PATH . 'includes/admin/admin-menu.php';
            require_once SMARTWOO_PATH . 'includes/sw-service/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-invoice/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-product/contr.php';
            
        }

        /** Load fontend file. */ 
        if ( smartwoo_is_frontend() ) {

            require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/contr.php';
            require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/woo-forms.php';
            require_once SMARTWOO_PATH . 'includes/frontend/invoice/contr.php';
            require_once SMARTWOO_PATH . 'includes/frontend/invoice/template.php';
            require_once SMARTWOO_PATH . 'includes/frontend/shortcode.php';
            require_once SMARTWOO_PATH . 'includes/frontend/service/template.php';
            require_once SMARTWOO_PATH . 'includes/frontend/service/contr.php';

        }
        add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 20 );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_styles' ), 22 );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_styles' ), 22 );
    }

    /**
     * Instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_styles() {

        if ( function_exists( 'smartwoo_is_frontend' ) && smartwoo_is_frontend() ) {
        wp_enqueue_style( 'smartwoo-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo-min.css', array(), SMARTWOO_VER, 'all' );
        
        }
        wp_enqueue_style( 'smartwoo-admin-utm-style', SMARTWOO_DIR_URL . 'assets/css/sw-admin-min.css', array(), SMARTWOO_VER, 'all' );

        if ( is_admin() ) {
            wp_enqueue_style( 'smartwoo-admin-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo-min.css', array(), SMARTWOO_VER, 'all' );
        }
    }

    public function load_scripts() {
        wp_enqueue_script( 'smartwoo-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-min.js', array( 'jquery' ), SMARTWOO_VER, true );
    
        // Script localizer.
        wp_localize_script(
            'smartwoo-script',
            'smart_woo_vars',
            array(
                'ajax_url'                  => admin_url( 'admin-ajax.php' ),
                'woo_my_account_edit'       => wc_get_account_endpoint_url( 'edit-account' ),
                'woo_payment_method_edit'   => wc_get_account_endpoint_url( 'payment-methods' ),
                'woo_billing_eddress_edit'  => wc_get_account_endpoint_url( 'edit-address/billing' ),
                'admin_invoice_page'        => esc_url_raw( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) ),
                'sw_admin_page'             => esc_url_raw( admin_url( 'admin.php?page=sw-admin' ) ),
                'sw_product_page'           => esc_url_raw( admin_url( 'admin.php?page=sw-products' ) ),
                'security'                  => wp_create_nonce( 'smart_woo_nonce' ),
                'home_url'                  => home_url( '/' ),
                'never_expire_value'        => '',
                'wp_spinner_gif_loader'     => admin_url('images/spinner.gif')
            )
        );
    }

    /**
     * Throw error when WooCommerce is not active.
     */
    public function woocommerce_dependency_nag() {
       if ( ! $this->woocommerce_loaded ) {
           // Throw error
           $woo_plugin_url = 'https://wordpress.org/plugins/woocommerce/';
           $notice         = sprintf(
               'Smart Woo Service Invoicing requires WooCommerce to be active. Please <a href="%s" class="activate-link" target="_blank">activate WooCommerce</a> or deactivate the plugin to avoid a fatal error.',
               esc_url( $woo_plugin_url )
           );
           add_action(
               'admin_notices',
               function () use ( $notice ) {
                   echo '<div class="notice notice-error"><p>' . wp_kses( $notice, array(
                        'a' => array(
                            'href' => array(),
                            'class' => array()
                        )
                       ) ) . '</p></div>';
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

    /** Smart Woo page rewrite rules */
    public function add_rules() {
        /** Product configuration page */
        add_rewrite_rule( '^configure/?$', 'index.php?configure=true', 'top' );

        /** WooCommerce my-acount endpoints */
        add_rewrite_endpoint( 'smartwoo-invoice', EP_PAGES );
        add_rewrite_endpoint( 'smartwoo-service', EP_PAGES );

        /** Service Subscription page */
        add_rewrite_endpoint( 'buy-new', EP_PAGES );
        add_rewrite_endpoint( 'view-subscription', EP_PAGES );
        add_rewrite_endpoint( 'view-subscriptions-by', EP_PAGES );
        add_rewrite_endpoint( 'upgrade', EP_PAGES );
        add_rewrite_endpoint( 'downgrade', EP_PAGES );

        if ( false === get_transient( '_smartwoo_flushed_rewrite_rules', false ) ) {
            flush_rewrite_rules();
            set_transient( '_smartwoo_flushed_rewrite_rules', true, WEEK_IN_SECONDS );
        }
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
            'view-subscriptions-by'
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
		 *
		 * This function checks if the 'smartwoo_auto_service_renewal' is not already scheduled
		 * and schedules it to run every 5 hours using the 'smartwoo_5_hours' cron interval.
		 */
		if ( ! wp_next_scheduled( 'smartwoo_auto_service_renewal' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_auto_service_renewal' );
		}

		/** Schedule some dynamic task to run five minutely. */
		if ( ! wp_next_scheduled( 'smartwoo_5_minutes_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_minutes', 'smartwoo_5_minutes_task' );
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

		/** Automate refunds */
		if ( ! wp_next_scheduled( 'smartwoo_refund_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'once_every_two_days', 'smartwoo_refund_task' );
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
        if ( isset( $_GET['smartwoo_action'] ) && has_action( $_GET['smartwoo_action'] ) ) {
            do_action( $_GET['smartwoo_action'] );
        }
    }
}