<?php
/**
 * File name   : email-templates.php
 * Author      : Callistus
 * Description : All mail templates
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Service Cancellation Email
 *
 * @param int    $user_id    The ID of the service owner
 * @param string $service_id The ID of the cancelled service
 */
function smartwoo_user_service_cancelled_mail( $service_id ) {

	$mail_is_enabled = get_option( 'smartwoo_cancellation_mail_to_user', 0 );
	
	if ( $mail_is_enabled ) {
		// Get service details
		$service_details = SmartWoo_Service_Database::get_service_by_id( $service_id );
		$sender_name	= get_option( 'smartwoo_email_sender_name' );
		$sender_email	= get_option( 'smartwoo_billing_email' );
		$business_name	= get_option( 'smartwoo_business_name' );
		$image_header	= get_option( 'smartwoo_email_image_header' );
		$user_id		= $service_details->getUserId();
		$user_info      = get_userdata( $user_id );
		$user_email     = $user_info->user_email;
		$user_firstname = $user_info->first_name;

		$cancellation_date = current_time( smartwoo_datetime_format() );

		// Extract relevant service details
		$service_name  = $service_details->getServiceName();
		$service_id    = $service_details->getServiceId();
		$billing_cycle = $service_details->getBillingCycle();
		$start_date    = smartwoo_check_and_format( $service_details->getStartDate(), true );
		$end_date      = smartwoo_check_and_format( $service_details->getEndDate(), true );
		$product_id    = $service_details->getProductId();
		$product       = wc_get_product( $product_id );

		if ( $product ) {
			$product_name  = $product->get_name();
			$product_price = $product->get_price();
		}

		// Email subject
		$subject = 'Service Cancellation Confirmation';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= '.card { border: 1px solid #ccc; padding: 10px; margin-top: 20px; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . '" Logo" style="max-width: 500px;"><br><br>';
		$message .= '<h1>Service Cancellation Confirmation</h1>';
		$message .= '<p>Dear ' . $user_firstname . ',</p>';
		$message .= '<p>We regret to inform you that your service with ' . esc_html( $business_name ) . ' has been cancelled as requested. We appreciate your past support and patronage.</p>';
		$message .= "<div class='card'>";
		$message .= '<p><strong>Service Details</strong></p>';
		$message .= '<p>Service Name: ' . esc_html( $product_name ) . '<br>';
		$message .= 'Billing Cycle: ' . esc_html( $billing_cycle ) . '<br>';
		$message .= 'Start Date: ' . esc_html( $start_date ) . '<br>';
		$message .= 'End Date: ' . esc_html( $end_date ) . '<br>';
		$message .= '</div>';
		$message .= '<p>Date of Cancellation: ' . $cancellation_date . '</p>';
		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:' . $sender_email . '">contact us</a>.</p>';
		$message .= '<p>Kindly note that our refund policy and terms of service apply to this cancellation.</p>';
		$message .= '<p>Thank you for choosing ' . esc_html( $business_name ) . '.</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $sender_name ) . ' <' . esc_html( $sender_email ) . '>',
		);

		// Send the email
		wp_mail( $user_email, $subject, $message, $headers );
	}
}

/**
 * Email for automatic renewal opt-out
 *
 * @param int    $user_id    The ID of the user who opted out
 * @param string $service_id The ID of the service
 */
