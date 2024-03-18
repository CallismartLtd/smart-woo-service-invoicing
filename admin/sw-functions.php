<?php
/**
 * File name    :   sw-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file
 */

defined( 'ABSPATH' ) || exit; // exit if eccessed directly

/**
 * Function to format date to a human-readable format or show 'Not Available'.
 *
 * @param string $dateString Date String.
 * @param bool   $includeTime Whether to include the time aspect. Default is true.
 * @return string Formatted date or 'Not Available'.
 */
function sw_check_and_format( $dateString, $includeTime = false ) {
	$format = $includeTime ? 'l jS F Y \a\t h:i:s A' : 'l jS F Y';

	return ! empty( $dateString ) ? esc_html( date_i18n( $format, strtotime( $dateString ) ) ) : esc_html( 'Not Available' );
}

/**
 * Extracts the date portion from a date and time string.
 *
 * @param string $dateTimeString The date and time string.
 * @return string The extracted date in 'Y-m-d' format.
 */
function sw_extract_date_only( string $datetimestring ) {
	// Explicitly cast $datetimestring to a string
	$datetimestring = (string) $datetimestring;

	// Use strtotime to convert the date and time string to a Unix timestamp
	$timestamp = strtotime( $datetimestring );

	// Use date to format the timestamp to the desired 'Y-m-d' format
	return date_i18n( 'Y-m-d', $timestamp );
}

/**
 * Convert timestamp to a readable date using sw_check_and_format function.
 *
 * @param int  $timestamp   Unix timestamp.
 * @param bool $includeTime Whether to include the time aspect. Default is true.
 * @return string Formatted date or 'Not Available'.
 */
function sw_convert_timestamp_to_readable_date( int $timestamp, bool $includeTime = true ) {
    // Convert the timestamp to a date string
    $dateString = date_i18n( 'Y-m-d H:i:s', $timestamp );

    // Use sw_check_and_format to format the date string
    return sw_check_and_format( $dateString, $includeTime );
}



/**
 * Check if Proration is Enabled or Disabled
 *
 * @return string Returns "Enabled" if sw_prorate is enabled, "Disabled" if disabled, or "Not Configured" if not set.
 */
function sw_Is_prorate() {
	$sw_prorate = get_option( 'sw_prorate', 'Disabled' );

	if ( $sw_prorate === 'Enable' ) {
		return 'Enabled';
	} elseif ( $sw_prorate === 'Disable' ) {
		return 'Disabled';
	} else {
		return 'Disabled';
	}
}

/**
 * Handle refund-related operations in the service log records.
 *
 * This function allows you to retrieve service log records with a 'Pending Refund' status or update
 * specific records based on user ID, service ID, amount, and change their transaction status and details.
 *
 * @param int|null    $user_id    User ID (optional).
 * @param int|null    $service_id Service ID (optional).
 * @param float|null  $amount     Amount (optional).
 * @param string|null $newstatus  New transaction status (optional).
 * @param string|null $details    Additional details for the updated records (optional).
 *
 * @return mixed Returns an array of service log records when no parameters are provided, or the number
 *               of updated records when updating records based on the provided parameters.
 */
function sw_REFUND_handler( $user_id = null, $service_id = null, $amount = null, $newstatus = null, $details = null ) {
	global $wpdb;

	$service_logs_table_name = $wpdb->prefix . 'sw_service_logs';

	if ( $user_id === null && $service_id === null && $amount === null ) {
		$query = $wpdb->prepare(
			"SELECT * FROM {$service_logs_table_name} WHERE transaction_status = %s",
			'Pending Refund'
		);

		// $query is prepared
		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $results;
	} else {
		$where = array( 'transaction_status' => 'Pending Refund' );

		if ( $user_id !== null ) {
			$where['user_id'] = $user_id;
		}
		if ( $service_id !== null ) {
			$where['service_id'] = $service_id;
		}
		if ( $amount !== null ) {
			$where['amount'] = $amount;
		}

		$updated = $wpdb->update(
			$service_logs_table_name,
			array(
				'transaction_status' => $newstatus,
				'details'            => $details,
			),
			$where,
			array(
				'%s', // Format for 'transaction_status'
				'%s'  // Format for 'details'
			),
			array(
				'%d', // Format for 'user_id'
				'%d', // Format for 'service_id'
				'%f'  // Format for 'amount'
			)
		);

		return $updated;
	}
}




/**
 * Log data into the database
 *
 * @param string    $log_id        ID to stamp the log
 * @param string    $log_type      The type of log
 * @param string 	$status 	   Status of the Log.
 * @param string    $details       Additional details or notes related to the log.
 * @param float  	$amount        Amount to log (Defaults to 0.00)
 * @param string	$note		   Internal Note for reference Purposes
 *
 */
