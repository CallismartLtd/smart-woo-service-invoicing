<?php
/**
 * File name    :   smart-woo-manager.php
 *
 * @author      :   Callistus
 * Description  :   Service and Invoice management file and functions
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Perform action when a new service purchase is complete
 *
 * @param string $invoice_id The invoice ID
 */
function sw_new_service_invoice_handler( $invoice_id ) {
	// Mark invoice as paid
	sw_mark_invoice_as_paid( $invoice_id );
}
// Hook into action that indicates New Service Purchase is complete
add_action( 'sw_new_service_purchase_complete', 'sw_new_service_invoice_handler' );


/**
 * Handling of Service subscription and Invoice based on WooCommerce order payment or status,
 * Essentially perform an action based on the order, or invoice type.
 *
 * @param int $order_id    The paid invoice order
 */
// First action hook is when an order is marked as completed
add_action( 'woocommerce_order_status_completed', 'sw_paid_invoice_order_manager', 50, 1 );

// Second action hook is when payment is processed by either the payment provider
add_action( 'woocommerce_payment_complete', 'sw_paid_invoice_order_manager', 55, 1 );

function sw_paid_invoice_order_manager( $order_id ) {

	// Get the order object
	$order = wc_get_order( $order_id );

	// Get the invoice ID from the order metadata
	$invoice_id = $order->get_meta( '_sw_invoice_id' );

	// Early termination if the order is not related to our plugin
	if ( empty( $invoice_id ) ) {
		return;
	}

	/**
	 * Get the invoice
	 * 
	 * @var object $invoice		Sw_Invoice object
	 * 
	 */
	$invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

	// Terminate if no invoice is gotten with the ID, which indicates invalid invoice ID
	if ( empty( $invoice ) ) {
		return;
	}

	// Access invoice properties
	$service_id   = $invoice->getServiceId();
	$invoice_type = $invoice->getInvoiceType();

	if ( $invoice_type === 'New Service Invoice' ) {
		// This is a new service invoice
		do_action( 'sw_new_service_purchase_complete', $invoice_id );
		// terminate execution
		return;
	}

	// Get the ID of the user who owns the order
	$user_id = $order->get_user_id();

	// If Service ID is available, this indicates an invoice for existing service
	if ( ! empty( $service_id ) && ! empty( $user_id ) ) {

		// Fetch the service status
		$service_status = sw_service_status( $service_id );
		/**
		 * Determine if the invoice is for the renewal of a Due service.
		 * Only invoices for services on this status are considered to be for renewal.
		 */
		if ( $service_status === 'Due for Renewal' || $service_status === 'Grace Period' && $invoice_type === 'Service Renewal Invoice' ) {

			// Call the function to renew the service
			sw_renew_service( $service_id, $invoice_id );

			/**
			 * Determine if the invoice is for the reactivation of an Expired service.
			 * Only invoices for services on this status are considered to be for reactivation.
			 */
		} elseif ( $service_status === 'Expired' && $invoice_type === 'Service Renewal Invoice' ) {
			// Call the function to reactivate the service
			sw_activate_expired_service( $service_id, $invoice_id );

			/**
			 * Determine if the invoice is for the Migration of a service.
			 * Only This invoice types for active services  are considered to be for reactivation.
			 */

		} elseif ( $service_status === 'Active' && $invoice_type === 'Service Upgrade Invoice' || $invoice_type === 'Service Downgrade Invoice' ) {

			// Fetch the service object
			$service = Sw_Service_Database::get_service_by_id( $service_id );
			// Call the function to handle the migration process
			sw_migrate_service( $service, $invoice_id );

		} else {
			return false;
		}
	}
}

/**
 * Renew a service based on the provided parameters.
 *
 * This function performs service renewal, relying on the confirmation that
 * the invoice ID provided in the third parameter is paid. If the invoice is
 * not paid, the function will return early.
 *
 * @param int $user_id User ID associated with the service.
 * @param int $service_id ID of the service to be renewed.
 * @param int $invoice_id ID of the invoice related to the service renewal.
 */
