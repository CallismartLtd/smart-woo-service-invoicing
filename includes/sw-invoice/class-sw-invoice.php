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
	/**
	 * @var int $id The Database ID for an invoice.
	 */
	private $id;

	/**
	 * @var string $service_id The public service ID associated with an invoice.
	 */
	private $service_id;

	/**
	 * @var int $user_id The ID of the user associated with an invoice.
	 */
	private $user_id;

	/**
	 * @var string $billing_address A formatted billing address of the user.
	 */
	private $billing_address;

	/**
	 * @var string $invoice_id The public invoice ID
	 */
	private $invoice_id;

	/**
	 * @var string $invoice_type The type of invoice.
	 */
	private $invoice_type;

	/**
	 * @var int $product_id The ID of the product associated with the invoice.
	 */
	private $product_id;

	/**
	 * @var int $order_id The ID of the order associated with the invoice.
	 */
	private $order_id;

	/**
	 * @var float $amount The primary price of the single product assciated witht he invoice.
	 */
	private $amount;

	/**
	 * @var float $fee The primary fee charged for the invoice.
	 */
	private $fee;

	/**
	 * @var string $payment_status The payment status of the invoice.
	 */
	private $payment_status;

	/**
	 * @var string $payment_gateway The payment option used to pay for the invoice.
	 */
	private $payment_gateway;

	/**
	 * @var string|int $transaction_id The transction ID used for invoice payment.
	 */
	private $transaction_id;

	/**
	 * @var string $date_created The date which the invoice was created.
	 */
	private $date_created;

	/**
	 * @var string $date_paid Teh date the invoce was paid.
	 */
	private $date_paid;

	/**
	 * @var string $date_due The date when the invoice is due.
	 */
	private $date_due;

	/**
	 * @var float $total The sum of the entire items in the invoice.
	 */
	private $total;

	/**
	 * Invoice meta data
	 * 
	 * @var array $meta_data An associative array of meta_name and meta_value
	 * @since 2.2.3
	 */
	protected $meta_data = array();

	// Constructor
	public function __construct() {}

	/**
	 |--------------------------------
	 | SETTERS
	 |--------------------------------
	 */
	/**
	 * Set ID
	 * 
	 * @param int $id The ID
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set the public invoice ID.
	 * @param string $invoice_id
	 */
	public function set_invoice_id( $invoice_id ) {
		$this->invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
	}

	// Deprecated, use set_invoice_id().
	public function setInvoiceId( $invoice_id ) {
		$this->set_invoice_id( $invoice_id );
	}

	/**
	 * Set service ID
	 * 
	 * @param string $service_id.
	 */
	public function set_service_id( $service_id ) {
		$this->service_id = sanitize_text_field( wp_unslash( $service_id ) );
	}

	// Deprecated, use set_service_id
	public function setServiceId( $service_id ) {
		$this->set_service_id( $service_id );
	}

	/**
	 * Set user ID
	 * 
	 * @param WP_User|WC_Customer|int $user Instace of WP_User or WC_Customer or int.
	 */
	public function set_user_id( $user ) {
		if ( ( $user instanceof WP_User ) ) {
			$this->user_id = $user->ID;
		} elseif( $user instanceof WC_Customer ) {
			$this->user_id = $user->get_id();
		} else {
			$this->user_id = intval( $user );
		}
	}
	
	//Deprecated use set_user_id.
	public function setUserId( $user_id ) {
		$this->set_user_id( $user_id );
	}

	/**
	 * Set billing address.
	 * 
	 * @param string $billing_address.
	 */
	public function set_billing_address( $billing_address ) {
		$this->billing_address = sanitize_text_field( wp_unslash( $billing_address ) );
	}
	
	// Deprecated use set_billing_address.
	public function setBillingAddress( $billing_address ) {
		$this->set_billing_address( $billing_address );
	}

	/**
	 * Set invoice type
	 * 
	 * @param string $invoice_type
	 */
	public function set_type( $invoice_type ) {
		$this->invoice_type = sanitize_text_field( wp_unslash( $invoice_type ) );
	}

	// Deprecated use set_invoice_type.
	public function setInvoiceType( string $invoice_type ) {
		$this->set_type( $invoice_type );
	}

	/**
	 * Set product ID
	 * 
	 * @param SmartWoo_Product|WC_Product|int $id
	 */
	public function set_product_id( $id ) {
		if ( $id instanceof SmartWoo_Product || $id instanceof WC_Product ) {
			$this->product_id = $id->get_id();
		} else {
			$this->product_id = absint( $id );
		}
	}
	
	// Deprecated use se_product_id.
	public function setProductId( int $product_id ) {
		$this->set_product_id( $product_id );
	}

	/**
	 * Set Order ID
	 * 
	 * @param WC_Order|int $order_id
	 */
	public function set_order_id( $order_id ) {
		if ( $order_id instanceof WC_Order ) {
			$this->order_id = $order_id->get_id();
		} else {
			$this->order_id = absint( $order_id );
		}
	}
	
	// Deprecated use set_order_id.
	public function setOrderId( $order_id ) {
		$this->set_order_id( $order_id );
	}

	/**
	 * Set amount for invoice.
	 * 
	 * @param float $amount.
	 */
	public function set_amount( $amount ) {
		$this->amount = floatval( $amount );
	}

	// Deprecated use set_amount.
	public function setAmount( $amount ) {
		$this->set_amount( $amount );
	}

	/**
	 * Set fee.
	 * 
	 * @param float $fee.
	 */
	public function set_fee( $fee ) {
		$this->fee = floatval( $fee );
	}

	// Deprecated use set_fee.
	public function setFee( $fee ) {
		$this->set_fee( $fee );
	}

	/**
	 * Set status
	 * 
	 * @param string $status
	 */
	public function set_status( $status ) {
		$this->payment_status = sanitize_text_field( wp_unslash( $status ) );
	}

	// Deprecated use set_status.
	public function setPaymentStatus( $payment_status ) {
		$this->set_status( $payment_status );
	}

	/**
	 * Set payment method
	 * 
	 * @param string $payment_method.
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_gateway = sanitize_text_field( wp_unslash( $payment_method ) );
	}

	public function setPaymentGateway( $payment_gateway ) {
		$this->set_payment_method( $payment_gateway );
	}

	/**
	 * Set transaction ID.
	 * 
	 * @param string $transaction ID.
	 */
	public function set_transaction_id( $value ) {
		$this->transaction_id = sanitize_text_field( wp_unslash( $value ) );
	}

	// Deprecated use set_transaction_id.
	public function setTransactionId( string $transaction_id ) {
		$this->set_transaction_id( $transaction_id );
	}

	/**
	 * Set date created
	 * 
	 * @param string $date_created.
	 */
	public function set_date_created( $date ){
		if ( 'now' === $date ) {
			$this->date_created = current_time( 'mysql' );
		} else {
			$this->date_created = sanitize_text_field( wp_unslash( $date ) );			
		}
	}

	// Deprecated, use set_date_created.
	public function setDateCreated( $date_created ) {
		$this->set_date_created( $date_created );
	}

	/**
	 * Set date paid.
	 * 
	 * @param string $date
	 */
	public function set_date_paid( $date ) {
		if( 'now' === $date ) {
			$this->date_paid = current_time( 'mysql' );
		} else {
			$this->date_paid = sanitize_text_field( wp_unslash( $date ) );
		}
	}

	// Deprecated use set_date_paid.
	public function setDatePaid( string $date_paid ) {
		$this->set_date_paid( $date_paid );
	}


	/**
	 * Set date due.
	 * 
	 * @param string $date
	 */
	public function set_date_due( $date ) {
		if( 'now' === $date ) {
			$this->date_due = current_time( 'mysql' );
		} else {
			$this->date_due = sanitize_text_field( wp_unslash( $date ) );
		}
	}

	// Deprecated, use set_date_due.
	public function setDateDue( $date ) {
		$this->set_date_due( $date );
	}
	
	/**
	 * Set total
	 * 
	 * @param float $total
	 */
	public function set_total( $total ) {
		$this->total = floatval( $total );
	}

	// Deprecated use set_total.
	public function setTotal( $total ) {
		$this->set_total( $total );
	}

	/**
	 |-----------------------------
	 | GETTERS
	 |-----------------------------
	 */
	/**
	 * Get the invoice real id
	 * 
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get public invoice ID
	 * 
	 * @return string
	 */
	public function get_invoice_id() {
		return $this->invoice_id;
	}

	public function getInvoiceId() {
		return $this->get_invoice_id();
	}

	/**
	 * Get associated service id.
	 * 
	 * @return string.
	 */
	public function get_service_id() {
		return $this->service_id;
	}

	public function getServiceId() {
		return $this->get_service_id();
	}

	/**
	 * Get user id
	 * 
	 * @return int.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	public function getUserId() {
		return $this->get_user_id();
	}

	/**
	 * Get billing address.
	 * @return string.
	 */
	public function get_billing_address( $context = 'view' ) {
		if ( empty( $this->billing_address ) && 'edit' === $context ) {
			$this->billing_address = smartwoo_get_user_billing_address( $this->user_id );
		} elseif ( empty( $this->billing_address ) && $this->is_guest_invoice() ) {
			$this->billing_address = $this->get_meta( 'billing_address' );
		}

		return $this->billing_address;
	}

	public function getBillingAddress() {
		return $this->get_billing_address();
	}

	/**
	 * Get invoice type
	 * 
	 * @return string.
	 */
	public function get_type() {
		return $this->invoice_type;
	}

	public function getInvoiceType() {
		return $this->get_type();
	}

	/**
	 * Get product id
	 * 
	 * @return id.
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get product object associated with invoice.
	 * 
	 * @return SmartWoo_Product|WC_Product|false
	 */
	public function get_product() {
		$product			= wc_get_product( $this->product_id );
		$GLOBALS['product']	= $product;

		return $product;
	}
	public function getProductId() {
		return $this->get_product_id();
	}

	/**
	 * Get order id associated with the invoice.
	 * 
	 * @return int|null
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * Get the order object for the order id
	 * 
	 * @return WC_Order|false
	 */
	public function get_order() {
		if ( empty( $this->order_id ) ) {
			return false;
		}

		return wc_get_order( $this->order_id );
	}
	public function getOrderId() {
		return $this->get_order_id();
	}

	/**
	 * Get amount
	 * 
	 * @return float.
	 */
	public function get_amount() {
		return $this->amount;
	}
	public function getAmount() {
		return $this->get_amount();
	}

	/**
	 * Get fee
	 * 
	 * @return float.
	 */
	public function get_fee() {
		return $this->fee;
	}
	public function getFee() {
		return $this->get_fee();
	}

	/**
	 * Get payment status
	 * 
	 * @return string.
	 */
	public function get_status() {
		return $this->payment_status;
	}
	public function getPaymentStatus() {
		return $this->get_status();
	}

	/**
	 * Get payment method
	 * 
	 * @return string
	 */
	public function get_payment_method() {
		return $this->payment_gateway;
	}
	public function getPaymentGateway() {
		return $this->get_payment_method();
	}

	/**
	 * Get transaction id
	 * 
	 * @return string|int
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}
	public function getTransactionId() {
		return $this->get_transaction_id();
	}

	/**
	 * Get date created
	 * 
	 * @return string
	 */
	public function get_date_created() {
		return $this->date_created;
	}
	public function getDateCreated() {
		return $this->get_date_created();
	}

	/**
	 * Get date paid
	 * 
	 * @return string
	 */
	public function get_date_paid() {
		return $this->date_paid;
	}
	public function getDatePaid() {
		return $this->get_date_paid();
	}

	/**
	 * Get the invoice due date
	 */
	public function get_date_due() {
		return $this->date_due;
	}
	public function getDateDue() {
		return $this->get_date_due();
	}

	/**
	 * Get invoice default total.
	 * @see filter "smartwoo_display_invoice_total" which is used to modify invoice total
	 * 		at runtime.
	 * 
	 * @return float
	 */
	public function get_total() {
		return $this->total;
	}
	public function getTotal() {
		return $this->get_total();
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
				admin_url( 'admin-post.php?action=smartwoo_admin_download_invoice&invoice_id=' . $this->get_invoice_id() ), 
				'_sw_download_token', '_sw_download_token' 
			);
		} else {
			$url = wp_nonce_url( 
				add_query_arg(
					array(
						'smartwoo_action'	=> 'sw_download_invoice',
						'invoice_id'		=> $this->get_invoice_id(),
					),
					get_permalink()
				),
				'_sw_download_token', '_sw_download_token' 
			);
		}

		return $url;
	}

	/**
	 * Get invoice pay url
	 */
	public function pay_url() {
		if ( empty( $this->order_id ) ) {
			return '';
		}
		return smartwoo_invoice_pay_url( $this->order_id  );
	}

	/**
	 * Get invoice preview URL.
	 * 
	 * @param string $context	The context to which the url is returned,
	 * 							values includes admin 		= The admin area invoice preview.
	 * 											frontend	= The client portal invoice preview.
	 * 											account		= The WooCommerce account page invoice preview.
	 * 
	 * @return string $preview_url The preview url.
	 * @see smartwoo_invoice_preview_url().
	 */
	public function preview_url( $context = '' ) {
		if ( 'admin' === $context ) {
			$preview_url = add_query_arg( 
				array( 
					'page' 			=> 'sw-invoices', 
					'tab' 			=> 'view-invoice', 
					'invoice_id'	=> $this->get_invoice_id() 
				), 
					admin_url( 'admin.php' ) 
			);
		} elseif ( 'frontend' === $context ) {
			$invoice_page_id	= get_option( 'smartwoo_invoice_page_id', 0 );
			$invoice_page_url 	= get_permalink( $invoice_page_id );
			$preview_url 		= add_query_arg(
				array(
					'invoice_page' => 'view_invoice',
					'invoice_id'   => $this->get_invoice_id(),
				),
				$invoice_page_url
			);
		} elseif ( 'account' === $context ) {
			$endpoint_url = wc_get_account_endpoint_url( 'smartwoo-invoice' );
			$preview_url = add_query_arg(
				array(
					'view_invoice' => true,
					'invoice_id'   => $this->get_invoice_id(),
				),
				$endpoint_url
			);
		} else {
			$preview_url = smartwoo_invoice_preview_url( $this->get_invoice_id() );
		}

		return $preview_url;
	}

	/**
	 * Retrieve customer's billing email.
	 * 
	 * @return string $email The customer's billing email.
	 */
	public function get_billing_email() {
		return smartwoo_get_client_billing_email( $this->get_user() );
	}

	/**
	 * Get the WC_Customer object of the invoice owner
	 * 
	 * @return WC_Customer
	 * @since 2.2.0
	 * @since 2.2.3 Added support for guest invoices.
	 */
	public function get_user() {
		$user = new WC_Customer( $this->get_user_id() );

		if ( $this->is_guest_invoice() ) {
			$user->set_email( $this->get_meta( 'billing_email' ) );
			$user->set_first_name( $this->get_meta( 'first_name' ) );
			$user->set_last_name( $this->get_meta( 'last_name' ) );
			
			// Set billing data.
			$user->set_billing_company( $this->get_meta( 'billing_company' ) );
			$user->set_billing_first_name( $this->get_meta( 'first_name' ) );
			$user->set_billing_last_name( $this->get_meta( 'last_name' ) );
			$user->set_billing_address( $this->get_meta( 'billing_address' ) );
			$user->set_billing_email( $this->get_meta( 'billing_email' ) );
			$user->set_billing_phone( $this->get_meta( 'billing_phone' ) );
		}

		return $user;
	}

	/*
	|--------------------------------
	| UTILITY METHODS
	|--------------------------------
	*/
	
	/**
	 * Check wether current user can access invoice.
	 * 
	 * @return bool True if current user can view invoice, false otherwise.
	 */
	public function current_user_can_access() {
		if ( $this->get_user_id() === get_current_user_id() ) {
			return true;
		}

		if ( $this->get_billing_email() === get_user_meta( get_current_user_id(), '_billing_email', true ) ) {
			return true;
		}

		// By default, a guest invoice is only accessible by guest users with the invoice link.
		if ( $this->is_guest_invoice() ) {
			return apply_filters( 'smartwoo_guest_invoice_access', true, $this );
		}

		return false;
	}

	/**
	 * Check whether an invoice is a guest invoice?
	 * 
	 * @return bool True if it's guest invoice, false otherwise
	 */
	public function is_guest_invoice() {
		return 'yes' === $this->get_meta( 'is_guest_invoice' );
	}

	// Helper method to convert database results to SmartWoo_Invoice objects
	public static function convert_array_to_invoice( $data ) {
		$self = new SmartWoo_Invoice();
		$self->set_id( $data['id'] );
		$self->set_invoice_id( $data['invoice_id'] );
		$self->set_product_id( $data['product_id'] );
		$self->set_amount( $data['amount'] );
		$self->set_total( $data['total'] );
		$self->set_status( $data['payment_status'] );
		$self->set_date_created( $data['date_created'] );
		$self->set_user_id( $data['user_id'] );
		$self->set_billing_address( $data['billing_address'] );
		$self->set_type( $data['invoice_type'] );
		$self->set_service_id( $data['service_id'] );
		$self->set_fee( $data['fee'] );
		$self->set_order_id( $data['order_id'] );
		$self->set_payment_method( $data['payment_gateway'] );
		$self->set_transaction_id( $data['transaction_id'] );
		$self->set_date_paid( $data['date_paid'] );
		$self->set_date_due( $data['date_due'] );

		/**
		 * Set up metadata
		 */
		$all_meta = SmartWoo_Invoice_Database::get_all_metadata( $self );
		$metadata = [];

		if ( ! empty( $all_meta ) ) {
			foreach( $all_meta as $meta ) {
				$metadata[$meta['meta_name']] = $meta['meta_value'];
			}
		}

		$self->meta_data = $metadata;
		return $self;
	}

	/**
	 |--------------------
	 | CRUD METHODS
	 |--------------------
	*/
	/**
	 * Save invoice to the database.
	 */
	public function save() {
		return SmartWoo_Invoice_Database::save( $this );
	}

	/**
	 * Set Meta Data on the object.
	 * 
	 * @param int|string $meta_name The name of the meta data.
	 * @param int|string $meta_value The value of the meta data.
	 */
	public function set_meta( $meta_name, $meta_value ) {
		$this->meta_data[sanitize_key( $meta_name )] = sanitize_text_field( wp_unslash( $meta_value ) );
	}

	/**
	 * Get a meta data on an invoice object.
	 * @param int|string $meta_name
	 * @return mixed
	 */
	public function get_meta( $meta_name ) {
		return isset( $this->meta_data[$meta_name] ) ? $this->meta_data[$meta_name] : false;
	}

	/**
	 * Get all the metadata on an invoice object.
	 * 
	 * @return array An associative array of meta_name => meta_value
	 */
	public function get_all_meta() {
		return $this->meta_data;
	}

	/**
	 * Delete All Meta data from the database.
	 */
	public function delete_all_meta() {
		return SmartWoo_Invoice_Database::delete_all_meta( $this );
	}
}
