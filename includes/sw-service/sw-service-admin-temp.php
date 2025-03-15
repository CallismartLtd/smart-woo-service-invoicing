<?php
/**
 * File name	sw-service-admin-temp.php
 * Template file for admin Service management.
 * 
 * @package SmartWoo\admin\templates.
 * @author Callistus.
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Admin Service Details page
 */
function smartwoo_admin_view_service_details() {

	$page_html  = '';
	$page_html .= '<div class="wrap">';
	// Prepare array for submenu navigation.
	

	$args       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query_var  =  'action=view-service&service_id=' . $service->getServiceId() .'&tab';
	$page_html .= smartwoo_sub_menu_nav( $tabs, 'Service Informations <a href="' . smartwoo_service_edit_url( $service->getServiceId() ) . '">edit</a>','sw-admin', $args, $query_var );

	switch ( $args ) {

		case 'logs':
			if ( class_exists( 'SmartWooPro', false ) ) {
				$page_html .= '<h2>Service Logs</h2>';
				$maybe_content = apply_filters( 'smartwoo_service_log_admin_page', array(), $service_id ); 

				foreach ( (array) $maybe_content as $key => $value ) {
					
					$page_html .= $value;

				}
			} else {
				$page_html .= smartwoo_pro_feature( 'service logs' );
			}
			break;
		
		case 'stats':
			$page_html .= '<h2>Usage Stats</h2>';
			if ( class_exists( 'SmartWooPro', false ) ) {
				$page_html .= apply_filters( 'smartwoo_stats', '', $service_id );
			} else {
				$page_html .= smartwoo_pro_feature( 'advanced stats');

			}
			break;

		default:
		$page_html .= '<h2>Service Details</h2>';
		$page_html .= '<div>';
		$page_html .= smartwoo_client_service_url_button( $service );
			$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-admin&action=edit-service&service_id=' . $service->getServiceId() ) ) . '"><button title="Edit Service"><span class="dashicons dashicons-edit"></span></button></a>';
			$page_html .= smartwoo_delete_service_button( $service->getServiceId() );
			$page_html .= '<span id="sw-delete-button" style="text-align:center;"></span>';
			$page_html . '</div>';
			$page_html .= smartwoo_show_admin_service_details( $service );
			break;
	}

	$page_html .= '</div>';
	return $page_html;
}

/**
 * Render customer details
 * 
 * @param object $service	SmartWoo_Service object.
 * @return string $page_html Client details container.
 */
function smartwoo_admin_show_customer_details( SmartWoo_Service $service ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
	}

	$user_id			= $service->getUserId();
	$user_info			= get_userdata( $user_id );
	$customer_name		= $user_info->first_name . ' ' . $user_info->last_name ?? 'Customer Name Not Found';
	$customer_email		= $user_info->user_email ?? 'Email Not Found';
	$billing_address	= smartwoo_get_user_billing_address( $user_id ) ?? 'Billing Address Not Found';
	$customer_phone		= get_user_meta( $user_id, 'billing_phone', true ) ?? 'Phone Number Not Found';
	$client_details 	= array(
		'Email Address' => $customer_email,
		'Phone Number'	=>  $customer_phone,
	);
	/** Additional Client details as an associative array */
	$additional_details = apply_filters( 'smartwoo_additional_client_details', array(), $user_id );
	$details = array_merge( $client_details, $additional_details );
	
	/**
	 * Client Details container.
	 */
	$page_html  = '<div class="serv-details-card">';
	$page_html .= '<div class="user-service-card">';
	$page_html .= '<h2>Full Name</h2>';
	$page_html .= '<p><h3><a style="text-decoration: none;" href="' . esc_url( get_edit_user_link( $user_id ) ) . '">' . esc_html( $customer_name ) . '</a></h3></p>';
	$page_html .= '<p class="smartwoo-container-item"><span>User ID:</span>' . esc_html( $user_id ) . '</p>';
	
	foreach ( $details as $heading => $value ) {
		$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html( $heading ) .':</span>' . esc_html( $value ) . '</p>';
	}
	$page_html .= '<p class="smartwoo-container-item"><span> Billing Address:</span>' . esc_html( $billing_address ) . '</p>';
		
	$page_html .= '</div>';
	$page_html .= '</div>';
	
	return $page_html;
}

