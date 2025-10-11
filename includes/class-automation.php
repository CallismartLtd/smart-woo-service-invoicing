<?php
/**
 * Smart Woo Automation handler file.
 * 
 * @author Callistus Nwachukwu
 * @since 2.4.3
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Automation {
    /**
     * @var self $instance A static instance of this class.
     */
    private static $instance = null;
    /**
     * Class constructor
     */
    private function __construct() {
        self::register_cron();
        add_action( 'smartwoo_five_hourly', array( __CLASS__, 'do_five_hourly' ) );        
        add_action( 'smartwoo_daily_task', array( __CLASS__, 'do_daily' ) );
        add_action( 'smartwoo_twice_daily_task', array( __CLASS__, 'do_twice_daily' ) );

    }
    
    /**
     * Singleton initializaation of this class.
     * 
     * @return self
     */
    public static function init() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Do automations that run every five hours
     */
    public static function do_five_hourly() {
        self::unpaid_invoice_reminder();
        self::notify_on_expiry_day();
        self::auto_create_invoice();
        
    }

    /**
     * Do automations that run once daily
     */
    public static function do_daily() {
        self::regulate_service_status();
        self::count_all_services();
    }

    /**
     * Do automations that run twice daily
     */
    public static function do_twice_daily() {
        self::service_expiry_reminder_to_admin();
    }
    
    /**
     * Initiates an automatic service renewal process by creating renewal invoice on due date
     * for services that are due.
     *
     * @return bool False if no service is due | True otherwise
     */
    private static function auto_create_invoice() {
        add_filter( 'smartwoo_is_frontend', '__return_false' ); // Ensures the process runs in backend context.

        $args = get_transient( 'smartwoo_auto_renew_args' );
        if ( false === $args || ! is_array( $args ) ) {
            $args = array( 'page' => 1, 'limit' => 20 ); // Default pagination args.
        }

        $all_services = SmartWoo_Service_Database::get_all_due( $args['page'], $args['limit'] );
        
        if ( empty( $all_services ) ) {
            delete_transient( 'smartwoo_auto_renew_args' ); // Reset the transient if no more services are due.
            return;
        }

        foreach ( $all_services as $service ) {
            $service_status = smartwoo_service_status( $service );

            if ( in_array( $service_status, ['Due for Renewal', 'Grace Period'], true ) ) {
                $has_invoice = SmartWoo_Invoice_Database::get_outstanding_invoice( $service->get_service_id() );
                
                if ( $has_invoice ) {
                    continue; // Skip if unpaid renewal invoice already exists.
                }

                // Prepare invoice data
                $date_due       = SmartWoo_Date_Helper::create_from( $service->get_end_date() )->format( 'Y-m-d H:i:s' );
                
                $inv_args	= array(
                    'user_id'		=> $service->get_user_id(), 
                    'product_id'	=> $service->get_product_id(), 
                    'status'		=> 'unpaid',
                    'type'			=> 'Service Renewal Invoice', 
                    'service_id'	=>  $service->get_service_id(),
                    'fee' 			=> 0,
                    'date_due'		=> $date_due,
                );

                $invoice = smartwoo_create_invoice( $inv_args );
                
                if ( $invoice ) {
                    
                    $client_payment_options = smartwoo_get_user_payment_options( $service->get_user_id() );
                    if ( $client_payment_options['primary'] ) {
                        $invoice->set_payment_method( $client_payment_options['primary'] );
                        if ( $order = $invoice->get_order() ) {
                            $order->set_payment_method( $client_payment_options['primary'] );
                            $order->save();

                        }
                        $invoice->save();
                    } else if ( $client_payment_options['backup'] ) {
                        $invoice->set_payment_method( $client_payment_options['backup'] );
                        if ( $order = $invoice->get_order() ) {
                            $order->set_payment_method( $client_payment_options['backup'] );
                            $order->save();

                        }
                        $invoice->save();
                    }

                    /**
                     * Fires when an auto renewal invoice is created.
                     * 
                     * @param SmartWoo_Invoice $invoice The invoice object.
                     * @param SmartWoo_Service $service The service subscription object.
                     */
                    do_action( 'smartwoo_auto_invoice_created', $invoice, $service );
                } else {
                    /**
                     * Fires when an auto renewal invoice creation fails
                     * 
                     * @param SmartWoo_Invoice $invoice The invoice object.
                     * @param string $message The reason for the failure.
                     */
                    do_action( 'smartwoo_auto_invoice_failed', $service, 'Invoice creation failed: database insertion error.' );
                }
            }
        }

        // Increment page for next batch of services
        $args['page']++;
        set_transient( 'smartwoo_auto_renew_args', $args, 12 * HOUR_IN_SECONDS );

    }

    /**
     * Check invoices that are pending payments, fire a `smartwoo_invoice_payment_reminder` action hook, and
     * set correct invoice status when payment is overdue.
     * 
     * @return void
     */
    private static function unpaid_invoice_reminder() {
        $last_checked = get_transient( 'smartwoo_checked_payment_reminder' );

        if ( $last_checked && ( $last_checked + DAY_IN_SECONDS ) > time() ) {
            return;
        }

        $args = get_transient( 'smartwoo_payment_reminder_loop' );

        if ( false === $args || ! is_array( $args ) ) {
            $args = array(
                'page'  => 1,
                'limit' => 20
            );
        }

		if ( wp_doing_cron() ) {
			add_filter( 'smartwoo_is_frontend', '__return_false' );
		}

        $unpaid_invoices = SmartWoo_Invoice_Database::get_invoices_by_payment_status( 'unpaid', $args );

        if ( empty( $unpaid_invoices ) ) {
            set_transient( 'smartwoo_checked_payment_reminder', time(), 2 * DAY_IN_SECONDS );
            delete_transient( 'smartwoo_payment_reminder_loop' ); // Reset the pagination.
            return;
        }

        foreach( $unpaid_invoices as $invoice ) {
            $due_date   = smartwoo_extract_only_date( $invoice->get_date_due() );
            $today      = smartwoo_extract_only_date( current_time( 'mysql' ) );
            if ( $due_date < $today ) {
                $invoice->set_status( 'due' );
                $invoice->save();
                continue;
            }

            /**
             * Fires for an unpaid invoice.
             * 
             * @param SmartWoo_Invoice $invoice The invoice object. 
             */
            do_action( 'smartwoo_invoice_payment_reminder', $invoice );
        }

        $args['page']++;
        set_transient( 'smartwoo_payment_reminder_loop', $args, DAY_IN_SECONDS );
    }

    /**
     * Check service subscriptions that are expiring today and trigger 'smartwoo_service_expired' action if found.
     *
     * @return void
     */
    private static function notify_on_expiry_day() {
        $last_checked = get_transient( 'smartwoo_expired_service_check' );

        if ( $last_checked && ( $last_checked + DAY_IN_SECONDS ) > time() ) {
            return;
        }

        $cache_key = 'smartwoo_expired_service_loop';
        $loop_args = get_transient( $cache_key );

        if ( false === $loop_args ) {
            $loop_args = array( 'page' => 1, 'limit' => 40 );
        }

        $on_expiry_threshold    = SmartWoo_Service_Database::get_on_expiry_threshold( $loop_args['page'], $loop_args['limit']  );
        if ( empty( $on_expiry_threshold ) ) {
            set_transient( 'smartwoo_expired_service_check', time(), DAY_IN_SECONDS );
            delete_transient( $cache_key );
            return;
        }

        foreach ( $on_expiry_threshold as $service ) {
            $current_date		= smartwoo_extract_only_date( current_time( 'mysql' ) );
            $expiration_date	= $service->get_expiry_date();

            if ( $current_date === $expiration_date ) {
                /**
                 * @hook 'smartwoo_service_expired' fires the day a service is expiring.
                 * @param SmartWoo_Service $service
                 */
                do_action( 'smartwoo_service_expired', $service );
            }
        }

        $loop_args['page']++;
        set_transient( $cache_key, $loop_args, 6 * HOUR_IN_SECONDS );
    }

    /**
     * Count all services in the database every five hours.
     * 
     * @since 2.0.12.
     */
    private static function count_all_services() {
        $count  = SmartWoo_Service_Database::count_all();
        update_option( 'smartwoo_all_services_count', $count );
    }

    /**
     * Normalize the status of a service subscription before expiration date, this is
     * used to handle 'Cancelled', 'Active NR' and other custom service, it ensures
     * the service is autocalculated at the end of each billing period.
     * 
     * If the service has already expired, it's automatically suspend in 7days time
     */
    private static function regulate_service_status() {
        $last_checked = get_transient( 'smartwoo_regulate_service_status' );

        if ( $last_checked && ( $last_checked + DAY_IN_SECONDS ) > time() ) {
            return;
        }

        $cache_key = 'smartwoo_regulate_service_status_loop';
        $loop_args = get_transient( $cache_key );

        if ( false === $loop_args || ! is_array( $loop_args ) ) {
            $loop_args = array( 'page' => 1, 'limit' => 40 );
        }

        $services = SmartWoo_Service_Database::get_on_expiry_threshold( $loop_args['page'], $loop_args['limit'] );
    
        if ( empty( $services ) ) {
            set_transient( 'smartwoo_regulate_service_status', time(), DAY_IN_SECONDS );
            delete_transient( $cache_key );
            return;
        }
    
        foreach ( $services as $service ) {
            if ( empty( $service->get_status() ) ) {
                continue;
            }
    
            if ( $service->is_expiring_tomorrow() ) {
    
                $field = array(
                    'status' => null, // Will be autocalculated.
                );
                SmartWoo_Service_Database::update_service_fields( $service->get_service_id(), $field );
    
            }
        }

        $loop_args['page']++;
        set_transient( $cache_key, $loop_args, 6 * HOUR_IN_SECONDS );
    }

    /**
     * Send service expiry mail to admin a day prior toexpiration date.
     */
    private static function service_expiry_reminder_to_admin() {
        $last_checked = get_transient( 'smartwoo_admin_expiry_service_mail_sent' );

        if ( $last_checked && ( $last_checked + DAY_IN_SECONDS ) > time() ) {
            return;
        }

        $cache_key = 'smartwoo_admin_expiry_service_mail_loop';
        $loop_args = get_transient( $cache_key );

        if ( false === $loop_args ) {
            $loop_args = array( 'page' => 1, 'limit' => 100 );
        }

        $on_expiry_threshold    = SmartWoo_Service_Database::get_on_expiry_threshold( $loop_args['page'], $loop_args['limit']  );
        if ( empty( $on_expiry_threshold ) ) {
            set_transient( 'smartwoo_admin_expiry_service_mail_sent', time(), DAY_IN_SECONDS );
            delete_transient( $cache_key );
            return;
        }

        $services = array();
        foreach ( $on_expiry_threshold as $the_service ){
            if ( $the_service->is_expiring_tomorrow() ) {
                $services[] = $the_service;
            }
        }

        if ( ! empty( $services ) && apply_filters( 'smartwoo_send_expiry_mail_to_admin', get_option( 'smartwoo_service_expiration_mail_to_admin', false ) ) ) {
            $mailer = new SmartWoo_Service_Expiration_Mail( $services, 'admin' );
            $mailer->send();
        }
        $loop_args['page']++;
        set_transient( $cache_key, $loop_args, DAY_IN_SECONDS );
    }

    /**
	 * register automation cron jobs.
	 */
	private static function register_cron() {

        /**
         * Five Hourly schedule
         */
		if ( ! wp_next_scheduled( 'smartwoo_five_hourly' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_five_hourly' );
		}

		/** Daily task automation. */
		if ( ! wp_next_scheduled( 'smartwoo_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_daily', 'smartwoo_daily_task' );
		}

		/** Twice Daily task automation */
		if ( ! wp_next_scheduled( 'smartwoo_twice_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_12_hours', 'smartwoo_twice_daily_task' );
		}

        if ( false === get_option( '__smartwoo_automation_last_scheduled_date', false ) ) {
    		update_option( '__smartwoo_automation_last_scheduled_date', current_time( 'timestamp' ) );
        
        }
	}
}