<?php
/**
 * Invoice Settings Template
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.
?>
<div class="smartwoo-settings-page">
    <h1><span class="dashicons dashicons-media-spreadsheet"></span> <?php esc_html_e( 'Invoice settings', 'smart-woo-service-invoicing' ); ?></h1>
    <form method="post" class="smartwoo-settings-form">

        <?php wp_nonce_field( 'sw_option_nonce', 'sw_option_nonce' ); ?>
        <?php do_action( 'smartwoo_before_invoice_options' ) ?>

        <!--Invoice Page -->
        <div class="sw-form-row">
            <label for="smartwoo_invoice_page_id" class="sw-form-label"><?php esc_html_e( 'Invoice Page', 'smart-woo-service-invoicing' ) ?></label>
            <span class="sw-field-description" title="This page should have this shortcode [smartwoo_invoice_page]">?</span>
            <select name="smartwoo_invoice_page_id" id="smartwoo_invoice_page_id" class="sw-form-input">
                <option value="0"><?php esc_html_e( 'Select an invoice page', 'smart-woo-service-invoicing' ); ?></option>
                <?php foreach ( $pages as $page ) : ?>
                    <option value="<?php echo esc_attr( $page->ID ); ?>"<?php selected( $invoice_page, $page->ID ); ?>><?php echo esc_html( $page->post_title ); ?></option>
                <?php endforeach;?>
            </select>
        </div>

        <!-- Invoice ID Prefix -->
        <div class="sw-form-row">
            <label for="smartwoo_invoice_id_prefix" class="sw-form-label"><?php esc_html_e( 'Invoice ID Prefix', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Enter a text to prifix your invoice IDs">?</span>
            <input class="sw-form-input" type="text" name="smartwoo_invoice_id_prefix" id="smartwoo_invoice_id_prefix" value="<?php echo esc_attr( $invoice_prefix ); ?>" placeholder="eg, INV">
        </div>

        <!-- Invoice Logo URL -->
        <div class="sw-form-row">
            <label for="smartwoo_invoice_logo_url" class="sw-form-label">Logo URL</label>
            <span class="sw-field-description" title="Paste the link to your logo url, size 512x512 pixels recommended.">?</span>
            <input type="text" name="smartwoo_invoice_logo_url" id="smartwoo_invoice_logo_url" value="<?php echo esc_attr( $invoice_logo_url ); ?>" placeholder=" eg. www.example/image.png" class="sw-form-input">
        </div>
            
        <?php do_action( 'smartwoo_after_invoice_options' ) ?>

        <!-- Invoice Watermark URL -->
        <div class="sw-form-row">
            <label for="smartwoo_invoice_watermark_url" class="sw-form-label"><?php esc_html_e( 'Watermark URL', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="Paste the link to your logo url, size 512x512 pixels recommended.">?</span>
            <input type="text" name="smartwoo_invoice_watermark_url" id="smartwoo_invoice_watermark_url" value="<?php echo esc_attr( $invoice_watermark_url ); ?>" placeholder="eg www.example/image.png" class="sw-form-input">
        </div>

        <!-- Global invoice generation date -->
        <div class="sw-form-row">
            <label for="smartwoo_auto_generate_invoice" class="sw-form-label"><?php esc_html_e( 'Auto Generate Invoice', 'smart-woo-service-invoicing' ); ?></label>
            <span class="sw-field-description" title="This option applies to the global 'next payment date' of a service subscription and can be overridden on individual subscription's 'next payment date'">?</span>
            <div class="sw-form-input sw-options-multiple">
                <p class="description-class">When should invoices be auto-generated?</p>
                <div>
                    <input type="number" name="next_payment_date_number" id="next_payment_date_number" min="1" value="<?php echo esc_html( $global_next_pay['number']) ?>">
                    <select name="next_payment_date_unit" id="next_payment_date_unit">
                        <option value="days" <?php selected( 'days', $global_next_pay['unit']) ?>>Day(s)</option>
                        <option value="weeks" <?php selected( 'weeks', $global_next_pay['unit']) ?>>Week(s)</option>
                        <option value="months" <?php selected( 'months', $global_next_pay['unit']) ?>>Month(s)</option>
                        <option value="years" <?php selected( 'years', $global_next_pay['unit']) ?>>Year(s)</option>
                    </select>
                    <select name="next_payment_date_operator" id="next_payment_date_operator">
                        <option value="-" <?php selected( '-', $global_next_pay['operator']) ?>>Before</option>
                        <option value="+" <?php selected( '+', $global_next_pay['operator']) ?>>After</option>
                    </select>
                    <strong>Subscription ends.</strong>
                </div>
            </div>
        </div>

        <input type="submit" class="sw-blue-button" name="sw_save_options" value="Save Settings">
    </form>

</div>