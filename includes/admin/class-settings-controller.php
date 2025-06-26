<?php
/**
 * The Smart Woo settings controller class file
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevents direct access.

/**
 * The Settings page, template and form controller
 */
class SmartWoo_Settings_Controller {
	/**
	 * Admin settings menu controller
	 */
	public static function menu_controller() {
		$tab = smartwoo_get_query_param( 'tab' );
		self::print_header();
		switch ( $tab ) {
			case 'business':
				self::business_option_page();
				break;

			case 'invoicing':
				self::invoice_options();
				break;

			case 'emails':
				self::email_options();
				break;

			case 'advanced':
				self::advanced_options();
				break;

			default:
			if ( has_action( 'smartwoo_options_' . $tab . '_content' ) ) {
				do_action( 'smartwoo_options_' . $tab . '_content' );
				
			} else {
				self::knowledge_base();
			}
		}
	}

	/**
	 * Print navigation header
	 */
	private static function print_header() {
		$tabs = array(
			'General'	=> array(
				'href'		=> admin_url( 'admin.php?page=sw-options' ),
				'active'	=> ''
			),
			'Business'  => array(
				'href'		=> admin_url( 'admin.php?page=sw-options&tab=business' ),
				'active'	=> 'business'
			),
			'Invoices' => array(
				'href'	=> admin_url( 'admin.php?page=sw-options&tab=invoicing' ),
				'active'	=> 'invoicing'
			),
			'Emails'    => array(
				'href'	=> admin_url( 'admin.php?page=sw-options&tab=emails' ),
				'active'	=> 'emails'
			),
			'Advanced'  => array(
				'href'	=> admin_url( 'admin.php?page=sw-options&tab=advanced' ),
				'active'	=> 'advanced'
			),

		);

		$menus = apply_filters( 'smartwoo_options_tab', $tabs );

		$title		= ! empty( smartwoo_get_query_param( 'tab' ) ) ? 'Settings' : 'Knowledge Base';
		SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $menus, 'tab' );
	
	}

	/**
	 * Smart Woo knowledge base page where users can find a brief but detailed guide on how to use the plugin.
	 */
	private static function knowledge_base() {
		$settings = array(
			'smartwoo_business_name' => array(
				'title'			=> 'Business name',
				'description'	=> __( 'This is the business name that will be shown on invoice addresses and emails.', 'smart-woo-service-invoicing' ),
				'missing'	=> empty( get_option( 'smartwoo_business_name' ) ),
				'url'		=> admin_url( 'admin.php?sw-options&tab=business' )
			),
			
			'smartwoo_invoice_page_id' => array(
				'title'			=> 'Invoice Page',
				'description'	=> __( 'The page where all invoices for a client is listed.', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_invoice_page_id' ) ),
				'url'			=> admin_url( 'admin.php?page=sw-options&tab=invoicing' )
			),

			'smartwoo_service_page_id' => array(
				'title'			=> 'Service subscription page',
				'description'	=> __( 'The main client portal where all subscriptions for a client is listed.', 'smart-woo-service-invoicing' ),
				'missing'	=> empty( get_option( 'smartwoo_service_page_id' ) ),
				'url'		=> admin_url( 'admin.php?page=sw-options&tab=business' )
			),

			'smartwoo_admin_phone_numbers' => array(
				'title'			=> 'Business Phones',
				'description'	=> __( 'These are the busines phone number(s) that will be shown on invoice addresses and emails.', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_business_name' ) ),
				'url'			=> admin_url( 'admin.php?sw-options&tab=business' )
			),

			'smartwoo_invoice_id_prefix' => array(
				'title'			=> 'Invoice ID prefix',
				'description'	=> __( 'This is the character(s) that will be placed before all invoice IDs', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_invoice_id_prefix' ) ),
				'url'			=> admin_url( 'admin.php?page=sw-options&tab=invoicing' )
			),

			'smartwoo_service_id_prefix' => array(
				'title'			=> 'Service ID prefix',
				'description'	=> __( 'This is the character(s) that will be placed before all service subscription IDs', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_service_id_prefix' ) ),
				'url'			=> admin_url( 'admin.php?sw-options&tab=business' )
			),
		
			'smartwoo_email_sender_name' => array(
				'title'			=> 'Email sender name',
				'description'	=> __( 'This is the name that will be used to send emails to your clients.', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_email_sender_name' ) ),
				'url'			=> admin_url( 'admin.php?sw-options&tab=emails' )
			),


			'smartwoo_billing_email' => array(
				'title'			=> 'Billing Email',
				'description'	=> __( 'This is the email address that will be used to send all subscription and invoice related emails.', 'smart-woo-service-invoicing' ),
				'missing'		=> empty( get_option( 'smartwoo_billing_email' ) ),
				'url'			=> admin_url( 'admin.php?sw-options&tab=emails' )
			),

			'smartwoo_email_image_header' => array(
				'title'			=> 'Email header image',
				'description'	=> __( 'This is the image used in email template header.', 'smart-woo-service-invoicing' ),
				'missing'	=> empty( get_option( 'smartwoo_email_image_header' ) ),
				'url'		=> admin_url( 'admin.php?sw-options&tab=emails' )
			),
			
			'smartwoo_product_text_on_shop' => array(
				'title'			=> 'Product add to cart text',
				'description'	=> __( 'The "add to cart text" for all subscription products', 'smart-woo-service-invoicing' ),				
				'missing'		=> empty( get_option( 'smartwoo_product_text_on_shop' ) ),
				'url'			=> admin_url( 'admin.php?sw-options&tab=advanced' )
			),
		
		);

		$missing_settings = array();
		foreach ( $settings as $id => $data ) {
			if ( ! $data['missing'] ) {
				continue;
			}

			$missing_settings[$id] = $data;
		}


		include_once SMARTWOO_PATH . 'templates/settings/knowledge-base.php';
	}

	/**
	 * The business option settings page
	 */
	private static function business_option_page() {
		smartwoo_set_document_title( 'Business Settings' );
		self::save_options();
		$site_name             = get_bloginfo( 'name' );
		$business_name         = get_option( 'smartwoo_business_name', $site_name );
		$admin_phone_numbers   = get_option( 'smartwoo_admin_phone_numbers', '' );
		$service_page          = get_option( 'smartwoo_service_page_id', 0 );
		$pages                 = get_pages();
		$service_id_prefix     = get_option( 'smartwoo_service_id_prefix', 'SID' );

		include_once SMARTWOO_PATH . 'templates/settings/business-settings.php';
		
	}

	/**
	 * Invoice options page
	 */
	private static function invoice_options() {
		smartwoo_set_document_title( 'Invoice Settings' );
		self::save_options();
		$invoice_prefix        	= get_option( 'smartwoo_invoice_id_prefix', 'CINV' );
		$invoice_page          	= get_option( 'smartwoo_invoice_page_id', 0 );
		$pages                 	= get_pages();
		$invoice_logo_url      	= get_option( 'smartwoo_invoice_logo_url' );
		$invoice_watermark_url	= get_option( 'smartwoo_invoice_watermark_url' );
		$global_next_pay		= smartwoo_get_global_nextpay( 'edit' );

		include_once SMARTWOO_PATH . 'templates/settings/invoice-settings.php';
	}

	/**
	 * Email options page
	 */
	private static function email_options() {
		$action = smartwoo_get_query_param( 'section' );
		$action = 'smartwoo_email_option_' . $action . '_section';

		if ( has_action( $action ) ) {
			do_action( $action );
			return;
		}
		
		smartwoo_set_document_title( 'Email Settings' );
		$options = self::get_email_options();
		
		self::save_email_options();
		$billing_email = get_option( 'smartwoo_billing_email' );
		$sender_name   = get_option( 'smartwoo_email_sender_name' );
		$email_image   = get_option( 'smartwoo_email_image_header' );
		$pro_installed = class_exists( 'SmartWooPro', false );

		include_once SMARTWOO_PATH . 'templates/settings/email-settings.php';
	}

	/**
	 * Advanced options page
	 */
	private static function advanced_options() {
		smartwoo_set_document_title( 'Advanced Settings' );
		self::save_advanced_options();
		$product_text		= get_option( 'smartwoo_product_text_on_shop', 'Configure' );
		$inv_footer_text	= get_option( 'smartwoo_invoice_footer_text', 'Thank you for the continued business and support. We value you so much.' );
		$fc_options			= smartwoo_fast_checkout_options();
		$options			= self::get_advaced_options();

		include_once SMARTWOO_PATH . 'templates/settings/advanced-settings.php';
	}

	/**
	 * Get all registered email options.
	 * 
	 * @return array
	 */
	private static function get_email_options() {
		$defaults = array(
			'smartwoo_new_invoice_mail'	=> array(
				'title'			=> __( 'New Invoice Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_new_service_order'	=> array(
				'title'			=> __( 'New Service Order Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Admin',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_service_processed_mail' => array(
				'title'			=> __( 'Service Processed Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_payment_reminder_to_client' => array(
				'title'			=> __( 'Payment Reminder Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_invoice_paid_mail' => array(
				'title'			=> __( 'Invoice Paid Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_renewal_mail' => array(
				'title'			=> __( 'Service Renewal Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_service_opt_out_mail' => array(
				'title'			=> __( 'Service Opt-out Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_cancellation_mail_to_user' => array(
				'title'			=> __( 'Service Cancellation Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),

			'smartwoo_service_expiration_mail' => array(
				'title'			=> __( 'Service Expiration Email', 'smart-woo-service-invoicing' ),
				'recipient'		=> 'Client',
				'previewable'	=> true,
				'editable'		=> true,
			),
			'smartwoo_service_expiration_mail_to_admin' => array(
				'title'			=> __( 'Service Expiration Email to Admin', 'smart-woo-service-invoicing' ),
				'recipient'		=> get_option( 'smartwoo_billing_email', '' ),
				'previewable'	=> true,
				'editable'		=> false,
			),
			'smartwoo_service_cancellation_mail_to_admin' => array(
				'title'			=> __( 'Service Cancellation Email to Admin', 'smart-woo-service-invoicing' ),
				'recipient'		=> get_option( 'smartwoo_billing_email', 'Admin' ),
				'previewable'	=> true,
				'editable'		=> false,
			),
		);

		$registered_options = apply_filters( 'smartwoo_email_options', $defaults );

		return $registered_options;
	}

	/**
	 * Get all registered advanced options.
	 * 
	 * @return array
	 */
	private static function get_advaced_options() {
		return apply_filters( 'smartwoo_advanced_options',
			array(
				'smartwoo_allow_fast_checkout',
				'smartwoo_allow_optout/Cancellation',
				'smartwoo_allow_invoice_tracking',
				'smartwoo_remove_plugin_data_during_uninstall'
			)
		);
	}

	/**
	 * Save advanced options
	 */
	private static function save_advanced_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sw_save_options'], $_POST['sw_option_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {

			if ( isset( $_POST['smartwoo_product_text_on_shop'] ) ) {
				$value =  ! empty( $_POST['smartwoo_product_text_on_shop'] ) ?  sanitize_text_field( wp_unslash( $_POST['smartwoo_product_text_on_shop'] ) ) : 'Configure';
				update_option( 'smartwoo_product_text_on_shop', $value );
			}

			if ( isset( $_POST['smartwoo_invoice_footer_text'] ) ) {
				$value =  ! empty( $_POST['smartwoo_invoice_footer_text'] ) ?  sanitize_text_field( wp_unslash( $_POST['smartwoo_invoice_footer_text'] ) ) : '';
				update_option( 'smartwoo_invoice_footer_text', $value );
			}

			$checkboxes = self::get_advaced_options();

			// Update checkbox options.
			foreach ( $checkboxes as $checkbox_name ) {
				if ( isset( $_POST[ $checkbox_name ] ) ) {
					update_option( $checkbox_name, 1  ); 
				} else {
					update_option( $checkbox_name, 0 ); 
				}
			}

			$fc_options			= array_intersect_key( $_POST, smartwoo_fast_checkout_options() );
			$sanitized_options	= array();
			
			foreach( $fc_options as $key => $value ) {
				if ( in_array( $key, ['modal_background_color', 'title_color', 'button_background_color', 'button_text_color'] ) ) {
					$sanitized_options[ $key ] = sanitize_hex_color( wp_unslash( $value ) );
				} else {
					$sanitized_options[ $key ] = sanitize_text_field( wp_unslash( $value ) );
				}
			}

			$fc_final_options = wp_parse_args( $sanitized_options, smartwoo_fast_checkout_options() );
			update_option( 'smartwoo_fast_checkout_options', $fc_final_options );

			echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>Settings saved!</p></div>' );

		}
	}

	/**
	 * Save business and invoice options
	 */
	private static function save_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sw_save_options'], $_POST['sw_option_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sw_option_nonce'] ) ), 'sw_option_nonce' ) ) {

			if ( isset( $_POST['smartwoo_invoice_page_id'] ) ) {
				update_option( 'smartwoo_invoice_page_id', absint( $_POST['smartwoo_invoice_page_id'] ) );
			}

			if ( isset( $_POST['smartwoo_invoice_logo_url'] ) ) {
				update_option( 'smartwoo_invoice_logo_url', sanitize_url( wp_unslash( $_POST['smartwoo_invoice_logo_url'] ), array( 'http', 'https' ) ) );
			}

			if ( isset( $_POST['smartwoo_invoice_watermark_url'] ) ) {
				update_option( 'smartwoo_invoice_watermark_url', sanitize_url( wp_unslash( $_POST['smartwoo_invoice_watermark_url'] ), array( 'http', 'https' ) ) );
			}

			if ( isset( $_POST['smartwoo_business_name'] ) ) {
				$business_name = isset( $_POST['smartwoo_business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['smartwoo_business_name'] ) ) : get_bloginfo( 'name' );
				update_option( 'smartwoo_business_name', sanitize_text_field( $business_name ) );
			}

			if ( isset( $_POST['smartwoo_admin_phone_numbers'] ) ) {
				// Remove any characters except numbers and commas.
				$phone_numbers       = preg_replace( '/[^0-9+,]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_admin_phone_numbers'] ) ) );
				$phone_numbers_array = explode( ',', $phone_numbers );
				$phone_numbers_array = array_filter( $phone_numbers_array );
				
				// Rearrange the phone numbers into a valid format.
				$formatted_phone_numbers = implode( ', ', $phone_numbers_array );
				
				// Update the option with the rearranged phone numbers.
				update_option( 'smartwoo_admin_phone_numbers', sanitize_text_field( $formatted_phone_numbers ) );
			}
			

			if ( isset( $_POST['smartwoo_service_page_id'] ) ) {
				update_option( 'smartwoo_service_page_id', absint( $_POST['smartwoo_service_page_id'] ) );
			}

			if ( isset( $_POST['smartwoo_prorate'] ) ) {
				$smartwoo_prorate_value = ( 'Enable' === sanitize_text_field( wp_unslash( $_POST['smartwoo_prorate'] ) ) ) ? 'Enable': 'Disable';
				update_option( 'smartwoo_prorate', $smartwoo_prorate_value );
			}

			if ( isset( $_POST['smartwoo_invoice_id_prefix'] ) ) {
				$invoice_number_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_invoice_id_prefix'] ) ) );
				update_option( 'smartwoo_invoice_id_prefix', $invoice_number_prefix );
			}

			if ( isset( $_POST['smartwoo_service_id_prefix'] ) ) {
				$service_id_prefix = preg_replace( '/[^a-zA-Z0-9]/', '', sanitize_text_field( wp_unslash( $_POST['smartwoo_service_id_prefix'] ) ) );
				update_option( 'smartwoo_service_id_prefix', $service_id_prefix );
			}

			if ( isset( $_POST['smartwoo_allow_migration'] ) ) {
				$smartwoo_allow_migration = ( 'Enable' === $_POST['smartwoo_allow_migration'] ) ? 'Enable' : 'Disable';
				update_option( 'smartwoo_allow_migration', $smartwoo_allow_migration );
			}

			if ( isset( $_POST['next_payment_date_operator'], $_POST['next_payment_date_unit'], $_POST['next_payment_date_number'] ) ) {
				$operator	= '-' === $_POST['next_payment_date_operator'] ? '-' : '+';
				$number		= ! empty( $_POST['next_payment_date_number'] ) ? absint( $_POST['next_payment_date_number'] ) : 7; // Always default to 7 days.
				$unit		= in_array( $_POST['next_payment_date_unit'], array( 'days', 'weeks', 'months', 'years' ), true ) ? sanitize_text_field( wp_unslash( $_POST['next_payment_date_unit'] ) ) : 'days'; 
			
				$value = array( 'operator' => $operator, 'number' => $number, 'unit' => $unit );
				update_option( 'smartwoo_global_next_payment_interval', $value );
			}

			echo wp_kses_post( '<div class="notice is-dismissible"><p>' . esc_html( 'Settings saved!', 'smart-woo-service-invoicing' ) . '</p></div>' );
		}

	}

	/**
	 * Save email options
	 */
	private static function save_email_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sw_save_email_options'], $_POST['sw_email_option_nonce']  ) && wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['sw_email_option_nonce'] ) ), 'sw_email_option_nonce') ) {

			// Update billing email.
			if ( isset( $_POST['smartwoo_billing_email'] ) ) {
				update_option( 'smartwoo_billing_email', sanitize_email( wp_unslash( $_POST['smartwoo_billing_email'] ) ) );
			}

			if ( isset( $_POST['smartwoo_email_image_header'] ) ) {
				update_option( 'smartwoo_email_image_header', sanitize_url( wp_unslash( $_POST['smartwoo_email_image_header'] ), array( 'http', 'https' ) ) );
			}

			if ( isset( $_POST['smartwoo_email_sender_name'] ) ) {
				update_option( 'smartwoo_email_sender_name', sanitize_text_field( wp_unslash( $_POST['smartwoo_email_sender_name'] ) ) );
			}

			$checkboxes = array_keys( self::get_email_options() );

			foreach ( $checkboxes as $checkbox_name ) {
				if ( isset( $_POST[ $checkbox_name ] ) ) {
					update_option( $checkbox_name, 1  ); 
				} else {
					update_option( $checkbox_name, 0 ); 
				}
			}
			echo wp_kses_post( '<div class="updated notice updated is-dismissible"><p>' . esc_html__( 'Settings saved!', 'smart-woo-service-invoicing' ) . '</p></div>' );

		}

	}

}