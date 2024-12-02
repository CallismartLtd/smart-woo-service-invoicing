<?php
/**
 * Emails sent during the service renewal processes
 * 
 * @author Callistus
 * @since 2.2
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Reactivation_Mail extends SmartWoo_Service_Mails {
    /**
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Static instance
     * 
     * @var SmartWoo_Service_Reactivation_Mail $instance
     */
    public static $instance = null;

    /**
     * Class constructor
     * 
     * @param SmartWoo_Service $service
     */
    public function __construct( $service ) {
        $this->service = $service;

        parent::__construct( $this->get_subject(), self::get_template(), $service );
    }

    /**
     * Run hooks
     */
    public static function init() {
        add_action( 'smartwoo_service_renewed', array( __CLASS__, 'send_mail' ) );
        add_action( 'smartwoo_expired_service_activated', array( __CLASS__, 'send_mail' ) );
    }

    /**
     * Email subject
     */
    public function get_subject() {
        return $this->service->get_name() . ' renewed';
    }

    /**
     * Get the email template
     */
    public static function get_template() {
        $message  = '<h1>{{service_name}} has been renewed</h1>';
		$message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
		$message .= '<p>Your service "{{service_name}}"  with {{business_name}} has successfully been renewed.</p>';
		$message .= '<p>The details of your renewed service are as follows:</p>';
		$message .= '<ul>';
		$message .= '<li>Service Name: {{product_name}} - {{service_name}}</li>';
		$message .= '<li>Pricing: {{product_price}}</li>';
		$message .= '<li>Service Type: {{service_type}}</li>';
		$message .= '<li>Start Date: {{start_date}}</li>';
		$message .= '<li>Next Payment Date: {{next_payment_date}}</li>';
		$message .= '<li>Expiration Date: {{expiry_date}}</li>';
		$message .= '</ul>';
		$message .= '<p>If you have any further questions or need assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';

        return apply_filters( 'smartwoo_service_reactivation_mail_template', $message, self::$instance );
    }

    /**
     * Send mail
     */
    public static function send_mail( $service ) {
        if ( apply_filters( 'smartwoo_service_reactivation_mail', get_option( 'smartwoo_renewal_mail', false ) ) ) {
            $self = new self( $service );

            $self->send();
        }
    }
}

SmartWoo_Service_Reactivation_Mail::init();