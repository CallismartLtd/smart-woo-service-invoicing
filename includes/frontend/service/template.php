<?php
/**
 * File name Template.php
 *
 * View File for frontend service.
 * 
 * @author Callistus
 * @package SmartWoo
 * @version 1.0.2
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Handles service details page.
 *
 * @param int    $current_user_id    Current user ID.
 * @return string Error message or service details.
 */
function smartwoo_service_details() { 
	if ( ! is_user_logged_in() ) {
		return smartwoo_login_form( array( 'notice' => smartwoo_notice( 'You must be logged in to access this page.' ), 'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	   
    }

	$url_service_id 	= isset( $_GET['service_id'] ) ? sanitize_text_field( wp_unslash( $_GET['service_id'] ) ) : '' ; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
	if ( empty( $url_service_id ) ) {
		return smartwoo_error_notice( 'Service ID parameter should not be manupulated.' );
	}

	$service	= SmartWoo_Service_Database::get_service_by_id( $url_service_id );
	$output		= '';

	if ( ! $service || ( $service && $service->getUserId() !== get_current_user_id() ) ) {
		$output  = smartwoo_get_navbar( 'Service Detail', smartwoo_service_page_url() );
		$output .= smartwoo_error_notice( 'Service Not Found.', 'smart-woo-service-invoicing' );

		return $output;
	}
	
	smartwoo_set_document_title( $service->getServiceName() );
	$service_name 		= $service->getServiceName() ? $service->getServiceName() : 'Not Available';
	$service_id   		= $service->get_service_id() ? $service->get_service_id() : 'Not Available';
	$product_id   		= $service->getProductId();
	$service_type 		= $service->getServicetype() ? $service->getServiceType() : 'Not Available';
	$product_info  		= wc_get_product( $product_id );
	$product_name  		= $product_info ? $product_info->get_name() : 'Product Not Found';
	$product_price 		= $product_info ? $product_info->get_price() : 0;
	$GLOBALS['product'] = $product_info;
	$billing_cycle     	= $service->getBillingCycle() ? $service->getBillingCycle() : 'Not Available';
	$start_date        	= smartwoo_check_and_format( $service->getStartDate(), true );
	$next_payment_date 	= smartwoo_check_and_format( $service->getNextPaymentDate() );
	$end_date          	= smartwoo_check_and_format( $service->getEndDate() );
	$service_button    	= smartwoo_client_service_url_button( $service );
	$status        	   	= smartwoo_service_status( $service_id );
	$expiry_date   		= smartwoo_get_service_expiration_date( $service );
	
	// Add the status tag to the service name.
	$service_name_with_status = $service_name . ' (' . $status . ')';
	$renew_link = esc_url(
		wp_nonce_url(
			add_query_arg(
				array(
					'service_id' => $service_id,
					'action'     => 'renew-service',
				),
				get_permalink()
			),
			'renew_service_nonce',
			'renew_nonce'
		)
	);
	$renew_button_text = ( 'Due for Renewal' === $status || 'Grace Period' === $status ) ? 'Renew' : 'Reactivate';

	/** 
	 * Add more buttons to the row
	 * 
	 * @param array Associative array of item => value
	 * @param SnartWoo_Service
	 */
	$buttons	= apply_filters( 'smartwoo_service_details_button_row', array(), $service );
	/**
	 * Add additional service information to the container
	 * 
	 * @param array Associative array of item => value
	 * @param SnartWoo_Service
	 */
	$additional_details = apply_filters( 'smartwoo_more_service_details', array(), $service );
	
	ob_start();
	include SMARTWOO_PATH . 'templates/frontend/subscriptions/view-subscription.php';
	return ob_get_clean();
}


/**
 * Handles the main service page.
 *
 * @return string $output The content.
 */
function smartwoo_service_front_temp() {
	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
    }
	$output 			   = '<div class="smartwoo-page">';
	$output 			  .= smartwoo_get_navbar( 'My Services' );
	$current_user 	       = wp_get_current_user();
	$full_name             = $current_user->first_name . ' '. $current_user->last_name  ;
	$user_id 			   = get_current_user_id();
	$active_count          = smartwoo_count_active_services() + smartwoo_count_nr_services();
	$due_for_renewal_count = smartwoo_count_due_for_renewal_services();
	$expired_count         = smartwoo_count_expired_services();
	$grace_period_count    = smartwoo_count_grace_period_services();
	
	$output .= '<p style="text-align: center; margin-top: 10px;">Welcome, ' . esc_html( $full_name ) . '!</p>';
	$output .= '<div class="status-counts">';
	$output .= '<p class="active-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Active',
			),
		get_permalink() . 'sort/' ) ) . '">Active: ' . esc_html( $active_count ) . '</a></p>';
	
		$output .= '<p class="due-for-renewal-count"><a href="' . esc_url(
		add_query_arg( 
			array( 
				'status' => 'Due for Renewal', 
			),
		get_permalink() . 'sort/' ) ) . '">Due: ' . esc_html( $due_for_renewal_count ) . '</a></p>';
	
	$output .= '<p class="expired-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Expired',
			),
		get_permalink() . 'sort/' ) ) . '">Expired: ' . esc_html( $expired_count ) . '</a></p>';
	$output .= '<p class="grace-period-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Grace Period',
			),
		get_permalink() . 'sort/' ) ) . '">Grace Period: ' . esc_html( $grace_period_count ) . '</a></p>';
	$output .= '</div>';

	// Service ID is not provided in the URL, display the list of services.
	$services         = SmartWoo_Service_Database::get_services_by_user( $user_id );
	$pending_services = smartwoo_get_user_pending_services();

	// Output services as cards.
	$output .= '<div class="client-services">';

	if ( ! empty( $services || ! empty( $pending_services ) ) ) {

		$output .= $pending_services; 

		foreach ( $services as $service ) {
			$service_name 	= esc_html( $service->getServiceName() );
			$service_id   	= esc_html( $service->get_service_id() );
			$view_link		= smartwoo_service_preview_url( $service->get_service_id() );
			$status			= smartwoo_service_status( $service_id );

			// Add the status tag to the service name.
			$service_name_with_status = $service_name . ' (' . $status . ')';

			$output .= '<div class="main-page-card">';
			$output .= '<h3>' . esc_html( $service_name_with_status ) . '</h3>';
			$output .= $service->print_expiry_notice();
			$output .= '<p>Service ID: ' . esc_html( $service_id ) . '</p>';
			$output .= '<a href="' . esc_url( $view_link ) . '" class="view-details-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a>';
			$output .= '</div>';
		}
	} else {
		$buy_product_page = smartwoo_service_page_url() . 'buy-new/';
		$output			 .= '<div class="main-page-card">';
		$output			 .= '<p>No service found.</p>';
		$output			 .= '<a href="' . esc_url( $buy_product_page ) . '" class="sw-blue-button">' . esc_html__( 'Buy New Service', 'smart-woo-service-invoicing' ) . '</a>';
		$output			 .= '</div>';
	}
	
	$output .= '</div>'; // Close the client-services div.
	$output .= '<div id="swloader">Just a moment</div>';
	$output .= '<div class="settings-tools-section">';
	$output .= '<h2>Settings and Tools</h2>';
	$output .= '<div class="sw-button-container">';
	$output .= '<a class="sw-blue-button" id="sw-billing-details">Billing Details</a>';
	$output .= '<a class="sw-blue-button" id="sw-load-user-details">My Details</a>';
	$output .= '<a class="sw-blue-button" id="sw-account-log">Account Logs</a>';
	$output .= '<a class="sw-blue-button" id="sw-load-transaction-history">Transaction History</a>';
	$output .= '</div>';
	$output .= '<div id="ajax-content-container"></div>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}




