<?php
// phpcs:ignoreFile

/**
 * File name    :   contr.php
 *
 * @author      :   Callistus
 * Description  :   Control File for service frontend
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * This line handles the client_services shortcode and is
 * used to determine which url parameters are allowed
 * in the client service page
 */


// Define the shortcode function
function sw_service_shortcode() {
	// Check if the user is not logged in
	if ( ! is_user_logged_in() ) {
		return 'You must be logged in to view this page.';
	}
	
	// Start output buffering
	ob_start();

	$current_user_id  = get_current_user_id();
	$current_user     = wp_get_current_user();
	$currentuseremail = $current_user->user_email;

	// Get and sanitize the 'service_page' parameter
	$url_param = isset( $_GET['service_page'] ) ? sanitize_key( $_GET['service_page'] ) : '';

	// Validate the 'service_page' parameter against a list of allowed values
	$allowed_actions = array( 'service_details', 'service_downgrade', 'active', 'renewal_due', 'expired', 'grace_period', 'service_upgrade', 'service_downgrade', 'buy_new_service' );

	// If 'service_page' is set and not empty, validate against allowed actions
	if ( $url_param !== '' && ! in_array( $url_param, $allowed_actions ) ) {
		echo 'Invalid action type.';
	} else {
		// Switch based on the validated 'service_page' parameter
		switch ( $url_param ) {
			case 'service_details':
				echo sw_handle_service_details( $current_user_id, $currentuseremail );
				break;
			case 'service_upgrade':
				echo sw_handle_upgrade_service( $current_user_id );
				break;
			case 'service_downgrade':
				echo sw_handle_downgrade_service( $current_user_id );
				break;
			case 'buy_new_service':
				echo sw_handle_buy_new_service( $current_user_id );
				break;
			case 'active':
				echo sw_handle_service_by_status( $current_user_id, 'Active' );
				break;
			case 'renewal_due':
				echo sw_handle_service_by_status( $current_user_id, 'Due for Renewal' );
				break;
			case 'expired':
				echo sw_handle_service_by_status( $current_user_id, 'Expired' );
				break;
			case 'grace_period':
				echo sw_handle_service_by_status( $current_user_id, 'Grace Period' );
				break;
			default:
				echo sw_handle_main_page( $current_user_id );
				break;
		}
	}

	// Get the buffered output
	$output = ob_get_clean();

	// Return the output
	return $output;
}

// AJAX handler for billing details.
add_action( 'wp_ajax_load_billing_details', 'sw_load_billing_details_callback' );

/**
 * Ajax callback for user billing details in frontend
 */

function sw_load_billing_details_callback() {
	// Check if the user is logged in
	if ( is_user_logged_in() ) {
		// Get the current user ID
		$user_id = get_current_user_id();

		// Get additional customer details
		$billingFirstName = get_user_meta( $user_id, 'billing_first_name', true );
		$billingLastName  = get_user_meta( $user_id, 'billing_last_name', true );
		$company_name     = get_user_meta( $user_id, 'billing_company', true );
		$email            = get_user_meta( $user_id, 'billing_email', true );
		$phone            = get_user_meta( $user_id, 'billing_phone', true );
		$website          = get_user_meta( $user_id, 'billing_website', true );
		$nationality      = get_user_meta( $user_id, 'billing_country', true );
		$billingAddress   = sw_get_user_billing_address( $user_id );

		// Construct the HTML for billing details
		$html  = '<div class="billing-details-container">';
		$html .= '<h3>Billing Details</h3>';
		$html .= '<p><strong>Name:</strong> ' . esc_html( $billingFirstName . ' ' . $billingLastName ) . '</p>';
		$html .= '<p><strong>Company Name:</strong> ' . esc_html( $company_name ) . '</p>';
		$html .= '<p><strong>Email Address:</strong> ' . esc_html( $email ) . '</p>';
		$html .= '<p><strong>Phone:</strong> ' . esc_html( $phone ) . '</p>';
		$html .= '<p><strong>Website:</strong> ' . esc_html( $website ) . '</p>';
		$html .= '<p><strong>Address:</strong> ' . esc_html( $billingAddress ) . '</p>';
		$html .= '<p><strong>Nationality:</strong> ' . esc_html( $nationality ) . '</p>';
		$html .= '<button class="account-button" onclick="confirmEditBilling()">Edit My Billing Address</button>';
		$html .= '</div>';

		// Send the HTML response
		echo $html;
	} else {
		// User is not logged in, handle accordingly
		echo 'User not logged in';
	}

	// prevent further outputing
	die();
}

