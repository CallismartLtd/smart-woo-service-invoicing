<?php
/**
 * File name sw-service-functions.php
 * Utility function to interact with service data.
 *
 * @author Callistus
 * @package SmartWoo\functions
 */

defined( 'ABSPATH' ) ||exit; // Prevent direct access.

/**
 * Generate a new SmartWoo_Service object and save it to the database.
 *
 * @param int         $user_id            User ID associated with the service.
 * @param int         $product_id         Product ID for the service.
 * @param string|null $service_name       Name of the service (optional).
 * @param string|null $service_url        URL associated with the service (optional).
 * @param string|null $service_type       Type or category of the service (optional).
 * @param string|null $invoice_id         Invoice ID associated with the service (optional).
 * @param string|null $start_date         Start date of the service (optional).
 * @param string|null $end_date           End date of the service (optional).
 * @param string|null $next_payment_date  Date of the next payment for the service (optional).
 * @param string|null $billing_cycle      Billing cycle for the service (optional).
 * @param string|null $status             Status of the service (optional).
 *
 * @return SmartWoo_Service|false The generated SmartWoo_Service object or false on failure.
 */
function smartwoo_generate_service(
	int $user_id,
	int $product_id,
	?string $service_name = null,
	?string $service_url = null,
	?string $service_type = null,
	?string $invoice_id = null,
	?string $start_date = null,
	?string $end_date = null,
	?string $next_payment_date = null,
	?string $billing_cycle = null,
	?string $status = null
) {
	// Generate service ID using the provided service_name or any other logic
	$service_id = smartwoo_generate_service_id( $service_name );

	// Create a new SmartWoo_Service object
	$new_service = new SmartWoo_Service(
		$user_id,
		$product_id,
		$service_id,
		$service_name,
		$service_url,
		$service_type,
		$invoice_id,
		$start_date,
		$end_date,
		$next_payment_date,
		$billing_cycle,
		$status
	);

	$saved_service_id = $new_service->save();

	// Check if the service was successfully saved
	if ( $saved_service_id !== false ) {
		$new_service = SmartWoo_Service_Database::get_service_by_id( $saved_service_id );
		// Trigger  action after service is created
		do_action( 'smartwoo_new_service_created', $new_service );

		// Retrieve the newly created service from the database using the saved ID
		return $new_service;
	}

	// Return false if the service creation or database insertion failed
	return false;
}

/**
 * The button to access the client URL, This parameters are not secure handling them
 * be subject to authentication by our service manager plugin
 *
 * @param object $service       The service.
 * @return string HTML markup button with url keypass
 */
function smartwoo_client_service_url_button( SmartWoo_Service $service ) {
	$user_id        = $service->getUserId();
	$user_info      = get_userdata( $user_id );
	$service_status = smartwoo_service_status( $service->getServiceId() );
	if ( 'Active' === $service_status || 'Active (NR)' === $service_status  || 'Due for Renewal' === $service_status || 'Grace Period' === $service_status && 'Web Service' === $service->getServiceType() ) {

		if ( $user_info ) {
			$user_email = $user_info->user_email;

			// Construct the service URL with specified parameters
			$access_client_service_url = esc_url( $service->getServiceUrl() ) . '?auth=1&email=' . urlencode( $user_email ) . '&userisfromcallismartparentwebsite=1&serviceid=' . $service->getServiceId() . '&requestingaccess=1';

			return '<a href="' . esc_url( $access_client_service_url ) . '" class="sw-red-button" target="_blank">Access Client Service 🌐</a>';
		}
	} elseif ( is_admin() ) {
		$user_email = $user_info->user_email;
		// Construct the service URL with specified parameters
		$access_client_service_url = esc_url( $service->getServiceUrl() ) . '?auth=1&email=' . urlencode( $user_email ) . '&userisfromcallismartparentwebsite=1&serviceid=' . $service->getServiceId() . '&requestingaccess=1';

		return '<a href="' . esc_url( $access_client_service_url ) . '" class="sw-red-button" target="_blank">Access Client Service 🌐</a>';

	}
	return false;
}

