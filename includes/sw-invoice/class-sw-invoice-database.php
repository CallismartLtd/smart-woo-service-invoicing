<?php

/**
 * Class Sw_Invoice_Database
 *
 * Provides database-related functionality for retrieving and managing Sw_Invoice objects.
 *
 * @since   1.0.0
*/

class Sw_Invoice_Database {

 /**
     * Retrieves invoices from the database based on various criteria.
     *
     * @since   1.0.0
     */

    // Method to get invoices based on criteria
    public static function get_invoices_by_criteria( $criteria, $value ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sw_invoice';

        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE $criteria = %s", $value );
        $results = $wpdb->get_results( $query, ARRAY_A );

        return self::convert_results_to_invoices( $results );
    }



    // Method to get all invoices from the database
    public static function get_all_invoices() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sw_invoice';

        $query = "SELECT * FROM $table_name";
        $results = $wpdb->get_results( $query, ARRAY_A );

        return self::convert_results_to_invoices( $results );
    }


    // Method to get invoices by user_id
    public static function get_invoices_by_user( $user_id ) {
        return self::get_invoices_by_criteria( 'user_id', $user_id );
    }

    public static function get_invoice_by_id( $invoice_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sw_invoice';
    
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE invoice_id = %s", $invoice_id );
        $result = $wpdb->get_row( $query, ARRAY_A );
    
        if ($result) {
            // Convert the array result to Sw_Invoice object
            return Sw_Invoice::convert_array_to_invoice( $result );
        }
    
        return false;
    }    

    // Method to get invoices by service_id
    public static function get_invoices_by_service( $service_id ) {
        return self::get_invoices_by_criteria( 'service_id', $service_id );
    }

    // Method to get invoices by Invoice Type
    public static function get_invoices_by_type( $invoice_type ) {
        return self::get_invoices_by_criteria( 'invoice_type', $invoice_type );
    }

    // Method to get invoices by Payment Status
    public static function get_invoices_by_payment_status( $payment_status ) {
        return self::get_invoices_by_criteria( 'payment_status', $payment_status );
    }

    // Method to get invoices by Order ID
    public static function get_invoices_by_order_id( $order_id ) {
        return self::get_invoices_by_criteria( 'order_id', $order_id );
    }
    

    // Method to get invoices by date_due
    public static function get_invoices_by_date_due( $date_due ) {
        return self::get_invoices_by_criteria( 'date_due', $date_due );
    }

    /**
     * Get the count of invoices by payment status.
     *
     * @param string $payment_status The payment status to filter by.
     *
     * @return int The count of invoices with the specified payment status.
     */
    public static function get_invoice_count_by_payment_status( $payment_status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sw_invoice';

        $query = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE payment_status = %s", $payment_status );
        $count = $wpdb->get_var( $query );

        return intval( $count );
    }

    /**
     * Get the count of invoices by payment status for the current user.
     *
     * @param int    $user_id        The user ID to filter by.
     * @param string $payment_status The payment status to filter by.
     *
     * @return int The count of invoices with the specified payment status for the current user.
     */
    public static function get_invoice_count_by_payment_status_for_user( $user_id, $payment_status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sw_invoice';

        $query = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND payment_status = %s", $user_id, $payment_status );
        $count = $wpdb->get_var( $query );

        return intval( $count );
    }

    
    private static function convert_results_to_invoices( $results ) {
        if ( ! is_array($results)) {
            // Handle the case when $results is not an array (e.g., single result)
            $results = [$results];
        }
    
        return array_map(function ( $result ) {
            return Sw_Invoice::convert_array_to_invoice( $result );
        }, $results);
    }

    
    /**
     * Creates and saves a new invoice in the database.
     *
     * @param Sw_Invoice $invoice The Sw_Invoice object to be created and saved.
     *
     * @return string|false The ID of the newly inserted invoice or false on failure.
     *
     * @since 1.0.0
     */
    public static function sw_create_invoice( Sw_Invoice $invoice ) {
        global $wpdb;

        // Our table name
        $table_name = $wpdb->prefix . 'sw_invoice';

        // Data to be inserted
        $data = array(
            'service_id'       => sanitize_text_field( $invoice->getServiceId() ),
            'user_id'          => absint( $invoice->getUserId() ),
            'billing_address'  => sanitize_text_field( $invoice->getBillingAddress() ),
            'invoice_id'       => sanitize_text_field( $invoice->getInvoiceId() ),
            'invoice_type'     => sanitize_text_field( $invoice->getInvoiceType() ),
            'product_id'       => absint( $invoice->getProductId() ),
            'order_id'         => absint( $invoice->getOrderId() ),
            'amount'           => absint( $invoice->getAmount() ),
            'fee'              => absint( $invoice->getFee() ),
            'payment_status'   => is_null( $invoice->getPaymentStatus() ) ? null : sanitize_text_field( $invoice->getPaymentStatus() ),
            'payment_gateway'  => is_null( $invoice->getPaymentGateway() ) ? null : sanitize_text_field( $invoice->getPaymentGateway() ),
            'transaction_id'   => is_null( $invoice->getTransactionId() ) ? null : sanitize_text_field( $invoice->getTransactionId() ),
            'date_created'     => sanitize_text_field( $invoice->getDateCreated() ),
            'date_paid'        => is_null($invoice->getDatePaid() ) ? null : sanitize_text_field( $invoice->getDatePaid() ) ,
            'date_due'         => is_null( $invoice->getDateDue() ) ? null : sanitize_text_field( $invoice->getDateDue() ),
            'total'            => absint( $invoice->getTotal() ),
        );

        // Data format (for %s, %d, etc.)
        $data_format = array(
            '%s', // service_id
            '%d', // user_id
            '%s', // billing_address
            '%s', // invoice_id
            '%s', // invoice_type
            '%d', // product_id
            '%d', // order_id
            '%f', // amount
            '%f', // fee
            '%s', // payment_status
            '%s', // payment_gateway
            '%s', // transaction_id
            '%s', // date_created
            '%s', // date_paid
            '%s', // date_due
            '%f', // total
        );

        // Insert data into the database
        $wpdb->insert( $table_name, $data, $data_format );

        // Return the ID of the newly inserted invoice or false on failure
        return $invoice->getInvoiceId();
    }


    /**
     * Updates an existing invoice in the database.
     *
     * @param Sw_Invoice $invoice The Sw_Invoice object to be updated.
     *
     * @return bool True on success, false on failure.
     *
     * @since 1.0.0
     */
    public static function update_invoice( Sw_Invoice $invoice ) {
        global $wpdb;

        // Our table name
        $table_name = $wpdb->prefix . 'sw_invoice';

        // Data to be updated
        $data = array(
            'service_id'       => sanitize_text_field( $invoice->getServiceId() ),
            'user_id'          => absint( $invoice->getUserId() ),
            'billing_address'  => sanitize_text_field( $invoice->getBillingAddress() ),
            'invoice_type'     => sanitize_text_field($invoice->getInvoiceType()),
            'product_id'       => absint( $invoice->getProductId() ),
            'order_id'         => absint( $invoice->getOrderId() ),
            'amount'           => floatval( $invoice->getAmount() ),
            'fee'              => floatval( $invoice->getFee() ),
            'payment_status'   => sanitize_text_field( $invoice->getPaymentStatus() ),
            'payment_gateway'  => sanitize_text_field( $invoice->getPaymentGateway() ),
            'transaction_id'   => sanitize_text_field( $invoice->getTransactionId() ),
            'date_created'     => sanitize_text_field( $invoice->getDateCreated() ),
            'date_paid'       => is_null( $invoice->getDatePaid() ) ? null : sanitize_text_field( $invoice->getDatePaid() ),
            'date_due'         => sanitize_text_field( $invoice->getDateDue() ),
            'total'            => floatval( $invoice->getTotal() ),
        );

        // Data format (for %s, %d, etc.) 
        $data_format = array(
            '%s', // service_id
            '%d', // user_id
            '%s', // billing_address
            '%s', // invoice_type
            '%d', // product_id
            '%d', // order_id
            '%f', // amount
            '%f', // fee
            '%s', // payment_status
            '%s', // payment_gateway
            '%s', // transaction_id
            '%s', // date_created
            '%s', // date_paid
            '%s', // date_due
            '%f', // total
        );

        // Where condition
        $where = array(
            'invoice_id' => sanitize_text_field( $invoice->getInvoiceId() ),
        );

        // Where format
        $where_format = array(
            '%s', // invoice_id
        );

        // Update data in the database
        $updated = $wpdb->update( $table_name, $data, $where, $data_format, $where_format );

        // Return true on success, false on failure
        return $updated !== false;
    }


    /**
     * Updates specified fields of an existing invoice in the database.
     *
     * @param string $invoice_id The ID of the invoice to update.
     * @param array  $fields     An associative array of fields to update and their new values.
     *
     * @return Sw_Invoice|bool The updated Sw_Invoice instance on success, false on failure.
     *
     * @since 1.0.0
     */
    public static function update_invoice_fields( $invoice_id, $fields ) {
        global $wpdb;

        // Our table name
        $table_name = $wpdb->prefix . 'sw_invoice';

        // Data to be updated
        $data = array();
        $data_format = array();

        foreach ( $fields as $field => $value ) {
            $data[ $field ] = sanitize_text_field( $value ); // Sanitize data before updating
            $data_format[] = self::get_data_format( $value );
        }

        // Where condition
        $where = array(
            'invoice_id' => sanitize_text_field( $invoice_id ),
        );

        // Where format
        $where_format = array(
            '%s', // invoice_id
        );

        // Update data in the database
        $updated = $wpdb->update( $table_name, $data, $where, $data_format, $where_format );

        if ( $updated !== false ) {
            // Fetch the updated invoice from the database
            $updated_invoice = self::get_invoice_by_id( $invoice_id );

            if ( $updated_invoice ) {
                return $updated_invoice;
            }
        }

        return false;
    }


    /**
     * Get the data format for a given value.
     *
     * @param mixed $value The value for which to determine the data format.
     *
     * @return string The data format.
     */
    private static function get_data_format( $value ) {
        if (is_numeric( $value ) ) {
            return is_float( $value ) ? '%f' : '%d';
        } elseif (is_bool( $value ) ) {
            return '%d'; // Assuming boolean values are stored as integers (0 or 1)
        } elseif ( $value instanceof DateTime ) {
            return '%s'; // Assuming DateTime values are stored as strings
        } else {
            return is_string( $value ) ? '%s' : '%s'; // Default to string if the type is unknown
        }
    }

    /**
     * Deletes an invoice from the database.
     *
     * @param string $invoice_id The ID of the invoice to delete.
     *
     * @return bool True on success, false on failure.
     *
     * @since 1.0.0
     */
    public static function delete_invoice( $invoice_id ) {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'sw_invoice';
    
        // Check if the invoice exists
        $existing_invoice = self::get_invoice_by_id( $invoice_id );
        if ( ! $existing_invoice ) {
            return 'Invoice not found.'; // Return an error message
        }
    
        // Perform the deletion
        $deleted = $wpdb->delete( $table_name, array( 'invoice_id' => $invoice_id ), array( '%s' ) );
    
        if ( false === $deleted ) {
            return 'Error deleting invoice.'; // Return an error message
        }
    
        return 'Invoice deleted successfully.';
    }
    

}
