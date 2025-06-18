<?php
/**
 * Template class file for invoice front page.
 * 
 * @author Callistus
 * @package SmartWoo\classes
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit; // Prevents direct access.

/**
 * Handles invoice pages
 */
class SmartWoo_Invoice_Frontend_Template {
	/**
	 * The main page handler
	 */
	public static function main_page() {			
		$limit 			= isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 10;
		$page 			= absint( max( 1, get_query_var( 'paged', 1 ) ) );
		$invoices		= SmartWoo_Invoice_Database::get_invoices_by_user( get_current_user_id() );
		$all_inv_count	= SmartWoo_Invoice_Database::count_all_by_user( get_current_user_id() );
		$total_pages 	= ceil( $all_inv_count / $limit );
		$total_items_count = count( $invoices );
		$not_found_text = 'All Invoices will appear here';

		include_once SMARTWOO_PATH . 'templates/frontend/invoices/front.php';

	}

	/**
	 * Invoice sorting page
	 */
	public static function sort() {
		$status			= sanitize_key( get_query_var( 'status' ) );
		smartwoo_set_document_title( ucfirst( $status ) . ' Invoices' );
		$limit 			= isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 10;
		$page 			= absint( get_query_var( 'paged', 1 ) );
		$invoices		= SmartWoo_Invoice_Database::get_invoices_by_payment_status( $status );
		$all_inv_count	= SmartWoo_Invoice_Database::count_this_status( $status );
		$total_pages 	= ceil( $all_inv_count / $limit );
		$total_items_count = count( $invoices );
		$not_found_text = 'No "' . ucfirst( $status ) . '" invoice found.';

		include_once SMARTWOO_PATH . 'templates/frontend/invoices/front.php';
	}

	/**
	 * Invoice details page
	 */
	public static function invoice_info() {
		smartwoo_set_document_title( 'View Invoice' );
		$invoice_id	= sanitize_text_field( wp_unslash( get_query_var( 'view-invoice', '' ) ? get_query_var( 'view-invoice', '' ) : get_query_var( 'smartwoo-invoice', '' ) ) );
		$invoice	= ! empty( $invoice_id ) ? SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id ) : false;

		if ( $invoice && $invoice->current_user_can_access() ) {
			$biller_details			= smartwoo_biller_details();
			$business_name			= $biller_details->business_name;
			$invoice_logo_url		= $biller_details->invoice_logo_url;
			$admin_phone_number		= $biller_details->admin_phone_number;
			$store_address			= $biller_details->store_address;
			$store_city				= $biller_details->store_city;
			$default_country		= $biller_details->default_country;
			$biller_email			= get_option( 'smartwoo_billing_email', 'N/A' );
			$user 					= $invoice->get_user();
			$first_name				= $user->get_first_name();
			$last_name				= $user->get_last_name();
			$billing_email			= $user->get_billing_email();
			$billing_phone			= $user->get_billing_phone();
			$customer_company_name	= $user->get_billing_company();
			$user_address			= $invoice->get_billing_address();
			$service_id 			= $invoice->get_service_id();
			$service    			= ! empty( $service_id ) ? SmartWoo_Service_Database::get_service_by_id( $service_id ) : false;

			if ( $service ) {
				$service_name	= $service->get_name();
			}

			$product			= $invoice->get_product();
			$product_name		= $product ? $product->get_name() : 'Product Not Found';
			$invoice_date		= smartwoo_check_and_format( $invoice->get_date_created(), true );
			$transaction_date	= smartwoo_check_and_format( $invoice->get_date_paid(), true );
			$invoice_due_date	= smartwoo_check_and_format( $invoice->get_date_due(), true );
			$payment_gateway	= ! empty( $invoice->get_payment_method_title() ) ? $invoice->get_payment_method_title() : 'N/A';
			$invoice_status		= $invoice->get_status();
			$transaction_id		= ! empty( $invoice->get_transaction_id() ) ? $invoice->get_transaction_id() : 'N/A';
			$invoice_items		= $invoice->get_items();
			$download_url		= $invoice->download_url();
		}
		
		/**
		 * Invoice template.
		 * 
		 * @filter smartwoo_invoice_template.
		 * @param string $template_path template file.
		 * @param SmartWoo_Invoice The invoice object.
		 */
		$template_path	= SMARTWOO_PATH . 'templates/frontend/invoices/view-invoice-temp.php';
		$file			= apply_filters( 'smartwoo_invoice_template', $template_path, $invoice );

		if ( file_exists( $file ) ) {
			include_once( $file );
		}

	}

	/**
	 * WooCommerce my-account page handler
	 */
	public static function woocommerce_myaccount_invoices_page() {

		if ( get_query_var( 'smartwoo-invoice', false ) ) {
			self::invoice_info();
		} else {
			self::main_page();
		}

	}

	/**
	 * Handles the rendering of [smartwoo_invoice_page] shortcode
	 */
	public static function shortcode_handler() {
		global $wp_query;

		$pages			= apply_filters( 'smartwoo_invoice_pages', array() );
		$current_page	= '';
		$handler	= array( __CLASS__, 'main_page' );
		$endpoints = SmartWoo_Config::instance()->get_query_vars();

		foreach ( $endpoints as $page ) {
			if ( isset( $wp_query->query_vars[$page] ) ) {
				$current_page = $page;
				break;
			}
		}

		if ( ! empty( $current_page ) && isset( $pages[$current_page] ) ) {
			$handler = $pages[$current_page];
		}

		if ( ! is_user_logged_in() && 'buy-new' !== $current_page ) {
			$handler =  array( __CLASS__, 'login_page' );
		}

		if ( is_callable( $handler ) ) {
			ob_start();
			call_user_func( $handler );
			return ob_get_clean();
		}
	}

	private static function login_page() {
		wp_enqueue_style( 'dashicons' );
		$args =  array( 
			'notice' => smartwoo_notice( 'Login to access this page.' ),
			'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) )
		);
		include_once SMARTWOO_PATH . 'templates/login.php';
	}
}


