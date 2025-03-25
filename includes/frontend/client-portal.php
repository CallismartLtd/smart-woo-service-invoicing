<?php
/**
 * The Smart Woo client portal handles service subscription, invoice and product pages.
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo Service Client portal class.
 */
class SmartWoo_Client_Portal {

    /**
     * Static instance
     */
    private static $instance = null;
	/**
	 * Current user.
	 * 
	 * @var WP_User
	 */
	protected $user;

	/**
	 * Current Page
	 */
	protected $page;

    /**
     * Class constructor
     */
    public function __construct() {
        $this->user = wp_get_current_user();
        $this->set_current_page();

        self::render();
    }

    /**
     * Set up current page.
     */
    private function set_current_page() {
        global $wp_query;
        
        $service_page_id    = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id    = absint( get_option( 'smartwoo_invoice_page_id' ) );
        $page_slug          = '';
        if ( is_page( $service_page_id ) ) {
            $page_slug = 'service';
        } elseif ( is_page( $invoice_page_id ) ) {
            $page_slug = 'invoice';
        }

        if ( isset ( $wp_query->query_vars['buy-new'] ) ) {

            $this->page = $page_slug . '/buy-new';

        } elseif ( isset( $wp_query->query_vars['view-subscription'] ) ) {

            $this->page = $page_slug . '/view-subscription';

        } elseif ( isset( $wp_query->query_vars['sort'] ) ) {

            $this->page = $page_slug . '/sort';

        } elseif ( isset( $wp_query->query_vars['downgrade'] ) ) {

            $this->page = $page_slug . '/downgrade';
            
        } elseif ( isset( $wp_query->query_vars['upgrade'] ) ) {

            $this->page = $page_slug . '/upgrade';
        } elseif ( isset( $wp_query->query_vars['view-invoice'] ) ) {

            $this->page = $page_slug . '/view-invoice';
        }
    
    }

    /**
     * Render the content of the current page
     */
    public static function render() {
        $page_contr = explode( '/', self::instance()->page, 2 );
        new SmartWoo_Service_Frontend( $page_contr[0] );

    }

    /**
     * instance of current class
     * 
     * @return self
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Short code handler
     */
    public static function shortcode() {
        ob_start();
        self::instance();
        return ob_get_clean();
    }
}