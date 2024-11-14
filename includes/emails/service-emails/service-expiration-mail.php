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
     * Class constructor
     */
    public function __construct( $service, $context = 'admin' ) {
        if ( is_array( $service ) ){
            $this->services = $service;
        } else {
            $this->service = $service;

        }
        $this->context = $context;

        parent::__construct( 'Service Expiration Notification', $this->get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_service_expired', array( __CLASS__, 'send_mail' ), 100 );
    }

    /**
     * Handle Email sending.
     * 
     * @param SmartWoo_Service $service
     */
    public static function send_mail( $service ) {

        if ( apply_filters( 'smartwoo_service_expiration_mail', true ) ) {

            $self = new self( $service );
            $self->send();

        }
    }

    /**
     * Default email template
     */
    public function get_template() {
        if ( 'user' === $this->context ) {
            return $this->user_mail_template();
        } else {
            return $this->admin_mail_template();
        }
    }

    /**
     * User email template
     */
    public function user_mail_template() {
        $message  = '<h1>Service Expiration Notification</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>Your service "{{service_name}}" has expired due to the end of the "{{billing_cycle}}" billing cycle. Unfortunately, no renewal action was taken in time.</p>';
        $message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li>Service Name: {{service_name}}</li>';
        $message .= '<li>Billing Cycle: {{billing_cycle}}</li>';
        $message .= '<li>Start Date: {{start_date}}</li>';
        $message .= '<li>End Date: {{end_date}}</li>';
        $message .= '</ul><br>';
        $message .= '<p>You can always log into your account and reactivate this service before it is finally suspended.</p>';

		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';

        return apply_filters( 'smartwoo_user_service_expiration_mail_template', $message, $this );
    }

    /**
     * Admin mail template
     */
    public function admin_mail_template() {
        $message  = '<h1>End Date Notification for Services Due Tomorrow</h1>';
        $message .= '<p>Dear Site Admin,</p>';
        $message .= '<p>This is to notify you that the following services are due to end tomorrow:</p>';
        $message .= '<h3>Service Details</h3>';
        
        foreach ( $this->services as $service ) {
            $this->service = $service;
            $message .= '<ul>';
            $message .= '<li>Service Name: {{product_name}} - {{service_name}}</li>';
            $message .= '<li>Service ID: {{service_id}}</li>';
            $message .= '<li>Billing Cycle: {{billing_cycle}}</li>';
            $message .= '<li>Start Date: {{start_date}}</li>';
            $message .= '<li>Next Payment Date: {{next_payment_date}}</li>';
            $message .= '<li>End Date: {{end_date}}</li>';
            $message .= '</ul><br>';

        }

		$message .= 'Pro rata refund is currently <strong>{{prorata_status}}</strong>';

        // Billing details.
		$message .= '<div style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">';
		$message .= '<p><strong>Customer Billing Details</strong></p>';
		$message .= '<p>Name: {{client_fullname}}</p>';
		$message .= '<p>Client Email: {{client_billing_email}}</p>';
		$message .= '<p>Address: {{client_billing_address}}</p>';
		$message .= '</div>';
        return apply_filters( 'smartwoo_admin_service_expiration_mail_template', $message, $this );
    }
}

SmartWoo_Service_Expiration_Mail::init();