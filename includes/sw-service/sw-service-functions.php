<?php
/**
 * File name sw-service-functions.php
 * Utility function to interact with service args.
 *
 * @author Callistus
 * @package SmartWoo\functions
 */

defined( 'ABSPATH' ) ||exit; // Prevent direct access.

/**
 * Generate a new SmartWoo_Service object and save it to the database.
 *
 * @param array $args
 * @return SmartWoo_Service|false The generated SmartWoo_Service object or false on failure.
 */
function smartwoo_create_service( $args ) {
	if ( ! isset( $args['service_name'] ) 
		|| ! isset( $args['product_id'] ) 
		|| ! isset( $args['start_date'] ) 
		|| ! isset( $args['user_id'] ) 
		|| ! isset( $args['next_payment_date'] ) 
		|| ! isset( $args['end_date'] ) 
		|| ! isset( $args['billing_cycle'] )
	) {
		return false;
	}

	$service_id = smartwoo_generate_service_id( sanitize_text_field( wp_unslash( $args['service_name'] ) ) );

	// Create a new SmartWoo_Service object
	$service = new SmartWoo_Service();
	$service->set_user_id( isset( $args['user_id'] ) ? $args['user_id'] : 0 );
	$service->set_product_id( isset( $args['product_id'] ) ? $args['product_id'] : 0 );
	$service->set_service_id( $service_id );
	$service->set_name( isset( $args['service_name'] ) ? $args['service_name'] : '' );
	$service->set_service_url( isset( $args['service_url'] ) ? $args['service_url'] : '' );
	$service->set_type( isset( $args['service_type'] ) ? $args['service_type'] : '' );
	$service->set_start_date( isset( $args['start_date'] ) ? $args['start_date'] : '' );
	$service->set_end_date( isset( $args['end_date'] ) ? $args['end_date'] : '' );
	$service->set_next_payment_date( isset( $args['next_payment_date'] ) ? $args['next_payment_date'] : '' );
	$service->set_billing_cycle( isset( $args['billing_cycle'] ) ? $args['billing_cycle'] : '' );
	$service->set_status( isset( $args['status'] ) ? $args['status'] : null );
	
	return $service->save() ? $service : false;
}

/**
 * The button to access the client URL, This parameters are not secure handling them
 * be subject to authentication by our service manager plugin.
 *
 * @param object $service       The service.
 * @return string HTML markup button with url keypass
 */
function smartwoo_client_service_url_button( SmartWoo_Service $service ) {
	if ( empty( $service->get_service_url() ) ) {
		return '';
	}

	$button_text 	= is_admin() ? 'Service URL' : 'Visit Website';
	$button_text 	= apply_filters( 'smartwoo_service_url_button_text', $button_text, $service );
	if ( smartwoo_is_frontend() ) {
		$button	=  '<a href="' . esc_url(  $service->get_service_url() ) . '" class="sw-red-button" target="_blank"><span class="dashicons dashicons-admin-site-alt3"></span> ' . esc_html( $button_text ) .'</a>';
	} else {
		$button	=  '<a href="' . esc_url(  $service->get_service_url() ) . '" target="_blank"><button title="'. esc_attr( $button_text ) .'"><span class="dashicons dashicons-admin-site-alt3"></span></button></a>';
	}
	/**
	 * @filter	smartwoo_service_url_button_html allows for total replacement of the client service
	 * 		url button by plugins;
	 */
	return apply_filters( 'smartwoo_service_url_button_html', $button, $button_text, $service );

}

/** 
 * Get the formatted url for service subscription page 
 */
function smartwoo_service_page_url() {
	if ( is_account_page() ) {
		$endpoint_url = wc_get_endpoint_url( 'smartwoo-service' );
		return esc_url_raw( $endpoint_url );
	}

	$page	= get_option( 'smartwoo_service_page_id', 0 );
	return get_permalink( $page );
}

/**
 *  Service subscription details preview URL
 * 
 * @param string $service_id The ID of service to preview.
 * @return string $preview_url
 * @since 2.0.0 Added support for admin page preview service url.
 */
function smartwoo_service_preview_url( $service_id ) {
    if ( is_account_page() ) {
        $preview_url = wc_get_endpoint_url( 'smartwoo-service', $service_id , wc_get_page_permalink( 'myaccount' ) );        
    } elseif( ( is_admin() && ! smartwoo_is_frontend() ) || ! smartwoo_is_frontend() ) {
		$preview_url = add_query_arg( 
			array(
				'tab'			=> 'view-service',
				'service_id'	=> $service_id
				
			),
			admin_url( 'admin.php?page=sw-admin')
		);
	} else {
		$preview_url = smartwoo_get_endpoint_url( 'view-subscription', $service_id, smartwoo_service_page_url() );
    }

	return $preview_url;
}

/**
 * Get edit url for service subscription
 * 
 * @param string $service_id The ID of service to edit.
 * @return string $edit_url Admin page url for edit service
 */
function smartwoo_service_edit_url( $service_id ) {
	$edit_url = add_query_arg( 
		array(
			'tab' 		=> 'edit-service',
			'service_id'	=> $service_id,
		),
		admin_url( 'admin.php?page=sw-admin')
	);
	return $edit_url;
}

