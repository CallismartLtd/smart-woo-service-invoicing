<?php
/**
 * File name    :   smart-woo-manager.php
 *
 * @author      :   Callistus
 * Description  :   Service and Invoice management file and functions
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Perform action when a new service purchase is complete
 *
 * @param string $invoice_id The invoice ID.
 */
function smartwoo_new_service_invoice_handler( $invoice_id ) {
	// Mark invoice as paid.
	smartwoo_mark_invoice_as_paid( $invoice_id );
}
// Hook into action that indicates New Service Purchase is complete.
add_action( 'smartwoo_new_service_purchase_complete', 'smartwoo_new_service_invoice_handler' );


/**
 * Handling of Service subscription and Invoice based on WooCommerce order payment or status,
 * Essentially perform an action based on the order, or invoice type.
 *
 * @param int $order_id    The paid invoice order.
 */
// First action hook is when an order is marked as completed
add_action( 'woocommerce_order_status_completed', 'smartwoo_paid_invoice_order_manager', 50, 1 );

// Second action hook is when payment is processed by either the payment provider
add_action( 'woocommerce_payment_complete', 'smartwoo_paid_invoice_order_manager', 55, 1 );

function smartwoo_paid_invoice_order_manager( $order_id ) {
	$order = wc_get_order( $order_id );
	$invoice_id = $order->get_meta( '_sw_invoice_id' );

	// Early termination if the order is not related to our plugin.
	if ( empty( $invoice_id ) ) {
		return;
	}

	/**
	 * Get the invoice
	 * 
	 * @var object $invoice		SmartWoo_Invoice object
	 * 
	 */
	$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	// Terminate if no invoice is gotten with the ID, which indicates invalid invoice ID.
	if ( empty( $invoice ) ) {
		return;
	}

	$service_id   = $invoice->getServiceId();
	$invoice_type = $invoice->getInvoiceType();

	if ( $invoice_type === 'New Service Invoice' ) {
		// This is a new service invoice.
		do_action( 'smartwoo_new_service_purchase_complete', $invoice_id );
		return;
	}

	$user_id = $order->get_user_id();

	// If Service ID is available, this indicates an invoice for existing service.
	if ( ! empty( $service_id ) && ! empty( $user_id ) ) {
		$service_status = smartwoo_service_status( $service_id );
		/**
		 * Determine if the invoice is for the renewal of a Due service.
		 * Only invoices for services on this status are considered to be for renewal.
		 */
		if ( 'Due for Renewal' === $service_status || 'Grace Period' === $service_status && 'Service Renewal Invoice' === $invoice_type ) {

			// Call the function to renew the service.
			smartwoo_renew_service( $service_id, $invoice_id );

			/**
			 * Determine if the invoice is for the reactivation of an Expired service.
			 * Only invoices for services on this status are considered to be for reactivation.
			 */
		} elseif ( $service_status === 'Expired' && $invoice_type === 'Service Renewal Invoice' ) {
			// Call the function to reactivate the service.
			smartwoo_activate_expired_service( $service_id, $invoice_id );

			/**
			 * Determine if the invoice is for the Migration of a service.
			 * Only This invoice types for active services are considered to be for Migration.
			 */

		} elseif ( 'Active' === $service_status && 'Service Upgrade Invoice' === $invoice_type || 'Service Downgrade Invoice' === $invoice_type ) {
			$service = SmartWoo_Service_Database::get_service_by_id( $service_id );
			smartwoo_migrate_service( $service, $invoice_id );

		} else {
			return false;
		}
	}
}

/**
 * Renew a service based on the provided parameters.
 *
 * This performs service renewal, relying on the confirmation that
 * the invoice ID provided in the third parameter is paid. If the invoice is
 * not paid, the function will return early.
 *
 * @param int $user_id User ID associated with the service.
 * @param int $service_id ID of the service to be renewed.
 * @param int $invoice_id ID of the invoice related.
 * @param string $service_id ID of the service to be renewed.
 * @param string $invoice_id ID of the invoice related to the service renewal.
 */
