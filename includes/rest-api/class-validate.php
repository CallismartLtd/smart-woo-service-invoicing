<?php
/**
 * The Validation class file.
 * 
 * @author  Callistus Nwachukwu
 * @package Callismart\Classes
 * @version 1.0.0
 * @since   1.0.0
 */
namespace SmartWoo_REST_API;

defined( 'ABSPATH' ) || exit;

/**
 * Smart Woo REST API validation class handles validation for REST API routes.
 */
class VALIDATE {

    /**
     * Validate that a value is an integer.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function integer( $value ) {
        if ( is_numeric( $value ) && intval( $value ) == $value ) {
            return true;
        }

        return new \WP_Error(
            'invalid_integer',
            __( 'The provided value must be an integer.', 'callismart' ),
            array( 'status' => 400 )
        );
    }

    /**
     * Validate that a string is non-empty.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function string( $value ) {
        if ( is_string( $value ) && $value !== '' ) {
            return true;
        }

        return new \WP_Error(
            'invalid_string',
            __( 'The provided value must be a non-empty string.', 'callismart' ),
            array( 'status' => 400 )
        );
    }

    /**
     * Validate that an email is in correct format.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function email( $value ) {
        if ( is_email( $value ) ) {
            return true;
        }

        return new \WP_Error(
            'invalid_email',
            __( 'The provided value must be a valid email address.', 'callismart' ),
            array( 'status' => 400 )
        );
    }

    /**
     * Validate a URL.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function url( $value ) {
        if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
            return true;
        }

        return new \WP_Error(
            'invalid_url',
            __( 'The provided value must be a valid URL.', 'callismart' ),
            array( 'status' => 400 )
        );
    }

    /**
     * Validate a boolean value.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function boolean( $value ) {
        if ( is_bool( $value ) || in_array( $value, array( '0','1',0,1,'true','false','yes','no' ), true ) ) {
            return true;
        }

        return new \WP_Error(
            'invalid_boolean',
            __( 'The provided value must be a boolean.', 'callismart' ),
            array( 'status' => 400 )
        );
    }

    /**
     * Validate an array.
     *
     * @param mixed $value The value to validate.
     * @return true|\WP_Error True if valid, WP_Error otherwise.
     */
    public static function array( $value ) {
        if ( is_array( $value ) ) {
            return true;
        }

        return new \WP_Error(
            'invalid_array',
            __( 'The provided value must be an array.', 'callismart' ),
            array( 'status' => 400 )
        );
    }
    
    /**
     * Check whether a value is empty for REST API validation.
     *
     * @param mixed $value The value to check.
     * @return true|\WP_Error True if not empty, WP_Error otherwise.
     */
    public static function not_empty( $value ) {
        // Strings: trim and check if still empty
        if ( is_string( $value ) && trim( $value ) === '' ) {
            return new \WP_Error(
                'rest_invalid_param',
                __( 'The value cannot be an empty string.', 'callismart' ),
                array( 'status' => 400 )
            );
        }

        // Arrays: must not be empty
        if ( is_array( $value ) && empty( $value ) ) {
            return new \WP_Error(
                'rest_invalid_param',
                __( 'The array cannot be empty.', 'callismart' ),
                array( 'status' => 400 )
            );
        }

        // Null or unset values
        if ( $value === null ) {
            return new \WP_Error(
                'rest_invalid_param',
                __( 'The value cannot be null.', 'callismart' ),
                array( 'status' => 400 )
            );
        }

        // Scalars like 0, false, etc. are allowed
        return true;
    }

}
