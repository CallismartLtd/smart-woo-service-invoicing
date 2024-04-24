<?php
/**
 * File name   : cron-schedule.php
 * Author      : Callistus
 * Description : Task schedule and cron definition.
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

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
 * Define a cron interval for once every two days.
 *
 * @param array $schedules Existing array of cron schedules.
 */
function smartwoo_once_two_days_cron( $schedules ) {
	// Add a new cron schedule interval for once every two days (48 hours).
	$schedules['smartwoo_once_every_two_days'] = array(
		'interval' => 2 * 24 * 60 * 60,
		'display'  => __( 'SmartWoo Once Every Two Days', 'smart-woo-service-invoicing' ),
	);

	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_once_two_days_cron' );


/**
 * Define a cron interval for 12 hours.
 *
 * @param array $schedules Existing array of cron schedules.
 */

function smartwoo_12hrs_cron( $schedules ) {
	$schedules['smartwoo_12_hours'] = array(
		'interval' => 12 * 60 * 60, // 12 hours in seconds
		'display'  => __( 'SmartWoo twice Daily', 'smart-woo-service-invoicing' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'smartwoo_12hrs_cron' );