function smartwoo_user_service_optout_mail( $service_id ) {

	$mail_is_enabled = get_option( 'smartwoo_service_opt_out_mail', 0 );

	if ( $mail_is_enabled ) {

		$sender_name	= get_option( 'smartwoo_email_sender_name' );
		$sender_email	= get_option( 'smartwoo_billing_email' );
		$business_name	= get_option( 'smartwoo_business_name' );
		$image_header	= get_option( 'smartwoo_email_image_header' );
		$user_id		= get_current_user_id();
		$user_info      = get_userdata( $user_id );
		$user_email     = $user_info->user_email;
		$user_firstname = $user_info->first_name;

		$service_details = SmartWoo_Service_Database::get_service_by_id( $service_id );

		if( empty( $service_details ) ) {
			return;
		}
		// Extract relevant service details
		$service_name = $service_details->getServiceName();
		$start_date   = smartwoo_check_and_format( $service_details->getStartDate() );
		$end_date     = smartwoo_check_and_format( $service_details->getEndDate() );

		// Email subject
		$subject = 'Auto Renewal Disabled';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= '.card { border: 1px solid #ccc; padding: 10px; margin-top: 20px; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . ' Logo" style="max-width: 500px;"><br><br>';
		$message .= '<h1>Auto Renewal for "' . esc_html( $service_name ) . '" has been disabled</h1>';
		$message .= '<p>Dear ' . esc_html( $user_firstname ) . ',</p>';
		$message .= '<p>You have successfully opted out of renewal for the service "' . esc_html( $service_name ) . '". The service is currently active but will not renew at the end of the billing cycle.</p>';
		$message .= '<p>Service Start Date: ' . esc_html( $start_date ) . '</p>';
		$message .= '<p>Service End Date: ' . esc_html( $end_date ) . '</p>';
		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:' . esc_attr( $sender_email ) . '">contact us</a>.</p>';
		$message .= '<p>Thank you for choosing ' . esc_html( $business_name ) . '.</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $sender_name ) . ' <' . esc_attr( $sender_email ) . '>',
		);

		wp_mail( $user_email, $subject, $message, $headers );
	}
}

/**
 * Email for service cancellation to the site admin
 *
 * @param int    $user_id    The ID of the user who cancelled the service
 * @param string $service_id The ID of the cancelled service
 */
function smartwoo_service_cancelled_mail_to_admin( $service_id ) {

	$mail_is_enabled = get_option( 'smartwoo_service_cancellation_mail_to_admin', 0 );
	if ( $mail_is_enabled ) {
		// Get service details
		$service_details = SmartWoo_Service_Database::get_service_by_id( $service_id );
		$user_id         = $service_details->getUserId();
		$user_info       = get_userdata( $user_id );
		$user_email      = $user_info->user_email;
		$user_full_name  = $user_info->first_name . ' ' . $user_info->last_name;
		$billing_address = smartwoo_get_user_billing_address( $user_id );

		// Extract relevant service details
		$service_name		= $service_details->getServiceName();
		$service_id			= $service_details->getServiceId();
		$billing_cycle		= $service_details->getBillingCycle();
		$start_date			= smartwoo_check_and_format( $service_details->getStartDate() );
		$next_payment_date 	= smartwoo_check_and_format( $service_details->getNextPaymentDate() );
		$end_date          	= smartwoo_check_and_format( $service_details->getEndDate() );
		$prorate_status    	= smartwoo_is_prorate();
		$product_info		= wc_get_product( $service_details->getProductId() );
		$product_name  		= $product_info ? $product_info->get_name() : 'N/A';
		$product_price 		= $product_info ? $product_info->get_price() : 'N/A';
		$business_name 		= get_option( 'smartwoo_business_name' );
		$image_header  		= get_option( 'smartwoo_email_image_header' );

		// Billing details.
		$billing_info  = '<div style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">';
		$billing_info .= '<p><strong>Customer Billing Details</strong></p>';
		$billing_info .= '<p>Name: ' . esc_html( $user_full_name ) . '</p>';
		$billing_info .= '<p>Email: ' . esc_html( $user_email ) . '</p>';
		$billing_info .= '<p>Address: ' . esc_html( $billing_address ) . '</p>';
		$billing_info .= '</div>';

		// Email subject
		$subject = 'Service Cancellation';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= 'h1 { color: #333; }';
		$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
		$message .= '.logo { display: block; margin: 0 auto; max-width: 500x; }';
		$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
		$message .= '.button:hover { background-color: #005bbf; }';
		$message .= '</style></head><body>';
		$message .= "<div class='container'>";
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . '" Logo style="max-width: 500px;"><br><br>';		
		$message .= '<h1>Service Cancellation</h1>';
		$message .= '<p>' . esc_html( $user_full_name ) . ' has cancelled their service. Find details below.</p>';
		$message .= '<div class="card">';
		$message .= '<p><strong>Service Details</strong></p>';
		$message .= '<p>Service Name: ' . esc_html( $product_name ) . ' - ' . esc_html( $service_name ) . '<br>';
		$message .= 'Service ID: ' . esc_html( $service_id ) . '<br>';
		$message .= 'Billing Cycle: ' . esc_html( $billing_cycle ) . '<br>';
		$message .= 'Start Date: ' . esc_html( $start_date ) . '<br>';
		$message .= 'Next Payment Date: ' . esc_html( $next_payment_date ) . '<br>';
		$message .= 'End Date: ' . esc_html( $end_date ) . '<br>';
		$message .= 'Pro rata refund is currently ' . esc_html( $prorate_status ) . '<br>';
		$message .= '</div>';
		$message .= wp_kses_post( $billing_info );
		$message .= '</body></html>';

		// Send the email to the site admin
		wp_mail( get_option( 'admin_email' ), $subject, $message );
	}
}

