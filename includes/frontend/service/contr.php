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
		return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'Login to access this page.' ), 'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	global $wp_query;
	$url_param = '';

	if ( isset ( $wp_query->query_vars['buy-new'] ) ) {
		$url_param = 'buy-new';
	}

	if ( isset ( $wp_query->query_vars['view-subscription'] ) ) {
		$url_param = 'view-subscription';
	}

	if ( isset ( $wp_query->query_vars['view-subscriptions-by'] ) ) {
		$url_param = 'view-subscriptions-by';
	}

	if ( isset ( $wp_query->query_vars['downgrade'] ) ) {
		$url_param = 'downgrade';
	}

	if ( isset ( $wp_query->query_vars['upgrade'] ) ) {
		$url_param = 'upgrade';
	}

	switch ( $url_param ) {
		case 'view-subscription':

			$service_details_page 	= smartwoo_service_details();
			$output 				=  wp_kses_post( $service_details_page );
			break;

		case 'upgrade':

			if ( ! method_exists( 'SmartWooPro', 'upgrade_temp' ) ) {
				$service_upgrade_page	= smartwoo_service_front_temp();
			} else {
				$upgrader = new SmartWooPro();
				$service_upgrade_page	= $upgrader->upgrade_temp();
			}

			$output	= wp_kses( $service_upgrade_page, smartwoo_allowed_form_html() );
			break;

		case 'downgrade':

			if ( ! method_exists( 'SmartWooPro', 'downgrade_temp' ) ) {
				$service_downgrade_page	= smartwoo_service_front_temp();
			} else {
				$downgrader = new SmartWooPro();
				$service_downgrade_page	= $downgrader->downgrade_temp();
			}

			$output	= wp_kses( $service_downgrade_page, smartwoo_allowed_form_html() );
			break;

		case 'buy-new':

			$buy_new_service_page 	= smartwoo_buy_new_temp();
			$output 			  	= wp_kses_post( $buy_new_service_page  );
			break;

		case 'view-subscriptions-by':

			$active_services_page 	= smartwoo_user_service_by_status();
			$output 				= wp_kses_post( $active_services_page );
			break;

		default:
			$main_page 	= smartwoo_service_front_temp();
			$output 	= wp_kses_post( $main_page );
			break;
		
	}
	return $output;	
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
		$billingAddress   = smartwoo_get_user_billing_address( $user_id );
		// Construct the HTML for billing details
		$html  = '<div class="card">';
		$html .= '<h3>Billing Details</h3>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Name:</strong></span> ' . esc_html( $billingFirstName . ' ' . $billingLastName ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Company Name:</strong></span> ' . esc_html( $company_name ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Email Address:</strong></span> ' . esc_html( $email ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Phone:</strong></span> ' . esc_html( $phone ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Website:</strong></span> <a href="' . esc_url( $website ) . '">' . esc_html( $website ) . '</a></p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Address:</strong></span> <div>' . esc_html( $billingAddress ) . '</div></p>';
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
		$html  = '<div class="smartwoo-details-container">';
		$html .= '<h3>' . esc_html__( 'My Details', 'smart-woo-service-invoicing' ) . '</h3>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Full Name:</strong></span> ' . esc_html( $full_name ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Email:</strong></span> ' . esc_html( $email ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Bio:</strong></span> ' . esc_html( $bio ) . '</p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Website:</strong></span> <a href="' . esc_url( $user_url ) . '">' . esc_html( $user_url ) . '</a></p>';
		$html .= '<p class="smartwoo-container-item"><span><strong>Account Type:</strong></span> ' . esc_html( ucwords( $user_role ) ) . '</p>';
		$html .= '</div>';		
		$html .= '<button class="account-button" id="edit-account-button">' . esc_html__( 'Edit My Information', 'smart-woo-service-invoicing' ) . '</button>';
		$html .= '<button class="account-button" id="view-payment-button">' . esc_html__( 'Payment Methods', 'smart-woo-service-invoicing' ) . '</button>';
		$html .= '</div>';
		// Send the HTML response.
		echo wp_kses_post( $html );
	} else {
		// User is not logged in, handle accordingly.
		esc_html_e( 'User not logged in', 'smart-woo-service-invoicing' );
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

	if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
		wp_die();
	}

	if ( ! is_user_logged_in() ) {
		wp_die( -1, 401);
	}

	// Get the current user object
	$current_user 		= wp_get_current_user();
	$user_id      		= $current_user->ID;
	$current_login_time = smartwoo_get_current_login_date( $user_id );
	$last_active		= smartwoo_get_last_login_date( $user_id );
	$registration_date 	= smartwoo_check_and_format( $current_user->user_registered, true );
	$total_spent 		= smartwoo_client_total_spent( $user_id );
	$user_agent			= wc_get_user_agent();
	$html = '<div class="account-logs-container">';
	$html .= '<h3>' . esc_html__( 'Account Logs', 'smart-woo-service-invoicing' ) . '</h3>';
	$html .= '<ul class="account-logs-list">';
	$html .= '<li class="account-log-item">' . esc_html__( 'Total Amount Spent: ', 'smart-woo-service-invoicing' ) . wc_price( $total_spent ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	//$html .= '<li class="account-log-item">' . esc_html__( 'User Agent: ', 'smart-woo-service-invoicing' ) . esc_html( $user_agent ) . '</li>';
	$html .= '<li class="account-log-item">' . esc_html__( 'Current Login Time: ', 'smart-woo-service-invoicing' ) . esc_html( $current_login_time )  . '</li>';
	$html .= '<li class="account-log-item">' . esc_html__( 'Last logged In: ', 'smart-woo-service-invoicing' ) . esc_html( $last_active ) . '</li>';
	$html .= '<li class="account-log-item">' . esc_html__( 'Registration Date: ', 'smart-woo-service-invoicing' ) . esc_html( $registration_date ) . '</li>';

	/**
	 * Retrieve User's Personal logged information using WooCommerce geolocation feature.
	 */

	$ip_address 	  = WC_Geolocation::get_ip_address();
	$location_data    = WC_Geolocation::geolocate_ip( $ip_address );
	
	// Display IP Address.
	$html .= '<li class="account-log-item">IP Address: ' . esc_html( $ip_address ) . '</li>';

	if ( ! empty( $location_data ) ) {
		$user_location	= $location_data['country'];
		$html .= '<li class="account-log-item">' . esc_html__( 'Location: ', 'smart-woo-service-invoicing' ) . esc_html( $user_location ) . '</li>';
	} else {
		$html .= '<li class="account-log-item">Location: ' . esc_html__( 'Unknown', 'smart-woo-service-invoicing' ) . '</li>';
	}

	$html .= '</ul>';
	$html .= '</div>';

	echo wp_kses_post( $html );

	// prevent further outputing.
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
		$html .= smartwoo_transactions_shortcode();

		echo wp_kses_post( $html );
	} else {
		// User is not logged in
		echo esc_html__( 'Please log in to view transaction history.', 'smart-woo-service-invoicing' );
	}

	// prevent further outputing
	die();
}
