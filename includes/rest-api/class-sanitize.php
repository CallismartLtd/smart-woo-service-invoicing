<?php
/**
 * The Sanitization class file.
 *
 * @author  Callistus Nwachukwu
 * @package Callismart\Classes
 * @version 1.1.0
 * @since   1.0.0
 */

namespace SmartWoo_REST_API;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo REST API sanitization class.
 *
 * These methods should only return safe values, never WP_Error.
 * Validation (rejecting bad values) should be handled in VALIDATE class.
 */
class SANITIZE {

    /**
     * Sanitize an integer value.
     *
     * @param mixed $value The value to sanitize.
     * @return int The sanitized integer.
     */
    public static function integer( $value ) {
        return intval( $value );
    }

    /**
     * Sanitize a string value.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized string.
     */
    public static function string( $value ) {
        return sanitize_text_field( (string) wp_unslash( $value ) );
    }

    /**
     * Sanitize an email address.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized email (may be empty if invalid).
     */
    public static function email( $value ) {
        return sanitize_email( $value );
    }

    /**
     * Sanitize a URL.
     *
     * @param mixed $value The value to sanitize.
     * @return string The sanitized URL (empty string if invalid).
     */
    public static function url( $value ) {
        return esc_url_raw( $value );
    }

    /**
     * Sanitize a boolean value.
     *
     * @param mixed $value The value to sanitize.
     * @return bool The sanitized boolean.
     */
    public static function boolean( $value ) {
        if ( in_array( $value, array( '1', 1, 'true', 'yes', true ), true ) ) {
            return true;
        }
        return false;
    }

    /**
     * Sanitize an array of scalar values.
     *
     * @param mixed $value The value to sanitize.
     * @return array The sanitized array (empty if not array).
     */
    public static function array( $value ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', wp_unslash( $value ) );
        }
        return array();
    }

    /**
     * Sanitize HTML string
     * 
     * @param $value
     */
    public static function html( $value ) {
        return wp_kses_post( $value );
    }
}