/**
 * Get all pending services for the current user.
 *
 * @return string HTML markup containing the service name and status.
 */
function smartwoo_get_user_pending_services() {
	
	if ( ! is_user_logged_in() ) {
	   return '';
    }

	$smartwoo_orders = SmartWoo_Order::get_user_orders( array( 'status' => 'processing' ) );
	// Initialize output variable.
	$output = '';

	foreach ( $smartwoo_orders as $order ) {
		if ( 'awaiting processing' !== $order->get_status() ) {
			continue;
		}

		$service_status = 'Pending';
		$service_name_with_status = $order->get_service_name() . ' (' . $service_status . ')';
		$output .= '<div class="main-page-card">';
		$output .= '<h3>' . esc_html( $service_name_with_status ) . '</h3>';
		$output .= smartwoo_notice( 'We are currently processing this service. It will be active as soon as the process is complete.' );
		$output .= '</div>';
		
	}

	return $output;
}

/**
 * Render services filtered by status.
 *
 * @param int    $current_user_id The user associated with the service.
 * @param string $status_label     The label of the status to filter services.
 * @return string                 The HTML output of the rendered services.
 */
function smartwoo_user_service_by_status() {

	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
    }

    $status_label = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'active'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $services = SmartWoo_Service_Database::get_services_by_user( get_current_user_id() );
    $output = smartwoo_get_navbar( 'My '. $status_label . ' Services', smartwoo_service_page_url() );

    if ( empty( $services ) ) {
		$buy_product_page = smartwoo_service_page_url() . 'buy-new/';
		$output       .= '<div class="wrap">';
		$output       .= '<div class="main-page-card">';
		$output       .= '<p>Oh! You currently do not have any service</p>';
		$output       .= '<a href="' . esc_url( $buy_product_page ) . '" class="sw-blue-button">' . esc_html__( 'Buy New Service', 'smart-woo-service-invoicing' ) . '</a>';
		$output       .= '<a href="' . esc_attr( get_permalink() ) . '" class="sw-blue-button">' . esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ) . '</a>';
		$output .= '</div>';
		$output .= '</div>';
		return $output;
	}

    $output .= '<div class="sw-table-wrapper">';
    $output .= '<h2>' . esc_html( $status_label ) . '</h2>';
    $output .= '<table class="sw-table">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th>Service Name</th>';
    $output .= '<th>Service ID</th>';
    $output .= '<th>Billing Cycle</th>';
    $output .= '<th>End Date</th>';
    $output .= '<th>Action</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $found_services = false;
	$count			= 0;

    foreach ( $services as $service ) {
        $status = smartwoo_service_status( $service );
        $view_link = smartwoo_service_preview_url( $service->get_service_id() );

        if ( ( 'Active (NR)' === $status && 'Active' === $status_label )  || $status === $status_label ) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html( $service->getServiceName() ) . '</td>';
            $output .= '<td>' . esc_html( $service->get_service_id() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getBillingCycle() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getEndDate() ) . '</td>';
            $output .= '<td><a href="' . esc_url( $view_link ) . '" class="sw-blue-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a></td>';
            $output .= '</tr>';
            $found_services = true;
			$count++;
        }
    }

    if ( ! $found_services ) {
        $output .= '<tr><td colspan="5" style="text-align: center;">No ' . esc_html( $status_label ) . ' services found.</td></tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    $output .= '<p class="sw-table-count">' . esc_html(  $count  ) . ' items</p>';

    return $output;
}