// Function to send payment reminder email for Service Renewals
function smartwoo_payment_reminder() {

	$mail_is_enabled = get_option( 'smartwoo_payment_reminder_to_client', 0 );
	if ( $mail_is_enabled ) {
		if ( wp_doing_cron() ) {
			add_filter( 'smartwoo_is_frontend', '__return_false' );

			$_GET['paged'] = 1;
			$_GET['limit'] = 100;
		}
				
		// Get sender details from options
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		// Retrieve Unpaid Invoices
		$unapaid_invoices = SmartWoo_Invoice_Database::get_invoices_by_payment_status( 'unpaid' );
		if ( empty( $unapaid_invoices ) ) {
			return;
		}

		foreach ( $unapaid_invoices as $invoice ) {
			$invoice_id		= $invoice->getInvoiceId();
			$invoice_amount	= $invoice->getTotal();
			$date_due		= smartwoo_check_and_format( $invoice->getDateDue(), true );
			$user_id		= $invoice->getUserId();
			$user_info		= get_userdata( $user_id );
			$user_full_name	= $user_info->first_name . ' ' . $user_info->last_name;
			$user_email		= $user_info->user_email;
			$payment_link	= smartwoo_generate_invoice_payment_url( $invoice_id, $user_email );

			// Prepare the email subject.
			$subject = 'Urgent: Unpaid Invoice Notification for ' . esc_html( $invoice_id );

			$message  = '<html><head><style>';
			$message .= 'body { font-family: Arial, sans-serif; }';
			$message .= 'h1 { color: #333; }';
			$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
			$message .= '.logo { display: block; margin: 0 auto; max-width: 500px; }';
			$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
			$message .= '.button:hover { background-color: #005bbf; }';
			$message .= '</style></head><body>';
			$message .= "<div class='container'>";
			$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . '" Logo style="max-width: 500px;"><br><br>';
			$message .= '<h1>A Soft Reminder</h1>';
			$message .= '<p>Dear ' . esc_html( $user_full_name ) .',</p>';
			$message .= '<p>We hope this email finds you well. We would like to bring to your attention an outstanding invoice associated with your account.</p>';
			$message .= '<p>Below are the details of the invoice:</p>';
			$message .= '<ul>';
			$message .= '<li>Invoice Number: ' . esc_html( $invoice_id ) . '</li>';
			$message .= '<li>Balance Due: ' . esc_html( $invoice_amount ) . '</li>';
			$message .= '<li>Date Due: ' . esc_html( $date_due ) . '</li>';
			$message .= '</ul>';
			$message .= '<p>Please make the payment before ' . esc_html( $date_due ) . ', to avoid any service interruption.</p>';
			$message .= '<p>To proceed with the payment, please click the button below:</p>';
			$message .= '<p><a href="' . esc_url( $payment_link ) . '" class="sw-red-button">Pay Now</a></p>';
			$message .= '<p>Payment Link: <a href="' . esc_url( $payment_link ) . '">'  . $payment_link . '</a></p>';
			$message .= '<p>If you have any questions or require assistance, please feel free to contact us. We are here to help you.</p>';
			$message .= '<p>Please note: For security reason, the link above will expire after 24hrs, you may need to log into your account manually when it expires</p>';
			$message .= '<p>Thank you for the continued business and support. We value you so much.</p>';
			$message .= 'Kind regards. <br>';
			$message .= '<p>' . esc_html( $business_name ) . '</p>';
			$message .= '</div></body></html>';

			// Email headers.
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . esc_attr( $sender_name ) . ' <' . esc_html( $sender_email ) . '>',
			);

			wp_mail( $user_email, $subject, $message, $headers );
		}
	}
}

