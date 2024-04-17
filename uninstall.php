<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;


/**
 * Delete pluigin settings during uninstall
 */
delete_option( 'smartwoo_invoice_id_prefix' );
delete_option( 'smartwoo_invoice_page_id' );
delete_option( 'smartwoo_invoice_logo_url' );
delete_option( 'smartwoo_invoice_watermark_url' );
delete_option( 'smartwoo_business_name' );
delete_option( 'smartwoo_admin_phone_numbers' );
delete_option( 'smartwoo_service_page_id' );
delete_option( 'smartwoo_billing_email' );
delete_option( 'smartwoo_email_sender_name' );
delete_option( 'smartwoo_upgrade_product_cat' );
delete_option( 'smartwoo_downgrade_product_cat' );
delete_option( 'smartwoo_prorate' );
delete_option( 'smartwoo_allow_migration' );
delete_option( 'smartwoo_service_id_prefix' );
delete_option( 'smartwoo_cancellation_mail_to_user' );
delete_option( 'smartwoo_service_opt_out_mail' );
delete_option( 'smartwoo_payment_reminder_to_client' );
delete_option( 'smartwoo_service_expiration_mail' );
delete_option( 'smartwoo_new_invoice_mail' );
delete_option( 'smartwoo_renewal_mail' );
delete_option( 'smartwoo_reactivation_mail' );
delete_option( 'smartwoo_invoice_paid_mail' );
delete_option( 'smartwoo_service_cancellation_mail_to_admin' );
delete_option( 'smartwoo_service_expiration_mail_to_admin' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );
delete_option( 'smartwoo_db_version' );

/**
 * Clear scheduled events
 */

wp_clear_scheduled_hook( 'smartwoo_auto_service_renewal' );
wp_clear_scheduled_hook( 'smartwoo_5_minutes_task' );
wp_clear_scheduled_hook( 'smartwoo_daily_task' );
wp_clear_scheduled_hook( 'smartwoo_once_in48hrs_task' );
wp_clear_scheduled_hook( 'move_old_renewal_orders_to_trash_event' );
wp_clear_scheduled_hook( 'smartwoo_daily_task' );
wp_clear_scheduled_hook( 'smartwoo_refund_task' );


