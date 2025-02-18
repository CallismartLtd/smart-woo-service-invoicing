<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once 'smart-woo-service-invoicing.php';

$delete = get_option( 'smartwoo_remove_plugin_data_during_uninstall', false );
if ( ! $delete ) {
    return;
}

/**
 * Drop database tables on uninstall
 */
global $wpdb;

$table_names = array(
    SMARTWOO_SERVICE_TABLE,
    SMARTWOO_INVOICE_TABLE,
    SMARTWOO_ASSETS_TABLE,
    SMARTWOO_INVOICE_META_TABLE
);

foreach ( $table_names as $table_name ) {
    $sql = $wpdb->prepare( "DROP TABLE IF EXISTS %s", $table_name );
    $wpdb->query( $sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive
}

/**
 * Delete plugin options during uninstall
 */
$options = array(
    'smartwoo_invoice_id_prefix',
    'smartwoo_invoice_page_id',
    'smartwoo_invoice_logo_url',
    'smartwoo_invoice_watermark_url',
    'smartwoo_business_name',
    'smartwoo_admin_phone_numbers',
    'smartwoo_service_page_id',
    'smartwoo_billing_email',
    'smartwoo_email_sender_name',
    'smartwoo_upgrade_product_cat',
    'smartwoo_downgrade_product_cat',
    'smartwoo_prorate',
    'smartwoo_allow_migration',
    'smartwoo_service_id_prefix',
    'smartwoo_cancellation_mail_to_user',
    'smartwoo_service_opt_out_mail',
    'smartwoo_payment_reminder_to_client',
    'smartwoo_service_expiration_mail',
    'smartwoo_new_invoice_mail',
    'smartwoo_renewal_mail',
    'smartwoo_invoice_paid_mail',
    'smartwoo_service_cancellation_mail_to_admin',
    'smartwoo_service_expiration_mail_to_admin',
    'smartwoo_pay_pending_invoice_with_wallet',
    'smartwoo_refund_to_wallet',
    'smartwoo_product_text_on_shop',
    'smartwoo_enable_api_feature',
    'smartwoo_allow_guest_invoicing',
    'smartwoo_remove_plugin_data_during_uninstall',
    'smartwoo_email_image_header',
    '__smartwoo_installed',
    '__smartwoo_added_rule',
    '__smartwoo_automation_scheduled_date',
    'smartwoo_db_version',
    'smartwoo_all_services_count',
    'smartwoo_pro_sell_intrest',
    'smartwoo_allow_invoice_tracking',
    '_smartwoo_flushed_rewrite_rules',
    '__smartwoo_automation_last_scheduled_date',
);

foreach ( $options as $option ) {
    delete_option( $option );
}

/**
 * Clear scheduled events
 */
$schedule_hooks = array(
    'smartwoo_auto_service_renewal',
    'smartwoo_daily_task',
    'smartwoo_once_in48hrs_task',
    'smartwoo_five_hourly',
    'smartwoo_twice_daily_task',
    'smartwoo_service_scan',
);

foreach ( $schedule_hooks as $hook ) {
    wp_clear_scheduled_hook( $hook );
}

/**
 * Recursively delete a directory and its contents.
 *
 * @param string $dir Directory path.
 * @return bool True on success, false on failure.
 * @since 2.2.1
 */
function smartwoo_delete_directory( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return false;
    }

    $items = array_diff( scandir( $dir ), array( '.', '..' ) );

    foreach ( $items as $item ) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if ( is_dir( $path ) ) {
            smartwoo_delete_directory( $path );
        } else {
            // phpcs:ignore
            @unlink( $path );
        }
    }
    // phpcs:ignore
    return @rmdir( $dir );
}

// Delete Smart Woo Upload directory if defined
if ( defined( 'SMARTWOO_UPLOAD_DIR' ) ) {
    smartwoo_delete_directory( SMARTWOO_UPLOAD_DIR );
}