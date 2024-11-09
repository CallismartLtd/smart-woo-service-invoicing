<?php
/**
 * Invoice Email class
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Emails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Invoice_Mails extends SmartWoo_Mail {
    /**
     * Smart Woo Invoice
     * @var SmartWoo_Invoice $invoice
     */
    protected $invoice;

    /**
     * @var WC_Customer $client
     */
    protected $client;

    /**
     * Flag to check whether this class can send email
     */
    protected $object_ready = false;

    /**
     * Email body
     * 
     * @param string $body
     */
    protected $body;

    /**
     * Place holders
     * 
     * @var array $placeholders
     */
    protected static $placeholders = array(
        '{{client_firstname}}',
        '{{client_lastname}}',
        '{{client_fullname}}',
        '{{client_billing_address}}',
        '{{invoice_id}}',
        '{{invoice_type}}',
        '{{invoice_date_created}}',
        '{{invoice_date_paid}}',
        '{{invoice_date_due}}',
        '{{invoice_status}}',
        '{{invoice_total}}',
        '{{order_id}}',
        '{{product_id}}',
        '{{service_id}}',
        '{{amount}}',
        '{{fee}}',
        '{{payment_gateway}}',
        '{{transaction_id}}',
        '{{auto_login_payment_link}}',
        '{{payment_link}}',

    );

    /**
     * Class constructor
     * 
     * @param string $subject The subject of the email.
     * @param string $body      The email body.
     * @param SmartWoo_Invoice|string $invoice_id Instance of SmartWoo_invoice or the public invoice ID(string)
     */
    public function __construct( $subject, $body, $invoice_id ) {
        $this->set_object( $invoice_id, $body );
        
        if ( $this->object_ready ){
            parent::__construct( $subject, $this->format_placeholders(), $this->recipients(), $this->attachments() );

        }
    }

    /**
     * Set up this object
     * 
     * @param SmartWoo_Invoice|string $invoice_id Invoice ID or object.
     * @param string $body Email body
     */
    public function set_object( $invoice_id, $body ) {
        $this->invoice  = ( $invoice_id instanceof SmartWoo_Invoice ) ? $invoice_id : SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
        $this->body     = $body;
        if ( $this->invoice ) {
            $this->client = new WC_Customer( $this->invoice->get_user_id() );
            $this->object_ready = true;
        }
    }

    /**
     * Invoice email recipients
     * @filters smartwoo_invoice_email_recipients Filters the recipients of an invoice email.
     *              @param string[] $recipients
     *              @param SmartWoo_Invoice $invoice
     *              
     */
    public function recipients() {
        return apply_filters( 'smartwoo_invoice_email_recipients', $this->invoice->get_billing_email(), $this->invoice );
    }

    /**
     * Invoice Email attachement
     * @filters Allows to add attachment to email
     *              @param string[] $attachments
     *              @param SmartWoo_Invoice
     */
    public function attachments() {
        return apply_filters( 'smartwoo_invoice_email_attachments', [], $this->invoice );
    }

    /**
     * Format and replace body placeholders with dynamic values
     * @return string The formatted email body with replaced placeholders
     */
    public function format_placeholders() {
        $replace_values = array();

        foreach ( self::get_placeholders() as $placeholder ) {
            switch ( $placeholder ) {
                case '{{client_firstname}}':
                    $replace_values[$placeholder] = $this->client->get_first_name();
                    break;
                case '{{client_lastname}}':
                    $replace_values[$placeholder] = $this->client->get_last_name();
                    break;
                case '{{client_fullname}}':
                    $replace_values[$placeholder] = $this->client->get_first_name() . ' ' . $this->client->get_last_name();
                    break;
                case '{{client_billing_address}}':
                    $replace_values[$placeholder] = $this->invoice->get_billing_address();
                    break;
                case '{{invoice_id}}':
                    $replace_values[$placeholder] = $this->invoice->get_invoice_id();
                    break;
                case '{{invoice_type}}':
                    $replace_values[$placeholder] = $this->invoice->get_type();
                    break;
                case '{{invoice_date_created}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->invoice->get_date_created() );
                    break;
                case '{{invoice_date_paid}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->invoice->get_date_paid() );
                    break;  // Added break here
                case '{{invoice_date_due}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->invoice->get_date_due() );
                    break;
                case '{{invoice_status}}':
                    $replace_values[$placeholder] = $this->invoice->get_status();
                    break;
                case '{{invoice_total}}':
                    $replace_values[$placeholder] = smartwoo_price( apply_filters( 'smartwoo_display_invoice_total', $this->invoice->get_total(), $this->invoice ) );
                    break;
                case '{{order_id}}':
                    $replace_values[$placeholder] = $this->invoice->get_order_id();
                    break;
                case '{{product_id}}':
                    $replace_values[$placeholder] = $this->invoice->get_product_id();
                    break;
                case '{{service_id}}':
                    $replace_values[$placeholder] = $this->invoice->get_service_id();
                    break;
                case '{{amount}}':
                    $replace_values[$placeholder] = smartwoo_price( $this->invoice->get_amount() );
                    break;
                case '{{fee}}':
                    $replace_values[$placeholder] = smartwoo_price( $this->invoice->get_fee() );
                    break;
                case '{{payment_gateway}}':
                    $replace_values[$placeholder] = $this->invoice->get_payment_gateway();
                    break;
                case '{{transaction_id}}':
                    $replace_values[$placeholder] = $this->invoice->get_transaction_id();
                    break;
                case '{{auto_login_payment_link}}':
                    $replace_values[$placeholder] = smartwoo_generate_invoice_payment_url( $this->invoice->get_invoice_id(), $this->invoice->get_billing_email() );
                    break;
                case '{{payment_link}}':
                    $replace_values[$placeholder] = $this->invoice->pay_url();
                    break;
                default:
                    $replace_values[$placeholder] = ''; // Default to empty string for undefined placeholders
            }
        }

        $formatted_body = $this->body;

        // Perform the replacements.
        foreach ( $replace_values as $placeholder => $value ) {
            $formatted_body = str_replace( $placeholder, $value, $formatted_body );
        }

        return $formatted_body;
    }

    /**
     * Get email placeholders
     */
    public static function get_placeholders() {
        return apply_filters( 'smartwoo_invoice_email_placeholders', self::placeholders );
    }

    /**
     * Handle email sending.
     * 
     * @filter smartwoo_invoice_mail_send Controls whether or not all mails related to invoices should be sent.
     */
    public function send() {
        if ( apply_filters( 'smartwoo_invoice_mail_send', true, $this->invoice ) ) {
            return parent::send();
        }
        return false;
    }

}