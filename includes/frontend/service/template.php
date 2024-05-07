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
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
    }

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$url_service_id 	= isset( $_GET['service_id'] ) ? sanitize_key( $_GET['service_id'] ) : '' ; 
	
	if ( empty( $url_service_id ) ) {
		return smartwoo_error_notice( 'Service ID parameter cannot be manupulated' );
	}

	$service	= SmartWoo_Service_Database::get_service_by_id( $url_service_id );
	$output		= smartwoo_get_navbar( 'Service Detail', smartwoo_service_page_url() );

	if ( $service && $service->getUserId() !== get_current_user_id()|| ! $service ) {
		return smartwoo_error_notice( 'Service Not Found', 'smart-woo-service-invoicing' );
	}

	$service_name 		= esc_html( $service->getServiceName() ? $service->getServiceName() : 'Not Available' );
	$service_id   		= esc_html( $service->getServiceId() ? $service->getServiceId() : 'Not Available' );
	$product_id   		= esc_html( $service->getProductId() );
	$service_type 		= esc_html( $service->getServicetype() ? $service->getServiceType() : 'Not Available' );
	$product_info  		= wc_get_product( $product_id );
	$product_name  		= $product_info ? $product_info->get_name() : 'Product Not Found';
	$product_price 		= $product_info ? $product_info->get_price() : 0;
	$billing_cycle     	= esc_html( $service->getBillingCycle() ? $service->getBillingCycle() : 'Not Available' );
	$start_date        	= smartwoo_check_and_format( $service->getStartDate(), true );
	$next_payment_date 	= smartwoo_check_and_format( $service->getNextPaymentDate() );
	$end_date          	= smartwoo_check_and_format( $service->getEndDate() );
	$service_button    	= smartwoo_client_service_url_button( $service );
	$status        	   	= smartwoo_service_status( $service_id );
	$usage_metrics 		= smartwoo_usage_metrics_temp( $service_id );
	$expiry_date   		= smartwoo_get_service_expiration_date( $service ); 
	$output 			.= '<div class="content">';
	// Add the status tag to the service name.
	$service_name_with_status = $service_name . ' (' . $status . ')';
	$output .= '<h3 style="text-align: center;">' . esc_html( $service_name_with_status ) . '</h3>';
	$output .= '<div class="inv-button-container" style="text-align: center;">';
	$output .= '<a href="' . esc_url( smartwoo_service_page_url() ) . '" class="back-button">Back to Services</a>';
	$renew_button_text = ( 'Due for Renewal' === $status || 'Grace Period' === $status ) ? 'Renew' : 'Reactivate';
	// "Renew" button when the service is due for renewal or expired.
	if ( 'Due for Renewal' === $status || 'Expired' === $status || 'Grace Period' === $status ) {
		// Add the nonce to the URL.
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

		// Output the "Renew" button with the nonce.
		$output .= '<a href="' . esc_url( $renew_link ) . '" class="renew-button">' . esc_html( $renew_button_text ) . '</a>';
	}
		// "Quick Action" button when the service status is 'Active'.
	if ( 'Active' === $status ) {

		$output .= '<a id="sw-service-quick-action" class="sw-blue-button"';
		$output .= ' data-service-name="' . esc_js( json_encode( $service_name ) ) . '"';
		$output .= ' data-service-id="' . esc_js( json_encode( $service_id ) ) . '"';
		$output .= '>' . esc_html__( 'Quick Action', 'smart-woo-service-invoicing' ) . '</a>';
	}

	if ( 'Active' === $status || 'Active (NR)' === $status || 'Grace Period' === $status ){
		$output .=  wp_kses_post( $service_button );
	}

	/** Filter button row */
	$buttons = apply_filters( 'smartwoo_service_details_button_row', array(), $service );

	foreach ( (array) $buttons as $button ) {
		$output .= $button;
	}
	$output .= '</div>';

	if ( $expiry_date === smartwoo_extract_only_date( current_time( 'mysql' ) ) ) {
		$output .= smartwoo_notice( 'Expiring Today' );
	} elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '+1 day' ) ) ) {
		$output .= smartwoo_notice( 'Expiring Tomorrow' );
	} elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '-1 day' ) ) ) {
		$output .= smartwoo_notice( 'Expired Yesterday' );
	}
	
	 
	$output .=  apply_filters( 'smartwoo_before_service_details_page', '', $service_id );
	$output .= '<div class="serv-details-card">';
	$output .= '<div id="swloader">Processing....</div>';
	$output .= '<p class="smartwoo-container-item"><span> Service ID:</span>' . esc_html( $service_id ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Service Type:</span>' . esc_html( $service_type ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Product Name:</span>' . esc_html( $product_name ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Amount:</span>' . esc_html( $product_price ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Billing Cycle:</span>' . esc_html( $billing_cycle ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Start Date:</span>' . esc_html( $start_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Next Payment Date:</span>' . esc_html( $next_payment_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> End Date:</span>' . esc_html( $end_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Expiry Date:</span>' . esc_html( smartwoo_check_and_format( $expiry_date, true ) ) . '</p>';
	/** Filter to add more details as associative array of title and value */
	$additional_details = apply_filters( 'smartwoo_more_service_details', array(), $service );
	
	foreach ( (array) $additional_details  as $title => $value ) {
		$output .= '<p class="smartwoo-container-item"><span> ' . $title . ':</span>' . esc_html( $value ) . '</p>';

	}
	$output .= '</div>';
	$output .=  apply_filters( 'smartwoo_after_service_details_page', '', $service_id );
	$output .= '</div>';

	return $output;
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
	$output 			   = smartwoo_get_navbar( 'My Services' );
	$output 			  .= '<div class="wrap">';
	$current_user 	       = wp_get_current_user();
	$full_name             = $current_user->first_name . ' '. $current_user->last_name  ;
	$user_id 			   = get_current_user_id();
	$active_count          = smartwoo_count_active_services( $user_id ) + smartwoo_count_nr_services( $user_id );
	$due_for_renewal_count = smartwoo_count_due_for_renewal_services( $user_id );
	$expired_count         = smartwoo_count_expired_services( $user_id );
	$grace_period_count    = smartwoo_count_grace_period_services( $user_id );
	
	$output .= '<p style="text-align: center; margin-top: 10px;">Welcome, ' . esc_html( $full_name ) . '!</p>';
	$output .= '<div class="status-counts">';
	$output .= '<p class="active-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Active',
			),
		get_permalink() . 'view-subscriptions-by/' ) ) . '">Active: ' . esc_html( $active_count ) . '</a></p>';
	
		$output .= '<p class="due-for-renewal-count"><a href="' . esc_url(
		add_query_arg( 
			array( 
				'status' => 'Due for Renewal', 
			),
		get_permalink() . 'view-subscriptions-by/' ) ) . '">Due: ' . esc_html( $due_for_renewal_count ) . '</a></p>';
	
	$output .= '<p class="expired-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Expired',
			),
		get_permalink() . 'view-subscriptions-by/' ) ) . '">Expired: ' . esc_html( $expired_count ) . '</a></p>';
	$output .= '<p class="grace-period-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'status' => 'Grace Period',
			),
		get_permalink() . 'view-subscriptions-by/') ) . '">Grace Period: ' . esc_html( $grace_period_count ) . '</a></p>';
	$output .= '</div>';

	// Service ID is not provided in the URL, display the list of services.
	$services         = SmartWoo_Service_Database::get_services_by_user( $user_id );
	$pending_services = smartwoo_user_processing_service( $user_id );

	// Output services as cards.
	$output .= '<div class="client-services">';

	if ( ! empty( $services || ! empty( $pending_services ) ) ) {

		$output .= $pending_services; 

		foreach ( $services as $service ) {
			$service_name 	= esc_html( $service->getServiceName() );
			$service_id   	= esc_html( $service->getServiceId() );
			$view_link		= smartwoo_service_preview_url( $service->getServiceId() );
			$status			= smartwoo_service_status( $service_id );
			$expiry_date 	= smartwoo_get_service_expiration_date( $service );

			// Add the status tag to the service name.
			$service_name_with_status = $service_name . ' (' . $status . ')';

			$output .= '<div class="main-page-card">';
			$output .= '<h3>' . esc_html( $service_name_with_status ) . '</h3>';
			if ( $expiry_date === smartwoo_extract_only_date( current_time( 'mysql' ) ) ) {
				$output .= smartwoo_notice( 'Expiring Today' );
			} elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '+1 day' ) ) ) {
				$output .= smartwoo_notice( 'Expiring Tomorrow' );
			} elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '-1 day' ) ) ) {
				$output .= smartwoo_notice( 'Expired Yesterday' );
			}
			$output .= '<p>Service ID: ' . esc_html( $service_id ) . '</p>';
			$output .= '<a href="' . esc_url( $view_link ) . '" class="view-details-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a>';
			$output .= '</div>';
		}
	} else {
		$output .= '<div class="main-page-card">';
		$output .= '<p style="color: black;">' . esc_html__( 'All your services will appear here.', 'smart-woo-service-invoicing' ) . '</p>';
		$output .= '</div>';
	}
	$output .= '</div>'; // Close the client-services div.
	$output .= '<div id="swloader">Loading...</div>';
	$output .= '<div class="settings-tools-section">';
	$output .= '<h2>Settings and Tools</h2>';
	$output .= '<div class="sw-button-container">';
	$output .= '<button class="minibox-button" id="sw-billing-details">Billing Details</button>';
	$output .= '<button class="minibox-button" id="sw-load-user-details">My Details</button>';
	$output .= '<button class="minibox-button" id="sw-account-log">Account Logs</button>';
	$output .= '<button class="minibox-button" id="sw-load-transaction-history">Transaction History</button>';
	$output .= '</div>';
	$output .= '<div id="ajax-content-container"></div>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}




