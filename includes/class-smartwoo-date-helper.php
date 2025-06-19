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
     * Internal DateTimeImmutable instance (always in UTC).
     *
     * @var DateTimeImmutable
     */
    protected $datetime;

    /**
     * Billing cycle to interval mapping.
     *
     * @var array<string, string>
     */
    const BILLING_CYCLE_INTERVALS = [
        'weekly'       => '+1 week',
        'monthly'      => '+1 month',
        'quarterly'    => '+3 months',
        'six monthly'  => '+6 months',
        'six monthtly' => '+6 months', // typo coverage
        'semiannually' => '+6 months',
        'yearly'       => '+1 year',
    ];

    /**
     * Constructor.
     *
     * @param string $date_string Date string (any supported PHP format).
     *
     * @throws Exception If the date is invalid.
     */
    public function __construct( $date_string = '' ) {
        $date_string = sanitize_text_field( $date_string );

        if ( empty( $date_string ) ) {
            $date_string = 'now';
        }

        try {
            $parsed = new DateTimeImmutable( $date_string );
        } catch ( Exception $e ) {
            throw new Exception( __( 'Invalid date string provided.', 'smart-woo-service-invoicing' ) );
        }

        // Always store in UTC internally.
        $this->datetime = $parsed->setTimezone( new DateTimeZone( 'UTC' ) );
    }

   /**
     * Static factory method to create a new SmartWoo_Date object from date string.
     *
     * @param string $date_string Date string.
     *
     * @return static
     */
    public static function create_from( $date_string ) {
        return new static( $date_string );
    }

    /**
     * Create a SmartWoo_Date_Helper instance from a Unix timestamp.
     *
     * @param int $timestamp Unix timestamp.
     *
     * @return static
     *
     * @throws Exception If the timestamp is invalid.
     */
    public static function create_from_timestamp( $timestamp ) {
        if ( ! is_int( $timestamp ) && ! ctype_digit( $timestamp ) ) {
            throw new Exception( __( 'Invalid timestamp provided.', 'smart-woo-service-invoicing' ) );
        }

        // Create DateTimeImmutable from timestamp in UTC
        $datetime = new DateTimeImmutable( '@' . (int) $timestamp );
        $datetime = $datetime->setTimezone( new DateTimeZone( 'UTC' ) );

        return new static( $datetime->format( DATE_ATOM ) );
    }



    /**
     * Get internal DateTimeImmutable.
     *
     * @return DateTimeImmutable
     */
    public function get_datetime() {
        return $this->datetime;
    }

    /**
     * Set the timezone of the internal DateTimeImmutable.
     *
     * If the provided timezone is invalid, fallback to the site timezone or UTC.
     *
     * @param string $timezone_string Timezone identifier (e.g., 'Europe/London').
     *
     * @return $this
     */
    public function set_timezone( $timezone_string = '' ) {
        $timezone_string = sanitize_text_field( $timezone_string );

        try {
            $timezone = new DateTimeZone( $timezone_string );
        } catch ( Exception $e ) {
            $timezone = wp_timezone();  // fallback to site timezone or UTC
        }

        $this->datetime = $this->datetime->setTimezone( $timezone );

        return $this;
    }

    /**
     * Format the internal date.
     *
     * @param string $format PHP date format string.
     *
     * @return string
     */
    public function format( $format = 'Y-m-d' ) {
        return $this->datetime->format( $format );
    }

    /**
     * Get the Unix timestamp.
     *
     * @return int
     */
    public function get_timestamp() {
        return $this->datetime->getTimestamp();
    }

    /**
     * Magic method to convert the object to string.
     *
     * Returns the date formatted as 'Y-m-d' by default.
     *
     * @return string
     */
    public function __toString() {
        return $this->format();
    }

    /**
     * Add an interval to the internal date.
     *
     * @param string $interval_string Any valid PHP relative date/time string (e.g., '+1 week', '+2 months').
     *
     * @return static New SmartWoo_Date_Helper instance with updated date.
     *
     * @throws Exception If the interval string is invalid.
     */
    public function add_interval( $interval_string ) {
        $new_datetime = $this->datetime->modify( $interval_string );

        if ( ! $new_datetime ) {
            throw new Exception( sprintf( __( 'Invalid interval string: %s', 'smart-woo-service-invoicing' ), $interval_string ) );
        }

        return new self( $new_datetime->format( DATE_ATOM ) );
    }

    /**
     * Subtract an interval from the internal date.
     *
     * The interval string will be converted to a negative relative time,
     * so inputs like '7 days' or '-7 days' both subtract 7 days.
     *
     * @param string $interval_string Any valid PHP relative date/time string (e.g., '7 days', '-2 months').
     *
     * @return static New SmartWoo_Date_Helper instance with updated date.
     *
     * @throws Exception If the interval string is invalid.
     */
    public function sub_interval( $interval_string ) {
        $interval_string = trim( $interval_string );
        $clean_interval = ltrim( $interval_string, '+' ); // Remove leading plus if present.

        if ( strpos( $clean_interval, '-' ) !== 0 ) {
            $clean_interval = '-' . $clean_interval;
        }

        return $this->add_interval( $clean_interval );
    }

    /**
     * Get the PHP date interval string for a billing cycle.
     *
     * @param string $billing_cycle Billing cycle name (e.g., 'Weekly', 'Monthly').
     *
     * @return string|null Interval string suitable for DateTime modify (e.g., '+1 week'), or null if unknown.
     */
    public static function get_billing_cycle_interval( $billing_cycle ) {
        $key = strtolower( trim( $billing_cycle ) );

        if ( isset( self::BILLING_CYCLE_INTERVALS[ $key ] ) ) {
            return self::BILLING_CYCLE_INTERVALS[ $key ];
        }

        return null;
    }

    /**
     * Calculate the next payment date based on previous cycle differences.
     *
     * @param string $prev_next_pd Previous cycle's next payment date (Y-m-d).
     * @param string $old_end_date Previous cycle's end date (Y-m-d).
     * @param string $new_end_date Current cycle's end date (Y-m-d).
     *
     * @return self.
     */
    public static function calculate_next_payment_date( $prev_next_pd, $old_end_date, $new_end_date ) {
        $old_next_payment = new SmartWoo_Date_Helper( $prev_next_pd );
        $old_end          = new SmartWoo_Date_Helper( $old_end_date );
        $new_end          = new SmartWoo_Date_Helper( $new_end_date );

        $interval_seconds = $old_end->get_timestamp() - $old_next_payment->get_timestamp();

        // No exception if interval is zero or negative, just use the same logic.
        $new_next_payment_timestamp = $new_end->get_timestamp() - $interval_seconds;

        $new_next_payment = SmartWoo_Date_Helper::create_from_timestamp( $new_next_payment_timestamp );

        return $new_next_payment;
    }

}