function smartwoo_renew_service( $service_id, $invoice_id ) {
	$service = SmartWoo_Service_Database::get_service_by_id( $service_id );
	$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
	// Mark the invoice as paid before renewing the service.
	$invoice_is_paid = smartwoo_mark_invoice_as_paid( $invoice_id );

	if ( false === $invoice_is_paid ) {
		// Invoice is already paid, or something went wrong.
		return;
	}

	if ( $service ) {

		// Add Action Hook Before Updating Service Information.
		do_action( 'smartwoo_before_service_renew', $service );

		// Calculate Renewal Dates based on Billing Cycle.
		$billing_cycle = $service->getBillingCycle();
		$old_end_date  = strtotime( $service->getEndDate() );

		switch ( $billing_cycle ) {
			case 'Monthly':
				$interval = '+1 month';
				break;
			case 'Quarterly':
				$interval = '+3 months';
				break;
			case 'Six Monthly':
				$interval = '+6 months';
				break;
			case 'Yearly':
				$interval = '+1 year';
				break;
			default:
				break;
		}

		// Calculate new dates and implement.
		$new_start_date        = date_i18n( 'Y-m-d', $old_end_date );
		$new_end_date          = date_i18n( 'Y-m-d', strtotime( $interval, $old_end_date ) );
		$new_next_payment_date = date_i18n( 'Y-m-d', strtotime( '-7 days', strtotime( $new_end_date ) ) );
		$service->setStartDate( $new_start_date );
		$service->setNextPaymentDate( $new_next_payment_date );
		$service->setEndDate( $new_end_date );
		$service->setStatus( null ); // Renewed service will be automatically calculated.
		$updated = SmartWoo_Service_Database::update_service( $service );
		// send email notification.
		smartwoo_renewal_sucess_email( $service );
		// Add Action Hook After Service Renewal.
		do_action( 'smartwoo_service_renewed', $service );

	}
}



/**
 * Activate an expired service.
 *
 * This performs service renewal, relying on the confirmation that
 * the invoice ID provided in the third parameter is paid. If the invoice is
 * not paid, the function will return early.
 *
 * @param int $user_id User ID associated with the service.
 * @param string $service_id ID of the service to be renewed.
 * @param string $invoice_id ID of the invoice related to the service renewal.
 */
function smartwoo_activate_expired_service( $service_id, $invoice_id ) {
	$expired_service = SmartWoo_Service_Database::get_service_by_id( $service_id );
	$invoice         = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
	$invoice_is_paid = smartwoo_mark_invoice_as_paid( $invoice_id );

	if ( $invoice_is_paid === false ) {
		// Invoice is already paid or something went wrong.
		return;
	}

	if ( $expired_service ) {

		// Add Action Hook Before Updating Service Information.
		do_action( 'smartwoo_before_activate_expired_service', $expired_service );

		$order_id        = $invoice->getOrderId();
		$order           = wc_get_order( $order_id );
		$order_paid_date = $order->get_date_paid()->format( 'Y-m-d H:i:s' );

		// 4. Calculate Activation Dates based on Billing Cycle.
		$billing_cycle = $expired_service->getBillingCycle();

		switch ( $billing_cycle ) {
			case 'Monthly':
				$interval = '+1 month';
				break;
			case 'Quarterly':
				$interval = '+3 months';
				break;
			case 'Six Monthtly':
				$interval = '+6 months';
				break;
			case 'Yearly':
				$interval = '+1 year';
				break;
			default:
				break;
		}

		// Calculate new dates and implement.
		$new_start_date        = $order_paid_date;
		$new_end_date          = date_i18n( 'Y-m-d', strtotime( $interval, strtotime( $new_start_date ) ) );
		$new_next_payment_date = date_i18n( 'Y-m-d', strtotime( '-7 days', strtotime( $new_end_date ) ) );
		$expired_service->setStartDate( $new_start_date );
		$expired_service->setNextPaymentDate( $new_next_payment_date );
		$expired_service->setEndDate( $new_end_date );
		$expired_service->setStatus( null );
		$updated = SmartWoo_Service_Database::update_service( $expired_service );
		smartwoo_renewal_sucess_email( $expired_service );

		// Add Action Hook After Service Activation.
		do_action( 'smartwoo_expired_service_activated', $expired_service );
		return true;
	}

	return false;
}

/**
 * Handle Quick Action button on the Service Details page (frontend).
 *
 * This function is hooked into WordPress template redirection to handle actions related
 * to service cancellation or billing cancellation based on the 'action' parameter in the URL.
 */
// Add Ajax actions
add_action( 'wp_ajax_smartwoo_cancel_or_optout', 'smartwoo_cancel_or_optout_service' );
add_action( 'wp_ajax_nopriv_smartwoo_cancel_or_optout', 'smartwoo_cancel_or_optout_service' );

