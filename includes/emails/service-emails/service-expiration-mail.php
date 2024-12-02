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
     * Class instance
     * 
     * @var SmartWoo_Service_Expiration_Mail $instance
     */
    public static $instance = null;

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
        add_action( 'smartwoo_five_hourly', array( __CLASS__, 'send_to_admin' ) );
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
     * Send service expiry mail to admin a day prior toexpiration date.
     */
    public static function send_to_admin() {
        $last_checked = get_transient( 'smartwoo_admin_expiry_service_mail_sent' );

        if ( $last_checked && ( $last_checked + DAY_IN_SECONDS ) > time() ) {
            return;
        }

        $cache_key = 'smartwoo_admin_expiry_service_mail_loop';
        $loop_args = get_transient( $cache_key );

        if ( false === $loop_args ) {
            $loop_args = array( 'page' => 1, 'limit' => 40 );
        }

        $on_expiry_threshold    = SmartWoo_Service_Database::get_on_expiry_threshold( $loop_args['page'], $loop_args['limit']  );
        if ( empty( $on_expiry_threshold ) ) {
            set_transient( 'smartwoo_admin_expiry_service_mail_sent', time(), DAY_IN_SECONDS );
            delete_transient( $cache_key );
            return;
        }

        $services = array();
        foreach ( $on_expiry_threshold as $the_service ){
            if ( $the_service->get_expiry_date() === date_i18n( 'Y-m-d', strtotime( '+1 day' ) ) ) {
                $services[] = $the_service;
            }
        }

        if ( ! empty( $services ) && apply_filters( 'smartwoo_send_expiry_mail_to_admin', get_option( 'smartwoo_service_expiration_mail_to_admin', false ) ) ) {
            $self = new self( $services, 'admin' );
            $self->send();
        }
        $loop_args['page']++;
        set_transient( $cache_key, $loop_args, DAY_IN_SECONDS );
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
     * User email template
     */
    public static function user_mail_template() {
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

        return apply_filters( 'smartwoo_user_service_expiration_mail_template', $message, self::$instance );
    }

    /**
     * Admin mail template
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
            $message .= '<li>Service Name: ' . $service->get_product_name() . ' - ' . $service->get_name() . '</li>';
            $message .= '<li>Service ID: ' . $service->get_service_id() . '</li>';
            $message .= '<li>Billing Cycle: ' . $service->get_billing_cycle() . '</li>';
            $message .= '<li>Start Date: ' . smartwoo_check_and_format( $service->get_start_date() ) . '</li>';
            $message .= '<li>Next Payment Date: ' . smartwoo_check_and_format( $service->get_next_payment_date() ) . '</li>';
            $message .= '<li>End Date: ' . smartwoo_check_and_format( $service->get_end_date() ) . '</li>';
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
}

SmartWoo_Service_Expiration_Mail::init();