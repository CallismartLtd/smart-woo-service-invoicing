<?php

/**
 * Class SmartWoo_Service_Database
 *
 * Provides database-related functionality for retrieving and managing SmartWoo_Service objects.
 *
 * @since   1.0.0
 */
class SmartWoo_Service_Database {

	/**
	 * Retrieves services from the database based on various criteria.
	 *
	 * @param string $criteria The criteria to filter the services.
	 * @param mixed  $value    The value to match for the given criteria.
	 *
	 * @return array An array of SmartWoo_Service objects.
	 *
	 * @since 1.0.0
	 */
	public static function get_services_by_criteria( $criteria, $value ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sw_service';
		// phpcs:disable
		$query   	= $wpdb->prepare( "SELECT * FROM $table_name WHERE $criteria = %s", $value );
		$results 	= $wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable
		return self::convert_results_to_services( $results );
	}

	/**
	 * Retrieves all services from the database.
	 *
	 * @return array An array of SmartWoo_Service objects.
	 *
	 * @since 1.0.0
	 */
	public static function get_all_services() {
		global $wpdb;
		// phpcs:disable
		$table_name = $wpdb->prefix . 'sw_service';
		$query   	= "SELECT * FROM $table_name";
		$results 	= $wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable
		return self::convert_results_to_services( $results );
	}

	/**
	 * Retrieves a service by its ID from the database.
	 *
	 * @param string $service_id The ID of the service to retrieve.
	 *
	 * @return SmartWoo_Service|false The SmartWoo_Service object if found, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public static function get_service_by_id( $service_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sw_service';
		// phpcs:disable
		$query  = $wpdb->prepare( "SELECT * FROM $table_name WHERE service_id = %s", $service_id );
		$result = $wpdb->get_row( $query, ARRAY_A );
		// phpcs:enable
		if ( $result ) {
			// Convert the array result to SmartWoo_Service object
			return SmartWoo_Service::convert_array_to_service( $result );
		}

		return false;
	}

	/**
	 * Retrieves a service by User ID from the database.
	 *
	 * @param int $user_id The ID of the user to for.
	 *
	 * @return SmartWoo_Service|false The SmartWoo_Service object if found, false otherwise.
	 *
	 * @since 1.0.0
	 */
	public static function get_services_by_user( $user_id ) {
		return self::get_services_by_criteria( 'user_id', $user_id );
	}



