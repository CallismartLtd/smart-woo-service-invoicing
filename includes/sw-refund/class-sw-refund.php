<?php
/**
 * File name   : class-sw-refund.php
 * Author      : Callistus
 * Description : Class file for Refund operations.
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

 defined( 'ABSPATH' ) || exit;

/**
 * Performs all refund related operation
 * 
 * 
 */
 class Sw_Refund extends Sw_Invoice_log {

    /**
     * Get a logged refund data
     * 
     * @param array $args        The Refund status
     */
    public function get_refund( array $args ) {
        global $wpdb;

        $default_args = array(
            'log_id' => '',
            'status' => 'Pending',
        );
        
        $table_name = SW_INVOICE_LOG_TABLE;
        $query   = $wpdb->prepare( "SELECT * FROM $table_name WHERE log_type = %s AND status = %s", 'Refund', $status );
        $result = $wpdb->get_row( $query, ARRAY_A );
        return self::convert_array_to_logs( $results );

    }

    /**
     * Get all refund logs
     * 
     * @param $refund   The type of data we want to fetch (now all refund)
     * @return array         Array of Sw_Invoice_log objects
     */

    public static function get_refund_logs( string $refund ) {
        return self::get_logs_by_criteria( 'log_type', $refund );

    }
 }