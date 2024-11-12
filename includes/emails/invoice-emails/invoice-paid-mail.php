<?php
/**
 * Mails that are sent when invoice is paid.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Invoice_Paid_Mail extends SmartWoo_Invoice_Mails {

    /**
     * The invoice
     * 
     * @var SmartWoo_Invoice $invoice
     */
    protected $invoice;

    /**
     * Class constructor
     */
    public function __construct( $invoice ) {
        $this->invoice = $invoice;

        parent::__construct( 'Invoice Payment Confirmation', $this->get_template(), $invoice );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_invoice_is_paid', array( __CLASS__, 'send_mail' ) );
        add_action( 'admin_post_smartwoo_invoice_paid_mail', array( __CLASS__, 'start_preview_buffer' ) );
    }

    /**
     * Handle Email sending.
     */
    public static function send_mail( $invoice ) {
        if ( apply_filters( 'smartwoo_paid_invoice_mail', get_option( 'smartwoo_invoice_paid_mail', 0 ) ) ) {
            $self = new self( $invoice );
            $self->send();
        }
    }

    /**
     * Default email template
     */
    public function get_template() {
        $message  = '<h1>Payment Receipt "{{invoice_id}}"</h1>';
		$message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
		$message .= '<p>This is a payment receipt for invoice #{{invoice_id}} paid on {{invoice_date_paid}}.</p>';
        $message .= '<br><h3>Invoice Details</h3>';

        $message .= '<ul>';
        $message .= '<li>Invoice ID: {{invoice_id}}</li>';
        $message .= '{{invoice_items}}';
        $message .= '<li>Total: {{invoice_total}}</li>';
        $message .= '</ul>';
		
        $template = apply_filters( 'smartwoo_invoice_paid_mail_template', $message );
        return $template;
    }

        /**
     * Email preview buffer
     */
    public static function start_preview_buffer() {
        
        $invoice    = new SmartWoo_Invoice();
      
        $invoice->set_invoice_id( smartwoo_generate_invoice_id() );
        $invoice->set_user_id( get_current_user_id() );
        $invoice->set_product_id( self::get_random_product_id() );
        $invoice->set_amount( wp_rand( 200, 500 ) );
        $invoice->set_total( wp_rand( 200, 500 ) );
        $invoice->set_status( 'unpaid' );
        $invoice->set_date_created( 'now' );
        $invoice->set_billing_address( smartwoo_get_client_billing_email( get_current_user_id() ) );
        $invoice->set_date_paid( 'now' );
        $invoice->set_service_id( smartwoo_generate_service_id( 'Awesome Service' ) );
        $invoice->set_type( 'Billing' );
        $invoice->set_fee( wp_rand( 200, 500 ));
        $invoice->set_date_due( 'now' );
        $self   = new self( $invoice );
        $self->preview_template();
    }
}

SmartWoo_Invoice_Paid_Mail::init();