<?php
/**
 * Business Settings Template
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>
<div class="smartwoo-settings-page">
    <h1><span class="dashicons dashicons-tagcloud"></span> Business Info</h1>
    <form method="post" class="smartwoo-settings-form">
        <?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
        <?php do_action( 'smartwoo_before_service_options' ) ?>
        
        <!-- Business Name -->
        <div class="sw-form-row">
            <label for="smartwoo_business_name" class="sw-form-label"><?php esc_html_e( 'Business Name', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Enter your business name">?</span>
            <input type="text" name="smartwoo_business_name" id="smartwoo_business_name" value="<?php echo esc_attr( $business_name ); ?>" placeholder="Enter business name" class="sw-form-input">
        </div>

        <!--Business Phone -->
        <div class="sw-form-row">
            <label for="smartwoo_admin_phone_numbers" class="sw-form-label"><?php esc_html_e( 'Phone Numbers', 'smart-woo-service-invoicing');?></label>
            <span class="sw-field-description" title="Enter admin phone numbers separated by commas (e.g., +123456789, +987654321).">?</span>
            <input type="text" name="smartwoo_admin_phone_numbers" id="smartwoo_admin_phone_numbers" value="<?php echo esc_attr( $admin_phone_numbers ); ?>" placeholder="Enter business phone numbers" class="sw-form-input">
        </div>

        <!--Service Page -->
        <div class="sw-form-row">
            <label for="smartwoo_service_page_id" class="sw-form-label"><?php esc_html_e( 'Service Page', 'smart-woo-service-invoicing' );?></label>
            <span class="sw-field-description" title="This page should have this shortcode [smartwoo_service_page] ">?</span>
            <select name="smartwoo_service_page_id" id="smartwoo_service_page_id" class="sw-form-input">
                <option value="0"><?php esc_html_e( 'Select a Service page', 'smart-woo-service-invoicing' ); ?></option>
                <?php foreach ( $pages as $page ) : ?>
                <option value="<?php echo esc_attr( $page->ID );?>"<?php selected( $service_page, $page->ID );?>><?php echo esc_html( $page->post_title );?> </option>
                <?php endforeach;?>
            </select>
        </div>

        <!-- Form field for service_id_prefix -->
        <div class="sw-form-row">
            <label for="smartwoo_service_id_prefix" class="sw-form-label"><?php esc_html_e( 'Service ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Enter a text to prifix your service IDs">?</span>
            <input class="sw-form-input" type="text" name="smartwoo_service_id_prefix" id="smartwoo_service_id_prefix" value="<?php echo esc_attr( $service_id_prefix ); ?>" placeholder="eg, SMWSI">
        </div>

        <?php echo wp_kses_post( smartwoo_pro_feature( 'migration-options' ) ) ;?>
        <?php do_action( 'smartwoo_after_service_options' ); ?>
        
        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">

    </form>
</div>

