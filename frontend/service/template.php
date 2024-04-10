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
function smartwoo_service_details( $current_user_id ) { 

	$url_service_id 	= isset( $_GET['service_id'] ) ? sanitize_key( $_GET['service_id'] ) : "" ; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$service 			= Sw_Service_Database::get_service_by_id( $url_service_id );

	if ( $service && $service->getUserId() !== $current_user_id || ! $service ) {
		return esc_html__( 'Service Not Found', 'smart-woo-service-invoicing' );
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
	$service_url       	= esc_url( $service->getServiceUrl() ? $service->getServiceUrl() : 'Not Available' );
	$service_button    	= smartwoo_client_service_url_button( $service );
	$status        	   	= sw_service_status( $service_id );
	$usage_metrics 		= sw_get_usage_metrics( $service_id );
	$expiry_date   		= sw_get_service_expiration_date( $service );
	$output 			= smartwoo_get_navbar( $current_user_id );
	$output 			.= '<div class="content">';
	// Add the status tag to the service name.
	$service_name_with_status = $service_name . ' (' . $status . ')';
	$output .= '<h3 style="text-align: center;">' . esc_html( $service_name_with_status ) . '</h3>';
	$output .= '<div class="inv-button-container" style="text-align: center;">';
	$output .= '<a href="' . esc_url( get_permalink() ) . '" class="back-button">Back to Services</a>';
	$renew_button_text = ( 'Due for Renewal' === $status || 'Grace Period' === $status ) ? 'Renew' : 'Reactivate';
	// "Renew" button when the service is due for renewal or expired.
	if ( 'Due for Renewal' === $status || 'Expired' === $status || 'Grace Period' === $status ) {
		// Generate a nonce for the renew action.
		$renew_nonce = wp_create_nonce( 'renew_service_nonce' );

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
		$output .= '<a href="' . esc_url( $renew_link ) . '" class="renew-button">' . esc_html__( $renew_button_text, 'smart-woo-service-invoicing' ) . '</a>';
	}
		// "Quick Action" button when the service status is 'Active'.
	if ( 'Active' === $status ) {

		$output .= '<a href="#" id="sw-service-quick-action" class="sw-red-button" data-service-name="' . esc_attr( $service_name ) . '">' . esc_html__( 'Quick Action', 'smart-woo-service-invoicing' ) . '</a>';
	}

	if ( 'Active' === $status || 'Active (NR)' === $status || 'Grace Period' === $status ):
		$output .=  wp_kses_post( $service_button );
	endif;
	$output .= '</div>';
	$output .=  wp_kses_post( $usage_metrics ) ;
	$output .= '<div class="serv-details-card">';
	$output .= '<p class="smartwoo-container-item"><span> Service Name:</span>' . esc_html( $service_id ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Service Type:</span>' . esc_html( $service_type ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Product Name:</span>' . esc_html( $product_name ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Amount:</span>' . esc_html( $product_price ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Billing Cycle:</span>' . esc_html( $billing_cycle ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Start Date:</span>' . esc_html( $start_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Next Payment Date:</span>' . esc_html( $next_payment_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> End Date:</span>' . esc_html( $end_date ) . '</p>';
	$output .= '<p class="smartwoo-container-item"><span> Expiry Date:</span>' . esc_html( smartwoo_check_and_format( $expiry_date, true ) ) . '</p>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}


/**
 * Handles the main service page.
 *
 * @param int $current_user_id The current user's id.
 * @return string $output The content.
 */
function smartwoo_service_front_temp( $current_user_id ) {
	$output 			   = smartwoo_get_navbar( $current_user_id );
	$output 			  .= '<div class="wrap">';
	$current_user 	       = wp_get_current_user();
	$full_name             = esc_html( $current_user->display_name );
	$user_id 			   = get_current_user_id();
	$active_count          = count_active_services( $user_id );
	$due_for_renewal_count = count_due_for_renewal_services( $user_id );
	$expired_count         = count_expired_services( $user_id );
	$grace_period_count    = count_grace_period_services( $user_id );
	// Get and sanitize the 'service_page' parameter.
	$url_param = isset( $_GET['service_page'] ) ? sanitize_key( $_GET['service_page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_page_url = esc_url( add_query_arg( 'service_page', $url_param, get_permalink() ) );
	// Output the full name of the current user.
	$output .= '<p style="text-align: center; margin-top: 10px;">Welcome, ' . esc_html( $full_name ) . '!</p>';
	$output .= '<div class="status-counts">';
	$output .= '<p class="active-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'service_page' => 'active',
				'action'       => 'active',
			),
			get_permalink()
		)
	) . '">Active: ' . esc_html( $active_count ) . '</a></p>';
	$output .= '<p class="due-for-renewal-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'service_page' => 'renewal_due',
				'action'       => 'renewal_due',
			),
			get_permalink()
		)
	) . '">Due: ' . esc_html( $due_for_renewal_count ) . '</a></p>';
	$output .= '<p class="expired-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'service_page' => 'expired',
				'action'       => 'expired',
			),
			get_permalink()
		)
	) . '">Expired: ' . esc_html( $expired_count ) . '</a></p>';
	$output .= '<p class="grace-period-count"><a href="' . esc_url(
		add_query_arg(
			array(
				'service_page' => 'grace_period',
				'action'       => 'grace_period',
			),
			get_permalink()
		)
	) . '">Grace Period: ' . esc_html( $grace_period_count ) . '</a></p>';
	$output .= '</div>';

	// Service ID is not provided in the URL, display the list of services.
	$services         = Sw_Service_Database::get_services_by_user( $current_user_id );
	$pending_services = smartwoo_user_processing_service( $current_user_id );

	// Output services as cards.
	$output .= '<div class="client-services">';

	if ( ! empty( $services || ! empty( $pending_services ) ) ) {

		$output .= $pending_services;

		foreach ( $services as $service ) {
			$service_name = esc_html( $service->getServiceName() );
			$service_id   = esc_html( $service->getServiceId() );

			// Create a link to view service details with the service_id as a URL parameter.
			$service_page_id = get_option( 'smartwoo_service_page_id', 0 );
			$page_url        = get_permalink( $service_page_id );
			$view_link       = esc_url(
				add_query_arg(
					array(
						'service_page' => 'service_details',
						'service_id'   => $service_id,
					),
					$page_url
				)
			);
			// Use sw_service_status to get the service status.
			$status      = sw_service_status( $service_id );
			$expiry_date = sw_get_service_expiration_date( $service );

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
function smartwoo_user_service_by_status( $current_user_id, $status_label = "" ) {
    // Get all services for the current user.
    $services	= Sw_Service_Database::get_services_by_user( $current_user_id );
    $output		= smartwoo_get_navbar( $current_user_id );

    if ( empty( $services ) ) {
        return esc_html__( 'You currently do not have any service', 'smart-woo-service-invoicing' );
    }

    // Display services in a table.
    $output	.= '<div class="sw-table-wrapper">';
    $output	.= '<h2>' . esc_html( $status_label ) . '</h2>';
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

    // Create a link to view service details with the service_id as a URL parameter.
    $service_page_id = get_option( 'smartwoo_service_page_id', 0 );
    $page_url        = get_permalink( $service_page_id );

    foreach ( $services as $service ) {

        $status = sw_service_status( $service->getServiceId() );
        // Check if the service status matches the specified label.
        if ( $status !== $status_label ) {
            // If not, display a message and return early.
            $output .= '<tr><td colspan="5" style="text-align: center;">' . esc_html__( 'No ' . $status_label . ' services found.', 'smart-woo-service-invoicing' ) . '</td></tr>';
            $output .= '</tbody></table></div>';
            $output .= '<p class="sw-table-count">' . esc_html( count( $services ) ) . ' items</p>';
            return $output;
        }

        // Render service details.
        $view_link = esc_url(
            add_query_arg(
                array(
                    'service_page' => 'service_details',
                    'service_id'   => $service->getServiceId(),
                ),
                $page_url
            )
        );

        $output .= '<tr>';
        $output .= '<td>' . esc_html( $service->getServiceName() ) . '</td>';
        $output .= '<td>' . esc_html( $service->getServiceId() ) . '</td>';
        $output .= '<td>' . esc_html( $service->getBillingCycle() ) . '</td>';
        $output .= '<td>' . esc_html( $service->getEndDate() ) . '</td>';
        $output .= '<td><a href="' . esc_url( $view_link ) . '" class="sw-blue-button">' . esc_html__( 'View Details', 'smart-woo-service-invoicing' ) . '</a></td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';
    $output .= '</div>';
    $output .= '<p class="sw-table-count">' . esc_html( count( $services ) ) . ' items</p>';

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
	$services         = Sw_Service_Database::get_services_by_user( $current_user_id );
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
			$status       = sw_service_status( $service_id );

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
	// Check if the user is logged in.
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$user_id      = $current_user->ID;
		$count = count_active_services( $user_id );

		// Output the count and "Services" text with inline CSS for centering.
		$output  = '<div style="text-align: center;">';
		$output .= '<h1 class="centered" style="text-align: center; margin: 0 auto; font-size: 45px;">' . esc_html( $count ) . '</h1>';
		$output .= '<p class="centered" style="text-align: center; font-size: 18px;">' . esc_html( 'Services', 'smart-woo-service-invoicing' ) . '</p>';
		$output .= '</div>';

		return $output;
	} else {
		return 0;
	}
}



/**
 * Handles the 'service_upgrade' action.
 *
 * @param int $current_user_id Current user ID.
 * @return string Output or result of the upgrade service operation.
 */
function smartwoo_upgrade_temp( $current_user_id ) {
	$services = Sw_Service_Database::get_services_by_user( $current_user_id );

	if ( isset( $_POST['upgrade_service_submit'] ) ) {
		if ( ! isset( $_POST['upgrade_service_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['upgrade_service_nonce'] ) ), 'upgrade_service_nonce' ) ) {
			return esc_html__( 'Nonce verification failed.', 'smart-woo-service-invoicing' );
		}

		$selected_service_id = sanitize_text_field( $_POST['selected_service'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected_product_id = absint( $_POST['selected_product'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Find the selected service in the user's services.
		$service_to_upgrade = null;

		foreach ( $services as $service ) {
			if ( $service->getServiceId() === $selected_service_id ) {
				$service_to_upgrade = $service;
				break;
			}
		}

		if ( ! $service_to_upgrade ) {
			return esc_html__( 'Selected service not found.', 'smart-woo-service-invoicing' );
		}

		// Check the status of the service using the sw_service_status function.
		$service_status = sw_service_status( $service_to_upgrade->getServiceId() );

		// Check if the service is 'Due for Renewal'.
		if ( 'Active' !== $service_status ) {
			return smartwoo_error_notice( 'Only Active Services can be Upgraded, Contact us if you need further assistance.' );
		}

		$selected_product = wc_get_product( $selected_product_id );

		if ( ! $selected_product || ! $selected_product->is_purchasable() ) {
			return smartwoo_error_notice( 'Selected product not found or not purchasable.' );
		}

		$service_price        	= sw_get_service_price( $service_to_upgrade );
		$product_price        	= $selected_product->get_price();
		$service_product_name 	= wc_get_product( $service_to_upgrade->getProductId() )->get_name();
		$fee               		= floatval( get_sw_service_product( $selected_product_id )['sign_up_fee'] ?? 0 );
		$new_service_price 		= $product_price + $fee;
		$prorate_status 		= smartwoo_is_prorate_();
		$usage_metrics 			= sw_check_service_usage( $selected_service_id );
		$order_total_data 		= sw_calculate_migration_order_total( $new_service_price, $usage_metrics['unused_amount'] );
		$output					= smartwoo_get_navbar( $current_user_id );
		$output 				.= '<div class="migration-order-container">';
		$existing_invoice_id 	= smartwoo_evaluate_service_invoices( $service_to_upgrade->getServiceId(), 'Service Upgrade Invoice', 'unpaid' );
		$output             	.= '<div class="migrate-order-details">';
		$output             	.= '<p class="upgrade-section-title">' . esc_html__( 'Service Upgrade Order', 'smart-woo-service-invoicing' ) . '</p>';
		
		if ( $existing_invoice_id ) {
			$output 			.= smartwoo_notice( 'This service has an outstanding invoice. If you proceed, you will be redirected to make the payment instead.' );
		}

		$output 	.= '<p><strong>' . esc_html__( 'Current service Details', 'smart-woo-service-invoicing' ) . '</strong></p>';
		$output 	.= '<p><strong>Current Service:</strong> ' . esc_html( $service_to_upgrade->getServiceName() ) . ' - ' . esc_html( $service_to_upgrade->getServiceId() ) . '</p>';
		$output 	.= '<p><strong>Product Name:</strong> ' . esc_html( $service_product_name ) . '</p>';
		$output 	.= '<p><strong>Pricing:</strong> ' . wc_price( $service_price ) . '</p>';
		
		if ( 'Enabled' === $prorate_status ) {
			$output .= '<p><strong>Amount Used:</strong> ' . wc_price( $usage_metrics['used_amount'] ) . '</p>';
			$output .= '<p><strong> Balance:</strong> ' . wc_price( $usage_metrics['unused_amount'] ) . '</p>';
		}
		$output 	.= '<p><strong>New Upgrade Details</strong></p>';
		$output 	.= '<p><strong>Product:</strong> ' . esc_html( $selected_product->get_name() ) . '</p>';
		$output 	.= '<p><strong>Pricing:</strong> ' . wc_price( $product_price ) . '</p>';
		$output 	.= '<p><strong>Sign-up Fee:</strong> ' . wc_price( get_sw_service_product( $selected_product_id )['sign_up_fee'] ) . '</p>';
		$output 	.= '<p class="migrate-summary-tittle"><strong>' . esc_html( 'Summary:' ) . '</strong></p>';
		
		if ( 'Enabled' === $prorate_status ) {
			$output .= '<p><strong>Refund Amount:</strong> ' . wc_price( $order_total_data['remaining_unused_balance'] ) . '</p>';
		}

		$output .= '<p><strong>New Order Total:</strong> ' . wc_price( $order_total_data['order_total'] ) . '</p>';
		// Hidden form to Post migration data.
		$output .= '<form method="post" action="">';
		$output .= wp_nonce_field( 'migration_nonce', 'migration_nonce' );
		$output .= '<input type="hidden" name="user_id" value="' . esc_attr( $current_user_id ) . '">';
		$output .= '<input type="hidden" name="service_id" value="' . esc_attr( $service_to_upgrade->getServiceId() ) . '">';
		$output .= '<input type="hidden" name="new_service_product_id" value="' . esc_attr( $selected_product_id ) . '">';
		$output .= '<input type="hidden" name="amount" value="' . esc_attr( $product_price ) . '">';
		$output .= '<input type="hidden" name="fee" value="' . esc_attr( $fee ) . '">';
		$output .= '<input type="hidden" name="order_total" value="' . esc_attr( $order_total_data['order_total'] ) . '">';
		$output .= '<input type="hidden" name="refund_amount" value="' . esc_attr( $order_total_data['remaining_unused_balance'] ) . '">';
		$output .= '<div class="upgrade-button-container">';
		$output .= '<button type="submit" name="proceed_with_upgrade">' . esc_html__( 'Upgrade Now', 'smart-woo-service-invoicing' ) . '</button>';
		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	$output = smartwoo_get_navbar( $current_user_id );
	if ( smartwoo_is_migration() ) {

		if ( empty( $services ) ) {
			return smartwoo_error_notice( 'No services found for upgrade.' );
		}

		$output .= '<form method="post" action="">';
		$output .= wp_nonce_field( 'upgrade_service_nonce', 'upgrade_service_nonce', true, false );
		// Create a select input with the user's services.
		$select_options  = '<select name="selected_service" required>';
		$select_options .= '<option value="" selected disabled>' . esc_html__( 'Select a Service', 'smart-woo-service-invoicing' ) . '</option>';

		foreach ( $services as $service ) {
			$select_options .= '<option value="' . esc_attr( $service->getServiceId() ) . '">' . esc_html( $service->getServiceName() ) . '</option>';
		}

		$select_options .= '</select>';
		// Container for select service.
		$output .= '<div class="select-service-container">';
		$output .= '<label for="selected_service">' . esc_html__( 'Choose a service to upgrade:', 'smart-woo-service-invoicing' ) .'</label>';
		$output .= $select_options;
		$output .= '</div>';

		// Container for products to use.
		$output .= '<div class="products-to-use-container">';

		// Retrieve selected product categories from options.
		$selected_upgrade_category = get_option( 'smartwoo_upgrade_product_cat', '0' );

		// Get products from selected categories.
		$products_args  = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $selected_upgrade_category,
				),
			),
		);
		$products_query = new WP_Query( $products_args );

		if ( $products_query->have_posts() ) {
			while ( $products_query->have_posts() ) {
				$products_query->the_post();
				$product_id      = get_the_ID();
				$product_name    = get_the_title();
				$product_price   = get_post_meta( $product_id, '_regular_price', true );
				$product_excerpt = get_post_field( 'post_excerpt', $product_id );
				$output .= '<div class="product-container">';
				$output .= '<label>';
				$output .= '<input type="checkbox" name="selected_product" value="' . esc_attr( $product_id ) . '" required>';
				$output .= '<strong>' . esc_html( $product_name ) . '</strong><br>';
				$output .= 'Price: ' . wc_price( $product_price ) . '<br>';
				$output .= 'Description: ' . esc_html( $product_excerpt );
				$output .= '</label>';
				$output .= '</div>';
			}

			wp_reset_postdata(); // Reset the post data to the main loop.
			$output .= '</div>';
			$output .= '<div class="upgrade-button-container">';
			$output .= '<button type="submit" name="upgrade_service_submit">' . esc_html__( 'Upgrade', 'smart-woo-service-invoicing' ) . '</button>';
			$output .= '</div>';

		} else {
			$output .= 'No products found.';
		}

		$output .= '</form>';

		return $output;
	} else {
		// Display a message or take appropriate action when update is not allowed.
		smartwoo_error_notice( 'Service upgrade is not allowed. Contact us if you need any assistance' );
	}
}


/**
 * Handles the 'service_downgrade' action.
 *
 * @param int $current_user_id Current user ID.
 * @return string Output or result of the downgrade service operation.
 */
function smartwoo_downgrade_temp( $current_user_id ) {
	$services = Sw_Service_Database::get_services_by_user( $current_user_id );
	$output = "";
	if ( isset( $_POST['downgrade_service_submit'] ) ) {
		if ( ! isset( $_POST['downgrade_service_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['downgrade_service_nonce'] ) ), 'downgrade_service_nonce' ) ) {
			return 'Nonce verification failed.';
		}

		$selected_service_id = sanitize_text_field( $_POST['selected_service'] );
		$selected_product_id = absint( $_POST['selected_downgrade_product'] );
		$service_to_downgrade = null;

		foreach ( $services as $service ) {
			if ( $service->getServiceId() === $selected_service_id ) {
				$service_to_downgrade = $service;
				break;
			}
		}

		if ( ! $service_to_downgrade ) {
			return esc_html__( 'Selected service not found.', 'smart-woo-service-invoicing' );
		}

		$service_status = sw_service_status( $service_to_downgrade->getServiceId() );

		if ( 'Active' === $service_status ) {
			return esc_html__( 'Only Active Services can be downgraded, Contact us if you need further assistance.', 'smart-woo-service-invoicing' );
		}

		$selected_product = wc_get_product( $selected_product_id );

		// Check if the selected product exists.
		if ( ! $selected_product || ! $selected_product->is_purchasable() ) {
			return 'Selected product not found or not purchasable.';
		}

		// Get the prices for the selected service and product.
		$service_price			= sw_get_service_price( $service_to_downgrade );
		$service_product_name 	= wc_get_product( $service_to_downgrade->getProductId() )->get_name();
		$product_price        	= $selected_product->get_price();
		$fee					= floatval( get_sw_service_product( $selected_product_id )['sign_up_fee'] ?? 0 );
		$new_service_price    	= $product_price + $fee;
		$prorate_status 		= smartwoo_is_prorate_();
		$usage_metrics 			= sw_check_service_usage( $selected_service_id );
		$order_total_data 		= sw_calculate_migration_order_total( $new_service_price, $usage_metrics['unused_amount'] );
		$output					.= smartwoo_get_navbar( $current_user_id );
		$output 				.= '<div class="migration-order-container">';
		$existing_invoice_id 	= smartwoo_evaluate_service_invoices( $service_to_downgrade->getServiceId(), 'Service Downgrade Invoice', 'unpaid' );
		$output             	.= '<div class="migrate-order-details">';
		$output             	.= '<p class="upgrade-section-title">' . esc_html__( 'Service Downgrade Order', 'smart-woo-service-invoicing' ) . '</p>';
		
		if ( $existing_invoice_id ) {
			$output 			.= smartwoo_notice( 'This service has an outstanding invoice. If you proceed, you will be redirected to make the payment instead.' );
		}

		$output	.= '<p><strong>Current Service Details</strong></p>';
		$output .= '<p><strong>Current Service:</strong> ' . esc_html( $service_to_downgrade->getServiceName() ) . ' - ' . esc_html( $service_to_downgrade->getServiceId() ) . '</p>';
		$output .= '<p><strong>Product Name:</strong> ' . $service_product_name . '</p>';
		$output .= '<p><strong>Pricing:</strong> ' . wc_price( $service_price ) . '</p>';
		if ( 'Enabled' === $prorate_status ) {
			$output .= '<p><strong>Amount Used:</strong> ' . wc_price( $usage_metrics['used_amount'] ) . '</p>';
			$output .= '<p><strong> Balance:</strong> ' . wc_price( $usage_metrics['unused_amount'] ) . '</p>';
		}
		$output .= '<p><strong>Substitute Service Details</strong></p>';
		$output .= '<p><strong>Product:</strong> ' . esc_html( $selected_product->get_name() ) . '</p>';
		$output .= '<p><strong>Pricing:</strong> ' . wc_price( $product_price ) . '</p>';
		$output .= '<p><strong>Sign-up Fee:</strong> ' . wc_price( get_sw_service_product( $selected_product_id )['sign_up_fee'] ) . '</p>';

		$output .= '<p class="migrate-summary-tittle"><strong>' . esc_html__( 'Summary:', 'smart-woo-service-invoicing' ) . '</strong></p>';
		
		if ( $prorate_status === 'Enabled' ) {
			$output .= '<p><strong>Refund Amount:</strong> ' . wc_price( $order_total_data['remaining_unused_balance'] ) . '</p>';
		}
		
		$output .= '<p><strong>New Order Total:</strong> ' . wc_price( $order_total_data['order_total'] ) . '</p>';
		// Hidden form to Post migration data.
		$output .= '<form method="post" name="migrate_service">';
		$output .= wp_nonce_field( 'migration_nonce', 'migration_nonce' );
		$output .= '<input type="hidden" name="user_id" value="' . esc_attr( $current_user_id ) . '">';
		$output .= '<input type="hidden" name="service_id" value="' . esc_attr( $service_to_downgrade->getServiceId() ) . '">';
		$output .= '<input type="hidden" name="new_service_product_id" value="' . esc_attr( $selected_product_id ) . '">';
		$output .= '<input type="hidden" name="amount" value="' . esc_attr( $product_price ) . '">';
		$output .= '<input type="hidden" name="fee" value="' . esc_attr( $fee ) . '">';
		$output .= '<input type="hidden" name="order_total" value="' . esc_attr( $order_total_data['order_total'] ) . '">';
		$output .= '<input type="hidden" name="refund_amount" value="' . esc_attr( $order_total_data['remaining_unused_balance'] ) . '">';
		$output .= '<div class="upgrade-button-container">';
		$output .= '<button type="submit" name="proceed_with_downgrade">Downgrade Now</button>';
		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	$output	.= smartwoo_get_navbar( $current_user_id );
	if ( smartwoo_is_migration() ) {

		// Check if user has any services to downgrade.
		if ( empty( $services ) ) {
			return esc_html__( 'No services found for downgrade.', 'smart-woo-service-invoicing' );
		}

		$output .= '<form method="post" action="">';
		$output .= wp_nonce_field( 'downgrade_service_nonce', 'downgrade_service_nonce', true, false );

		// Create a select input with the user's services.
		$select_options  = '<select name="selected_service" required>';
		$select_options .= '<option value="" selected disabled>' . esc_html__( 'Select a Service', 'smart-woo-service-invoicing' ) . '</option>';

		foreach ( $services as $service ) {
			$select_options .= '<option value="' . esc_attr( $service->getServiceId() ) . '">' . esc_html( $service->getServiceName() ) . '</option>';
		}
		$select_options .= '</select>';

		// Container for select service.
		$output .= '<div class="select-service-container">';
		$output .= '<p>Choose a service to downgrade:</p>' . $select_options;
		$output .= '</div>';

		// Container for downgrade options.
		$output .= '<div class="products-to-use-container">';

		// Retrieve selected downgrade categories from options.
		$selected_downgrade_category = get_option( 'smartwoo_downgrade_product_cat', '0' );

		// Get products from selected downgrade categories.
		$downgrade_args  = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'id',
					'terms'    => $selected_downgrade_category,
				),
			),
		);
		$downgrade_query = new WP_Query( $downgrade_args );

		if ( $downgrade_query->have_posts() ) {
			while ( $downgrade_query->have_posts() ) {

				$downgrade_query->the_post();
				$product_id      = get_the_ID();
				$product_name    = get_the_title();
				$product_price   = get_post_meta( $product_id, '_price', true );
				$product_excerpt = get_the_excerpt();
				$output .= '<div class="product-container">';
				$output .= '<label>';
				$output .= '<input type="checkbox" name="selected_downgrade_product" value="' . esc_attr( $product_id ) . '" required>';
				$output .= '<strong>' . esc_html( $product_name ) . '</strong><br>';
				$output .= 'Price: ' . esc_html( $product_price ) . '<br>';
				$output .= 'Description: ' . esc_html( $product_excerpt );
				$output .= '</label>';
				$output .= '</div>';

			}

			wp_reset_postdata();
			$output .= '</div>';
			$output .= '<div style="text-align:center;">';
			$output .= '<button type="submit" class="sw-blue-button" name="downgrade_service_submit">' . esc_html__( 'Downgrade', 'smart-woo-service-invoicing' ) . '</button>';
			$output .= '</div>';

		} else {
			$output .= esc_html__( 'No products found for downgrade.', 'smart-woo-service-invoicing' );
		}

		$output .= '</form>';

		return $output;

	} else {
		// Display a message or take appropriate action when update is not allowed.
		$output .= smartwoo_notice( 'Service downgrade is not allowed. Contact us if you need any assistance' );
		return $output;
	}
}


/**
 * Handle new service product pruchase
 */
function smartwoo_buy_new_temp() {

	// Get Smart Woo Products.
	$smartwoo_products = Sw_Product::get_all();
	$output = '';
	$output .= smartwoo_get_navbar( get_current_user_id() );

	if ( ! empty( $smartwoo_products ) ) {

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
			$output .= '<p>' . esc_html( $product_excerpt ) . '</p>';
			$output .= '<a href="' . esc_url( smartwoo_configure_page( $product_id ) ) . '" class="sw-blue-button" >' . esc_html__( 'Configure Product', 'smart-woo-service-invoicing' ) . '</a>';
			$output .= '</div>';
		}

		return $output;

	} else {
		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
		$output       .= '<div class="main-page-card">';
		$output       .= '<p>We do not have service products for purchase yet!</p>';
		$output       .= '<a href="' . esc_url( $shop_page_url ) . '" class="sw-blue-button">' . esc_html__( 'Shop Page', 'smart-woo-service-invoicing' ) . '</a>';
		$output       .= '<a href="' . esc_attr( get_permalink() ) . '" class="sw-blue-button">' . esc_html__( 'Dashboard', 'smart-woo-service-invoicing' ) . '</a>';

		$output .= '</div>';
		return $output;
	}
}
