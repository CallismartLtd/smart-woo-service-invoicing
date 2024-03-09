<?php
/**
 * File name    :   tera-wallet-int.php
 *
 * @author      :   Callistus
 * Description  :   Integration of Tera Wallet plugin for invoice payment and refunds
 */

defined( 'ABSPATH' ) || exit; // exit if eccessed directly


// Hook to run the process_service_renewals function when the cron event is triggered
add_action( 'process_service_renewals_event', 'sw_pay_invoice_through_woo_wallet' );


// The main function to process service renewals
function sw_pay_invoice_through_woo_wallet() {
	// Get all unpaid nvoices
	$unpaid_invoices = Sw_Invoice_Database::get_invoices_by_payment_status( 'unpaid' );

	if ( empty( $unpaid_invoices ) ) {
		return;
	}

	foreach ( $unpaid_invoices as $invoice ) {
		// Get invoice ID and User ID
		$invoice_id = $invoice->getInvoiceId();
		$user_id    = $invoice->getUserId();

		// Construct payment details
		$payment_details = 'Auto debit for the payment of ' . $invoice_id;

		// Get the order total price
		$invoice_total = $invoice->getTotal();

		// Get the user's wallet balance
		$wallet_balance = woo_wallet()->wallet->get_wallet_balance( $user_id, 'edit' );

		if ( $wallet_balance >= $invoice_total && $invoice_total > 0 ) {
			// Attempt to debit the wallet
			woo_wallet()->wallet->debit( $user_id, $invoice_total, $payment_details );

			// Update the related order to complete
			$order = wc_get_order( $invoice->getOrderId() );
			$order->update_status( 'completed' );

			// Log Payment successful
			smart_woo_log( $user_id, 'No Service ID', $invoice_total, 'successful', $payment_details );
		} else {
			// Insufficient wallet balance
			smart_woo_log( $user_id, $invoice_id, $invoice_total, 'failed', 'insufficient funds' );
		}
	}
}