/**
 *  Service details preview URL
 */
function smartwoo_service_preview_url( $service_id ) {
    if ( is_account_page() ) {
        $endpoint_url = wc_get_account_endpoint_url( 'smartwoo-service' );
        $preview_url = add_query_arg(
            array(
                'view_service' => true,
                'service_id'   => $service_id,
            ),
            $endpoint_url
        );
        return esc_url( $preview_url );
    } else {
        $page_id = get_option( 'smartwoo_service_page_id', 0 );
        $page_url = get_permalink( $page_id );
        $preview_url = add_query_arg( array( 'service_id'   => $service_id, ), $page_url .'view-subscription/' );
        return esc_url_raw( $preview_url );
    }
}


function smartwoo_service_page_url() {
	
	if ( is_account_page() ) {
		$endpoint_url = wc_get_account_endpoint_url( 'smartwoo-service' );
		return esc_url_raw( $endpoint_url );
	}

	$page		= get_option( 'smartwoo_service_page_id', 0 );
	$page_url	= get_permalink( $page );
	return esc_url_raw( $page_url );
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
 * Check if a service subscription is active.
 *
 * @param object $service The service object.
 *
 * @return bool True if the subscription is active, false otherwise.
 */
function smartwoo_is_service_active( SmartWoo_Service $service ) {
	$end_date          = smartwoo_extract_only_date( $service->getEndDate() );
	$next_payment_date = smartwoo_extract_only_date( $service->getNextPaymentDate() );
	$current_date      = smartwoo_extract_only_date( current_time( 'mysql' ) );

	if ( $next_payment_date > $current_date && $end_date > $current_date ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if a service subscription is due.
 *
 * @param object $service  The service object
 *
 * @return bool True if the subscription is due, false otherwise
 */
function smartwoo_is_service_due( SmartWoo_Service $service ) {
	$end_date          = smartwoo_extract_only_date( $service->getEndDate() );
	$next_payment_date = smartwoo_extract_only_date( $service->getNextPaymentDate() );
	$current_date      = smartwoo_extract_only_date( current_time( 'mysql' ) );
	if ( $next_payment_date <= $current_date && $end_date > $current_date ) {
		return true;

	} else {
		return false;
	}
}

/**
 * Check if a service is on grace period.
 *
 * @param object $service The service object.
 *
 * @return bool true if the subscription is on grace period, false otherwise.
 */
function smartwoo_is_service_on_grace( SmartWoo_Service $service ) {

	$end_date     = smartwoo_extract_only_date( $service->getEndDate() );
	$current_date = smartwoo_extract_only_date( current_time( 'mysql' ) );

	// Check if the service has passed its end date.
	if ( $current_date >= $end_date ) {
		$product_id = $service->getProductId();

		// Get the grace period end date using the end date as the reference.
		$grace_period_date = smartwoo_get_grace_period_end_date( $product_id, $end_date );

		// Check if there is a valid grace period and if the current date is within the grace period.
		if ( ! empty( $grace_period_date ) && $current_date <= smartwoo_extract_only_date( $grace_period_date ) ) {
			return true;
		}
	}

	return false;
}


/**
 * Check if a service has expired.
 *
 * @param object $service The service object.
 *
 * @return bool true if the subscription has expired, false otherwise.
 */
function smartwoo_has_service_expired( SmartWoo_Service $service ) {

	$current_date = smartwoo_extract_only_date( current_time( 'mysql' ) );
	$expiration_date = smartwoo_get_service_expiration_date( $service );

	// Check if the current date has passed the expiration date.
	if ( $current_date >= $expiration_date ) {
		return true;
	}

	return false;
}


/**
 * Get the status of a service.
 *
 * @param string $service_id The service ID.
 *
 * @return string The status.
 */
function smartwoo_service_status( $service_id ) {

	// Get the service object.
	$service = SmartWoo_Service_Database::get_service_by_id( $service_id );

	// Get the status text from the DB which overrides the calculated status.
	$overriding_status = $service->getStatus();

	// Check calculated statuses.
	$active       = smartwoo_is_service_active( $service );
	$due          = smartwoo_is_service_due( $service );
	$grace_period = smartwoo_is_service_on_grace( $service );
	$expired      = smartwoo_has_service_expired( $service );

	// Check overriding status first
	if ( ! empty( $overriding_status ) ) {
		return $overriding_status;
	}

	// Check calculated statuses in order of priority.
	if ( $active ) {
		return 'Active';
	} elseif ( $due ) {
		return 'Due for Renewal';
	} elseif ( $grace_period ) {
		return 'Grace Period';
	} elseif ( $expired ) {
		return 'Expired';
	}

	// Default status if none of the conditions match.
	return 'Unknown';
}

/**
 * Count the number of 'Active' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Active' services.
 */
function smartwoo_count_active_services( $user_id = null ) {
	$services				= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$active_services_count	= 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Active' === $status ) {
			++$active_services_count;
		}
	}
	return $active_services_count;
}

/**
 * Count the number of 'Due for Renewal' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Due for Renewal' services.
 */
function smartwoo_count_due_for_renewal_services( $user_id = null ) {
	$services			= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$due_services_count = 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Due for Renewal' === $status ) {
			++$due_services_count;
		}
	}

	return $due_services_count;
}

