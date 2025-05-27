<?php
/**
 * Template class file for service subscription front page.
 * 
 * @author Callistus
 * @package SmartWoo\classes
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Handles all service subscripion frontend templates methods.
 */
class SmartWoo_Service_Frontend_Template {
	/**
	 * Main service page
	 */
	public static function main_page() {	
		$current_user			= wp_get_current_user();
		$full_name				= $current_user->first_name . ' '. $current_user->last_name  ;
		$user_id				= $current_user->ID;
		$active_count			= smartwoo_count_active_services() + smartwoo_count_nr_services();
		$due_for_renewal_count	= smartwoo_count_due_for_renewal_services();
		$expired_count			= smartwoo_count_expired_services();
		$grace_period_count		= smartwoo_count_grace_period_services();
		$active_count_url		= smartwoo_get_endpoint_url( 'status', 'active' );
		$due_count_url			= smartwoo_get_endpoint_url( 'status', 'due-for-renewal' );
		$expired_count_url		= smartwoo_get_endpoint_url( 'status', 'expired' );
		$grace_count_url		= smartwoo_get_endpoint_url( 'status', 'grace-period' );
		$buy_product_page		= smartwoo_get_endpoint_url( 'buy-new' );
		
		$page					= max( 1, get_query_var( 'paged' ) );
		$limit					= isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 9;
		$all_services			= SmartWoo_Service_Database::get_services_by_user( $user_id, $page, $limit );
		$pending_services		= SmartWoo_Service_Database::get_user_awaiting_services( $user_id );
		$services				= array_merge( $all_services, $pending_services );
		
		$all_services_count		= SmartWoo_Service_Database::count_user_services( $user_id );
		$total_items_count		= count( $all_services );
		$total_pages			= ceil( $all_services_count / $limit );
		
		include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/front.php';
	}

	/**
	 * Service Subscription infomation template
	 */
	public static function sub_info() {
		$url_service_id 	= get_query_var( 'view-subscription' ) ? get_query_var( 'view-subscription' ) : get_query_var( 'smartwoo-service' );
		$service			= SmartWoo_Service_Database::get_service_by_id( $url_service_id );
		if (  $service && $service && $service->current_user_can_access() ) {
			smartwoo_set_document_title( $service->get_name() );
			$product			= $service->get_product();
			$product_name  		= $product ? $product->get_name() : 'Product Not Found';
			$GLOBALS['product'] = $product;
			$service_button    	= smartwoo_client_service_url_button( $service );
			$status        	   	= smartwoo_service_status( $service );
			$expiry_date   		= smartwoo_get_service_expiration_date( $service );
			$renew_button_text = ( 'Due for Renewal' === $status || 'Grace Period' === $status ) ? 'Renew' : 'Reactivate';

			/** 
			 * Add more buttons to the row
			 * 
			 * @param array Associative array of item => value
			 * @param SnartWoo_Service
			 */
			$buttons	= apply_filters( 'smartwoo_service_details_button_row', array(), $service );
			/**
			 * Add additional service information to the container
			 * 
			 * @param array Associative array of item => value
			 * @param SnartWoo_Service
			 */
			$additional_details = apply_filters( 'smartwoo_more_service_details', array(), $service );

		} else {
			$service = null;
		}
		
		include SMARTWOO_PATH . 'templates/frontend/subscriptions/view-subscription.php';
	}

	/**
	 * The profile catalog template
	 */
	public static function product_catalog() {
		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
		$smartwoo_products = SmartWoo_Product::get_all();

		include_once SMARTWOO_PATH . 'templates/frontend/smartwoo-products-catalog.php';
	}

	/**
	 * Sort service subscription by status
	 */
	public static function sort() {		
		$status		= get_query_var( 'status' );
		$services 	= array();
		$page		= get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$limit		= isset( $_GET['limit'] ) ? absint( $_GET['limit'] ) : 10;
		
		$status_label		= '';
		$all_services_count = 0;
		
		if ( 'active' === $status ) {
			$services	= SmartWoo_Service_Database::get_all_active( $page, $limit );
			$status_label = 'Active';
			$all_services_count = smartwoo_count_active_services();
		} elseif ( 'due-for-renewal' === $status ) {
			$services			= SmartWoo_Service_Database::get_all_due( $page, $limit );
			$status_label		= 'Due for Renewal';
			$all_services_count = smartwoo_count_due_for_renewal_services();

		} elseif ( 'grace-period' === $status ) {
			$services			= SmartWoo_Service_Database::get_all_on_grace( $page, $limit );
			$status_label		= 'Grace Period';
			$all_services_count = smartwoo_count_grace_period_services();

		} elseif ( 'expired' === $status ) {
			$services	= SmartWoo_Service_Database::get_all_expired( $page, $limit );
			$status_label = 'Expired';
			$all_services_count = smartwoo_count_expired_services();
		}

		$total_items_count	= count( $services );
		$total_pages		= ceil( $all_services_count / $limit );

		include SMARTWOO_PATH . 'templates/frontend/subscriptions/subscription-sort.php';
	}

