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
		'billing_cycle varchar(20) DEFAULT NULL',
		'status varchar(20) DEFAULT NULL',
		'PRIMARY KEY  (id)',
	);

	smartwoo_create_database_table( $service_table_name, $service_structure );

	/**
	 * Define the structure for the 'sw_invoice' table.
	 * This table contains the main information pertaining to invoices.
	 */
	$invoice_table_name = SMARTWOO_INVOICE_TABLE;
	$invoice_structure  = array(
		'id mediumint(9) NOT NULL AUTO_INCREMENT',
		'service_id varchar(255) DEFAULT NULL',
		'user_id mediumint(9) DEFAULT NULL',
		'billing_address text DEFAULT NULL',
		'invoice_id varchar(255) NOT NULL',
		'invoice_type varchar(255) DEFAULT NULL',
		'product_id mediumint(9) NOT NULL',
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
	 * Define the structure for the Service Log table.
	 * This table is used to log renewed services informations.
	 */
	$auto_renew_table_name = SMARTWOO_SERVICE_LOG_TABLE;
	$auto_renew_structure  = array(
		'id mediumint(9) NOT NULL AUTO_INCREMENT',
		'service_id varchar(255) NOT NULL',
		'log_type varchar(255) NOT NULL',
		'details text DEFAULT NULL',
		'note text DEFAULT NULL',
		'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		'PRIMARY KEY  (id)',
	);

	smartwoo_create_database_table( $auto_renew_table_name, $auto_renew_structure );

	/**
	 * Defined the wp_sw_invoice_log table where 
	 * all transaction related data are logged
	 */
	$service_logs_table_name = SMARTWOO_INVOICE_LOG_TABLE;
	$service_logs_structure  = array(
		'id mediumint(9) NOT NULL AUTO_INCREMENT',
		'log_id varchar(255) NOT NULL',
		'log_type varchar(60) DEFAULT NULL',
		'amount decimal(10, 2) DEFAULT NULL',
		'status varchar(20) DEFAULT NULL',
		'details text DEFAULT NULL',
		'note varchar(255) DEFAULT NULL',
		'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
		'updated_at datetime DEFAULT NULL',
		'PRIMARY KEY  (id)',
		'INDEX log_id_index (log_id)',
		'INDEX log_type_index (log_type)',
		'INDEX status_index (status)',
	);

	smartwoo_create_database_table( $service_logs_table_name, $service_logs_structure );

	/**
	 * Assets Table
	 */
	$assets_table = SMARTWOO_ASSETS_TABLE;
	$assets_table_structure = array(
		'asset_id mediumint(9) NOT NULL AUTO_INCREMENT',
		'service_id varchar(255) NOT NULL',
		'asset_name varchar(255) DEFAULT NULL',
		'asset_data text DEFAULT NULL',
		'asset_key varchar(255) NOT NULL',
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
    $table_exists 	= $wpdb->get_var( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared --False positive 
	$is_update		= 'running' === get_transient( 'smartwoo_db_update' );
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
	$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}", ARRAY_A );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$column_names = array();
	foreach ( $columns as $column ) {
		$column_names[] = $column['Field'];
	}

	if ( ! in_array( $new_col, $column_names ) ) {
		$new_col 	= $new_col . ' ' . $constrnts;
		$query		= "ALTER TABLE {$table_name} ADD {$new_col} AFTER `asset_key`;";
		$result		= $wpdb->query( $query );  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		
		if ( ! $result  && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $wpdb->last_error );
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
	SmartWoo::count_all_services();
}
