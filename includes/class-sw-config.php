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
        define( 'SMARTWOO_SERVICE_LOG_TABLE', $wpdb->prefix . 'sw_service_logs' );
        define( 'SMARTWOO_INVOICE_LOG_TABLE', $wpdb->prefix . 'sw_invoice_logs' );
        define( 'SMARTWOO_ASSETS_TABLE', $wpdb->prefix . 'sw_assets' );
        define( 'SMARTWOO_PLUGIN_BASENAME', plugin_basename( SMARTWOO_FILE ) );
        define( 'SMARTWOO_UPLOAD_DIR', trailingslashit( wp_upload_dir()['basedir'] ) . 'smartwoo-uploads' );
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
        add_action( 'woocommerce_order_details_before_order_table', array( $this, 'remove_order_again_button' ) );
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
            add_action( 'wp_consent_api_consent_changed', array( __CLASS__, 'revoke_tracking' ) );
                      
        }

        add_action( 'template_redirect', array( $this, 'protect_endpoints' ), 10 );
        add_action( 'init', array( $this, 'init_hooks' ) );
        add_filter( 'woocommerce_account_menu_items', 'smartwoo_register_woocommerce_account_menu', 40 );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_filter( 'woocommerce_account_smartwoo-invoice_endpoint', 'smartwoo_invoice_myaccount_content' );
        add_filter( 'woocommerce_account_smartwoo-service_endpoint', 'smartwoo_service_myaccount_content' );
        add_filter( 'template_include', array( __CLASS__, 'product_config_template' ) );

        /** Register our crons */
        add_filter( 'cron_schedules', array( $this, 'register_cron' ) );
        
        add_filter( 'get_edit_post_link', array( 'SmartWoo_Product', 'get_edit_url' ), 100, 2 );
        add_action( 'woocommerce_save_account_details', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        add_action( 'woocommerce_customer_save_address', 'smartwoo_save_edited_bio_and_user_url', 20, 2 );
        
        add_action( 'admin_post_smartwoo_create_product', 'smartwoo_process_new_product' );
        add_action( 'admin_post_smartwoo_edit_product', 'smartwoo_process_product_edit' );
        add_action( 'woocommerce_new_order', array( $this, 'clear_order_cache' ), 20, 2 );

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
        require_once SMARTWOO_PATH . 'includes/sw-invoice/invoice.downloadable.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/class-sw-invoice-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-invoice/sw-invoice-function.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/class-sw-service-database.php';
        require_once SMARTWOO_PATH . 'includes/sw-service/sw-service-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/class-sw-product.php';
        require_once SMARTWOO_PATH . 'includes/sw-product/sw-product-functions.php';
        require_once SMARTWOO_PATH . 'includes/sw-utm.php';
        require_once SMARTWOO_PATH . 'includes/frontend/woocommerce/my-account.php';
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
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
        if ( function_exists( 'smartwoo_is_frontend' ) && smartwoo_is_frontend() ) {
            wp_enqueue_style( 'smartwoo-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
        }
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() ) {
            wp_enqueue_style( 'smartwoo-invoice-style', SMARTWOO_DIR_URL . 'assets/css/smart-woo-invoice' . $suffix . '.css', array(), SMARTWOO_VER, 'all' );
            if( isset( $_GET['view_invoice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- False positive, global array value not processed.
                wp_enqueue_style( 'dashicons' );
            }
        } 

        if ( is_admin() ) {
            $utm_style_uri  = SMARTWOO_DIR_URL . 'assets/css/sw-admin' . $suffix . '.css';
            $admin_style    = SMARTWOO_DIR_URL . 'assets/css/smart-woo' . $suffix . '.css';
            wp_enqueue_style( 'smartwoo-admin-utm-style', $utm_style_uri, array(), SMARTWOO_VER, 'all' );
            wp_enqueue_style( 'smartwoo-admin-style', $admin_style, array(), SMARTWOO_VER, 'all' );
        }
    }

    /**
     * JavaScript registeration.
     */
    public function load_scripts() {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';

        $l10n   =   array(
            'smartwoo_plugin_url'       => SMARTWOO_DIR_URL,
            'smartwoo_assets_url'       => SMARTWOO_DIR_URL . 'assets/',
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'woo_my_account_edit'       => wc_get_account_endpoint_url( 'edit-account' ),
            'woo_payment_method_edit'   => wc_get_account_endpoint_url( 'payment-methods' ),
            'woo_billing_eddress_edit'  => wc_get_account_endpoint_url( 'edit-address/billing' ),
            'sw_admin_page'             => esc_url_raw( admin_url( 'admin.php?page=sw-admin' ) ),
            'new_service_page'          => esc_url_raw( admin_url( 'admin.php?page=sw-admin&action=add-new-service')),
            'admin_invoice_page'        => esc_url_raw( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) ),
            'admin_order_page'          => esc_url_raw( admin_url( 'admin.php?page=sw-service-orders' ) ),
            'sw_product_page'           => esc_url_raw( admin_url( 'admin.php?page=sw-products' ) ),
            'sw_options_page'           => esc_url_raw( admin_url( 'admin.php?page=sw-options' ) ),
            'security'                  => wp_create_nonce( 'smart_woo_nonce' ),
            'home_url'                  => home_url( '/' ),
            'is_account_page'           => is_account_page(),
            'cart_is_configured'        => apply_filters( 'cart_is_configured', false ),
            'never_expire_value'        => apply_filters( 'smartwoo_never_expire_value', '' ),
            'wp_spinner_gif_loader'     => admin_url('images/spinner.gif'),
            'smartwoo_plugin_page'      => apply_filters( 'smartwoo_plugin_url', 'https://callismart.com.ng/smart-woo-service-invoicing' ),
            'smartwoo_pro_page'         => apply_filters( 'smartwoo_pro_purchase_page', 'https://callismart.com.ng/smart-woo-service-invoicing/#go-pro' ),
            
        );

        wp_enqueue_script( 'smartwoo-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        wp_localize_script( 'smartwoo-script', 'smart_woo_vars', $l10n );
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id', 0 ) );

        if ( is_page( $invoice_page_id ) || is_account_page() || is_admin() ) {
            wp_enqueue_script( 'smartwoo-invoice-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-invoice' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
        }

        if ( is_admin() ) {
            wp_enqueue_script( 'smartwoo-admin-script', SMARTWOO_DIR_URL . 'assets/js/smart-woo-admin' . $suffix . '.js', array( 'jquery' ), SMARTWOO_VER, true );
            wp_localize_script( 'smartwoo-admin-script', 'smartwoo_admin_vars', $l10n );
        }
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
            wp_cache_delete( 'smartwoo_count_unprocessed_orders', 'smartwoo_orders' );
        }
    }

    /**
     * Set up product configuration page template.
     *
     * This function is a callback for the 'template_include' filter and returns
     * the template file path for the configure page or the original template.
     *
     * @param string $template The original template file path.
     * @return string The template file path for the configure page or the original template.
     */

    public static function product_config_template( $template ) {
        // Check if the current page is the configure page.
        if ( get_query_var( 'configure' ) ) {
            // Define the path to the configure template file.
            $filtered_template = apply_filters( 'smartwoo_product_config_template', '' );
            smartwoo_set_document_title( __( 'Product Configuration', 'smart-woo-service-invoicing' ) );

            if ( file_exists( $filtered_template ) ) {
                $template = $filtered_template;

            } else {
                $template = SMARTWOO_PATH . 'templates/configure.php';
            }
        }

        return $template;
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
}