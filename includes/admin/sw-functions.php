<?php
/**
 * File name    :   sw-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Get the local Date and time format
 * 
 * @since 1.0.3
 * @return object|null stdClass or null
 */
function smartwoo_locale_date_format() {
	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );

	$format = new stdClass();
	$format->date_format = $date_format;
	$format->time_format = $time_format;
	return $format;
}

/**
 * Retrives a combination of local date and time format
 */
function smartwoo_datetime_format() {
	return smartwoo_locale_date_format()->date_format . ' ' . smartwoo_locale_date_format()->time_format;
}

/**
 * Function to format date to a human-readable format or show 'Not Available'.
 *
 * @param string $dateString Date String.
 * @param bool   $includeTime Whether to include the time aspect. Default is true.
 * @return string Formatted date or 'Not Available'.
 */
function smartwoo_check_and_format( $dateString, $includeTime = false ) {
	$locale = smartwoo_locale_date_format();
	$format = $includeTime ? $locale->date_format . ' ' . $locale->time_format : $locale->date_format;
	return ! empty( $dateString ) ? date_i18n( $format, strtotime( $dateString ) ) : 'N/A';
}

/**
 * Extracts the date portion from a date and time string.
 *
 * @param string $dateTimeString The date and time string.
 * @return null|string The extracted date in 'Y-m-d' format.
 */
function smartwoo_extract_only_date( $datetimestring ) {

	if ( empty( $datetimestring ) ) {
		return $datetimestring;
	}

	$date_object = new DateTime( $datetimestring );
	return $date_object->format( 'Y-m-d' );
}

/**
 * Convert timestamp to a readable date using smartwoo_check_and_format function.
 *
 * @param int  $timestamp   Unix timestamp.
 * @param bool $includeTime Whether to include the time aspect. Default is true.
 * @return string Formatted date or 'Not Available'.
 */
function smartwoo_timestamp_to_date( ?int $timestamp, bool $includeTime = true ) {

	if ( empty( $timestamp ) ) {
		return $timestamp;
	}

    return  date_i18n( smartwoo_datetime_format(), $timestamp );
}

/**
 * Calculate the difference between two dates and return it as a human-readable string.
 *
 * @param string $start_date The start date in 'Y-m-d H:i:s' format.
 * @param string $end_date The end date in 'Y-m-d H:i:s' format.
 * 
 * @return string The human-readable difference between the two dates.
 * 
 * @since 1.0.4
 */
function smartwoo_time_diff_string( $start_date, $end_date ) {
    // Create DateTime objects from the provided timestamps.
    $start  = new DateTime( $start_date );
    $end    = new DateTime( $end_date );

    // Calculate the difference between the two DateTime objects.
    $interval = $start->diff( $end );

    // Build the human-readable string.
    $parts = [];

    if ( $interval->y !== 0 ) {
        $parts[] = $interval->y . ' year' . ( $interval->y > 1 ? 's' : '' );
    }
    if ( $interval->m !== 0 ) {
        $parts[] = $interval->m . ' month' . ( $interval->m > 1 ? 's' : '' );
    }
    if ( $interval->d !== 0 ) {
        $parts[] = $interval->d . ' day' . ( $interval->d > 1 ? 's' : '' );
    }
    if ( $interval->h !== 0 ) {
        $parts[] = $interval->h . ' hour' . ( $interval->h > 1 ? 's' : '' );
    }
    if ( $interval->i !== 0 ) {
        $parts[] = $interval->i . ' minute' . ( $interval->i > 1 ? 's' : '' );
    }
    if ( $interval->s !== 0 ) {
        $parts[] = $interval->s . ' second' . ( $interval->s > 1 ? 's' : '' );
    }

    // If no difference at all.
    if ( empty( $parts ) ) {
        return '0 seconds';
    }

    // Join the parts into a single string.
    return implode( ', ', $parts );
}

/**
 * Convert duration to a readable date.
 * 
 * @since 1.0.52
 * @param int|float $duration The duration in seconds.
 * @return string|bool $readable_format A formatted string from year to seconds or false. 
 */