	/**
	 * Creates and saves a new service in the database.
	 *
	 * @param SmartWoo_Service $service The SmartWoo_Service object to be created and saved.
	 *
	 * @return int|false The ID of the newly inserted service or false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function sw_create_service( SmartWoo_Service $service ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sw_service';

		$data = array(
			'user_id'           => absint( $service->getUserId() ),
			'product_id'        => absint( $service->getProductId() ),
			'service_name'      => sanitize_text_field( $service->getServiceName() ),
			'service_url'       => esc_url_raw( $service->getServiceUrl() ),
			'service_type'      => sanitize_text_field( $service->getServiceType() ),
			'service_id'        => sanitize_text_field( $service->getServiceId() ),
			'invoice_id'        => sanitize_text_field( $service->getInvoiceId() ),
			'start_date'        => sanitize_text_field( $service->getStartDate() ),
			'end_date'          => sanitize_text_field( $service->getEndDate() ),
			'next_payment_date' => sanitize_text_field( $service->getNextPaymentDate() ),
			'billing_cycle'     => sanitize_text_field( $service->getBillingCycle() ),
			'status'            => sanitize_text_field( $service->getStatus() ),
		);

		$data_format = array(
			'%d', // user_id
			'%d', // product_id
			'%s', // service_name
			'%s', // service_url
			'%s', // service_type
			'%s', // service_id
			'%s', // invoice_id
			'%s', // start_date
			'%s', // end_date
			'%s', // next_payment_date
			'%s', // billing_cycle
			'%s', // status
		);

		// phpcs:disable
		$wpdb->insert( $table_name, $data, $data_format );
		// phpcs:enable
		return $service->getServiceId();
	}

	/**
	 * Updates an existing service in the database.
	 *
	 * @param SmartWoo_Service $service The SmartWoo_Service object to be updated.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function update_service( SmartWoo_Service $service ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sw_service';
		$data 		= array(
			'user_id'           => absint( $service->getUserId() ),
			'product_id'        => absint( $service->getProductId() ),
			'service_name'      => sanitize_text_field( $service->getServiceName() ),
			'service_url'       => esc_url_raw( $service->getServiceUrl() ),
			'service_type'      => sanitize_text_field( $service->getServiceType() ),
			'invoice_id'        => sanitize_text_field( $service->getInvoiceId() ),
			'start_date'        => sanitize_text_field( $service->getStartDate() ),
			'end_date'          => sanitize_text_field( $service->getEndDate() ),
			'next_payment_date' => sanitize_text_field( $service->getNextPaymentDate() ),
			'billing_cycle'     => sanitize_text_field( $service->getBillingCycle() ),
			'status'            => is_null( $service->getStatus() ) ? null : sanitize_text_field( $service->getStatus() ),
		);

		$data_format = array(
			'%d', // user_id
			'%d', // product_id
			'%s', // service_name
			'%s', // service_url
			'%s', // service_type
			'%s', // invoice_id
			'%s', // start_date
			'%s', // end_date
			'%s', // next_payment_date
			'%s', // billing_cycle
			'%s', // status
		);

		$where = array(
			'service_id' => sanitize_text_field( $service->getServiceId() ),
		);

		$where_format = array(
			'%s', // service_id
		);

		// phpcs:disable
		$updated = $wpdb->update( $table_name, $data, $where, $data_format, $where_format );
		// phpcs:enable
		return $updated !== false;
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
		} elseif ( $value instanceof DateTime ) {
			return '%s';
		} else {
			return is_string( $value ) ? '%s' : '%s';
		}
	}

	/**
	 * Updates specified fields of an existing service in the database.
	 *
	 * @param string $service_id The ID of the service to update.
	 * @param array  $fields     An associative array of fields to update and their new values.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function update_service_fields( $service_id, $fields ) {
		global $wpdb;

		$table_name 	= $wpdb->prefix . 'sw_service';
		$data			= array();
		$data_format 	= array();

		foreach ( $fields as $field => $value ) {
			$data[ $field ] = sanitize_text_field( $value );
			$data_format[]  = self::get_data_format( $value );
		}

		$where = array(
			'service_id' => sanitize_text_field( $service_id ),
		);

		$where_format = array(
			'%s', // service_id
		);

		// phpcs:disable
		$updated = $wpdb->update( $table_name, $data, $where, $data_format, $where_format );
		// phpcs:enable
		return $updated !== false;
	}


	/**
	 * Converts an array of database results to an array of SmartWoo_Service objects.
	 *
	 * @param array $results An array of database results.
	 *
	 * @return array An array of SmartWoo_Service objects.
	 *
	 * @since 1.0.0
	 */
	private static function convert_results_to_services( $results ) {
		$services = array();

		foreach ( $results as $result ) {
			$services[] = SmartWoo_Service::convert_array_to_service( $result );
		}

		return $services;
	}

		/**
		 * Deletes a service from the database.
		 *
		 * @param string $service The ID of the service to delete.
		 *
		 * @return bool True on success, false on failure.
		 *
		 * @since 1.0.0
		 */
	public static function delete_service( $service_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sw_service';

		// Check if the service exists
		$existing_service = self::get_service_by_id( $service_id );
		if ( ! $existing_service ) {
			return 'Service not found.';
		}

		// phpcs:disable
		$deleted = $wpdb->delete( $table_name, array( 'service_id' => $service_id ), array( '%s' ) );
		// phpcs:enable

		if ( false === $deleted ) {
			return 'Error deleting Service.';
		}

		return 'Service deleted successfully.';
	}
}
