<?php
/**
 * Mails that are sent when user opts out for auto renewal of a service.
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Optout_Mail extends SmartWoo_Service_Mails {

    /**
     * Email ID
     */
    public static $id = 'smartwoo_service_opt_out_mail';

    /**
     * The service
     * 
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Class instance
     * 
     * @var SmartWoo_Service_Optout_Mail $instance
     */
    public static $instance = 'SmartWoo_Service_Optout_Mail';

    /**
     * Class constructor
     */
    public function __construct( $service ) {
        $this->service  = $service;
        self::$instance = $this;

        parent::__construct( 'Auto Renewal Disabled', self::get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init(){
        add_action( 'smartwoo_user_opted_out', array( __CLASS__, 'send_mail' ), 100 );
        add_filter( 'smartwoo_register_email_templates', array( __CLASS__, 'register_template' ) );
    }

    /**
     * Handle Email sending.
     * 
     * @param string $service_id
     * @param SmartWoo_Service $service
     */
    public static function send_mail( $service_id ) {

        $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        if ( $service && apply_filters( 'smartwoo_service_optout_mail', get_option( 'smartwoo_service_opt_out_mail', false ) ) ) {

            $self = new self( $service );
            $self->send();

        }
    }

    /**
     * Default email template for auto-renewal cancellation.
     */
    public static function get_template() {
        $message  = '<h1>Auto Renewal Disabled for "{{service_name}}"</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>You have successfully opted out of auto-renewal for the service "<strong>{{service_name}}</strong>". The service is currently "<strong>{{status}}</strong>" and will <strong>not renew</strong> at the end of the billing cycle.</p>';
        $message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li><strong>Service Name:</strong> {{service_name}}</li>';
        $message .= '<li><strong>Billing Cycle:</strong> {{billing_cycle}}</li>';
        $message .= '<li><strong>Start Date:</strong> {{start_date}}</li>';
        $message .= '<li><strong>End Date:</strong> {{end_date}}</li>';
        $message .= '</ul><br>';
        $message .= '<p>If you have any questions or require assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';

        return apply_filters( 'smartwoo_service_opt_out_mail_template', $message, self::$instance );
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

SmartWoo_Service_Optout_Mail::init();