/**
 * Count the number of 'Active No Renewal' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Active (NR)' services.
 */
function smartwoo_count_nr_services( $user_id = null ) {
	$services 			= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$nr_services_count 	= 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Active (NR)' === $status ) {
			++$nr_services_count;
		}
	}

	return $nr_services_count;
}

/**
 * Count the number of 'Expired' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Expired' services.
 */
function smartwoo_count_expired_services( $user_id = null ) {
	$services 				= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$expired_services_count = 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Expired' === $status ) {
			++$expired_services_count;
		}
	}

	return $expired_services_count;
}

/**
 * Count the number of 'Grace Period' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Grace Period' services.
 */
function smartwoo_count_grace_period_services( $user_id = null ) {
	$services 						= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$grace_period_services_count 	= 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Grace Period' === $status ) {
			++$grace_period_services_count;
		}
	}
	return $grace_period_services_count;
}

/**
 * Count the number of 'Suspended' services for a specific user or all users.
 *
 * @param int|null $user_id The user ID (optional).
 * @return int The number of 'Suspended' services.
 */
function smartwoo_count_suspended_services( $user_id = null ) {
	$services 			= ( $user_id !== null ) ? smartwoo_get_service( $user_id ) : smartwoo_get_service();
	$suspended_services = 0;

	foreach ( $services as $service ) {
		$status = smartwoo_service_status( $service->service_id );

		if ( 'Suspended' === $status ) {
			++$suspended_services;
		}
	}

	return $suspended_services;
}

/**
 * Normalize the status of a service before expiration date, this is
 * used to handle 'Cancelled', 'Active NR' and other custom service, it ensures
 * the service is autocalculated at the end of each billing period.
 * 
 * If the service has already expired, it's automatically suspend in 7days time
 */
function smartwoo_regulate_service_status() {
	$services = SmartWoo_Service_Database::get_all_services();

	foreach ( $services as $service ) {
		$expiry_date    = smartwoo_get_service_expiration_date( $service );
		$service_status = smartwoo_service_status( $service->getServiceId() );

		if ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '+1 day' ) ) ) {

			$field = array(
				'status' => null,
			);
			SmartWoo_Service_Database::update_service_fields( $service->getServiceId(), $field );

		} elseif ( 'Expired' === $service_status && $expiry_date <= date_i18n( 'Y-m-d', strtotime( '-7 days' ) ) ) {
			$field = array(
				'status' => 'Suspended',
			);
			SmartWoo_Service_Database::update_service_fields( $service->getServiceId(), $field );
		}
	}
}
// Hook to run daily.
add_action( 'smartwoo_daily_task', 'smartwoo_regulate_service_status' );

/**
 * Generate a unique service ID based on the provided service name.
 *
 * @param string $service_name The name of the service.
 *
 * @return string The generated service ID.
 */