function sw_renew_service( $service_id, $invoice_id ) {

	// Identify the Service and Invoice
	$service = Sw_Service_Database::get_service_by_id( $service_id );
	$invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );


	// Mark the invoice as paid before renewing the service
	$invoice_is_paid = sw_mark_invoice_as_paid( $invoice_id );

	if ( $invoice_is_paid === false ) {
		// Invoice is already paid, or something went wrong
		return; // Terminate further execution
	}

	if ( $service ) {

		// Add Action Hook Before Updating Service Information
		do_action( 'sw_before_service_renew', $service );

		// Calculate Renewal Dates based on Billing Cycle
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
				// Handle other cases if needed
				break;
		}

		// Calculate new dates
		$new_start_date        = date_i18n( 'Y-m-d', $old_end_date );
		$new_end_date          = date_i18n( 'Y-m-d', strtotime( $interval, $old_end_date ) );
		$new_next_payment_date = date_i18n( 'Y-m-d', strtotime( '-7 days', strtotime( $new_end_date ) ) );

		// Update the service using the update_service method
		$service->setStartDate( $new_start_date );
		$service->setNextPaymentDate( $new_next_payment_date );
		$service->setEndDate( $new_end_date );
		$service->setStatus( null ); // Renewed service will be automatically calculated

		// Perform the update
		$updated = Sw_Service_Database::update_service( $service );
		// send email notification
		sw_renewal_sucess_email( $service );
		// Add Action Hook After Service Renewal
		do_action( 'sw_service_renewed', $service );

	}
}



/**
 * Activate an expired service.
 *
 * This function performs service renewal, relying on the confirmation that
 * the invoice ID provided in the third parameter is paid. If the invoice is
 * not paid, the function will return early.
 *
 * @param int $user_id User ID associated with the service.
 * @param int $service_id ID of the service to be renewed.
 * @param int $invoice_id ID of the invoice related to the service renewal.
 */
function sw_activate_expired_service( $service_id, $invoice_id ) {
	// 1. Identify the Expired Service
	$expired_service = Sw_Service_Database::get_service_by_id( $service_id );
	$invoice         = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

	// Check and update invoice status before service update
	$invoice_is_paid = sw_mark_invoice_as_paid( $invoice_id );

	if ( $invoice_is_paid === false ) {
		// Invoice is already paid or something went wrong
		return; // Terminate further execution if needed
	}

	if ( $expired_service ) {

		// Add Action Hook Before Updating Service Information
		do_action( 'sw_before_activate_expired_service', $expired_service );

		// 3. Get the Order Paid Date
		$order_id        = $invoice->getOrderId();
		$order           = wc_get_order( $order_id );
		$order_paid_date = $order->get_date_paid()->format( 'Y-m-d H:i:s' );

		// 4. Calculate Activation Dates based on Billing Cycle
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
				// Handle other cases if needed
				break;
		}

		// Calculate new dates
		$new_start_date        = $order_paid_date;
		$new_end_date          = date_i18n( 'Y-m-d', strtotime( $interval, strtotime( $new_start_date ) ) );
		$new_next_payment_date = date_i18n( 'Y-m-d', strtotime( '-7 days', strtotime( $new_end_date ) ) );

		// Update the service using the update_service method
		$expired_service->setStartDate( $new_start_date );
		$expired_service->setNextPaymentDate( $new_next_payment_date );
		$expired_service->setEndDate( $new_end_date );
		$expired_service->setStatus( null );

		// Perform the update
		$updated = Sw_Service_Database::update_service( $expired_service );

		// send email notification
		sw_renewal_sucess_email( $expired_service );

		// Add Action Hook After Service Activation
		do_action( 'sw_expired_service_activated', $expired_service );

		// Return true for successful activation
		return true;
	}

	// Return false if activation failed
	return false;
}



/**
 * Helper function to handle customer billing details when invoice orders are created
 *
 * @param WC_Order $order The WooCommerce order object.
 */
function sw_order_billing_helper( $order ) {
    // Get the customer ID for the order.
    $user_id = $order->get_user_id();

	$order->set_billing_first_name( get_user_meta( $user_id, 'billing_first_name', true ) );
	$order->set_billing_last_name( get_user_meta( $user_id, 'billing_last_name', true ) );
	$order->set_billing_company( get_user_meta( $user_id, 'billing_company', true ) );
	$order->set_billing_address_1( get_user_meta( $user_id, 'billing_address_1', true ) );
	$order->set_billing_address_2( get_user_meta( $user_id, 'billing_address_2', true ) );
	$order->set_billing_city( get_user_meta( $user_id, 'billing_city', true ) );
	$order->set_billing_postcode( get_user_meta( $user_id, 'billing_postcode', true ) );
	$order->set_billing_country( get_user_meta( $user_id, 'billing_country', true ) );
	$order->set_billing_state( get_user_meta( $user_id, 'billing_state', true ) );
	$order->set_billing_email( get_user_meta( $user_id, 'billing_email', true ) );
	$order->set_billing_phone( get_user_meta( $user_id, 'billing_phone', true ) );

	$order->save();
    
}

