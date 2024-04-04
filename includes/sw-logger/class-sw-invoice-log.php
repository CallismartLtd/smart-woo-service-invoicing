<?php
/**
 * File name   : class-sw-logger.php
 * Author      : Callistus
 * Description : Class definition file for Smart Woo Logs
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access
 
 require_once SW_ABSPATH . 'includes/sw-refund/class-sw-refund.php';


/**
 * Class Sw_Invoice_log
 * 
 * Represents the Smart Woo Logs
 */

class Sw_Invoice_log {

    // Properties.
    private $id;
    private $log_id;
    private $log_type;
    private $amount;
    private $status;
    private $details;
    private $note;
    private $created_at;
    private $updated_at;
    
    // Setter Methods.
    public function setLogId( string $log_id ) {
        $this->log_id    = $log_id;
    }

    public function setLogType( string $log_type ) {
        $this->log_type  = $log_type;
    }

    public function setAmount( float $amount ) {
        $this->amount    = $amount;
    }

    public function setStatus( string $status ) {
        $this->status    = $status;
    }

    public function setDetails( string $details ) {
        $this->details   = $details;
    }

    public function setNote( string $note ) {
        $this->note  = $note;
    }
    public function setDateCreated( string $dated_string ) {
        $this->created_at  = $dated_string;
    }
    public function setDateUpdated( string $dated_string ) {
        $this->updated_at  = $dated_string;
    }

    // Getter Methods
    public function getLogId() {
        return $this->log_id;
    }