function smartwoo_generate_service_id( string $service_name ) {
	$service_id_prefix = get_option( 'smartwoo_service_id_prefix', 'SID' );

	$first_alphabets = array_map(
		function ( $word ) {
			return strtoupper( substr( $word, 0, 1 ) );
		},
		explode( ' ', $service_name )
	);

	$unique_id = uniqid();

	$generated_service_id = $service_id_prefix . '-' . implode( '', $first_alphabets ) . $unique_id;

	return $generated_service_id;
}


// AJAX action to generate service ID
add_action( 'wp_ajax_smartwoo_service_id_ajax', 'smartwoo_ajax_service_id_callback' );
/**
 * Generarte service ID via ajax.
 */
function smartwoo_ajax_service_id_callback() { 

	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security' ) ) {
		wp_die( -1, 403 );
	}

	$service_name = sanitize_text_field( $_POST['service_name'] );
	$generated_service_id = smartwoo_generate_service_id( $service_name );
	echo esc_html( $generated_service_id );
	wp_die();
}


/**
 * Get the expiration date for a service based on its end date and grace period.
 *
 * @param object $service The service object.
 * @return string The calculated expiration date.
 */
function smartwoo_get_service_expiration_date( SmartWoo_Service $service ) {
	$end_date			= smartwoo_extract_only_date( $service->getEndDate() );
	$product_id			= $service->getProductId();
	$grace_period_date	= smartwoo_extract_only_date( smartwoo_get_grace_period_end_date( $product_id, $end_date ) );
	$expiration_date	= $grace_period_date ?? $end_date;

	return $expiration_date;
}


/**
 * Service Expiration Action Trigger
 *
 * This function is hooked into _event' action to check for services
 * that have expired today (end date is today) and trigger the 'smartwoo_service_expired' action.
 *
 * @return void
 */
add_action( 'smartwoo_daily_task', 'smatwoo_check_services_expired_today' );

/**
 * Check services for expiration today and trigger 'smartwoo_service_expired' action if found.
 *
 * @return void
 */
function smatwoo_check_services_expired_today() {
	$services = SmartWoo_Service_Database::get_all_services();

	foreach ( $services as $service ) {
		$current_date		= smartwoo_extract_only_date( current_time( 'mysql' ) );
		$expiration_date	= smartwoo_get_service_expiration_date( $service );

		if ( $current_date === $expiration_date ) {
			// Trigger the 'smartwoo_service_expired' action with the current service
			do_action( 'smartwoo_service_expired', $service );
		}
	}
}

/**
 * Get the analysis of service usage.
 *
 * @param string $service_id   The ID of the service.
 *
 * @return array|false Array containing used amount, unused amount, service cost, and additional metrics, or false on failure
 */
