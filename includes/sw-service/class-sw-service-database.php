<?php
/**
 * Class file for Smart Woo Service Database interactions.
 * 
 * @author Callistus.
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Performs database CRUD operation for service subscriptions.
 * 
 * @package SmartWooService
 * @since   1.0.0
 */
class SmartWoo_Service_Database {

	/*
	|----------------------
	| GET OPERATIONS
	|----------------------
	*/

	/**
	 * Retrieves all services from the database.
	 *
	 * @return SmartWoo_Service[] An array of SmartWoo_Service objects.
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
	 * 
	 * @param int $page   The current pagination number.
	 * @param int $limit  The number of results needed.
	 * @return SmartWoo_Service[] An array of SmartWoo_Service objects.
	 */
	public static function get_all( $page = 1, $limit = 10 ) {
		global $wpdb;
		
		$cache_key	= sprintf( 'get_all_%d_%d', $page, $limit );
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			$services = array();
			$offset = ( $page - 1 ) * $limit;
			// phpcs:ignore WordPress.DB
			$query = $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " ORDER BY `id` DESC LIMIT %d OFFSET %d",
				$limit, 
				$offset
			);
			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );

			}

			wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );

		}

		return $services;
	}

	/**
	 * Retrieves a service by its ID from the database.
	 *
	 * @param string $service_id The ID of the service to retrieve.
	 *
	 * @return SmartWoo_Service|null The SmartWoo_Service object if found, null otherwise.
	 *
	 * @since 1.0.0
	 * @since 2.0.12 Implemented object caching.
	 */
	public static function get_service_by_id( $service_id ) {
		$cache_key	= sprintf( 'get_service_by_id_%s', $service_id );
		$service	= wp_cache_get( $cache_key, 'smartwoo_service_database' );
		if ( false !== $service ) {
			return $service;
		}

		global $wpdb;

		$query  = $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE service_id = %s", $service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		if ( $result ) {
			$service	= SmartWoo_Service::set_from_array( $result );
			wp_cache_set( $cache_key, $service, 'smartwoo_service_database', HOUR_IN_SECONDS );
			
			return $service;
		}

		return null;
	}

	/**
	 * Get services associated with the given user ID.
	 *
	 * @param int $user_id The ID of the user to for.
	 * @param int $page	The page being requested.
	 * @param int $limit The number of items to retrieve.
	 * @return SmartWoo_Service[] The SmartWoo_Service object if found, false otherwise.
	 *
	 * @since 1.0.0
	 * @since 2.4.0 Added pagination params.
	 */
	public static function get_services_by_user( $user_id, $page = 1, $limit = 10 ) {
		$user_id 	= absint( $user_id );
		$cache_key	= sprintf( 'get_services_by_user_%s_%s_%s', $user_id , $page, $limit );
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			global $wpdb;

			$services 	= array();
			$offset		= ( $page - 1 ) * $limit;
			$query   	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE user_id = %d LIMIT %d OFFSET %d", $user_id, $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$results 	= $wpdb->get_results( $query, ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
	
			if ( $results ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}
		// Return empty array.
		return $services;
	}

	/**
	 * Get user services that are awaiting processing
	 * 
	 * @param int $user_id The user ID.
	 * @return SmartWoo_Service[]
	 */
	public static function get_user_awaiting_services( $user_id ) {
		$orders = SmartWoo_Order::get_user_orders( array( 'customer' => $user_id, 'status' => 'processing' ) );

		$services = array();
		if ( ! empty( $orders ) ) {
			foreach( $orders as $order ) {
				$service = new SmartWoo_Service();
				$service->set_name( $order->get_service_name() );
				$service->set_status( 'Processing' );
				$service->set_start_date( $order->get_date_created()->format( 'Y-m-d' ) );
				$service->set_billing_cycle( $order->get_billing_cycle() );
				$services[] = $service;
			}
		}

		return $services;
	}

	/**
	 * Get services that are within the "Active" threshold or have the "Active" status override.
	 * 
	 * @param int $page  The current page being requested.
	 * @param int $limit The limit for the current page.
	 * @since 2.0.12
	 * @return array|null Array of services or null if parameters are invalid.
	 */
	public static function get_all_active( int $page = 1, int $limit = 25 ) {
		
		$cache_key	= smartwoo_is_frontend() ? sprintf( 'smartwoo_all_active_services_%d_%d_%d', get_current_user_id(), $page, $limit ) : sprintf( 'smartwoo_all_active_services_%d_%d', $page, $limit );
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );
		
		if ( false === $services ) {
			global $wpdb;
			$offset 	= ( $page - 1 ) * $limit;
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
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}
	
		return $services;
	}

	/**
	 * Get services that have custom labels (status).
	 * 
	 * @param array $args Associative array of args.
	 * @return SmartWoo_Service[] Array of SmartWoo_Service Objects or empty array.
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
		$cache_key		= sprintf( 'get_%s_%d_%d', $parsed_args['status'], $parsed_args['page'], $parsed_args['limit'] );
		if ( smartwoo_is_frontend() ) {
			$cache_key	= sprintf( 'get_%d_%s_%d_%d', get_current_user_id(), $parsed_args['status'], $parsed_args['page'], $parsed_args['limit'] );
		}
		
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

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
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}
	
	/**
	 * Get services that are within "Due for Renewal" threshold or have the "Due for Renewal" status override.
	 * 
	 * @param int $page  The current page being requested.
	 * @param int $limit The limit for the current page.
	 * @since 2.0.12
	 * @return SmartWoo_Service[] Array of services.
	 */
	public static function get_all_due( $page = 1, $limit = 10 ) {
		if ( empty( $page ) ) {
			$page = 1;
		}

		
		$cache_key	= sprintf( 'get_all_due_%d_%d', $page, $limit );
		if( smartwoo_is_frontend() ) {
			$cache_key	= sprintf( 'get_all_due_%d_%d_%d', get_current_user_id(), $page, $limit );
		}
		
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			global $wpdb;
			$services	= array();
			$offset = ( $page - 1 ) * $limit;

			// Base query for backend.
			// phpcs:disable
			$query = $wpdb->prepare(
				"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " 
				WHERE (
					`status` = %s
					OR ( (`status` IS NULL OR `status` = %s)
						AND `next_payment_date` <= CURDATE() AND `end_date` >= CURDATE()
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
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}

	/**
	 * Get services that are within the "Expired" date threshold or has "Grace Period" status overide.
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

		$cache_key	= sprintf( 'get_all_on_grace_%d_%d', $page, $limit );

		if( smartwoo_is_frontend() ) {
			$cache_key	= sprintf( 'get_all_on_grace_%d_%d_%d', get_current_user_id(), $page, $limit );
		}

		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			global $wpdb;
			
			$offset 	= ( $page - 1 ) * $limit;
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
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}

	/**
	 * Get services that are within the "Expired" date threshold or has "Expired" status overide.
	 * 
	 * @param int $page		The current page being requested.
	 * @param int $limit	The limit for the current page.
	 * @since 2.0.12
	 */
	public static function get_all_expired( $page =  1, $limit = 10 ) {
		if ( empty( $page ) ) {
			return null; // Return null for invalid input.
		}

		$cache_key	= sprintf( 'get_all_expired_%d_%d', $page, $limit );

		if ( smartwoo_is_frontend() ) {
			$cache_key	= sprintf( 'get_all_expired_%d_%d_%d', get_current_user_id(), $page, $limit );
		}

		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			global $wpdb;

			$offset 	= ( $page - 1 ) * $limit;
			$today		= current_time( 'Y-m-d' );
			$services	= array();
			$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `end_date` < %s OR `status` = %s", $today, 'Expired' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			
			if ( smartwoo_is_frontend() ) {
				$query	= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " WHERE (`end_date` < %s OR `status` = %s) AND `user_id` = %d", $today, 'Expired', get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
	
			if ( ! empty( $limit ) ) {
				$query .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
	
			$results	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
				
			if ( ! empty( $results ) ) {
				$the_services	= self::convert_results_to_services( $results );
	
				foreach( $the_services as $service ) {
					if ( ! smartwoo_is_service_on_grace( $service ) ) {
						$services[]	= $service;
					}
	
				}
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}
		return $services;
	}

	/**
	 * Get all active subscribers.
	 * 
	 * @param int $page The current pagination number.
	 * @param int $limit The number of results to return.
	 * @return object[] $data The results with pagination data.
	 */
	public static function get_active_subscribers( $page = 1, $limit = 10 ) {
		global $wpdb;

		$cache_key	= sprintf( 'get_active_subscribers_%s_%s', $page, $limit );
		$users		= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $users ) {
			$offset	= ( absint( $page ) - 1 ) * absint( $limit );
			$table_name = SMARTWOO_SERVICE_TABLE;
			$query		= $wpdb->prepare( "SELECT DISTINCT `user_id` FROM `{$table_name}` WHERE `end_date` > CURDATE()  LIMIT %d OFFSET %d", $limit, $offset );
			
			$results	= $wpdb->get_results( $query, ARRAY_A );
			$users		= [];

			foreach( (array) $results as $result ) {
				$user = get_user_by( 'id', $result['user_id'] );

				if ( ! $user ) {
					continue;
				}

				$_user	= (object) array(
					'id'			=> $user->ID,
					'name'			=> $user->display_name,
					'avatar_url'	=> get_avatar_url( $user->ID ),
					'member_since'	=> $user->user_registered,
					'last_seen'		=> smartwoo_get_last_login_date( $user->ID ),
					'email'			=> $user->user_email,
					'billing_email'	=> smartwoo_get_client_billing_email( $user->ID ),
					'total_service'	=> self::count_user_services( $user )
				);


				$users[] = $_user;
			}

			wp_cache_set( $cache_key, $users, 'smartwoo_service_database', HOUR_IN_SECONDS );
		}

		return $users;
	}

	/**
	 * Search a service by a search term.
	 *
	 * @return SmartWoo_Service[] Array of SmartWoo_Service Objects or empty array.
	 */
	public static function search( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search_term'	=> smartwoo_get_query_param( 'search_term', null ),
			'page'			=> smartwoo_get_query_param( 'paged', 1 ),
			'limit'			=> smartwoo_get_query_param( 'limit', 10 )
		);

		$parsed_args	= wp_parse_args( $args, $defaults );

		$search_term 	= $parsed_args['search_term'];
        $limit  		= $parsed_args['limit'];
		$page			= $parsed_args['page'];
		$offset 		= ( $page - 1 ) * $limit;

		// Try to retrieve the results from the cache.
		$cache_key	= sprintf( 'smartwoo_services_%s_%d_%d', $search_term, $page, $limit );
		$services	= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $services ) {
			$services	= array(); // Initialize an empty array for services.
			$like		= '%' . $wpdb->esc_like( $search_term ) . '%';
			$table_name = SMARTWOO_SERVICE_TABLE;

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query = $wpdb->prepare( 
				"SELECT * FROM {$table_name}
				WHERE `id` LIKE %s
				OR `user_id` LIKE %s 
				OR `service_name` LIKE %s 
				OR `service_url` LIKE %s 
				OR `service_type` LIKE %s 
				OR `service_id` LIKE %s 
				OR `product_id` LIKE %s 
				OR `status` LIKE %s
				LIMIT %d
				OFFSET %d", 
				$like,
				$like,
				$like,
				$like,
				$like,
				$like, 
				$like, 
				$like, 
				$limit,
				$offset
			);

			$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			if ( ! empty( $results ) ) {
				$services = self::convert_results_to_services( $results );
				wp_cache_set( $cache_key, $services, 'smartwoo_service_database', HOUR_IN_SECONDS );
			}
		}

		return $services;
	}

	/**
	 * Query the service subscription database table - Not functional.
	 * 
	 * @param array $args An associative array of arguments.
	 * @return mixed
	 * @since 2.0.15
	 */
	public static function query( $args = array() ) {
		global $wpdb;
		$table_name = SMARTWOO_SERVICE_TABLE;

		$default	= array(
			'id'				=> 0,
			'user_id'			=> 0,
			'service_name'		=> '',
			'service_url'		=> '',
			'service_type'		=> '',
			'service_id'		=> '',
			'product_id'		=> 0,
			'start_date'		=> '',
			'next_payment_date'	=> '',
			'end_date'			=> '',
			'billing_cycle'		=> '',
			'status'			=> NULL,
			'date_created'		=> ''
		);

		$db_clause = array(
			'limit'		=> 25,
			'page'		=> 1,
			'order'		=> 'DESC',
			'order_by'	=> '',
			'return'	=> 'object'
		);

		$default_args			= array_merge( $default, $db_clause );
		$allowed_return_types	= array( 'object', 'service_ids', 'ids' );
		$allowed_columns		= array_keys( $default );
		$parsed_args			= wp_parse_args( $args, $default_args );

		$base_query = "SELECT";

		if ( 'service_ids' === $parsed_args['return'] ) {
			$base_query .= " `service_id`";
		} elseif ( 'ids' === $parsed_args['return'] ) {
			$base_query .= " `id`";
		} else {
			$base_query.= " *";
		}

		$base_query .= " FROM {$table_name}";

		$values = array();
		$where	= array();

		// Build query for active status.
		if ( is_null( $parsed_args['status'] ) || '' === $parsed_args['status'] || 'active' === strtolower( $parsed_args['status'] ) ) {
			$where[] = "( 
				`status` = %s 
				OR (
						(`status` IS NULL OR `status` = %s) 
						AND `next_payment_date` > CURDATE() 
						AND `end_date` > CURDATE()
					)
			)";
			$values[] = 'Active';
			$values[] = '';

		} elseif ( in_array( $parsed_args['status'], array( 'due for renewal', 'renewal' ) ) || '' === $parsed_args['status'] || 'due for renewal' === strtolower( $parsed_args['status'] ) ) {
			$where[] = "(
				`status` = %s
				OR ( (`status` IS NULL OR `status` = %s)
					AND `next_payment_date` <= CURDATE() AND `end_date` > CURDATE()
				)
			)";
		}

		$values[] = 'Active';
		$values[] = '';


	}

	/**
	 * Get services that are on expiry threshold.
	 *
	 * A service is considered "on expiry threshold" when either:
	 *  - its next_payment_date has passed (next_payment_date < NOW()) AND the service
	 *    is still within its end_date (end_date >= NOW()), OR
	 *  - the service has expired (end_date < NOW()) but the end_date is within the
	 *    last $threshold days (end_date >= DATE_SUB( NOW(), INTERVAL %d DAY )).
	 *
	 * Signature preserved: ( $page, $limit, $threshold ) so existing callers won't break.
	 *
	 * @param int $page      Current request page. Default 1.
	 * @param int $limit     Items per page. Default 10.
	 * @param int $threshold Number of days after expiry to include (grace period). Default 7.
	 * @return SmartWoo_Service[]
	 */
	public static function get_on_expiry_threshold( $page = 1, $limit = 10, $threshold = 7 ) {
		global $wpdb;

		$table_name = SMARTWOO_SERVICE_TABLE;

		$page      = max( 1, absint( $page ) );
		$limit     = max( 1, absint( $limit ) );
		$threshold = max( 0, absint( $threshold ) );
		$offset    = ( $page - 1 ) * $limit;

		$sql = " SELECT * FROM {$table_name}
			WHERE (
				next_payment_date IS NOT NULL
				AND next_payment_date < NOW()
				AND end_date IS NOT NULL
				AND end_date >= NOW()
			)
			OR (
				end_date IS NOT NULL
				AND end_date < NOW()
				AND end_date >= DATE_SUB( NOW(), INTERVAL %d DAY )
			)
			ORDER BY end_date ASC
			LIMIT %d
			OFFSET %d
		";

		$query   = $wpdb->prepare( $sql, $threshold, $limit, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		if ( empty( $results ) ) {
			return array();
		}

		return self::convert_results_to_services( $results );
	}


	/**
	 * Get All Metadata.
	 * 
	 * @return array
	 */
	public static function get_all_metadata( SmartWoo_Service $service ) {
		global $wpdb;
		$query		= $wpdb->prepare( "SELECT * FROM " . SMARTWOO_SERVICE_META_TABLE ." WHERE `service_id` = %s", $service->get_service_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$results	= $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return ( ! empty( $results ) ) ?  $results : array();
	}

	/**
	|-------------------
	| COUNT OPERATIONS
	|-------------------
	*/

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
	 * Get total number of service subscriptions in the database.
	 * 
	 * The count operation is automated to prevent overhead.
	 * @see `SmartWoo_Automation::count_all_services()`
	 * 
	 * @return int
	 */
	public static function get_total_records() {
		return get_option( 'smartwoo_all_services_count', 0 );
	}

	/**
	 * Count services that are within the "Active" threshold or have the "Active" status override.
	 *
	 * Uses transient API to cache results for 1 hour.
	 *
	 * @since 2.5
	 * @return int The number of active services.
	 */
	public static function count_active() {
		global $wpdb;

		// Use a different cache key for frontend vs backend.
		$cache_key = smartwoo_is_frontend()
			? sprintf( 'smartwoo_count_active_services_%d', get_current_user_id() )
			: 'smartwoo_count_active_services';

		// Check cache first.
		$count = get_transient( $cache_key );

		if ( false === $count ) {
			$query = "
				SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . "
				WHERE (
					`status` = %s
					OR (
						(`status` IS NULL OR `status` = %s)
						AND `next_payment_date` > CURDATE()
						AND `end_date` > CURDATE()
					)
				)
			";

			// Append user filter if frontend.
			if ( smartwoo_is_frontend() ) {
				$query .= " AND `user_id` = %d";
				$count  = $wpdb->get_var( $wpdb->prepare( $query, 'Active', '', get_current_user_id() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$count  = $wpdb->get_var( $wpdb->prepare( $query, 'Active', '' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Store in transient for 1 hour.
			set_transient( $cache_key, (int) $count, 5 * MINUTE_IN_SECONDS );
		}

		return (int) $count;
	}

	/**
	 * Count services that are within "Due for Renewal" threshold
	 * or have the "Due for Renewal" status override.
	 *
	 * Uses transient API to cache results for 1 hour.
	 *
	 * @since 2.5
	 * @return int The number of due-for-renewal services.
	 */
	public static function count_due() {
		global $wpdb;

		// Cache key should differ for frontend vs backend.
		$cache_key = smartwoo_is_frontend()
			? sprintf( 'smartwoo_count_due_services_user_%d', get_current_user_id() )
			: 'smartwoo_count_due_services';

		$count = get_transient( $cache_key );

		if ( false === $count ) {
			// Base query (backend).
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . "
				WHERE (
					`status` = %s
					OR (
						(`status` IS NULL OR `status` = %s)
						AND `next_payment_date` <= CURDATE()
						AND `end_date` >= CURDATE()
					)
				)",
				'Due for Renewal',
				''
			); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Modify query for frontend users.
			if ( smartwoo_is_frontend() ) {
				$query = $wpdb->prepare(
					"SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . "
					WHERE (
						`status` = %s
						OR (
							(`status` IS NULL OR `status` = %s)
							AND `next_payment_date` <= CURDATE()
							AND `end_date` > CURDATE()
						)
					)
					AND `user_id` = %d",
					'Due for Renewal',
					'',
					get_current_user_id()
				); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			// Run query.
			$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			// Save to cache for 1 hour.
			set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );
		}

		return (int) $count;
	}

	/**
	 * Count services currently "On Grace" (expired + grace status).
	 * 
	 * Uses transient caching to avoid expensive recalculations.
	 *
	 * @return int $count
	 * @since 2.5
	 */
	public static function count_on_grace() {
		$cache_key = 'smartwoo_count_on_grace';

		// If frontend, count should be user-specific.
		if ( smartwoo_is_frontend() ) {
			$cache_key .= '_' . get_current_user_id();
		}

		$count = get_transient( $cache_key );
		if ( false !== $count ) {
			return $count;
		}

		global $wpdb;

		// Count query (similar WHERE conditions as get_all_on_grace).
		$query = $wpdb->prepare(
			"SELECT id FROM " . SMARTWOO_SERVICE_TABLE . " 
			 WHERE (
				`status` = %s
				OR (
					(`status` IS NULL OR `status` = %s)
					AND `end_date` <= CURDATE()
				)
			 )",
			'Grace Period',
			''
		);

		if ( smartwoo_is_frontend() ) {
			$query = $wpdb->prepare(
				"SELECT id FROM " . SMARTWOO_SERVICE_TABLE . " 
				 WHERE (`end_date` < CURDATE() OR `status` = %s) 
				 AND `user_id` = %d",
				'Grace Period',
				get_current_user_id()
			);
		}

		// Fetch IDs and filter in PHP (to respect smartwoo_is_service_on_grace).
		return $results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$services = self::convert_results_to_services( $results );

		$count = 0;
		foreach ( $services as $service ) {
			if ( smartwoo_is_service_on_grace( $service ) ) {
				$count++;
			}
		}

		set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );

		return $count;
	}

	/**
	 * Count services that are expired (end_date passed OR status = "Expired"),
	 * excluding those currently on grace.
	 *
	 * @return int
	 * @since 2.0.12
	 */
	public static function count_expired() {
		$cache_key = 'smartwoo_count_expired';

		// Make it user-specific for frontend.
		if ( smartwoo_is_frontend() ) {
			$cache_key .= '_' . get_current_user_id();
		}

		$count = get_transient( $cache_key );
		if ( false !== $count ) {
			return $count;
		}

		global $wpdb;

		$today = current_time( 'Y-m-d' );

		$query = $wpdb->prepare(
			"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " 
			 WHERE `end_date` < %s OR `status` = %s",
			$today,
			'Expired'
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( smartwoo_is_frontend() ) {
			$query = $wpdb->prepare(
				"SELECT * FROM " . SMARTWOO_SERVICE_TABLE . " 
				 WHERE (`end_date` < %s OR `status` = %s) AND `user_id` = %d",
				$today,
				'Expired',
				get_current_user_id()
			); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		$results  = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
		$services = array();

		if ( ! empty( $results ) ) {
			$the_services = self::convert_results_to_services( $results );

			foreach ( $the_services as $service ) {
				// Ensure we exclude ones "on grace".
				if ( ! smartwoo_is_service_on_grace( $service ) ) {
					$services[] = $service;
				}
			}
		}

		$count = count( $services );
		set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );

		return $count;
	}

	/**
	 * Count services that are within the expiry threshold.
	 *
	 * A service is considered "on expiry threshold" when either:
	 *  - next_payment_date < NOW() and end_date >= NOW(), OR
	 *  - end_date < NOW() but end_date is within the last $threshold days.
	 *
	 * @param int $threshold Number of days after expiry to include (grace period). Default 7.
	 * @return int
	 * @since 2.0.12
	 */
	public static function count_on_expiry_threshold( $threshold = 7 ) {
		$cache_key = 'smartwoo_count_on_expiry_threshold';

		$count = get_transient( $cache_key );
		if ( false !== $count ) {
			return $count;
		}

		global $wpdb;
		$table_name = SMARTWOO_SERVICE_TABLE;

		$sql = " SELECT COUNT(*) FROM {$table_name}
			WHERE (
				next_payment_date IS NOT NULL
				AND next_payment_date < NOW()
				AND end_date IS NOT NULL
				AND end_date >= NOW()
			)
			OR (
				end_date IS NOT NULL
				AND end_date < NOW()
				AND end_date >= DATE_SUB( NOW(), INTERVAL %d DAY )
			)
		";

		$query = $wpdb->prepare( $sql, absint( $threshold ) );
		$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

		// Cache for 5 minutes.
		set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );

		return $count;
	}


	/**
	 * Count services by custom labels (status).
	 *
	 * Uses specialized methods if available,
	 * otherwise falls back to a direct DB query.
	 * Cached for 1 hour with Transients API.
	 *
	 * @param string $status The service status (e.g., 'Pending', 'Expired').
	 * @since 2.0.12
	 * @return string Formatted count bucket (e.g., "10+", "50+", "100+").
	 */
	public static function count_by_status( string $status ) {
		$status = sanitize_text_field( wp_unslash( $status ) );

		switch ( strtolower( $status ) ) {
			case 'due for renewal':
				return self::count_due();

			case 'grace period':
				return self::count_on_grace();

			case 'expired':
				return self::count_expired();

			case 'expiry-threshold':
				return self::count_on_expiry_threshold();
			case 'active':
				return self::count_active();

			default:
				break;
		}

		global $wpdb;

		$cache_key = smartwoo_is_frontend()
			? sprintf( 'smartwoo_count_%s_services_user_%d', strtolower( $status ), get_current_user_id() )
			: sprintf( 'smartwoo_count_%s_services', strtolower( $status ) );

		// Try cache first.
		$count = get_transient( $cache_key );

		if ( false === $count ) {
			// Base query.
			$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `status` = %s",
				$status
			); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Restrict by user if frontend.
			if ( smartwoo_is_frontend() ) {
				$query .= $wpdb->prepare( " AND `user_id` = %d", get_current_user_id() ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			$count = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared

			// Cache result.
			set_transient( $cache_key, $count, 5 * MINUTE_IN_SECONDS );
		}

		return $count;
	}


	/**
	 * Get the total number of active subscribers.
	 * 
	 * @return int
	 */
	public static function get_total_active_subscribers() {
		global $wpdb;

		$cache_key	= 'get_total_active_subscribers';
		$total		= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $total ) {
			$table_name = SMARTWOO_SERVICE_TABLE;
			$query = "SELECT COUNT( DISTINCT `user_id`) FROM `{$table_name}` WHERE  `end_date` > CURDATE()";
			$total = (int) $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $total, 'smartwoo_service_database',5 * MINUTE_IN_SECONDS );
		}

		return $total;
	}

	/**
	 * Count all services associated with the given user.
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
		$cache_key	= sprintf( 'count_user_services_%s', $id );
		$total		= wp_cache_get( $cache_key, 'smartwoo_service_database' );

		if ( false === $total ) {
			$query	= $wpdb->prepare( "SELECT COUNT(*) FROM " . SMARTWOO_SERVICE_TABLE . " WHERE `user_id`= %d", $id );
			$total	=  (int) $wpdb->get_var( $query );
			wp_cache_set( $cache_key, $total, 'smartwoo_service_database', HOUR_IN_SECONDS );
		}

		return $total;
	}

	/**
	|---------------------------------
	| DB INSERTION / UPDATE OPERATION 
	|---------------------------------
	*/

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
			'user_id'           => absint( $service->get_user_id() ),
			'product_id'        => absint( $service->get_product_id() ),
			'service_name'      => sanitize_text_field( $service->get_name() ),
			'service_url'       => sanitize_url( $service->get_service_url(), array( 'http', 'https') ),
			'service_type'      => sanitize_text_field( $service->get_type() ),
			'service_id'        => sanitize_text_field( $service->get_service_id() ),
			'start_date'        => sanitize_text_field( $service->get_start_date() ),
			'end_date'          => sanitize_text_field( $service->get_end_date() ),
			'next_payment_date' => sanitize_text_field( $service->get_next_payment_date() ),
			'date_created'		=> current_time( 'mysql' ),
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
			'%s', // start_date
			'%s', // end_date
			'%s', // next_payment_date
			'%s', // Date created
			'%s', // billing_cycle
			'%s', // status
		);

		
		$saved = $wpdb->insert( SMARTWOO_SERVICE_TABLE, $data, $data_format );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		
		if ( $saved ) {
			self::save_all_metadata( $service );
			update_option( 'smartwoo_all_services_count', self::count_all() );
			$service->set_id( $wpdb->insert_id );
			wp_cache_flush_group( 'smartwoo_service_database' );
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
			'user_id'           => absint( $service->get_user_id() ),
			'product_id'        => absint( $service->get_product_id() ),
			'service_name'      => sanitize_text_field( $service->get_name() ),
			'service_url'       => esc_url_raw( $service->get_service_url() ),
			'service_type'      => sanitize_text_field( $service->get_type() ),
			'start_date'        => sanitize_text_field( $service->get_start_date() ),
			'end_date'          => sanitize_text_field( $service->get_end_date() ),
			'next_payment_date' => sanitize_text_field( $service->get_next_payment_date() ),
			'date_created'		=> sanitize_text_field( $service->get_date_created() ?: null ),
			'billing_cycle'     => sanitize_text_field( $service->get_billing_cycle() ),
			'status'            => is_null( $service->get_status() ) ? null : sanitize_text_field( $service->get_status() ),
		);

		$data_format = array(
			'%d', // user_id
			'%d', // product_id
			'%s', // service_name
			'%s', // service_url
			'%s', // service_type
			'%s', // start_date
			'%s', // end_date
			'%s', // next_payment_date
			'%s', // Date created
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
		self::save_all_metadata( $service );
		delete_transient( 'smartwoo_print_expiry_notice_' . $service->get_id() );
		delete_transient( 'smartwoo_status_' . $service->get_service_id() );
		wp_cache_delete( 'smartwoo_status_' . $service->get_service_id() );
		wp_cache_flush_group( 'smartwoo_service_database' );
		return $updated !== false;
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
		wp_cache_flush_group( 'smartwoo_service_database' );
		return $updated !== false;
	}


	/**
	 * Save or update all service meta data to the database.
	 * 
	 * @param SmartWoo_Service $service
	 */
	public static function save_all_metadata( SmartWoo_Service $service ) {
		global $wpdb;
		$table_name = SMARTWOO_SERVICE_META_TABLE;
		$meta_data	= $service->get_all_meta();
		$updated = 0;
		foreach( $meta_data as $name => $value ) {
			$data = array(
				'meta_name'		=> $name,
				'meta_value'	=> $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- False positive
				'service_id'	=> $service->get_service_id()
			);

			$data_format = [];
			foreach( $data as $v ) {
				$data_format[] = self::get_data_format( $v );
			}

			$data_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( "SELECT `meta_id` FROM {$table_name} WHERE `service_id` = %s AND `meta_name` = %s", $service->get_service_id(), $name )// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- False positive, query is prepared
			);

			if ( $data_exists ) {
				$where = array( 'service_id' => $service->get_service_id(), 'meta_name' => $name );
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
	|---------------------
	| DB UTILITY METHODS
	|---------------------
	*/

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
			$services[] = SmartWoo_Service::set_from_array( $result );
		}

		return $services;
	}

	/**
	 * Format a raw integer into bucketed display count.
	 *
	 * Examples:
	 *  - 1..10 → "10+"
	 *  - 11..20 → "20+"
	 *  - 21..50 → "50+"
	 *  - 51..100 → "100+"
	 *  - 101..1000 → "1k+"
	 *  - 1001+ → "10k+"
	 *
	 * @param int $num Raw count.
	 * @return string
	 * @since 2.0.12
	 */
	protected static function format_count_bucket( $num ) {
		if ( $num <= 10 ) {
			return '10+';
		} elseif ( $num <= 20 ) {
			return '20+';
		} elseif ( $num <= 50 ) {
			return '50+';
		} elseif ( $num <= 100 ) {
			return '100+';
		} elseif ( $num <= 1000 ) {
			return '1k+';
		} elseif ( $num <= 10000 ) {
			return '10k+';
		}

		return '10k+'; // fallback cap
	}

	/**
	|---------------------
	| DELETE OPERATIONS
	|---------------------
	*/

	/**
	 * Deletes a service from the database.
	 *
	 * @param SmartWoo_Service|string $service The service object or the public ID of the service to delete.
	 *
	 * @return bool True on success, false on failure.
	 *
	 * @since 1.0.0
	 */
	public static function delete_service( $service_id ) {
		global $wpdb;

		// Check if the service exists.
		$existing_service = ( $service_id instanceof SmartWoo_Service ) ? $service_id : self::get_service_by_id( $service_id );
		if ( ! $existing_service ) {
			return false;
		}

		$service_id = $existing_service->get_service_id();

		$assets_obj = new SmartWoo_Service_Assets();
		$assets_obj->set_service_id( $service_id );
		$assets_obj->delete_all();
		self::delete_all_meta( $existing_service );

		/**
		 * Delete all invoices.
		 */
		$wpdb->delete( SMARTWOO_INVOICE_TABLE, array( 'service_id' => $service_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		
		$deleted = $wpdb->delete( SMARTWOO_SERVICE_TABLE, array( 'service_id' => $service_id ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		delete_transient( 'smartwoo_status_' . $service_id );
		wp_cache_delete( 'smartwoo_status_' . $service_id );
		wp_cache_flush_group( 'smartwoo_service_database' );
		update_option( 'smartwoo_all_services_count', self::count_all() );
		return $deleted !== false;
	}

	/**
	 * Delete All Metadata
	 */
	public static function delete_all_meta( SmartWoo_Service $service ) {
		global $wpdb;
		$deleted	= $wpdb->delete( SMARTWOO_SERVICE_META_TABLE, array( 'service_id' => $service->get_service_id() ), array( '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $deleted !== false;
	}

	/**
	 * Delete a single metadata from the database.
	 * 
	 * @param string $service_id The service public ID.
	 * @param string $meta_name The meta name.
	 */
	public static function delete_meta( $service_id, $meta_name ) {
		global $wpdb;
		$deleted	= $wpdb->delete( SMARTWOO_SERVICE_META_TABLE, array( 'service_id' => $service_id, 'meta_name' => $meta_name ), array( '%s', '%s' ) );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $deleted !== false;
	}
}
