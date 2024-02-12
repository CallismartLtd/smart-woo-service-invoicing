<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

function delete(){
    $invoice_number_prefix = delete_option('sw_invoice_id_prefix', 'CINV');
    $invoice_preview_page = delete_option('invoice_preview_page', 0);
    $invoice_logo_url = delete_option('invoice_logo_url', '');
    $invoice_watermark_url = delete_option('invoice_watermark_url', '');
    $business_name = delete_option('business_name', '');
    $admin_phone_numbers = delete_option('admin_phone_numbers', '');
    $selected_categories = delete_option('selected_categories', []);
    $service_page = delete_option('sw_invoice_servise_page', 0);
    $billing_email = delete_option('sw_billing_email', '');
    $sender_name = delete_option('sender_name', '');
    $selected_upgrade_category = delete_option('selected_upgrade_category', '0');
    $selected_downgrade_category = delete_option('selected_downgrade_category', '0');
    $sw_prorate = delete_option('sw_prorate', 'Select option');
    $sw_allow_migration = delete_option('sw_allow_migration', 'Disable');
}