function smartwoo_analyse_service_usage( $service_id ) {
	// Get service details
	$service_details = SmartWoo_Service_Database::get_service_by_id( $service_id );

	if ( ! $service_details ) {
		// Service not found
		return false;
	}

	// Extract relevant service details
	$start_date   = strtotime( esc_html( $service_details->getStartDate() ) );
	$end_date     = strtotime( esc_html( $service_details->getEndDate() ) );
	$current_date = current_time( 'timestamp', 0 );
	$product_id   = $service_details->getProductId();

	// Get product details from WooCommerce
	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		// Product not found
		return false;
	}

	// Get the cost of the first product
	$service_cost = (float) $product->get_price(); // Treat as float.

	// Ensure non-negative values for service cost.
	$service_cost = max( 0, $service_cost );

	// Calculate the total days and days passed.
	$total_days  = max( 1, ( $end_date - $start_date ) / 86400 ); // 86400 seconds in a day.
	$days_passed = max( 0, min( $total_days, (int) ( ( $current_date - $start_date ) / 86400 ) ) );

	// Calculate the unused amount based on the daily rate.
	$daily_rate    = $total_days > 0 ? $service_cost / $total_days : 0;
	$unused_amount = $service_cost - ( $daily_rate * $days_passed );

	// Calculate used amount.
	$used_amount = $service_cost - $unused_amount;

	// Additional Metrics.
	$total_service_cost = $service_cost;
	$average_daily_cost = $total_days > 0 ? $total_service_cost / $total_days : 0;

	// Cost Per Product
	$product_costs = array();
	$product_name  = $product->get_name();
	$product_price = (float) $product->get_price(); // Treat as float.

	$product_costs[ $product_name ] = max( 0, $product_price ); // Ensures non-negative value.

	// Percentage Usage.
	$percentage_used   = ( $total_service_cost > 0 ) ? ( $used_amount / $total_service_cost ) * 100 : 0;
	$percentage_unused = ( $total_service_cost > 0 ) ? ( $unused_amount / $total_service_cost ) * 100 : 0;

	// Days Remaining.
	$days_remaining_seconds = max( 0, $total_days - $days_passed ) * 86400;
	$days_remaining         = floor( $days_remaining_seconds / 86400 );
	$hours_remaining        = floor( ( $days_remaining_seconds % 86400 ) / 3600 );
	$minutes_remaining      = floor( ( $days_remaining_seconds % 3600 ) / 60 );
	$seconds_remaining      = $days_remaining_seconds % 60;

	// Average Hourly Usage.
	$average_hourly_usage = ( $total_days > 0 ) ? ( $used_amount / $total_days ) / 24 : 0;

	// Convert to readable format.
	$readable_remaining = sprintf( '%d days %02d:%02d:%02d', $days_remaining, $hours_remaining, $minutes_remaining, $seconds_remaining );

	return array(
		'used_amount'          => max( 0, $used_amount ), // Ensure non-negative value
		'unused_amount'        => max( 0, $unused_amount ), // Ensure non-negative value
		'service_cost'         => max( 0, $total_service_cost ), // Ensure non-negative value
		'average_daily_cost'   => max( 0, $average_daily_cost ), // Ensure non-negative value
		'product_costs'        => $product_costs,
		'percentage_used'      => max( 0, $percentage_used ), // Ensure non-negative value
		'percentage_unused'    => max( 0, $percentage_unused ), // Ensure non-negative value
		'days_remaining'       => $readable_remaining,
		'total_days'           => max( 1, $total_days ), // Ensure non-zero value
		'total_used_days'      => max( 0, $days_passed ),
		'remaining_days'       => max( 0, $total_days - $days_passed ),
		'current_date_time'    => smartwoo_check_and_format( current_time( 'mysql' ), true ),
		'average_hourly_usage' => max( 0, $average_hourly_usage ), // Ensure non-negative value
	);
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
		$product_id = $service->getProductId();
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
 * @param int    $product_id The ID of the Product.
 * @param string $reference_date Reference date for calculating the grace period end date.
 * @return int|null Numeric representation of the grace period in hours, or null if not applicable.
 */
function smartwoo_get_grace_period_end_date( $product_id, $reference_date ) {

	$end_date = null;
	$product = new SmartWoo_Product( $product_id );

	// Get grace period from product metadata
	$grace_period_number	= $product->get_grace_period_number();
	$grace_period_unit		= $product->get_grace_period_unit();

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

	return '<button class="delete-service-button" data-service-id="' . esc_attr( $service_id ) . '">' . __( 'Delete Service ⌫', 'smart-woo-service-invoicing' ) . '</button>';
}

// Add Ajax actions
add_action( 'wp_ajax_smartwoo_delete_service', 'smartwoo_delete_service' );
add_action( 'wp_ajax_nopriv_smartwoo_delete_service', 'smartwoo_delete_service' );

function smartwoo_delete_service() {

	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security' ) ) {
		wp_die( -1, 403 );
	}
	

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( -1, 403);
	}

	$service_id = isset( $_POST['service_id'] ) ? sanitize_key( $_POST['service_id'] ) : '';

	// Validate the service ID
	if ( empty( $service_id ) ) {
		wp_send_json_error( 'Invalid Service ID.' );
		wp_die( -1, 404);

	}

	$delete_result = SmartWoo_Service_Database::delete_service( $service_id );

	// Check the result and send appropriate response
	if ( is_string( $delete_result ) ) {
		// An error occurred.
		wp_send_json_error( $delete_result );
	} else {
		// Success.
		wp_send_json_success( $delete_result );
		exit();
	}
}


