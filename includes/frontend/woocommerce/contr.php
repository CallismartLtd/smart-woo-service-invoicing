<?php
/**
 * File name   : contr.php
 * Author      : Callistus
 * Description : Controller file for our integration with WooCommerce on frontend
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Save Edited bio and User URL during edit account submit.
 */
function smartwoo_save_edited_bio_and_user_url( $user_id, $address_type = 'billing' ) {

    if ( isset( $_POST['smart_woo_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smart_woo_form_nonce'] ) ), 'smart_woo_form_nonce' ) ) {

        if ( isset( $_POST['bio'] ) ) {
            update_user_meta ( $user_id, 'description', sanitize_textarea_field( $_POST['bio'] ) );
        }        
        
        if ( isset( $_POST['billing_website'] ) ) {
            update_user_meta ( $user_id, 'billing_website', sanitize_url( $_POST['billing_website'], array( 'http', 'https' ) ) );
        }

        if ( isset( $_POST['website'] ) ) {
            $user_data = array(
                'ID'           => $user_id,
                'user_url'     => sanitize_url( $_POST['website'], array( 'http', 'https' ) ) ,
            );
            wp_update_user( $user_data );
        }
    }
}
