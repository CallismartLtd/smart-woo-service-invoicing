<?php
/**
 * SmartWoo Date Helper Class.
 *
 * Provides internal date calculations for subscriptions, invoices, and payment cycles
 * using timezone-safe, immutable DateTime operations.
 *
 * @package SmartWooServiceInvoicing
 * @since 2.4.0
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Date_Helper {

    /**
     * Get the WordPress site timezone as DateTimeZone.
     *
     * @since 2.4.0
     * @return DateTimeZone
     */
    public static function get_wp_timezone() {
        return wp_timezone();
    }
        
    /**
     * Get a DateTimeImmutable object from a date string.
     *
     * @param string $date_string Date string.
     * @return DateTimeImmutable|null
     */
    public static function get_date( $date_string ) {
        try {
            return new DateTimeImmutable( $date_string, self::get_wp_timezone() );
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * Add an interval to a date.
     *
     * @param DateTimeImmutable $date   Date object.
     * @param string            $interval String like "+1 month".
     * @return DateTimeImmutable|null
     */
    public static function add_interval( DateTimeImmutable $date, $interval ) {
        try {
            return $date->modify( $interval );
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * Add billing cycle interval to a date.
     *
     * @since 2.3.0
     * @param string $date_string Date string (e.g., '2025-07-31').
     * @param string $cycle Billing cycle key (e.g., 'Monthly', 'Yearly').
     * @return DateTimeImmutable
     * @throws InvalidArgumentException If billing cycle is unsupported.
     */
    public static function add_billing_cycle( $date_string, $cycle ) {
        $timezone = self::get_wp_timezone();
        $date     = new DateTimeImmutable( $date_string, $timezone );

        switch ( $cycle ) {
            case 'Weekly':
                $interval = new DateInterval( 'P1W' );
                break;
            case 'Monthly':
                $interval = new DateInterval( 'P1M' );
                break;
            case 'Quarterly':
                $interval = new DateInterval( 'P3M' );
                break;
            case 'Semiannually':
                $interval = new DateInterval( 'P6M' );
                break;
            case 'Yearly':
                $interval = new DateInterval( 'P1Y' );
                break;
            default:
                throw new InvalidArgumentException( 'Unsupported billing cycle: ' . $cycle );
        }

        return $date->add( $interval );
    }

    /**
     * Apply the global next payment date offset.
     *
     * @since 2.3.0
     * @param DateTimeImmutable|string $date Date object or string.
     * @return DateTimeImmutable
     */
    public static function apply_global_next_payment_offset( $date ) {
        $timezone = self::get_wp_timezone();

        if ( ! $date instanceof DateTimeImmutable ) {
            $date = new DateTimeImmutable( $date, $timezone );
        }

        $global_setting = smartwoo_get_global_nextpay( 'view' );
        $adjusted_date  = $date->modify( $global_setting );

        return $adjusted_date;
    }

    /**
     * Format a DateTimeImmutable object.
     *
     * @param DateTimeImmutable $date Date object.
     * @param string            $format Date format (default: 'Y-m-d').
     * @return string
     */
    public static function format_date( DateTimeImmutable $date, $format = 'Y-m-d' ) {
        return $date->format( $format );
    }

       /**
     * Calculate the next payment date using the offset from previous cycle.
     *
     * @param string $new_end_date          New end date (Y-m-d).
     * @param string $old_end_date          Old end date (Y-m-d).
     * @param string $old_next_payment_date Old next payment date (Y-m-d).
     * @return string Formatted next payment date (Y-m-d).
     */
    public static function calculate_next_payment_date( $new_end_date, $old_end_date, $old_next_payment_date ) {
        $new_end = self::get_date( $new_end_date );

        if ( $old_end_date && $old_next_payment_date ) {
            $old_end           = self::get_date( $old_end_date );
            $old_next_payment = self::get_date( $old_next_payment_date );

            if ( $old_end && $old_next_payment ) {
                $diff = $old_end->diff( $old_next_payment );
                return self::format_date( $new_end->add( $diff ) );
            }
        }

        // Fallback to global setting.
        $global_offset = smartwoo_get_global_nextpay();
        $calculated    = $new_end->modify( $global_offset );
        return self::format_date( $calculated );
    }
}
