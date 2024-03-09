<?php
// phpcs:ignoreFile

/**
 * File name   : src.php
 * Author      : Callistus
 * Description : Source Loader file
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Throw error when WooCommerce is not active
 */
function sw_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		// Throw error
		$woo_plugin_url = 'https://wordpress.org/plugins/woocommerce/';
		$notice         = sprintf(
			'Smart Woo Service Invoicing requires WooCommerce to be active. Please <a href="%s" class="activate-link" target="_blank">activate WooCommerce</a>. or deactive plugin to avoid fatal error',
			$woo_plugin_url
		);
		add_action(
			'admin_notices',
			function () use ( $notice ) {
				echo '<div class="notice notice-error is-dismissible">' . $notice . '</div>';
			}
		);
	}
}


function enqueue_smart_woo_scripts() {

	// Enqueue styles for both admin and frontend
	wp_enqueue_style( 'smart-woo-invoice-style', SW_DIR_URL . 'assets/css/smart-woo.css', array(), '1.0', 'all' );

	if ( is_admin() ) {
		// Enqueue admin-specific styles
		wp_enqueue_style( 'smart-woo-invoice-style-admin', SW_DIR_URL . 'assets/css/smart-woo.css', array(), '1.0', 'all' );
	}

	// Enqueue the JavaScript file
	wp_enqueue_script( 'smart-woo-script', SW_DIR_URL . 'assets/js/smart-woo.js', array( 'jquery' ), '1.0', true );

	// Localize the script
	wp_localize_script(
		'smart-woo-script',
		'smart_woo_vars',
		array(
			'ajax_url'                 => admin_url( 'admin-ajax.php' ),
			'woo_my_account_edit'      => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'edit-account/',
			'woo_payment_method_edit'  => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'payment-methods/',
			'woo_billing_eddress_edit' => get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'edit-address/billing',
			'admin_invoice_page'       => esc_url( admin_url( 'admin.php?page=sw-invoices&action=dashboard' ) ),
			'sw_admin_page'            => esc_url( admin_url( 'admin.php?page=sw-admin' ) ),
			'security'                 => wp_create_nonce( 'smart_woo_nonce' ),
			'home_url'                 => esc_url( home_url( '/' ) ),
			'never_expire_value'       => '',
		)
	);
}

// Hook into the appropriate action to enqueue the scripts and styles
add_action( 'admin_enqueue_scripts', 'enqueue_smart_woo_scripts' );
add_action( 'wp_enqueue_scripts', 'enqueue_smart_woo_scripts' );


/**
 * Hook into 'smart_woo_init' to load the plugin files
 * this hook is only fired when WooCommerce is active.
 */
add_action( 'smart_woo_init', 'sw_load_dependencies' );

/**
 * Load plugin files
 */
function sw_load_dependencies() {

	require_once SW_ABSPATH . 'admin/sw-functions.php';
	require_once SW_ABSPATH . 'admin/include/cron-schedule.php';
	require_once SW_ABSPATH . 'admin/include/service-remote.php';
	require_once SW_ABSPATH . 'admin/include/smart-woo-manager.php';
	include_once SW_ABSPATH . 'admin/include/sw_service_api.php';
	require_once SW_ABSPATH . 'includes/sw-invoice/invoice.downloadable.php';
	require_once SW_ABSPATH . 'includes/sw-invoice/class-sw-invoice.php';
	require_once SW_ABSPATH . 'includes/sw-invoice/class-sw-invoice-database.php';
	require_once SW_ABSPATH . 'includes/sw-invoice/sw-invoice-function.php';
	require_once SW_ABSPATH . 'includes/sw-service/class-sw-service.php';
	require_once SW_ABSPATH . 'includes/sw-service/class-sw-service-database.php';
	require_once SW_ABSPATH . 'includes/sw-service/sw-service-functions.php';
	require_once SW_ABSPATH . 'includes/sw-product/class-sw-product.php';
	require_once SW_ABSPATH . 'includes/sw-product/sw-product-functions.php';
	require_once SW_ABSPATH . 'includes/sw-product/sw-order-config.php';
	require_once SW_ABSPATH . 'templates/email-templates.php';

	// Only load compatibility file when TeraWallet plugin is installed.
	if ( function_exists( 'woo_wallet' ) ) {
		require_once SW_ABSPATH . 'admin/include/tera-wallet-int.php';
	}
	
	// Only load admin menu and subsequent files in admin page.
	if ( is_admin() ) {

		require_once SW_ABSPATH . 'admin/admin-menu.php';
		require_once SW_ABSPATH . 'includes/sw-service/contr.php';
		require_once SW_ABSPATH . 'includes/sw-invoice/contr.php';
		require_once SW_ABSPATH . 'includes/sw-product/contr.php';

	}
	// Load fontend file
	if ( is_smart_woo_frontend() ) {

		require_once SW_ABSPATH . 'frontend/woocommerce/contr.php';
		require_once SW_ABSPATH . 'frontend/woocommerce/my-account.php';
		require_once SW_ABSPATH . 'frontend/woocommerce/woo-forms.php';
		require_once SW_ABSPATH . 'frontend/invoice/contr.php';
		require_once SW_ABSPATH . 'frontend/invoice/template.php';
		require_once SW_ABSPATH . 'frontend/shortcode.php';
		require_once SW_ABSPATH . 'frontend/service/template.php';
		require_once SW_ABSPATH . 'frontend/service/contr.php';

	}

	// Do action after loading plugin files
	do_action( 'smart_woo_loaded' );
}
