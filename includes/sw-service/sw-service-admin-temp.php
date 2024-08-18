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

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
	}

	$service_id = isset( $_GET['service_id'] ) ? sanitize_key( $_GET['service_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $service_id ) ) {
		return smartwoo_error_notice( 'Service ID parameter cannot be manipulated' );
	}
	$service    = SmartWoo_Service_Database::get_service_by_id( $service_id );
	if ( empty( $service ) ) {
		return smartwoo_error_notice( 'Service not fund' );
	}
	$page_html  = '';
	$page_html .= '<div class="wrap">';
	// Prepare array for submenu navigation.
	$tabs = array(
		''			=> 'Dashboard',
		'details'   => 'Details',
		'client' 	=> 'Client Info',
		'assets' 	=> 'Assets',
		'stats'  	=> 'Stats & Usage',
		'logs'   	=> 'Service Logs',

	);

	$args       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query_var  =  'action=view-service&service_id=' . $service->getServiceId() .'&tab';
	$page_html .= smartwoo_sub_menu_nav( $tabs, 'Service Informations <a href="' . smartwoo_service_edit_url( $service->getServiceId() ) . '">edit</a>','sw-admin', $args, $query_var );

	switch ( $args ) {
		case 'client':
			$page_html .= '<h2>Client Details</h2>';
			$page_html .= smartwoo_admin_show_customer_details( $service );
			$maybe_info = apply_filters( 'smartwoo_customer_details', array(), $service );

			foreach ( (array) $maybe_info as $key => $value ) {
				$page_html .= $value;
			}
			break;
		case 'assets':
			ob_start();
			$assets = $service->get_assets();
			$page_html .= '<h1>Assets <span class="dashicons dashicons-database-view"></span></h1>';

			include_once SMARTWOO_PATH . 'templates/service-admin-temp/service-assets.php';
			$page_html .= ob_get_clean();
			break;

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
 * Render Details of a service.
 * 
 * @param object $service	 SmartWoo_Service Object
 */
function smartwoo_show_admin_service_details( SmartWoo_Service $service ) {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'smart-woo-service-invoicing' ) );
	}

	$service_status		= smartwoo_service_status( $service->getServiceId() );
	$currency_symbol	= get_woocommerce_currency_symbol();

	/**
	 * Service details container
	 */
	$page_html	= '<div class="serv-details-card">';
	$page_html .= '<span style="display: inline-block; text-align: right; color: white; background-color: red; padding: 10px; border-radius: 5px; font-weight: bold;">' . esc_html( $service_status ) . '</span>';
	$page_html .= '<h2>Service Name</h2>';
	$page_html .= '<h3>'. $service->get_product_name() . ' - ' . esc_html( $service->getServiceName() ) . '</h3>';
	$page_html .= '<p class="smartwoo-container-item"><span> Service ID:</span>' . esc_html( $service->getServiceId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Service Type:</span>' . esc_html( $service->getServiceType() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Service URL:</span>' . esc_html( $service->getServiceUrl() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Amount:</span>' . esc_html( $currency_symbol . $service->get_pricing() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Billing Cycle:</span>' . esc_html( $service->getBillingCycle() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Start Date:</span>' . smartwoo_check_and_format( $service->getStartDate() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Next Payment Date:</span>' . smartwoo_check_and_format( $service->getNextPaymentDate() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> End Date:</span>' . smartwoo_check_and_format( $service->getEndDate() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span> Expiration Date:</span>' . smartwoo_check_and_format( smartwoo_get_service_expiration_date( $service ) ) . '</p>';
	/** Filter to add more details as associative array of title and value */
	$additional_details = apply_filters( 'smartwoo_more_service_details', array(), $service );
	
	foreach ( (array) $additional_details  as $title => $value ) {
		$page_html .= '<p class="smartwoo-container-item"><span> ' . $title . ':</span>' . esc_html( $value ) . '</p>';

	}

	/** Filter button row */
	$buttons = apply_filters( 'smartwoo_service_details_button_row', array(), $service );
	
	foreach ( (array) $buttons as $button ) {
		$page_html .= $button;
	}
	$page_html .= '<span id="sw-delete-button" style="text-align:center;"></span>';
	$page_html .= smartwoo_client_service_url_button( $service );
	$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-admin&action=edit-service&service_id=' . $service->getServiceId() ) ) . '" class="sw-blue-button">Edit this Service</a>';
	$page_html .= smartwoo_delete_service_button( $service->getServiceId() );
	$page_html .= '</div>';
	return $page_html;
}

/**
 * Plugin Admin Dashboard Page
 */
function smartwoo_dashboard_page() {

	$all_services = SmartWoo_Service_Database::get_all_services();
	$due_for_renewal_count		= smartwoo_count_due_for_renewal_services();
	$expired_services_count 	= smartwoo_count_expired_services();
	$grace_period_services		= smartwoo_count_grace_period_services();
	$active_services_count		= smartwoo_count_active_services();
	$suspended_service_acount 	= smartwoo_count_suspended_services();
	$nr_services 				= smartwoo_count_nr_services();
	$services   				= SmartWoo_Service_Database::get_all_services();

	/**
	 * HTML content for dashboard Page.
	 */
	$page_html  = '<div class="wrap">';
	$page_html .= '<h1>' . __( 'Service Subscriptions', 'smart-woo-service-invoicing') . '</h1>';
	$page_html .= '<div class="sw-button">';
	$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-admin&action=add-new-service' ) ) . '" class="sw-blue-button">Add New Service</a>';
	$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-options' ) ) . '" class="sw-blue-button">Settings</a>';
	$page_html .= '</div>';
	$page_html .= '<div class="dashboard-container">';
	/**
	 * All services count
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'All Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( count( $all_services ) ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Active services count.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Active', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $active_services_count ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Count for Active No-Renewal Services.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Active NR', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $nr_services ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Count for Due for Renewal Services.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Due', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $due_for_renewal_count ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Count for Grace Period Services.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Grace Period', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $grace_period_services ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Count for Expired Services.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Expired', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $expired_services_count ) . '</p>';
	$page_html .= '</div>';

	/**
	 * Count for Suspended Services.
	 */
	$page_html .= '<div class="dashboard-card">';
	$page_html .= '<h2>' . __( 'Suspended', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p class="count">' . esc_html( $suspended_service_acount ) . '</p>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Active Services container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Active Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';
	$active_services_found = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Active' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$active_services_found = true;
			}
		}
	}

	if ( ! $active_services_found ) {
		$page_html .= '<p>' . __( 'No service is active', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Active No-Renewal container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'No-Renewal Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<p>' . __( 'Active but will not renew when they expire', 'smart-woo-service-invoicing' ) . '</p>';
	$page_html .= '<ul>';

	$nr_services_found   = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Active (NR)' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$nr_services_found = true;
			}
		}
	}

	if ( ! $nr_services_found ) {
		$page_html .= '<p>' . __( 'No Service with "No Renewal" status was found.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Due for Renewal Service container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>'. __( 'Services Due','smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';

	$due_services_found  = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {

			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Due for Renewal' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() )) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$due_services_found = true;
			}
		}
	}

	if ( ! $due_services_found ) {
		$page_html .= '<p>' . __( 'No services have are due.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Grace period Services container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Grace Period', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';

	$grace_services = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Grace Period' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$grace_services = true;
			}
		}
	}

	if ( ! $grace_services ) {
		$page_html .= '<p>' . __( 'No service is on grace period.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Cancelled Services container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Cancelled Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';
	$cancelled_services_found = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Cancelled' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$cancelled_services_found = true;
			}
		}
	}

	if ( ! $cancelled_services_found ) {
		$page_html .= '<p>' . __( 'No services have been Cancelled.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Expired services container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Expired Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';
	$expired_services_found = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Expired' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$expired_services_found = true;
			}
		}
	}

	if ( ! $expired_services_found ) {
		$page_html .= '<p>' . __( 'No services have expired.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Suspended Services Container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Suspended Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';
	$active_services_found = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Suspended' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$active_services_found = true;
			}
		}
	}

	if ( ! $active_services_found ) {
		$page_html .= '<p>' . __( 'No service is suspended.', 'smart-woo-service-invoicing' ) . '</p>';
	}
	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';

	/**
	 * Pending Services container.
	 */
	$page_html .= '<div class="dashboard-list-container">';
	$page_html .= '<div class="dashboard-list-card">';
	$page_html .= '<h2>' . __( 'Pending Services', 'smart-woo-service-invoicing' ) . '</h2>';
	$page_html .= '<ul>';
	$pending_services_found = false;

	if ( ! empty( $services ) ) {
		foreach ( $services as $service ) {
			$service_status = smartwoo_service_status( $service->getServiceId() );

			if ( 'Pending' === $service_status ) {
				$page_html .= '<li><a href="' . esc_url( smartwoo_service_preview_url( $service->getServiceId() ) ) . '">' . esc_html( $service->getServiceName() ) . ' - ' . esc_html( $service->getServiceId() ) . '</a></li>';
				$pending_services_found = true;
			}
		}
	}

	if ( ! $pending_services_found ) {
		$page_html .= '<p>' . __( 'No service is Pending.', 'smart-woo-service-invoicing' ) . '</p>';
	}

	$page_html .= '</ul>';
	$page_html .= '</div>';
	$page_html .= '</div>';
	$page_html .= '</div>';
	return $page_html;
}



