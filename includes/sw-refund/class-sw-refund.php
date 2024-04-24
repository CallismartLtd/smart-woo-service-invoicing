<?php
/**
 * File name   : class-sw-refund.php
 * Author      : Callistus
 * Description : Class file for Refund operations.
 *
 * @since      : 1.0.1
 * @package    : SmartWooClass
 */

 defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Performs all refund related operation
 * 
 * 
 */
 class SmartWoo_Refund extends SmartWoo_Invoice_log {


    /**
     * Get refund data based on provided arguments.
     *
     * @param array $args Arguments to filter refund data.
     * @return array|object Depending on whether log_id is provided, returns an array of refund data or a single refund object.
     */
    public static function get_refund( array $args = array() ) {
        global $wpdb;

        $default_args = array(
            'log_id'        => '',
            'status'        => 'Pending',
            'created_at'    => ''
        );

        $merged_args = wp_parse_args( $args, $default_args );

        // phpcs:disable
        $table_name = SW_INVOICE_LOG_TABLE;
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE log_type = %s", 'Refund' );

        if ( ! empty( $merged_args['log_id'] ) ) {

            $query .= $wpdb->prepare( " AND log_id = %s", $merged_args['log_id'] );

            // Execute the query and fetch single row.
            $result = $wpdb->get_row( $query, ARRAY_A );

            // Convert the result to SmartWoo_Invoice_log object and return.
            return self::convert_array_to_logs( $result );
        }

        if ( ! empty( $merged_args['status'] ) ) {
            $query .= $wpdb->prepare( " AND status = %s", $merged_args['status'] );
        }
        if ( ! empty( $merged_args['created_at'] ) ) {
            $query .= $wpdb->prepare( " AND created_at = %s", $merged_args['created_at'] );
        }

        $results = $wpdb->get_results( $query, ARRAY_A );
        // phpcs:enable

        $logs = array();
        foreach ( $results as $data ) {
            $logs[] = self::convert_array_to_logs( $data );
        }
        return $logs;
    }

    /**
     * Method to mark a Refund Log type as refunded
     */
    public static function refunded( $log_id, $note = 'Successfully Refunded' ) {

        $refund = self::get_refund_by_id( $log_id, 'Pending' );

        if ( $refund ) {

            $refund->setStatus( 'Completed' );
            $refund->setNote( $note );
            $refund->update( $refund );

            return true;
        } else {
            return false;
        }
    }

    /**
     * Get refund data by log ID and status.
     *
     * @param string $log_id The log ID to retrieve refund data.
     * @param string $status The status of the refund log to fetch.
     * @return object|null Refund data as a SmartWoo_Invoice_log object, or null if not found.
     */
    public static function get_refund_by_id( $log_id, $status ) {
        global $wpdb;
        // phpcs:disable
        $table_name = SW_INVOICE_LOG_TABLE;
        
        $query = $wpdb->prepare( "
            SELECT log_id, log_type, amount, status, details, note, created_at, updated_at
            FROM $table_name
            WHERE log_id = %s AND status = %s
            LIMIT 1
        ", $log_id, $status );

        $refund_data = $wpdb->get_row( $query, ARRAY_A );
        // phpcs:enable

        if ( $refund_data ) {
            return self::convert_array_to_logs( $refund_data );
        } else {
            return null;
        }
    }
 }