/**
 * Function Code For Service Mini Card.
 */
function smartwoo_service_mini_card() {
	if ( ! is_user_logged_in() ) {
		return 'Hello! It looks like you\'re not logged in.';
	}
	$current_user_id  = get_current_user_id();
	$services         = SmartWoo_Service_Database::get_services_by_user( $current_user_id );
	$output           = '<div class="mini-card">';
	$output          .= '<h2>My Services</h2>';

	if ( empty( $services ) ) {
		// Display a message if no services are found.
		$output .= '<p>All Services will appear here.</p>';
	} else {
		foreach ( $services as $service ) {
			$service_name = esc_html( $service->getServiceName() );
			$service_id   = esc_html( $service->get_service_id() );

			// Create a link to the client_services page with the service_id as a URL parameter.
			$service_link = smartwoo_service_preview_url( $service_id );
			$status       = smartwoo_service_status( $service_id );

			// Add each service name, linked row, and status with a horizontal line.
			$output .= '<p><a href="' . esc_url( $service_link ) . '">' . esc_html( $service_name ) . '</a>  ' . esc_html( $status ) . '</p>';
			$output .= '<hr>';
		}
	}

	$output .= '</div>';
	return $output;
}

/**
 * Render the count for active Service, usefull if you want to
 *  just show active service count for the logged user.
 *
 * @return int $output incremented number of active service(s) or 0 if there is none
 */
