<?php
/**
 * Mails that are sent when service is cancelled.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Cancelled_Service_Mail extends SmartWoo_Service_Mails {

    /**
     * The service
     * 
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Context in which this email is set up.
     */
    private $context = 'admin';

    /**
     * Class constructor
     */
    public function __construct( $service, $context = 'admin' ) {
        $this->service = $service;
        $this->context = $context;

        parent::__construct( 'Service Cancellation Confirmation', $this->get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_user_cancelled_service', array( __CLASS__, 'send_mail' ), 100, 2 );
    }

    /**
     * Handle Email sending.
     * 
     * @param string $service_id
     * @param SmartWoo_Service $service
     */
    public static function send_mail( $service_id, $service ) {

        $send_to_user   = get_option( 'smartwoo_cancellation_mail_to_user', false );
        $send_to_admin  = get_option( 'smartwoo_cancellation_mail_to_user', false );

        if ( apply_filters( 'smartwoo_cancelled_service_mail', true ) ) {

            if ( $send_to_user ) {
                $self = new self( $service, 'user' );
                $self->recipients = $self->service->get_billing_email();
                $self->send();
            }

            if ( $send_to_admin ) {
                $self = new self( $service );
                $self->recipients = get_option( 'smartwoo_billing_email' );

                $self->send();
            }

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
     * User Email template
     */
    public function user_mail_template() {
        $message  = '<h1>Service Cancellation Confirmation</h1>';
		$message .= '<p><strong>Dear {{client_fullname}}</strong>,</p>';
		$message .= '<p>We regret to inform you that your service with {{business_name}} has been cancelled as requested. We appreciate your past support and patronage.</p>';
		$message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li>Service Name: {{service_name}}</li>';
        $message .= '<li>Billing Cycle: {{billing_cycle}}</li>';
        $message .= '<li>Start Date: {{start_date}}</li>';
        $message .= '<li>End Date: {{end_date}}</li>';
        $message .= '</ul><br>';
		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';
		$message .= '<p>Kindly note that our refund policy and terms of service apply to this cancellation.</p>';

        return apply_filters( 'smartwoo_user_cancelled_service_mail_template', $message, $this->service );
    }

    /**
     * Admin mail template
     */
    public function admin_mail_template() {
		$message  = '<h1>Service Cancellation</h1>';
		$message .= '<p>Hi, <strong>{{client_fullname}}</strong> has cancelled their service. Find details below.</p>';
		$message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
		$message .= '<li>Service Name: {{product_name}} - {{service_name}}</li>';
		$message .= '<li>Service ID: {{service_id}}</li>';
		$message .= '<li>Billing Cycle: {{billing_cycle}}</li>';
		$message .= '<li>Start Date: {{start_date}}</li>';
		$message .= '<li>Next Payment Date: {{next_payment_date}}</li>';
		$message .= '<li>End Date: {{end_date}}</li>';
        $message .= '</ul><br>';

		$message .= 'Pro rata refund is currently <strong>{{prorata_status}}</strong>';

        // Billing details.
		$message .= '<div style="border: 1px solid #ccc; padding: 10px; margin-top: 20px;">';
		$message .= '<p><strong>Customer Billing Details</strong></p>';
		$message .= '<p>Name: {{client_fullname}}</p>';
		$message .= '<p>Client Email: {{client_billing_email}}</p>';
		$message .= '<p>Address: {{client_billing_address}}</p>';
		$message .= '</div>';
        return apply_filters( 'smartwoo_admin_cancelled_service_mail_template', $message, $this->service );
    }

}

SmartWoo_Cancelled_Service_Mail::init();