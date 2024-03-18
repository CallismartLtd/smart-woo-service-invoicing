<?php
/**
 * File name   : sw-db.php
 * Author      : Callistus
 * Description : Database table definition file
 *
 * @since      : 1.0.0
 * @package    : SmartWooServiceInvoicing
 */

 defined('ABSPATH') || exit; // Exist if accessed directly
 	
/**
* All the database structures are defined here
*/
function sw_plugin_db_schema() {

	// Define the current database version
	$sw_db_version = '1.0.1'; // Update the version when making schema changes.

	// Check the stored version
	$stored_version = get_option( 'sw_db_version' );

	if ( $sw_db_version !== $stored_version ) {

		/**
		 * Define the structure for the 'sw_service' table.
		 * This table contians the main service subscription informations.
		 */
		$service_table_name = SW_SERVICE_TABLE;
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

		sw_create_database_table( $service_table_name, $service_structure );

		/**
		 * Define the structure for the 'sw_invoice' table.
		 * This table contains the main information pertaining to invoices.
		 */
		$invoice_table_name = SW_INVOICE_TABLE;
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

		sw_create_database_table( $invoice_table_name, $invoice_structure );

		/**
		 * Define the structure for the Service Log table.
		 * This table is used to log renewed services informations.
		 */
		$auto_renew_table_name = SW_SERVICE_LOG_TABLE;
		$auto_renew_structure  = array(
			'id mediumint(9) NOT NULL AUTO_INCREMENT',
			'renewed_user_id mediumint(9) NOT NULL',
			'renewed_service_name varchar(255) NOT NULL',
			'renewed_service_url text NOT NULL',
			'renewed_service_id varchar(255) NOT NULL',
			'renewed_order_id mediumint(9) NOT NULL',
			'renewed_start_date date NOT NULL',
			'renewed_end_date date NOT NULL',
			'renewed_next_payment_date date NOT NULL',
			'renewed_billing_cycle varchar(20) NOT NULL',
			'PRIMARY KEY  (id)',
		);

		sw_create_database_table( $auto_renew_table_name, $auto_renew_structure );
		// Define the structure for the Invoicing Logging table
		$service_logs_table_name = SW_INVOICE_LOG_TABLE;
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
			'INDEX log_id_index (log_id)', // Index for log_id column
			'INDEX log_type_index (log_type)', // Index for log_type column
			'INDEX status_index (status)', // Index for status column
		);


		sw_create_database_table( $service_logs_table_name, $service_logs_structure );

		// Update the stored version
		update_option( 'sw_db_version', $sw_db_version );
	}
}

/**
 * Create the necessary database table
 *
 * @param string $table_name        The name of the table
 * @param array  $table_structure   The column names
 */
function sw_create_database_table( string $table_name, array $table_structure ) {
	global $wpdb;
	include_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Check if the table already exists
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
		$charset_collate = sw_get_charset_collate();

		$sql = "CREATE TABLE $table_name (";
		foreach ( $table_structure as $column ) {
			$sql .= "$column, ";
		}
		$sql  = rtrim( $sql, ', ' ); // Remove the trailing comma and space
		$sql .= ") $charset_collate;";

		dbDelta( $sql );
	}
}

/**
 * Retrieve the database charset and collate settings.
 *
 * This function generates a string that includes the default character set and collate
 * settings for the WordPress database, based on the global $wpdb object.
 *
 * @global wpdb $wpdb The WordPress database object.
 * @return string The generated charset and collate settings string.
 */
function sw_get_charset_collate() {
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