/**
 * Outputs the form fields for manually adding a new service.
 *
 * @since 1.0.0
 */
function smartwoo_new_service_form() {

	ob_start();
	?>
	<h2>Add New Service</h2>
	<p> Publish new service subscription and setup billing systems</p>
	<div class="sw-form-container">
	<form action="" method="post">

		<?php
		wp_nonce_field( 'sw_add_new_service_nonce', 'sw_add_new_service_nonce' );
		?>

		<!-- Service Name -->
		<div class="sw-form-row">
			<label for="service_name" class="sw-form-label"><?php esc_html_e( 'Service Name *', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service name (required)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_name" class="sw-form-input" id="service_name" required>
		</div>

		<!-- Service Type -->
		<div class="sw-form-row">
			<label for="service_type" class="sw-form-label"><?php esc_html_e( 'Service Type', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_type" class="sw-form-input" id="service_type">
		</div>

		<!-- Service URL -->
		<div class="sw-form-row">
			<label for="service_url" class="sw-form-label"><?php esc_html_e( 'Service URL', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service URL e.g., https:// (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="url" name="service_url" class="sw-form-input" id="service_url">
		</div>

		<!-- Choose a Client -->
		<div class="sw-form-row">
			<label for="user_id" class="sw-form-label"><?php esc_html_e( 'Choose a Client:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose a user from WordPress.(required)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php
			// WordPress User Dropdown.
			wp_dropdown_users(
				array(
					'name'             => 'user_id',
					'show_option_none' => esc_html__( 'Select User', 'smart-woo-service-invoicing' ),
					'class'            => 'sw-form-input',
					'show'             => 'display_name_with_login'
				)
			);
			?>
		</div>

		<!-- Service Products -->
		<div class="sw-form-row">
			<label for="service_products" class="sw-form-label"><?php esc_html_e( 'Service Products', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Select one product. This product price and fees will be used to create the next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php
			smartwoo_product_dropdown( '', true );
			?>
		</div>

		<!-- Start Date -->
		<div class="sw-form-row">
			<label for="start_date" class="sw-form-label"><?php esc_html_e( 'Start Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the start date for the service subscription.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="start_date" id="start_date" class="sw-form-input" required>
		</div>

		<!-- Billing Cycle -->
		<div class="sw-form-row">
			<label for="billing_cycle" class="sw-form-label"><?php esc_html_e( 'Billing Cycle', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the billing cycle for the service. Invoices are created toward the end of the billing cycle.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<select name="billing_cycle" id="billing_cycle" class="sw-form-input" required>
				<option value=""><?php esc_html_e( 'Select billing cycle', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Monthly"><?php esc_html_e( 'Monthly', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Quarterly"><?php esc_html_e( 'Quarterly', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Six Monthly"><?php esc_html_e( 'Six Monthly', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Yearly"><?php esc_html_e( 'Yearly', 'smart-woo-service-invoicing' ); ?></option>
			</select>
		</div>

		<!-- Next Payment Date -->
		<div class="sw-form-row">
			<label for="next_payment_date" class="sw-form-label"><?php esc_html_e( 'Next Payment Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the next payment date. Services will be due and an invoice will be created on this day.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="next_payment_date" id="next_payment_date" class="sw-form-input" required>
		</div>

		<!-- End Date -->
		<div class="sw-form-row">
			<label for="end_date" class="sw-form-label"><?php esc_html_e( 'End Date', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the end date for the service. This service will expire on this day if the product does not have a grace period set up.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="date" name="end_date" id="end_date" class="sw-form-input" required>
		</div>

		<!-- Set Service Status -->
		<div class="sw-form-row">
			<label for="status" class="sw-form-label"><?php esc_html_e( 'Set Service Status:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Set the status for the service. Status should be automatically calculated, choose another option to override the status. Please Note: an invoice will be created if the status is set to Due for Renewal.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<select name="status" id="status" class="sw-form-input">
				<option value=""><?php esc_html_e( 'Auto Calculate', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Active"><?php esc_html_e( 'Active', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Active (NR)"><?php esc_html_e( 'Disable Renewal', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Suspended"><?php esc_html_e( 'Suspend Service', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Cancelled"><?php esc_html_e( 'Cancel Service', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Due for Renewal"><?php esc_html_e( 'Due for Renewal', 'smart-woo-service-invoicing' ); ?></option>
				<option value="Expired"><?php esc_html_e( 'Expired', 'smart-woo-service-invoicing' ); ?></option>
			</select>
		</div>


		<!-- Submit Button -->
		<div class="sw-form-row">
			<input type="submit" name="add_new_service_submit" class="sw-blue-button" value="Publish">
		</div>
	</form>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render service editor form.
 */
function smartwoo_edit_service_form() {
	$url_service_id = sanitize_text_field( wp_unslash( $_GET['service_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	
	if ( empty( $url_service_id ) ) {
		return smartwoo_error_notice( 'Service Parameter cannot be manipulated' );
	}
	$service	= SmartWoo_Service_Database::get_service_by_id( $url_service_id );

	if ( empty( $service ) ) {
		return smartwoo_error_notice( 'Service not found.' );
	}
	$tabs = array(
		''			=> 'Dashboard',
		'details'   => 'View',
	);

	$args       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query_var  =  'action=view-service&service_id=' . $service->getServiceId() .'&tab';

	$service_name		= $service->getServiceName();
	$service_url		= $service->getServiceUrl();
	$service_type		= $service->getServiceType();
	$product_id			= $service->getProductId();
	$user_id			= $service->getUserId();
	$invoice_id			= $service->getInvoiceId();
	$start_date			= $service->getStartDate();
	$end_date			= $service->getEndDate();
	$next_payment_date 	= $service->getNextPaymentDate();
	$billing_cycle		= $service->getBillingCycle();
	$status				= $service->getStatus();
	$is_downloadable	= $service->has_asset();
	$product_name		= $service->get_product_name();
	if ( $is_downloadable ) {
		$assets	= $service->get_assets();
		$downloadables 	= array();
		$additionals	= array();

		foreach ( $assets as $asset ) {
			if ( 'downloads' === $asset->get_asset_name() ) {
				foreach ( $asset->get_asset_data() as $file => $url ) {
					$downloadables[$file]	= $url;
				}

				$id = $asset->get_id();
				continue;
			}
			
			$additionals[] = $asset;
		}
	}
	

	ob_start();
	include_once SMARTWOO_PATH . 'templates/service-admin-temp/edit-service.php';
	return ob_get_clean();
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
	$page .= '<option value="Six Monthly" ' . selected( 'Six Monthly', $billing_cycle, false ) . '>' . esc_html__( '6 Months', 'smart-woo-service-invoicing' ) . '</option>';
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
