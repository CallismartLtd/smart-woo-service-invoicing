<?php
/**
 * Mail that is sent after a service order has been processed.
 * 
 * @author Callistus
 * @since 2.2.1
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Service_Processed_Mail extends SmartWoo_Service_Mails {
    /**
     * Email ID
     */
    public static $id = 'smartwoo_service_processed_mail';

    /**
     * The service
     * 
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Class instance
     * 
     * @var SmartWoo_Service_Processed_Mail $instance
     */
    public static $instance = 'SmartWoo_Service_Processed_Mail';

    /**
     * Class constructor
     */
    public function __construct( $service ) {
        $this->service  = $service;
        self::$instance = $this;

        parent::__construct( 'Service Order Processed', self::get_template(), $service );

    }

    /**
     * Hook runner
     */
    public static function init() {
        add_action( 'smartwoo_new_service_is_processed', array( __CLASS__, 'send_mail' ) );
    }

    /**
     * Send service processed mail.
     * @param string $service_id The service ID.
     */
    public static function send_mail( $service_id ) {
        $service = SmartWoo_Service_Database::get_service_by_id( $service_id );
        if ( $service && apply_filters( 'smartwoo_service_processed_mail', get_option( 'smartwoo_service_processed_mail', false ) ) ) {

            $self = new self( $service );
            $self->send();

        }

    }

    /**
     * Get template
     */
    public static function get_template() {
        $message  = '<h1>Service Subscription Order Completed.</h1>';
        $message .= '<p>Dear <strong>{{client_fullname}}</strong>,</p>';
        $message .= '<p>This is to notify you that your order for "<strong>{{service_name}}</strong>" has now been completed.</p>';
        $message .= '<h3>Service Details</h3>';
        $message .= '<ul>';
        $message .= '<li><strong>Service Name:</strong> {{service_name}}</li>';
        $message .= '<li><strong>Service ID:</strong> <span style="color: green; font-weight: bold;">{{service_id}}</span></li>';
        $message .= '<li><strong>Billing Cycle:</strong> {{billing_cycle}}</li>';
        $message .= '<li><strong>Start Date:</strong> {{start_date}}</li>';
        $message .= '<li><strong>Next Due Date:</strong> {{next_payment_date}}</li>';
        $message .= '<li><strong>End Date:</strong> {{end_date}}</li>';
        $message .= '</ul><br>';
        $message .= '<p>If you have any questions or require assistance, please do not hesitate to <a href="mailto:{{sender_mail}}">contact us</a>.</p>';
        return apply_filters( 'smartwoo_service_processed_mail_template', $message, self::$instance);
    }
}

SmartWoo_Service_Processed_Mail::init();