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
	 * The admin dashboard menu controller
	 */
	public static function menu_controller() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
		}
	
		$action = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
		switch ( $action ) {
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
			default:
				self::dashboard();
				break;
		}
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
			$status = smartwoo_service_status( $service );
			$status_class = strtolower( str_replace( ' ', '-', $status ) );
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
		$url_service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
		if ( empty( $url_service_id ) ) {
			return smartwoo_error_notice( 'Service Parameter cannot be manipulated' );
		}
		$service	= SmartWoo_Service_Database::get_service_by_id( $url_service_id );
	
		if ( empty( $service ) ) {
			return smartwoo_error_notice( 'Service not found.' );
		}
		smartwoo_set_document_title( 'Edit ' . $service->getServiceName() );
		$tabs = array(
			''				=> 'Dashboard',
			'view-service'	=> 'View',
			'edit-service'	=> 'Edit'
		);
	
		$args       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query_var  =  'service_id=' . $service->getServiceId() .'&tab';
	
		$service_name		= $service->getServiceName();
		$service_url		= $service->getServiceUrl();
		$service_type		= $service->getServiceType();
		$product_id			= $service->getProductId();
		$user_id			= $service->getUserId();
		$invoice_id			= $service->getInvoiceId();
		$start_date			= $service->getStartDate();
		$end_date			= $service->getEndDate();
		$next_payment_date 	= $service->getNextPaymentDate();
		$billing_cycle		= $service->getBillingCycle();
		$status				= $service->getStatus();
		$is_downloadable	= $service->has_asset();
		$product_name		= $service->get_product_name();
		if ( $is_downloadable ) {
			$assets	= $service->get_assets();
			$downloadables		= array();
			$additionals		= array();
			$downloads_a_obj	= null;
	
			foreach ( $assets as $asset ) {
				if ( 'downloads' === $asset->get_asset_name() ) {
					foreach ( $asset->get_asset_data() as $file => $url ) {
						$downloadables[$file]	= $url;
					}
	
					$downloads_a_obj = $asset;
					$id = $asset->get_id();
					continue;
				}
				
				$additionals[] = $asset;
			}
		}
		
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
			 * Additional Client details as an associative array 
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
}

/**
 * Handles add-new service page.
 * This function is responsible for handling the manual creation of a
 * service subscription in the admin area
 */
