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
	 * Register inbox-related AJAX actions.
	 */
	public static function register_ajax_hooks() {
		add_action( 'wp_ajax_smartwoo_update_consent', array( __CLASS__, 'ajax_update_consent' ) );
		add_action( 'wp_ajax_smartwoo_support_inbox_actions', array( __CLASS__, 'support_inbox_actions' ) );
	}

	/**
	 * Update user consent.
	 */
	public static function ajax_update_consent() {
		check_ajax_referer( 'smartwoo_inbox_nonce' );

		$consent = smartwoo_get_post_param( 'consent', false );

		$inbox = new \Callismart\SupportInbox();
		$inbox->set_consent( $consent );

		wp_send_json_success( array(
			'message' => $consent
				? __( 'Consent saved. You will now receive messages.', 'smart-woo-service-invoicing' )
				: __( 'You have opted out of messages.', 'smart-woo-service-invoicing' ),
		) );
	}

	/**
	 * Handle all inbox-related AJAX actions (fetch, mark read/unread, delete, get, etc).
	 *
	 * Supports single or bulk operations.
	 *
	 * @since 0.0.6
	 */
	public static function support_inbox_actions() {
		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid CSRF token', 'smart-woo-service-invoicing' ) ), 401 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'You are not allowed to perform this action.', 'smart-woo-service-invoicing' ),
			) );
		}

		$action_type = smartwoo_get_post_param( 'action_type' );
		$message_id  = smartwoo_get_post_param( 'message_id' );
		$message_ids = isset( $_POST['message_ids'] ) ? (array) $_POST['message_ids'] : array();
		$force       = ! empty( smartwoo_get_post_param( 'force' ) );

		$valid_actions = array( 'fetch', 'get_message', 'read', 'unread', 'all_read', 'delete' );

		if ( ! in_array( $action_type, $valid_actions, true ) ) {
			wp_send_json_error( array(
				'message' => __( 'Invalid inbox action type.', 'smart-woo-service-invoicing' ),
			) );
		}

		$inbox  = new \Callismart\SupportInbox();
		$result = false;

		switch ( $action_type ) {

			/**
			 * Fetch messages (manual or forced).
			 */
			case 'fetch':
				if ( ! $inbox->has_consent() ) {
					wp_send_json_error( array(
						'message' => __( 'You have not given consent to receive messages.', 'smart-woo-service-invoicing' ),
					) );
				}

				$result = $inbox->maybe_fetch_messages( $force );

				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				}

				wp_send_json_success( array(
					'message'  => __( 'Inbox updated successfully.', 'smart-woo-service-invoicing' ),
					'messages' => $inbox->get_messages(),
				) );
				break;

			/**
			 * Get a specific message by ID.
			 */
			case 'get_message':
				if ( empty( $message_id ) ) {
					wp_send_json_error( array( 'message' => __( 'Message ID required.', 'smart-woo-service-invoicing' ) ) );
				}

				$messages = $inbox->get_messages();
				if ( ! isset( $messages[ $message_id ] ) ) {
					wp_send_json_error( array( 'message' => __( 'Message not found.', 'smart-woo-service-invoicing' ) ) );
				}

				wp_send_json_success( array(
					'message' => __( 'Message retrieved successfully.', 'smart-woo-service-invoicing' ),
					'data'    => $messages[ $message_id ],
				) );
				break;

			/**
			 * Mark single or multiple messages as read.
			 */
			case 'read':
				if ( ! empty( $message_ids ) ) {
					foreach ( $message_ids as $id ) {
						$inbox->mark_as_read( sanitize_text_field( $id ) );
					}
					$result = true;
				} elseif ( ! empty( $message_id ) ) {
					$inbox->mark_as_read( $message_id );
					$result = true;
				} else {
					wp_send_json_error( array( 'message' => __( 'No message ID(s) provided.', 'smart-woo-service-invoicing' ) ) );
				}
				break;

			/**
			 * Mark single or multiple messages as unread.
			 */
			case 'unread':
				if ( ! empty( $message_ids ) ) {
					foreach ( $message_ids as $id ) {
						$inbox->mark_as_unread( sanitize_text_field( $id ) );
					}
					$result = true;
				} elseif ( ! empty( $message_id ) ) {
					$inbox->mark_as_unread( $message_id );
					$result = true;
				} else {
					wp_send_json_error( array( 'message' => __( 'No message ID(s) provided.', 'smart-woo-service-invoicing' ) ) );
				}
				break;

			/**
			 * Mark all messages as read.
			 */
			case 'all_read':
				$result = $inbox->mark_all_as_read();
				break;

			/**
			 * Delete one or more messages.
			 */
			case 'delete':
				if ( ! empty( $message_ids ) ) {
					foreach ( $message_ids as $id ) {
						$inbox->delete_message( sanitize_text_field( $id ) );
					}
					$result = true;
				} elseif ( ! empty( $message_id ) ) {
					$inbox->delete_message( $message_id );
					$result = true;
				} else {
					wp_send_json_error( array( 'message' => __( 'No message ID(s) provided to delete.', 'smart-woo-service-invoicing' ) ) );
				}
				break;
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array(
			'message'  => __( 'Action completed successfully.', 'smart-woo-service-invoicing' ),
			'messages' => $inbox->get_messages(),
		) );
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

		$tab   = smartwoo_get_query_param( 'tab', 'Support Overview' );
		$title = ucwords( str_replace( '-', ' ', $tab ) );

		// Check if "Support" is NOT at the start or end
		if ( ! preg_match( '/^Support\b/i', $title ) && ! preg_match( '/\bSupport$/i', $title ) ) {
			$title = 'Support ' . $title;
		}
		
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
		
		// Initialize inbox handler.
		$inbox = new \Callismart\SupportInbox();

		// Get stored messages and consent status.
		$messages     = $inbox->get_messages();
		$unread_count = count( $inbox->get_unread_messages() );
		$has_consent  = $inbox->has_consent();

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

SmartWoo_Support_Controller::register_ajax_hooks();