function smart_woo_log( $log_id, $log_type, $status, $details = '',  $amount = 0, $note = '' ) {
	
	// Instantiate an object of the class.
	$log = new Sw_Invoice_log();

	// Set data using setter methods
	$log->setLogId( $log_id );
	$log->setLogType( $log_type );
	$log->setAmount( $amount );
	$log->setStatus( $status );
	$log->setDetails( $details );
	$log->setNote( $note );
	
	// Log the data
	$log->save( $log );


}

/**
 * Procedural function to perform refund.
 *
 * This function initiates the refund process for the specified logged data by its ID.
 *
 * @since : 1.0.1
 * 
 * @param string $log_id The ID of the logged data to refund.
 * @return bool True if the refund is successfully initiated, false otherwise.
 */
function sw_refund_completed( $log_id ) {
    return Sw_Refund::refunded( $log_id );
}


/**
 * Check the configured Invoice Number Prefix.
 *
 * @return string The configured Invoice Number Prefix.
 */
function sw_get_invoice_number_prefix() {
	$invoice_number_prefix = get_option( 'sw_invoice_id_prefix', 'CINV' );
	return $invoice_number_prefix;
}


/**
 * Generate unique Token
 *
 * @return string Random 32-character hexadecimal token
 */
function sw_generate_unique_token() {
	return bin2hex( random_bytes( 16 ) );
}

/**
 * Generates a unique payment link based on invoice details and stores necessary information in transients.
 *
 * @param string $invoice_id  The Service ID associated with the Invoice.
 * @param string $user_email  The email address of the user associated with the order.
 *
 * @return string The generated payment link with a unique URL structure.
 */
function swsi_generate_payment_link( $invoice_id, $user_email ) {

	// Generate a unique token
	$token = sw_generate_unique_token();

	// Get and sanitize the parameter values
	$invoice_id = sanitize_text_field( $invoice_id );
	$user_email = sanitize_email( $user_email );

	// Store the information in a transient with a 24-hour expiration
	set_transient(
		'sw_payment_info_' . $token,
		array(
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
		),
		24 * HOUR_IN_SECONDS
	);

	// Construct a unique URL structure for the payment link
	$payment_link = add_query_arg(
		array(
			'action'     => 'sw_invoice_payment',
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
			'token'      => $token,  // Include the generated token
		),
		home_url()
	);

	return esc_url( $payment_link );
}

/**
 * Verify the token, fetch associated information, and delete the token if valid.
 *
 * @param string $token The token to verify.
 *
 * @return array|false If the token is valid, return an array with invoice_id and user_email; otherwise, return false.
 */
function swsi_verify_token( $token ) {
	// Retrieve information from the transient
	$payment_info = get_transient( 'sw_payment_info_' . $token );

	// Check if the transient exists and has not expired
	if ( $payment_info ) {
		// Extract relevant information
		$invoice_id = $payment_info['invoice_id'];
		$user_email = $payment_info['user_email'];

		// Delete the transient to ensure one-time use
		delete_transient( 'sw_payment_info_' . $token );

		return array(
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
		);
	}

	// Token is invalid or expired
	return false;
}


/**
 * Performs check if migration is allowed
 */
function sw_Is_migration() {
	$sw_allow_migration = get_option( 'sw_allow_migration', 'Disable' );

	// Check if service upgrade is enabled
	return $sw_allow_migration === 'Enable';
}


/**
 * Generate a notice message.
 *
 * @param string $message The notice message.
 * @return string HTML markup for the notice message.
 */
function sw_notice( $message ) {
	// HTML and styles for the notice message
	$output  = '<div style="background-color: #ffe9a7; padding: 10px; border: 1px solid #f3c100; border-radius: 5px; margin: 10px 0; display: flex; align-items: center;">';
	$output .= '<span style="font-size: 20px; margin-right: 10px;">⚠</span>';
	$output .= '<p style="margin: 0; flex-grow: 1; font-weight: bold;">' . esc_html( $message ) . '</p>'; 
	$output .= '<span style="font-size: 20px; margin-right: 10px;">⚠</span>';
	$output .= '</div>';

	return $output;
}


if ( ! function_exists( 'sw_error_notice' ) ) {
	/**
	 * Display an error notice to the user.
	 *
	 * @param string|array $messages Error message(s) to display.
	 */
	function sw_error_notice( $messages ) {
		echo '<div class="sw-error-notice notice notice-error is-dismissible">';

		if ( is_array( $messages ) ) {
			echo sw_notice( 'Errors !!' );

			$error_number = 1;

			foreach ( $messages as $message ) {
				echo '<p>' . esc_html( $error_number . '. ' . $message ) . '</p>';
				++$error_number;
			}
		} else {
			echo sw_notice( 'Error !!' );
			echo '<p>' . esc_html( $messages ) . '</p>';
		}

		echo '</div>';
	}
}



/**
 * Redirects to the invoice preview page based on the provided invoice ID.
 *
 * @param int $invoice_id The ID of the invoice.
 */
