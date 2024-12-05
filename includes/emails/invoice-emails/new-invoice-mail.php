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
     * Email ID
     * 
     * @var string $id
     */
    public static $id = 'smartwoo_new_invoice_mail';
    /**
     * The invoice
     * 
     * @var SmartWoo_Invoice $invoice
     */
    protected $invoice;

    /**
     * Static instance
     * 
     * @var SmartWoo_New_Invoice_Mail $instance
     */
    public static $instance = 'SmartWoo_New_Invoice_Mail';

    /**
     * Class constructor
     */
    public function __construct( $invoice ) {
        $this->invoice  = $invoice;
        self::$instance = $this;
        parent::__construct( 'New Invoice', self::$instance->get_template(), $invoice );
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
    public static function get_template() {
        $message  = '<h1>New invoice "{{invoice_id}}"</h1>';
		$message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
		$message .= '<p>New invoice has been generated for you</p>';
        $message .= '<p>Status: {{invoice_status}}</p>';
        $message .= '<p>Due On: {{invoice_date_due}}</p>';
        $message .= '<p>Invoice Date: {{invoice_date_created}}</p>';
        
        $message .= '<br><h3>Invoice Details</h3>';
        $message .= '<ul>';
        $message .= '<li>Invoice ID: <strong>{{invoice_id}}</strong></li>';
        $message .= '</ul>';
        $message .= '{{invoice_items}}';
        $message .= '<p style="text-align:right; margin-right:10%;"><strong>Total</strong>: {{invoice_total}}</p>';
		$message .= '<p><strong>View invoice:</strong> <a href="{{preview_url}}">{{preview_url}}</a></p>';
        $template = apply_filters( 'smartwoo_new_invoice_mail_template', $message, self::$instance );
        return $template;
    }
}

SmartWoo_New_Invoice_Mail::init();