<?php
/**
 * File name    :   contr.php
 * 
 * Description  :   Control File for service frontend
 * 
 * @author      :   Callistus
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * This the callback function handles the services shortcode and is
 * used to determine which url parameters are allowed
 * in the client service page.
 */
function smartwoo_service_shortcode() {

	if ( ! is_user_logged_in() ) {
		return esc_html__( 'You must be logged in to view this page.', 'smart-woo-service-invoicing' );
	}

	$current_user_id  = get_current_user_id();
	$current_user     = wp_get_current_user();
	// Get and sanitize the 'service_page' parameter.
	$url_param = isset( $_GET['service_page'] ) ? sanitize_key( $_GET['service_page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$allowed_actions = array( 'service_details', 'service_downgrade', 'active', 'renewal_due', 'expired', 'grace_period', 'service_upgrade', 'service_downgrade', 'buy_new_service' );

	// If 'service_page' is set and not empty, validate against allowed actions.
	if ( $url_param !== '' && ! in_array( $url_param, $allowed_actions ) ) {
		return esc_html__( 'Invalid action type.' );
	} 

	// Switch based on the validated 'service_page' parameter.
	switch ( $url_param ) {
		case 'service_details':

			$service_details_page 	= smartwoo_service_details( $current_user_id );
			$output 				=  $service_details_page;
			break;

		case 'service_upgrade':

			$service_upgrade_page 	= smartwoo_upgrade_temp( $current_user_id );
			$output 				= $service_upgrade_page;
			break;

		case 'service_downgrade':

			$service_downgrade_page = smartwoo_downgrade_temp( $current_user_id );
			$output 				= $service_downgrade_page;
			break;

		case 'buy_new_service':

			$buy_new_service_page 	= smartwoo_buy_new_temp( $current_user_id );
			$output 			  	= $buy_new_service_page;
			break;

		case 'active':

			$active_services_page 	= smartwoo_user_service_by_status( $current_user_id, 'Active' );
			$output 				= $active_services_page;
			break;

		case 'renewal_due':
			
			$due_services_page 	= smartwoo_user_service_by_status( $current_user_id, 'Due for Renewal' );
			$output 			= $due_services_page;
			break;

		case 'expired':
			$expired_services_page 	= smartwoo_user_service_by_status( $current_user_id, 'Expired' );
			$output 				= $expired_services_page;
			break;

		case 'grace_period':
			$on_grace_services 	= smartwoo_user_service_by_status( $current_user_id, 'Grace Period' );
			$output 			= $on_grace_services;
			break;
		default:
			$main_page 	= smartwoo_front_main_page( $current_user_id );
			$output 	= $main_page;
			break;
		
	}
	
	// Return the output for shortcode handler.
	return wp_kses_post( $output );
}

// AJAX handler for billing details.
add_action( 'wp_ajax_load_billing_details', 'smartwoo_load_billing_details_callback' );

/**
 * Ajax callback for user billing details in frontend.
 */

function smartwoo_load_billing_details_callback() {

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
		wp_die();
	}

	if ( is_user_logged_in() ) {

		$user_id = get_current_user_id();
		// Get additional customer details
		$billingFirstName = get_user_meta( $user_id, 'billing_first_name', true );
		$billingLastName  = get_user_meta( $user_id, 'billing_last_name', true );
		$company_name     = get_user_meta( $user_id, 'billing_company', true );
		$email            = get_user_meta( $user_id, 'billing_email', true );
		$phone            = get_user_meta( $user_id, 'billing_phone', true );
		$website          = get_user_meta( $user_id, 'billing_website', true );
		$billingAddress   = sw_get_user_billing_address( $user_id );
		// Construct the HTML for billing details
		$html  = '<div class="billing-details-container">';
		$html .= '<h3>Billing Details</h3>';
		$html .= '<p><strong>Name:</strong> ' . esc_html( $billingFirstName . ' ' . $billingLastName ) . '</p>';
		$html .= '<p><strong>Company Name:</strong> ' . esc_html( $company_name ) . '</p>';
		$html .= '<p><strong>Email Address:</strong> ' . esc_html( $email ) . '</p>';
		$html .= '<p><strong>Phone:</strong> ' . esc_html( $phone ) . '</p>';
		$html .= '<p><strong>Website:</strong><a> ' . esc_url( $website ) . '</a></p>';
		$html .= '<p><strong>Address:</strong> ' . esc_html( $billingAddress ) . '</p>';
		$html .= '<button class="account-button" id="edit-billing-address">' . esc_html__( 'Edit My Billing Address', 'smart-woo-service-invoicing' ) . '</button>';
		$html .= '</div>';
		echo wp_kses_post( $html );
	} else {
		// User is not logged in, handle accordingly.
		echo esc_html__( 'User not logged in', 'smart-woo-service-invoicing' );
	}

	// prevent further outputing
	wp_die();
}

/**
 * Ajax action handler for user account details.
 */
add_action( 'wp_ajax_load_my_details', 'smartwoo_load_my_details_callback' );
/**
 * Ajax callback for user details in frontend.
 */
function smartwoo_load_my_details_callback() {

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
	
		wp_die();
	}

	if ( is_user_logged_in() ) {

		$current_user = wp_get_current_user();
		// Get user details
		$full_name = $current_user->display_name ;
		$email     = $current_user->user_email ;
		$bio       = $current_user->description ;
		$user_role = implode( ', ', $current_user->roles );
		$user_url  = $current_user->user_url ;
		// Construct the HTML for user details.
		$html  = '<div class="user-details-container">';
		$html .= '<h3>' . esc_html__( 'My Details', 'smart-woo-service-invoicing' ) . '</h3>';
		$html .= '<div class="user-details">';
		$html .= '<p><strong>Full Name:</strong> ' . esc_html( $full_name ) . '</p>';
		$html .= '<p><strong>Email:</strong> ' . esc_html( $email ) . '</p>';
		$html .= '<p><strong>Bio:</strong> ' . esc_html( $bio ) . '</p>';
		$html .= '<p><strong>Website:</strong> ' . esc_html( $user_url ) . '</p>';
		$html .= '<p><strong>Account type:</strong> ' . esc_html( ucwords( $user_role ) ) . '</p>';
		$html .= '</div>';
		$html .= '<button class="account-button" id="edit-account-button">' . esc_html__( 'Edit My Information' ) . '</button>';
		$html .= '<button class="account-button" id="view-payment-button">' . esc_html__( 'Payment Methods' ) . '</button>';
		$html .= '</div>';
		// Send the HTML response.
		echo wp_kses_post( $html );
	} else {
		// User is not logged in, handle accordingly.
		echo esc_html__( 'User not logged in', 'smart-woo-service-invoicing' );
	}

	// prevent further outputing.
	wp_die();
}

