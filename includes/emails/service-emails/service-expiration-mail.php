<?php
/**
 * Mails that are sent when service expires.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Expiration_Mail extends SmartWoo_Service_Mails {

    /**
     * Mail ID
     */
    public static $id = 'smartwoo_service_expiration_mail';

    /**
     * The service
     * 
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Smart Woo Services
     * 
     * @var SmartWoo_service[] $services An array of Smart Woo Service objects.
     */
    public $services = array();

    /**
     * Context in which this email is set up.
     */
    private $context = 'admin';

    /**
     * Class instance
     * 
     * @var SmartWoo_Service_Expiration_Mail $instance
     */
    public static $instance = 'SmartWoo_Service_Expiration_Mail';

    /**
     * Class constructor
     */
    public function __construct( $service, $context = 'admin' ) {
        if ( is_array( $service ) && 'admin' === $context ){
            $this->services     = $service;
            $this->recipients   = apply_filters( 'smartwoo_admin_billing_email', get_option( 'smartwoo_billing_email' ) );
        } else {
            $this->service = $service;

        }
        $this->context = $context;
        self::$instance = $this;

        parent::__construct( 'Service Expiration Notification', self::get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_service_expired', array( __CLASS__, 'send_mail' ), 100 );
        add_filter( 'smartwoo_register_email_templates', array( __CLASS__, 'register_template' ) );
    }

    /**
     * Handle Email sending.
     * 
     * @param SmartWoo_Service $service
     */
    public static function send_mail( $service ) {

        if ( apply_filters( 'smartwoo_service_expiration_mail', get_option( 'smartwoo_service_expiration_mail' ) ) ) {

            $self = new self( $service, 'user' );
            $self->send();

        }
    }

    /**
     * Default email template
     */
    public static function get_template( $context = '' ) {
        if ( empty( $context ) ) {
            $context = self::$instance->context;
        }
        if ( 'user' === $context ) {
            return self::user_mail_template();
        } else {
            return self::$instance->admin_mail_template();
        }
    }

    /**
     * Template for service expiration mail sent to the client.
     */
    public static function user_mail_template() {
        $message  = '<h1>Service Expiration Notification</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>We wish to notify you that your service, "<strong>{{service_name}}</strong>", has expired following the conclusion of the "<strong>{{billing_cycle}}</strong>" billing cycle. Unfortunately, no renewal action was taken before the expiration date.</p>';

        $message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li><strong>Service Name:</strong> {{service_name}}</li>';
        $message .= '<li><strong>Billing Cycle:</strong> {{billing_cycle}}</li>';
        $message .= '<li><strong>Start Date:</strong> {{start_date}}</li>';
        $message .= '<li><strong>End Date:</strong> {{end_date}}</li>';
        $message .= '</ul>';

        $message .= '<p>To avoid service suspension or data loss, you can log into your account and reactivate this service as soon as possible.</p>';
        $message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';

        return apply_filters( 'smartwoo_service_expiration_mail_template', $message, self::$instance );
    }

    /**
     * Template for service expiration mail sent to the admin before the actual expiration date.
     * This allows the admin to take actions like engaging the client before their service expiration.
     */
    public function admin_mail_template() {
        $message  = '<h1>End Date Notification for Services Due Tomorrow</h1>';
        $message .= '<p>Dear Site Admin,</p>';
        $message .= '<p>This is to notify you that the following services are due to end tomorrow:</p>';
        $message .= '<h3>Service Details</h3>';
        $number = 1;
        
        foreach ( $this->services as $service ) {
            $client =  $service->get_user();
            $message .= '<h2>' . $number . '</h2>';
            $message .= '<ul>';
            $message .= '<li><strong>Service Name:</strong> ' . $service->get_product_name() . ' - ' . $service->get_name() . '</li>';
            $message .= '<li><strong>Service ID:</strong> ' . $service->get_service_id() . '</li>';
            $message .= '<li><strong>Billing Cycle:</strong> ' . $service->get_billing_cycle() . '</li>';
            $message .= '<li><strong>Start Date:</strong> ' . smartwoo_check_and_format( $service->get_start_date() ) . '</li>';
            $message .= '<li><strong>Next Payment Date:</strong> ' . smartwoo_check_and_format( $service->get_next_payment_date() ) . '</li>';
            $message .= '<li><strong>End Date:</strong> ' . smartwoo_check_and_format( $service->get_end_date() ) . '</li>';
            $message .= '</ul><br>';

            // Billing details.
            $message .= '<div style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">';
            $message .= '<p><strong>Customer Billing Details</strong></p>';
            $message .= '<p>Name: ' . $client->get_first_name() . ' ' . $client->get_last_name() . '</p>';
            $message .= '<p>Client Email: ' . $client->get_billing_email() . '</p>';
            $message .= '<p>Address: ' . $service->get_billing_address() . '</p>';
            $message .= '</div>';
            $message .= '<br>';
            $message .= '<hr>';
            $message .= '<hr>';
            $number++;
        }

        $message .= 'Sent on <strong>' . smartwoo_check_and_format( current_time( 'mysql' ), true ) .'</strong>';

        return apply_filters( 'smartwoo_admin_service_expiration_mail_template', $message, $this );
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

SmartWoo_Service_Expiration_Mail::init();