/**
 * Ajax callback for user details in frontend
 */
function sw_load_my_details_callback() {
	// Check if the user is logged in
	if ( is_user_logged_in() ) {
		// Get the current user object
		$current_user = wp_get_current_user();

		// Get user details
		$full_name = $current_user->display_name ;
		$email     = $current_user->user_email ;
		$bio       = $current_user->description ;
		$user_role = implode( ', ', $current_user->roles );
		$user_url  = $current_user->user_url ;

		// Construct the HTML for user details
		$html  = '<div class="user-details-container">';
		$html .= '<h3>My Details</h3>';
		$html .= '<div class="user-details">';
		$html .= '<p><strong>Full Name:</strong> ' . esc_html( $full_name ) . '</p>';
		$html .= '<p><strong>Email:</strong> ' . esc_html( $email ) . '</p>';
		$html .= '<p><strong>Bio:</strong> ' . esc_html( $bio ) . '</p>';
		$html .= '<p><strong>Website:</strong> ' . esc_html( $user_url ) . '</p>';
		$html .= '<p><strong>Account type:</strong> ' . esc_html( ucwords( $user_role ) ) . '</p>';
		$html .= '</div>';
		$html .= '<button class="account-button" onclick="confirmEditAccount()">Edit My Information</button>';
		$html .= '<button class="account-button" onclick="confirmPaymentMethods()">Payment Methods</button>';
		$html .= '</div>';
		// Send the HTML response
		echo $html;
	} else {
		// User is not logged in, handle accordingly
		echo 'User not logged in';
	}

	// prevent further outputing
	die();
}
add_action( 'wp_ajax_load_my_details', 'sw_load_my_details_callback' );

add_action( 'wp_ajax_load_account_logs', 'sw_load_account_logs_callback' );
/**
 * Ajax function callback for account logs in frontend
 */
function sw_load_account_logs_callback() {
	// Check if the user is logged in
	if ( is_user_logged_in() ) {
		// Get the current user object
		$current_user = wp_get_current_user();

		if ( $current_user ) {

			$user_id      		= $current_user->ID;
			$current_login_time = sw_get_current_login_date( $user_id );
			$last_active		= sw_get_last_login_date( $user_id );

			// Retrieve user registration date from the database
			$registration_date 	=  sw_check_and_format( $current_user->user_registered, true );

			// Start constructing the HTML for account logs
			$html  = '<div class="account-logs-container">';
			$html .= '<h3>Account Logs</h3>';
			$html .= '<ul class="account-logs-list">';

			// Total service renewed
			$renewed_services_count = get_renewed_services_count( $user_id );
			$html .= '<li class="account-log-item"> Total Renewed Services: ' . esc_attr( $renewed_services_count ) . '</li>';

			// Display Total Amount Spent first
			$total_spent = sw_get_total_spent_by_user( $user_id );
			$html .= '<li class="account-log-item">Total Amount Spent: ' . $total_spent . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			// Display Last Active Time
			$html .= '<li class="account-log-item">Current Login Time: ' . esc_attr( $current_login_time )  . '</li>';
			$html .= '<li class="accont-log-item">Last logged In: ' . esc_attr( $last_active ) . '</li>';

			// Display user registration date
			$html .= '<li class="account-log-item">Registration Date: ' . esc_attr( $registration_date ) . '</li>';

			// Retrieve and display the user's location using ip-api.com
			$ip_address    = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
			$location_info = file_get_contents( 'http://ip-api.com/json/' . $ip_address );
			$location_data = json_decode( $location_info );
			
			// Display IP Address
			$html .= '<li class="account-log-item">IP Address: ' . esc_attr( $ip_address ) . '</li>';

			if ( $location_data && $location_data->status === 'success' ) {
				$user_location             = $location_data->city . ', ' . $location_data->country;
				$internet_service_provider = $location_data->isp;

				$html .= '<li class="account-log-item">Location: ' . esc_attr( $user_location ) . '</li>';
				$html .= '<li class="account-log-item">Internet Service Provider: ' . esc_attr( $internet_service_provider ) . '</li>';
			} else {
				$html .= '<li class="account-log-item">Location: ' . esc_attr( 'Unknown' ) . '</li>';
			}

			$html .= '</ul>';
			$html .= '</div>';

			// Send the HTML response
			echo $html;
		} else {
			// No activity information found
			echo '<p>No activity information found.</p>';
		}
	} else {
		// User is not logged in
		echo '<p>Please log in to view user activity information.</p>';
	}

	// prevent further outputing
	die();
}



