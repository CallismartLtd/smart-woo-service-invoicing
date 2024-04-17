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
function smartwoo_5hr_cron( $schedules ) {
	$schedules['smartwoo_5_hours'] = array(
		'interval' => 5 * 60 * 60,
		'display'  => __( 'SmartWoo Every 5 Hours', 'smart-woo-service-invoicing' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_5hr_cron' );

/**
 * Schedule the auto-renewal event.
 *
 * This function checks if the 'smartwoo_auto_service_renewal' is not already scheduled
 * and schedules it to run every 5 hours using the 'smartwoo_5_hours' cron interval.
 */
function smartwoo_renewal_scheduler() {

	if ( ! wp_next_scheduled( 'smartwoo_auto_service_renewal' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_hours', 'smartwoo_auto_service_renewal' );
	}
}
add_action( 'wp', 'smartwoo_renewal_scheduler' );

/**
 * Define a custom cron interval for every 5 minutes.
 *
 * This function adds a custom cron schedule interval of 5 minutes for Smart Woo.
 *
 * @param array $schedules Existing array of cron schedules.
 * @return array Modified array with the new Smart Woo cron interval.
 */
function smartwoo_5mins_cron( $schedules ) {
	// Add a new cron schedule interval for every 5 minutes.
	$schedules['smartwoo_5_minutes'] = array(
		'interval' => 5 * 60,
		'display'  => __( 'SmartWoo Every 5 Minutes', 'smart-woo-service-invoicing' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_5mins_cron' );

/**
 * Schedule a cron job to auto-update paid services using the 5-minute interval.
 */
function smartwoo_5mins_task_scheduler() {
	// Check if the event is not already scheduled.
	if ( ! wp_next_scheduled( 'smartwoo_5_minutes_task' ) ) {
		// Schedule the event to run every 5 minutes.
		wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_5_minutes', 'smartwoo_5_minutes_task' );
	}
}
add_action( 'wp', 'smartwoo_5mins_task_scheduler' );

/**
 * Define a custom cron interval for once a day.
 *
 * Adds a cron schedule interval named 'smartwoo_daily' for Smart Woo,
 * running every 24 hours. Used to schedule events in WordPress.
 *
 * @param array $schedules Existing array of cron schedules.
 * @return array Modified array with the new Smart Woo cron interval.
 */
function smartwoo_daily_cron( $schedules ) {
	// Add a new cron schedule interval for once a day (every 24 hours).
	$schedules['smartwoo_daily'] = array(
		'interval' => 24 * 60 * 60,
		'display'  => __( 'SmartWoo Daily', 'smart-woo-service-invoicing' ),
	);

	// Return the modified array of cron schedules.
	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_daily_cron' );

/**
 * Schedule daily service suspension email.
 *
 * @param array $schedules Existing array of cron schedules.
 */
function smartwoo_daily_task_scheduler() {
	if ( ! wp_next_scheduled( 'smartwoo_daily_task' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_daily', 'smartwoo_daily_task' );
	}
}
add_action( 'wp', 'smartwoo_daily_task_scheduler' );

/**
 * Define a cron interval for once every two days.
 *
 * @param array $schedules Existing array of cron schedules.
 */
function smartwoo_once_two_days_cron( $schedules ) {
	// Add a new cron schedule interval for once every two days (48 hours).
	$schedules['smartwoo_once_every_two_days'] = array(
		'interval' => 2 * 24 * 60 * 60,
		'display'  => __( 'SmartWoo Once Every Two Days', 'smart-woo-invoice' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_once_two_days_cron' );

/**
 * Schedule payment reminders to run once every two days.
 */
function smartwoo_once_in48hr_scheduler() {
	// Check if 'smartwoo_once_in48hrs_task' is not already scheduled.
	if ( ! wp_next_scheduled( 'smartwoo_once_in48hrs_task' ) ) {
		// Schedule the event to run once every two days.
		wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_once_every_two_days', 'smartwoo_once_in48hrs_task' );
	}
}
add_action( 'wp', 'smartwoo_once_in48hr_scheduler' );

/**
 * Define a cron interval for 12 hours.
 *
 * @param array $schedules Existing array of cron schedules.
 */

function smartwoo_12hrs_cron( $schedules ) {
	$schedules['smartwoo_12_hours'] = array(
		'interval' => 12 * 60 * 60, // 12 hours in seconds
		'display'  => __( 'SmartWoo twice Daily', 'smart-woo-invoice' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_12hrs_cron' );

/**
 * Schedule the task to run every 12 hours
 */
function smartwoo_twice_daily_scheduler() {
	// Check if the event is not already scheduled
	if ( ! wp_next_scheduled( 'smartwoo_twice_daily_task' ) ) {
		// Use wp_schedule_event to set up the scheduled event
		wp_schedule_event( current_time( 'timestamp' ), 'smartwoo_12_hours', 'smartwoo_twice_daily_task' );
	}
}
add_action( 'wp', 'smartwoo_twice_daily_scheduler' );

/**
 * Schedule pending refund services to process every two days.
 */
function smartwoo_refund_scheduler() {
	// Check if 'smartwoo_refund_task' is not already scheduled.
	if ( ! wp_next_scheduled( 'smartwoo_refund_task' ) ) {
		// Use the custom interval 'once_every_two_days'.
		wp_schedule_event( current_time( 'timestamp' ), 'once_every_two_days', 'smartwoo_refund_task' );
	}
}
add_action( 'wp', 'smartwoo_refund_scheduler' );