/**
 * Get all pending Services for a user.
 *
 * @param int $user_id        The user's ID.
 *
 * @return string HTML markup containing the service name and status.
 */
function smartwoo_user_processing_service( $user_id ) {
	
	if ( ! is_user_logged_in() ) {
	   return;
    }

	if ( $user_id <= 0 ) {
		return $user_id;
	}

	$orders = wc_get_orders(
		array(
			'customer' => $user_id,
		)
	);

	// Initialize output variable.
	$output = '';

	foreach ( $orders as $order ) {
		$is_config_order = smartwoo_check_if_configured( $order );
		$order_status    = $order->get_status();

		if (  $is_config_order && 'processing' === $order_status ) {
			$items = $order->get_items();

			foreach ( $items as $item_id => $item ) {
				// Get the service name from order item meta.
				$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );

				// Break the loop once the service name is found.
				if ( $service_name ) {
					break;
				}
			}

			$order_id       = $order->get_id();
			$service_status = 'Pending';
			$service_name_with_status = $service_name . ' (' . $service_status . ')';
			$output .= '<div class="main-page-card">';
			$output .= '<h3>' . esc_html( $service_name_with_status ) . '</h3>';
			$output .= smartwoo_notice( 'We are currently processing this service. It will be active as soon as we are done processing it.' );
			$output .= '</div>';
		}
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

    $status_label = isset( $_GET['status'] ) ? sanitize_text_field( str_replace( array('/', '\\'), '', $_GET['status'] ) ) : 'active';
    $services = SmartWoo_Service_Database::get_services_by_user( get_current_user_id() );
    $output = smartwoo_get_navbar( 'My '. $status_label . ' Services', smartwoo_service_page_url() );

    if ( empty( $services ) ) {
        return esc_html__( 'You currently do not have any services.', 'smart-woo-service-invoicing' );
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
        $status = smartwoo_service_status( $service->getServiceId() );
        $view_link = smartwoo_service_preview_url( $service->getServiceId() );

        if ( ( 'Active (NR)' === $status && 'Active' === $status_label )  || $status === $status_label ) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html( $service->getServiceName() ) . '</td>';
            $output .= '<td>' . esc_html( $service->getServiceId() ) . '</td>';
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
			$service_id   = esc_html( $service->getServiceId() );

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

	// Get Smart Woo Products.
	$smartwoo_products = SmartWoo_Product::get_all_products();
	$output  = smartwoo_get_navbar( 'Buy New Service', get_permalink( wc_get_page_id( 'shop' ) ) );
	$output .= '<div class="wrap">';

	if ( empty( $smartwoo_products ) ) {
		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
		$output       .= '<div class="main-page-card">';
		$output       .= '<p>We do not have service products for purchase yet!</p>';
		$output       .= '<a href="' . esc_url( $shop_page_url ) . '" class="sw-blue-button">' . esc_html__( 'Shop Page', 'smart-woo-service-invoicing' ) . '</a>';
		$output       .= '<a href="' . esc_attr( get_permalink() ) . '" class="sw-blue-button">' . esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ) . '</a>';
	
		$output .= '</div>';
		return $output;
	}

	foreach ( $smartwoo_products as $product ) {
		$product_id      = $product->get_id();
		$product_name    = $product->get_name();
		$product_price   = $product->get_price();
		$sign_up_fee     = $product->get_sign_up_fee();
		$billing_cycle   = $product->get_billing_cycle();
		$product_excerpt = $product->get_short_description();

		$output .= '<div class="sw-product-container">';
		$output .= '<h3>' . esc_html( $product_name ) . '</h3>';
		$output .= '<p>Price: ' . wc_price( $product_price ) . '</p>';
		$output .= '<p>Sign-Up Fee: ' . $sign_up_fee . '</p>';
		$output .= '<p><strong>' . esc_html( $billing_cycle ) . '</strong> Billing Cycle</p>';
		$output .= '<p>' . wp_kses_post( wp_trim_words( $product_excerpt, 40 ) ) . '</p>';
		$output .= '<a href="' . esc_url( smartwoo_configure_page( $product_id ) ) . '" class="sw-blue-button" >' . esc_html__( 'Configure Product', 'smart-woo-service-invoicing' ) . '</a>';
		$output .= '<a href="' . esc_url( $product->get_permalink() ) . '" class="sw-blue-button" >' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a>';
		$output .= '</div>';
	}
	$output .= '</div>';
	return $output;

}