add_action( 'wp_ajax_load_transaction_history', 'sw_load_transaction_history_callback' );
/**
 * Ajax callback for user transaction history in the frontend
 */
function sw_load_transaction_history_callback() {
	// Check if the user is logged in
	if ( is_user_logged_in() ) {
		// Get the current user object
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;

		$html = '<h3>Transaction History</h3>';

		// Manually process and include the [transactions] shortcode
		ob_start();
		echo do_shortcode( '[sw_transactions]' );
		$transactions_content = ob_get_clean();

		// Include the transactions content in the HTML response
		$html .= $transactions_content;

		// Send the HTML response
		echo $html;
	} else {
		// User is not logged in
		echo '<p>Please log in to view transaction history.</p>';
	}

	// prevent further outputing
	die();
}


/**
 * Set user's login timestamp.
 * 
 * @param string $user_login	User's Username.
 * @param object $user			WordPress user object.
 * @since      : 1.0.1 
 */
function sw_timestamp_user_at_login( $user_login, $user ) {
	update_user_meta( $user->ID, 'sw_login_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_login', 'sw_timestamp_user_at_login', 99, 2 );

/**
 * Set user's logout timestamp.
 * 
 * @param $user_id		The logged user's ID
 */
function sw_timestamp_user_at_logout( $user_id ){
	update_user_meta( $user_id, 'sw_logout_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_logout', 'sw_timestamp_user_at_logout' );

/**
 * Retrieve the user's current login date and time.
 * 
 * @param int $user_id The User's ID.
 * @since      : 1.0.1
 */
function sw_get_current_login_date( $user_id ) {
    $timestamp = get_user_meta( $user_id, 'sw_login_timestamp', true );

    // Check if $timestamp is not a valid integer (may be a string)
    if ( ! is_numeric( $timestamp ) || intval( $timestamp ) <= 0 ) {
        // Fallback to current time if $timestamp is not a valid integer
        $timestamp = current_time( 'timestamp' );
    }

    return sw_convert_timestamp_to_readable_date( $timestamp, true );
}

/**
 * Retrieve the user's last login date and time
 * 
 * @param int $user_id  The User's ID
 * @since	: 1.0.1
 */
function sw_get_last_login_date( $user_id ) {

	$timestamp = get_user_meta( $user_id, 'sw_logout_timestamp', true );

    // Check if $timestamp is not a valid integer (may be a string)
    if ( ! is_numeric( $timestamp ) || intval( $timestamp ) <= 0 ) {
        // Fallback to current time if $timestamp is not a valid integer
        $timestamp = current_time( 'timestamp' );
    }

    return sw_convert_timestamp_to_readable_date( $timestamp, true );
}

