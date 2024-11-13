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
     * Class constructor
     */
    public function __construct( $service ) {
        $this->service = $service;

        parent::__construct( 'Auto Renewal Disabled', $this->get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_user_opted_out', array( __CLASS__, 'send_mail' ), 100 );
    }

    /**
     * Handle Email sending.
     * 
     * @param string $service_id
     * @param SmartWoo_Service $service
     */
    public static function send_mail( $service_id ) {

        if ( apply_filters( 'smartwoo_service_optout_mail', true ) ) {

            $self = new self( $service );
            $self->send();

        }
    }

    /**
     * Default email template
     */
    public function get_template() {
        $message  = '<h1>Auto Renewal for "{{service_name}}" has been disabled</h1>';
		$message .= '<p><strong>Dear {{client_fullname}}</strong>,</p>';
		$message .= '<p>You have successfully opted out of auto renewal for the service "{{service_name}}". The service is currently "<strong>{{status}}</strong>" but will not renew at the end of the billing cycle.</p>';
		$message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li>Service Name: {{service_name}}</li>';
        $message .= '<li>Billing Cycle: {{billing_cycle}}</li>';
        $message .= '<li>Start Date: {{start_date}}</li>';
        $message .= '<li>End Date: {{end_date}}</li>';
        $message .= '</ul><br>';
		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';

        return apply_filters( 'smartwoo_service_optout_mail_template', $message, $this->service );
    }

}

SmartWoo_Service_Expiration_Mail::init();