add_action( 'new_invoice_order', 'sw_order_billing_helper' );


/**
 * Paid Invoice order status is updated to completed
 */
function sw_complete_processing_invoice_orders() {
    // Get processing orders created via this plugin
    $args   = array(
        'status' => 'processing',
    );
    $orders = wc_get_orders( $args );

    // Loop through each order
    foreach ( $orders as $order ) {
        if ( ! $order->is_created_via( SW_PLUGIN_NAME ) ) {
            continue; // Move to the next iteration if the order is not created via this plugin
        }
        
        // Get the order ID
        $order_id = $order->get_id();
        
        // We need to get the invoice associated with the order
        $invoices = Sw_Invoice_Database::get_invoices_by_order_id( $order_id );
        
        // Initialize a flag to track whether any eligible invoices are found
        $eligible_invoices_found = false;

        if ( ! empty( $invoices ) ) {
            // Loop through all invoices and check for eligible invoice types
            foreach ( $invoices as $invoice ) {
                $invoice_type = $invoice->getInvoiceType();
                if ( in_array( $invoice_type, array( 'Service Renewal Invoice', 'Service Downgrade Invoice', 'Service Upgrade Invoice' ) ) ) {
                    // Update the flag to indicate that an eligible invoice is found
                    $eligible_invoices_found = true;
                    break; // No need to continue checking invoices once an eligible one is found
                }
            }
        }

        // If eligible invoices are found, update the order status to 'completed'
        if ( $eligible_invoices_found ) {
            $order->update_status( 'completed' );
        }
    }
}

// Run every five minutes
add_action( 'smart_woo_5_minutes_task', 'sw_complete_processing_invoice_orders' );


/**
 * Handle Quick Action button on the Service Details page (frontend).
 *
 * This function is hooked into WordPress template redirection to handle actions related
 * to service cancellation or billing cancellation based on the 'action' parameter in the URL.
 */
add_action( 'template_redirect', 'sw_service_OptOut_or_Cancellation' );

