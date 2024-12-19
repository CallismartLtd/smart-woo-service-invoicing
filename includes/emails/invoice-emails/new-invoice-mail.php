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
        add_action( 'smartwoo_new_invoice_created', array( __CLASS__, 'send_mail' ), 9999 );
        add_filter( 'smartwoo_new_invoice_mail_template', array( __CLASS__, 'add_payment_url' ), 10, 2 );
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
     * Default email template for new invoice mails.
     */
    public static function get_template() {
        $message  = '<h1>New Invoice "{{invoice_id}}"</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>We hope this email finds you well. We are pleased to inform you that a new invoice has been generated for your account.</p>';

        $message .= '<br><h3>Invoice Details:</h3>';
        $message .= '<ul>';
        $message .= '<li>Invoice ID: <strong>{{invoice_id}}</strong></li>';
        $message .= '<li>Invoice Type: <strong>{{invoice_type}}</strong></li>';
        $message .= '<li>Status: <strong>{{invoice_status}}</strong></li>';
        $message .= '<li>Due On: <strong>{{invoice_date_due}}</strong></li>';
        $message .= '<li>Invoice Date: <strong>{{invoice_date_created}}</strong></li>';
        $message .= '</ul>';
        
        $message .= '{{invoice_items}}';
        
        $message .= '<p style="text-align: right; margin-right: 10%;"><strong>Total:</strong> {{invoice_total}}</p>';
        $message .= '<p><strong>View Invoice:</strong> <a href="{{preview_url}}">{{preview_url}}</a></p>';

        $template = apply_filters( 'smartwoo_new_invoice_mail_template', $message, self::$instance );

        return $template;
    }

    /**
     * Appends payment links to new invoice mails when invoice is unpaid.
     * 
     * @param string $template Default new invoice mail template.
     * @param self $self
     */
    public static function add_payment_url( $template, $self ) {
        $is_edit    = isset( $_GET['tab'], $_GET['section'] ) && 'edit' === $_GET['section'];
        $is_preview = isset( $_GET['action'] ) && 'smartwoo_mail_preview' === $_GET['action'];

        if ( $is_edit ) {
            return $template;
        }

        if ( 'unpaid' === $self->invoice->get_status() ) {
            if ( $is_preview ) {

                add_filter( 'smartwoo_maybe_add_script', array( __CLASS__, 'print_scripts' ) );
                $template .= '<br/><hr/>';
                $template .= '<div class="sw-payment-intro">
                    <h3>Payment Info(automatically add for unpaid invoices)</h3>
                </div>';

            }

            $template .= '<div class="sw-payment-link-container" style="border: 1px solid #ccc; padding: 10px; margin-top: 20px; border-radius: 5px;">';
            $template .= '<p>To make the payment securely, please click the button below:</p>';
            $template .= '<p><a class="button" href="{{auto_login_payment_link}}">Pay Now</a></p>';
            $template .= '<p>If the button above does not work, you may use the following link:</p>';
            $template .= '<a href="{{auto_login_payment_link}}">{{auto_login_payment_link}}</a>';
            $template .= '<p>Please note: This link will expire in 24 hours. After that, you may need to log into your account manually to make the payment.</p>';
            $template .= '</div>';
        }

        return $template;
    }

    /**
     * Print scripts for template preview
     */
    public static function print_scripts() {
        return '<script src="' . SMARTWOO_DIR_URL .'assets/js/smart-woo-template-preview.js">
        </script>';
    }
}

SmartWoo_New_Invoice_Mail::init();