<?php
/**
 * The client service subscription page controller
 * 
 * @author Callistus
 * @package SmartWoo\Classes
 * @since 2.4.0
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Handles the client service subscription portal.
 */
class SmartWoo_Front_Service_Subscription_Page {
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
		$this->pages = apply_filters( 'smartwoo_subscription_pages', array() );

	}

	/**
	 * Handle the pages and endpoints accordingly
	 */
	private function dispatch() {
		$this->get_header();
		
		$handler	= array( 'SmartWoo_Service_Frontend_Template', 'main_page' );
		if ( ! empty( $this->page ) && isset( $this->pages[$this->page] ) ) {
			$handler = $this->pages[$this->page];
		}

		if ( ! is_user_logged_in() && 'buy-new' !== $this->page ) {
			$handler =  array( $this, 'login_page' );
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
		if ( ! smartwoo_is_block_theme() ) {
			get_header();

		} else {
			do_action( 'wp_head' ); // We don't support FSE at the moment
			wp_admin_bar_render(); // Admin bar 
		}
	}

	/**
	 * Get the template footer
	 */
	private function get_footer() {
		if ( ! smartwoo_is_block_theme() ) {
			get_footer();
		} else {
			do_action( 'wp_footer' );
		}
	}

	/**
	 * Render login page
	 */
	public function login_page() {
		wp_enqueue_style( 'dashicons' );
		$args =  array( 
			'notice' => smartwoo_notice( 'Login to access this page.' ),
			'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) )
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

}

SmartWoo_Front_Service_Subscription_Page::instance();