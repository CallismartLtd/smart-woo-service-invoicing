<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;


/**
 * Delete pluigin settings during uninstall
 */
delete_option( 'sw_invoice_id_prefix' );
delete_option( 'invoice_preview_page' );
delete_option( 'sw_invoice_logo_url' );
delete_option( 'sw_invoice_watermark_url' );
delete_option( 'sw_business_name');
delete_option( 'sw_admin_phone_numbers' );
delete_option( 'sw_service_page', 0 );
delete_option( 'sw_billing_email' );
delete_option( 'sender_name');
delete_option( 'sw_upgrade_product_cat' );
delete_option( 'sw_downgrade_product_cat' );
delete_option( 'sw_prorate' );
delete_option( 'sw_allow_migration', 'Disable' );
delete_option( 'sw_service_id_prefix' );
delete_option( 'sw_cancellation_mail_to_user' );
delete_option( 'sw_service_opt_out_mail' );
delete_option( 'sw_payment_reminder_to_client' );
delete_option( 'sw_service_expiration_mail' );
delete_option( 'sw_new_invoice_mail' );
delete_option( 'sw_send_renewal_mail' );
delete_option( 'sw_reactivation_mail' );
delete_option( 'sw_invoice_paid_mail' );
delete_option( 'sw_service_cancellation_mail_to_admin' );
delete_option( 'sw_service_expiration_mail_to_admin' );
delete_option( 'sw_db_version' );

/**
 * Clear scheduled events
 */

wp_clear_scheduled_hook( 'auto_renew_services_event' );
wp_clear_scheduled_hook( 'smart_woo_5_minutes_task' );
wp_clear_scheduled_hook( 'smart_woo_daily_task' );
wp_clear_scheduled_hook( 'sw_once_in_two_days_task' );
wp_clear_scheduled_hook( 'move_old_renewal_orders_to_trash_event' );
wp_clear_scheduled_hook( 'process_service_renewals_event' );
wp_clear_scheduled_hook( 'process_pending_refund_event' );