function smartwoo_readable_duration( $duration ) {
    if ( is_string( $duration ) ) {
        return $duration;
    }
    
    if ( ! is_int( $duration ) && ! is_float( $duration ) ) {
        return false;
    }

    $duration = round( $duration );
    $years      = floor( $duration / ( 365 * 24 * 3600 ) );
    $duration   %= ( 365 * 24 * 3600 );
    $months     = floor( $duration / ( 30 * 24 * 3600 ) );
    $duration   %= ( 30 * 24 * 3600 );
    $weeks      = floor( $duration / ( 7 * 24 * 3600 ) );
    $duration   %= ( 7 * 24 * 3600 );
    $days       = floor( $duration / ( 24 * 3600 ) );
    $duration   %= ( 24 * 3600 );
    $hours      = floor( $duration / 3600 );
    $duration   %= 3600;
    $minutes    = floor( $duration / 60 );
    $seconds    = $duration % 60;

    $readable_parts = array();
    if ( $years > 0 ) {
        $readable_parts[] = $years . ' year' . ( $years > 1 ? 's' : '' );
    }

    if ( $months > 0 ) {
        $readable_parts[] = $months . ' month' . ( $months > 1 ? 's' : '' );
    }

    if ( $weeks > 0 ) {
        $readable_parts[] = $weeks . ' week' . ( $weeks > 1 ? 's' : '' );
    }

    if ( $days > 0 ) {
        $readable_parts[] = $days . ' day' . ( $days > 1 ? 's' : '' );
    }

    if ( $hours > 0 ) {
        $readable_parts[] = $hours . ' hour' . ( $hours > 1 ? 's' : '' );
    }

    if ( $minutes > 0 ) {
        $readable_parts[] = $minutes . ' minute' . ( $minutes > 1 ? 's' : '' );
    }

    if ( $seconds > 0 ) {
        $readable_parts[] = $seconds . ' second' . ( $seconds > 1 ? 's' : '' );
    }

    $readable_format = implode( ', ', $readable_parts );
    return $readable_format;
}

/**
 * Format a given price with WooCommerce currency settings and thousand separator.
 *
 * @param float $amount The amount to be formatted.
 * @return string $formatted_price The formatted price with currency symbol and thousand separator.
 * @since 1.0.4
 */
function smartwoo_price( $amount ) {
	if ( empty( $amount ) ) {
		$amount = 0.00;
	}

	if ( is_string( $amount ) ) {
		$amount = floatval( $amount );
	}
	
    $decimals           = wc_get_price_decimals();
    $decimal_separator  = wc_get_price_decimal_separator();
    $thousand_separator = wc_get_price_thousand_separator();
    $price              = number_format( abs( $amount ), $decimals, $decimal_separator, $thousand_separator );

	/**
	 * Properly formats negative amount and uses currency position as set in WC settings.
	 * @since 2.0.15
	 */
	$currency_pos       = get_option( 'woocommerce_currency_pos' );
	$currency_symbol    = get_woocommerce_currency_symbol();
	$is_negative        = $amount < 0;

	// Format based on WooCommerce currency position.
	switch ( $currency_pos ) {
		case 'left':
			$formatted_price = $is_negative ? '-' . $currency_symbol . $price : $currency_symbol . $price;
			break;
		case 'right':
			$formatted_price = $is_negative ? '-' . $price . $currency_symbol : $price . $currency_symbol;
			break;
		case 'left_space':
			$formatted_price = $is_negative ? '-' . $currency_symbol . ' ' . $price : $currency_symbol . ' ' . $price;
			break;
		case 'right_space':
			$formatted_price = $is_negative ? '-' . $price . ' ' . $currency_symbol : $price . ' ' . $currency_symbol;
			break;
		default:
			$formatted_price = $is_negative ? '-' . $currency_symbol . $price : $currency_symbol . $price;
	}

	// Allow further customization via filter.
	return apply_filters( 'smartwoo_price', $formatted_price, $price, $amount );
}



/**
 * Check if Proration is Enabled or Disabled
 *
 * @return string Returns "Enabled" if smartwoo_prorate is enabled, "Disabled" if disabled, or "Not Configured" if not set.
 */
function smartwoo_is_prorate() {
	$smartwoo_prorate = get_option( 'smartwoo_prorate', 'Disabled' );

	if ( $smartwoo_prorate === 'Enable' ) {
		return 'Enabled';
	} elseif ( $smartwoo_prorate === 'Disable' ) {
		return 'Disabled';
	} else {
		return 'Disabled';
	}
}