function smartwoo_process_new_service_form() {

	if ( isset( $_POST['add_new_service_submit'], $_POST['sw_add_new_service_nonce']  ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_add_new_service_nonce'] ) ), 'sw_add_new_service_nonce' ) ) {

		$user_id				= isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id				= isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$service_name      		= isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
		$service_type      		= isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : '';
		$service_url       		= isset( $_POST['service_url'] ) ? sanitize_url( wp_unslash( $_POST['service_url'] ), array( 'http', 'https' ) ) : '';
		$invoice_id        		= isset( $_POST['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_id'] ) ) : '';
		$start_date        		= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$billing_cycle     		= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
		$next_payment_date 		= isset( $_POST['next_payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date'] ) ) : '';
		$end_date          		= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$status            		= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$process_downloadable   = ! empty( $_POST['sw_downloadable_file_urls'][0] ) && ! empty( $_POST['sw_downloadable_file_names'][0] );
		$process_more_assets    = ! empty( $_POST['add_asset_types'][0] ) && ! empty( $_POST['add_asset_names'][0] ) && ! empty( $_POST['add_asset_values'][0] );

		$our_billing_cycles = array(
			'Monthly',
			'Quarterly',
			'Six Monthly',
			'Yearly',
		);
		// Validation
		$validation_errors = array();

		if ( ! preg_match( '/^[A-Za-z0-9\s]+$/', $service_name ) ) {
			$validation_errors[] = 'Service name should only contain letters, and numbers.';
		}

		if ( ! in_array( $billing_cycle, $our_billing_cycles, true ) ) {
			$validation_errors[] = 'Billing Cycle not supported.';
		}

		if ( ! empty( $service_type ) && ! preg_match( '/^[A-Za-z0-9\s]+$/', $service_type ) ) {
			$validation_errors[] = 'Service type should only contain letters, numbers, and spaces.';
		}

		if ( ! empty( $service_url ) && filter_var( $service_url, FILTER_VALIDATE_URL ) === false ) {
			$validation_errors[] = 'Invalid service URL format.';
		}

		if ( empty( $product_id ) ) {
			$validation_errors[] = 'A product is required to set up a service.';
		}

		if ( empty( $start_date ) || empty( $end_date ) || empty( $next_payment_date ) || empty( $billing_cycle ) ) {
			$validation_errors[] = 'All Dates must correspond to the billing circle';
		}

		if ( ! empty( $validation_errors ) ) {
			smartwoo_set_form_error( $validation_errors );
			wp_redirect( admin_url( 'admin.php/?page=sw-admin&action=add-new-service' ) );
			exit;
		}

		// Create new service.
		$args = array(
			'user_id'		=> $user_id,
			'product_id' 	=> $product_id,
			'service_name'	=> $service_name,
			'service_url'	=> $service_url,
			'service_type'	=> $service_type,
			'start_date'	=> $start_date,
			'end_date'		=> $end_date,
			'next_payment_date'	=> $next_payment_date,
			'billing_cycle'	=> $billing_cycle,
			'status'		=> $status
		);
		$newservice = smartwoo_create_service( $args );

		if ( ! $newservice ) {
			smartwoo_set_form_error( 'Unable to save to the database, use the help tab above if the issue persists.' );
			wp_redirect( admin_url( 'admin.php/?page=sw-admin&action=add-new-service' ) );
			exit;
		}

		$saved_service_id = $newservice->getServiceId();
		// Process downloadable assets first.
		if ( $process_downloadable ) {
			$file_names     = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) );
			$file_urls      = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_urls'] ) );
			$is_external    = isset( $_POST['is_external'] ) ? sanitize_text_field( wp_unslash( $_POST['is_external'] ) ) : 'no';
			$asset_key      = isset( $_POST['asset_key'] ) ? sanitize_text_field( wp_unslash( $_POST['asset_key'] ) ) : '';
			$access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['access_limits'] ) ) : array();

			$downloadables  = array();
			if ( count( $file_names ) === count( $file_urls ) ) {
				$downloadables  = array_combine( $file_names, $file_urls );
			}
			
			foreach ( $downloadables as $k => $v ) {
				if ( empty( $k ) || empty( $v ) ) {
					unset( $downloadables[$k] );
				}
			}

			if ( ! empty( $downloadables ) ) {
				$raw_assets = array(
					'asset_name'    => 'downloads',
					'service_id'    => $saved_service_id,
					'asset_data'    => $downloadables,
					'access_limit'  => isset( $access_limit[0] ) && '' !== $access_limit[0] ? intval( $access_limit[$index] ) : -1,
					'is_external'   => $is_external,
					'asset_key'     => $asset_key,
					'expiry'        => $end_date,
				);

				$obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
				$obj->save();

			} 
		}
			
		if ( $process_more_assets ) {
			/**
			 * Additional assets are grouped by their asset types, this is to say that
			 * an asset type will be stored with each asset data.
			 * 
			 * Asset data will be an extraction of a combination of each asset name and value
			 * in the form.
			 */
			$asset_tpes 	= array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_types'] ) );
			$the_keys		= array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_names'] ) );
			$the_values		= array_map( 'wp_kses_post', wp_unslash( $_POST['add_asset_values'] ) );
			$access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['access_limits'] ) ) : array();

			$asset_data = array();

			// Attempt tp pair asset names and values.
			if ( count( $the_keys ) === count( $the_values ) ) {
				$asset_data = array_combine( $the_keys, $the_values );
			}

			// If this pairing was successful.
			if ( ! empty( $asset_data ) ) {
				// The assets types are numerically indexed.
				$index      = 0;
				array_shift( $access_limit ); // Remove limit for downloadables which is already proceesed.
				/**
				 * We loop through each part of the combined asset data to
				 * save it with an asset type in the database.
				 */
				foreach ( $asset_data as $k => $v ) {
					// Empty asset name or value will not be saved.
					if ( empty( $k ) || empty( $v ) || empty( $asset_tpes[$index] ) ) {
						unset( $asset_data[$k] );
						unset( $asset_tpes[$index] );
						unset( $access_limit[$index] );

						$index++;
						continue;
						
					}

					// Proper asset data structure where asset name is used to identify the asset type.
					$raw_assets = array(
						'asset_data'    => array_map( 'wp_kses_post', wp_unslash( array( $k => $v ) ) ),
						'asset_name'    => $asset_tpes[$index],
						'expiry'        => $end_date,
						'service_id'    => $saved_service_id,
						'access_limit'  => isset( $access_limit[$index] ) && '' !== $access_limit[$index] ? intval( $access_limit[$index] ) : -1,
					);

					// Instantiation of SmartWoo_Service_Asset using the convert_array method.
					$obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
					$obj->save();
					$index++;
				}
			}
		}
		
		$details_url      = admin_url( 'admin.php?page=sw-admin&action=view-service&service_id=' . $saved_service_id );
		$message       = '<div class="notice notice-success is-dismissible"><p><strong>Service successfully added.</strong> <a href="' . esc_url( $details_url ) . '">View Details</a></p></div>';
		smartwoo_set_form_success( $message );
		wp_redirect( admin_url( 'admin.php/?page=sw-admin&action=add-new-service' ) );
		exit;
		
	}
}