function smartwoo_cancel_or_optout_service() {

	if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security' ) ) {
		wp_die( -1, 401 );
	}

	if ( ! is_user_logged_in() ) {
		wp_die( -1, 403 );
	}

	$action 				= isset( $_POST['selected_action'] ) ? sanitize_key( $_POST['selected_action'] ) : '';
	$ajax_service_id 		= isset( $_POST['service_id'] ) ? sanitize_key( $_POST['service_id'] ) : '';
	
	if ( empty( $action) && empty( $ajax_service_id ) ) {
		wp_die( -1, 406 );

	}

	$service				= SmartWoo_Service_Database::get_service_by_id( $ajax_service_id );
	$user_id  				= get_current_user_id();
	$service_id				= $service->getServiceId();
	$next_service_status	= null;
	$user_cancelled_service	= false;
	$user_opted_out			= false;

	if ( 'sw_cancel_service' === $action ) {
		$next_service_status ='Cancelled';
		$user_cancelled_service = true;
	} elseif ( 'sw_cancel_billing' === $action ) {
		$next_service_status ='Active (NR)';
		$user_opted_out = true;

	}

	SmartWoo_Service_Database::update_service_fields( $service_id, array( 'status' => $next_service_status ) );

	if ( $user_cancelled_service ) {
		// Notify user and admin about service cancellation.
		smartwoo_user_service_cancelled_mail( $user_id, $service_id );
		smartwoo_service_cancelled_mail_to_admin( $service_id );
		
		$log_renewal 	= new SmartWoo_Service_Log();
		$log_renewal->setServiceId( $service->getServiceId() );
		$log_renewal->setLogType( 'Cancellation' );
		$log_renewal->setNote( 'Client Cancelled this service' );
		$log_renewal->save();
		
		// Trigger action with service object as argument.
		do_action( 'smartwoo_service_deactivated', $service );

		$details = 'Refund for the cancellation of ' . $service_id;
		smartwoo_create_prorata_refund( $service_id, $details );
	} elseif ( $user_opted_out ) {
		smartwoo_user_service_optout_mail( $user_id, $service_id );
		$log_renewal 	= new SmartWoo_Service_Log();
		$log_renewal->setServiceId( $service->getServiceId() );
		$log_renewal->setLogType( 'Cancellation' );
		$log_renewal->setNote( 'Client Cancelled this service' );
		$log_renewal->save();
	}
}

/**
 * Perform Prorata Refund for unused service due to cancellation
 *
 * @param string $service_id ID of the cancelled service
 * @return float|false Refund amount if successful, false otherwise.
 */
function smartwoo_create_prorata_refund( $service_id, $details ) {

	if ( 'Enabled' !== smartwoo_is_prorate() ) {
		return false;
    }

    $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
    if ( ! $service ) {
        return false;
    }

    $service_usage = smartwoo_analyse_service_usage( $service_id );

    if ( $service_usage && $service_usage['unused_amount'] > 0 ) {
		$refund_amount 	= $service_usage['unused_amount'];
        $note    		= 'A refund has been scheduled and may take up to 48 hours to be processed.';
        smartwoo_invoice_log( $service_id, 'Refund', 'Pending', $details, $refund_amount, $note );

        return $refund_amount;
    }

    // No refund amount calculated.
    return false;
}


/**
 * Handle the payment link, verify the token, log in the user, and process the payment.
 */

add_action( 'template_redirect', 'smartwoo_process_payment_link' );

function smartwoo_process_payment_link() {
	// Check if the pay-invoice action is set in the URL
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'sw_invoice_payment' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Get and sanitize the parameters from the URL
		$token = sanitize_text_field( $_GET['token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Verify the token.
		$payment_info = smartwoo_verify_token( $token );

		if ( ! $payment_info ) {
			// Token is invalid or expired.
			wp_die( 'Invalid or expired link', 401 );
		}
		// Extract relevant information.
		$invoice_id = $payment_info['invoice_id'];
		$user_email = $payment_info['user_email'];
		$user		= get_user_by( 'email', $user_email );

		if ( ! $user ) {
			wp_die( 'User not found', 403 );
		}
			
		// Make sure the SmartWoo_Invoice_Database class is defined and loaded.
		if ( ! class_exists( 'SmartWoo_Invoice_Database' ) ) {
			wp_die( 'Invoice is not fully loaded', 425 );
		}

		$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

		if ( ! $invoice ) {
			wp_die( 'Invoice not found', 404 );
		}

		$user_id         = $user->ID;
		$invoice_status  = $invoice->getPaymentStatus();
		$invoice_user_id = $invoice->getUserId();

		if ( $invoice_user_id !== $user_id ) {
			wp_die( 'You don\'t have the required permission to pay for this invoice, contact us if you need help', 403 );
		}

		$order_id 	= $invoice->getOrderId();
		$order 		= wc_get_order( $order_id );

		if ( $order && 'pending' !== $order->get_status() || 'unpaid' !== $invoice_status ) {
			wp_die( 'Invoice cannot be paid for, contact us if you need further assistance' );
		}
		
		// Conditions has been met, user should be logged in.
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID );
		do_action( 'wp_login', $user->user_login, $user );
		// Redirect to the order pay page.
		wp_safe_redirect( smartwoo_order_pay_url( $order_id ) );
		exit();
	}
}