/**
 * Send Service expiration mail to client on expiration day.
 *
 * @param object $service       The service object
 * @hook "smartwoo_service_expired" triggered by smatwoo_check_services_expired_today function
 */

function smartwoo_send_service_expiration_email( $service ) {

	$mail_is_enabled = get_option( 'smartwoo_service_expiration_mail', 0 );
	if ( $mail_is_enabled ) {

		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		$user_id       = $service->getUserId();
		$service_name  = $service->getServiceName();
		$service_id    = $service->getServiceId();
		$billing_cycle = $service->getBillingCycle();

		$user = get_userdata( $user_id );

		if ( $user ) {
			$user_email    = $user->user_email;
			$user_fullname = $user->first_name . ' ' . $user->last_name;

			// Prepare the email subject
			$subject = 'Service Expiration Notification';

			$message  = '<html><head><style>';
			$message .= 'body { font-family: Arial, sans-serif; }';
			$message .= 'h1 { color: #333; }';
			$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
			$message .= '.logo { display: block; margin: 0 auto; max-width: 500px; }';
			$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
			$message .= '.button:hover { background-color: #005bbf; }';
			$message .= '</style></head><body>';
			$message .= '<div class="container">';
			$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . '" Logo style="max-width: 500px;"><br><br>';
			$message .= '<h1>Service Expiration Notification</h1>';
			$message .= '<p>Dear ' . esc_html( $user_fullname ) . ',</p>';
			$message .= '<p>Your service ' . esc_html( $service_name ) . ' has expired due to the end of the service period with a "' . esc_html( $billing_cycle ) . ' billing cycle. Unfortunately, no renewal action was taken in time.</p>';
			$message .= '<p>You can always log into your account and reactivate this service before it is finally suspended.</p>';
			$message .= '<p>Thank you for choosing our services.</p>';
			$message .= 'Kind regards. <br>';
			$message .= '<p>' . esc_html( $business_name ) . '</p>';
			$message .= '</div></body></html>';

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . esc_attr( $sender_name ) . ' <' . esc_attr( $sender_email ) . '>',
			);

			wp_mail( $user_email, $subject, $message, $headers );
		}
	}
}


/**
 * Send service expiration mail to admin a day before expiration day
 */

function smartwoo_send_expiry_mail_to_admin() {

	$mail_is_enabled = get_option( 'smartwoo_service_expiration_mail_to_admin', 0 );

	if ( $mail_is_enabled ) {

		// Get sender details from options
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		$subject 		= 'End Date Notification for Services Due Tomorrow';
		$services      	= SmartWoo_Service_Database::get_all_services();
		$tomorrow_date 	= date_i18n( 'Y-m-d', strtotime( '+1 day' ) );

		if ( ! empty( $services ) ) {

			// Prepare the email message
			$message  = '<html><head><style>';
			$message .= 'body { font-family: Arial, sans-serif; }';
			$message .= 'h1 { color: #333; }';
			$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
			$message .= '</style></head><body>';
			$message .= "<div class='container'>";
			$message .= "<img src='" . esc_url( $image_header ) . "' alt='" . esc_attr( $business_name ) . " Logo' style='max-width: 500px;'><br><br>";
			$message .= '<h1>End Date Notification for Services Due Tomorrow</h1>';
			$message .= '<p>Dear Site Admin,</p>';
			$message .= '<p>This is to notify you that the following services are due to end tomorrow:</p>';
			foreach ( $services as $service ) {
				$expiration_date	= smartwoo_get_service_expiration_date( $service );
				$user_id			= $service->getUserId();
				$service_name 		= $service->getServiceName();
				$service_id   		= $service->getServiceId();
				$user_info      	= get_userdata( $user_id );
				$user_full_name 	= $user_info->first_name . ' ' . $user_info->last_name;

				$message .= '<ul>';
				$message .= '<li>Service Name: ' . esc_html( $service_name ) . '</li>';
				$message .= '<li>Service ID: ' . esc_html( $service_id ) . '</li>';
				$message .= '<li>User: ' . esc_html( $user_full_name ) . '</li>';
				$message .= '<br>';
				$message .= '</ul>';
			}

			$message .= '<p>Please take necessary actions to handle these services as they are approaching their end dates.</p>';
			$message .= '<p>Thank you for your attention.</p>';
			$message .= 'Kind regards, ' . esc_html( $sender_name ) . '</p>';
			$message .= '<p><strong>' . esc_html( $business_name ) . '</strong></p>';
			$message .= '</div></body></html>';

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . esc_html( $sender_name ) . ' <' . esc_html( $sender_email ) . '>',
			);

			if ( $expiration_date === $tomorrow_date ) {
				wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
			}
		}
	}
}