/**
 * Ajax action handler for account log.
 */
add_action( 'wp_ajax_load_account_logs', 'smartwoo_load_account_logs_callback' );
/**
 * Ajax function callback for account logs in frontend
 */
function smartwoo_load_account_logs_callback() {

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
		wp_die();
	}

	if ( is_user_logged_in() ) {
		// Get the current user object
		$current_user = wp_get_current_user();
		$user_id      		= $current_user->ID;
		$current_login_time = smartwoo_get_current_login_date( $user_id );
		$last_active		= smartwoo_get_last_login_date( $user_id );
		$registration_date 	= smartwoo_check_and_format( $current_user->user_registered, true );
		$total_spent 		= smartwoo_get_total_spent_by_user( $user_id );
		$html = '<div class="account-logs-container">';
		$html .= '<h3>' . esc_html__( 'Account Logs' ) . '</h3>';
		$html .= '<ul class="account-logs-list">';
		$html .= '<li class="account-log-item">' . esc_html__( 'Total Amount Spent: ', 'smart-woo-service-invoicing' ) . wc_price( $total_spent ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$html .= '<li class="account-log-item">' . esc_html__( 'Current Login Time: ', 'smart-woo-service-invoicing' ) . esc_html( $current_login_time )  . '</li>';
		$html .= '<li class="account-log-item">' . esc_html__( 'Last logged In: ', 'smart-woo-service-invoicing' ) . esc_html( $last_active ) . '</li>';
		$html .= '<li class="account-log-item">' . esc_html__( 'Registration Date: ' ) . esc_html( $registration_date ) . '</li>';

		/**
		 * Retrieve User's Personal logged information using WooCommerce geolocation feature.
		 */

		$ip_address 	  = WC_Geolocation::get_ip_address();
		$location_data    = WC_Geolocation::geolocate_ip( $ip_address );
		
		// Display IP Address
		$html .= '<li class="account-log-item">IP Address: ' . esc_html( $ip_address ) . '</li>';

		if ( ! empty( $location_data ) ) {
			$user_location             = $location_data['city'] . ', ' . $location_data['country'];

			$html .= '<li class="account-log-item">' . esc_html__( 'Location: ', 'smart-woo-service-invoicing' ) . esc_html( $user_location ) . '</li>';
		} else {
			$html .= '<li class="account-log-item">Location: ' . esc_html__( 'Unknown', 'smart-woo-service-invoicing' ) . '</li>';
		}

		$html .= '</ul>';
		$html .= '</div>';

		echo wp_kses_post( $html );
		
	} else {
		// User is not logged in
		echo esc_html__( 'Please log in to view user activity information.' );
	}

	// prevent further outputing
	die();
}