/**
 * Get service information based on various parameters.
 *
 * @param int|null    $user_id       The user ID (optional).
 * @param string|null $service_id    The service ID (optional).
 * @param int|null    $invoice_id    The invoice ID (optional).
 * @param string|null $service_name  The service name (optional).
 * @param string|null $billing_cycle The billing cycle (optional).
 *
 * @return array|object|false An array of services if no specific parameters are provided,
 *                            or service information as an object, or false if not found.
 */
function smartwoo_get_service( $user_id = null, $service_id = null, $invoice_id = null, $service_name = null, $billing_cycle = null, $service_type = null ) {
	global $wpdb;
	$table_name = SMARTWOO_SERVICE_TABLE;

	// Prepare the base query.
	$query = "SELECT * FROM $table_name WHERE 1";

	// Add conditions based on provided parameters.
	if ( $user_id !== null ) {
		$query .= $wpdb->prepare( ' AND user_id = %d', $user_id );
	}
	if ( $service_id !== null ) {
		$query .= $wpdb->prepare( ' AND service_id = %s', $service_id );
	}
	if ( $invoice_id !== null ) {
		$query .= $wpdb->prepare( ' AND invoice_id = %d', $invoice_id );
	}
	if ( $service_name !== null ) {
		$query .= $wpdb->prepare( ' AND service_name = %s', $service_name );
	}
	if ( $billing_cycle !== null ) {
		$query .= $wpdb->prepare( ' AND billing_cycle = %s', $billing_cycle );
	}

	// phpcs:disable
	if ( $service_id !== null ) {
		return $wpdb->get_row( $query );
	} else {
		return $wpdb->get_results( $query );
	}
	// phpcs:enable
}

/**
 * Check if a service is on grace period.
 *
 * @param SmartWoo_Service $service The service object.
 *
 * @return bool true if the subscription is on grace period, false otherwise.
 */
function smartwoo_is_service_on_grace( SmartWoo_Service $service ) {
	if ( 'Grace Period' === $service->get_status() ) {
		return true;
	}
	$end_date     = smartwoo_extract_only_date( $service->get_end_date() );
	$current_date = smartwoo_extract_only_date( current_time( 'mysql' ) );

	if ( $current_date >= $end_date ) {
		$grace_period_date 	= smartwoo_get_grace_period_end_date( $service->get_product(), $end_date );

		return ( ! empty( $grace_period_date ) && $current_date <= smartwoo_extract_only_date( $grace_period_date ) );
	}

	return false;
}

/**
 * Get the current, definitive status of a service subscription.
 *
 * @param SmartWoo_Service|string $service_id The SmartWoo_Service object instance, or the service ID.
 * If an ID is provided, the function will attempt to
 * retrieve the service object from the database using SmartWoo_Service_Database.
 * @return string The effective status of the service (e.g., 'Active', 'Due for Renewal', 'Grace Period', 'Expired', 'Unknown').
 */
function smartwoo_service_status( $service_id ): string {
    $service = ( $service_id instanceof SmartWoo_Service ) ? $service_id : SmartWoo_Service_Database::get_service_by_id( $service_id );
    if ( ! $service ) {
        return 'unknown';
    }

    return $service->get_effective_status();
}


/**
 * Count the number of 'Active' services.
 */
function smartwoo_count_active_services() {
	return SmartWoo_Service_Database::count_active();
}

/**
 * Count the number of 'Due for Renewal' services.
 */
function smartwoo_count_due_for_renewal_services() {
	return SmartWoo_Service_Database::count_due();
}

/**
 * Count the number of 'Active No Renewal' services.
 */
function smartwoo_count_nr_services() {
	return SmartWoo_Service_Database::count_by_status( 'Active (NR)' );
}

/**
 * Count the number of 'Expired' services.
 */
function smartwoo_count_expired_services() {
	return count( SmartWoo_Service_Database::get_all_expired( 1, null ) );
}

/**
 * Count the number of 'Grace Period' services.
 */
function smartwoo_count_grace_period_services() {
	return count( SmartWoo_Service_Database::get_all_on_grace( 1, null ) );
}

/**
 * Count the number of 'Cancelled' services.
 */
function smartwoo_count_cancelled_services() {
	return count( SmartWoo_Service_Database::get_( array( 'status' => 'Cancelled', 'limit' => 0 ) ) );
}

/**
 * Count the number of 'Suspended' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Suspended' services.
 */
function smartwoo_count_suspended_services() {
	return count( SmartWoo_Service_Database::get_( array( 'status' => 'Suspended', 'limit' => 0 ) ) );
}

/**
 * Generate a unique service ID based on the provided service name.
 *
 * @param string $service_name The name of the service.
 *
 * @return string The generated service ID.
 * @since 2.2.3 Deprecated the use of uniqid() function for generating service ID.
 */