/**
 *  Send mail to user after sucessful service renewal.
 *
 * @param object        $service        The renewed Service
 */
function smartwoo_renewal_sucess_email( $service ) {

	$mail_is_enabled = get_option( 'smartwoo_renewal_mail', 0 );
	if ( $mail_is_enabled ) {
		// Get the renewed service information
		$service_name          = $service->getServiceName();
		$service_id            = $service->getServiceId();
		$new_start_date        = $service->getStartDate();
		$new_next_payment_date = $service->getNextPaymentDate();
		$new_end_date          = $service->getEndDate();
		$service_type          = $service->getServiceType() ?? 'Not Available';
		$user_id               = $service->getUserId();
		$user_info             = get_userdata( $user_id );
		$user_full_name        = $user_info->first_name . ' ' . $user_info->last_name ?? '';
		$user_email            = $user_info->user_email ?? '';
		$product_id            = $service->getProductId();
		$product               = wc_get_product( $product_id );
		$product_name          = $product->get_name() ?? 'Product Name is not Available';
		$service_pricing       = $product->get_price() ?? 0;

		// Get sender details from options
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		// Email subject
		$subject = $service_name . ' has been renewed';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= 'h1 { color: #333; }';
		$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
		$message .= '.logo { display: block; margin: 0 auto; max-width: 500px; }';
		$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
		$message .= '.button:hover { background-color: #005bbf; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . ' Logo" style="max-width: 500px;"><br><br>';
		$message .= '<h1>' . esc_html( $service_name ) . ' has been renewed</h1>';
		$message .= '<p>Dear ' . esc_html( $user_full_name ) . ',</p>';
		$message .= '<p>Your service "' . esc_html( $service_name ) . '"  with us has successfully been renewed.</p>';
		$message .= '<p>The details of your renewed service are as follows:</p>';
		$message .= '<ul>';
		$message .= '<li>Service Name: ' . esc_html( $product_name ) . ' - ' . esc_html( $service_name ) . '</li>';
		$message .= '<li>Pricing: ' . smartwoo_price( $service_pricing ) . '</li>';
		$message .= '<li>Service Type: ' . esc_html( $service_type ) . '</li>';
		$message .= '<li>Start Date: ' . esc_html( $new_start_date ) . '</li>';
		$message .= '<li>Next Payment Date: ' . esc_html( $new_next_payment_date ) . '</li>';
		$message .= '<li>Expiration Date: ' . esc_html( $new_end_date ) . '</li>';
		$message .= '</ul>';
		$message .= '<p>We appreciate your continued patronage and thank you for choosing our services.</p>';
		$message .= 'Kind regards. <br>';
		$message .= '<p>' . esc_html( $business_name ) . '</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_html( $sender_name ) . ' <' . esc_html( $sender_email ) . '>',
		);
		wp_mail( $user_email, $subject, $message, $headers );
	}
}


/**
 * Send mail when automatic invoice is create for a service
 *
 * @param object $invoice      The Invoice created.
 * @param object $service      The service which has the invoice.
 */