/**
 * Handle edit service page
 */
function smartwoo_process_edit_service_form() {

	if ( isset( $_POST['edit_service_submit'], $_POST['sw_edit_service_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_service_nonce'] ) ), 'sw_edit_service_nonce' ) ) {
		
		// Initialize an array to store validation errors.
		$errors 	= array();
		$user_id    = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		// Validate Service Name.
		$service_name = isset( $_POST['service_name'] ) ? sanitize_text_field( wp_unslash( $_POST['service_name'] ) ) : '';
		
		if ( ! preg_match( '/^[a-zA-Z0-9\s]+$/', $service_name ) ) {
			$errors['service_name'] = 'Service Name should only contain letters, numbers, and spaces.';
		}

		// Validate Service Type
		$service_type = isset( $_POST['service_type'] ) ? sanitize_text_field( wp_unslash( $_POST['service_type'] ) ) : '';
		if ( ! empty( $service_type ) && ! preg_match( '/^[a-zA-Z0-9\s]+$/', $service_type ) ) {
			$errors['service_type'] = 'Service Type should only contain letters, numbers, and spaces.';
		}
		// Validate Service URL
		$service_url = isset( $_POST['service_url'] ) ? sanitize_url( wp_unslash( $_POST['service_url'] ) ) : '';
		if ( ! empty( $service_url ) && ( ! filter_var( $service_url, FILTER_VALIDATE_URL ) || strpos( $service_url, ' ' ) !== false ) ) {
			$errors['service_url'] = 'Service URL should be a valid URL.';
		}

		$invoice_id				= isset( $_POST['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_id'] ) ) : '';
		$start_date				= isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$billing_cycle			= isset( $_POST['billing_cycle'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_cycle'] ) ) : '';
		$next_payment_date		= isset( $_POST['next_payment_date'] ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date'] ) ) : '';
		$end_date				= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
		$status					= isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
		$service_id				= isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ): wp_die( 'Service ID missing' );
		$service				= SmartWoo_Service_Database::get_service_by_id( $service_id );
		$process_downloadable   = ! empty( $_POST['sw_downloadable_file_urls'][0] ) && ! empty( $_POST['sw_downloadable_file_names'][0] );
		$process_more_assets    = ! empty( $_POST['add_asset_types'][0] ) && ! empty( $_POST['add_asset_names'][0] ) && ! empty( $_POST['add_asset_values'][0] );

		
		if ( ! $service ) {
			$errors[] = 'The service does not exist, may it\'s deleted.';
		}
		// Check for validation errors before updating
		if ( ! empty( $errors ) ) {
			smartwoo_set_form_error( $errors );
			wp_redirect( smartwoo_service_edit_url( $service_id ) );
			exit;
		}
		$service->setUserId( $user_id );
		$service->setProductId( $product_id );
		$service->setServiceName( $service_name );
		$service->setServiceType( $service_type );
		$service->setServiceUrl( $service_url );
		$service->setInvoiceId( $invoice_id );
		$service->setStartDate( $start_date );
		$service->setBillingCycle( $billing_cycle );
		$service->setNextPaymentDate( $next_payment_date );
		$service->setEndDate( $end_date );
		$service->setStatus( $status );

		// Perform the update.
		$updated = SmartWoo_Service_Database::update_service( $service );

		if ( $updated ) {

			// Process downloadable assets first.
			if ( $process_downloadable ) {
				$file_names     = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_names'] ) );
				$file_urls      = array_map( 'sanitize_text_field', wp_unslash( $_POST['sw_downloadable_file_urls'] ) );
				$is_external    = isset( $_POST['is_external'] ) ? sanitize_text_field( wp_unslash( $_POST['is_external'] ) ) : 'no';
				$asset_key      = isset( $_POST['asset_key'] ) ? sanitize_text_field( wp_unslash( $_POST['asset_key'] ) ) : '';
				$asset_ids		= isset( $_POST['asset_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['asset_ids'] ) ) : 0;
				$access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'intval', wp_unslash( $_POST['access_limits'] ) ) : array();
				$downloadables  = array();
				if ( count( $file_names ) === count( $file_urls ) ) {
					$downloadables  = array_combine( $file_names, $file_urls );
				}
				
				foreach ( $downloadables as $k => $v ) {
					if ( empty( $k ) || empty( $v ) ) {
						unset( $downloadables[$k] );
					}
				}

				if ( ! empty( $downloadables ) ) {
					$raw_assets = array(
						'asset_name'    => 'downloads',
						'service_id'    => $service_id,
						'asset_data'    => $downloadables,
						'access_limit'  => isset( $access_limit[0] ) && '' !== $access_limit[0] ? intval( $access_limit[0] ) : -1,
						'is_external'   => $is_external,
						'asset_key'     => $asset_key,
						'expiry'        => $end_date,
					);

					if ( ! empty( $asset_ids ) ) {
						$raw_assets['asset_id'] = $asset_ids[0]; // Downloadable asset ID will always be number 1.
					}

					$obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
					$obj->save();

				} 
			}
				
			if ( $process_more_assets ) {
				/**
				 * Additional assets are grouped by their asset types, this is to say that
				 * an asset type will be stored with each asset data.
				 * 
				 * Asset data will be an extraction of a combination of each asset name and value
				 * in the form.
				 */
				$asset_tpes		= array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_types'] ) );
				$the_keys   	= array_map( 'sanitize_text_field', wp_unslash( $_POST['add_asset_names'] ) );
				$the_values 	= array_map( 'wp_kses_post', wp_unslash( $_POST['add_asset_values'] ) );
				$asset_ids		= ! empty( $_POST['asset_ids'] ) ? array_map( 'intval', wp_unslash( $_POST['asset_ids'] ) ) : 0;
				$access_limit	= isset( $_POST['access_limits'] ) ? array_map( 'intval', wp_unslash( $_POST['access_limits'] ) ) : array();

				$asset_data = array();

				// Attempt to pair asset names and values.
				if ( count( $the_keys ) === count( $the_values ) ) {
					$asset_data = array_combine( $the_keys, $the_values );
				}

				// If this pairing was successful.
				if ( ! empty( $asset_data ) ) {
					// The assets types and IDS are numerically indexed.
					$index      = 0;
					if ( $process_downloadable  && ! empty( $asset_ids ) ) {
						array_shift( $asset_ids ); // remove index of downloadable assets.
						array_shift( $access_limit ); // Remove index for downloadable assets
					}
					

					/**
					 * We loop through each part of the combined asset data to
					 * save it with an asset type in the database.
					 */
					foreach ( $asset_data as $k => $v ) {
						// Empty asset name or value will not be saved.
						if ( empty( $k ) || empty( $v ) || empty( $asset_tpes[$index] ) ) {
							if ( $process_downloadable  && ! empty( $asset_ids ) ) {
								unset( $asset_ids[$index] );
								unset( $access_limit[$index] );
							}
							unset( $asset_data[$k] );
							unset( $asset_tpes[$index] );
							$index++;
							continue;
							
						}

						// Proper asset data structure where asset name is used to identify the asset type.
						$raw_assets = array(
							'asset_data'    => array_map( 'wp_kses_post', wp_unslash( array( $k => $v ) ) ),
							'asset_name'    => $asset_tpes[$index],
							'expiry'        => $end_date,
							'service_id'    => $service_id,
							'access_limit'  => isset( $access_limit[$index] ) && '' !== $access_limit[$index] ? intval( $access_limit[$index] ) : -1,
						);
						if ( ! empty( $asset_ids ) ) {
							$raw_assets['asset_id'] = $asset_ids[$index];
						}
						// Instantiation of SmartWoo_Service_Asset using the convert_array method.
						$obj = SmartWoo_Service_Assets::convert_arrays( $raw_assets );
						$obj->save();
						$index++;
					}
				}
			}
			if ( ! $process_downloadable && ! $process_more_assets && $service->has_asset() ) {
				$asset_obj = new SmartWoo_Service_Assets();
				$asset_obj->set_service_id( $service_id );
				$asset_obj->delete_all();
			}
			smartwoo_set_form_success('Service updated.' );
		} else {
			smartwoo_set_form_error( 'Failed to update the service.' );
		}

		if ( $status === 'Cancelled' || $status === 'Suspended' || $status === 'Expired' ) {
			do_action( 'smartwoo_service_deactivated', $service );
		} else {
			do_action( 'smartwoo_service_active', $service );
		}
		wp_redirect( smartwoo_service_edit_url( $service_id ) );
		exit;
	} 
}
