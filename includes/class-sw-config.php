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
        $this->init();
    }

    /**
     * Init hooks.
     */
    public function init() {
        add_action( 'woocommerce_loaded', array( $this, 'check_woocommerce' ) );
        add_action( 'smartwoo_init', array( $this, 'load_dependencies' ) );
        add_action( 'admin_init', array( $this, 'woocommerce_dependency_nag' ) );
        add_action( 'smart_woo_loaded', array( $this, 'init_hooks' ) );
        add_action( 'before_woocommerce_init', array( $this, 'woocommerce_custom_order_compatibility' ) );
        register_activation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'install' ) );
        register_deactivation_hook( SMARTWOO_FILE, array( 'SmartWoo_Install', 'deactivate' ) );
    }
    public function init_hooks() {
        add_action( 'init', array( $this, 'add_rules' ) );
        add_filter( 'woocommerce_account_menu_items', 'smartwoo_register_woocommerce_account_menu', 40 );
        add_filter( 'woocommerce_account_smartwoo-invoice_endpoint', 'smartwoo_invoice_myacoount_content' );
        add_filter( 'woocommerce_account_smartwoo-service_endpoint', 'smartwoo_service_myacoount_content' );
        self::add_automations();

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
            do_action( 'smart_woo_loaded' );
        }
    }

    /**
     * load files.
     */
    public function include() {

        require_once SMARTWOO_PATH . 'admin/sw-functions.php';
        require_once SMARTWOO_PATH . 'admin/include/cron-schedule.php';
        require_once SMARTWOO_PATH . 'admin/include/service-remote.php';
        require_once SMARTWOO_PATH . 'admin/include/smart-woo-manager.php';
        include_once SMARTWOO_PATH . 'admin/include/sw_service_api.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/invoice.downloadable.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/sw-invoice-function.php';
        require_once SMARTWOO_PATH . 'includes/sw-logger/class-sw-invoice-log.php';
        require_once SMARTWOO_PATH . 'includes/sw-logger/class-sw-service-log.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/sw-service-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/class-sw-product.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-product-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-order-config.php';
        require_once SMARTWOO_PATH . 'templates/email-templates.php';

        // Only load compatibility file when TeraWallet plugin is installed.
        if ( function_exists( 'woo_wallet' ) ) {
            require_once SMARTWOO_PATH . 'admin/include/tera-wallet-int.php';
        }

        // Only load admin menu and subsequent files in admin page.
        if ( is_admin() ) {
            require_once SMARTWOO_PATH . 'admin/admin-menu.php';
            require_once SMARTWOO_PATH . 'includes/sw-service/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-invoice/contr.php';
            require_once SMARTWOO_PATH . 'includes/sw-product/contr.php';
            
        }

        // Load fontend file
        if ( smartwoo_is_frontend() ) {

            require_once SMARTWOO_PATH . 'frontend/woocommerce/contr.php';
            require_once SMARTWOO_PATH . 'frontend/woocommerce/my-account.php';
            require_once SMARTWOO_PATH . 'frontend/woocommerce/woo-forms.php';
            require_once SMARTWOO_PATH . 'frontend/invoice/contr.php';
            require_once SMARTWOO_PATH . 'frontend/invoice/template.php';
            require_once SMARTWOO_PATH . 'frontend/shortcode.php';
            require_once SMARTWOO_PATH . 'frontend/service/template.php';
            require_once SMARTWOO_PATH . 'frontend/service/contr.php';

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
    }

    public function load_styles() {

        if ( function_exists( 'smartwoo_is_frontend' ) && smartwoo_is_frontend() ) {
        wp_enqueue_style( 'smartwoo-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo.css', array(), SMARTWOO_VER, 'all' );
        
        }
    
        if ( is_admin() ) {
            wp_enqueue_style( 'smartwoo-admin-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo.css', array(), SMARTWOO_VER, 'all' );
        }
    }

    public function load_scripts() {
        wp_enqueue_script( 'smartwoo-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo.js', array( 'jquery' ), SMARTWOO_VER, true );
    
        // Script localizer.
        wp_localize_script(
            'smartwoo-script',
            'smart_woo_vars',
            array(
                'ajax_url'                 => admin_url( 'admin-ajax.php' ),
                'woo_my_account_edit'      => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'edit-account/',
                'woo_payment_method_edit'  => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'payment-methods/',
                'woo_billing_eddress_edit' => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'edit-address/billing',
                'admin_invoice_page'       => esc_url_raw( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) ),
                'sw_admin_page'            => esc_url( admin_url( 'admin.php?page=sw-admin' ) ),
                'sw_product_page'           => esc_url( admin_url( 'admin.php?page=sw-products' ) ),
                'security'                 => wp_create_nonce( 'smart_woo_nonce' ),
                'user_invoice_page'			=> '',
                'home_url'                 => esc_url( home_url( '/' ) ),
                'never_expire_value'       => '',
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

    public static function add_rules() {
        add_rewrite_rule( '^configure/?$', 'index.php?configure=true', 'top' );
        add_rewrite_endpoint( 'smartwoo-invoice', EP_PAGES );
        add_rewrite_endpoint( 'smartwoo-service', EP_PAGES );
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

		update_option( '__smartwoo_automation_last_scheduled_date', current_time( 'timestamp' ) );
	}
}