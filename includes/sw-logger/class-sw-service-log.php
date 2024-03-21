<?php
/**
 * File name   : class-sw-service-log.php
 * Author      : Callistus
 * Description : Class definition file for service logging feature
 *
 * @since      : 1.0.1
 * @package    : SmartWooServiceInvoicing
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access

/**
 * Class SW_Service_Log
 * Model used for service logging feature
 * 
 * @since   : 1.0.1
 * @package : SmartWooServiceInvoicing
 */
class Sw_Service_Log {
    //Props
    private $id;
    private $service_id;
    private $log_type;
    private $details;
    private $note;
    private $created_at;
    private $updated_at;

    /**
     * Setter methods
     * 
     * @param string|int
     */

     public function setId( int $id ) {
        $this->id           = $id;
     }

    public function setServiceId( string $service_id ) {
        $this->service_id   = $service_id;
    }

    public function setLogType( string $log_type ) {
        $this->log_type     = $log_type;
    }

    public function setDetails( string $details ) {
        $this->details      = $details;
    }

    public function setNote( string $note ) {
        $this->note         = $note;
    }

    public function setDateCreated( string $date_string ) {
        $this->created_at   = $date_string;
    }

    public function setDateUpdated( string $date_string ){
        $this->updated_at   = $date_string;
    }

    /**
     * Getter methods to retrieve the values of class properties.
     * 
     * @return string|int The value of the property.
     */

    public function getId() {
        return $this->id;
    }

    public function getServiceId() {
        return $this->service_id;
    }

    public function getLogType() {
        return $this->log_type;
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
     * Method to insert service logs into the database.
     *
     * @return bool True if insertion was successful, false if insertion fails.
     */
    public function save() {
        global $wpdb;
        $table_name = SW_SERVICE_LOG_TABLE;

        // Prepare data to be inserted
        $data = array(
            'service_id'    => sanitize_text_field( $this->getServiceId() ),
            'log_type'      => sanitize_text_field( $this->getLogType() ),
            'details'       => sanitize_text_field( $this->getDetails() ),
            'note'          => sanitize_text_field( $this->getNote() ),
            'created_at'    => current_time( 'mysql' ),
            'updated_at'    => current_time( 'mysql' )
        );
        $data_format = array(
            '%s', // Service ID
            '%s', // Log type
            '%s', // Details
            '%s', // Note
            '%s', // Created at
            '%s'  // Updated At
        );

        // Insert data into the database
        $result = $wpdb->insert( $table_name, $data, $data_format );

        // Return the result of insertion
        return $result;
    }


    /**
     * Method to update logged data
     */
    public function update( self $log ) {
        global $wpdb;
        $table_name = SW_SERVICE_LOG_TABLE;
    
        // Prepare data to be updated
        $data = array(
            'service_id'    => sanitize_text_field( $log->getServiceId() ),
            'log_type'      => sanitize_text_field( $log->getLogType() ),
            'details'       => sanitize_text_field( $log->getDetails() ),
            'note'          => sanitize_text_field( $log->getNote() ),
            'updated_at'    => current_time( 'mysql' )
        );
        $where = array(
            'service_id' => sanitize_text_field( $log->getServiceId() )
        );
        $data_format = array(
            '%s', // Service ID
            '%s', // Log type
            '%s', // Details
            '%s', // Note
            '%s'  // Updated At
        );
    
        // Update data in the database
        $result = $wpdb->update( $table_name, $data, $where, $data_format );
    
        // Return true if update was successful, false otherwise
        return $result !== false;
    }
    
    protected static function convert_array_to_logs( $data ) {
        $log = new Sw_Service_Log();
    
        if ( isset( $data['service_id'] ) ) {
            $log->setServiceId( $data['service_id'] );
        }
    
        if ( isset( $data['log_type'] ) ) {
            $log->setLogType( $data['log_type'] );
        }
    
        if ( isset( $data['details'] ) ) {
            $log->setDetails( $data['details'] );
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
    
    public static function get_logs_by_criteria( $criteria, $value, bool $get_row = false ) {
        global $wpdb;
        $table_name = SW_SERVICE_LOG_TABLE;
        $query   = $wpdb->prepare( "SELECT * FROM $table_name WHERE $criteria = %s", $value );
        if ( true === $get_row ) {
            $results =  $wpdb->get_row( $query, ARRAY_A );
            if ( $results === null ) {
                return false; // Query failed
            }
            return self::convert_array_to_logs( $results );
        } elseif ( false === $get_row ) {
            $results = $wpdb->get_results( $query, ARRAY_A );
            if ( $results === null ) {
                return false; // Query failed
            }
        }
        
        // Convert results to Sw_Service_Log objects
        $logs = array();
        foreach ( $results as $data ) {
            $logs[] = self::convert_array_to_logs( $data );
        }
        
        return $logs;
    }
    
    /**
     * Get all Renewal logs for a given service ID
     * 
     * @param string $service_id The service ID to filter by
     * @return array             Array of Sw_Service_Log objects for renewal logs
     */
    public static function get_renewal_logs_for_service_id($service_id) {
        return self::get_logs_by_criteria('service_id', $service_id);
    }

    /**
     * Retrieve all Sw_Service_Log log_type that is "Renewal"
     * Use this method when you want to get all renewal logs of a service
     */
    public static function get_renewal_log() {
        global $wpdb;

        $table_name  = SW_SERVICE_LOG_TABLE;
        $query       = $wpdb->prepare( "SELECT * FROM $table_name WHERE log_type = %s", 'Renewal' );
        $results     = $wpdb->get_results( $query, ARRAY_A );

        if ( null === $results ) {
            // return empty array
            return array();
        }

        $renewal_log = array();
        foreach ( $results as $data ) {
            $renewal_log[] = self::convert_array_to_logs( $data );
        }

        return $renewal_log;
    }


}