<?php
/**
 * SmartWoo System Diagnosis.
 *
 * Gathers full diagnostic data for support use — strictly environment data only.
 *
 * @package SmartWoo\Admin
 * @author  Callistus
 * @since   1.1.0
 */

namespace SmartWoo;

defined( 'ABSPATH' ) || exit;

class Diagnosis {

	/**
	 * Collect complete diagnostic data.
	 *
	 * @return array
	 */
	public static function get_system_report() {
		global $wpdb;

		$theme   = wp_get_theme();
		$plugins = get_plugins();
		$active  = get_option( 'active_plugins', array() );

		$report = array(
			'plugin' => array(
				'name'        => 'Smart Woo Service Invoicing',
				'version'     => defined( 'SMARTWOO_VER' ) ? SMARTWOO_VER : 'unknown',
				'basename'    => defined( 'SMARTWOO_PLUGIN_BASENAME' ) ? SMARTWOO_PLUGIN_BASENAME : 'unknown',
				'upload_dir'  => defined( 'SMARTWOO_UPLOAD_DIR' ) ? SMARTWOO_UPLOAD_DIR : '',
				'pro_active'  => class_exists( 'SmartWooPro' ),
				'wp_debug'    => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			),

			'wordpress' => array(
				'version'       => get_bloginfo( 'version' ),
				'site_url'      => esc_url( site_url() ),
				'admin_url'     => esc_url( admin_url() ),
				'multisite'     => is_multisite(),
				'language'      => get_locale(),
				'active_theme'  => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
				'active_plugins'=> self::get_active_plugins( $plugins, $active ),
			),

			'server' => array(
				'os'              => PHP_OS_FAMILY,
				'server_software' => sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ?? '' ),
				'php_version'     => phpversion(),
				'max_upload'      => ini_get( 'upload_max_filesize' ),
				'post_max'        => ini_get( 'post_max_size' ),
				'memory_limit'    => ini_get( 'memory_limit' ),
				'time_limit'      => ini_get( 'max_execution_time' ),
				'timezone'        => wp_timezone_string(),
			),

			'php' => self::php_environment(),

			'tables' => self::get_plugin_tables(),

			'mpdf' => self::mpdf_environment(),

			'database' => array(
				'version' => $wpdb->db_version(),
				'charset' => $wpdb->charset,
				'collate' => $wpdb->collate,
				'stats'   => self::database_stats(),
			),

			'checks' => self::health_checks(),

			'timestamp' => current_time( 'mysql' ),
		);

		return apply_filters( 'smartwoo_system_report_data', $report );
	}

	/**
	 * Active plugins list.
	 */
	private static function get_active_plugins( $plugins, $active ) {
		$list = array();

		foreach ( $active as $file ) {
			if ( isset( $plugins[ $file ] ) ) {
				$list[] = array(
					'name'    => $plugins[ $file ]['Name'],
					'version' => $plugins[ $file ]['Version'],
				);
			}
		}

		return $list;
	}

	/**
	 * PHP environment details.
	 */
	private static function php_environment() {
		return array(
			'version'         => phpversion(),
			'error_log'       => ini_get( 'error_log' ),
			'display_errors'  => ini_get( 'display_errors' ),
			'extensions'      => get_loaded_extensions(),
			'default_charset' => ini_get( 'default_charset' ),
			'sapi'            => php_sapi_name(),
		);
	}

	/**
	 * SmartWoo DB table constants and existence check.
	 */
	private static function get_plugin_tables() {
		global $wpdb;

		$tables = array();
		$defined_tables = array(
			'SMARTWOO_SERVICE_TABLE'      => defined( 'SMARTWOO_SERVICE_TABLE' ) ? SMARTWOO_SERVICE_TABLE : '',
			'SMARTWOO_SERVICE_META_TABLE' => defined( 'SMARTWOO_SERVICE_META_TABLE' ) ? SMARTWOO_SERVICE_META_TABLE : '',
			'SMARTWOO_INVOICE_TABLE'      => defined( 'SMARTWOO_INVOICE_TABLE' ) ? SMARTWOO_INVOICE_TABLE : '',
			'SMARTWOO_INVOICE_META_TABLE' => defined( 'SMARTWOO_INVOICE_META_TABLE' ) ? SMARTWOO_INVOICE_META_TABLE : '',
			'SMARTWOO_ASSETS_TABLE'       => defined( 'SMARTWOO_ASSETS_TABLE' ) ? SMARTWOO_ASSETS_TABLE : '',
		);

		foreach ( $defined_tables as $const => $table ) {
			if ( empty( $table ) ) {
				continue;
			}

			$exists = $wpdb->get_var( $wpdb->prepare(
				"SHOW TABLES LIKE %s", $table
			) );

			$tables[ $const ] = array(
				'name'   => $table,
				'exists' => ( $exists === $table ),
			);
		}

		return $tables;
	}

	/**
	 * Database row stats for main plugin tables.
	 */
	private static function database_stats() {
		global $wpdb;

		$stats = array();

		$tables = array(
			'service'  => defined( 'SMARTWOO_SERVICE_TABLE' ) ? SMARTWOO_SERVICE_TABLE : '',
			'invoice'  => defined( 'SMARTWOO_INVOICE_TABLE' ) ? SMARTWOO_INVOICE_TABLE : '',
			'assets'   => defined( 'SMARTWOO_ASSETS_TABLE' ) ? SMARTWOO_ASSETS_TABLE : '',
		);

		foreach ( $tables as $key => $table ) {
			if ( ! empty( $table ) && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
				$stats[ $key . '_rows' ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			}
		}

		return $stats;
	}

	/**
	 * mPDF diagnostic info.
	 */
	private static function mpdf_environment() {
		return array(
			'installed'      => class_exists( '\Mpdf\Mpdf' ),
			'required_exts'  => array( 'mbstring', 'gd', 'zlib' ),
			'available_exts' => array_intersect( array( 'mbstring', 'gd', 'zlib' ), get_loaded_extensions() ),
			'temp_dir'       => defined( '_MPDF_TEMP_PATH' ) ? _MPDF_TEMP_PATH : sys_get_temp_dir(),
			'writable'       => is_writable( defined( '_MPDF_TEMP_PATH' ) ? _MPDF_TEMP_PATH : sys_get_temp_dir() ),
		);
	}

	/**
	 * Environment health checks.
	 */
	private static function health_checks() {
		return array(
			'uploads_dir_writable' => defined( 'SMARTWOO_UPLOAD_DIR' ) && wp_is_writable( SMARTWOO_UPLOAD_DIR ),
			'mpdf_ready'           => class_exists( '\Mpdf\Mpdf' ) && in_array( 'mbstring', get_loaded_extensions(), true ),
			'curl_enabled'         => function_exists( 'curl_init' ),
			'json_enabled'         => function_exists( 'json_encode' ),
			'openssl_enabled'      => extension_loaded( 'openssl' ),
			'php_version_ok'       => version_compare( PHP_VERSION, '8.0', '>=' ),
		);
	}

	/**
	 * Get JSON version of the report.
	 *
	 * @return string
	 */
	public static function get_report_json() {
		return wp_json_encode( self::get_system_report(), JSON_PRETTY_PRINT );
	}
}
