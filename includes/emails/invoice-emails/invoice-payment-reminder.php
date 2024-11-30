<?php
/**
 * Invoice payment reminder for unpaid and due invoices.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Invoice_Payment_Reminder extends SmartWoo_Invoice_Mails {

    /**
     * The invoice
     * 
     * @var SmartWoo_Invoice $invoice
     */
    protected $invoice;

    /**
     * Static instance
     * 
     * @var SmartWoo_Invoice_Payment_Reminder $instance
     */
    public static $instance = null;

    /**
     * Class constructor
     */
    public function __construct( $invoice ) {
        $this->invoice = $invoice;
        self::$instance = $this;
        parent::__construct( 'Payment Reminder', self::get_template(), $invoice );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_invoice_payment_reminder', array( __CLASS__, 'send_mail' ) );
    }

    /**
     * Handle Email sending.
     */
    public static function send_mail( $invoice ) {
        if ( apply_filters( 'smartwoo_invoice_payment_reminder_mail', get_option( 'smartwoo_new_invoice_mail', 0 ) ) ) {
            $self = new self( $invoice );
            $self->send();
        }
    }

    /**
     * Default email template for payment reminder
     */
    public static function get_template() {
        $message  = '<h1>Important: Unpaid Invoice Reminder for "{{invoice_id}}"</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>We hope this email finds you well. We would like to bring to your attention an outstanding invoice associated with your account.</p>';
        
        if ( ! empty( self::$instance->invoice->get_service_id() ) ){
            $message .= '<p>To maintain uninterrupted service and avoid potential late fees, we kindly request your prompt attention to this matter.</p>';
        }
        $message .= '<p>Invoice Details:</p>';
        $message .= '<ul>';
        $message .= '<li>Balance Due: {{invoice_total}}</li>';
        $message .= '<li>Due Date: {{invoice_date_due}}</li>';
        $message .= '</ul>';
        $message .= '<p>To make the payment securely, please click the button below:</p>';
        $message .= '<p><a class="button" href="{{auto_login_payment_link}}">Pay Now</a></p>';
        $message .= '<p>If the button above does not work, you may use the following link:</p>';
        $message .= '<a href="{{auto_login_payment_link}}">{{auto_login_payment_link}}</a>';
        $message .= '<p>Please note: This link will expire in 24 hours. After that, you may need to log into your account manually to make the payment.</p>';

        $template = apply_filters( 'smartwoo_new_invoice_mail_template', $message, self::$instance );
        return $template;
    }
}

SmartWoo_Invoice_Payment_Reminder::init();