    public function getLogType() {
        return $this->log_type;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getDetails() {
        return $this->details;
    }

    public function getNote() {
        return $this->note;
    }

    public function getDateCreated() {
        return $this->created_at;
    }
    
    public function getDateUpdated() {
        return $this->updated_at;
    }
    

    /**
     * Convert array to Sw_Invoice_Log
     */
    protected static function convert_array_to_logs( array $data ) {
        $log = new Sw_Invoice_log();
        
        if ( isset($data['log_id'] ) ) {
            $log->setLogId( $data['log_id'] );
        }
        if ( isset($data['log_type'] ) ) {
            $log->setLogType( $data['log_type'] );
        }
        if ( isset( $data['amount'] ) ) {
            $log->setAmount( $data['amount'] );
        }
        if ( isset( $data['status'] ) ) {
            $log->setStatus( $data['status'] );
        }
        if ( isset( $data['details'] ) ) {
            $log->setDetails($data['details']);
        }
        if ( isset( $data['note'] ) ) {
            $log->setNote( $data['note'] );
        }
        if ( isset( $data['created_at'] ) ) {
            $log->setDateCreated( $data['created_at'] );
        }
        if ( isset( $data['updated_at'] ) ) {
            $log->setDateUpdated( $data['updated_at'] );
        }
        
        return $log;
    }
        
    /**
     * Method to log data into sw_service_logs' or 'sw_invoice_logs table
     * 
     * @param object $log     Object of Sw_Invoice_log
     */
    public function save() {
        global $wpdb;
        $table_name = SW_INVOICE_LOG_TABLE;

        // Prepare data to be inserted
        $data = array(
            'log_id'        => sanitize_text_field( $this->getLogId() ),
            'log_type'      => sanitize_text_field( $this->getLogType() ),
            'amount'        => floatval( $this->getAmount() ),
            'status'        => sanitize_text_field( $this->getStatus() ),
            'details'       => sanitize_text_field( $this->getDetails() ),
            'note'          => sanitize_text_field( $this->getNote() ),
            'created_at'    => current_time( 'mysql' ),
            'updated_at'    => current_time( 'mysql' )
        );

        // Data format (for %s, %d, etc.)
        $data_format = array(
            '%s', // log ID
            '%s', // Log Type
            '%f', // Amount
            '%s', // Status
            '%s', // Details
            '%s', // Note
            '%s', // Date Created
            '%s' // Date Updated
        );

        // Insert data into the database
        $result = $wpdb->insert( $table_name, $data, $data_format );

        // Return the newly logged data
        return $result ? $result > 0 : false;
    }

    /**
     * Method to update a log in 'sw_service_logs' or 'sw_invoice_logs' table
     * 
     * @param object $log     Object of Sw_Invoice_log
     */
    public function update( self $log ) {
        global $wpdb;
        $table_name = SW_INVOICE_LOG_TABLE;

        // Prepare data to be updated
        $data = array(
            'log_type'      => sanitize_text_field( $log->getLogType() ),
            'amount'        => floatval( $log->getAmount() ),
            'status'        => sanitize_text_field( $log->getStatus() ),
            'details'       => sanitize_text_field( $log->getDetails() ),
            'note'          => sanitize_text_field( $log->getNote() ),
            'updated_at'    => current_time( 'mysql' ), // Update the updated_at timestamp
        );

        // Data format (for %s, %d, etc.)
        $where = array(
            'log_id' => sanitize_text_field( $log->getLogId() ),
        );

        $data_format = array(
            '%s', // Log Type
            '%f', // Amount
            '%s', // Status
            '%s', // Details
            '%s', // Note
            '%s', // Date Updated
        );

        $where_format = array(
            '%s', // Log ID
        );

        // Update data in the database
        $wpdb->update( $table_name, $data, $where, $data_format, $where_format );

        // Return the updated log data
        return $log;
    }
    
    /**
     * Get Logged data based on given criteria
     * 
     * @since : 1.0.1 
     * @param string $criteria The criteria for filtering
     * @param mixed  $value    The value to filter by
     * @param string $row      The columns to select (optional, defaults to '*')
     * @return array           Array of Sw_Invoice_log objects
     */
    public static function get_logs_by_criteria( $criteria, $value, bool $get_row = false ) {
        global $wpdb;
        $table_name = SW_INVOICE_LOG_TABLE;
        $query   = $wpdb->prepare( "SELECT * FROM $table_name WHERE $criteria = %s", $value );
        if ( true === $get_row ) {
            $results =  $wpdb->get_row( $query, ARRAY_A );
            return self::convert_array_to_logs( $results );
        } elseif ( false === $get_row ) {
            $results = $wpdb->get_results( $query, ARRAY_A );
        }
        
        if ( $results === null ) {
            // Handle database query failure
            return array(); // Return an empty array or throw an exception
        }
        
        // Convert results to Sw_Invoice_log objects
        $logs = array();
        foreach ( $results as $data ) {
            $logs[] = self::convert_array_to_logs( $data );
        }
        
        return $logs;
    }        
    

    /**
     * Clean up logs by log_id
     * 
     * @since : 1.0.1 
     * @param string $log_id The log_id to delete
     * @return void
     */
    public static function cleanup_logs_by_id( $log_id ) {
        global $wpdb;
        $table_name = SW_INVOICE_LOG_TABLE;

        $where = array(
            'log_id = %s',
        );

        $where_format = array(
            '%s',
        );

        // Delete logs by log_id
        $wpdb->delete( $table_name, $where, array( $log_id ), $where_format );
    }

    /**
     * Render log data for a given log ID in HTML output.
     *
     * @since 1.0.1
     * @param string $log_id The log ID for which to render data.
     * @return string HTML representation of the log data.
     */
    public static function render_log_html_output( $log_id ) {
        $logs = self::get_logs_by_criteria( 'log_id', $log_id );
        
        $output = '<div class="serv-details-card">';

        if ( empty( $logs ) ) {
           $output .= smartwoo_notice( 'No log data found.' );
           $output .= '</div>';

           return $output;
        }
            $output .= '<h3> Logged Info</h3>';

        foreach ( $logs as $log ) {
            $output .= '<p class="invoice-details-item"><span> Log ID:</span>' . esc_html( $log->getLogId() ) . '</p>';
            $output .= '<p class="invoice-details-item"><span> Log Type:</span>' . esc_html( $log->getLogType() ) . '</p>';
            $output .= '<p class="invoice-details-item"><span> Log Amount:</span>' . get_woocommerce_currency_symbol() . esc_html( $log->getAmount() ) . '</p>';
            $output .= '<p class="invoice-details-item"><span> Log Status:</span>' . esc_html( $log->getStatus() ) . '</p>';
            $output .= '<p class="invoice-details-item"><span> Log Details:</span>' . esc_html( $log->getDetails() ) . '</p>';
            if ( is_admin() ) {
                $output .= '<p class="invoice-details-item"><span> Internal Note:</span>' . esc_html( $log->getNote() ) . '</p>';
            }
            $output .= '<p class="invoice-details-item"><span> Date Created:</span>' . esc_html( smartwoo_check_and_format( $log->getDateCreated() ) ) . '</p>';
            if ( ! empty( $log->getDateUpdated() ) ) {
                $output .= '<p class="invoice-details-item"><span> Last Updated:</span>' . esc_html( smartwoo_check_and_format( $log->getDateUpdated() ) ) . '</p>';
            }
                $output .= '<hr>';
            $output .= '<hr>';

        }

        $output .= '</div>';

        return $output;
    }

}