<?php
/**
 * File name   : sw-db.php
 * Author      : Callistus
 * Description : Database table definition file
 *
 * @since      : 1.0.0
 * @package SmartWoo\Database
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * All the database structures are defined here
 */
function smartwoo_db_schema() {
	/**
	 * Define the structure for the 'sw_service' table.
	 * This table contians the main service subscription informations.
	 */
	$service_table_name = SMARTWOO_SERVICE_TABLE;
	$service_structure  = array(
		'id mediumint(9) NOT NULL AUTO_INCREMENT',
		'user_id mediumint(9) NOT NULL',
		'service_name varchar(255) NOT NULL',
		'service_url text DEFAULT NULL',
		'service_type varchar(255) DEFAULT NULL',
		'service_id varchar(255) NOT NULL',
		'product_id mediumint(9) NOT NULL',
		'invoice_id varchar(255) DEFAULT NULL',
		'start_date date DEFAULT NULL',
		'end_date date DEFAULT NULL',
		'next_payment_date date DEFAULT NULL',
		'date_created date DEFAULT NULL',
		'billing_cycle varchar(20) DEFAULT NULL',
		'status varchar(20) DEFAULT NULL',
		'PRIMARY KEY  (id)',
	);

	smartwoo_create_database_table( $service_table_name, $service_structure );

	/**
	 * Service meta table.
	 * 
	 * @since 2.5
	 */
	$service_meta_table = SMARTWOO_SERVICE_META_TABLE;
	$service_meta_table_structure = array(
		'meta_id MEDIUMINT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
		'service_id VARCHAR(255) DEFAULT NULL',
		'meta_name VARCHAR(255) DEFAULT NULL',
		'meta_value TEXT DEFAULT NULL',
		'INDEX service_meta_name_index (meta_name)',
		'INDEX service_meta_service_id_index (service_id)',
	);

	smartwoo_create_database_table( $service_meta_table, $service_meta_table_structure );

	/**
	 * Define the structure for the 'sw_invoice' table.
	 * This table contains the main invoice data.
	 */
	$invoice_table_name = SMARTWOO_INVOICE_TABLE;
	$invoice_structure  = array(
		'id mediumint(9) NOT NULL AUTO_INCREMENT',
		'service_id varchar(255) DEFAULT NULL',
		'user_id mediumint(9) DEFAULT NULL',
		'billing_address text DEFAULT NULL',
		'invoice_id varchar(255) NOT NULL',
		'invoice_type varchar(255) DEFAULT NULL',
		'product_id TEXT DEFAULT NULL',
		'order_id mediumint(9) DEFAULT NULL',
		'amount decimal(10, 2) NOT NULL',
		'fee decimal(10, 2) DEFAULT NULL',
		'payment_status varchar(20) NOT NULL',
		'payment_gateway varchar(255) DEFAULT NULL',
		'transaction_id varchar(255) DEFAULT NULL',
		'date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		'date_paid datetime DEFAULT NULL',
		'date_due datetime DEFAULT NULL',
		'total decimal(10, 2) NOT NULL',
		'PRIMARY KEY  (id)',
	);

	smartwoo_create_database_table( $invoice_table_name, $invoice_structure );

	/**
	 * Invoice meta table.
	 * 
	 * @since 2.2.3
	 */
	$invoice_meta_table = SMARTWOO_INVOICE_META_TABLE;
	$invoice_meta_table_structure = array(
		'meta_id MEDIUMINT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
		'invoice_id VARCHAR(255) DEFAULT NULL',
		'meta_name VARCHAR(255) DEFAULT NULL',
		'meta_value TEXT DEFAULT NULL',
	);

	smartwoo_create_database_table( $invoice_meta_table, $invoice_meta_table_structure );


	/**
	 * Assets Table
	 */
	$assets_table = SMARTWOO_ASSETS_TABLE;
	$assets_table_structure = array(
		'asset_id mediumint(9) NOT NULL AUTO_INCREMENT',
		'service_id varchar(255) NOT NULL',
		'asset_name varchar(255) DEFAULT NULL',
		'asset_data TEXT DEFAULT NULL',
		'asset_key TEXT NOT NULL',
		'is_external varchar(20) DEFAULT NULL', // added 2.0.1
		'access_limit mediumint(9) DEFAULT NULL',
		'expiry DATETIME DEFAULT NULL',
		'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
		'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		'INDEX service_id_index (service_id)',
		'INDEX asset_name_index (asset_name)',
		'PRIMARY KEY  (asset_id)',

	);

	smartwoo_create_database_table( $assets_table, $assets_table_structure );
}

/**
 * Create the necessary database table.
 *
 * @param string $table_name        The name of the table.
 * @param array  $table_structure   The column names and types.
 */
function smartwoo_create_database_table( string $table_name, array $table_structure ) {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$query			= $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name );
    $table_exists 	= $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive 

	if ( $table_exists !== $table_name ) {
        $charset_collate = smartwoo_get_charset_collate();

		$sql = "CREATE TABLE $table_name (";
		foreach ( $table_structure as $column ) {
			$sql .= "$column, ";
		}

        $sql  = rtrim( $sql, ', ' ); // Remove the trailing comma and space.
        $sql .= ") $charset_collate;";

        // Execute the SQL query.
        $result  = dbDelta( $sql );

    }

	$stored_version 		= get_option( 'smartwoo_db_version' );
	if ( SMARTWOO_DB_VER !== $stored_version ) {
		// Update the stored version.
		update_option( 'smartwoo_db_version', SMARTWOO_DB_VER );
	}
}