add_action( 'wp_ajax_load_transaction_history', 'smartwoo_load_transaction_history_callback' );
/**
 * Ajax callback for user transaction history in the frontend
 */
function smartwoo_load_transaction_history_callback() {

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security' ) ) {
		wp_die();
	}

	if ( is_user_logged_in() ) {

		$html = '<h3>Transaction History</h3>';
		$html .= smartwoo_transactions_shortcode_output();

		echo wp_kses_post( $html );
	} else {
		// User is not logged in
		echo esc_html__( 'Please log in to view transaction history.' );
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
function smartwoo_timestamp_user_at_login( $user_login, $user ) {
	update_user_meta( $user->ID, 'sw_login_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_login', 'smartwoo_timestamp_user_at_login', 99, 2 );

/**
 * Set user's logout timestamp.
 * 
 * @param $user_id		The logged user's ID
 */
function smartwoo_timestamp_user_at_logout( $user_id ){
	update_user_meta( $user_id, 'sw_logout_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_logout', 'smartwoo_timestamp_user_at_logout' );

/**
 * Retrieve the user's current login date and time.
 * 
 * @param int $user_id The User's ID.
 * @since      : 1.0.1
 */
function smartwoo_get_current_login_date( $user_id ) {
    $timestamp = get_user_meta( $user_id, 'sw_login_timestamp', true );

    // Check if $timestamp is not a valid integer (may be a string)
    if ( ! is_numeric( $timestamp ) || intval( $timestamp ) <= 0 ) {
        // Fallback to current time if $timestamp is not a valid integer
        $timestamp = current_time( 'timestamp' );
    }

    return smartwoo_convert_timestamp_to_readable_date( $timestamp, true );
}

/**
 * Retrieve the user's last login date and time
 * 
 * @param int $user_id  The User's ID
 * @since	: 1.0.1
 */
function smartwoo_get_last_login_date( $user_id ) {

	$timestamp = get_user_meta( $user_id, 'sw_logout_timestamp', true );

    // Check if $timestamp is not a valid integer (may be a string)
    if ( ! is_numeric( $timestamp ) || intval( $timestamp ) <= 0 ) {
        // Fallback to current time if $timestamp is not a valid integer
        $timestamp = current_time( 'timestamp' );
    }

    return smartwoo_convert_timestamp_to_readable_date( $timestamp, true );
}

