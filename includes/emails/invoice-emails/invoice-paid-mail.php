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
}

SmartWoo_Invoice_Paid_Mail::init();