/**
 * Retrieve and display service usage metrics.
 *
 * @param int $user_id    User ID.
 * @param int $service_id Service ID.
 */
function smartwoo_usage_metrics_temp( $service_id ) {
	$service = SmartWoo_Service_Database::get_service_by_id( $service_id );

	$service_name = $service->getServiceName();

	$usage_metrics = smartwoo_analyse_service_usage( $service_id );

	// Check if metrics are available
	if ( false !== $usage_metrics ) {
		// Extract metrics
		$used_amount          = $usage_metrics['used_amount'];
		$unused_amount        = $usage_metrics['unused_amount'];
		$total_service_cost   = $usage_metrics['service_cost'];
		$average_daily_cost   = $usage_metrics['average_daily_cost'];
		$product_costs        = $usage_metrics['product_costs'];
		$percentage_used      = $usage_metrics['percentage_used'];
		$percentage_unused    = $usage_metrics['percentage_unused'];
		$days_remaining       = $usage_metrics['days_remaining'];
		$total_days           = $usage_metrics['total_days'];
		$total_used_days      = $usage_metrics['total_used_days'];
		$remaining_days       = $usage_metrics['remaining_days'];
		$current_date_time    = $usage_metrics['current_date_time'];
		$average_hourly_usage = $usage_metrics['average_hourly_usage'];

		// Display metrics in HTML div with inline CSS and WordPress comments
		$metrics = '<div class="serv-details-card">';

		$metrics .= '<h3>Service Usage Metrics</h3>';
		$metrics .= '<p class="service-name">' . $service_name . ' - ' . $service_id . '</p>';
		$metrics .= "<ul class='product-costs'>";
		foreach ( $product_costs as $product_name => $product_cost ) {
			$metrics .= '<li>Product: ' . $product_name . ': ' . wc_price( $product_cost ) . '</li>';
		}
		$metrics .= '</ul>';

		// Cost details.
		$metrics .= '<h4>Cost Details:</h4>';

		$metrics .= '<p class="smartwoo-container-item"><span>Total Cost:</span>' . wc_price( $total_service_cost ) .'</p>';
		$metrics .= '<p class="smartwoo-container-item"><span> Average Daily Cost:</span>' . wc_price( $average_daily_cost ) . '</p>';

		// Usage breakdown
		$metrics .= '<h4>Usage Breakdown:</h4>';
		$metrics .= '<p class="smartwoo-container-item"><span>Used Amount:</span>' . wc_price( $used_amount ) . '</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Unused Amount:</span>' . wc_price( $unused_amount ) . '</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Percentage Used:</span>' . esc_html( $percentage_used ) . '%</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Percentage Unused:</span>' . esc_html( $percentage_unused ) . '%</p>';

		// Days information
		$metrics .= '<h4>Days Information:</h4>';
		$metrics .= '<p class="smartwoo-container-item"><span>Total Days:</span> ' . esc_html( $total_days ) . '</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Used Days:</span>' . esc_html( $total_used_days ) . '</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Remaining Days:</span>'. esc_html( $remaining_days ) . '</p>';
		$metrics .= '<p class="smartwoo-container-item"><span>Expiration Countdown:</span>' . esc_html( $days_remaining ) . '</p>';

		// Additional information
		$metrics .= '<h4>Additional Information:</h4>';
		$metrics .= '<p class="smartwoo-container-item"><span>Current date and Time:</span>' . esc_html( $current_date_time ) . "</p>";
		$metrics .= '<p class="smartwoo-container-item"><span>Usage Per hour:</span>' . wc_price( $average_hourly_usage ) . "</p>";
		$metrics .= '</div>';

	} else {
		// Handle the case where service details are not available
		$metrics  = '<div class="sw-no-service-details">';
		$metrics .= smartwoo_notice( 'Service usage metrics cannot be retrieved at this time' );
		$metrics .= '</div>';
	}
	return $metrics;
}
