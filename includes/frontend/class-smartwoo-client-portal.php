<?php
/**
 * The client portal class file.
 * 
 * @author Callistus
 * @package SmartWoo\Classes
 * @since 2.4.0
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Handles the client portal.
 */
class SmartWoo_Client_Portal {
	/**
	 * The current page requested.
	 * 
	 * @var string $page The current page or endpoint.
	 */
	private $page = '';

	/**
	 * All pages and their callback functions.
	 * 
	 * @var array $pages An associative array of page_or_endpoint_name => callback function.
	 */
	private $pages = array();

	/**
	 * A singleton instance of the class
	 * 
	 * @var self $instance
	 */
	private static $instance = null;

	/**
	 * Class constructor
	 */
	private function __construct() {
		$this->router_init();
		$this->dispatch();
	}

	/**
	 * Set up the current page
	 */
	private function router_init() {
		global $wp_query;

		$endpoints = SmartWoo_Config::instance()->get_query_vars();

		foreach ( $endpoints as $page ) {
			if ( isset( $wp_query->query_vars[$page] ) ) {
				$this->page = $page;
				break;
			}
		}

		/**
		 * @see `$this->pages`
		 */
		$service_page_handlers	= apply_filters( 'smartwoo_subscription_pages', array() );
		$invoice_page_handlers	= apply_filters( 'smartwoo_invoice_pages', array() );
		$this->pages			= apply_filters( 'smartwoo_client_portal_pages', array_merge( $invoice_page_handlers, $service_page_handlers ) );

	}

	/**
	 * Handle the pages and endpoints accordingly
	 */
	private function dispatch() {
		$this->get_header();
		
		$handler	= $this->get_default_page_handler();

		if ( ! empty( $this->page ) && isset( $this->pages[$this->page] ) ) {
			$handler = $this->pages[$this->page];
		}

		if ( ! is_user_logged_in() && 'buy-new' !== $this->page ) {
			$handler =  array( $this, 'login_page' );
		}

		if ( 'status' === $this->page ) {
			// Get the status endpoint callback.
			$handler = self::get_status_handler();
		}

		if ( is_callable( $handler ) ) {
			call_user_func( $handler );
		}

		$this->get_footer();
	}

	/**
	 * Get the template header
	 */
	private function get_header() {
		if ( smartwoo_is_block_theme() ) {
			// get_template_part( 'header' );
			do_action( 'wp_head' );
		} else {
			get_header();
		}
	}

	/**
	 * Get the template footer
	 */
	private function get_footer() {
		if ( smartwoo_is_block_theme() ) {
			// get_template_part( 'footer' );
			do_action( 'wp_footer' );
		} else {
			get_footer();
    }
	}

	/**
	 * Render login page
	 */
	private function login_page() {
		global $wp;
	
		wp_enqueue_style( 'dashicons' );
		$args =  array( 
			'notice' => smartwoo_notice( 'Login to access this page.' ),
			'redirect' => home_url( $wp->request ?? '' )
		);
		include_once SMARTWOO_PATH . 'templates/login.php';
	}

	/**
	 * Instanciate a singleton instance of this class
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the default page callback functions as handlers for either the service subscription
	 * page or the invoices page.
	 * 
	 * @return 
	 */
	private function get_default_page_handler() {
		$service_page_id = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id' ) );
		
		if ( is_page( $service_page_id ) ) {
			$handler = array( 'SmartWoo_Service_Frontend_Template', 'main_page' );
		} elseif ( is_page( $invoice_page_id ) ) {
			$handler = array( 'SmartWoo_Invoice_Frontend_Template', 'main_page' );
		}

		return $handler;
		
	}

	/**
	 * Get the sort callback functions as handlers for either the service subscription
	 * page or the invoices page.
	 * 
	 * @return 
	 */
	private static function get_status_handler() {
		$service_page_id = absint( get_option( 'smartwoo_service_page_id' ) );
        $invoice_page_id = absint( get_option( 'smartwoo_invoice_page_id' ) );
		
		if ( is_page( $service_page_id ) ) {
			$handler = array( 'SmartWoo_Service_Frontend_Template', 'sort' );
		} elseif ( is_page( $invoice_page_id ) ) {
			$handler = array( 'SmartWoo_Invoice_Frontend_Template', 'sort' );
		}

		return $handler;
		
	}
}

SmartWoo_Client_Portal::instance();