function smartwoo_send_auto_renewal_email( $invoice, $service ) {

	$mail_is_enabled = get_option( 'smartwoo_new_invoice_mail', 0 );

	if ( $mail_is_enabled ) {
		// Get the user object
		$user_info = get_userdata( $invoice->getUserId() );

		// Generate the payment link using the Invoice ID, and user's email
		$payment_link = smartwoo_generate_invoice_payment_url( $invoice->getInvoiceId(), $user_info->user_email );

		$user_full_name = $user_info->first_name . ' ' . $user_info->last_name;
		$user_email     = $user_info->user_email;
		$sender_name    = get_option( 'smartwoo_email_sender_name' );
		$sender_email   = get_option( 'smartwoo_billing_email' );
		$business_name  = get_option( 'smartwoo_business_name' );
		$image_header   = get_option( 'smartwoo_email_image_header' );

		// Get Service Name and Service ID from order custom fields
		$service_name = $service->getServiceName();
		$service_id   = $service->getServiceId();

		// Email subject
		$subject = 'Auto Renewal Invoice for ' . $service_name;

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= 'h1 { color: #333; }';
		$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
		$message .= '.logo { display: block; margin: 0 auto; max-width: 500px; }';
		$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
		$message .= '.button:hover { background-color: #005bbf; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . ' Logo" style="max-width: 500px;"><br><br>';
		$message .= '<h1>New invoice "' . $invoice->getInvoiceId() . '" for ' . $service_name . '</h1>';
		$message .= '<p>Dear ' . $user_full_name . ',</p>';
		$message .= '<p>An invoice for the renewal of "' . $service_name . '" with Service ID "' . $service_id . '" has been generated and is pending payment.</p>';
		$message .= '<p>Hurry now to pay to avoid interruption when your service expires.</p>';

		// Display the payment link both as a button and a text URL
		$message .= '<p>To proceed with the payment, please click the button below:</p>';
		$message .= '<p><a class="button" href="' . esc_url( $payment_link ) . '">Pay Now</a></p>';
		$message .= '<p>If the button above is not displayed, you can use the following link to make the payment:</p>';
		$message .= '<p><a href="' . esc_url( $payment_link ) . '">' . esc_url( $payment_link ) . '</a></p>';
		$message .= '<p>Please note: the above link will expire after 24hrs, you may need to log into your account manually when it expires</p>';
		$message .= 'Kind regards. <br>';
		$message .= '<p>' . $business_name . '</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_attr( $sender_name ) . ' <' . esc_attr( $sender_email ) . '>',
		);

		// Send the email
		wp_mail( $user_email, $subject, $message, $headers );
	}
}

/**
 * Send mail when user generates invoice for service renewal.
 *
 * @param object $invoice    The invoice used
 * @param object $service    The service being reactivated
 */
function smartwoo_send_user_generated_invoice_mail( $invoice, $service ) {

	$mail_is_enabled = get_option( 'smartwoo_reactivation_mail', 0 );

	if ( $mail_is_enabled ) {
		// User Details
		$user_id        = $invoice->getUserId();
		$user_info      = get_userdata( $user_id );
		$user_full_name = $user_info->first_name . ' ' . $user_info->last_name; // Get user's full name
		$user_email     = $user_info->user_email;
		// Sender Details
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		// Service Details
		$service_name        = $service->getServiceName();
		$service_id          = $service->getServiceId();
		$status              = smartwoo_service_status( $service_id );
		$service_action_text = ( $status === 'Due for Renewal' || $status === 'Grace Period' ) ? 'renewal' : 'reactivation';

		// Generate the payment link
		$payment_link = smartwoo_generate_invoice_payment_url( $invoice->getInvoiceId(), $user_email );

		// Email subject
		$subject = 'Reactivation for ' . $service_name . ' has been initiated';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= 'h1 { color: #333; }';
		$message .= '.container { max-width: 600px; margin: 0 auto; padding: 20px; }';
		$message .= '.button { display: inline-block; padding: 10px 20px; background-color: #0073e6; color: #fff; text-decoration: none; border-radius: 5px; }';
		$message .= '.button:hover { background-color: #005bbf; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . ' Logo" style="max-width: 500px;"><br><br>';
		$message .= '<h1>New invoice "' . $invoice->getInvoiceId() . '" for ' . $service_name . '</h1>';
		$message .= '<p>Dear ' . $user_full_name . ',</p>';
		$message .= '<p>You have generated an invoice for the ' . $service_action_text . ' of your service "' . $service_name . '" with Service number "' . $service_id . '". If you are yet to pay, please proceed to complete the payments now.</p>';

		// Display the payment link both as a button and a text URL
		$message .= '<p>To proceed with the payment, please click the button below:</p>';
		$message .= '<p><a class="button" href="' . esc_url( $payment_link ) . '">Pay Now</a></p>';
		$message .= '<p>If the button above is not displayed, you can use the following link to make the payment:</p>';
		$message .= '<p><a href="' . esc_url( $payment_link ) . '">' . esc_url( $payment_link ) . '</a></p>';
		$message .= '<p>Please note: for your security, the above link will expire after 24hrs, you may need to log into your account manually when it expires</p>';
		$message .= 'Kind regards. <br>';
		$message .= '<p>' . $business_name . '</p>';
		$message .= '</div></body></html>';

		// Email headers
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_attr( $sender_name ) . ' <' . esc_attr( $sender_email ) . '>',
		);

		// Send the email
		wp_mail( $user_email, $subject, $message, $headers );
	}
}


