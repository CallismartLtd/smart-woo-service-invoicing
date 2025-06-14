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
     * Email ID
     * 
     * @var string $id
     */
    public static $id = 'smartwoo_payment_reminder_to_client';

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
    public static $instance = 'SmartWoo_Invoice_Payment_Reminder';

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
        add_filter( 'smartwoo_register_email_templates', array( __CLASS__, 'register_template' ) );
    }

    /**
     * Handle Email sending.
     */
    public static function send_mail( $invoice ) {
        if ( apply_filters( 'smartwoo_invoice_payment_reminder_mail', get_option( 'smartwoo_new_invoice_mail', 0 ) ) ) {
            $self = new self( $invoice );
            return $self->send();
        }
    }

    /**
     * Default email template for payment reminder.
     */
    public static function get_template() {
        $message  = '<h1>Important: Unpaid Invoice Reminder for "{{invoice_id}}"</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>We hope this email finds you well. We would like to bring to your attention an outstanding invoice associated with your account.</p>';
        
        $message .= '<br><h3>Invoice Details:</h3>';
        $message .= '<ul>';
        $message .= '<li>Invoice ID: <strong>{{invoice_id}}</strong></li>';
        $message .= '<li>Invoice Type: <strong>{{invoice_type}}</strong></li>';
        $message .= '<li>Status: <strong>{{invoice_status}}</strong></li>';
        $message .= '<li>Due On: <strong>{{invoice_date_due}}</strong></li>';
        $message .= '<li>Invoice Date: <strong>{{invoice_date_created}}</strong></li>';
        $message .= '</ul>';
        
        $message .= '{{invoice_items}}'; // Placeholder for dynamically inserted invoice items
        
        $message .= '<p style="text-align: right; margin-right: 10%;"><strong>Total:</strong> {{invoice_total}}</p>';
        $message .= '<ul>';
        $message .= '<li><strong>Balance Due:</strong> {{invoice_total}}</li>';
        $message .= '<li><strong>Due Date:</strong> {{invoice_date_due}}</li>';
        $message .= '</ul><hr>';
        
        $message .= '<p>To make the payment securely, please click the button below:</p>';
        $message .= '<p><a class="button" href="{{auto_login_payment_link}}">Pay Now</a></p>';
        $message .= '<p>If the button above does not work, you may use the following link:</p>';
        $message .= '<a href="{{auto_login_payment_link}}">{{auto_login_payment_link}}</a>';
        
        $message .= '<p>Please note: This link will expire in 24 hours. After that, you may need to log into your account manually to make the payment.</p>';
        $message .= '<p><strong>View Invoice:</strong> <a href="{{preview_url}}">{{preview_url}}</a></p>';

        $template = apply_filters( 'smartwoo_payment_reminder_to_client_template', $message, self::$instance );

        return $template;
    }

    /**
     * Register email template
     * 
     * @param array $templates
     */
    public static function register_template( $templates ) {
        $templates[self::$id] = __CLASS__;

        return $templates;
    }
}

SmartWoo_Invoice_Payment_Reminder::init();