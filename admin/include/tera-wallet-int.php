<?php
/**
 * File name    :   tera-wallet-int.php
 *
 * @author      :   Callistus
 * Description  :   Integration of Tera Wallet plugin for invoice payment and refunds
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access

// Hook to run the process_service_renewals function when the cron event is triggered
add_action( 'smartwoo_daily_task', 'sw_pay_invoice_through_woo_wallet' );


/**
 * The main function to process service renewals
 */
function sw_pay_invoice_through_woo_wallet() {
	$enabled = get_option( 'smartwoo_pay_pending_invoice_with_wallet', 0 );

	if ( ! $enabled ) {
		return;
	}
	// Get all unpaid invoices
	$unpaid_invoices = SmartWoo_Invoice_Database::get_invoices_by_payment_status( 'unpaid' );

	if ( empty( $unpaid_invoices ) ) {
		return;
	}

	foreach ( $unpaid_invoices as $invoice ) {
		// Get invoice ID and User ID
		$invoice_id = $invoice->getInvoiceId();
		$user_id    = $invoice->getUserId();

		// Construct payment details
		$details = 'Wallet debit for the payment of invoice ID: "' . $invoice_id .'".';
		// Get the order total price
		$invoice_total = $invoice->getTotal();

		// Get the user's wallet balance
		$wallet_balance = woo_wallet()->wallet->get_wallet_balance( $user_id, 'edit' );

		if ( $wallet_balance >= $invoice_total && $invoice_total > 0 ) {
			// Attempt to debit the wallet
			$transaction_id = woo_wallet()->wallet->debit( $user_id, $invoice_total, $details );

			if ( $transaction_id ) {
				// Get the related order and mark payment as paid
				$order = wc_get_order( $invoice->getOrderId() );
				$order->payment_complete( $transaction_id );

				// Invoice will be updated after the order is paid or completed
				// Log Payment successful
				$note    = 'The invoice with this ID was paid via tera wallet.';
				smartwoo_invoice_log( $invoice_id, 'Debit', 'Completed', $details, $invoice_total, $note );
			}

		}
	}
}


/**
 * Perform Rfunds through tera Wallet
 */
function sw_refund_through_woo_wallet() {
	


}