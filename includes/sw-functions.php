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
 * @param array $args List of arguments to overide.
 * @return string $formatted_price The formatted price with currency symbol and thousand separator.
 * @since 1.0.4
 * @since 2.3.0 Add the $args parameter.
 */
function smartwoo_price( $amount, $args = array() ) {
	if ( empty( $amount ) ) {
		$amount = 0.00;
	}

	if ( ! is_numeric( $amount ) ) {
		$amount = floatval( $amount );
	}

	$args = apply_filters(
		'smartwoo_price_args',
		wp_parse_args(
			$args,
			array(
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
			)
		)
	);
	
    $decimals           = $args['decimals'];
    $decimal_separator  = $args['decimal_separator'];
    $thousand_separator = $args['thousand_separator'];
    $price              = number_format( abs( $amount ), $decimals, $decimal_separator, $thousand_separator );

	/**
	 * Properly formats negative amount and uses currency position as set in WC settings.
	 * @since 2.0.15
	 */
	$currency_pos       = get_option( 'woocommerce_currency_pos' );
	$currency_symbol    = get_woocommerce_currency_symbol( $args['currency'] );
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
 * @param SmartWoo_Invoice|string $invoice  The SmartWoo_Invoice object or the invoice ID.
 * @return string The generated payment link.
 */
function smartwoo_generate_invoice_payment_url( $invoice ) {
	$invoice = ( $invoice instanceof SmartWoo_Invoice ) ? $invoice : SmartWoo_Invoice_Database::get_invoice_by_id( $invoice );

	if ( ! $invoice ) {
		return '';
	}

	// Generate a unique token.
	$token 		= smartwoo_generate_token();
	$invoice_id = $invoice->get_invoice_id();
	$user_email = $invoice->get_user()->get_email();

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

	return $payment_link;
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
		// Delete the transient to ensure one-time use.
		delete_transient( 'smartwoo_payment_token' . $token );
		return $payment_info;
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
	$class = ( true === $dismisable ) ? 'sw-notice notice-info is-dismissible' : 'sw-notice';
	$output  = '<div class="' . esc_attr( $class ) . '">';
	$output .= '<p>' . wp_kses_post( $message ) . '</p>'; 
	$output .= '</div>';

	return $output;
}



/**
 * Display an error notice to the user.
 *
 * @param string|array $messages Error message(s) to display.
 */
function smartwoo_error_notice( $messages, $dismisable = false ) {

	if ( "" === $messages ) {
		return $message; // message is required.
	}
	$class = ( true === $dismisable ) ? 'sw-error-notice  is-dismissible' : 'sw-error-notice';
	$error = '<div class="' . esc_attr ( $class ) .'">';
	if ( $dismisable ) {
		$error .= '<span class="dashicons dashicons-dismiss swremove-field" style="color:#ff0707; float: right; font-weight: 600; margin-right: 10px; padding: 5px;
		cursor: pointer; font-size: 16px;" title="dismiss"></span>';
	}

	if ( is_array( $messages ) ) {
		$error .= smartwoo_notice( 'Errors!' );

		$error_number = 1;

		foreach ( $messages as $message ) {
			$error .= '<p>' . esc_html( $error_number . '. ' . $message ) . '</p>';
			++$error_number;
		}
	} else {
		$error .= smartwoo_notice( 'Error!' );
		$error .= '<p>' . wp_kses_post( $messages ) . '</p>';
	}

	$error .= '</div>';
	return $error;
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

	if ( $order->get_meta( '_smartwoo_is_service_order' ) ) {
		return true;
	}

	$items = $order->get_items();

	foreach ( $items as $item ) {
		if ( $item->get_meta( '_smartwoo_service_name' ) 
		|| $item->get_meta( 'Service Name' ) 
		|| $item->get_meta( '_smartwoo_service_url' ) 
		|| $item->get_meta( '_smartwoo_sign_up_fee' ) ) {
			return true;
		} 
	}

	return false;
}

/**
 * Get the count for service orders awaiting processing.
 *
 * Acts as a wrapper around SmartWoo_Order::count_awaiting_processing(),
 * caching the result in a transient for performance.
 *
 * @return int The total number of unprocessed orders.
 * @since 2.0.0
 */
function smartwoo_count_unprocessed_orders() {
	$count = get_transient( 'smartwoo_count_unprocessed_orders' );

	if ( false === $count ) {
		// Delegate actual counting to the SmartWoo_Order class.
		$count = SmartWoo_Order::count_awaiting_processing();

		set_transient( 'smartwoo_count_unprocessed_orders', $count, HOUR_IN_SECONDS );
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

    if ( is_account_page() ) {
        return '';
    }

	wp_enqueue_style( 'dashicons' );

    $nav_item = apply_filters( 'smartwoo_nav_items', 
		array(
			'Services'	=> smartwoo_service_page_url(),
			'Invoices'	=> smartwoo_invoice_page_url(),
			'Buy New'	=> smartwoo_get_endpoint_url( 'buy-new', '', smartwoo_service_page_url() ),
			'Logout'	=> ''
		)
    );

	if ( ! is_user_logged_in() ) {
		unset( $nav_item['Services'], $nav_item['Invoices'], $nav_item['Logout'] );
	}

    $current_page = '';
    $page_title   = $title;

    $nav_bar  = '<div class="service-navbar">';
    $nav_bar .= '<div class="navbar-title-container">';
    $nav_bar .= '<h3><a href="' . esc_url( $title_url ) . '">' . esc_html( $page_title ) . '</a></h3>';
    $nav_bar .= '</div>';

    $nav_bar .= '<div class="navbar-links-container">';
    $nav_bar .= '<div style="text-align: right;">';
    $nav_bar .= '<span class="dashicons dashicons-no-alt sw-close-icon"></span>';
    $nav_bar .= '</div>';
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
	
	$nav_bar .= '<div class="sw-menu-icon">';
	$nav_bar .= '<span class="dashicons dashicons-menu"></span>';
	$nav_bar .= '</div>';

    $nav_bar .= '</div>';

    return $nav_bar;
}


	
/**
 * Define Helper callback function for the `wp_kses_allowed_html` filter
 * 
 * This function defines a callback for the `wp_kses_allowed_html` filter,
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
		'ul'	=> array(
			'style'		=> true,
			'class'		=> true,
			'limit'		=> true
		),
		'li'	=> array(),
		'a'		=> array(
			'data-product_name'	=> true
		),
	) );

	return array_merge( $allowed_tags, $smartwoo_allowed );
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
			'readonly'	=> true,
			'field-name'	=> true
        ),
        'option'		=> array(
            'value'		=> true,
            'selected'	=> true,
			'class'		=> true,
			'id'		=> true,
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
			'onclick'	=> true,
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
			'style'		=> true,
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
		'ul'		=> array(
			'style'		=> true,
			'class'		=> true,
			'limit'		=> true
		),
		'li'		=> array(
			'class'	=> true,
			'id'	=> true
		),
		'img'		=> array(
			'src'	=> true,
			'alt'	=> true
		),
		'td'	=> array(
			'style'	=> true
		),
		'textarea'	=> array(
			'class'	=> true,
			'style'	=> true,
			'id'	=> true,
			'name'	=> true,
			'placeholder'	=> true
		)
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
	return smartwoo_get_endpoint_url( smartwoo_get_product_config_query_var(), $product_id, home_url() );
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
 * @param bool $hide_logged_in Whether to hide the form when current user is logged in?
 * @since 1.1.0
 */
function smartwoo_login_form( $options, $hide_logged_in = false ) {
	/**
	 * @since 2.2.2 Hides form.
	 */
	if ( $hide_logged_in && is_user_logged_in() ) {
		return '';
	}

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
	if ( $error = smartwoo_get_form_error() ) {
		$form .= '<div id="sw-error-div">' . wp_kses_post( $error ) . '</div>';
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
	$form .= '<button type="submit" class="sw-blue-button" id="sw-login-btn">' . apply_filters( 'smartwoo_login_button_text', __( 'login', 'smart-woo-service-invoicing' ) ) . '</button>';
	$form .= '</div>';
	$form .= '</div>';
	$form .= '</form>';

	return $form;
}

/**
 * Get the current user's session id for Smart Woo
 * 
 * @return string $session_id
 * @since 2.2.2
 */
function smartwoo_get_user_session_id() {
    if ( ! isset( $_COOKIE['smartwoo_user_session'] ) ) {
        // Generate a unique session ID for the user
        $session_id = smartwoo_secure_uuid4();
		if ( ! headers_sent() ) {
			setcookie( 'smartwoo_user_session', $session_id, time() + MINUTE_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		}
		
        $_COOKIE['smartwoo_user_session'] = $session_id;
    }
    return sanitize_text_field( wp_unslash( $_COOKIE['smartwoo_user_session'] ) );
}

/**
 * Generate a scure uuid.
 * 
 * @since 2.2.2
 */
function smartwoo_secure_uuid4() {
	// Generate 16 bytes (128 bits) of random data.
	$data = random_bytes(16);

	// Ensure that the version and variant bits are correctly set.
	$data[6] = chr( (ord($data[6] ) & 0x0f ) | 0x40 ); // Set version to 4 (0100).
	$data[8] = chr( ( ord( $data[8] ) & 0x3f ) | 0x80 ); // Set variant to 10xx.

	// Split the binary data into segments for UUID formatting.
	$parts = unpack( 'N1a/n1b/n1c/n1d/N1e', $data );

	// Format the UUID using the unpacked values.
	return sprintf(
		'%08x-%04x-%04x-%04x-%012x',
		$parts['a'], $parts['b'], $parts['c'], $parts['d'], $parts['e']
	);
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

	$user_id = is_user_logged_in() ? get_current_user_id() : smartwoo_get_user_session_id();
	
	return set_transient( 'smartwoo_form_validation_error_' . $user_id, $data, MINUTE_IN_SECONDS );
}

/**
 * Get form validation error
 * 
 * @return mixed $error.
 * @since 2.0.0
 */
function smartwoo_get_form_error() {
	$user_id = is_user_logged_in() ? get_current_user_id() : smartwoo_get_user_session_id();

	$error = get_transient( 'smartwoo_form_validation_error_'. $user_id );
	if ( false !== $error ) {
		delete_transient( 'smartwoo_form_validation_error_'. $user_id );
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
	
	$user_id = is_user_logged_in() ? get_current_user_id() : smartwoo_get_user_session_id();

	return set_transient( 'smartwoo_form_validation_success_'. $user_id, $message, 30 );
}

/**
 * Get form success message
 */
function smartwoo_get_form_success() {
	$user_id = is_user_logged_in() ? get_current_user_id() : smartwoo_get_user_session_id();

	$message = get_transient( 'smartwoo_form_validation_success_'. $user_id );
	if ( false !== $message ) {
		delete_transient( 'smartwoo_form_validation_success_'. $user_id );
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
function smartwoo_download_url( $resource_id, $asset_id, $service_id ) {
	if ( empty( $resource_id ) || empty( $asset_id ) ) {
		return '';
	}
	$url = add_query_arg( array(
		'smartwoo_action' 	=> 'smartwoo_download',
		'resource_id'		=> rawurlencode( $resource_id ),
		'asset_id'			=> rawurlencode( $asset_id ),
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
	$title					= wp_strip_all_tags( sanitize_text_field( wp_unslash( $title ) ) );
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

/**
 * Construct a dropdown of all WordPress users and add option for guest users.
 * 
 * @param string $selected The selected value.
 * @param bool $args An associative array of html attributes and more.
 * @since 2.2.3
 */
function smartwoo_dropdown_users( $selected = '', $args = array() ) {
	$default_args = array(
		'class'		=> 'sw-form-input',
		'id'		=> 'user_data',
		'required'	=> false,
		'echo'		=> true,
		'add_guest'	=> true,
		'name'		=> 'user_data',
		'field_name'	=> 'A user',
		'option_none'	=> __( 'Select user', 'smart-woo-service-invoicing' )
	);

	$parsed_args = wp_parse_args( $args, $default_args );

	$users	= get_users( array( 'fields' => array( 'display_name', 'user_email', 'ID' ) ) );
	
	$dropdown = '<select class="' . $parsed_args['class'] . '" name="' . $parsed_args['name'] . '" id="' . $parsed_args['id'] . '" field-name="' . $parsed_args['field_name'] . '" ' . ( $parsed_args['required'] ? 'required' : '' ) . '>';
	$dropdown .= '<option value="">' . $parsed_args['option_none'] . '</option>';

	if ( $parsed_args['add_guest'] ) {
		$dropdown .= '<option value="smartwoo_guest">' . __( 'Guest', 'smart-woo-service-invoicing' ). '</option>';

	}
	foreach ( $users as $wp_user ) {
		$attr = selected( $selected, $wp_user->ID . '|' . $wp_user->user_email, false );
		$dropdown .= '<option value="' . $wp_user->ID . '|' . $wp_user->user_email . '" ' . $attr . '>' . $wp_user->display_name . ' (' . $wp_user->user_email . ')</option>';
	}

	/**
	 * Filter to add more options to the dropdown, does not modify the existing options.
	 */
	$dropdown .= apply_filters( 'smartwoo_dropdown_users_add', '' );
	$dropdown .= '</select>';

	$dropdown .= apply_filters( 'smartwoo_dropdown_user_meta', 
		'<div class="sw-invoice-form-meta">
			<input type="hidden" name="is_guest_invoice" value="no"/>
			<input type="hidden" name="first_name" />
			<input type="hidden" name="last_name" />
			<input type="hidden" name="billing_email" />
			<input type="hidden" name="billing_company" />
			<input type="hidden" name="billing_address" />
			<input type="hidden" name="billing_phone" />
		</div>' 
	);

	if ( $parsed_args['echo'] ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	} else {
		return $dropdown;
	}
}

/**
 * Add an input field to control the limit of items displayed on sw table or any table
 * that sets the `limit` value in the url query arg.
 */
function smartwoo_table_limit_field( $limit = 25 ) {
	?>
		<div class="sw-table-limit-container">
			<input type="number" id="smartwooChangeLimitValue" placeholder="Change Limit" min="0" max="100" value="<?php echo absint( $limit ); ?>"><input type="button" id="smartwooChangeLimitBtn" value="Change Limit">
		</div>
		<script>
			let changeTableLimitbtn = document.querySelector( '#smartwooChangeLimitBtn' );

			if ( changeTableLimitbtn ) {
				changeTableLimitbtn.addEventListener( 'click', ()=>{
					let url = new URL( window.location.href );
					let limitValue = document.querySelector( '#smartwooChangeLimitValue' ).value;
					if ( limitValue > 0 ) {
						url.searchParams.set( 'limit', limitValue );
						window.location.href = url;
					} else {
						url.searchParams.delete( 'limit' );
						window.location.href = url;
					}
				});
			}
		</script>
	<?php
}

/**
 * Submenu navigation button tab function
 *
 * @param array  $tabs         An associative array of tabs (tab_slug => tab_title).
 * @param string $title        The title of the current submenu page.
 * @param string $page_slug    The admin menu/submenu slug.
 * @param string $current_tab  The current tab parameter for the submenu page.
 * @param string $query_var    The query variable.
 */
function smartwoo_sub_menu_nav( $tabs, $title, $page_slug, $current_tab, $query_var ) {
	$output  = '<div class="wrap">';
	$output .= '<h1 class="wp-heading-inline">' . wp_kses_post( $title ) . '</h1>';
	$output .= '<nav class="nav-tab-wrapper">';

	foreach ( $tabs as $tab_slug => $tab_title ) {
		$active_class = ( $current_tab === $tab_slug ) ? 'nav-tab-active' : '';

		if ( '' === $tab_slug ) {
			$output      .= "<a href='" . esc_url( admin_url( 'admin.php?page=' . $page_slug ) ) . "' class='nav-tab $active_class'>$tab_title</a>";

		} else {
			$output      .= "<a href='" . esc_url( add_query_arg( $query_var, $tab_slug, admin_url( 'admin.php?page=' . $page_slug ) ) ) . "' class='nav-tab $active_class'>$tab_title</a>";

		}
	}

	$output .= '</nav>';
	$output .= '</div>';

	return $output;
}

/**
 * Render the billing cycle select input
 * 
 * @param mixed $selected The selected option.
 * @param array $args Array of HTML attributes.
 */
function smartwoo_billing_cycle_dropdown( $selected = null, $args = array()) {
	$default_args = array(
		'class'		=> 'sw-form-input',
		'id'		=> 'sw_billing_cycle',
		'required'	=> false,
		'echo'		=> true,
		'name'		=> 'billing_cycle',
		'option_none'	=> __( 'Select Billing Cycle', 'smart-woo-service-invoicing' ),
		'field_name'	=> __( 'A billing cycle', 'smart-woo-service-invoicing' )
	);

	$parsed_args = wp_parse_args( $args, $default_args );
	$billing_cycles = smartwoo_supported_billing_cycles();
	$dropdown = '<select class="' . $parsed_args['class'] . '" name="' . $parsed_args['name'] . '" id="' . $parsed_args['id'] . '" field-name="' . $parsed_args['field_name'] . '" ' . ( $parsed_args['required'] ? 'required': '' ) . '>';
	$dropdown .= '<option value="">' . $parsed_args['option_none'] . '</option>';
	foreach( $billing_cycles as $value => $label ) {
		$is_selected = ( $value === $selected ) ? 'selected="selected"' : '';
		$dropdown   .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $is_selected ) . '>' . esc_html( $label ) . '</option>';
	}
	$dropdown .= '</select>';
	if ( true === $parsed_args['echo'] ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	}
	return $dropdown;
}

/**
 * Get supported billing cycle
 */
function smartwoo_supported_billing_cycles() {
	return apply_filters( 'smartwoo_supported_billing_cycles',
		array(
			'Weekly'		=> __( 'Weekly', 'smart-woo-service-invoicing' ),
			'Monthly'		=> __( 'Monthly', 'smart-woo-service-invoicing' ),
			'Quarterly'		=> __( 'Quarterly', 'smart-woo-service-invoicing' ),
			'Semiannually'	=> __( 'Semiannually', 'smart-woo-service-invoicing' ),
			'Yearly'		=> __( 'Yearly', 'smart-woo-service-invoicing' )
		)
	);
}

/**
 * Get default gravatar image url
 */
function smartwoo_get_avatar_placeholder_url() {
	return SMARTWOO_DIR_URL . '/assets/images/avatar-mysteryperson.png';
}

/**
 * Get service subscriptions status dropdown.
 * 
 * @param mixed $selected The selected option.
 * @param array $args	List of html attributes
 * 
 * @since 2.5 Support for additional options.
 * @return string The dropdown HTML.
 */
function smartwoo_service_status_dropdown( $selected = '', $args = array() ) {
	$default_args = array(
		'class'		=> 'sw-form-input',
		'id'		=> 'status',
		'required'	=> false,
		'echo'		=> true,
		'name'		=> 'status',
		'option_none'	=> __( 'Auto Calculate', 'smart-woo-service-invoicing' ),
		'additional_options' => array()
	);

	$parsed_args = wp_parse_args( $args, $default_args );
	$statuses = smartwoo_supported_service_status();

	$dropdown = '<select class="' . $parsed_args['class'] . '" name="' . $parsed_args['name'] . '" id="' . $parsed_args['id'] . '" ' . ( $parsed_args['required'] ? 'required' : '' ) . '>';
	$dropdown .= '<option value="">' . $parsed_args['option_none'] . '</option>';

	foreach ( $statuses as $value => $label ) {
		$attr = selected( $selected, $value, false );
		$dropdown .= '<option value="' . $value . '" ' . $attr . '>' . $label . '</option>';
	}

	if ( ! empty( $parsed_args['additional_options'] ) && is_array( $parsed_args['additional_options'] ) ) {
		foreach ( $parsed_args['additional_options'] as $value => $label ) {
			$attr = selected( $selected, $value, false );
			$dropdown .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $attr ) . '>' . esc_html( $label ) . '</option>';
		}
	}

	$dropdown .= '</select>';

	if ( true === $parsed_args['echo'] ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );

	}

	return $dropdown;

}

/**
 * Get supported service status with descripion label.
 */
function smartwoo_supported_service_status() {
	return apply_filters( 'smartwoo_supported_service_status',
		array(
			'Active'			=> __( 'Active', 'smart-woo-service-invoicing' ),
			'Active (NR)'		=> __( 'Disable Renewal', 'smart-woo-service-invoicing' ),
			'Suspended'			=> __( 'Suspend Service', 'smart-woo-service-invoicing' ),
			'Cancelled'			=> __( 'Cancel Service', 'smart-woo-service-invoicing' ),
			'Due for Renewal'	=> __( 'Due for Renewal', 'smart-woo-service-invoicing' ),
			'Expired'			=> __( 'Expired', 'smart-woo-service-invoicing' )
		)
	);
}

/**
 * Interpretes the value of a service subscription status to a system value.
 * 
 * @param string|null $status The status to interprete.
 * @return string|null The interpretted status.
 * @since 2.5.0
 */
function smartwoo_interprete_service_status( $status ) {
	// This describes the way we determine the status of a service subscription.
	// The system auto calculates status that are set to null, so empty values are set to null.
	if ( is_null( $status ) || '' === $status ) {
		return null;
	}

	// We attempt to interprete the status value to a system value.
	// The system values are: Active, Active (NR), Suspended, Cancelled, Due for Renewal, Expired.
	// Any other value is filtered through `smartwoo_interprete_service_status`.
	switch ( strtolower( $status ) ) {
		case 'active':
		case 'is_active':
		case 'service_active':
		case 'activate':
		case 'activated':
			$status = 'Active';
			break;
		case 'active (nr)':
		case 'active (no renewal)':
		case 'disable renewal':
		case 'disable_renewal':
		case 'renewal_disabled':
		case 'no_renewal':
		case 'active_nr':
		case 'active_no_renewal':
			$status = 'Active (NR)';
			break;
		case 'suspended':
		case 'suspend service':
		case 'is_suspended':
		case 'service_suspended':
		case 'suspend':
			$status = 'Suspended';
			break;
		case 'cancelled':
		case 'canceled':
		case 'cancel service':
		case 'is_cancelled':
		case 'service_cancelled':
			$status = 'Cancelled';
			break;
		case 'due for renewal':
		case 'due_renewal':
		case 'due for_renewal':
		case 'due_renewal':
		case 'due':
		case 'is_due':
		case 'renewal_due':
			$status = 'Due for Renewal';
			break;
		case 'expired':
		case 'expire':
		case 'is_expired':
		case 'has_expired':
		case 'service_expired':
			$status = 'Expired';
			break;
		case 'auto':
		case 'automatic':
		case 'auto_calc':
		case 'auto_calculate':
		case 'auto-calculate':
			$status = null;
			break;
		default:
		$status = $status;
		break;

	}

	return apply_filters( 'smartwoo_interprete_service_status', $status );
}

/**
 * Get the settings for global next payment date.
 * 
 * @param string $context The context.
 * @since 2.3.0
 * @return string|array
 */
function smartwoo_get_global_nextpay( $context = 'view' ) {
	$options = get_option( 'smartwoo_global_next_payment_interval', false );

	if ( ! is_array( $options ) ) {
		$options = array( 'operator' => '-', 'number' => 7, 'unit' => 'days' );
	}

	$value = $options['operator'] . $options['number'] . ' ' . $options['unit'];
	return ( 'edit' === $context ) ? $options : $value  ;
}

/**
 * Check if the current theme is a block-based (FSE) theme.
 *
 * @since 1.0.0
 * @return bool
 */
function smartwoo_is_block_theme() {
    if ( function_exists( 'wp_is_block_theme' ) ) {
        return (bool) wp_is_block_theme();
    }

    if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
        return (bool) gutenberg_is_fse_theme();
    }

    return false;
}

/**
 * Generates an HTML and CSS switch toggle.
 * 
 * @return string HTML for the switch toggle.
 */
function smartwoo_get_switch_toggle( array $args ) {
    $default_args = array(
        'id'       => '',
        'name'     => '',
        'checked'  => false,
        'disabled' => false,
        'value'    => '1',
    );

    $parsed_args = wp_parse_args( $args, $default_args );
    $checked_attr  = $parsed_args['checked'] ? 'checked' : '';
    $disabled_attr = $parsed_args['disabled'] ? 'disabled' : '';
    ?>
        <input
            type="checkbox"
            id="<?php echo esc_attr( $parsed_args['id'] ); ?>"
            name="<?php echo esc_attr( $parsed_args['name'] ); ?>"
            value="<?php echo esc_attr( $parsed_args['value'] ); ?>"
            class="smartwoo-switch-toggle-input"
            <?php echo esc_attr( $checked_attr ); ?>
            <?php echo esc_attr( $disabled_attr ); ?>
        >
        <label for="<?php echo esc_attr( $parsed_args['id'] ); ?>" class="smartwoo-switch-toggle-label">
            <span class="smartwoo-switch-toggle-inner"></span>
            <span class="smartwoo-switch-toggle-slider"></span>
        </label>
    <?php
}

/**
 * Get the edit billing details form.
 * 
 * @since 1.0.15
 */
/**
 * Output the edit billing address form in Smart Woo client portal.
 */
function smartwoo_get_edit_billing_form() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user_id  = get_current_user_id();
    $customer = new WC_Customer( $user_id );

    $address_fields = WC()->countries->get_address_fields(
        $customer->get_billing_country(),
        'billing_'
    );

    // Pre-fill values from WC_Customer object
    foreach ( $address_fields as $key => $field ) {
        $getter = 'get_' . $key;
        $address_fields[ $key ]['value'] = is_callable( [ $customer, $getter ] )
            ? $customer->$getter()
            : get_user_meta( $user_id, $key, true );
    }

	include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/form-edit-address.php';
}

/**
 * Get the edit account details form.
 *
 * @since 2.0.15
 */
function smartwoo_get_edit_account_form() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user = wp_get_current_user();

    // Define account fields (pattern follows WooCommerce form conventions)
    $account_fields = array(
        'account_first_name' => array(
            'type'        => 'text',
            'label'       => __( 'First name', 'smart-woo-service-invoicing' ),
            'required'    => true,
            'class'       => array( 'form-row-first' ),
            'autocomplete'=> 'given-name',
            'value'       => $user->first_name,
        ),
        'account_last_name' => array(
            'type'        => 'text',
            'label'       => __( 'Last name', 'smart-woo-service-invoicing' ),
            'required'    => true,
            'class'       => array( 'form-row-last' ),
            'autocomplete'=> 'family-name',
            'value'       => $user->last_name,
        ),
        'account_display_name' => array(
            'type'        => 'text',
            'label'       => __( 'Display name', 'smart-woo-service-invoicing' ),
            'required'    => true,
            'description' => __( 'This will be how your name will be displayed in the account section and in reviews', 'smart-woo-service-invoicing' ),
            'value'       => $user->display_name,
        ),
        'account_email' => array(
            'type'        => 'email',
            'label'       => __( 'Email address', 'smart-woo-service-invoicing' ),
            'required'    => true,
            'autocomplete'=> 'email',
            'value'       => $user->user_email,
        ),
    );

    include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/form-edit-account.php';
}


/**
 * Get the correct URL format for an endpoint according to the site permalink structure
 * 
 * @param string $endpoint The page endpoint.
 * @param string $query_value The value of the query variable.
 * @return string
 */
function smartwoo_get_endpoint_url( $endpoint, $query_value = '', $permalink = '' ) {
	if ( empty( $permalink ) ) {
		$permalink = get_permalink();
	}

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string	= '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink		= current( explode( '?', $permalink ) );
			
		} else {
			$query_string = '';
		}
		
		$url = trailingslashit( $permalink ); 
		$url .= trailingslashit( $endpoint ) . ( ! empty( $query_value ) ? user_trailingslashit( $query_value ) : '' );
		$url .= $query_string;
	} else {
		$endpoint	= ( 'page' === $endpoint ) ? 'paged' : $endpoint;
		$url = add_query_arg( $endpoint, $query_value, $permalink );
	}

	return $url;
}

/**
 * Get the fast checkout options.
 * 
 * @return array
 */
function smartwoo_fast_checkout_options() {
	$options = get_option( 'smartwoo_fast_checkout_options', array() );
	$default_options = array(
		'title'						=> 'Configure {{product_name}}',
		'service_name_placeholder'	=> 'Service name (required)',
		'url_placeholder'			=> 'URL (optional)',
		'checkout_button_text'		=> 'Checkout',
		'description'				=> __( 'This subscription requires a name. Please enter one before proceeding.', 'smart-woo-service-invoicing' ),
		'title_color'				=> '#333333',
		'modal_background_color'	=> '#FFFFFF',
		'button_background_color'	=> '#0073E6',
		'button_text_color'			=> '#FFFFFF',
	);

	$parsed_options	= wp_parse_args( $options, $default_options );

	return $parsed_options;
}

/**
 * Retrieve and sanitize a query parameter from the URL, with automatic type detection.
 *
 * @param string $key     The key to retrieve from the query parameters.
 * @param mixed  $default The default value to return if the key is not found.
 * @return mixed The sanitized value of the query parameter, or the default value.
 */
function smartwoo_get_query_param( $key, $default = '' ) {
    return smartwoo_get_param( $key, $default, $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Retrieve and sanitize the value(s) of a POST key, with automatic type detection.
 *
 * @param string $key     The key to retrieve from the query parameters.
 * @param mixed  $default The default value to return if the key is not found.
 * @return mixed The sanitized value of the query parameter, or the default value.
 */
function smartwoo_get_post_param( $key, $default = '' ) {
    return smartwoo_get_param( $key, $default, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Retrieve and sanitize a parameter from a given source array, with automatic type detection.
 *
 * This function supports automatic type detection and sanitization for common data types:
 * - Arrays: sanitized recursively with `sanitize_text_field()`.
 * - Numeric strings: cast to `int` or `float`.
 * - Boolean-like values: evaluated with `FILTER_VALIDATE_BOOLEAN` (supports "true", "false", "1", "0", "yes", "no").
 * - Email addresses: validated and sanitized with `sanitize_email()`.
 * - URLs: validated with `FILTER_VALIDATE_URL` and sanitized with `esc_url_raw()`.
 * - All other strings: sanitized with `sanitize_text_field()`.
 *
 * @param string $key     The key to retrieve from the source array.
 * @param mixed  $default Optional. The default value to return if the key is not set. Default ''.
 * @param array  $source  Optional. The source array to read from (e.g., $_GET or $_POST). Default empty array.
 *
 * @return mixed The sanitized value if found, or the default value if the key is not present.
 */
function smartwoo_get_param( $key, $default = '', $source = array() ) {
    if ( ! isset( $source[ $key ] ) ) {
        return $default;
    }

    $value = wp_unslash( $source[ $key ] );

    if ( is_array( $value ) ) {
        return array_map( 'sanitize_text_field', $value );
    }

    if ( is_numeric( $value ) ) {
        return ( strpos( $value, '.' ) !== false ) ? floatval( $value ) : intval( $value );
    }

    $lower = strtolower( $value );
    if ( in_array( $lower, [ 'true', 'false', '1', '0', 'yes', 'no' ], true ) ) {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
    }

    if ( is_email( $value ) ) {
        return sanitize_email( $value );
    }

    if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
        return esc_url_raw( $value );
    }

    return sanitize_text_field( $value );
}
/**
 * Render a help tooltip
 * 
 * @param string $message
 * @param bool $echo Whether to echo or return.
 */
function smartwoo_help_tooltip( $message, $echo = true ) {
	$helptab = '<span class="smartwoo-tooltip" data-title="'. esc_attr( $message ) . '">?</span>';

	if ( $echo ) {
		echo wp_kses_post( $helptab );
	} else{
		return $helptab;
	}
}

/**
 * Enqueue the css and javascript files for the asset media player
 */
function smartwoo_enqueue_media_assets() {
	SmartWoo_Config::enqueue_asset_editor();
}

/**
 * Get a user's payment options.
 *
 * @param int $user_id
 * @return array
 */
function smartwoo_get_user_payment_options( $user_id ) {
    $defaults = array(
        'primary' => '',
        'backup'  => '',
    );

    $stored = get_user_meta( $user_id, '_smartwoo_payment_options', true );

    if ( ! is_array( $stored ) ) {
        return $defaults;
    }

    return wp_parse_args( $stored, $defaults );
}

/**
 * Parses a user agent string and returns a short description.
 *
 * @param string $user_agent_string The user agent string to parse.
 * @return string Description in the format: "Browser Version on OS (Device)".
 */
function smartwoo_parse_user_agent( $user_agent_string ) {
    $info = array(
        'browser' => 'Unknown Browser',
        'version' => '',
        'os'      => 'Unknown OS',
        'device'  => 'Desktop',
    );

    // Detect OS
    if ( preg_match( '/Windows NT ([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $os_version = $matches[1];
        $info['os'] = match ( $os_version ) {
            '10.0' => 'Windows 10',
            '6.3'  => 'Windows 8.1',
            '6.2'  => 'Windows 8',
            '6.1'  => 'Windows 7',
            '6.0'  => 'Windows Vista',
            '5.1'  => 'Windows XP',
            default => 'Windows ' . $os_version
        };
    } elseif ( preg_match( '/Mac OS X ([0-9_.]+)/i', $user_agent_string, $matches ) ) {
        $info['os'] = 'macOS ' . str_replace( '_', '.', $matches[1] );
    } elseif ( preg_match( '/Linux/i', $user_agent_string ) && ! preg_match( '/Android/i', $user_agent_string ) ) {
        $info['os'] = 'Linux';
    } elseif ( preg_match( '/Android ([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['os']     = 'Android ' . $matches[1];
        $info['device'] = 'Mobile';
    } elseif ( preg_match( '/iPhone|iPad|iPod/i', $user_agent_string, $matches ) ) {
        $info['os']     = 'iOS';
        $info['device'] = ( 'iPad' === $matches[0] ) ? 'Tablet' : 'Mobile';
    }

    // Detect device type
    if ( preg_match( '/BlackBerry|Mobile Safari|Opera Mini|Opera Mobi|Firefox Mobile|webOS|NokiaBrowser|Series40|NintendoBrowser/i', $user_agent_string ) ) {
        $info['device'] = 'Mobile';
    } elseif ( preg_match( '/Tablet|iPad|Nexus 7|Nexus 10|GT-P|SM-T/i', $user_agent_string ) ) {
        $info['device'] = 'Tablet';
    }

    // Detect browser & version
    if ( preg_match( '/Edg\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        // New Chromium-based Edge
        $info['browser'] = 'Edge';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/Edge\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        // Legacy Edge
        $info['browser'] = 'Edge';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/(OPR|Opera)\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Opera';
        $info['version'] = $matches[2];
    } elseif ( preg_match( '/CriOS\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Chrome iOS';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/Chrome\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Chrome';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/Firefox\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Firefox';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/Safari\/([0-9.]+)/i', $user_agent_string, $matches ) && ! preg_match( '/Chrome|Edg/i', $user_agent_string ) ) {
        $info['browser'] = 'Safari';
        if ( preg_match( '/Version\/([0-9.]+)/i', $user_agent_string, $version_matches ) ) {
            $info['version'] = $version_matches[1];
        } else {
            $info['version'] = $matches[1];
        }
    } elseif ( preg_match( '/MSIE ([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Internet Explorer';
        $info['version'] = $matches[1];
    } elseif ( preg_match( '/Trident\/([0-9.]+)/i', $user_agent_string, $matches ) ) {
        $info['browser'] = 'Internet Explorer';
        $info['version'] = ( '7.0' === $matches[1] ) ? '11.0' : 'Unknown IE';
    }

    // Build the return string
    return trim( sprintf(
        '%s%s on %s (%s)',
        $info['browser'],
        $info['version'] ? ' ' . $info['version'] : '',
        $info['os'],
        $info['device']
    ) );
}
