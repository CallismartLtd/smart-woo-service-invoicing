<?php
/**
 * Service Email class
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Emails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Mails extends SmartWoo_Mail {
    /**
     * Smart Woo Service
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * @var WC_Customer $client
     */
    protected $client;

    /**
     * Flag to check whether this class can send email
     */
    protected $object_ready = false;

    /**
     * recipients for same email
     * 
     * @var string[] $recipients
     */
    protected $recipients;

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
        '{{client_billing_email}}',
        '{{service_id}}',
        '{{service_name}}',
        '{{service_type}}',
        '{{billing_cycle}}',
        '{{start_date}}',
        '{{end_date}}',
        '{{next_payment_date}}',
        '{{product_id}}',
        '{{product_name}}',
        '{{product_price}}',
        '{{current_date}}',
        '{{status}}',
        '{{sender_mail}}',
        '{{business_name}}',
        '{{prorata_status}}',
        '{{expiry_date}}'
    );

    /**
     * Class constructor
     * 
     * @param string $subject The subject of the email.
     * @param string $body      The email body.
     * @param SmartWoo_Service|string $service Instance of SmartWoo_Service or the public service ID(string)
     */
    public function __construct( $subject, $body, $service ) {
        $this->set_object( $service, $body );
        
        if ( $this->object_ready ){
            parent::__construct( $subject, $this->format_placeholders(), $this->recipients(), $this->attachments() );

        }
    }

    /**
     * Set up this object
     * 
     * @param SmartWoo_Service|string $service Invoice ID or object.
     * @param string $body Email body
     */
    public function set_object( $service, $body ) {
        if( is_array( $service ) ) {
            $this->service = $service[0];
        } else {
            $this->service  = ( $service instanceof SmartWoo_Service ) ? $service : SmartWoo_Service_Database::get_service_by_id( $service );

        }
        $this->body     = $body;
        if ( $this->service ) {
            $this->client = new WC_Customer( $this->service->get_user_id() );
            $this->object_ready = true;
        }
    }

    /**
     * Service email recipients
     * @filters smartwoo_service_email_recipients Filters the recipients of a service email.
     *              @param string[] $recipients
     *              @param SmartWoo_Service $service
     *              
     */
    public function recipients() {
        if ( is_null( $this->recipients ) ) {
            $this->recipients = $this->service->get_billing_email();
        }
        
        return apply_filters( 'smartwoo_invoice_email_recipients', $this->recipients, $this->service );
    }

    /**
     * Service Email attachement
     * @filters Allows to add attachment to email
     *              @param string[] $attachments
     *              @param SmartWoo_Service
     */
    public function attachments() {
        return apply_filters( 'smartwoo_service_email_attachments', [], $this->service );
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
                    $replace_values[$placeholder] = $this->service->get_billing_address();
                    break;
                case '{{service_id}}':
                    $replace_values[$placeholder] = $this->service->get_service_id();
                    break;
                case '{{service_type}}':
                    $replace_values[$placeholder] = $this->service->get_type();
                    break;
                case '{{start_date}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->service->get_start_date() );
                    break;
                case '{{end_date}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->service->get_end_date() );
                    break;
                case '{{next_payment_date}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( $this->service->get_next_payment_date() );
                    break;
                case '{{status}}':
                    $replace_values[$placeholder] = smartwoo_service_status( $this->service );
                    break;
                case '{{service_name}}':
                    $replace_values[$placeholder] = $this->service->get_name();
                    break;
                case '{{billing_cycle}}':
                    $replace_values[$placeholder] = $this->service->get_billing_cycle();
                    break;
                case '{{client_billing_email}}':
                    $replace_values[$placeholder] = $this->service->get_billing_email();
                    break;
                case '{{product_id}}':
                    $replace_values[$placeholder] = $this->service->get_product_id();
                    break;
                case '{{product_name}}':
                    $replace_values[$placeholder] = $this->service->get_product_name();
                    break;
                case '{{product_price}}':
                    $replace_values[$placeholder] = smartwoo_price( $this->service->get_pricing() );
                    break;
                case '{{expiry_date}}':
                    $replace_values[$placeholder] = $this->service->get_expiry_date();
                    break;
                case '{{current_date}}':
                    $replace_values[$placeholder] = smartwoo_check_and_format( current_time( 'mysql' ) );
                    break;
                case '{{sender_mail}}':
                    $replace_values[$placeholder] =  get_option( 'smartwoo_billing_email', 'billing@' . site_url() );
                    break;
                case '{{business_name}}':
                    $replace_values[$placeholder] = get_option( 'smartwoo_business_name', get_bloginfo( 'name' ) );
                    break;
                case '{{prorata_status}}':
                    $replace_values[$placeholder] = smartwoo_is_prorate();
                    break;
                default:
                    $replace_values[$placeholder] = ''; // Default to empty string for undefined placeholders
            }
        }

        $formatted_body = $this->body;

        // Perform the replacements.
        foreach ( $replace_values as $placeholder => $value ) {
            if ( empty( $value ) ){
                continue;
            }
            
            $formatted_body = str_replace( $placeholder, $value, $formatted_body );
        }

        return $formatted_body;
    }

    /**
     * Get email placeholders
     */
    public static function get_placeholders() {
        return apply_filters( 'smartwoo_service_email_placeholders', self::$placeholders );
    }

    /**
     * Handle email sending.
     * 
     * @filter smartwoo_invoice_mail_send Controls whether or not all mails related to invoices should be sent.
     */
    public function send() {
        if ( apply_filters( 'smartwoo_service_mail_send', true, $this->service ) ) {
            return parent::send();
        }
        return false;
    }
}