	/**
	 * WooCommerce My Account page handler
	 */
	public static function woocommerce_myaccount_services_page() {
		if ( get_query_var( 'smartwoo-service' )  ) {
			self::sub_info();
		} else { 
			?><div class="smartwoo-page">
				<h2><?php  __( 'Services', 'smart-woo-service-invoicing' ); ?></h2>
				<?php echo wp_kses_post( smartwoo_active_service_count_shortcode() );?>
				<?php echo wp_kses_post( self::mini_card( array( 'title' => 'My Invoices', 'limit' => 8 ) ) );?>
				<div class="settings-tools-section">
					<h2>Settings and Tools</h2>
					<div id="swloader">Just a moment</div>
					<div class="sw-button-container">
						<a class="sw-blue-button" id="sw-billing-details">Billing Details</a>
						<a class="sw-blue-button" id="sw-load-user-details">My Details</a>
						<a class="sw-blue-button" id="sw-account-log">Account Logs</a>
						<a class="sw-blue-button" id="sw-load-transaction-history">Transaction History</a>
					</div>
					<div id="ajax-content-container"></div>
				</div>
			</div><?php
		}
	}

	/**
	 * Handles the rendering of the [smartwoo_service_page] shortcode
	 */
	public static function shortcode_handler() {
		global $wp_query;

		$pages			= apply_filters( 'smartwoo_subscription_pages', array() );
		$current_page	= '';
		$handler	= array( __CLASS__, 'main_page' );
		$endpoints = SmartWoo_Config::instance()->get_query_vars();

		foreach ( $endpoints as $page ) {
			if ( isset( $wp_query->query_vars[$page] ) ) {
				$current_page = $page;
				break;
			}
		}

		if ( ! empty( $current_page ) && isset( $pages[$current_page] ) ) {
			$handler = $pages[$current_page];
		}

		if ( ! is_user_logged_in() && 'buy-new' !== $current_page ) {
			$handler =  array( __CLASS__, 'login_page' );
		}

		if ( is_callable( $handler ) ) {
			ob_start();
			call_user_func( $handler );
			return ob_get_clean();
		}
	}

	/**
	 * Handles the [smartwoo_service_mini_card] shortcode
	 */
	public static function mini_card( $atts ) {

		if ( ! is_user_logged_in() ) {
			return '';
		}
		$atts = shortcode_atts( 
			array(
				'title'		=> 'My Services',                     
				'limit' 	=> 5,
			),
			$atts, 
			'smartwoo_service_mini_card'
		);

		$output				= '<div class="smartwoo-mini-card">';
		$output          	.= '<h2>' . esc_html( $atts['title'] )  . '</h2>';
		$output				.= '<hr>';
		
		$output	.= '<ul class="mini-card-content" limit="' . esc_attr( $atts['limit'] ) . '">';
		$output	.= '<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text "></span></li>';
		$output	.= '</ul>';
	
		$output .= '</div>';
		return $output;


	}

	private static function login_page() {
		wp_enqueue_style( 'dashicons' );
		$args =  array( 
			'notice' => smartwoo_notice( 'Login to access this page.' ),
			'redirect' => add_query_arg( array_map( 'sanitize_text_field', wp_unslash( $_GET ) ) )
		);
		include_once SMARTWOO_PATH . 'templates/login.php';
	}
}

/**
 * Function Code For Service Mini Card.
 */
function smartwoo_service_mini_card() {
	$current_user_id  = get_current_user_id();
	$services         = SmartWoo_Service_Database::get_services_by_user( $current_user_id );
	$output           = '<div class="mini-card">';
	$output          .= '<h2>My Services</h2>';

	if ( empty( $services ) ) {
		// Display a message if no services are found.
		$output .= '<p>All Services will appear here.</p>';
	} else {
		foreach ( $services as $service ) {
			$service_name = esc_html( $service->getServiceName() );
			$service_id   = esc_html( $service->get_service_id() );

			// Create a link to the client_services page with the service_id as a URL parameter.
			$service_link = smartwoo_service_preview_url( $service_id );
			$status       = smartwoo_service_status( $service_id );

			// Add each service name, linked row, and status with a horizontal line.
			$output .= '<p><a href="' . esc_url( $service_link ) . '">' . esc_html( $service_name ) . '</a>  ' . esc_html( $status ) . '</p>';
			$output .= '<hr>';
		}
	}

	$output .= '</div>';
	return $output;
}

/**
 * Render the count for active Service, usefull if you want to
 *  just show active service count for the logged user.
 *
 * @return int $output incremented number of active service(s) or 0 if there is none
 */
function smartwoo_active_service_count_shortcode() {	
	$current_user = wp_get_current_user();
	$user_id      = $current_user->ID;
	$count = smartwoo_count_active_services( $user_id ) + smartwoo_count_nr_services( $user_id );

	// Output the count and "Services" text with inline CSS for centering.
	$output  = '<div style="text-align: center;">';
	$output .= '<h1 class="centered" style="text-align: center; margin: 0 auto; font-size: 45px;">' . esc_html( $count ) . '</h1>';
	$output .= '<p class="centered" style="text-align: center; font-size: 18px;">' . esc_html( 'Services', 'smart-woo-service-invoicing' ) . '</p>';
	$output .= '</div>';

	return $output;

}