/**
 * Initiates an automatic service renewal process by creating renewal invoice on due date
 * for services that are due.
 *
 * @Do_action "smartwoo_auto_invoice_created" triggers after successful invoice creation
 * @return bool False if no service is due | True otherwise
 */
function smartwoo_auto_renew_services() {
	$all_services = SmartWoo_Service_Database::get_all_services();

	if ( empty( $all_services ) ) {
		return;
	}

	foreach ( $all_services as $service ) {
		$user_id		= $service->getUserId();
		$service_id		= $service->getServiceId();
		$service_name	= $service->getServiceName();
		$product_id		= $service->getProductId();
		$service_status = smartwoo_service_status( $service_id );

		if ( 'Due for Renewal' === $service_status ) {
			$existing_invoice_id = smartwoo_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid' );
			if ( $existing_invoice_id ) {
				continue;
			}
			// New Invoice Data
			$payment_status = 'unpaid';
			$invoice_type   = 'Service Renewal Invoice';
			$date_due       = current_time( 'mysql' );

			// Generate Unpaid invoice.
			$new_invoice_id = smartwoo_create_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );
			if ( $new_invoice_id ) {
				// Get the invoice object
				$newInvoice = SmartWoo_Invoice_Database::get_invoice_by_id( $new_invoice_id );
				do_action( 'smartwoo_auto_invoice_created', $newInvoice, $service );
			}
			
		}
	}
}
// Hook to scheduled event
add_action( 'smartwoo_auto_service_renewal', 'smartwoo_auto_renew_services' );

/**
 * Handles service renewal when the client clicks the renew button on
 * Service Details page
 */
add_action( 'template_redirect', 'smartwoo_manual_service_renewal' );

function smartwoo_manual_service_renewal() {

	// Verify the nonce
	if ( isset( $_GET['action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['renew_nonce'] ) ), 'renew_service_nonce' ) ) {
		
		$service_id = sanitize_key( $_GET['service_id'] );
		$service    = SmartWoo_Service_Database::get_service_by_id( $service_id );
		$product_id = $service->getProductId();

		if ( ! $service || $service->getUserId() !== get_current_user_id() ) {
			wp_die( 'Error: Service does not exist.', 404 );
		}

		$service_status = smartwoo_service_status( $service_id );

		if ( 'Due for Renewal' === $service_status || 'Expired' === $service_status || 'Grace Period' === $service_status ) {

			$existing_invoice_id = smartwoo_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid' );
			if ( $existing_invoice_id ) {

				smartwoo_redirect_to_invoice_preview( $existing_invoice_id );

			}

			$payment_status = 'unpaid';
			$invoice_type   = 'Service Renewal Invoice';
			$date_due       = current_time( 'mysql' );

			// Generate Unpaid invoice
			$NewInvoiceID = smartwoo_create_invoice( get_current_user_id(), $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );

			if ( $NewInvoiceID ) {
				$NewInvoice   = SmartWoo_Invoice_Database::get_invoice_by_id( $NewInvoiceID );
				smartwoo_send_user_generated_invoice_mail( $NewInvoice, $service );
				$new_order_id = $NewInvoice->getOrderId();
				$new_order    = wc_get_order( $new_order_id );
				$checkout_url = smartwoo_order_pay_url( $new_order_id );
				wp_safe_redirect( $checkout_url );
				exit();
			}
		}
	}
	
}

/**
 * Perform Migration of service.
 *
 * @param object $service      The Service to be migrated.
 * @param string $invoice_id   The ID of the invoice used for migration payment.
 * @return bool  True if migrated, false if not migrated.
 */
function smartwoo_migrate_service( $service, $invoice_id ) {
	$service_id     = $service->getServiceId();
	$product_id     = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id )->getProductId();
	$invoice_status = smartwoo_mark_invoice_as_paid( $invoice_id );

	if ( false === $invoice_status ) {
		// Invoice is already paid, or something went wrong.
		$log 	= new SmartWoo_Service_Log();
		$log->setServiceId( $service->getServiceId() );
		$log->setLogType( 'Migration' );
		$log->setDatails( 'Service migration failed');
		$log->setNote( 'Attempt to check invoice payment status returned unexpected result, check payment status for ' . $invoice_id );
		$log->save();

		return false;
	}

	// Update the service with the new product ID.
	$fields = array( 'product_id' => $product_id, );

	$migrated = SmartWoo_Service_Database::update_service_fields( $service_id, $fields );
	do_action( 'smartwoo_service_migrated', $migrated );

	return $migrated;
}