/**
 * Invoice Payment confirmation mail
 *
 * @param object $invoice   The paid invoice
 */

function smartwoo_invoice_paid_mail( $invoice ) {

	$mail_is_enabled = get_option( 'smartwoo_invoice_paid_mail', 0 );

	if ( $mail_is_enabled ) {

		// User Details
		$user_id        = $invoice->getUserId();
		$user_info      = get_userdata( $user_id );
		$user_full_name = $user_info->first_name . ' ' . $user_info->last_name; // Get user's full name
		$user_email     = $user_info->user_email;
		// Sender Details
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$sender_email  = get_option( 'smartwoo_billing_email' );
		$business_name = get_option( 'smartwoo_business_name' );
		$image_header  = get_option( 'smartwoo_email_image_header' );

		// Invoice Details
		$invoice_id      = $invoice->getInvoiceId();
		$paid_date       = smartwoo_check_and_format( $invoice->getDatePaid() );
		$payment_gateway = $invoice->getPaymentGateway();
		$amount          = $invoice->getAmount();
		$fee             = $invoice->getFee();
		$total           = $invoice->getTotal();
		$invoice_type    = $invoice->getInvoiceType();
		$transaction_id  = $invoice->getTransactionId();

		// Email subject
		$subject = 'Invoice Payment Confirmation';

		// Email message
		$message  = '<html><head><style>';
		$message .= 'body { font-family: Arial, sans-serif; }';
		$message .= '.card { border: 1px solid #ccc; padding: 10px; margin-top: 20px; }';
		$message .= '</style></head><body>';
		$message .= '<div class="container">';
		$message .= '<div class="container">';
		$message .= '<img src="' . esc_url( $image_header ) . '" alt="' . esc_attr( $business_name ) . ' Logo" style="max-width: 500px;"><br><br>';
		$message .= '<p>Dear ' . $user_full_name . ',</p>';
		$message .= '<p>This is a payment receipt for invoice ' . $invoice_id . ' paid on ' . $paid_date . '.</p>';
		$message .= '<ul>';
		$message .= '<li>Amount: ' . $amount . '</li>';
		$message .= '<li>Fee: ' . $fee . '</li>';
		$message .= '<li>Invoice Type: ' . $invoice_type . '</li>';
		$message .= '<li>Payment Method: ' . $payment_gateway . '</li>';
		$message .= '<li>Transaction ID: ' . $transaction_id . '</li>';
		$message .= '<li>Total: ' . $total . '</li>';
		$message .= '</ul>';
		$message .= 'Kind regards. <br>';
		$message .= '<p>' . $business_name . '</p>';
		$message .= '</div></body></html>';

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . esc_attr( $sender_name ) . ' <' . esc_attr( $sender_email ) . '>',
		);

		wp_mail( $user_email, $subject, $message, $headers );
	}
}