/**
 * Render the service ID generator input.
 *
 * @param string|null $service_name Optional. The service name to pre-fill the input.
 * @param bool        $echo         Optional. Whether the output should be printed or return.
 * @param bool        $required     Optional. Whether the input is required. Default is true.
 */
function smartwoo_service_ID_generator_form( $service_name = null, $echo = true, $required = true ) {
	ob_start();
	?>
	<div class="sw-form-row">
		<label for="service-name" class="sw-form-label"><?php esc_html_e( 'Service Name:', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="<?php esc_attr_e( 'Service name here', 'smart-woo-service-invoicing' ); ?>">?</span>
		<input type="text" class="sw-form-input" id="service-name" name="service_name" <?php echo $required ? 'required' : ''; ?> value="<?php echo esc_attr( $service_name ); ?>">
	</div>

	<!-- Add an animated loader element -->
	<div id="swloader"><?php echo esc_html__( 'Generating...', 'smart-woo-service-invoicing' ); ?></div>

	<div class="sw-form-row">
		<label for="generated-service-id" class="sw-form-label"><?php esc_html_e( 'Generate Service ID *', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="<?php esc_attr_e( 'Click the button to generate a unique service ID', 'smart-woo-service-invoicing' ); ?>">?</span>
		<input type="text" class="sw-form-input" id="generated-service-id" name="service_id" readonly>
	</div>

	<div class="sw-form-row">
		<label for="button" class="sw-form-label"></label>
		<button id="generate-service-id-btn" type="button" class="sw-red-button"><?php esc_html_e( 'Click to generate', 'smart-woo-service-invoicing' ); ?></button>
	</div>

   	<?php
	if ( true === $echo ){
		$content = ob_get_clean();
		echo wp_kses( $content, smartwoo_allowed_form_html() );
	} else {
		return ob_get_clean();

	}
}

/**
 * Helper Function to render the new service order form.
 *
 * @param int    $user_id           The User's ID.
 * @param int    $order_id          The ID of the order to be processed.
 * @param string $service_name      The Configured service name in the order item.
 * @param string $service_url       The Configured service url.
 * @param string $user_full_name    Full name of the user associated with the order.
 * @param string $start_date        The start Date of the service.
 * @param string $billing_cycle     The Billing Cycle.
 * @param string $next_payment_date The start date of the service.
 * @param string $end_date          The end date of the service.
 * @param string $status            The status (Default 'pending').
 */
function smartwoo_new_service_order_form( $user_id, $order_id, $service_name, $service_url, $user_full_name, $start_date, $billing_cycle, $next_payment_date, $end_date, $status ) {
	$page  = '<h1>Process New Service Order</h1>';
	$page .= '<p>After processing, this order will be marked as completed.</p>';
	
	$page .= '<div class="sw-form-container">';
	$page .= '<form method="post" action="'. admin_url( 'admin-post.php' ) . '">';

	$product_id = 0;
	$order      = wc_get_order( $order_id );
	
	if ( ! empty( $order ) ) {
		$items = $order->get_items( 'line_item');
		if ( ! empty( $items ) ) {
			$first_item = reset( $items );
			$product_id = $first_item->get_product_id();
		}
	}
	
	$product_name = wc_get_product( $product_id )->get_name();
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="order_id" class="sw-form-label">' . __( 'Order:', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="The order ID and Product Name">?</span>';
	$page .= '<input type="text" name="order_id" id="order_id" class="sw-form-input" value="' . esc_attr( $order_id ) . ' - ' . esc_html( $product_name ) . '" readonly>';
	$page .= '</div>';
	ob_start();
 	smartwoo_service_ID_generator_form( $service_name, true, true );
	$page .= ob_get_clean();
	$page .= '<input type="hidden" name="product_id" value="' . esc_attr( $product_id ) . '">';
	$page .= '<input type="hidden" name="action" value="smartwoo_service_from_order">';
	$page .= wp_nonce_field( 'sw_process_new_service_nonce', 'sw_process_new_service_nonce' );
	// Service URL.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="service_url" class="sw-form-label">' . esc_html__( 'Service URL:', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Enter the service URL e.g., https:// (optional)', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="url" name="service_url" class="sw-form-input" id="service_url" value="' . esc_url( $service_url ) . '" >';
	$page .= '</div>';
	// Service Type.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="service_type" class="sw-form-label">' . esc_html__( 'Service Type', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="text" name="service_type" class="sw-form-input" id="service_type">';
	$page .= '</div>';
	// Client's Name.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="user_id" class="sw-form-label">' . esc_html__( 'Client\'s Name', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'The user whose ID is associated with the order', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="text" class="sw-form-input" name="user_id" id="user_id" value="' . esc_attr( $user_full_name ) . '" readonly>';
	$page .= '</div>';
	
	$page .= '<input type="hidden" name="user_id" value="' . esc_attr( $user_id ) . '">';
	// Sart date.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="start_date" class="sw-form-label">' . esc_html__( 'Start Date:', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Choose the start date for the service subscription, service was ordered on this date.', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="date" name="start_date" class="sw-form-input" id="start_date" value="' . esc_attr( $start_date ) . '" required>';
	$page .= '</div>';
	// Billing Cycle.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="billing_cycle" class="sw-form-label">' . esc_html__( 'Billing Cycle', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'This billing cycle was set from the product, you may edit it, invoices are created toward to the end of the billing cycle.', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>';
	$page .= '<option value="">' . esc_html__( 'Select billing cycle', 'smart-woo-service-invoicing' ) . '</option>';
	$page .= '<option value="Monthly" ' . selected( 'Monthly', $billing_cycle, false ) . '>' . esc_html__( 'Monthly', 'smart-woo-service-invoicing' ) . '</option>';
	$page .= '<option value="Quarterly" ' . selected( 'Quarterly', $billing_cycle, false ) . '>' . esc_html__( 'Quarterly', 'smart-woo-service-invoicing' ) . '</option>';
	$page .= '<option value="Six Monthly" ' . selected( 'Six Monthly', $billing_cycle, false ) . '>' . esc_html__( 'Semiannually', 'smart-woo-service-invoicing' ) . '</option>';
	$page .= '<option value="Yearly" ' . selected( 'Yearly', $billing_cycle, false ) . '>' . esc_html__( 'Yearly', 'smart-woo-service-invoicing' ) . '</option>';
	$page .= '</select>';
	$page .= '</div>';
	// Next Payment Date.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="next_payment_date" class="sw-form-label">' . esc_html__( 'Next Payment Date', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Choose the next payment date, services will be due and invoice is created on this day.', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="date" class="sw-form-input" name="next_payment_date" id="next_payment_date" value="' . esc_attr( $next_payment_date ) . '" required>';
	$page .= '</div>';
	// End Date.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="end_date" class="sw-form-label">' . esc_html__( 'End Date', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<input type="date" class="sw-form-input" name="end_date" id="end_date" value="' . esc_attr( $end_date ) . '" required>';
	$page .= '</div>';
	// Status.
	$page .= '<div class="sw-form-row">';
	$page .= '<label for="status" class="sw-form-label">' . esc_html__( 'Set Service Status:', 'smart-woo-service-invoicing' ) . '</label>';
	$page .= '<span class="sw-field-description" title="' . esc_attr__( 'Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: invoice will be created if the status is set to Due for Renewal', 'smart-woo-service-invoicing' ) . '">?</span>';
	$page .= '<select name="status" class="sw-form-input" id="status">';
	
	$status_options = array(
		''                => esc_html__( 'Auto Calculate', 'smart-woo-service-invoicing' ),
		'Pending'         => esc_html__( 'Pending', 'smart-woo-service-invoicing' ),
		'Active (NR)'     => esc_html__( 'Active (NR)', 'smart-woo-service-invoicing' ),
		'Suspended'       => esc_html__( 'Suspended', 'smart-woo-service-invoicing' ),
		'Due for Renewal' => esc_html__( 'Due for Renewal', 'smart-woo-service-invoicing' ),
		'Expired'         => esc_html__( 'Expired', 'smart-woo-service-invoicing' ),
	);
	
	foreach ( $status_options as $value => $label ) {
		$page .= '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $status, false ) . '>' . esc_html( $label ) . '</option>';
	}
	
	$page .= '</select>';
	$page .= '</div>';
	
	$page .= '<input type="submit" name="smartwoo_process_new_service" class="sw-blue-button" id="create_new_service" value="Process">';

	$page .= '</form>';
	$page .= '</div>';
	return $page;
}
