<?php
/**
 * Invoice form controller object.
 *
 * @author  Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Invoice form controller
 * 
 * @since 2.2.3
 */
class SmartWoo_Invoice_Form_Controller{
	/**
	 * @var SmartWoo_Invoice_Form_Controller
	 */
	private static $instance = null;

	/**
	 * Stores form fields.
	 * 
	 * @var array
	 */
	private $form_fields = array(
		'invoice_id'		=> '',
		'product_id'		=> 0,
		'fee'				=> 0,
		'service_id'		=> '',
		'user_id'			=> 0,
		'user_email'		=> '',
		'due_date'			=> 'now',
		'invoice_type'		=> '',
		'payment_status' 	=> 'unpaid'
		
	);


	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_smartwoo_admin_create_invoice_from_form', array( __CLASS__, 'new_form_submit' ), 10 );
		add_action( 'wp_ajax_smartwoo_admin_edit_invoice_from_form', array( __CLASS__, 'edit_form_submit' ), 10 );
	}

	/**
	 * Singleton instance of current class.
	 * 
	 * @return self
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
     * New invoice form submission handler.
     * 
     * @since 2.0.15 Created.
	 * @since 2.2.3 Now processes Invoice form form through ajax.
     */
    public static function new_form_submit() {
		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
            wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ), 401 );
        }

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ), 403 );
		}

		if ( ! isset( $_POST['smartwoo_send_new_invoice_mail'] ) || 'yes' !== $_POST['smartwoo_send_new_invoice_mail'] ) {
			add_filter( 'smartwoo_new_invoice_mail', '__return_false' );
		}
		
		$errors = apply_filters( 'smartwoo_invoice_form_error', self::instance()->check_errors() );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $errors, true ) ), 200 );
		}

		$is_guest_invoice = isset( $_POST['is_guest_invoice'] ) && 'yes' === $_POST['is_guest_invoice'];

		$invoice = self::create_invoice( $is_guest_invoice );

		if ( is_wp_error( $invoice ) ) {
			wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $invoice->get_error_message() ) ), 200 );
		}
		do_action( 'smartwoo_handling_new_invoice_form_success', $invoice );
		wp_send_json_success( array( 'message' => 'Invoice Created', 'redirect_url' => $invoice->preview_url( 'admin' ) ), 200 );
    }

	/**
	 * Edit invoice form handler.
	 */
	public static function edit_form_submit() {
		if ( ! check_ajax_referer( 'smart_woo_nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => 'Action failed basic authentication.' ), 401 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ), 403 );
		}

		$errors = apply_filters( 'smartwoo_invoice_form_error', self::instance()->check_errors() );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $errors, true ) ), 200 );
		}

		$is_guest_invoice	= isset( $_POST['is_guest_invoice'] ) && 'yes' === $_POST['is_guest_invoice'];
		$updated			= self::update_invoice( $is_guest_invoice );

		if ( is_wp_error( $updated ) ) {
			wp_send_json_error( array( 'htmlContent' => smartwoo_error_notice( $updated->get_error_message() ) ), 200 );
		}

		do_action( 'smartwoo_handling_edit_invoice_form_success', $updated );
		wp_send_json_success( array( 'message' => 'Invoice Updated', 'redirect_url' => admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $updated->get_invoice_id() ) ), 200 );
	}

	/**
	 * Invoice edit form renderer.
	 */
	public static function edit_form() {
		smartwoo_set_document_title( 'Edit Invoice' );
		$invoice_id	= isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$invoice	= SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
		$selected	= $invoice ? $invoice->get_user_id() . '|' . $invoice->get_user()->get_email() : '';
		
		if ( $invoice && $invoice->is_guest_invoice() ) {
			$selected = -1 . '|' . $invoice->get_billing_email();
			add_filter( 'smartwoo_dropdown_users_add', function() use( $selected, $invoice ) {
				return '<option class="sw-guest-option" value="' . esc_attr( $selected ) .'"' . selected( $selected, $selected, false ) . '>' . esc_html( $invoice->get_user()->get_first_name() . ' ' . $invoice->get_user()->get_last_name() . ' ('. $invoice->get_billing_email() . ')' ) .'</option>';
			});

			add_filter( 'smartwoo_dropdown_user_meta', function() use( $invoice ){
				return 
					'<div class="sw-invoice-form-meta">
						<input type="hidden" name="is_guest_invoice" value="yes"/>
						<input type="hidden" name="first_name"  value="' . esc_attr( $invoice->get_user()->get_billing_first_name() ) . '"/>
						<input type="hidden" name="last_name" value="' . esc_attr( $invoice->get_user()->get_billing_last_name() ) . '"/>
						<input type="hidden" name="billing_email" value="' . esc_attr( $invoice->get_billing_email() ) . '"/>
						<input type="hidden" name="billing_company" value="' . esc_attr( $invoice->get_user()->get_billing_company() ) . '"/>
						<input type="hidden" name="billing_address" value="' . esc_attr( $invoice->get_billing_address() ) . '"/>
						<input type="hidden" name="billing_phone" value="' . esc_attr( $invoice->get_user()->get_billing_phone() ) . '"/>
					</div>' 
				;
			});
		}
		include_once SMARTWOO_PATH . 'templates/invoice-admin-temp/edit-invoice.php';
	}

	/**
	 * Helper method to create invoice.
	 * 
	 * @param bool $is_guest_invoice Whether the invoice is a guest invoice, defaults to false.
	 * @return SmartWoo_Invoice|WP_Error SmartWoo_Invoice object when the submision is valid, WP_Error otherwise
	 */
	private static function create_invoice( $is_guest_invoice = false ) {
		$args 		= self::instance()->get_form_data();
		$amount		= wc_get_product( $args['product_id'] ) ? wc_get_product( $args['product_id'] )->get_price() : 0;
		$total		= $amount + $args['fee'];
		$invoice	= new SmartWoo_Invoice();

		$invoice->set_invoice_id( smartwoo_generate_invoice_id() );
		
		$invoice->set_product_id( $args['product_id'] );
		$invoice->set_amount( $amount );
		$invoice->set_total( $total );
		$invoice->set_status( $args['payment_status'] );
		$invoice->set_date_created( current_time( 'mysql' ) );
		$invoice->set_user_id( $args['user_id'] );
		$invoice->set_type( $args['invoice_type'] );
		$invoice->set_service_id( $args['service_id'] );
		$invoice->set_fee( $args['fee'] );
		$invoice->set_date_due( $args['due_date'] );

		if ( 'paid' === $args['payment_status'] ) {
			$invoice->set_date_paid( 'now' );
		}

		if ( $is_guest_invoice ) {
			$guest_data = self::instance()->get_posted_guest_data();
			foreach( $guest_data as $key => $value ) {
				$invoice->set_meta( $key, $value );
			}

			$invoice->set_user_id( 0 );
			$invoice->set_billing_address( $guest_data['billing_address'] );


		} else {
			// Billing Address is typically set for guests.
			$invoice->set_billing_address( smartwoo_get_user_billing_address( $args['user_id'] ) );

		}

		if ( 'unpaid' === $args['payment_status'] ) {
			$order_id = smartwoo_generate_pending_order( $invoice );
			$invoice->set_order_id( $order_id );
		}

		return $invoice->save() ? $invoice: new WP_Error( 'invoice_creation_error', 'Invoice creation failed', array( 'status' => 503 ) );

	}

	/**
	 * Helper method to update invoice.
	 * 
	 * @param bool $is_guest_invoice Whether we are handling a guest invoice update or not?
	 * @return SmartWoo_Invoice|WP_Error SmartWoo_Invoice object when the submision is valid, WP_Error otherwise
	 */
	public static function update_invoice( $is_guest_invoice ) {
		$args		= self::instance()->get_form_data();
		$invoice	= SmartWoo_Invoice_Database::get_invoice_by_id( $args['invoice_id'] );

		if ( ! $invoice ) {
			return new WP_Error( 'invalid_invoice_id', 'The invoice does not exist.', array( 'status' => 404 ) );
		}

		$amount		= wc_get_product( $args['product_id'] )->get_price();
		$total		= $amount + $args['fee'];

		$invoice->set_product_id( $args['product_id'] );
		$invoice->set_amount( $amount );
		$invoice->set_total( $total );
		$invoice->set_status( $args['payment_status'] );
		$invoice->set_date_due( $args['due_date'] );
		$invoice->set_type( $args['invoice_type'] );
		$invoice->set_user_id( $args['user_id'] );
		$invoice->set_service_id( $args['service_id'] );
		$invoice->set_fee( $args['fee'] );

		if ( $is_guest_invoice ) {
			$guest_data = self::instance()->get_posted_guest_data();
			foreach( $guest_data as $key => $value ) {
				$invoice->set_meta( $key, $value );
			}

			$invoice->set_user_id( 0 );
			$invoice->set_billing_address( $guest_data['billing_address'] );
		} else {
			// Update the is_guest_invoice meta data.
			$invoice->delete_meta( 'is_guest_invoice', true );
			// Billing Address is typically set for guests.
			$invoice->set_billing_address( smartwoo_get_user_billing_address( $args['user_id'] ) );
		}

		// Pending orders are created only when invoice is unpaid and no pending order exists for it.
		if ( 'unpaid' === $args['payment_status'] && ! $invoice->get_order() ) {
			$order_id = smartwoo_generate_pending_order( $invoice );
			$invoice->set_order_id( $order_id );
		}

		return $invoice->save() ? $invoice : new WP_Error( 'invoice_update_error', 'Invoice update failed', array( 'status' => 503 ) );
	}

	/**
	 * Get the submitted form data.
	 * 
	 * @return array
	 */
	private function get_form_data() {
		return $this->form_fields;
	}

	/**
	 * The the posted guest data
	 * 
	 * @return array
	 */
	private function get_posted_guest_data() {
		$guest_data = array(
			'first_name'		=> isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
			'last_name'			=> isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
			'billing_address'	=> isset( $_POST['billing_address'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address'] ) ) : '',
			'billing_phone'		=> isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '',
			'billing_company'	=> isset( $_POST['billing_company'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) : '',
			'billing_email'		=> isset( $_POST['billing_email'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_email'] ) ) : '',
			'is_guest_invoice'	=> isset( $_POST['is_guest_invoice'] ) ? sanitize_text_field( wp_unslash( $_POST['is_guest_invoice'] ) ) : '',
		);

		return $guest_data;
	}

	/**
	 * Form error checker
	 * 
	 * @return array|false An array of errors or false when no error is found.
	 */
	public function check_errors() {
		$errors = array();
		/**
		 * Check user data.
		 */
		$user_data		= isset( $_POST['user_data'] ) ? sanitize_text_field( wp_unslash( $_POST['user_data'] ) ) : '';
		if ( ! $user_data ) {
			$error[] = 'Please select a user.';
		}

		$user_id	= false;
		$user_email	= false;
		
		if ( $user_data ) {
			$parts = explode( '|', $user_data );
			if ( count( $parts ) > 1 ) {
				$user_id	= intval( $parts[0] );
				$user_email = sanitize_text_field( $parts[1] );
			}
		}

		if ( ! $user_email || ! is_email( $user_email ) ) {
			$errors[] = 'The user\'s email is not valid.';
		}
		
		if ( empty( $user_id ) ) {
			$errors[] = 'The user does not have a valid ID.';
		}

		$this->form_fields['user_id'] 		= $user_id;
		$this->form_fields['user_email']	= $user_email;

		/**
		 * Check product data.
		 */
		$product_id	= isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		if ( empty( $product_id ) && ! SmartWoo::pro_is_installed() ) {
			$errors[] = 'Add a product to the invoice.';
			$product = wc_get_product( $product_id );
			if ( ! $product || ! is_a( $product, 'SmartWoo_Product') ) {
				$errors[] = 'The selected product does not exist.';
			}
		}

		$this->form_fields['product_id'] = $product_id;

		/**
		 * Check Service ID field for possible duplicate invoice type for a service.
		 */
		$service_id	= isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
		$this->form_fields['service_id'] = $service_id;

		/**
		 * Check invoice Type
		 */
		$invoice_type	= ! empty( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : 'Billing';
		if ( empty( $invoice_type ) ) {
			$errors[] = 'Please select a valid Invoice Type.';
		}
		$this->form_fields['invoice_type'] = $invoice_type;

		$invoice_type_exists 	= false;
		
		if ( ! empty( $service_id ) ) {
			$invoice_type_exists = smartwoo_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );
		}

		if ( $invoice_type_exists ) {
			$errors[] = 'This Service has "' . $invoice_type . '" that is ' . $payment_status;
		}

		$due_date	= isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : current_time( 'mysql' );				
		$this->form_fields['due_date'] = $due_date;
		
		$fee	= isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0;
		$this->form_fields['fee'] = $fee;

		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : 'unpaid';
		$this->form_fields['payment_status'] = $payment_status;

		if ( isset( $_POST['invoice_id'] ) ) {
			$this->form_fields['invoice_id'] = sanitize_text_field( wp_unslash( $_POST['invoice_id'] ) );
		}

		return ( ! empty( $errors ) ) ? $errors : false;
	}
}

SmartWoo_Invoice_Form_Controller::instance();
