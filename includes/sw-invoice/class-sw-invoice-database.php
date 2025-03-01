<?php
/**
 * Class file for Smart Woo Invoice Database interaction.
 * 
 * @author Callistus.
 * @since 1.0.0
 * @package SmartWoo\Classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Class SmartWoo_Invoice_Database
 *
 * Provides database-related functionality for retrieving and managing SmartWoo_Invoice objects.
 *
 * @since   1.0.0
 * @package SmartWooInvoice
 */
class SmartWoo_Invoice_Database { 

	/**
	 * Get paginated invoices from the database.
	 *
	 * @return array|bool Array of SmartWoo_Invoice object, false otherwise.
	 */
	public static function get_all_invoices( $page = 1, $limit = 20 ) {
		global $wpdb;
		
		// Calculate the offset.
		$offset = ( $page - 1 ) * $limit;
		
		$query = $wpdb->prepare( 
			"SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " ORDER BY `date_created` DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$limit, 
			$offset 
		);
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $results ) ) {
			return $invoices = self::convert_results_to_invoices( $results );

		}

		return false;
	}

	/**
	 * Count all invoices in the database.
	 *
	 * @return int The total number of invoices in the database.
	 */
	public static function count_all() {
		global $wpdb;

		$query = "SELECT COUNT(*) FROM " . SMARTWOO_INVOICE_TABLE;
		$total_invoices = (int) $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $total_invoices;
	}


	/**
	 * Get invoices for a user by the user's ID.
	 * 
	 * @param int $user_id	The ID of the user.
	 */
	public static function get_invoices_by_user( $user_id = 0 ) {
		global $wpdb, $wp_query;
		
		if ( ! is_user_logged_in() ) {
			return;
		}
		$page	= ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$limit 	= ( isset( $_GET['limit'] ) && ! empty( $_GET['limit'] ) ) ? absint( $_GET['limit'] ) : 10; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( smartwoo_is_frontend() ) {
			$page	= ( isset( $wp_query->query_vars['paged'] ) && ! empty( $wp_query->query_vars['paged'] ) ) ? absint( $wp_query->query_vars['paged'] ) : 1;
			$limit 	= 10; 
		}

		
		// Calculate the offset.
		$offset = ( $page - 1 ) * $limit;

		if ( empty( $user_id ) ) {
			false;
		}
		$user_id = absint( $user_id );

		$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE user_id = %s ORDER BY `date_created` DESC LIMIT %d OFFSET %d", absint( $user_id ), $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results	= $wpdb->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		
		if ( $results ) {
			return self::convert_results_to_invoices( $results );
		}
		return false;
	}

	/**
	 * Get an Invoice by it's invoice_id.
	 * 
	 * @param string $invoice_id	The invoice id.
	 * @return SmartWoo_Invoice|false
	 */
	public static function get_invoice_by_id( $invoice_id = '' ) {
		global $wpdb;

		if ( empty( $invoice_id ) ) {
			return false;
		}

		if ( $invoice_id instanceof SmartWoo_Invoice ) {
			$invoice_id = $invoice_id->get_invoice_id();
		}
		
		$invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
		$query  = $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE invoice_id = %s", $invoice_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,	WordPress.DB.PreparedSQL.NotPrepared

		if ( $result ) {
			return SmartWoo_Invoice::convert_array_to_invoice( $result );
		}

		return false;
	}

	/**
	 *  Method to get invoices by service_id.
	 * 
	 * @param string $service_id The ID of the service to retrieve it's invoice.
	 */
	public static function get_invoices_by_service( $service_id = '' ) {
		if ( empty( $service_id ) ) {
			return false;
		}

		global $wpdb;

		if ( $service_id instanceof SmartWoo_Service ) {
			$service_id = $service_id->getServiceId();
		}

		$service_id	= sanitize_text_field( wp_unslash( $service_id ) );
		$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE service_id = %s", $service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results 	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( $results ) {
			return self::convert_results_to_invoices( $results );

		}
		return false;
	}

	/**
	 * Method to get invoices by Invoice Type.
	 * 
	 * @param string $type	The invoice type.
	 */
	public static function get_invoices_by_type( $invoice_type = '' ) {
		global $wpdb;
		if ( empty( $invoice_type ) ) {
			return false;
		}

		$invoice_type	= sanitize_text_field( wp_unslash( $invoice_type ) );
		$query			= $wpdb->prepare( "SELECT * FROM ". SMARTWOO_INVOICE_TABLE ." WHERE invoice_type = %s", $invoice_type ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results 		= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( $results ) {
			return self::convert_results_to_invoices( $results );	
		}
		return false;
	}

	/**
	 * Method to get invoices by Payment Status.
	 * 
	 * @param string $payment_status The invoice payment status.
	 */
	public static function get_invoices_by_payment_status( $payment_status = '' ) {
		global $wpdb, $wp_query;

		if ( empty( $payment_status ) ) {
			return false;
		}

		$payment_status = sanitize_text_field( wp_unslash( $payment_status ) );

		$page	= ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$limit 	= ( isset( $_GET['limit'] ) && ! empty( $_GET['limit'] ) ) ? absint( $_GET['limit'] ) : 10; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		// Calculate the offset.
		$offset = ( $page - 1 ) * $limit;
		$query	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE payment_status = %s ORDER BY `date_created` DESC LIMIT %d OFFSET %d", $payment_status , $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( smartwoo_is_frontend() ) {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			$page	= ( isset( $wp_query->query_vars['paged'] ) && ! empty( $wp_query->query_vars['paged'] ) ) ? absint( $wp_query->query_vars['paged'] ) : 1;
			$limit 	= 10; 
			$offset = ( $page - 1 ) * $limit;

			$query	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE payment_status = %s AND `user_id` = %d ORDER BY `date_created` DESC LIMIT %d OFFSET %d", $payment_status , get_current_user_id(), $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		}
		
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching,	WordPress.DB.PreparedSQL.NotPrepared

		if ( $results ) {
			return self::convert_results_to_invoices( $results );	
		}
		return false;

	}

	/**
	 * Method to get an invoice by Order ID.
	 * 
	 * @param int $order_id	The order ID associated with the invoices.
	 */
	public static function get_invoice_by_order_id( $order_id = 0 ) {
		global $wpdb;

		if ( empty( $order_id ) ) {
			return false;
		}

		if ( $order_id instanceof WC_Order ) {
			$order_id = $order_id->get_id();
		}

		$order_id	= absint( $order_id );
		$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE order_id = %s", $order_id ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results 	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	
		if ( $results ) {
			return self::convert_results_to_invoices( $results );	
		}
		return false;	
	}


	/**
	 * Method to get invoices by date_due.
	 * 
	 * @param string $due_date The due date.
	 */
	public static function get_invoices_by_date_due( $date_due ) {
		global $wpdb;

		if ( empty( $date_due ) ) {
			return false;
		}

		$date_due	= sanitize_text_field( $date_due );
		$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_INVOICE_TABLE . " WHERE date_due = %s", $date_due ); // phpcs:ignore 	WordPress.DB.PreparedSQL.NotPrepared
		$results 	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( $results ) {
			return self::convert_results_to_invoices( $results );	
		}
		return false;
	}

	/**
	 * Get the count of invoices by payment status.
	 *
	 * @param string $payment_status The payment status to filter by.
	 *
	 * @return int The count of invoices with the specified payment status.
	 */
	public static function count_this_status( $payment_status ) {
		global $wpdb;

		$payment_status = sanitize_text_field( wp_unslash( $payment_status  ) );

		$query = $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_INVOICE_TABLE . " WHERE payment_status = %s", $payment_status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( smartwoo_is_frontend() ) {
			$query = $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_INVOICE_TABLE . " WHERE payment_status = %s AND `user_id` = %d", $payment_status, get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		}
		$count = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return absint( $count );
	}

	/**
	 * Get the count of invoices by payment status for the current user.
	 *
	 * @param int    $user_id        The user ID to filter by.
	 * @param string $payment_status The payment status to filter by.
	 *
	 * @return int The count of invoices with the specified payment status for the current user.
	 */
	public static function count_payment_status( $user_id, $payment_status ) {
		global $wpdb;
		if ( ! is_numeric( $user_id ) || ! is_string( $payment_status ) ) {
			return false;
		}

		$user_id		= absint( $user_id );
		$payment_status = sanitize_text_field( wp_unslash( $payment_status ) );
		$query			= $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_INVOICE_TABLE . " WHERE user_id = %d AND payment_status = %s", $user_id, $payment_status ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count 			= $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return absint( $count );
	}

	/**
	 * Count all invoices for a user.
	 * 
	 * @param int $user_id The user's ID
	 * @return int $count The total entries for the user.
	 */
	public static function count_all_by_user( $user_id ) {
		global $wpdb;
		$count = 0;
		if ( empty( $user_id ) ) {
			return $count;
		}
		$query = $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_INVOICE_TABLE . " WHERE user_id = %d", absint( $user_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count = $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return absint( $count );
	}

	/**
	 * Query the invoice database table.
	 * 
	 * @param array $args {
	 *     Array of key-value pairs, where the key is the column name and the value is the value to search for.
	 * }
	 * @param string $mode How we want the result to be retrieved, values can be(single, row, column, all) defaults to row.
	 * @return array|null
	 * @since 2.0.15
	 */
	public static function query( $args, $mode = 'row' ) {
		global $wpdb;
		$table_name = SMARTWOO_INVOICE_TABLE;

		// List of allowed columns for filtering
		$columns = array(
			'service_id',
			'user_id',
			'billing_address',
			'invoice_type',
			'product_id',
			'order_id',
			'amount',
			'fee',
			'payment_status',
			'payment_gateway',
			'transaction_id',
			'date_created',
			'date_paid',
			'date_due',
			'total',
		);

		// Initialize where clause array.
		$where = array();
		$values = array();

		// Loop through args and build the WHERE clause.
		foreach ( $args as $column => $value ) {
			if ( in_array( $column, $columns, true ) ) {
				// Get the data format for this value using the get_data_format method.
				$format		= self::get_data_format( $value );
				$where[] 	= "$column = $format";
				$values[] 	= $value;
			}
		}

		// If we have valid where conditions, build the query
		if ( ! empty( $where ) ) {
			$query 			= "SELECT * FROM {$table_name} WHERE " . implode( ' AND ', $where );
			$prepared_query = $wpdb->prepare( $query, $values );  // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive, query is prepared.
			if ( 'column' === $mode ) {
				$results	= $wpdb->get_col( $prepared_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- False positive, query is prepared.
			} elseif ( 'single' === $mode ) {
				$results	= $wpdb->get_var( $prepared_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- False positive, query is prepared.
			} elseif ( 'all' === $mode ) {
				$results	= $wpdb->get_results( $prepared_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- False positive, query is prepared.
			} else {
				$results	= $wpdb->get_row( $prepared_query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery -- False positive, query is prepared.

			}

			if ( ! empty( $results ) ) {
				return self::convert_results_to_invoices( $results, $mode );

			}
		}

		return null;
	}

	/**
	 * convert database result(s) to SmartWoo_Invoice object.
	 * 
	 * @param mixed|array can be array or anything else.
	 * @param string $mode How results where gotten.
	 * @return array|SmartWoo_Invoice|int|string An array collection of SmartWoo_Invoice or SmartWoo_Invoice object, intiger or string.
	 * @since 2.0.15 Expanded support for data type args and return value.
	 */
	private static function convert_results_to_invoices( $results, $mode = 'all' ) {
		if ( 'single' === $mode ) {
			return $results;
		}

		if ( 'row' === $mode ) {
			return SmartWoo_Invoice::convert_array_to_invoice( $results );
		}

		return array_map(
			function ( $result ) {
				return SmartWoo_Invoice::convert_array_to_invoice( $result );
			},
			$results
		);
	}


	/**
	 * Creates and saves a new invoice in the database.
	 *
	 * @param SmartWoo_Invoice $invoice The SmartWoo_Invoice object to be created and saved.
	 *
	 * @return string|false The ID of the newly inserted invoice or false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function save( SmartWoo_Invoice $invoice ) {
		global $wpdb;
		/**
		 * @since 2.0.15 Proceeds to update invoice if invoice_id already exists in the DB.
		 */
		$table_name = SMARTWOO_INVOICE_TABLE;
		$invoice_exists = $wpdb->get_var(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( "SELECT `invoice_id` FROM {$table_name} WHERE `invoice_id`= %s", $invoice->get_invoice_id() ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- False positive, query is prepared.
		);

		if ( $invoice_exists ) {
			return self::update_invoice( $invoice );
		}

		// Data to be inserted.
		$data = array(
			'service_id'      => $invoice->get_service_id(),
			'user_id'         => $invoice->get_user_id(),
			'billing_address' => $invoice->get_billing_address(),
			'invoice_id'      => $invoice->get_invoice_id(),
			'invoice_type'    => $invoice->get_type(),
			'product_id'      => $invoice->get_product_id(),
			'order_id'        => $invoice->get_order_id(),
			'amount'          => $invoice->get_amount(),
			'fee'             => $invoice->get_fee(),
			'payment_status'  => is_null( $invoice->get_status() ) ? null : $invoice->get_status(),
			'payment_gateway' => is_null( $invoice->get_payment_method() ) ? null : $invoice->get_payment_method(),
			'transaction_id'  => is_null( $invoice->get_transaction_id() ) ? null : $invoice->get_transaction_id(),
			'date_created'    => current_time( 'mysql' ),
			'date_paid'       => is_null( $invoice->get_date_paid() ) ? null : $invoice->get_date_paid(),
			'date_due'        => is_null( $invoice->get_date_due() ) ? null : sanitize_text_field( $invoice->get_date_due() ),
			'total'           => $invoice->get_total(),
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

		if ( $wpdb->insert( SMARTWOO_INVOICE_TABLE, $data, $data_format ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			self::save_all_metadata( $invoice );
			/**
			 * @hook smartwoo_new_invoice_created
			 * @param SmartWoo_Invoice $invoice
			 * @since 2.2.0
			 */
			do_action( 'smartwoo_new_invoice_created', $invoice );
			
			return $invoice->get_invoice_id();
		}
		return false;
	}


	/**
	 * Updates an existing invoice in the database.
	 *
	 * @param SmartWoo_Invoice $invoice The SmartWoo_Invoice object to be updated.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function update_invoice( SmartWoo_Invoice $invoice ) {
		global $wpdb;

		// Data to be updated.
		$data = array(
			'service_id'      => $invoice->get_service_id(),
			'user_id'         => $invoice->get_user_id(),
			'billing_address' => $invoice->get_billing_address(),
			'invoice_type'    => $invoice->get_type(),
			'product_id'      => $invoice->get_product_id(),
			'order_id'        => $invoice->get_order_id(),
			'amount'          => $invoice->get_amount(),
			'fee'             => $invoice->get_fee(),
			'payment_status'  => $invoice->get_status(),
			'payment_gateway' => $invoice->get_payment_method(),
			'transaction_id'  => $invoice->get_transaction_id(),
			'date_created'    => $invoice->get_date_created(),
			'date_paid'       => $invoice->get_date_paid(),
			'date_due'        => $invoice->get_date_due(),
			'total'           => $invoice->get_total(),
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

		// Where condition.
		$where = array(
			'invoice_id' => sanitize_text_field( $invoice->get_invoice_id() ),
		);

		// Where format
		$where_format = array(
			'%s', // invoice_id
		);

		/**
		 * @hook `smartwoo_before_update_invoice` Fires before an invoice is updated.
		 * 
		 * @param string $invoice_id	The invoice ID
		 */
		do_action( 'smartwoo_before_update_invoice', $invoice->get_invoice_id() );

		$updated = $wpdb->update( SMARTWOO_INVOICE_TABLE, $data, $where, $data_format, $where_format ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		
		if ( $updated !== false ) {
			self::save_all_metadata( $invoice );
			/**
			 * @hook `smartwoo_invoice_updated` Fires after an invoice is updated.
			 * 
			 * @param SmartWoo_Invoice $invoice_id
			 */
			do_action( 'smartwoo_invoice_updated', $invoice );
			return $invoice->get_invoice_id();
		}

		return false;
	}

	/**
	 * Save or update all invoice meta data into the database.
	 * 
	 * @param SmartWoo_Invoice $invoice
	 */
	public static function save_all_metadata( SmartWoo_Invoice $invoice ) {
		global $wpdb;
		$table_name = SMARTWOO_INVOICE_META_TABLE;
		$meta_data	= $invoice->get_all_meta();
		$updated = 0;
		foreach( $meta_data as $name => $value ) {
			$data = array(
				'meta_name'		=> $name,
				'meta_value'	=> $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- False positive
				'invoice_id'	=> $invoice->get_invoice_id()
			);

			$data_format = [];
			foreach( $data as $v ) {
				$data_format[] = self::get_data_format( $v );
			}

			$data_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( "SELECT `meta_id` FROM {$table_name} WHERE `invoice_id` = %s AND `meta_name` = %s", $invoice->get_invoice_id(), $name )
			);

			if ( $data_exists ) {
				$where = array( 'invoice_id' => $invoice->get_invoice_id(), 'meta_name' => $name );
				if ( $wpdb->update( $table_name, $data, $where, $data_format, array( '%s' ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$updated++;
				}

				continue;
			} elseif ( $wpdb->insert( $table_name, $data, $data_format ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$updated++;
	
			}
		}

		return $updated;
	}

	/**
	 * Get All Metadata.
	 * 
	 * @return array
	 */
	public static function get_all_metadata( SmartWoo_Invoice $invoice ) {
		global $wpdb;
		$table_name = SMARTWOO_INVOICE_META_TABLE;
		$query		= $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `invoice_id` = %s", $invoice->get_invoice_id() );
		$results	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return ( ! empty( $results ) ) ?  $results : array();
	}

	/**
	 * Updates specified fields of an existing invoice in the database.
	 *
	 * @param string $invoice_id The ID of the invoice to update.
	 * @param array  $fields     An associative array of fields to update and their new values.
	 *
	 * @return SmartWoo_Invoice|false The updated SmartWoo_Invoicenvoice instance on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function update_invoice_fields( $invoice_id, $fields ) {
		global $wpdb;

		// Data to be updated.
		$data        = array();
		$data_format = array();

		foreach ( $fields as $column => $value ) {
			$data[ $column ] = sanitize_text_field( wp_unslash( $value ) );
			$data_format[]  = self::get_data_format( $value );
		}

		// Where condition.
		$where = array(
			'invoice_id' => sanitize_text_field( wp_unslash( $invoice_id ) ),
		);

		// Where format.
		$where_format = array(
			'%s', // invoice_id
		);

		$updated = $wpdb->update( SMARTWOO_INVOICE_TABLE, $data, $where, $data_format, $where_format ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( $updated ) {
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
		if ( is_numeric( $value ) ) {
			return is_float( $value ) ? '%f' : '%d';
		} elseif ( is_bool( $value ) ) {
			return '%d';
		} else {
			return '%s';
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

		$invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
		// Check if the invoice exists.
		$existing_invoice = self::get_invoice_by_id( $invoice_id );
		if ( ! $existing_invoice ) {
			return false;
		}

		/**
		 * delete the invoice.
		 */
		$deleted	= $wpdb->delete( SMARTWOO_INVOICE_TABLE, array( 'invoice_id' => $invoice_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		// Delete all metadata.
		self::delete_all_meta( $existing_invoice );

		// Delete associated order.
		$asso_order	= $existing_invoice->get_order();
		if ( ! empty( $asso_order ) ) {
			$asso_order->delete( true );
		}

		/**
		 * Fires when an invoice is deleted
		 * 
		 * @param SmartWoo_Invoice $existing_invoice
		 */
		do_action( 'smartwoo_invoice_deleted', $existing_invoice );
		return $deleted !== false;
	}

	/**
	 * Delete All Metadata
	 */
	public static function delete_all_meta( SmartWoo_Invoice $invoice ) {
		global $wpdb;
		$deleted	= $wpdb->delete( SMARTWOO_INVOICE_META_TABLE, array( 'invoice_id' => $invoice->get_invoice_id() ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $deleted !== false;
	}

	/**
	 * Delete a single metadata from the database.
	 * 
	 * @param string $invoice_id The invoice ID.
	 * @param string $meta_name The meta name.
	 */
	public static function delete_meta( $invoice_id, $meta_name ) {
		global $wpdb;
		$deleted	= $wpdb->delete( SMARTWOO_INVOICE_META_TABLE, array( 'invoice_id' => $invoice_id, 'meta_name' => $meta_name ), array( '%s', '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $deleted !== false;
	}
}