function sw_redirect_to_invoice_preview( $invoice_id ) {
	$invoice_page = get_option( 'sw_invoice_page', 0 );
	$redirect_url = get_permalink( $invoice_page ) . '?invoice_page=view_invoice&invoice_id=' . $invoice_id;
	wp_safe_redirect( $redirect_url );
	exit();
}


/**
 * Get all orders for the configured service or check if a specific order is configured.
 *
 * @param int|null $order_id_to_get Optional. If provided, returns the ID of the specified order.
 * @return int|array The ID of the specified order if $order_id_to_get is provided and is configured, or an array of order IDs for configured orders.
 */
function sw_get_orders_for_configured_products( $order_id_to_get = null ) {
	// If $order_id_to_get is provided, return the ID of the specified order
	if ( $order_id_to_get !== null ) {
		$order = wc_get_order( $order_id_to_get );
		if ( $order && has_sw_configured_products( $order ) ) {
			return $order_id_to_get;
		} else {
			return 0; // Return 0 to indicate that the specified order is not configured
		}
	}

	// Initialize an empty array to store order IDs
	$order_ids = array();

	// Query WooCommerce orders
	$orders = wc_get_orders(
		array(
			'limit' => -1,  // Retrieve all orders
		)
	);

	// Loop through the orders
	foreach ( $orders as $order ) {
		// Check if the order has configured products
		if ( $order && has_sw_configured_products( $order ) ) {
			$order_ids[] = $order->get_id();
		}
	}

	// If $order_id_to_get is not provided, return the array of order IDs
	return $order_ids;
}

/**
 * Check if an order has configured products.
 *
 * @param WC_Order $order The WooCommerce order object.
 * @return bool True if the order has configured products, false otherwise.
 */
function has_sw_configured_products( $order ) {
	$items = $order->get_items();

	foreach ( $items as $item_id => $item ) {
		$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
		if ( ! empty( $service_name ) ) {
			return true; // Configured product found
		}
	}

	return false; // No configured products found
}


/**
 * Service and Invoice pages navigation bar
 *
 * @param int $user_id   The current user's ID
 */
function sw_get_navbar( $current_user_id ) {

	// Get the URL of the service page from the plugin options
	$service_page_id  = get_option( 'sw_service_page', 0 );
	$service_page_url = get_permalink( $service_page_id );

	// Get the URL of the invoice preview page from the plugin options
	$invoice_preview_page_id  = get_option( 'sw_invoice_page', 0 );
	$invoice_preview_page_url = get_permalink( $invoice_preview_page_id );

	// Determine the current page
	$current_page_slug = '';
	$navbar_title      = '';

	if ( is_page( $service_page_id ) ) {
		$current_page_slug = 'services';
		$navbar_title      = 'My Services';
	} elseif ( is_page( $invoice_preview_page_id ) ) {
		$current_page_slug = 'invoices';
		$navbar_title      = 'My Invoices';
	}

	// Set the default page title
	$page_title = $navbar_title;

	// If the current page is 'services' and a service action is selected
	if ( $current_page_slug === 'services' && isset( $_GET['service_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$service_action = sanitize_key( $_GET['service_action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Customize the page title based on the selected service action
		switch ( $service_action ) {
			case 'upgrade':
				$page_title = 'Upgrade Service';
				break;
			case 'downgrade':
				$page_title = 'Downgrade Service';
				break;
			case 'buy_new':
				$page_title = 'Buy New Service';
				break;
		}
	}

	echo '<div class="service-navbar">';

	// Container for the title (aligned to the left)
	echo '<div class="navbar-title-container">';
	echo '<h3>' . esc_html( $page_title ) . '</h3>';
	echo '</div>';

	// Container for the links (aligned to the right)
	echo '<div class="navbar-links-container">';
	echo '<ul>';

	// Add link to the service page
	echo '<li><a href="' . esc_url( $service_page_url ) . '" class="' . ( $current_page_slug === 'services' ? 'current-page' : '' ) . '">Services</a></li>';

	// Add link to the invoice preview page
	echo '<li><a href="' . esc_url( $invoice_preview_page_url ) . '" class="' . ( $current_page_slug === 'invoices' ? 'current-page' : '' ) . '">Invoices</a></li>';

	// Add dropdown for service actions only on the service page
	if ( $current_page_slug === 'services' ) {
		// Dropdown for service actions
		echo '<li class="service-actions-dropdown">';
		echo '<select onchange="redirectBasedOnServiceAction(this.value)">';
		echo '<option value="" selected>Select Action</option>';
		echo '<option value="upgrade">Upgrade Service</option>';
		echo '<option value="downgrade">Downgrade Service</option>';
		echo '<option value="buy_new">Buy New Service</option>';
		// Add more options as needed
		echo '</select>';
		echo '</li>';
	}

	echo '</ul>';
	echo '</div>';

	echo '</div>';
}
/**
 * Determine whether or not we are in the frontend
 * 
 * @since 1.0.1
 */
function is_smart_woo_frontend() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return true;
	}
}