function smartwoo_generate_service_id( $service_name ) {
	$service_id_prefix = get_option( 'smartwoo_service_id_prefix', 'SID' );

	$first_alphabets = array_map(
		function ( $word ) {
			return strtoupper( substr( $word, 0, 1 ) );
		},
		explode( ' ', (string)$service_name )
	);

	// Generate a more secure unique identifier
	$unique_id = bin2hex( random_bytes(4) ) . dechex( time() );

	$generated_service_id = $service_id_prefix . '-' . implode( '', $first_alphabets ) . $unique_id;

	return $generated_service_id;
}


/**
 * Get the expiration date for a service based on its end date and grace period.
 *
 * @param SmartWoo_Service $service The service object.
 * @return string The calculated expiration date.
 */
function smartwoo_get_service_expiration_date( SmartWoo_Service $service ) {
	$end_date			= smartwoo_extract_only_date( $service->get_end_date() );
	$grace_period_date	= smartwoo_extract_only_date( smartwoo_get_grace_period_end_date( $service->get_product(), $end_date ) );
	$expiration_date	= $grace_period_date ?? $end_date;

	return $expiration_date;
}

/**
 * Get the price of a service
 *
 * @param object $service The service.
 *
 * @return float|false The price of the service or false if not found.
 */
function smartwoo_get_service_price( SmartWoo_Service $service ) {

	if ( $service !== false ) {
		$product_id = $service->get_product_id();
		$product    = wc_get_product( $product_id );

		if ( $product ) {

			$product_price = $product->get_price();

			if ( $product_price !== false ) {
				return floatval( $product_price );
			}
		}
	}

	// Return false if the service product price is not found.
	return false;
}



/**
 * Get grace period information based on a Product ID and it's grace period settings.
 *
 * @param SmartWoo_Product $product The Smart Woo Product instance.
 * @param string $reference_date Reference date for calculating the grace period end date.
 * @return int|null Numeric representation of the grace period in hours, or null if not applicable.
 */
function smartwoo_get_grace_period_end_date( SmartWoo_Product $product, $reference_date ) {
	$end_date = null;

	// Get grace period from product metadata
	$grace_period_number	= ! empty( $product ) ? $product->get_grace_period_number(): null;
	$grace_period_unit		= ! empty( $product ) ? $product->get_grace_period_unit(): null;

	// Calculate the end date of the grace period.
	if ( empty( $grace_period_number ) && empty( $grace_period_unit ) ) {
		return $end_date;
	}
	switch ( $grace_period_unit ) {
		case 'days':
			$end_date = date_i18n( 'Y-m-d', strtotime( '+' . $grace_period_number . ' days', strtotime( $reference_date ) ) );
			break;
		case 'weeks':
			$end_date = date_i18n( 'Y-m-d', strtotime( '+' . ( $grace_period_number * 7 ) . ' days', strtotime( $reference_date ) ) );
			break;
		case 'months':
			$end_date = date_i18n( 'Y-m-d', strtotime( '+' . $grace_period_number . ' months', strtotime( $reference_date ) ) );
			break;
		case 'years':
			$end_date = date_i18n( 'Y-m-d', strtotime( '+' . $grace_period_number . ' years', strtotime( $reference_date ) ) );
			break;
		default:
			$end_date = null;
	}

return $end_date;
}



/**
 * Render the delete Service Button
 */
function smartwoo_delete_service_button( string $service_id ) {
	if ( ! is_admin() ) {
		return '';
	}
	return '<button class="delete-service-button" args-service-id="' . esc_attr( $service_id ) . '" title="Delete Service"><span class="dashicons dashicons-trash"></span></button>';
}

function smartwoo_delete_service() {

	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
		wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ) );
	}
	

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'You don\'t have the required permission to delete this args.' ) );
	}

	$service_id = isset( $_POST['service_id'] ) ? sanitize_key( $_POST['service_id'] ) : '';

	if ( empty( $service_id ) ) {
		wp_send_json_error( array( 'message' => 'Service ID is missing.' ) );

	}

	$deleted = SmartWoo_Service_Database::delete_service( $service_id );

	if ( ! $deleted ) {
		wp_send_json_error( array( 'message' => 'Unable to delete this service.') );
	} else {
		wp_send_json_success( array( 'message' => 'Service subscription has been deleted' ) );
	}
}

/**
 * Get the statuses that indicate Active service.
 * 
 * @return array
 * @since 2.0.1
 */
function smartwoo_active_service_statuses() {
	return
	apply_filters( 'smartwoo_active_service_statuses', array(
		'Active', 'Active (NR)', 'Due for Renewal', 'Due', 'Grace Period' )
	);
}

/**
 * Print service status.
 * 
 * @param SmartWoo_Service $service The service subscription object.
 * @param array $classes $addtional classes.
 */
function smartwoo_print_service_status( SmartWoo_Service $service, $classes = [] ) {
	$status			= smartwoo_service_status( $service );
	$status_class	= strtolower( str_replace( array( ' ', '(', ')'), array( '-', '', '' ), $status ) );
	$classes		= implode( ' ', $classes );
	printf( '<span class="smartwoo-status %s %s">%s</span>', esc_attr( $classes ), esc_attr( $status_class ), esc_html( $status ) );
}