/**
 * Get the charset settings
 *
 * @global wpdb $wpdb The WordPress database object.
 * @return string The generated charset and collate settings string.
 */
function smartwoo_get_charset_collate() {
	global $wpdb;
	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}
	return $charset_collate;
}

/**
 * Inclusion of is_external column in the assets table
 * 
 * @since 2.0.2
 */
function smartwoo_db_update_201_is_external() {
	global $wpdb;
	$table_name = SMARTWOO_ASSETS_TABLE;
	$new_col 	= 'is_external';
	$constrnts	= 'varchar(20) DEFAULT NULL';
	$columns	= $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}", ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$column_names = array();

	foreach ( $columns as $column ) {
		$column_names[] = $column['Field'];
	}

	if ( ! in_array( $new_col, $column_names ) ) {
		$new_col 	= $new_col . ' ' . $constrnts;
		$query		= "ALTER TABLE {$table_name} ADD {$new_col} AFTER `asset_key`;";
		$result		= $wpdb->query( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		
		if ( ! $result  && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- False positive, WP_DEBUG status checked.
		}
	}
}

/**
 * Migration of Wallet refund option name to correct English phrase.
 */
function smartwoo_migrate_options_201() {
	$option = get_option( 'smartwoo_refund_with_wallet', false );

	if ( $option ) {
		update_option( 'smartwoo_refund_to_wallet', $option );
		delete_option( 'smartwoo_refund_with_wallet' );
	}
}

/**
 * Count all services and save to option
 */
function smartwoo_2012_update_service_count() {
	SmartWoo_Service_Database::count_all();
}

/**
 * Mail option name update
 * 
 * @since 2.2.0
 */
function smartwoo_220_mail_option_update() {
	wp_clear_scheduled_hook( 'smartwoo_5_minutes_task' );
	delete_option( 'smartwoo_reactivation_mail' );
	SmartWoo_Install::create_upload_dir();
	$options = array(
		''
	);
}

/**
 * Modify product_id column to TEXT if necessary.
 * 
 * @since 2.3.0
 */
function smartwoo_230_alter_product_id_column() {
	global $wpdb;
	$table_name  = SMARTWOO_INVOICE_TABLE;
	$column_name = 'product_id';
	$column_type = 'TEXT DEFAULT NULL';

	// Check if the column exists and its current type.
	$current_column_type = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive, query is prepared
		$wpdb->prepare( 
			"SELECT DATA_TYPE 
			 FROM INFORMATION_SCHEMA.COLUMNS 
			 WHERE TABLE_NAME = %s 
			 AND COLUMN_NAME = %s",
			$table_name,
			$column_name
		) 
	);

	// Only alter the column if it's not already TEXT.
	if ( $current_column_type && 'text' !== strtolower( $current_column_type ) ) {
		$wpdb->query( "ALTER TABLE {$table_name} MODIFY COLUMN {$column_name} {$column_type};" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! empty( $wpdb->last_error ) ) {
			error_log( "Database error modifying {$column_name}: " . $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}

/**
 * Inclusion of date_created column in the service subscription table.
 * 
 * @since 2.4.3
 */
function smartwoo_db_update_243_service_date_created() {
	global $wpdb;
	$table_name		= SMARTWOO_SERVICE_TABLE;
	$new_col		= 'date_created';
	$constrnts		= 'date DEFAULT NULL';
	$columns		= $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}", ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$column_names	= wp_list_pluck( $columns, 'Field' );

	if ( ! in_array( $new_col, $column_names ) ) {
		$new_col	= $new_col . ' ' . $constrnts;
		$query		= "ALTER TABLE {$table_name} ADD {$new_col} AFTER `next_payment_date`;";
		$result		= $wpdb->query( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		
		if ( ! $result  && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- False positive, WP_DEBUG status checked.
		}
	}
}

/**
 * Modify asset_key to text type and asset data to longtext type in assets table
 * 
 * @since 2.5.3
 */
function smartwoo_253_alter_assets_table_columns() {
	global $wpdb;
	$table_name = SMARTWOO_ASSETS_TABLE;
	$columns_to_modify = array(
		'asset_key'  => 'TEXT NOT NULL',
		'asset_data' => 'LONGTEXT DEFAULT NULL',
	);

	foreach ( $columns_to_modify as $column_name => $new_type ) {
		// Check current column type
		$current_column_type = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive, query is prepared
			$wpdb->prepare( 
				"SELECT DATA_TYPE 
				 FROM INFORMATION_SCHEMA.COLUMNS 
				 WHERE TABLE_NAME = %s 
				 AND COLUMN_NAME = %s",
				$table_name,
				$column_name
			) 
		);

		// Only alter the column if it's not already of the desired type
		if ( $current_column_type && strtolower( $current_column_type ) !== strtolower( explode( ' ', $new_type )[0] ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} MODIFY COLUMN {$column_name} {$new_type};" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! empty( $wpdb->last_error ) ) {
				error_log( "Database error modifying {$column_name}: " . $wpdb->last_error ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}
	}
}

/**
 * Clear deprecated cron hooks
 */
function smartwoo_243_clear_deprecated_cron_hooks() {
	wp_clear_scheduled_hook( 'smartwoo_auto_service_renewal' );
	wp_clear_scheduled_hook( 'smartwoo_5_minutes_task' );
	wp_clear_scheduled_hook( 'smartwoo_once_in48hrs_task' );
	wp_clear_scheduled_hook( 'smartwoo_service_scan' );
}