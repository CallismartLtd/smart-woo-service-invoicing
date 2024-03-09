<?php
/**
 * File name   : contr.php
 * Author      : Callistus
 * Description : Controller file for our integration with WooCommerce on frontend
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit;

/**
 * Save Edited bio and User URL during edit account submit
 */
function sw_save_edited_bio_and_user_url( $user_id, $address_type = 'billing' ) {
    error_log( 'Save fired' );
    if ( isset( $_POST['smart_woo_form_nonce'] ) && wp_verify_nonce( $_POST['smart_woo_form_nonce'], 'smart_woo_form_nonce' ) ) {

        if ( isset( $_POST['bio'] ) ) {
            update_user_meta ( $user_id, 'description', sanitize_textarea_field( $_POST['bio'] ) );
        }        
        
        if ( isset( $_POST['billing_website'] ) ) {
            update_user_meta ( $user_id, 'billing_website', sanitize_textarea_field( $_POST['billing_website'] ) );
        }

        if ( isset( $_POST['website'] ) ) {
            $user_data = array(
                'ID'           => $user_id,
                'user_url'     => esc_url_raw( $_POST['website'] ) ,
            );
            wp_update_user( $user_data );
        }
    }
}
add_action( 'woocommerce_save_account_details', 'sw_save_edited_bio_and_user_url', 20, 2 );
add_action( 'woocommerce_customer_save_address', 'sw_save_edited_bio_and_user_url', 20, 2 );