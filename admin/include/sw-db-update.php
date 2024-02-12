<?php
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Function to create or update tables
function create_or_update_table($wpdb, $table_name, $current_structure, $db_version) {
    $charset_collate = $wpdb->get_charset_collate();

    $table_version_option = "smart_invoice_{$table_name}_db_version";

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Create the table if it doesn't exist
        $sql = "CREATE TABLE $table_name (" . implode(', ', $current_structure) . ") $charset_collate;";
        dbDelta($sql);

        // Save or update the database version for this table
        update_option($table_version_option, $db_version);
    } else {
        // Check and update table structure if needed
        $existing_columns = $wpdb->get_col("DESC $table_name", 0);
        $missing_columns = array_diff($current_structure, $existing_columns);

        if (!empty($missing_columns)) {
            // Add missing columns
            foreach ($missing_columns as $column_definition) {
                $alter_sql = "ALTER TABLE $table_name ADD COLUMN $column_definition";
                $wpdb->query($alter_sql);
            }

            // Update the table version if columns were added
            update_option($table_version_option, $db_version);
        }
    }
}