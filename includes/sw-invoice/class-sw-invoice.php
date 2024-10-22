<?php
/**
 * Class file for Smart Woo Invoice.
 * 
 * @author Callistus.
 * @since 1.0.0
 * @package SmartWoo\Classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Class SmartWoo_Invoice
 *
 * Represents an invoice for the Smart Woo Service Invoicing.
 *
 * @since   1.0.0
 * @package SmartWooInvoice
 */
class SmartWoo_Invoice {

	// Properties
	private $id;
	private $service_id;
	private $user_id;
	private $billing_address;
	private $invoice_id;
	private $invoice_type;
	private $product_id;
	private $order_id;
	private $amount;
	private $fee;
	private $payment_status;
	private $payment_gateway;
	private $transaction_id;
	private $date_created;
	private $date_paid;
	private $date_due;
	private $balance = 0;
	private $total;

	// Constructor
	public function __construct(
		string $invoice_id,
		int $product_id,
		float $amount,
		float $total,
		string $payment_status,
		?string $date_created 		= null,
		?int $user_id = null,
		?string $billing_address 	= null,
		?string $invoice_type 		= null,
		?string $service_id 		= null,
		?float $fee 				= null,
		?int $order_id 				= null,
		?string $payment_gateway 	= null,
		?string $transaction_id 	= null,
		?string $date_paid 			= null,
		?string $date_due 			= null
	) {
		$this->invoice_id      = $invoice_id;
		$this->product_id      = $product_id;
		$this->amount          = $amount;
		$this->total           = $total;
		$this->payment_status  = $payment_status;
		$this->date_created    = $date_created ?? current_time( 'mysql' ); // Set created to the current time if not provided
		$this->user_id         = $user_id;
		$this->billing_address = $billing_address ?? null;
		$this->invoice_type    = $invoice_type ?? null;
		$this->service_id      = $service_id ?? null;
		$this->fee             = $fee ?? 0;
		$this->order_id        = $order_id ?? null;
		$this->payment_gateway = $payment_gateway ?? null;
		$this->transaction_id  = $transaction_id ?? null;
		$this->date_paid       = $date_paid ?? null;
		$this->date_due        = $date_due;
	}

	// Setter methods
	public function setServiceId( string $service_id ) {
		$this->service_id = $service_id;
	}

	public function setUserId( int $user_id ) {
		$this->user_id = $user_id;
	}

	public function setBillingAddress( string $billing_address ) {
		$this->billing_address = $billing_address;
	}

	public function setInvoiceId( string $invoice_id ) {
		$this->invoice_id = $invoice_id;
	}

	public function setInvoiceType( string $invoice_type ) {
		$this->invoice_type = $invoice_type;
	}

	public function setProductId( int $product_id ) {
		$this->product_id = $product_id;
	}

	public function setOrderId( int $order_id ) {
		$this->order_id = $order_id;
	}

	public function setAmount( float $amount ) {
		$this->amount = $amount;
	}

	public function setFee( float $fee ) {
		$this->fee = $fee;
	}

	public function setPaymentStatus( string $payment_status ) {
		$this->payment_status = $payment_status;
	}

	public function setPaymentGateway( string $payment_gateway ) {
		$this->payment_gateway = $payment_gateway;
	}

	public function setTransactionId( string $transaction_id ) {
		$this->transaction_id = $transaction_id;
	}

	public function setDateCreated( string $date_created ) {
		$this->date_created = $date_created;
	}

	public function setDatePaid( string $date_paid ) {
		$this->date_paid = $date_paid;
	}

	public function setDateDue( string $date_due ) {
		$this->date_due = $date_due;
	}

	public function set_balance( $balance ) {
		$this->balance;
	}
	
	public function setTotal( float $total ) {
		$this->total = $total;
	}

	// Getter methods
	public function get_id() {
		return $this->id;
	}
	public function getServiceId() {
		return $this->service_id;
	}

	public function getUserId() {
		return $this->user_id;
	}

	public function getBillingAddress() {
		return $this->billing_address;
	}

	public function getInvoiceId() {
		return $this->invoice_id;
	}

	public function getInvoiceType() {
		return $this->invoice_type;
	}

	public function getProductId() {
		return $this->product_id;
	}

	public function getOrderId() {
		return $this->order_id;
	}

	public function getAmount() {
		return $this->amount;
	}

	public function getFee() {
		return $this->fee;
	}

	public function getPaymentStatus() {
		return $this->payment_status;
	}

	public function getPaymentGateway() {
		return $this->payment_gateway;
	}

	public function getTransactionId() {
		return $this->transaction_id;
	}

	public function getDateCreated() {
		return $this->date_created;
	}

	public function getDatePaid() {
		return $this->date_paid;
	}

	public function getDateDue() {
		return $this->date_due;
	}

	public function get_balance() {

		return $this->balance;
	}

	public function getTotal() {
		return $this->total;
	}

	/**
	 * Get invoice download URL
	 * 
	 * @param string $context Pass "admin"if in admin area.
	 * @return string $ur formatted invoice download url.
	 */
	public function download_url( $context = 'frontend' ) {
		if ( 'admin' === $context ) {
			$url = wp_nonce_url( 
				admin_url( 'admin-post.php?action=smartwoo_admin_download_invoice&invoice_id=' . $this->getInvoiceId() ), 
				'_sw_download_token', '_sw_download_token' 
			);
		} else {
			$url = wp_nonce_url( 
				add_query_arg(
					array(
						'smartwoo_action'	=> 'sw_download_invoice',
						'invoice_id'		=> $this->getInvoiceId(),
						'user_id'			=> get_current_user_id(),
					),
					get_permalink()
				),
				'_sw_download_token', '_sw_download_token' 
			);
		}

		return $url;
	}

	/**
	 * Retrieve customer's billing email
	 */
	public function get_billing_email() {
		return smartwoo_get_client_billing_email( $this->getUserId() );
	}
	// Helper method to convert database results to SmartWoo_Invoice objects
	public static function convert_array_to_invoice( $data ) {
		return new SmartWoo_Invoice(
			$data['invoice_id'],
			$data['product_id'],
			$data['amount'],
			$data['total'],
			$data['payment_status'],
			$data['date_created'],
			$data['user_id'],
			$data['billing_address'],
			$data['invoice_type'],
			$data['service_id'],
			$data['fee'],
			$data['order_id'],
			$data['payment_gateway'],
			$data['transaction_id'],
			$data['date_paid'],
			$data['date_due']
		);
	}
}
