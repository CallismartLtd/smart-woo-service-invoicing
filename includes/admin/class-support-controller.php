<?php
/**
 * The Smart Woo support controller class file
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevents direct access.

/**
 * The support page, template and form controller
 */
class SmartWoo_Support_Controller {
	/**
	 * Callismart Tech Store URL
	 * 
	 * @var string $store_url
	 */
	private static $store_url = 'https://callismart.com.ng';

	/**
	 * Callismart Tech Support URL
	 * 
	 * @var string $support_url
	 */
	private static $support_url	= 'https://support.callismart.com.ng';

	/**
	 * Page controller
	 */
	public static function menu_controller() {
		$tab = smartwoo_get_query_param( 'tab' );
		self::print_header();
		switch ( $tab ) {
			case 'inbox':
				self::inbox();
				break;

			case 'vip-support':
				self::vip_support();
				break;

			case 'tools':
				self::tools();
				break;

			default:
			self::overview();
			
		}
	}

	/**
	 * Print navigation header
	 */
	private static function print_header() {
		$tabs = array(
			'Overview'	=> array(
				'href'		=> admin_url( 'admin.php?page=sw-support' ),
				'active'	=> ''
			),
			'Inbox'  => array(
				'href'		=> admin_url( 'admin.php?page=sw-support&tab=inbox' ),
				'active'	=> 'inbox'
			),
			'VIP Support' => array(
				'href'	=> admin_url( 'admin.php?page=sw-support&tab=vip-support' ),
				'active'	=> 'vip-support'
			),
			'Tools'  => array(
				'href'	=> admin_url( 'admin.php?page=sw-support&tab=tools' ),
				'active'	=> 'tools'
			),

		);

		$title		= ! empty( smartwoo_get_query_param( 'tab' ) ) ? 'Support' : 'Support Overview';
		SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $tabs, 'tab' );
	
	}

	/**
	 * Support overview page.
	 */
	private static function overview() {

		$support_packages = self::get_support_products();

		include_once SMARTWOO_PATH . 'templates/admin/support/overview.php';
	}

	/**
	 * The inbox page
	 */
	private static function inbox() {
		smartwoo_set_document_title( 'Inbox | Smart Woo' );
		

		include_once SMARTWOO_PATH . 'templates/admin/support/inbox.php';
		
	}

	/**
	 * VIP support page
	 */
	private static function vip_support() {
		smartwoo_set_document_title( 'VIP Support | Smart Woo' );
		

		include_once SMARTWOO_PATH . 'templates/admin/support/vip-support.php';
	}

	/**
	 * Tools page
	 */
	private static function tools() {
		

		include_once SMARTWOO_PATH . 'templates/admin/support/tools.php';
	}

	/**
	 * Get Smart Woo support products.
	 *
	 * @return array|WP_Error $products
	 */
	public static function get_support_products() {
		// delete_transient( 'smartwoo_support_products' ); // For debugging
		$products = get_transient( 'smartwoo_support_products' );

		if ( false === $products ) {
			$url          = trailingslashit( self::$store_url ) . 'wp-json/wc/store/v1/products?category=smart-woo-assist';
			$request_args = array(
				'timeout'   => 60,
				'sslverify' => true,
			);

			$response = wp_remote_get( $url, $request_args );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				return new WP_Error( 'empty_response', __( 'Empty response from store.', 'smart-woo-service-invoicing' ) );
			}

			$data = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'json_decode_error', __( 'Invalid JSON data from store.', 'smart-woo-service-invoicing' ) );
			}

			// Extract and normalize product data
			$products = array();

			foreach ( $data as $item ) {

				$price_raw  = $item['prices']['price'] ?? 0;
				$currency   = $item['prices']['currency_code'] ?? 'USD';
				$minor_unit = $item['prices']['currency_minor_unit'] ?? 2;

				// Convert to major units (e.g., cents → dollars)
				$price_major = $price_raw / pow( 10, $minor_unit );

				$products[] = array(
					'id'			=> absint( $item['id'] ?? 0 ),
					'name'			=> sanitize_text_field( $item['name'] ?? '' ),
					'slug'			=> sanitize_title( $item['slug'] ?? '' ),
					'description'	=> wp_kses_post( $item['description'] ?? '' ),
					'short_description'	=> wp_kses_post( $item['short_description'] ?? '' ),
					'price'				=> $price_major,
					'price_html'		=> smartwoo_price( $price_major, array( 'currency' => $currency ) ),
					'currency'			=> $currency,
					// 'checkout_url'		=> esc_url( trailingslashit( self::$store_url ) . 'app-support-checkout/' . absint( $item['id'] ) . '/' ),
					'checkout_url'		=> esc_url( trailingslashit( site_url( 'app-support-checkout/23' ) ) ),
					'permalink'			=> esc_url( $item['permalink'] ?? '' ),
				);
			}

			set_transient( 'smartwoo_support_products', $products, DAY_IN_SECONDS );
		}

		return $products;
	}

}