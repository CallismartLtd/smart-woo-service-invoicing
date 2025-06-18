<?php
/**
 * Invoice controller object.
 *
 * @author  Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Invoice controller class for handling all action hooks, form templates, invoice/order relationship management
 * and admin menus.
 * 
 * @since 2.2.3
 */
class SmartWoo_Invoice_Controller {
	/**
	 * @var SmartWoo_Invoice_Controller
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
		'payment_status' 	=> 'unpaid',
		'payment_method'	=> ''
		
	);


	/**
	 * Class constructor
	 */
	public function __construct() {
		// Test purposes
		// add_action( 'admin_post_smartwoo_admin_create_invoice_from_form',  array( __CLASS__, 'new_form_submit' ), 10 );
			
		add_action( 'wp_ajax_smartwoo_admin_create_invoice_from_form', array( __CLASS__, 'new_form_submit' ), 10 );
		add_action( 'wp_ajax_smartwoo_admin_edit_invoice_from_form', array( __CLASS__, 'edit_form_submit' ), 10 );
		add_filter( 'smartwoo_allowed_table_actions', array( __CLASS__, 'register_table_actions' ) );
		add_action( 'smartwoo_invoice_table_actions', array( __CLASS__, 'ajax_table_callback' ), 10, 2 );
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
	 * Admin invoice menu controller
	 */
	public static function menu_controller() {
		$tab = smartwoo_get_query_param( 'tab' );
		$menu_tabs = array(
			'Add New'	=> array(
				'href'		=> admin_url( 'admin.php?page=sw-invoices&tab=add-new-invoice' ),
				'active'	=> 'add-new-invoice'
			)
		);
		$title = 'Invoices';
		if ( ! empty( $tab ) ) {
			$add_new_menu	= $menu_tabs['Add New'];
			unset( $menu_tabs['Add New'] );
			$menu_tabs['Invoices'] = array(
				'href'		=> admin_url( 'admin.php?page=sw-invoices' ),
				'active'	=> ''
			);
			$menu_tabs['Add New'] = $add_new_menu;
		
			if ( 'add-new-invoice' !== $tab && 'sort-by' !== $tab ) {
				$title = 'Invoice Details';
				$menu_tabs['View'] = array(
					'href'      => admin_url( 'admin.php?page=sw-invoices&tab=view-invoice&invoice_id=' . smartwoo_get_query_param( 'invoice_id' ) ),
					'active'    => 'view-invoice'
				);

				$menu_tabs['Related Service'] = array(
					'href'      => admin_url( 'admin.php?page=sw-invoices&tab=related-service&invoice_id=' . smartwoo_get_query_param( 'invoice_id' ) ),
					'active'    => 'related-service'
				);
				$menu_tabs['Logs'] = array(
					'href'      => admin_url( 'admin.php?page=sw-invoices&tab=log&invoice_id=' . smartwoo_get_query_param( 'invoice_id' ) ),
					'active'    => 'log'
				);
				$menu_tabs['Edit'] = array(
					'href'      => admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . smartwoo_get_query_param( 'invoice_id' ) ),
					'active'    => 'edit-invoice'
				);
			}
		
		}
		
		SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $menu_tabs, 'tab' );
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		switch ( $tab ) {
			case 'add-new-invoice':
				wp_enqueue_script( 'smartwoo-jquery-timepicker' );
				include_once SMARTWOO_PATH . 'templates/invoice-admin-temp/add-invoice.php';
				break;
	
			case 'edit-invoice':
				wp_enqueue_script( 'smartwoo-jquery-timepicker' );
				SmartWoo_Invoice_Controller::edit_form();
				break;
	
			case 'sort-by':
				self::invoices_by_status();
				break;
	
			case 'view-invoice':
			case 'related-service':
			case 'log':
				self::view_invoice();
				break;
	
			default:
				self::dashboard();
				break;
		}
	}

	/**
	 * Invoice management dashboard.
	 */
	private static function dashboard(){
		$page	= smartwoo_get_query_param( 'paged', 1 );
		$limit 	= smartwoo_get_query_param( 'limit', 25 );

		$page_title 	= 'All Invoices';
		$all_invoices 	= SmartWoo_Invoice_Database::get_all_invoices( $page, $limit );
		$all_inv_count 	= SmartWoo_Invoice_Database::count_all();
		$total			= ceil( $all_inv_count / $limit );
		$paged 			= smartwoo_get_query_param( 'paged', 1 );
		$prev			= $paged - 1;
		$next			= $paged + 1;
		$status_counts	= array(
			'paid'      => SmartWoo_Invoice_Database::count_this_status( 'paid' ),
			'unpaid'    => SmartWoo_Invoice_Database::count_this_status( 'unpaid' ),
			'cancelled' => SmartWoo_Invoice_Database::count_this_status( 'cancelled' ),
			'due'       => SmartWoo_Invoice_Database::count_this_status( 'due' ),
		);

		$status = ''; // For template compatibility.
		
		include_once SMARTWOO_PATH . 'templates/invoice-admin-temp/dashboard.php';
	}

	/**
	 * View Invoices by status template
	 */
	private static function invoices_by_status() {
		$status	= smartwoo_get_query_param( 'status', 'pending' );
		$page	= smartwoo_get_query_param( 'paged', 1 );
		$limit 	= smartwoo_get_query_param( 'limit', 20 );

		if ( ! in_array( $status, array( 'due', 'cancelled', 'paid', 'unpaid' ), true ) ) {
			echo wp_kses_post( smartwoo_error_notice( 'Status parameter should not be manipulated! <a href="' . esc_url( admin_url( 'admin.php?page=sw-invoices' ) ) . '">Back</>' ) );
			return;
		}

		$page_title = ucfirst( $status ) . ' Invoices';
		smartwoo_set_document_title( $page_title );

		$all_invoices	= SmartWoo_Invoice_Database::get_invoices_by_payment_status( $status );
		$all_inv_count 	= absint( SmartWoo_Invoice_Database::count_this_status( $status ) );
		$total			= ceil( $all_inv_count / $limit );
		$paged 			= smartwoo_get_query_param( 'paged', 1 );
		$prev			= $paged - 1;
		$next			= $paged + 1;
		
		$status_counts	= array(
			'paid'      => SmartWoo_Invoice_Database::count_this_status( 'paid' ),
			'unpaid'    => SmartWoo_Invoice_Database::count_this_status( 'unpaid' ),
			'cancelled' => SmartWoo_Invoice_Database::count_this_status( 'cancelled' ),
			'due'       => SmartWoo_Invoice_Database::count_this_status( 'due' ),
		);

		include_once SMARTWOO_PATH . 'templates/invoice-admin-temp/dashboard.php';
	}

	/**
	 * View Invoice Template
	 */
	private static function view_invoice() {
		$invoice_id = smartwoo_get_query_param( 'invoice_id' );
		$invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

		if ( $invoice ) {
			$service		= SmartWoo_Service_Database::get_service_by_id( $invoice->get_service_id() );
			$status_class	= ! empty( $service ) ? strtolower( str_replace( array( ' ', '(', ')'), array( '-', '', '' ), smartwoo_service_status( $service ) ) ) : '';
		}

		switch ( smartwoo_get_query_param( 'tab' ) ){
			case 'related-service':
				$page_file = SMARTWOO_PATH .'templates/invoice-admin-temp/view-related-services.php';
				break;
			case 'log':
				$feature = 'invoice logs';
				$page_file = apply_filters( 'smartwoo_invoice_log_template', smartwoo_pro_feature_template() );
				break;
			default:
				$page_file = SMARTWOO_PATH . 'templates/invoice-admin-temp/view-invoice.php';
		}

		file_exists( $page_file) ? include_once $page_file : '';
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

		$errors = apply_filters( 'smartwoo_invoice_form_error', self::instance()->check_errors( 'edit invoice' ) );

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
		$invoice_id	= smartwoo_get_query_param( 'invoice_id' );
		$invoice	= SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
		$selected	= $invoice ? $invoice->get_user_id() . '|' . $invoice->get_user()->get_email() : '';
		
		if ( $invoice ) {

			if ( ! in_array( $invoice->get_type(), smartwoo_supported_invoice_types() ) ) {
				add_filter( 'smartwoo_supported_invoice_types', function( $types ) use ( $invoice ){
					$types[$invoice->get_type()] = $invoice->get_type() . ' (Custom Invoice type)';
	
					return $types;
				});
			}


			if ( $invoice->is_guest_invoice() ) {
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
		$invoice->set_payment_method( $args['payment_method'] );

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
			$invoice->save();
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
	private static function update_invoice( $is_guest_invoice ) {
		$args		= self::instance()->get_form_data();
		$invoice	= SmartWoo_Invoice_Database::get_invoice_by_id( $args['invoice_id'] );

		if ( ! $invoice ) {
			return new WP_Error( 'invalid_invoice_id', 'The invoice does not exist.', array( 'status' => 404 ) );
		}

		$amount		= wc_get_product( $args['product_id'] ) ? wc_get_product( $args['product_id'] )->get_price() : 0;
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
			$invoice->save();
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
	public function check_errors( $context = 'new invoice' ) {
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
		$invoice_type	= ! empty( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : '';
		if ( empty( $invoice_type ) ) {
			$errors[] = 'Please select a valid Invoice Type.';
		}
		$this->form_fields['invoice_type'] = $invoice_type;

		$invoice_type_exists 	= false;
		
		if ( ! empty( $service_id ) ) {
			$invoice_type_exists = smartwoo_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );
		}

		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : '';
		$this->form_fields['payment_status'] = $payment_status;

		if ( $invoice_type_exists && 'new invoice' === $context ) {
			$errors[] = 'This Service has "' . $invoice_type . '" that is ' . $payment_status;
		}

		$due_date	= isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : current_time( 'mysql' );				
		$this->form_fields['due_date'] = $due_date;
		
		$fee	= isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0;
		$this->form_fields['fee'] = $fee;


		if ( isset( $_POST['invoice_id'] ) ) {
			$this->form_fields['invoice_id'] = sanitize_text_field( wp_unslash( $_POST['invoice_id'] ) );
		}

		if ( isset( $_POST['payment_method'] ) ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$gateway_id			= sanitize_text_field( wp_unslash( $_POST['payment_method'] ) );
			$payment_method		= isset( $available_gateways[$gateway_id] ) ? $gateway_id : '';
			$this->form_fields['payment_method'] = $payment_method;
		}

		return ( ! empty( $errors ) ) ? $errors : false;
	}

	/**
	 * Add allowed table actions to the sw-table.
	 * 
	 * @param array $actions Allowed actions from the filter.
	 * @return array $actions
	 */
	public static function register_table_actions( $actions ) {
		$actions[] = 'paid';
		$actions[] = 'unpaid';
		$actions[] = 'cancelled';
		$actions[] = 'due';
		$actions[] = 'delete';

		return $actions;
	}

    /**
     * Ajax callback for order table actions
     * 
     * @param string $selected_action The selected action.
     * @param mixed $data The data to be processed.
     */
    public static function ajax_table_callback( $selected_action, $data ) {
		if ( ! is_array( $data ) ) {
			$data = (array) $data;
		}

		$response = array( 'message' => 'Invalid actions' );

		foreach ( $data as $invoice_id ) {
			$invoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

			if ( ! $invoice ) {
				continue;
			}
			switch( $selected_action ) {

				case 'paid':
					$paid = false;
					if ( 'paid' !== $invoice->get_status() ) {
						$order = $invoice->get_order();
						if ( $order ) {
							$transction_id = $order->get_transaction_id() ? $order->get_transaction_id() : 'smartwoo|' . $order->get_id() . '|' . time();
							$order->payment_complete( $transction_id ); // Actions fired by this method should handle invoice statucs update.
							$paid = true;
						} else {
							$invoice->set_status( 'paid' );
							$paid = true;
						}

						// Manually update invoice if these actions did not updated the invoice status.
						if ( $paid && 'paid' !== $invoice->get_status() ) {
							$invoice->set_status( 'paid' );
							$invoice->set_date_paid( 'now' );
							$invoice->save();

						}
					}

					if ( $paid ) {
						$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' has been marked as paid';
					}
					break;
				case 'unpaid':
					if ( 'unpaid' !== $invoice->get_status() ) {
						$invoice->set_status( 'unpaid' );
						$invoice->save();
					}
					$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' has been marked as unpaid';
					break;
				case 'due':
					if ( 'due' !== $invoice->get_status() ) {
						$invoice->set_status( 'due' );
						$invoice->save();
					}
					$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' has been marked as due';
					break;
				case 'cancelled': 
					if ( 'cancelled' !== $invoice->get_status() ) {
						$invoice->set_status( 'cancelled' );
						$invoice->save();
					}
					$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' has been cancelled';
					break;
				case 'delete':
					if ( $invoice->delete() ) {
						$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' has been deleted';
					} else {
						$response['message'] = 'The selected invoice' . ( count( $data ) > 1 ? 's' : '' ) . ' cannot be deleted.';
					}
					break;


			}
		}

		wp_send_json_success( $response );

	}

}

SmartWoo_Invoice_Controller::instance();
