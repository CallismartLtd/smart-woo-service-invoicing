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
     * Email ID
     * 
     * @var string $id
     */
    public static $id = 'smartwoo_invoice_paid_mail';

    /**
     * The invoice
     * 
     * @var SmartWoo_Invoice $invoice
     */
    protected $invoice;

    /**
     * Static self
     * 
     * @var SmartWoo_Invoice_Paid_Mail $instance
     */
    public static $instance = 'SmartWoo_Invoice_Paid_Mail';

    /**
     * Class constructor
     */
    public function __construct( $invoice ) {
        $this->invoice = $invoice;

        parent::__construct( 'Invoice Payment Confirmation', self::get_template(), $invoice );
        self::$instance = $this;

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_invoice_is_paid', array( __CLASS__, 'send_mail' ) );
        add_action( 'admin_post_smartwoo_invoice_paid_mail', array( __CLASS__, 'start_preview_buffer' ) );
        add_filter( 'smartwoo_register_email_templates', array( __CLASS__, 'register_template' ) );
    }

    /**
     * Handle Email sending.
     */
    public static function send_mail( $invoice ) {
        if ( apply_filters( 'smartwoo_invoice_paid_mail', get_option( 'smartwoo_invoice_paid_mail', 0 ) ) ) {
            $self = new self( $invoice );
            $self->send();
        }
    }

    /**
     * Default email template for paid invoice mails.
     */
    public static function get_template() {
        $message  = '<h1>Payment Receipt for Invoice "{{invoice_id}}"</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>This is a payment receipt for Invoice #{{invoice_id}}, successfully paid on {{invoice_date_paid}}.</p>';
        
        $message .= '<br><h3>Invoice Details:</h3>';
        $message .= '<ul>';
        $message .= '<li><strong>Invoice ID:</strong> {{invoice_id}}</li>';
        $message .= '<li><strong>Invoice Type:</strong> {{invoice_type}}</li>';
        $message .= '<li><strong>Status:</strong> {{invoice_status}}</li>';
        $message .= '<li><strong>Invoice Date:</strong> {{invoice_date_created}}</li>';
        $message .= '<li><strong>Paid On:</strong> {{invoice_date_paid}}</li>';
        $message .= '</ul>';
        
        $message .= '{{invoice_items}}'; // Placeholder for dynamically inserted invoice items.
        
        $message .= '<p style="text-align: right; margin-right: 10%;"><strong>Total:</strong> {{invoice_total}}</p>';
        $message .= '<p><strong>View Invoice:</strong> <a href="{{preview_url}}">{{preview_url}}</a></p>';
        
        $template = apply_filters( 'smartwoo_invoice_paid_mail_template', $message, self::$instance );
        
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

SmartWoo_Invoice_Paid_Mail::init();