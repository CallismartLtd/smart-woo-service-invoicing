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
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_post_smartwoo_admin_create_invoice_from_form', array( __CLASS__, 'new_invoice_form_handler' ), 10 );

	}

	/**
	 * Singleton instance of current class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	/**
     * New invoice form handler.
     * 
     * @since 2.0.15
     */
    public static function new_invoice_form_handler() {
		
        if ( isset( $_POST['create_invoice'], $_POST['sw_create_invoice_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_create_invoice_nonce'] ) ), 'sw_create_invoice_nonce' ) ) {
            if ( ! isset( $_POST['smartwoo_send_new_invoice_mail'] ) || 'yes' !== $_POST['smartwoo_send_new_invoice_mail'] ) {
                remove_action( 'smartwoo_new_invoice_created', array( 'SmartWoo_New_Invoice_Mail', 'send_mail' ) );
            }
            
            $user_id        = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : -1;
            $product_id     = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
            $invoice_type	= ! empty( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : 'Billing';
            $service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : '';
            $due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : current_time( 'mysql' );				
            $fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : 0;
            $payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : 'unpaid';
            // Check for a duplicate unpaid invoice for a service.
            $invoice_type_exists = smartwoo_evaluate_service_invoices( $service_id, $invoice_type, 'unpaid' );
    
            // Validate inputs.
            $errors = array();
            if ( $invoice_type_exists ) {
                $errors[] = 'This Service has "' . $invoice_type . '" that is ' . $payment_status;
            }
    
            if ( empty( $user_id ) ) {
                $errors[] = 'Select a user.';
            }
    
            if ( empty( $product_id ) ) {
                $errors[] = 'Service Product is required.';
            }
    
            if ( empty( $invoice_type ) ) {
                $errors[] = 'Please select a valid Invoice Type.';
            }

            $errors = apply_filters( 'smartwoo_handling_new_invoice_form_error', $errors );
    
            if ( ! empty( $errors ) ) {
                smartwoo_set_form_error( $errors );
                wp_safe_redirect( admin_url( 'admin.php?page=sw-invoices&tab=add-new-invoice' ) );
                exit;
            }

            $createdInvoiceID = smartwoo_create_invoice( $user_id, $product_id, $payment_status, $invoice_type, $service_id, $fee, $due_date );

            if ( $createdInvoiceID ) {
                do_action( 'smartwoo_handling_new_invoice_form_success', $createdInvoiceID );
                $detailsPageURL = esc_url( admin_url( "admin.php?page=sw-invoices&tab=view-invoice&invoice_id=$createdInvoiceID" ) );
                smartwoo_set_form_success( 'Invoice created successfully! <a href="' . esc_url( $detailsPageURL ) .'">' . __( 'View Invoice Details', 'smart-woo-service-invoicing' ) .'</a>' );
            }
            wp_safe_redirect( admin_url( 'admin.php?page=sw-invoices&tab=add-new-invoice' ) );
            exit; 
        }
        smartwoo_set_form_error( 'Something went wrong' );
        wp_safe_redirect( admin_url( 'admin.php?page=sw-invoices&tab=add-new-invoice' ) );
        exit;
    }


	/**
	 * Helper method to validate invoice form submission.
	 * 
	 * @return true|WP_Error True when the submision is valid, WP_Error otherwise
	 */
	private static function validate_form_post() {

	}
}

SmartWoo_Invoice_Form_Controller::instance();
/**
 * Edit invoice page controller.
 */
function smartwoo_edit_invoice_page() {

	$invoice_id	= isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$page_html	= '<h2>Edit Invoice 📄</h2>';
	if ( empty( $invoice_id ) ) {
		$page_html .= smartwoo_error_notice( 'Missing Invoice ID' );
		return $page_html;
	}
	$existingInvoice = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( empty( $existingInvoice ) ) {
		return smartwoo_error_notice( 'Invoice not found' );
	}

	if ( isset( $_POST['sw_update_invoice'], $_POST['sw_edit_invoice_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_edit_invoice_nonce'] ) ), 'sw_edit_invoice_nonce' ) ) {
		// Sanitize and validate inputs
		$user_id        = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : $existingInvoice->getUserId();
		$product_id     = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : $existingInvoice->getProductId();
		$invoice_type   = isset( $_POST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_POST['invoice_type'] ) ) : $existingInvoice->getInvoiceType();
		$service_id     = isset( $_POST['service_id'] ) ? sanitize_text_field( wp_unslash( $_POST['service_id'] ) ) : null;
		$fee            = isset( $_POST['fee'] ) ? floatval( $_POST['fee'] ) : $existingInvoice->getFee();
		$payment_status = isset( $_POST['payment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_status'] ) ) : $existingInvoice->getPaymentStatus();
		$due_date       = isset( $_POST['due_date'] ) ? sanitize_text_field( wp_unslash( $_POST['due_date'] ) ) : null;

		// Validate inputs
		$errors = array();
		if ( empty( $user_id ) ) {
			$errors[] = 'Select a user.';
		}

		if ( empty( $product_id ) ) {
			$errors[] = 'Select a product.';
		}

		if ( ! empty( $errors ) ) {

			return smartwoo_error_notice( $errors );
		}

		if ( empty( $errors ) ) {

			$amount = wc_get_product( $product_id )->get_price();
			$total = $amount + ( $fee ?? 0 );

			$existingInvoice->setAmount( floatval( $amount ) );
			$existingInvoice->setTotal( floatval( $total ) );
			$existingInvoice->setUserId( absint( $user_id ) );
			$existingInvoice->setProductId( absint( $product_id ) );
			$existingInvoice->setInvoiceType( sanitize_text_field( $invoice_type ) );
			$existingInvoice->setServiceId( sanitize_text_field( $service_id ) );
			$existingInvoice->setFee(floatval( $fee ) );
			$existingInvoice->setPaymentStatus( sanitize_text_field( $payment_status ) );
			$existingInvoice->setDateDue( sanitize_text_field( $due_date ) );

			// Call the method to update the invoice in the database
			$updated = SmartWoo_Invoice_Database::update_invoice( $existingInvoice );

			// Check the result
			if ( $updated ) {
				if ( 'paid' === $payment_status ) {
					$existingInvoice->get_order() ? $existingInvoice->get_order()->update_status( 'completed' ): '';
				}
				$page_html .= esc_html( "Invoice updated successfully! ID: $invoice_id" );
			} else {
				$page_html .= 'Failed to update the invoice.';
			}
		}
	}
	smartwoo_set_document_title( 'Edit Invoice' );
	$page_html .= smartwoo_edit_invoice_form( $existingInvoice );
	return $page_html;
}