/**
 * Invoice mini card, aids in displaying invoice content anywhere with a post. 
 * 
 * @return string HTML Post markup.
 */
function smartwoo_invoice_mini_card() {

	if ( ! is_user_logged_in() ) {
		woocommerce_login_form( array( 'message' => smartwoo_notice( 'You must be logged in to access this page' ) ) );
	   return;
    }

    $current_user_id = get_current_user_id();
	/**
	 * Starts card markup.
	 */
    $table_html      	  = '<div class="mini-card">';
    $table_html     	 .= '<h2>' . esc_html__( 'My Invoices', 'smart-woo-service-invoicing') . '</h2>';
    $table_html     	 .= '<table>';   
    $all_invoices         = SmartWoo_Invoice_Database::get_invoices_by_user( $current_user_id );

    if ( $all_invoices ) {

        foreach ( $all_invoices as $invoice ) {

            $invoice_id     = esc_html( $invoice->get_invoice_id() );
            $generated_date = esc_html( smartwoo_check_and_format( $invoice->get_date_created() ) );
            $order_id       = esc_html( $invoice->get_order_id() );
            $table_html    .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Invoice ID:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value">' . esc_html( $invoice_id ) . '</td>
            </tr>';

            $table_html .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Date:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value">' . esc_html__( 'Generated on - ', 'smart-woo-service-invoicing' ) . $generated_date. '</td>
            </tr>';

            $preview_invoice_url = smartwoo_invoice_preview_url( $invoice->get_invoice_id() );

            $table_html .= '<tr>
                <td class="invoice-table-heading">' . esc_html__( 'Action:', 'smart-woo-service-invoicing' ) . '</td>
                <td class="invoice-table-value"><a href="' . esc_url( $preview_invoice_url ) .'" class="invoice-preview-button">' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a>';

            // Show the "Pay" button beside the "View" button only if the order is pending.
            if ( 'unpaid' === $invoice->get_status() ) {
                $checkout_url  = smartwoo_invoice_pay_url( $invoice->get_order_id() );
                $table_html   .= '<a href="' . esc_url( $checkout_url ) .'" class="invoice-pay-button">' . esc_html__( 'Pay', 'smart-woo-service-invoicing' ) . '</a>';
            }

            $table_html .= '</td></tr>';

            $table_html .= "<tr><td colspan='2'></td></tr>";
        }
    } else {
        $table_html .= "<tr><td colspan='2'>" . esc_html__( 'All your invoices will appear here.', 'smart-woo-service-invoicing' ) . "</td></tr>";
    }

    // Close the table markup.
    $table_html .= '</table>';

    $table_html .= '</div>';

    return $table_html;
}



/**
 * Render WooCommerce Orders as transactions.
 */
function smartwoo_transactions_shortcode() {
	$output		= "";

	if ( ! is_user_logged_in() ) {
	   return $output;
    }

	$orders = smartwoo_get_configured_orders_for_service( null, true );

	if ( ! $orders ) {
		$output	.= '<p>' . esc_html__( 'Transaction histories will appear here','smart-woo-service-invoicing' ) . '</p>';
		return $output;
	}
	$output	.= '<div class="sw-table-wrapper">';
	$output	.= '<table class="sw-table">';
	$output	.= '<thead>';
	$output	.= '<tr>';
	$output	.= '<th>' . esc_html__( 'Status', 'smart-woo-service-invoicing' ) . '</th>';
	$output	.= '<th>' . esc_html__( 'Amount', 'smart-woo-service-invoicing' ) . '</th>';
	$output	.= '<th>' . esc_html__( 'Date', 'smart-woo-service-invoicing' ) . '</th>';
	$output	.= '<th>' . esc_html__( 'Action', 'smart-woo-service-invoicing' ) .'</th>';
	$output	.= '</tr>';
	$output	.= '<tbody>';

	foreach ( $orders as $order ) {

		$order_id		= $order->get_id();
		$amount         = $order->get_total();
		$order_status   = $order->get_status();
		$order_date     = smartwoo_check_and_format( $order->get_date_created(), true );
		$payment_method = $order->get_payment_method_title();
		$product_names  = array();
		$output	.= '<tr>';
		$output	.= '<td>' . esc_html( $order_status ) . '</td>';
		$output	.= '<td>' . smartwoo_price( $amount, array( 'currency' => $order->get_currency() ) ) . '</td>';
		$output	.= '<td>' . esc_html( $order_date ) . '</td>';
		$output	.= '<td><a href="' . esc_url( $order->get_view_order_url() ) . '" class="invoice-preview-button">' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
		$output	.= '</tr>';
	}

	$output	.= '</tbody>';
	$output	.= '</table>';
	$view_all_url = wc_get_account_endpoint_url( 'orders' );
	$output	.= '<p><a href="' . esc_url( $view_all_url ) . '" class="sw-blue-button">' . esc_html__( 'View Older Transactions', 'smart-woo-service-invoicing' ) . '</a></p>';
	$output	.= '</div>';
	$output	.= '</div>';

	return $output;
}

