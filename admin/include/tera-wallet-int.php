<?php
/**
 * File name    :   tera-wallet-int.php
 *
 * @author      :   Callistus
 * Description  :   Integration of Tera Wallet plugin for invoice payment and refunds
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

// Hook to run the process_service_renewals function when the cron event is triggered
add_action( 'smartwoo_daily_task', 'smartwoo_pay_invoice_through_woo_wallet' );

/**
 * The main function to process service renewals
 */
function smartwoo_pay_invoice_through_woo_wallet() {
	$enabled = get_option( 'smartwoo_pay_pending_invoice_with_wallet', 0 );

	if ( ! $enabled ) {
		return;
	}
	// Get all unpaid invoices.
	$unpaid_invoices = SmartWoo_Invoice_Database::get_invoices_by_payment_status( 'unpaid' );

	if ( empty( $unpaid_invoices ) ) {
		return;
	}

	foreach ( $unpaid_invoices as $invoice ) {

		$invoice_id 	= $invoice->getInvoiceId();
		$user_id    	= $invoice->getUserId();
		$details 		= 'Wallet debit for the payment of invoice ID: "' . $invoice_id .'".';
		$invoice_total 	= $invoice->getTotal();

		// Get the user's wallet balance
		$wallet_balance = woo_wallet()->wallet->get_wallet_balance( $user_id, 'edit' );

		if ( $wallet_balance >= $invoice_total && $invoice_total > 0 ) {

			$transaction_id = woo_wallet()->wallet->debit( $user_id, $invoice_total, $details );

			if ( $transaction_id ) {
				// Get the related order and mark payment as paid.
				$order = wc_get_order( $invoice->getOrderId() );
				// Invoice will be updated after the order is paid or completed.
				$order->payment_complete( $transaction_id );

				// Log Payment successful.
				$note    = 'The invoice with this ID was paid via tera wallet.';
				smartwoo_invoice_log( $invoice_id, 'Debit', 'Completed', $details, $invoice_total, $note );
			}

		}
	}
}

/**
 * Schedule refunds via TeraWallet to run once in 48hrs.
 */
add_action( 'smartwoo_once_in48hrs_task', 'smartwoo_refund_through_woo_wallet' );
/**
 * Perform Rfunds through tera Wallet
 */
function smartwoo_refund_through_woo_wallet() {
	$enabled = get_option( 'smartwoo_refund_with_wallet', 0 );
	if ( ! $enabled ) {
		return false;
	}

	$refunds = SmartWoo_Refund::get_refund();
	if ( empty( $refunds ) ) {
		return false;
	}

	foreach ( $refunds as $refund ) {
		$refund_id				= $refund->getLogId();
		$amount					= $refund->getAmount();
		$invoice				= SmartWoo_Invoice_Database::get_invoice_by_id( $refund_id );
		$service				= SmartWoo_Service_Database::get_service_by_id( $refund_id );
		$refund_parent_object	= $invoice ? $invoice: $service;

		if ( empty( $refund_parent_object ) ) {
			return false;
		}

		$user_id		= $refund_parent_object->getUserId();
		$transaction_id = woo_wallet()->wallet->credit( $user_id, $amount, 'Refund for "' . $refund_id .'"' );

		if ( $transaction_id ) {
			smartwoo_refund_completed( $refund->getLogId() );
		}
		return $transaction_id;
	}
}