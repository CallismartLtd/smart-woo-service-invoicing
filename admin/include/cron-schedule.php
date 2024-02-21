<?php

/**
 * This file handles all the automation that happens in this plugin, we use wordpress cron job to
 * to set and shedule these events to run at specific intervals
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


 // Define a Smart Woo cron interval for every 5 hours
function sw_service_cron_intervals_5_hours($schedules) {
    $schedules['sw_5_hours'] = array(
        'interval' => 5 * 60 * 60, // 5 hours in seconds
        'display'  => __('Smart Woo Every 5 Hours', 'smart-woo')
    );
    return $schedules;
}
add_filter('cron_schedules', 'sw_service_cron_intervals_5_hours');

// Schedule the auto-renewal event
function schedule_auto_renewal_event() {
    if (!wp_next_scheduled('auto_renew_services_event')) {
        // Schedule the event to run every 5 hours
        wp_schedule_event(current_time('timestamp'), 'sw_5_hours', 'auto_renew_services_event');
    }
}
add_action('wp', 'schedule_auto_renewal_event');



// Define a custom cron interval for every 5 minutes
function sw_service_cron_intervals_5_minutes($schedules) {
    $schedules['sw_5_minutes'] = array(
        'interval' => 5 * 60, // 5 minutes in seconds
        'display'  => __('Smart Woo Every 5 Minutes', 'smart-woo')
    );
    return $schedules;
}
add_filter('cron_schedules', 'sw_service_cron_intervals_5_minutes');


// Schedule a cron job to auto-update paid services using the 5-minute interval
function schedule_auto_update_paid_services() {
    if (!wp_next_scheduled('auto_update_paid_services')) {
        wp_schedule_event(current_time('timestamp'), 'sw_5_minutes', 'auto_update_paid_services');
    }
}
add_action('wp', 'schedule_auto_update_paid_services');


// Define a custom cron interval for once a day with a different name
function sw_service_cron_intervals_once_per_day($schedules) {
    $schedules['sw_once_per_day'] = array(
        'interval' => 24 * 60 * 60, // 24 hours in seconds
        'display'  => __('Smart Woo Once Per Day', 'smart-woo')
    );
    return $schedules;
}
add_filter('cron_schedules', 'sw_service_cron_intervals_once_per_day');


// Schedule a cron job to send service suspension email using the 'once_per_day' interval
function sw_daily_task_schedule() {
    if (!wp_next_scheduled('smart_woo_daily_task')) {
        wp_schedule_event(current_time('timestamp'), 'sw_once_per_day', 'smart_woo_daily_task');
    }
}
add_action('wp', 'sw_daily_task_schedule');



// Define a  cron interval for once every two days
function sw_service_cron_intervals_once_every_two_days($schedules) {
    $schedules['sw_once_every_two_days'] = array(
        'interval' => 2 * 24 * 60 * 60, // 48 hours in seconds
        'display'  => __('Smart Woo Once Every Two Days', 'smart-woo')
    );
    return $schedules;
}
add_filter('cron_schedules', 'sw_service_cron_intervals_once_every_two_days');

// Schedule the payment reminders function to run once every two days
function sw_schedule_once_in_two_days_task() {
    if (!wp_next_scheduled('sw_once_in_two_days_task')) {
        wp_schedule_event(current_time('timestamp'), 'sw_once_every_two_days', 'sw_once_in_two_days_task');
    }
}
add_action('wp', 'sw_schedule_once_in_two_days_task');





/**
 * This part handles the cleaning of the Service Renewal database 
 */

 // Schedule the event to move old Service Renewals to trash
 function schedule_move_old_renewal_orders_to_trash() {
    if (!wp_next_scheduled('move_old_renewal_orders_to_trash_event')) {
        wp_schedule_event(current_time('timestamp'), 'sw_once_per_day', 'move_old_renewal_orders_to_trash_event');
    }
}
add_action('wp', 'schedule_move_old_renewal_orders_to_trash');


// Schedule the process_service_renewals function to run once per day
function schedule_service_renewals_cron() {
    if ( ! wp_next_scheduled( 'process_service_renewals_event' ) && function_exists( 'woo_wallet' ) ) {
        wp_schedule_event(current_time('timestamp'), 'sw_once_per_day', 'process_service_renewals_event');
    }
}
add_action('wp', 'schedule_service_renewals_cron');







function schedule_pending_refund_services() {
    if (!wp_next_scheduled('process_pending_refund_event')) {
        // Use the custom interval 'once_every_two_days'
        wp_schedule_event(current_time('timestamp'), 'once_every_two_days', 'process_pending_refund_event');
    }
}
add_action('wp', 'schedule_pending_refund_services');





// Schedule the auto-renewal event
function sw_deactivate_service_url_event() {
    if (!wp_next_scheduled('deactivate_expired_service')) {
        // Schedule the event to run every 5 hours
        wp_schedule_event(current_time('timestamp'), 'sw_5_hours', 'deactivate_expired_service');
    }
}
add_action('wp', 'sw_deactivate_service_url_event');
