<?php
/**
 * Mails that are sent when there is a new invoice.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_New_Invoice_Mail extends SmartWoo_Invoice_Mails {

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

        parent::__construct( 'New Invoice', $this->get_template(), $invoice );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_new_invoice_created', array( __CLASS__, 'send_mail' ) );
    }

    /**
     * Handle Email sending.
     */
    public static function send_mail( $invoice ) {
        if ( apply_filters( 'smartwoo_new_invoice_mail', get_option( 'smartwoo_new_invoice_mail', 0 ) ) ) {
            $self = new self( $invoice );
            $self->send();
        }
    }

    /**
     * Default email template
     */
    public function get_template() {
        $message  = '<h1>New invoice "{{invoice_id}}"</h1>';
		$message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
		$message .= '<p>New invoice has been generated for you</p>';
        $message .= '<p>Status: {{invoice_status}}</p>';
        $message .= '<p>Due On: {{invoice_date_due}}</p>';
        $message .= '<p>Invoice Date: {{invoice_date_created}}</p>';
        if ( 'paid' === $this->invoice->get_status() ) {
            $message .= '<p>Date Paid: {{invoice_date_paid}}</p>';

        }

        $message .= '<br><h3>Invoice Details</h3>';
        $message .= '<ul>';
        $message .= '<li>Invoice ID: {{invoice_id}}</li>';
        $message .= '{{invoice_items}}';
        $message .= '<li>Total: {{invoice_total}}</li>';
        $message .= '</ul>';
        if ( 'unpaid' === $this->invoice->get_status() ) {

            $message .= '<p>To proceed with the payment, please click the button below:</p>';
            $message .= '<p><a class="button" href="{{auto_login_payment_link}}">Pay Now</a></p>';
            $message .= '<p>If the button above is not working, you can use the following link to make the payment:</p>';
            $message .= '<p><strong>{{auto_login_payment_link}}</strong></p>';
            $message .= '<p>Please note: the above link will expire after 24hrs, you may need to log into your account manually when it expires</p>';

        }
		
        $template = apply_filters( 'smartwoo_new_invoice_mail_template', $message );
        return $template;
    }
}

SmartWoo_New_Invoice_Mail::init();