/**
 * Check the configured Invoice Number Prefix.
 *
 * @return string The configured Invoice Number Prefix.
 */
function smartwoo_get_invoice_id_prefix() {
	$invoice_number_prefix = get_option( 'smartwoo_invoice_id_prefix', 'CINV' );
	return $invoice_number_prefix;
}


/**
 * Generate unique Token
 *
 * @return string Random 32-character hexadecimal token
 */
function smartwoo_generate_token() {
	return bin2hex( random_bytes( 16 ) );
}

/**
 * Generates a unique payment link based on invoice details and stores necessary information in transients.
 *
 * @param string $invoice_id  The Service ID associated with the Invoice.
 * @param string $user_email  The email address of the user associated with the order.
 *
 * @return string The generated payment link with a unique URL structure.
 */
function smartwoo_generate_invoice_payment_url( $invoice_id, $user_email ) {

	// Generate a unique token.
	$token 		= smartwoo_generate_token();
	$invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
	$user_email = sanitize_email( $user_email );

	// Store the information in a transient with a 24-hour expiration.
	set_transient(
		'smartwoo_payment_token' . $token,
		array(
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
		),
		24 * HOUR_IN_SECONDS
	);

	// Construct a unique URL structure for the payment link.
	$payment_link = add_query_arg(
		array(
			'action'     => 'sw_invoice_payment',
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
			'token'      => $token,
		),
		home_url()
	);

	return esc_url( $payment_link );
}

/**
 * Verify the token, fetch associated information, and delete the token if valid.
 *
 * @param string $token The token to verify.
 *
 * @return array|false If the token is valid, return an array with invoice_id and user_email; otherwise, return false.
 */
function smartwoo_verify_token( $token ) {
	// Retrieve information from the transient.
	$payment_info = get_transient( 'smartwoo_payment_token' . $token );

	if ( $payment_info ) {

		$invoice_id = $payment_info['invoice_id'];
		$user_email = $payment_info['user_email'];

		// Delete the transient to ensure one-time use.
		delete_transient( 'smartwoo_payment_token' . $token );

		return array(
			'invoice_id' => $invoice_id,
			'user_email' => $user_email,
		);
	}

	// Token is invalid or expired.
	return false;
}


/**
 * Performs check if migration is allowed
 */
function smartwoo_is_migration() {
	$option = get_option( 'smartwoo_allow_migration', 'Disable' );
	if ( 'Enable' === $option ) {
		return 'Enabled';
	}
	return 'Disabled';

}


/**
 * Generate a notice message.
 *
 * @param string $message The notice message.
 * @return string HTML markup for the notice message.
 */
function smartwoo_notice( $message, $dismisable = false ) {
	$class = ( true === $dismisable ) ? 'sw-notice notice notice-info is-dismissible' : 'sw-notice';
	$output  = '<div class="' . esc_attr( $class ) . '">';
	$output .= '<p>' . esc_html( $message ) . '</p>'; 
	$output .= '</div>';

	return $output;
}


if ( ! function_exists( 'smartwoo_error_notice' ) ) {
	/**
	 * Display an error notice to the user.
	 *
	 * @param string|array $messages Error message(s) to display.
	 */
	function smartwoo_error_notice( $messages, $dismisable = false ) {

		if ( "" === $messages ) {
			return $message; // message is required.
		}
		$class = ( true === $dismisable ) ? 'sw-error-notice notice notice-error is-dismissible' : 'sw-error-notice';
		$error = '<div class="' . esc_attr ( $class ) .'">';

		if ( is_array( $messages ) ) {
			$error .= smartwoo_notice( 'Errors !!' );

			$error_number = 1;

			foreach ( $messages as $message ) {
				$error .= '<p>' . esc_html( $error_number . '. ' . $message ) . '</p>';
				++$error_number;
			}
		} else {
			$error .= smartwoo_notice( 'Error !!' );
			$error .= '<p>' . esc_html( $messages ) . '</p>';
		}

		$error .= '</div>';
		return $error;
	}
}