function sw_service_OptOut_or_Cancellation() {
	// Check if the 'action' parameter is set and is one of the allowed actions
	if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'sw_cancel_service', 'sw_cancel_billing' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action     = sanitize_key( $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$user_id    = get_current_user_id();
		$service_id = isset( $_GET['service_id'] ) ? sanitize_text_field( $_GET['service_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Check if the service_id is provided and the service is currently 'Active'
		if ( ! empty( $service_id ) && sw_service_status( $service_id ) === 'Active' ) {
			// Determine the next service status based on the action
			$next_service_status = ( $action === 'sw_cancel_service' ) ? 'Cancelled' : 'Active (NR)';
			// Update the service status
			Sw_Service_Database::update_service_fields( $service_id, array( 'status' => $next_service_status ) );

			// Perform additional actions based on the action
			if ( $action === 'sw_cancel_service' ) {
				// Notify user and admin about service cancellation
				sw_user_service_cancelled_mail( $user_id, $service_id );
				sw_service_cancelled_mail_to_admin( $service_id );
				// Trigger action with service object as argument
				do_action( 'sw_service_deactivated', Sw_Service_Database::get_service_by_id( $service_id ) );

				// Check if pro-rata refunds are enabled
				if ( sw_Is_prorate() === 'Enabled' ) {
					// Perform pro-rata refund
					$details = 'Refund for the cancellation of ' . $service_id;
					sw_create_prorata_refund( $service_id, $details );
				}
			} elseif ( $action === 'sw_cancel_billing' ) {
				// Notify user about billing cancellation
				sw_user_service_optout( $user_id, $service_id );
			}

			// Redirect to the service details page
			$service_details_url = add_query_arg(
				array(
					'service_page' => 'service_details',
					'service_id'   => $service_id,
				),
				get_permalink()
			);
			wp_safe_redirect( $service_details_url );
			exit();
		} else {
			// Redirect to the service details page even if the service is not 'Active'
			$service_details_url = add_query_arg(
				array(
					'service_page' => 'service_details',
					'service_id'   => $service_id,
				),
				get_permalink()
			);
			wp_safe_redirect( $service_details_url );
			exit();
		}
	}
}

/**
 * Perform Prorata Refund for unused service due to cancellation
 *
 * @param string $service_id ID of the cancelled service
 * @return float|false Refund amount if successful, false otherwise.
 */
function sw_create_prorata_refund( $service_id, $details ) {
    // Check if pro-rata refunds are enabled.
    if ( sw_Is_prorate() !== 'Enabled' ) {
        // Pro-rata refunds are disabled, do not proceed with the refund.
        return false;
    }

    // Check if it's a valid service.
    $service = Sw_Service_Database::get_service_by_id( $service_id );
    if ( ! $service ) {
        // Service not found.
        return false;
    }

    // Retrieve Service usage to determine unused balance.
    $service_usage = sw_check_service_usage( $service_id );

    if ( $service_usage !== false && $service_usage['unused_amount'] > 0 ) {
        // Get the unused amount and make it the refund amount.
        $refund_amount = $service_usage['unused_amount'];

        // Log the refund data into our database from where refunds can easily be processed.
        $note    = 'A refund has been scheduled and may take up to 48 hours to be processed.';
        smart_woo_log( $service_id, 'Refund', 'Pending', $details, $refund_amount, $note );

        return $refund_amount;
    }

    // No refund amount calculated.
    return false;
}


/**
 * Handle the payment link, verify the token, log in the user, and process the payment.
 */

add_action( 'template_redirect', 'swsi_handle_payment_link' );

function swsi_handle_payment_link() {
	// Check if the pay-invoice action is set in the URL
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'sw_invoice_payment' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Get and sanitize the parameters from the URL
		$token = sanitize_text_field( $_GET['token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Verify the token
		$payment_info = swsi_verify_token( $token );

		if ( $payment_info ) {
			// Extract relevant information
			$invoice_id = $payment_info['invoice_id'];
			$user_email = $payment_info['user_email'];

			// Retrieve the user
			$user = get_user_by( 'email', $user_email );

			if ( $user ) {
				// Make sure the Sw_Invoice_Database class is defined and loaded
				if ( class_exists( 'Sw_Invoice_Database' ) ) {
					$invoice = Sw_Invoice_Database::get_invoice_by_id( $invoice_id );

					if ( $invoice ) {
						// Additional validation for service status
						$user_id         = $user->ID;
						$invoice_status  = $invoice->getPaymentStatus();
						$invoice_user_id = $invoice->getUserId();

						if ( $invoice_user_id === $user_id && $invoice_status === 'unpaid' ) {

							$order_id = $invoice->getOrderId();
							// Get the order object
							$order = wc_get_order( $order_id );

							if ( $order && $order->get_status() === 'pending' ) {
								// Log in the user
								wp_set_current_user( $user->ID, $user->user_login );
								wp_set_auth_cookie( $user->ID );
								do_action( 'wp_login', $user->user_login, $user );

								// Redirect to the order pay page
								$order_key    = $order->get_order_key();
								$checkout_url = wc_get_checkout_url() . 'order-pay/' . $order->get_id() . '/?pay_for_order=true&key=' . $order_key;
								wp_safe_redirect( $checkout_url );
								exit();
							} else {
								// Show wp_die with backlink
								wp_die(
									'Sorry, we cannot process payments for this invoice. Please contact us if you need further assistance',
									'Error',
									array(
										'response'  => 400,
										'back_link' => true,
									)
								);
							}
						} else {
							// Show wp_die with backlink
							wp_die(
								'This invoice cannot be paid for. Please contact us if you need further assistance',
								'Error',
								array(
									'response'  => 400,
									'back_link' => true,
								)
							);
						}
					} else {
						// Show wp_die with backlink
						wp_die(
							'Invoice not found',
							'Error',
							array(
								'response'  => 400,
								'back_link' => true,
							)
						);
					}
				} else {
					// Show wp_die with backlink
					wp_die(
						'Sw_Invoice_Database class not found',
						'Error',
						array(
							'response'  => 400,
							'back_link' => true,
						)
					);
				}
			} else {
				// Show wp_die with backlink
				wp_die(
					'User not found',
					'Error',
					array(
						'response'  => 400,
						'back_link' => true,
					)
				);
			}
		} else {
			// Token is invalid or expired; handle accordingly
			wp_die(
				'Invalid or expired link',
				'Error',
				array(
					'response'  => 400,
					'back_link' => true,
				)
			);
		}
	}
}



/**
 * Initiates an automatic service renewal process by creating renewal invoice on due date
 *
 * @Do_action "sw_auto_invoice_created" triggers after successful invoice creation
 * @action @param object $newInvoice  The instance of the newly created invoice
 * @action @param object $service     The instance of the service being renewed
 * @return bool False if no service is due | True otherwise
 */

// Hook to  scheduled event
add_action( 'auto_renew_services_event', 'sw_auto_renew_services' );

function sw_auto_renew_services() {
	// Get all services
	$all_services = SW_Service_Database::get_all_services();
	if ( empty( $all_services ) ) {
		return;
	}

	foreach ( $all_services as $service ) {
		$user_id      = $service->getUserId();
		$service_id   = $service->getServiceId();
		$service_name = $service->getServiceName();
		$product_id   = $service->getProductId();

		// Check the status of the service using the sw_service_status function
		$service_status = sw_service_status( $service_id );

		// Check if the service is 'Due for Renewal'
		if ( $service_status === 'Due for Renewal' ) {

			// Check if there is Service Renewal Invoice for each service
			$existing_invoice_id = sw_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid' );
			if ( $existing_invoice_id ) {
				continue; // proceeds with the next iteration
			} else {
				// New Invoice Data
				$payment_status = 'unpaid';
				$invoice_type   = 'Service Renewal Invoice';
				$date_due       = current_time( 'mysql' );

				// Generate Unpaid invoice
				$new_invoice_id = sw_generate_new_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );
				if ( $new_invoice_id ) {
					// Get the invoice object
					$newInvoice = Sw_Invoice_Database::get_invoice_by_id( $new_invoice_id );
					do_action( 'sw_auto_invoice_created', $newInvoice, $service );
				}
			}
		}
	}
}


/**
 * Handles service renewal when the client clicks the renew button on
 * Service Details page
 */
add_action( 'template_redirect', 'sw_manual_service_renewal' );

function sw_manual_service_renewal() {
	// Check if the renewal action is set in the URL
	if ( isset( $_GET['action'] ) && $_GET['action'] === 'renew-service' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Verify the nonce
		if ( isset( $_GET['renew_nonce'] ) && wp_verify_nonce( $_GET['renew_nonce'], 'renew_service_nonce' ) ) {

			// Get and sanitize the service ID
			$service_id = sanitize_text_field( $_GET['service_id'] );
			$service    = Sw_Service_Database::get_service_by_id( $service_id );
			$product_id = $service->getProductId();

			if ( ! $service ) {
				wp_die( 'Error: Service does not exist.' );
			}

			// Check the status of the service using the sw_service_status function
			$service_status = sw_service_status( $service_id );

			// Check if the service is 'Due for Renewal'
			if ( $service_status === 'Due for Renewal' || $service_status === 'Expired' || $service_status === 'Grace Period' ) {

				// Check if there is Service Renewal Invoice for the service
				$existing_invoice_id = sw_evaluate_service_invoices( $service_id, 'Service Renewal Invoice', 'unpaid' );
				if ( $existing_invoice_id ) {

					sw_redirect_to_invoice_preview( $existing_invoice_id );

				} else {
					// New Invoice Data
					$payment_status = 'unpaid';
					$invoice_type   = 'Service Renewal Invoice';
					$date_due       = current_time( 'Y-m-d H:i:s' );

					// Generate Unpaid invoice
					$NewInvoiceID = sw_generate_new_invoice( get_current_user_id(), $product_id, $payment_status, $invoice_type, $service_id, null, $date_due );

					if ( $NewInvoiceID ) {
						$NewInvoice   = Sw_Invoice_Database::get_invoice_by_id( $NewInvoiceID );
						$new_order_id = $NewInvoice->getOrderId();
						$new_order    = wc_get_order( $new_order_id );
						$order_key    = $new_order->get_order_key();
						sw_send_user_generated_invoice_mail( $NewInvoice, $service );
						$checkout_url = wc_get_checkout_url() . 'order-pay/' . $new_order_id . '/?pay_for_order=true&key=' . $order_key;
						wp_safe_redirect( $checkout_url );
						exit();
					}
				}
			}
		} else {
			// If nonce verification fails
			wp_die( 'Error: Service Renewal Action failed authentication.' );
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
function sw_migrate_service( $service, $invoice_id ) {
	$service_id     = $service->getServiceId();
	$product_id     = Sw_Invoice_Database::get_invoice_by_id( $invoice_id )->getProductId();
	$invoice_status = sw_mark_invoice_as_paid( $invoice_id );

	if ( $invoice_status === false ) {
		// Invoice is already paid, or something went wrong.
		error_log( 'Service migration failed for service ID: ' . $service_id . ', Invoice Check returned false, which may indicate invoice has already been paid. Invoice ID: ' . $invoice_id );

		return false; // Indicate failure.
	}

	// Update the service with the new product ID.
	$fields = array(
		'product_id' => $product_id,
	);

	$migrated = Sw_Service_Database::update_service_fields( $service_id, $fields );

	// Log or handle any errors during the update if needed.
	if ( ! $migrated ) {
		error_log( 'Service migration failed for service ID: ' . $service_id );
	}
	do_action( 'sw_service_migrated', $migrated );

	return $migrated; // Return the result of the migration.
}