function smartwoo_active_service_count_shortcode() {
	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
    }
	
	$current_user = wp_get_current_user();
	$user_id      = $current_user->ID;
	$count = smartwoo_count_active_services( $user_id ) + smartwoo_count_nr_services( $user_id );

	// Output the count and "Services" text with inline CSS for centering.
	$output  = '<div style="text-align: center;">';
	$output .= '<h1 class="centered" style="text-align: center; margin: 0 auto; font-size: 45px;">' . esc_html( $count ) . '</h1>';
	$output .= '<p class="centered" style="text-align: center; font-size: 18px;">' . esc_html( 'Services', 'smart-woo-service-invoicing' ) . '</p>';
	$output .= '</div>';

	return $output;

}



/**
 * Handle new service product pruchase
 */
function smartwoo_buy_new_temp() {
	smartwoo_set_document_title( apply_filters( 'smartwoo_buy_new_product_page_title', 'Buy New' ) );
	// Get Smart Woo Products.
	$smartwoo_products = SmartWoo_Product::get_all();
	$output		= '<div class="smartwoo-page">';
	$output	   .= smartwoo_get_navbar( 'Buy New Service', get_permalink( wc_get_page_id( 'shop' ) ) );

	if ( empty( $smartwoo_products ) ) {
		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
		$output       .= '<div class="main-page-card">';
		$output       .= '<p>We do not have service products for purchase yet!</p>';
		$output       .= '<a href="' . esc_url( $shop_page_url ) . '" class="sw-blue-button">' . esc_html__( 'Shop Page', 'smart-woo-service-invoicing' ) . '</a>';
		$output       .= '<a href="' . esc_attr( get_permalink() ) . '" class="sw-blue-button">' . esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ) . '</a>';
	
		$output .= '</div>';
		return $output;
	}

	$output .= '<div class="sw-products-container">';

	foreach ( $smartwoo_products as $product ) {
		$GLOBALS['product'] = $product;
		$product_id			= $product->get_id();
		$product_name    	= $product->get_name();
		$product_price   	= $product->get_price();
		$sign_up_fee     	= $product->get_sign_up_fee();
		$billing_cycle   	= $product->get_billing_cycle();
		$product_excerpt 	= $product->get_short_description();

		$output .= '<div class="sw-product-container">';
		$output .= '<h3>' . esc_html( $product_name ) . '</h3>';
		$output .= '<p>Price: ' . smartwoo_price( $product_price ) . '</p>';
		$output .= '<p>Sign-Up Fee: ' . smartwoo_price( $sign_up_fee ) . '</p>';
		$output .= '<p><strong>' . esc_html( $billing_cycle ) . '</strong> Billing Cycle</p>';
		$output .= '<div class="sw-description">';
		$output .= 	wp_kses_post( $product_excerpt );
		$output .= '</div>';
		$output .= '<a href="' . esc_url( smartwoo_configure_page( $product_id ) ) . '" class="sw-blue-button product_type_'. esc_attr( $product->get_type() ) .' add_to_cart_button" data-product_id="' . esc_attr( $product->get_id() ) .'" data-product_name="' . $product->get_name() .'">' . esc_html__( 'Configure Product', 'smart-woo-service-invoicing' ) . '</a>';
		$output .= '<a href="' . esc_url( $product->get_permalink() ) . '" class="sw-blue-button" >' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a>';
		$output .= '</div>';
	}
	$output .= '</div>';
	$output .= '</div>';
	return $output;

}