/**
 * Redirects to the invoice preview page based on the provided invoice ID.
 *
 * @param int $invoice_id The ID of the invoice.
 */
function smartwoo_redirect_to_invoice_preview( $invoice_id ) {
	wp_safe_redirect( smartwoo_invoice_preview_url( $invoice_id) );
	exit();
}


/**
 * Get all orders configured for service subscription.
 *
 * @param int|null  $order_id	If provided, returns the ID of the specified order.
 * @param bool $current_user Whether or not to get for current user.
 * @return int|array $order_id | $orders	ID of or Configured orders.
 */
function smartwoo_get_configured_orders_for_service( $order_id = null, $current_user = false ) {

	if ( null !== $order_id ) {
		$order = wc_get_order( $order_id );
		if ( $order && smartwoo_check_if_configured( $order ) ) {
			return $order_id;
		} else {
			return 0;
		}
	}

	$args =	array(
		'limit' => -1,
	);

	if ( true === $current_user ) {
		$args['customer'] = get_current_user_id();	 	
	}


	$wc_orders 	= wc_get_orders( $args );
	$orders		= array();

	if ( empty( $wc_orders ) ) {
		return false;
	}

	foreach ( $wc_orders as $order ) {

		if ( smartwoo_check_if_configured( $order ) ) {
			$orders[] = $order;
		}
	}
	return $orders;
}

/**
 * Check if an order has configured products.
 *
 * @param WC_Order|int $order The WooCommerce order object.
 * @return bool True if the order has configured products, false otherwise.
 */
