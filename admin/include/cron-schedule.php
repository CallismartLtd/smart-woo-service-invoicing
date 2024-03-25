<?php
/**
 * File name   : cron-schedule.php
 * Author      : Callistus
 * Description : Task schedule and cron definition.
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Define a Smart Woo cron interval for every 5 hours.
 *
 * This function adds a custom cron schedule interval of 5 hours for Smart Woo.
 *
 * @param array $schedules Existing array of cron schedules.
 * @return array Modified array with the new Smart Woo cron interval.
 */
function sw_service_cron_intervals_5_hours( $schedules ) {
	// Add a new cron schedule interval for every 5 hours.
	$schedules['sw_5_hours'] = array(
		'interval' => 5 * 60 * 60, // 5 hours in seconds
		'display'  => __( 'Smart Woo Every 5 Hours', 'smart-woo-invoice-invoice-invoice' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'sw_service_cron_intervals_5_hours' );


/**
 * Schedule the auto-renewal event.
 *
 * This function checks if the 'auto_renew_services_event' is not already scheduled
 * and schedules it to run every 5 hours using the 'sw_5_hours' cron interval.
 */
function schedule_auto_renewal_event() {
	// Check if the 'auto_renew_services_event' is not already scheduled.
	if ( ! wp_next_scheduled( 'auto_renew_services_event' ) ) {
		// Schedule the event to run every 5 hours.
		wp_schedule_event( current_time( 'timestamp' ), 'sw_5_hours', 'auto_renew_services_event' );
	}
}
add_action( 'wp', 'schedule_auto_renewal_event' );




/**
 * Define a custom cron interval for every 5 minutes.
 *
 * This function adds a custom cron schedule interval of 5 minutes for Smart Woo.
 *
 * @param array $schedules Existing array of cron schedules.
 * @return array Modified array with the new Smart Woo cron interval.
 */
function sw_service_cron_intervals_5_minutes( $schedules ) {
	// Add a new cron schedule interval for every 5 minutes.
	$schedules['sw_5_minutes'] = array(
		'interval' => 5 * 60, // 5 minutes in seconds
		'display'  => __( 'Smart Woo Every 5 Minutes', 'smart-woo-invoice-invoice' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'sw_service_cron_intervals_5_minutes' );



/**
 * Schedule a cron job to auto-update paid services using the 5-minute interval.
 */
function sw_schedule_five_minutes_task() {
	// Check if the event is not already scheduled.
	if ( ! wp_next_scheduled( 'smart_woo_5_minutes_task' ) ) {
		// Schedule the event to run every 5 minutes.
		wp_schedule_event( current_time( 'timestamp' ), 'sw_5_minutes', 'smart_woo_5_minutes_task' );
	}
}
add_action( 'wp', 'sw_schedule_five_minutes_task' );



/**
 * Define a custom cron interval for once a day.
 *
 * Adds a cron schedule interval named 'sw_once_per_day' for Smart Woo,
 * running every 24 hours. Used to schedule events in WordPress.
 *
 * @param array $schedules Existing array of cron schedules.
 * @return array Modified array with the new Smart Woo cron interval.
 */
function sw_service_cron_intervals_once_per_day( $schedules ) {
	// Add a new cron schedule interval for once a day (every 24 hours).
	$schedules['sw_once_per_day'] = array(
		'interval' => 24 * 60 * 60, // 24 hours in seconds
		'display'  => __( 'Smart Woo Once Per Day', 'smart-woo-invoice' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'sw_service_cron_intervals_once_per_day' );




/**
 * Schedule daily service suspension email.
 *
 * @param array $schedules Existing array of cron schedules.
 */
function sw_daily_task_schedule() {
	if ( ! wp_next_scheduled( 'smart_woo_daily_task' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'sw_once_per_day', 'smart_woo_daily_task' );
	}
}
add_action( 'wp', 'sw_daily_task_schedule' );




/**
 * Define a cron interval for once every two days.
 *
 * @param array $schedules Existing array of cron schedules.
 */
function sw_service_cron_intervals_once_every_two_days( $schedules ) {
	// Add a new cron schedule interval for once every two days (48 hours).
	$schedules['sw_once_every_two_days'] = array(
		'interval' => 2 * 24 * 60 * 60, // 48 hours in seconds
		'display'  => __( 'Smart Woo Once Every Two Days', 'smart-woo-invoice' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'sw_service_cron_intervals_once_every_two_days' );


/**
 * Schedule payment reminders to run once every two days.
 */
function sw_schedule_once_in_two_days_task() {
	// Check if 'sw_once_in_two_days_task' is not already scheduled.
	if ( ! wp_next_scheduled( 'sw_once_in_two_days_task' ) ) {
		// Schedule the event to run once every two days.
		wp_schedule_event( current_time( 'timestamp' ), 'sw_once_every_two_days', 'sw_once_in_two_days_task' );
	}
}
add_action( 'wp', 'sw_schedule_once_in_two_days_task' );


/**
 * Schedule daily service renewals process.
 */
function schedule_service_renewals_cron() {
	if ( ! wp_next_scheduled( 'process_service_renewals_event' ) && function_exists( 'woo_wallet' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'sw_once_per_day', 'process_service_renewals_event' );
	}
}
add_action( 'wp', 'schedule_service_renewals_cron' );



/**
 * Define a cron interval for 12 hours.
 *
 * @param array $schedules Existing array of cron schedules.
 */

function sw_service_cron_intervals_12_hours( $schedules ) {
	$schedules['sw_12_hours'] = array(
		'interval' => 12 * 60 * 60, // 12 hours in seconds
		'display'  => __( 'Smart Woo twice Daily', 'smart-woo-invoice' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'sw_service_cron_intervals_12_hours' );

/**
 * Schedule the task to run every 12 hours
 */
function schedule_twice_daily_task() {
	// Check if the event is not already scheduled
	if ( ! wp_next_scheduled( 'sw_twice_daily_task' ) ) {
		// Use wp_schedule_event to set up the scheduled event
		wp_schedule_event( current_time( 'timestamp' ), 'sw_12_hours', 'sw_twice_daily_task' );
	}
}
add_action( 'wp', 'schedule_twice_daily_task' );





/**
 * Schedule pending refund services to process every two days.
 */
function schedule_pending_refund_services() {
	// Check if 'process_pending_refund_event' is not already scheduled.
	if ( ! wp_next_scheduled( 'process_pending_refund_event' ) ) {
		// Use the custom interval 'once_every_two_days'.
		wp_schedule_event( current_time( 'timestamp' ), 'once_every_two_days', 'process_pending_refund_event' );
	}
}
add_action( 'wp', 'schedule_pending_refund_services' );
