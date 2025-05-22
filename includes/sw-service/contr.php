<?php
/**
 * File name contr.php
 * Description Controller file for Service
 * 
 * @author Callistus
 * @package SmartWoo\adminTemplates
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Admin_Controller {

	/**
	 * Service form input fields
	 * 
	 * @var array $form_fields
	 */
	private $form_fields = array(
		'sw_service_id'		=> '',
		'sw_service_name'	=> '',
		'sw_service_type'	=> '',
		'sw_service_url'	=> '',
		'product_id'		=> 0,
		'sw_user_id'		=> 0,
		'start_date'		=> '',
		'billing_cycle'		=> '',
		'next_payment_date'	=> '',
		'end_date'			=> '',
		'status'			=> null,

		'has_assets'					=> false,
		'smartwoo_downloadable_assets'	=> array(
			'download_asset_type_id'	=> 0,
			'is_external'					=> 'no',
			'sw_downloadable_file_names'	=> array(),
			'sw_downloadable_file_urls'		=> array(),
			'asset_key'						=> '',
			'download_limit'				=> -1
		),

		'additional_asset_types'	=> array(
			'asset_type_ids'	=> array(),
			'asset_type_names'	=> array(),
			'asset_type_keys'	=> array(),
			'asset_type_values'	=> array(),
			'access_limits'		=> array()
		),
	);

	/**
	 * Singleton instance
	 * 
	 * @var self $instance
	 */
	private static $instance = null;

	/**
	 * Instanciate a singleton instance of this class.
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
	 * The admin dashboard menu controller
	 */
	public static function menu_controller() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
		}
	
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
		switch ( $tab ) {
			case 'view-service':
				self::view_service_page();
				break;
	
			case 'add-new-service':
				self::add_new_service_page();
				break;
	
			case 'edit-service':
				self::edit_service_page();
				break;
			case 'client':
				self::view_client();
				break;
			case 'assets':
				self::view_assets();
				break;
			case 'stats':
				self::service_stats_page();
				break;
			case 'logs':
				self::service_logs_page();
				break;
			default:
				self::dashboard();
				break;
		}
	}

	/**
	 * Action hook runner
	 */
	public static function listen() {
		// add_action( 'admin_post_smartwoo_add_service', array( __CLASS__, 'new_service_form_submission' ) );
        // add_action( 'admin_post_smartwoo_edit_service', array( __CLASS__, 'edit_service_form_submission' ) );

		add_action( 'wp_ajax_smartwoo_add_service', array( __CLASS__, 'new_service_form_submission' ) );
        add_action( 'wp_ajax_smartwoo_edit_service', array( __CLASS__, 'edit_service_form_submission' ) );
		add_action( 'wp_ajax_smartwoo_service_from_order', array( __CLASS__, 'process_new_service_order_form' ) );
	}

	/**
	 * The admin dashboard page
	 */
	private static function dashboard() {
		include_once SMARTWOO_PATH . 'templates/service-admin-temp/dashboard.php';
	}

	/**
	 * The add new service page
	 */
	private static function add_new_service_page() {
		add_filter( 'smartwoo_dropdown_user_meta', function(){
			return '';
		});
		smartwoo_set_document_title( 'Create New Service' );
		wp_enqueue_script( 'smartwoo-jquery-timepicker' );
		
		$tabs = array(
			''					=> 'Dashboard',
			'add-new-service'	=> 'Add New'
		);

		include_once SMARTWOO_PATH . 'templates/service-admin-temp/add-service.php';
	}

	/**
	 * View service subscription details page.
	 */
	private static function view_service_page() {
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab		= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		$service    = SmartWoo_Service_Database::get_service_by_id( $service_id );

		if ( $service ) {
			smartwoo_set_document_title( $service->get_name() );
			$product		= $service->get_product();
			$product_name	= $product ? $product->get_name() : 'NA';
			$image_url      = ( $product && wp_get_attachment_url( $product->get_image_id() ) ) ? wp_get_attachment_url( $product->get_image_id() ) : wc_placeholder_img_src();
			$description	= $product ? wp_trim_words( $product->get_short_description(), 30, '...' ) : '<p>No description found</p>';
			$product_url	= $product ? $product->get_permalink() : '';
			$status 		= smartwoo_service_status( $service );
			$status_class 	= strtolower( str_replace( ' ', '-', $status ) );
			$args			= array( 'service_id' => $service->get_service_id(), 'limit' => 5 );
			$invoices		= SmartWoo_Invoice_Database::get_service_invoices( $args );
		}
		
		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'Details',
			'client'		=> 'Client Info',
			'assets'		=> 'Assets',
			'stats'			=> 'Stats & Usage',
			'logs'			=> 'Service Logs',
	
		);
		include_once SMARTWOO_PATH . 'templates/service-admin-temp/view-service.php';
	}

	/**
	 * Edit service page
	 */
	private static function edit_service_page() {
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
		$service	= SmartWoo_Service_Database::get_service_by_id( $service_id );
	
		if ( $service ) {
			wp_enqueue_script( 'smartwoo-jquery-timepicker' );
			smartwoo_set_document_title( 'Edit ' . $service->get_name() );
			add_filter( 'smartwoo_dropdown_user_meta', function(){
				return '';
			});
			$user					= get_userdata( $service->get_user_id() );
			$user_fullname			= $user ? $user->display_name : '';
			$has_asset	= $service->has_asset();
			if ( $has_asset ) {
				$assets	= $service->get_assets();
				$downloadables			= array();
				$additionals			= array();
				$download_asset_object	= null;
				$download_asset_type_id	= 0;
				foreach ( $assets as $asset ) {
					if ( 'downloads' === $asset->get_asset_name() ) {
						foreach ( $asset->get_asset_data() as $file => $url ) {
							$downloadables[$file]	= $url;
						}
		
						$download_asset_object = $asset;
						$download_asset_type_id = $asset->get_id();
						continue;
					}
					
					$additionals[] = $asset;
				}
			}
		}
		

		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'View',
			'edit-service'	=> 'Edit'
		);
	
		$tab	= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query_var  =  'service_id=' . $service_id .'&tab';
			
		include_once SMARTWOO_PATH . 'templates/service-admin-temp/edit-service.php';
	}

	/**
	 * View Client page
	 */
	private static function view_client() {
		smartwoo_set_document_title( 'Client Info' );
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab		= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		$service    = SmartWoo_Service_Database::get_service_by_id( $service_id );
		
		if ( $service ) {
			$client				= $service->get_user();
			$client_full_name	= $client->get_billing_first_name() . ' ' . $client->get_billing_last_name();
			$edit_user_url		= get_edit_user_link( $client->get_id() );
			$billing_email		= $service->get_billing_email();
			$street_address		= $client->get_billing_address_1() . ' ' . $client->get_billing_address_2();
			$is_paying_client	= $client->get_is_paying_customer();
			$total_services		= SmartWoo_Service_Database::count_user_services( $client->get_id() ) ;
			$total_invoices		= SmartWoo_Invoice_Database::count_all_by_user( $client->get_id() );

			/**
			 * Client billing details
			 * 
			 * @param array $client_billing_data An associative array of title => value of billing info.
			 */
			$client_billing_data = apply_filters(
				'smartwoo_client_billing_info_display', 
				array(
					'Company'			=> $client->get_billing_company(),
					'Email Adrress'		=> $billing_email,
					'Phone Number'		=> $client->get_billing_phone(),
					'Street Address'	=> $street_address,
					'City'				=> $client->get_billing_city(),
					'Postal / Zip'		=> $client->get_billing_postcode() ? $client->get_billing_postcode() : 'N/A',
					'State / Region'	=> smartwoo_get_state_name( $client->get_billing_country(), $client->get_billing_state() ),
					'Country'			=> smartwoo_get_country_name( $client->get_billing_country() )
				) 
			);

			/** 
			 * Additional Client details.
			 * 
			 * @param array $additional_details An associative array of title => value of additional clien info.
			*/
			$additional_details = apply_filters( 'smartwoo_additional_client_details', array(), $client->get_id() );
		}
		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'Details',
			'client'		=> 'Client Info',
			'assets'		=> 'Assets',
			'stats'			=> 'Stats & Usage',
			'logs'			=> 'Service Logs',
	
		);

		include_once SMARTWOO_PATH . '/templates/service-admin-temp/view-client.php';
	}

	/**
	 * View service assets page
	 */
	private static function view_assets() {
		smartwoo_set_document_title( 'Assets' );
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab		= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		$service    = SmartWoo_Service_Database::get_service_by_id( $service_id );

		if ( $service ) {
			smartwoo_set_document_title( $service->get_name() . ' Assets' );
			$assets 		= $service->get_assets();
			$total_assets	= count( $assets );
			$downloadables			= array();
			$additionals			= array();
			$download_asset_object	= null;
			$download_asset_type_id	= 0;
			foreach ( $assets as $asset ) {
				if ( 'downloads' === $asset->get_asset_name() ) {
					foreach ( $asset->get_asset_data() as $file => $url ) {
						$downloadables[$file]	= $url;
					}
	
					$download_asset_object = $asset;
					$download_asset_type_id = $asset->get_id();
					continue;
				}
				
				$additionals[] = $asset;
			}

		}

		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'Details',
			'client'		=> 'Client Info',
			'assets'		=> 'Assets',
			'stats'			=> 'Stats & Usage',
			'logs'			=> 'Service Logs',
	
		);
		include_once SMARTWOO_PATH . 'templates/service-admin-temp/service-assets.php';

	}

	/**
	 * Service statistics page
	 */
	private static function service_stats_page() {
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab		= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'Details',
			'client'		=> 'Client Info',
			'assets'		=> 'Assets',
			'stats'			=> 'Stats & Usage',
			'logs'			=> 'Service Logs',
	
		);
		include_once SMARTWOO_PATH . '/templates/service-admin-temp/stats.php';
	}

	/**
	 * Service logs page
	 */
	private static function service_logs_page() {
		smartwoo_set_document_title( 'Logs' );
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab		= isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'Details',
			'client'		=> 'Client Info',
			'assets'		=> 'Assets',
			'stats'			=> 'Stats & Usage',
			'logs'			=> 'Service Logs',
	
		);

		include_once SMARTWOO_PATH . '/templates/service-admin-temp/service-logs.php';
	}

	/**
	 * Handle the new service form submission in admin > add-new-service page.
	 */
	public static function new_service_form_submission() {

		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => 'Action failed basic authentication' ), 400 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have the required permission to create new service subscription.' ), 401 );
		}

		$errors	= self::instance()->check_errors();

		if ( ! empty( $errors ) ){
			wp_send_json_error( array( 'message' => 'Form Errors', 'htmlContent' => smartwoo_error_notice( $errors, true ) ) );
		}

		$service	= self::save_service();

		if ( is_wp_error( $service ) ) {
			wp_send_json_error( array( 'message' => $service->get_error_message(), 'htmlContent' => smartwoo_error_notice( $service->get_error_message(), true ) ) );
		}

		wp_send_json_success( array( 'message' => 'Service has been saved', 'redirect_url' => $service->preview_url() ));

	}

	/**
	 * Handle edit service form submission.
	 */
	public static function edit_service_form_submission() {
		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => 'Action failed basic authentication' ), 400 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have the required permission to edit a service subscription.' ), 401 );
		}
		$errors	= self::instance()->check_errors();

		if ( ! empty( $errors ) ){
			wp_send_json_error( array( 'message' => 'Form Errors', 'htmlContent' => smartwoo_error_notice( $errors, true ) ) );
		}

		$service	= self::save_service();

		if ( is_wp_error( $service ) ) {
			wp_send_json_error( array( 'message' => $service->get_error_message(), 'htmlContent' => smartwoo_error_notice( $service->get_error_message(), true ) ) );
		}

		$status = smartwoo_service_status( $service );
		$deactivated_statuses = array( 'Expired', 'Suspended', 'Cancelled' );
		if ( in_array( $status, $deactivated_statuses, true ) ) {
			do_action( 'smartwoo_service_deactivated', $service );
		} else {
			do_action( 'smartwoo_service_active', $service );
		}

		wp_send_json_success( array( 'message' => 'Service has been saved', 'redirect_url' => smartwoo_service_edit_url( $service->get_service_id() ) ) );
		 
	}

	/**
	 * Handle new service order form processing from admin
	 */
	public static function process_new_service_order_form() {
		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => 'Action failed basic authentication' ), 400 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have the required permission to process a new service order.' ), 401 );
		}
		$errors		= self::instance()->check_errors();
		$order_id	= isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$order		= SmartWoo_Order::get_order( $order_id );
		
		if ( ! $order ) {
			$errors[]	= 'This order does not exists anymore.';
		}

		if ( ! empty( $errors ) ){
			wp_send_json_error( array( 'message' => 'Form Errors', 'htmlContent' => smartwoo_error_notice( $errors, true ) ) );
		}

		$service	= self::save_service();

		if ( is_wp_error( $service ) ) {
			wp_send_json_error( array( 'message' => $service->get_error_message(), 'htmlContent' => smartwoo_error_notice( $service->get_error_message(), true ) ) );
		}
		
		$invoice_id     = $order->get_invoice_id();
		SmartWoo_Invoice_Database::update_invoice_fields( $invoice_id, array( 'service_id' => $service->get_service_id() ) );
		$order->processing_complete();
		
		/**
		 * Fires after a new service order has been processed.
		 * @param string $service_id
		 */
		do_action( 'smartwoo_new_service_is_processed', $saved_service_id );
		wp_send_json_success( array( 'message' => 'Service has been processed', 'redirect_url' => $service->preview_url() ) );
	
	}

	/**
	 * Set up form fields and check for errors
	 */
	public function check_errors() {
		$errors	= array();
		
		$user_data = isset( $_POST['sw_user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sw_user_id'] ) ) : false;
		if ( ! $user_data ) {
			$error[] = 'Please select a user.';
		}

		$user_id	= false;
		
		if ( $user_data ) {
			$parts = explode( '|', $user_data, 1 );
			$user_id	= intval( $parts[0] );
		}
		
		if ( empty( $user_id ) ) {
			$errors[] = 'The user does not have a valid ID.';
		}

		$this->form_fields['sw_user_id']	= $user_id;

		$service_name = isset( $_POST['sw_service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sw_service_name'] ) ) : '';
		if ( empty( $service_name ) ) {
			$errors[]	= 'Service name is missing.';
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ): 0;

		if ( ! wc_get_product( $product_id ) ) {
			$errors[]	= 'Invalid or deleted product.';
		}

		$this->form_fields['sw_service_name']	= $service_name;
		$this->form_fields['product_id']		= $product_id;
		$this->form_fields['sw_service_type']	= isset( $_POST['sw_service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['sw_service_type'] ) ) : '';
		$this->form_fields['sw_service_url']	= isset( $_POST['sw_service_url'] ) ? sanitize_text_field( wp_unslash( $_POST['sw_service_url'] ) ) : '';
		
		$start_date	= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		if ( empty( $start_date ) ) {
			$errors[]	= 'Please enter a start date';
		}
		$this->form_fields['start_date']	= $start_date;

		$billing_cycle	= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';

		if ( empty( $billing_cycle ) ) {
			$errors[]	= 'Please select a billing cycle';
		}
		$this->form_fields['billing_cycle']		= $billing_cycle;

		$next_payment_date	=  isset( $_POST['next_payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date'] ) ) : '';

		if ( empty( $next_payment_date ) ) {
			$errors[]	= 'Please enter the next payment date';
		}
		$this->form_fields['next_payment_date']	= $next_payment_date;
		
		$end_date	= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		
		if ( empty( $end_date ) ) {
			$errors[]	= 'Please select an end date';
		}
		$this->form_fields['end_date']	= $end_date;
		$this->form_fields['status']	= isset( $_POST['status'] ) && '' !== $_POST['status'] ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : NULL;
		
		$this->form_fields['has_assets']	= isset( $_POST['has_assets'] );
		$this->form_fields['smartwoo_downloadable_assets']['sw_downloadable_file_names']	= isset( $_POST['sw_downloadable_file_names'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) ) : array();
		$this->form_fields['smartwoo_downloadable_assets']['sw_downloadable_file_urls']		= isset( $_POST['sw_downloadable_file_urls'] ) ? array_map( 'sanitize_url', wp_unslash( $_POST['sw_downloadable_file_urls'] ) ) : array();
		$this->form_fields['smartwoo_downloadable_assets']['is_external']					= isset( $_POST['is_external'] ) && 'yes' === $_POST['is_external'] ? 'yes' : 'no';
		$this->form_fields['smartwoo_downloadable_assets']['asset_key']						= isset( $_POST['asset_key'] ) ? sanitize_text_field( wp_unslash( $_POST['asset_key'] ) ) : '';
		$this->form_fields['smartwoo_downloadable_assets']['download_asset_type_id']		= isset( $_POST['download_asset_type_id'] ) ? absint( $_POST['download_asset_type_id'] ) : 0;
		$this->form_fields['smartwoo_downloadable_assets']['download_limit']				= isset( $_POST['download_limit'] ) ? sanitize_text_field( wp_unslash( self::min_minus_1( $_POST['download_limit'] ) ) ) : -1;
	
		$this->form_fields['additional_asset_types']["asset_type_ids"]		= isset( $_POST['asset_type_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['asset_type_ids'] ) ) : array();
		$this->form_fields['additional_asset_types']["asset_type_names"]	= isset( $_POST['additional_asset_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['additional_asset_types'] ) ) : array(); // This are the real asset names, just like downloads.
		$this->form_fields['additional_asset_types']["asset_type_keys"]		= isset( $_POST['additiional_asset_names'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['additiional_asset_names'] ) ) : array(); // This fields are the asset keys.
		$this->form_fields['additional_asset_types']["asset_type_values"]	= isset( $_POST['additional_asset_values'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['additional_asset_values'] ) ) : array(); // This field are the asset values.
		$this->form_fields['additional_asset_types']["access_limits"] 		= isset( $_POST['access_limits'] ) && is_array( $_POST['access_limits'] ) ? array_map( array( __CLASS__, 'min_minus_1' ), wp_unslash( $_POST['access_limits'] ) ) : array();

		$this->form_fields['sw_service_id'] = isset( $_POST['sw_service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sw_service_id'] ) ) : '';
	
		return $errors;
	
	}

	/**
	 * Get the submitted form data.
	 * 
	 * @return array
	 */
	private function get_form_data() {
		return apply_filters(
			'smartwoo_service_form_data',
			$this->form_fields
		);
	}

	/**
	 * Save service from the submitted form.
	 * 
	 * @return SmartWoo_Service|WP_Error
	 */
	private static function save_service() {
		$form_fields 	= self::instance()->get_form_data();
		$new_service	= true;
		if ( ! empty( $form_fields['sw_service_id'] ) ) {
			$service	= SmartWoo_Service_Database::get_service_by_id( $form_fields['sw_service_id'] );

			if ( ! $service ) {
				return new WP_Error( 'invalid_service', 'This service subscription does not exist.' );
			}

			$new_service	= false;

		} else {
			$service	= new SmartWoo_Service();
			$service->set_service_id( smartwoo_generate_service_id( $form_fields['sw_service_name'] ) );

		}

		$service->set_name( $form_fields['sw_service_name'] );
		$service->set_user_id( $form_fields['sw_user_id'] );
		$service->set_billing_cycle( $form_fields['billing_cycle'] );
		$service->set_product_id( $form_fields['product_id'] );
		$service->set_service_url( $form_fields['sw_service_url'] );
		$service->set_type( $form_fields['sw_service_type'] );
		$service->set_start_date( date( 'Y-m-d', strtotime( $form_fields['start_date'] ) ) );
		$service->set_next_payment_date( date( 'Y-m-d', strtotime( $form_fields['next_payment_date'] ) ) );
		$service->set_end_date( date( 'Y-m-d', strtotime( $form_fields['end_date'] ) ) );
		
		$service->set_status( $form_fields['status'] );

		if ( $form_fields['has_assets'] ) {
			self::set_up_assets( $service );
		} elseif ( ! $form_fields['has_assets'] && $service->has_asset() ) {
			$asset = $service->get_assets()[0];
			$asset->delete_all();
		}

		return $service->save() ? $service : new WP_Error( 'service_not_saved', 'Unable to save service to the database' );

	}

	/**
	 * Set up service assets from the submitted form
	 * 
	 * @param SmartWoo_Service $service
	 */
	private static function set_up_assets( &$service ) {
		$form_fields			= self::instance()->get_form_data();
		$downloadable_assets 	= $form_fields['smartwoo_downloadable_assets'];
		$file_names				= $downloadable_assets['sw_downloadable_file_names'];
		$file_urls				= $downloadable_assets['sw_downloadable_file_urls'];
		$download_limit			= $downloadable_assets['download_limit'];
		
		$files 	= array();

		if ( count( $file_names ) === count( $file_urls ) ) {
			$files = array_combine( $file_names, $file_urls );
		}

		foreach( $files as $name => $value ) {
			if ( empty( $name ) || empty( $value ) ) {
				unset( $files[$name] );
			}
		}

		$raw_asset = array();

		if ( ! empty( $files ) ) {
			$raw_asset = array(
				'asset_name'    => 'downloads',
				'service_id'    => $service->get_service_id(),
				'asset_data'    => $files,
				'access_limit'  => $download_limit,
				'is_external'   => $downloadable_assets['is_external'],
				'asset_key'     => $downloadable_assets['asset_key'],
				'expiry'        => $service->get_end_date(),
			);
		}

		$obj	= SmartWoo_Service_Assets::convert_arrays( $raw_asset );
		if ( ! empty( $downloadable_assets['download_asset_type_id'] ) ) {
			$obj->set_id( $downloadable_assets['download_asset_type_id'] );
		}

		if ( $obj->get_id() &&  empty( $files ) ) {
			$obj->delete();
		} else {
			$obj->save();

		}
	

		// Process additional assets types.
		$additional_assets	= $form_fields['additional_asset_types'];
		$asset_type_names	= $additional_assets['asset_type_names'];
		$asset_type_keys	= $additional_assets['asset_type_keys'];
		$asset_type_values	= $additional_assets['asset_type_values'];
		$limits				= $additional_assets['access_limits'];
		$asset_ids			= $additional_assets['asset_type_ids'];

		/**
		 * Additional asset types are handled differently.
		 * An `asset_type_name` can almost be equivalent to `downloads`, just that `downloads` is a different asset type
		 * and `asset_type_name` is a custom asset name added by the user.
		 */

		if ( count( $asset_type_keys ) === count( $asset_type_values ) ) {
			$custom_assets = array_combine( $asset_type_keys, $asset_type_values );
		}
		$index	= 0;

		foreach( $custom_assets as $key => $value ) {
			if ( empty( $key ) || empty( $value || empty( $additional_assets[$index] ) ) ) {
				unset( $custom_assets[$key], $additional_assets[$index], $limits[$index] );
				$index++;
				continue;
			}

			$raw_asset = array(
				'asset_data'    => array( trim( $key ) => trim( $value ) ),
				'asset_name'    => $asset_type_names[$index],
				'expiry'        => $service->get_end_date(),
				'service_id'    => $service->get_service_id(),
				'access_limit'  => $limits[$index]
			);

			// Instantiation of SmartWoo_Service_Asset using the convert_array method.
			$obj = SmartWoo_Service_Assets::convert_arrays( $raw_asset );

			if ( ! empty( $asset_ids[$index] ) ) {
				$obj->set_id( $asset_ids[$index] );
			}

			$obj->save();
			$index++;

		}

	}

	/**
	 * Makes the minimum value to be -1 and not 0.
	 * 
	 * @param string|int $value
	 */
	public static function min_minus_1( $value ) {
		return ( empty( $value ) || $value < 0 ) ? -1 : intval( $value ); 
	}
}

SmartWoo_Admin_Controller::listen();