function smartwoo_check_if_configured( $order ) {

	if ( is_int( $order ) ) {
		$order = wc_get_order( $order );
	}

	if ( ! $order ) {
		return false;
	}

	$items = $order->get_items();

	foreach ( $items as $item_id => $item ) {
		$service_name = wc_get_order_item_meta( $item_id, 'Service Name', true );
		if ( ! empty( $service_name ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get the count for service orders awaiting processing.
 * 
 * @return int $count The total number of unprocessed orders.
 * @since 2.0.0
 */
function smartwoo_count_unprocessed_orders() {
	$count		= wp_cache_get( 'smartwoo_count_unprocessed_orders' );
	if ( false === $count ) {
		$count	= 0;

		$args = array(
			'limit'		=> -1,
			'status'	=> 'processing',
		);

		if ( smartwoo_is_frontend() ) {
			$args['customer'] = get_current_user_id();
		}
	
		$wc_orders	= wc_get_orders( $args );
		
	
		if ( empty( $wc_orders ) ) {
			return $count;
		}
		
	
		foreach ( $wc_orders as $order ) {
			if ( smartwoo_check_if_configured( $order ) ) {
				$count++;
			}
		}
		wp_cache_set( 'smartwoo_count_unprocessed_orders', $count, 'smartwoo_orders', HOUR_IN_SECONDS );
	}
	return $count;
}
/**
 * Frontend navigation menu bar
 *
 * @param string $title The Title of the page.
 * @param string $title_url The URL for the title link.
 */
function smartwoo_get_navbar( $title = '', $title_url = '' ) {

    if ( ! is_user_logged_in() || is_account_page() ) {
        return '';
    }

	wp_enqueue_style( 'dashicons' );

    $nav_item = apply_filters( 'smartwoo_nav_items', 
		array(
			'Services'	=> smartwoo_service_page_url(),
			'Invoices'	=> smartwoo_invoice_page_url(),
			'Buy New'	=> smartwoo_service_page_url() . 'buy-new/',
			'Logout'	=> ''
		)
    );

    $current_page = '';
    $page_title   = $title;

    $nav_bar  = '<div class="service-navbar">';
    $nav_bar .= '<div class="navbar-title-container">';
    $nav_bar .= '<h3><a href="' . esc_url( $title_url ) . '">' . esc_html( $page_title ) . '</a></h3>';
    $nav_bar .= '</div>';

    // Container for the links (aligned to the right).
    $nav_bar .= '<div class="navbar-links-container">';
    $nav_bar .= '<ul>';
    foreach ( $nav_item as  $text => $url ) {
		if ( 'Logout' === $text ) {
			$nav_bar .= '<li><a class="smart-woo-logout">' . esc_html( $text ) . '</a></li>';

		} else {
			$nav_bar .= '<li><a href="' . esc_url( $url ) . '" class="smart-woo-nav-text">' . esc_html( $text ) . '</a></li>';

		}
    }
    $nav_bar .= '</ul>';
    $nav_bar .= '</div>';
	
	// Hamburger icon for toggle.
	$nav_bar .= '<div class="sw-menu-icon">';
	$nav_bar .= '<span class="dashicons dashicons-menu"></span>';
	$nav_bar .= '</div>';

    $nav_bar .= '</div>';

    return $nav_bar;
}


	
/**
 * Define Helper callback function for the wp_kses_allowed_html filter
 * 
 * This function defines a callback for the wp_kses_allowed_html filter,
 * which is used to modify the allowed HTML tags and attributes for the
 * wp_kses_post() function. By adding or modifying the allowed tags and
 * attributes, we can ensure that specific HTML elements are retained
 * when using wp_kses_post(). This callback function is intended to be
 * used in conjunction with the smartwoo_get_navbar() function to customize
 * the allowed HTML for the navigation bar output.
 *
 * @param array  $allowed_tags An array of allowed HTML tags and their attributes.
 * @param string $context      The context in which the HTML is being sanitized.
 * @return array               The modified array of allowed HTML tags and attributes.
 */ 
if( ! function_exists( 'smartwoo_kses_allowed' ) ){
	function smartwoo_kses_allowed( $allowed_tags, $context ) {
	
		$smartwoo_allowed = apply_filters( 'smartwoo_kses_allowed', array(
			'div' => array(
				'class' => true,
				'id' => true,
				'style' => true,
			),
			'p' => array(
				'class' => true,
			),
			'input' => array(
				'class' => true,
				'type' => true,
				'id' => true,
				'checked' => true,
				'data-option' => true,
			),
			'label' => array(
				'for' => true,
				'class' => true,
			),
			'script' => array(
				'type' => true,
				'src' => true,
			),
			'style' => array(),
			'ul'	=> array(),
			'li'	=> array(),
		) );
	
		return array_merge( $allowed_tags, $smartwoo_allowed );
	}
	
}	

/**
 * Smart Woo allowed form html tags.
 */
function smartwoo_allowed_form_html() {
    return apply_filters( 'smartwoo_allowed_form_html', array(
        'form'		=> array(
            'action'	=> true,
            'method'	=> true,
            'class'		=> true,
            'id'		=> true,
        ),
        'input'		=> array(
            'type'		=> true,
            'min'		=> true,
            'max'		=> true,
			'placeholder' => true,
            'name'		=> true,
            'value'		=> true,
            'class'		=> true,
            'id'		=> true,
			'autocomplete' => true,
        ),
        'select'	=> array(
            'name'		=> true,
            'id'		=> true,
            'class'		=> true,
			'required'	=> true,
			'readonly'	=> true
        ),
        'option'		=> array(
            'value'		=> true,
            'selected'	=> true,
        ),
        'label'		=> array(
            'for'		=> true,
            'class'		=> true,
            'style'		=> true,
        ),
        'span'		=> array(
            'class'		=> true,
            'title'		=> true,
			'id'		=> true,
			'style'		=> true,
        ),
        'div'		=> array(
            'class'		=> true,
			'id'		=> true,
			'style'		=> true,
        ),
		'button'	=> array(
			'class'		=> true,
			'id'		=> true,
			'type'		=> true,
			'data-removed-id' => true,
		),
		'a'			=> array(
			'href'		=> true,
			'class'		=> true,
			'id'		=> true,
			'smartwoo-product-id' => true,
			'target'	=> true,
			'title'		=> true,
			'style'		=> true,
			'tempname'	=> true
		),
        'h1'		=> array(),
		'h2'		=> array(),
        'h3'		=> array(),
        'h4'		=> array(),
        'p'			=> array(),
        'hr'		=> array(),
		'strong'	=> array(),
		'br'		=> array(),
        'input'		=> array(
            'type'		=> true,
            'class'		=> true,
            'id'		=> true,
            'name'		=> true,
            'value'		=> true,
            'checked'	=> true,
            'selected'	=> true,
			'required'	=> true,
			'readonly'	=> true,
			'placeholder'	=> true,
			'style'		=> true,
        ),
		'p'			=> array(
			'class'		=> true,
			'span'		=> true,
			'strong'	=> true,
		),
		'ul'		=> array(),
		'li'		=> array(),
    ) );
}

/**
 * Determine whether or not we are in the frontend
 * 
 * @since 1.0.1
 * @since 2.0.12 Added smartwoo_is_frontend filter @param bool.
 */
function smartwoo_is_frontend() {
	if ( ! is_admin() || wp_doing_ajax() ) {
		return apply_filters( 'smartwoo_is_frontend', true );
	}

	return false;
}

/**
 * Product configuration page.
 * 
 * @param int $product_id the product ID.
 * @return string $configure page | link to current page #.
 */
function smartwoo_configure_page( $product_id ){
	if ( empty( $product_id ) || "product" !== get_post_type( absint( $product_id ) ) ) {
		return $product_id;
	}
	return home_url( '/configure/?product_id=' . absint( $product_id ) );
}


/**
 * Set user's login timestamp.
 * 
 * @param string $user_login	User's Username.
 * @param object $user			WordPress user object.
 * @since      : 1.0.1 
 */
function smartwoo_timestamp_user_at_login( $user_login, $user ) {
	update_user_meta( $user->ID, 'smartwoo_login_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_login', 'smartwoo_timestamp_user_at_login', 99, 2 );

/**
 * Set user's logout timestamp.
 * 
 * @param $user_id		The logged user's ID
 */
function smartwoo_timestamp_user_at_logout( $user_id ){
	update_user_meta( $user_id, 'smartwoo_logout_timestamp', current_time( 'timestamp' ) );
}
add_action( 'wp_logout', 'smartwoo_timestamp_user_at_logout' );

/**
 * Retrieve the user's current login date and time.
 * 
 * @param int $user_id The User's ID.
 * @since      : 1.0.1
 */
function smartwoo_get_current_login_date( $user_id ) {
    $timestamp = get_user_meta( $user_id, 'smartwoo_login_timestamp', true );

    if ( ! is_numeric( $timestamp ) || absint( $timestamp ) <= 0 ) {
        // Fallback to current time if $timestamp is not a valid integer.
        $timestamp = current_time( 'timestamp' );
    }

    return smartwoo_timestamp_to_date( $timestamp, true );
}

/**
 * Retrieve the user's last login date and time
 * 
 * @param int $user_id  The User's ID
 * @since	: 1.0.1
 */
function smartwoo_get_last_login_date( $user_id ) {

	$timestamp = get_user_meta( $user_id, 'smartwoo_logout_timestamp', true );

    // Check if $timestamp is not a valid integer (may be a string).
    if ( ! is_numeric( $timestamp ) || absint( $timestamp ) <= 0 ) {
		$timestamp = current_time( 'timestamp' );
    }

    return smartwoo_timestamp_to_date( $timestamp, true );
}


/**
 * Smart Woo login form
 * 
 * @param array $options assosciative array of options.
 * @since 1.1.0
 */
function smartwoo_login_form( $options ) {
	wp_enqueue_style( 'dashicons' );
	$default_options = array(
		'notice'	=> '',
		'redirect'	=> get_permalink(),
	);

	$parsed_args = wp_parse_args( $options, $default_options );

	$form  = '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="smartwoo-login-form" id="smartwoo-login-form">';
	$form .= '<div class="smartwoo-login-form-content">';
	$form .= '<div class="smartwoo-login-form-notice">';
	$form .= wp_kses_post( $parsed_args['notice'] );
	if ( get_transient( 'smartwoo_login_error' ) ) {
		$form .= wp_kses_post( get_transient( 'smartwoo_login_error' ) );
		delete_transient( 'smartwoo_login_error' );
	}
	$form .= '</div>';

	$form .= '<div class="smartwoo-login-form-body">';
	$form .= '<label for="sw-user-login" class="smartwoo-login-form-label">Username/Email *</label>';
	$form .= '<input type="text" id="sw-user-login" class="smartwoo-login-input" name="user_login" />';
	$form .= '</div>';

	$form .= '<div class="smartwoo-login-form-body">';
	$form .= '<label for="sw-user-password" class="smartwoo-login-form-label">Password *</label>';
	$form .= '<input type="password" id="sw-user-password" class="smartwoo-login-input" name="password" />';
	$form .= '<span id="smartwoo-login-form-visible" class="dashicons dashicons-visibility"></span>';
	$form .= '<span id="smartwoo-login-form-invisible" class="dashicons dashicons-hidden" style="display: none"></span>';
	$form .= '</div>';

	$form .= wp_nonce_field( 'smartwoo_login_nonce', 'smartwoo_login_nonce' );
	$form .= '<input type="hidden" name="action" value="smartwoo_login_form" />';
	$form .= '<input type="hidden" name="redirect" value="' . esc_url( $parsed_args['redirect'] ) . '" />';
	$form .= '<input type="hidden" name="referer" value="' . esc_url( wp_get_referer() ) . '" />';
	$form .= '<div style="display:flex; flex-direction: row; justify-content: space-between;">';
	$form .= '';
	$form .= '<label style="margin-left:10px;" for="remember_me"> <input id="remember_me" type="checkbox" name="remember_me"/> Remember Me</label>';
	$form .= '<button type="submit" class="sw-blue-button">' . apply_filters( 'smartwoo_login_button_text', __( 'login', 'smart-woo-service-invoicing' ) ) . '</button>';
	$form .= '</div>';
	$form .= '</div>';
	$form .= '</form>';

	return $form;
}

/**
 * Set form validation error
 * 
 * @param array|string $data	The error to set.
 * @return bool
 * @since 2.0.0
 */
function smartwoo_set_form_error( $data ) {
	if ( empty( $data ) ) {
		return false;
	}
	
	return set_transient( 'smartwoo_form_validation_error_'. get_current_user_id(), $data, 10 );
}

/**
 * Get form validation error
 * 
 * @return mixed $error.
 * @since 2.0.0
 */
function smartwoo_get_form_error() {
	$error = get_transient( 'smartwoo_form_validation_error_'. get_current_user_id() );
	if ( false !== $error ) {
		delete_transient( 'smartwoo_form_validation_error_'. get_current_user_id() );
	}

	return $error;
}

/**
 * Set form success message.
 * 
 * @param mixed $message The message.
 * @return bool|mixed
 */
function smartwoo_set_form_success( $message ) {
	if ( empty( $message ) ) {
		return false;
	}
	
	return set_transient( 'smartwoo_form_validation_success_'. get_current_user_id(), $message, 10 );
}

/**
 * Get form success message
 */
function smartwoo_get_form_success() {
	$message = get_transient( 'smartwoo_form_validation_success_'. get_current_user_id() );
	if ( false !== $message ) {
		delete_transient( 'smartwoo_form_validation_success_'. get_current_user_id() );
	}

	return $message;
}


/**
 * Construct a download url for downloadable assets.
 * 
 * @param string $resource The URL of the resource to download
 * 
 * @return string $url The download url (optional).
 */
function smartwoo_download_url( $resource_id, $key, $asset_id, $service_id ) {
	if ( empty( $resource_id ) || empty( $key ) ) {
		return '';
	}
	$url = add_query_arg( array(
		'smartwoo_action' 	=> 'smartwoo_download',
		'resource_id'		=> rawurlencode( $resource_id ),
		'asset_id'			=> rawurlencode( $asset_id ),
		'key'				=> rawurlencode( $key ),
		'service_id'		=> rawurlencode( $service_id ),
		'token'				=> wp_create_nonce( 'smartwoo_download_nonce' ),
	), smartwoo_service_page_url() );

	return $url;
}

/**
 * Set the document title of any page in WordPress.
 *
 * @param string $title The page title.
 * @since 2.0.15
 */
function smartwoo_set_document_title( $title ) {
    $sep           			= apply_filters( 'document_title_separator', '-' );
    $title_parts   			= array( 'title' => $title );
    $title_parts['site']	= get_bloginfo( 'name', 'display' );

    // Combine title and site name with the separator
    $final_title   = implode( " $sep ", array_filter( $title_parts ) );
    ?>
    <script>
        document.addEventListener( "DOMContentLoaded", function() {
            document.title = <?php echo wp_json_encode( $final_title ); ?>;
        });
    </script>
    <?php
}

