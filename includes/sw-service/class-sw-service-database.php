<?php
/**
 * Class file for Smart Woo Service Database interactions.
 * 
 * @author Callistus.
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Class SmartWoo_Service_Database
 * Provides database-related functionality for retrieving and managing SmartWoo_Service objects.
 * 
 * @package SmartWooService
 * @since   1.0.0
 */
class SmartWoo_Service_Database {

	/**
	 * Retrieves all services from the database.
	 *
	 * @return array An array of SmartWoo_Service objects.
	 *
	 * @since 1.0.0
	 * @since 1.0.14 Restricted this method to cron jobs only.
	 */ 
	public static function get_all_services() {
		if ( ! wp_doing_cron() ) {
			return;
		}
		
		global $wpdb;
		$query   	= "SELECT * FROM ". SMARTWOO_SERVICE_TABLE; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results 	= $wpdb->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		if ( $results ) {
			return self::convert_results_to_services( $results );
		}
		// Empty array.
		return array();
	}

	/**
	 * Get all services from database with pagination technique.
	 */
	public static function get_all() {
		global $wpdb;

		$page	= ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1; // phpcs:disable
		$limit 	= ( isset( $_GET['limit'] ) && ! empty( $_GET['limit'] ) ) ? absint( $_GET['limit'] ) : 10; // phpcs:disable
		
		// Calculate the offset.
		$offset = ( $page - 1 ) * $limit;
 		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$query = $wpdb->prepare( 
			"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " ORDER BY `id` DESC LIMIT %d OFFSET %d",
			$limit, 
			$offset 
		);
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $results ) ) {
			return $invoices = self::convert_results_to_services( $results );

		}

		return false;
	}

	/**
	 * Retrieves a service by its ID from the database.
	 *
	 * @param string $service_id The ID of the service to retrieve.
	 *
	 * @return SmartWoo_Service|false The SmartWoo_Service object if found, false otherwise.
	 *
	 * @since 1.0.0
	 * @since 2.0.12 Implemented object caching.
	 */
	public static function get_service_by_id( $service_id ) {
		$service	= wp_cache_get( 'smartwoo_service_' . $service_id );
		if ( false !== $service ) {
			return $service;
		}

		global $wpdb;

		$query  = $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE service_id = %s", $service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		if ( $result ) {
			// Convert the array result to SmartWoo_Service object
			$service	= SmartWoo_Service::convert_array_to_service( $result );
			wp_cache_set( 'smartwoo_service_' . $service_id, $service, 'smartwoo_service', HOUR_IN_SECONDS );
			
			return $service;
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
	public static function get_services_by_user( $user_id = '' ) {
		if ( empty( $user_id ) ) {
			return $user_id; // User ID must be provided.
		}

		$user_id 	= absint( $user_id );
		$services	= wp_cache_get( 'smartwoo_user_services_' . $user_id );

		if ( false === $services ) {
			global $wpdb;
			$services 	= array();
			$query   	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE user_id = %d", $user_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results 	= $wpdb->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
	
			if ( $results ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( 'smartwoo_user_services_' . $user_id, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}
		// Return empty array.
		return $services;
	}

	/**
	 * Get services that are within the "Active" range or have the "Active" status override.
	 * 
	 * @param int $page  The current page being requested.
	 * @param int $limit The limit for the current page.
	 * @since 2.0.12
	 * @return array|null Array of services or null if parameters are invalid.
	 */
	public static function get_all_active( $page = 1, $limit = null ) {
		if ( empty( $page ) ) {
			return null; // Return null for invalid input.
		}
		
		$offset 	= ( $page - 1 ) * $limit;
		$cache_key	= 'smartwoo_all_active_services_' . $page . '_' . $offset;
		if ( smartwoo_is_frontend() ) {
			$cache_key	= 'smartwoo_all_' . get_current_user_id() .'_active_services_' . $page . '_' . $offset;
		}

		$services	= wp_cache_get( $cache_key );
		
		if ( false === $services ) {
			global $wpdb;
			$services	= array();
			$query = "
				SELECT * FROM " . SMARTWOO_SERVICE_TABLE . 
				" WHERE ( 
					`status` = %s 
					OR (
						(`status` IS NULL OR `status` = %s) 
						AND `next_payment_date` > CURDATE() 
						AND `end_date` > CURDATE()
					)
				)";
		
			// Append user condition if in frontend.
			if ( smartwoo_is_frontend() ) {
				$query .= " AND `user_id` = %d";
			}
		
			// Append LIMIT and OFFSET if $limit is provided.
			if ( ! empty( $limit ) ) {
				$query .= " LIMIT %d OFFSET %d";
			}
		
			// Prepare the query with necessary parameters.
			if ( smartwoo_is_frontend() ) {
				if ( ! empty( $limit ) ) {
					$query = $wpdb->prepare( $query, 'Active', '', get_current_user_id(), $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive
				} else {
					$query = $wpdb->prepare( $query, 'Active', '', get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- False positive.
				}
			} else {
				if ( ! empty( $limit ) ) {
					$query = $wpdb->prepare( $query, 'Active', '', $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				} else {
					$query = $wpdb->prepare( $query, 'Active', '' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}
			}
		
			$results = $wpdb->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			
			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( $cache_key, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}
	
		return $services;
	}

	/**
	 * Get services that have custom labels (status).
	 * 
	 * @param array $args Associative array of args.
	 * @return array Array of SmartWoo_Service Objects or empty array.
	 */
	public static function get_( $args ) {
		// Ensure that $args is an array.
		if ( ! is_array( $args ) ) {
			return array();
		}

		// Default arguments.
		$default_args = array(
			'page'   => 1,
			'limit'  => 10,
			'status' => 'Pending'
		);

		// Parse incoming arguments and merge them with defaults.
		$parsed_args	= wp_parse_args( $args, $default_args );
		$offset      	= ( $parsed_args['page'] - 1 ) * $parsed_args['limit'];
		$cache_key		= 'smartwoo_get_'. $parsed_args['status'] . '_' . $parsed_args['page'] . '_' . $offset;
		if ( smartwoo_is_frontend() ) {
			$cache_key	= 'smartwoo_' . get_current_user_id() . 'get_' . $parsed_args['status'] . '_' . $parsed_args['page'] . '_' . $offset;
		}
		
		$services	= wp_cache_get( $cache_key );

		if ( false === $services ) {
			global $wpdb;
			$services = array();
			// Start building the query.
			$query = $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `status` = %s", sanitize_text_field( wp_unslash( $parsed_args['status'] ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Check if on the frontend to filter by user.
			if ( smartwoo_is_frontend() ) {
				$query .= $wpdb->prepare( " AND `user_id` = %d", get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Add pagination if limit is set.
			if ( ! empty( $parsed_args['limit'] ) ) {
				$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $parsed_args['limit'], $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Execute the query and get the results.
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			// Return the converted results or an empty array.
			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( $cache_key, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}

	
	/**
	 * Get services that are within "Due for Renewal" range or have the "Due for Renewal" status override.
	 * 
	 * @param int $page  The current page being requested.
	 * @param int $limit The limit for the current page.
	 * @since 2.0.12
	 * @return array|null Array of services or null if parameters are invalid.
	 */
	public static function get_all_due( $page = 1, $limit = 10 ) {
		if ( empty( $page ) ) {
			return null; // Return null for invalid input.
		}

		$offset = ( $page - 1 ) * $limit;
		$cache_key	= 'smartwoo_all_dueservices_' . $page . '_' . $offset;
		if( smartwoo_is_frontend() ) {
			$cache_key	= 'smartwoo_all_' . get_current_user_id() .'_due_services_' . $page . '_' . $offset;
		}
		
		$services	= wp_cache_get( $cache_key );

		if ( false === $services ) {
			global $wpdb;
			$services	= array();
			// Base query for backend.
			// phpcs:disable
			$query = $wpdb->prepare(
				"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " 
				WHERE (
					`status` = %s
					OR ( (`status` IS NULL OR `status` = %s)
						AND `next_payment_date` <= CURDATE() AND `end_date` > CURDATE()
					)
					
				
				)", 
				
				'Due for Renewal',
				''
			);

			// Add pagination for backend or frontend users if limit is specified.
			if ( ! empty( $limit ) ) {
				$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			// Modify query for frontend users.
			if ( smartwoo_is_frontend() ) {
				$query = $wpdb->prepare(
					"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . "
					WHERE (
						`status` = %s
						OR ( (`status` IS NULL OR `status` = %s)
							AND `next_payment_date` <= CURDATE() AND `end_date` > CURDATE()
						)
					) 
					AND `user_id` = %d",
					'Due for Renewal',
					'',
					get_current_user_id()
				);

				// Add pagination for frontend if limit is specified.
				if ( ! empty( $limit ) ) {
					$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				}
			}

			// Fetch results.
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			
			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( $cache_key, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}


		return $services;
	}


	/**
	 * Get services that are within the "Expired" date range or has "Expired" status overide.
	 * 
	 * @param int $page		The current page being requested.
	 * @param int $limit	The limit for the current page.
	 * @return null|array	Null for invalid input, array of SmartWoo_Service or empty array otherwise.
	 * @since 2.0.12
	 */
	public static function get_all_on_grace( $page =  1, $limit = 10 ) {
		if ( empty( $page ) ) {
			return null; // Return null for invalid input.
		}

		$offset 	= ( $page - 1 ) * $limit;
		$cache_key	= 'smartwoo_all_grace_services_' . $page . '_' . $offset;
		
		if ( smartwoo_is_frontend() ) {
			$cache_key	= 'smartwoo_all_' . get_current_user_id() .'_grace_services_' . $page . '_' . $offset;
		}

		$services	= wp_cache_get( $cache_key );
		if ( false === $services ) {
			global $wpdb;
			$services	= array();
			$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				" WHERE (
					`status` = %s
					OR (
						(`status` IS NULL OR `status` = %s)
						AND `end_date` <= CURDATE()
					)
					
				)
				
			", 'Grace Period', '');
			
			if ( smartwoo_is_frontend() ) {
				$query	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE (`end_date` < CURDATE() OR `status` = %s) AND `user_id` = %d", 'Grace Period', get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
	
			if ( ! empty( $limit ) ) {
				$query	.= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset );
			}
	
			$results	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
	
			if ( ! empty( $results ) ) {
				$the_services	= self::convert_results_to_services( $results );
	
				foreach( $the_services as $service ) {
					if ( smartwoo_is_service_on_grace( $service ) ) {
						$services[]	= $service;
					}
	
				}
				wp_cache_set( $cache_key, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}

	/**
	 * Get services that are within the "Expired" date range or has "Expired" status overide.
	 * 
	 * @param int $page		The current page being requested.
	 * @param int $limit	The limit for the current page.
	 * @since 2.0.12
	 */
	public static function get_all_expired( $page =  1, $limit = 10 ) {
		if ( empty( $page ) ) {
			return null; // Return null for invalid input.
		}

		global $wpdb;

		$offset 	= ( $page - 1 ) * $limit;
		$cache_key	= 'smartwoo_all_expired_services_' . $page . '_' . $offset;
		if ( smartwoo_is_frontend() ) {
			$cache_key	= 'smartwoo_all_' . get_current_user_id() .'_expired_services_' . $page . '_' . $offset;
		}
		$services	= wp_cache_get( $cache_key );

		if ( false === $services ) {
			$today		= current_time( 'Y-m-d' );
			$services	= array();
			$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `end_date` < %s OR `status` = %s", $today, 'Expired' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			
			if ( smartwoo_is_frontend() ) {
				$query	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE (`end_date` < %s OR `status` = %s) AND `user_id` = %d", $today, 'Expired', get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
	
			if ( ! empty( $limit ) ) {
				$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
	
			$results	= smartwoo_is_frontend() ? wp_cache_get( 'smartwoo_user_expired_services_' . get_current_user_id() ) : wp_cache_get( 'smartwoo_expired_services' );
			if ( false === $results ) {
				$results	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			}
	
			if ( ! empty( $results ) ) {
				$the_services	= self::convert_results_to_services( $results );
	
				foreach( $the_services as $service ) {
					if ( ! smartwoo_is_service_on_grace( $service ) ) {
						$services[]	= $service;
					}
	
				}
				wp_cache_set( $cache_key, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}
		return $services;
	}

	/**
	 * Search a service by a search term with exact match.
	 *
	 * @return array Array of SmartWoo_Service Objects or empty array.
	 */
	public static function search() {
		global $wpdb;

		// Check if search term is present in the URL parameters.
		$search_term 	= isset( $_GET['search_term'] ) ? sanitize_text_field( wp_unslash( $_GET['search_term'] ) ) : wp_die( 'Search term missing' );
        $limit  		= isset( $_GET['limit'] ) ? intval( $_GET['limit'] ) : 10;
		$page			= isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
		$offset 		= ( $page - 1 ) * $limit;

		// Try to retrieve the results from the cache.
		$services = wp_cache_get( 'smartwoo_services_' . $search_term );

		// If cache is not available, query the database.
		if ( false === $services ) {
			$services = array(); // Initialize an empty array for services.

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query = $wpdb->prepare( 
				"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . "
				WHERE `id` = %s 
				OR `user_id` = %s 
				OR `service_name` = %s 
				OR `service_url` = %s 
				OR `service_type` = %s 
				OR `service_id` = %s 
				OR `product_id` = %s 
				OR `status` = %s
				LIMIT %d
				OFFSET %d", 
				$search_term, 
				$search_term, 
				$search_term, 
				$search_term, 
				$search_term, 
				$search_term, 
				$search_term, 
				$search_term, 
				$limit,
				$offset
			);

			// Execute the query.
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			// If results are found, convert them to services and cache them.
			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( 'smartwoo_services_' . $search_term, $services, 'smartwoo_service', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}


	/**
	 * Count all the record in the database.
	 * 
	 * @return int
	 * @since 2.0.12
	 */
	public static function count_all() {
		global $wpdb;
		$query	= "SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$count	= (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $count;
	}

	/**
	 * Count all services owned by a user
	 * 
	 * @param int|WC_Customer|WP_User
	 */
	public static function count_user_services( $id ) {
		global $wpdb;
		if ( is_a( $id, 'WC_Customer' ) ) {
			$id = $id->get_id();
		} elseif ( is_a( $id, 'WP_User' ) ){
			$id = $id->ID;
		}

		$id = absint( $id );

		$query	= $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `user_id`= %d", $id );
		return (int) $wpdb->get_var( $query );
	}
	/**
	 * Get services that are within expiry threshold.
	 * 
	 * @param int $page Current request page.
	 * @param int $limit Current request limit
	 */
	public static function get_on_expiry_threshold( $page =  1, $limit = 10 ) {
		global $wpdb;
		$table_name = SMARTWOO_SERVICE_TABLE;

		$offset 		= ( absint( $page ) - 1 ) * absint( $limit );

		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE `end_date` >= CURDATE() AND `next_payment_date` < CURDATE() LIMIT %d OFFSET %d", $limit, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );
		$services = array();
		if ( ! empty( $results ) ) {
			$services = self::convert_results_to_services( $results );

		}

		return $services;
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
	public static function create_service( SmartWoo_Service &$service ) {
		global $wpdb;
		$it_exists = $wpdb->get_var(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( "SELECT `service_id` FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `service_id`= %s", $service->get_service_id() ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- False positive, query is prepared.
		);

		if ( $it_exists ) {
			return self::update_service( $service );
		}

		$data = array(
			'user_id'           => absint( $service->getUserId() ),
			'product_id'        => absint( $service->get_product_id() ),
			'service_name'      => sanitize_text_field( $service->get_name() ),
			'service_url'       => sanitize_url( $service->get_service_url(), array( 'http', 'https') ),
			'service_type'      => sanitize_text_field( $service->get_type() ),
			'service_id'        => sanitize_text_field( $service->get_service_id() ),
			'invoice_id'        => sanitize_text_field( $service->get_invoice_id() ),
			'start_date'        => sanitize_text_field( $service->get_start_date() ),
			'end_date'          => sanitize_text_field( $service->get_end_date() ),
			'next_payment_date' => sanitize_text_field( $service->get_next_payment_date() ),
			'billing_cycle'     => sanitize_text_field( $service->get_billing_cycle() ),
			'status'            => sanitize_text_field( $service->get_status() ),
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

		
		$saved = $wpdb->insert( SMARTWOO_SERVICE_TABLE, $data, $data_format );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		
		if ( $saved ) {
			SmartWoo::count_all_services();
			$service->set_id( $wpdb->insert_id );

			/**
			 * @action_hook smartwoo_new_service_created Fires when a new service is inserted into the database.
			 * 				@param SmartWoo_Service
			 */
			do_action( 'smartwoo_new_service_created', $service );
			return true;

		}
		
		return false;
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
	public static function update_service( SmartWoo_Service &$service ) {
		global $wpdb;

		$data 		= array(
			'user_id'           => absint( $service->getUserId() ),
			'product_id'        => absint( $service->get_product_id() ),
			'service_name'      => sanitize_text_field( $service->get_name() ),
			'service_url'       => esc_url_raw( $service->get_service_url() ),
			'service_type'      => sanitize_text_field( $service->get_type() ),
			'invoice_id'        => sanitize_text_field( $service->get_invoice_id() ),
			'start_date'        => sanitize_text_field( $service->get_start_date() ),
			'end_date'          => sanitize_text_field( $service->get_end_date() ),
			'next_payment_date' => sanitize_text_field( $service->get_next_payment_date() ),
			'billing_cycle'     => sanitize_text_field( $service->get_billing_cycle() ),
			'status'            => is_null( $service->get_status() ) ? null : sanitize_text_field( $service->get_status() ),
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
			'service_id' => sanitize_text_field( $service->get_service_id() ),
		);

		$where_format = array(
			'%s', // service_id
		);

		$updated = $wpdb->update( SMARTWOO_SERVICE_TABLE, $data, $where, $data_format, $where_format );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		delete_transient( 'smartwoo_print_expiry_notice_' . $service->get_id() );
		delete_transient( 'smartwoo_status_' . $service->get_service_id() );
		wp_cache_delete( 'smartwoo_status_' . $service->get_service_id() );
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

		$updated = $wpdb->update( SMARTWOO_SERVICE_TABLE, $data, $where, $data_format, $where_format );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		delete_transient( 'smartwoo_status_' . $service_id );
		wp_cache_delete( 'smartwoo_status_' . $service_id );

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

		// Check if the service exists
		$existing_service = self::get_service_by_id( $service_id );
		if ( ! $existing_service ) {
			return false;
		}

		$assets_obj = new SmartWoo_Service_Assets();
		$assets_obj->set_service_id( $service_id );
		$assets_obj->delete_all();
		/**
		 * Delete all invoices.
		 */
		$wpdb->delete( SMARTWOO_INVOICE_TABLE, array( 'service_id' => $service_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		
		$deleted = $wpdb->delete( SMARTWOO_SERVICE_TABLE, array( 'service_id' => $service_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		delete_transient( 'smartwoo_status_' . $service_id );
		wp_cache_delete( 'smartwoo_status_' . $service_id );
		SmartWoo::count_all_services();

		return $deleted !== false;
	}
}
