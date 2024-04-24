<?php
/**
 * File name   : class-sw-install.php
 * Plugin activation class.
 *
 * @author Callistus
 * @since      : 1.0.2
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

class SmartWoo_Install {

	private static $db_updates = array(
		'1.0.2' => array(
			''
		)
	);

	private static $installing = false;

	/**
	 * Installation.
	 */
	public static function install() {
		if ( self::is_installing() ) {
			return;
		}

		if ( true === self::is_new_installation() ) {
			self::create_tables();
			self::create_options();
		}
		self::rewrite_rule();
		self::add_automations();
	}

	private static function installing() {
		set_transient( '_smartwoo_is_installing', true, 60*10 );
		self::$installing = true;
	}

	private static function is_installing() {
		if ( true === get_transient( '_smartwoo_is_installing' ) ) {
			
			self::$installing = true;
		} 
		return self::$installing;
	}
	

	/**
	 * Create database tables.
	 */
	private static function create_tables() {
		// Load the db table file to have access to the properties
		include_once SMARTWOO_PATH . 'admin/include/sw-db.php';
		smartwoo_db_schema();
	}

	/**
	 * Set up default options.
	 */
	private static function create_options() {
		
		add_option( 'smartwoo_invoice_id_prefix', 'SmartWoo' );
		add_option( 'smartwoo_service_id_prefix', 'SmartWoo' );
		add_option( 'smartwoo_invoice_page_id', 0 );
		add_option( 'smartwoo_service_page_id', 0 );
		add_option( 'smartwoo_business_name', get_bloginfo( 'name' ) );
		add_option( 'smartwoo_billing_email' );
		add_option( 'smartwoo_email_sender_name', get_bloginfo( 'name' )  );
		add_option( 'smartwoo_prorate', 0 );
		add_option( 'smartwoo_allow_migration', 0 );
		add_option( 'smartwoo_cancellation_mail_to_user', 1 );
		add_option( 'smartwoo_service_opt_out_mail', 1 );
		add_option( 'smartwoo_payment_reminder_to_client', 1 );
		add_option( 'smartwoo_service_expiration_mail', 1 );
		add_option( 'smartwoo_new_invoice_mail', 1 );
		add_option( 'smartwoo_renewal_mail', 1 );
		add_option( 'smartwoo_reactivation_mail', 1 );
		add_option( 'smartwoo_invoice_paid_mail', 1 );
		add_option( 'smartwoo_service_cancellation_mail_to_admin', 1 );
		add_option( 'smartwoo_service_expiration_mail_to_admin', 1 );
		add_option( 'smartwoo_product_text_on_shop', 'Add to Cart' );
		add_option( 'smartwoo_enable_api_feature', 0 );
		add_option( 'smartwoo_allow_guest_invoicing', 0 );
	}

	/**
	 * Add automation schedules.
	 */
	private static function add_automations() {
		/**
		 * Schedule the auto-renewal event.
		 *
		 * This function checks if the 'smartwoo_auto_service_renewal' is not already scheduled
		 * and schedules it to run every 5 hours using the 'smartwoo_5_hours' cron interval.
		 */
		if ( ! wp_next_scheduled( 'smartwoo_auto_service_renewal' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_auto_service_renewal' );
		}

		/** Schedule some dynamic task to run five minutely. */
		if ( ! wp_next_scheduled( 'smartwoo_5_minutes_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_minutes', 'smartwoo_5_minutes_task' );
		}

		/** Daily task automation. */
		if ( ! wp_next_scheduled( 'smartwoo_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_daily', 'smartwoo_daily_task' );
		}

		/** Once in 48hrs( runs one in two days) task */
		if ( ! wp_next_scheduled( 'smartwoo_once_in48hrs_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_once_every_two_days', 'smartwoo_once_in48hrs_task' );
		}

		/** Twice Daily task automation */
		if ( ! wp_next_scheduled( 'smartwoo_twice_daily_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_12_hours', 'smartwoo_twice_daily_task' );
		}

		/** Automate refunds */
		if ( ! wp_next_scheduled( 'smartwoo_refund_task' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'once_every_two_days', 'smartwoo_refund_task' );
		}

		update_option( '__smartwoo_automation_scheduled_date', current_time( 'timestamp' ) );
	}

	/**
	 * Check if it's new installation.
	 */
	private static function is_new_installation() {
		if ( false === get_option( '__smartwoo_installed', false ) ){
			return true;
		}
		return false;
	}

	private static function rewrite_rule() {
		add_rewrite_rule( '^configure/?$', 'index.php?configure=true', 'top' );
		if ( false === get_option( '__smartwoo_added_rule', false ) ) {
			flush_rewrite_rules();
			update_option( '__smartwoo_added_rule', true );
		}
	}
}

