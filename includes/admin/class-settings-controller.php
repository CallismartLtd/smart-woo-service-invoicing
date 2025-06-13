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
			'Knowledge Base'	=> array(
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

		$menu_keys	= array_keys( $menus );
		$title		= ! empty( smartwoo_get_query_param( 'tab' ) ) ? 'Settings' : 'Knowledge Base';
		SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $menus, 'tab' );
	
	}

	/**
	 * Smart Woo knowledge base page where users can find a brief but detailed guide on how to use the plugin.
	 */
	private static function knowledge_base() {

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


/**
 * Admin Settings Main page
 */
function smartwoo_options_main_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	?>
	<div class="wrap">
		<h2>Smart Woo Settings and Knowledgebase</h2>

		<div class="sw-container">
			<div class="sw-left-column">
				<h3>Quick Set-up Guides</h3>
				<ul>
					<li><a class="settings-nav" href="#general-concept">General</a></li>
					<li><a class="settings-nav" href="#step1">Step 1</a></li>
					<li><a class="settings-nav" href="#step2">Step 2</a></li>
					<li><a class="settings-nav" href="#step3">Step 3</a></li>
				</ul>
			</div>

			<div class="sw-right-column">
				<div id="first-display" class="image-section">
					<h3> Smart Woo Service Invoicing</h3>
					<img src="<?php echo esc_url( SMARTWOO_DIR_URL . 'assets/images/smart-woo-img.png' ); ?>" alt="plugin screenshot" style="width: 50%;">
					<p>Here you will find useful information to get you started.</p>
			    </div>
			
				<div id="general-concept" class="instruction">
					<h3>Introduction</h3>
					<p><strong>Smart Woo Service Invoicing integrates powerful service subscription capabilities into your website. This includes automatic invoice creation for services that are due, prompt reminders, and a host of other interesting features.</strong></p>
					<p>To get started, there are basically three steps needed to get your subscriptions up and running.</p>
				</div>


				<div id="step1" class="instruction">
				<h3>Basic Set-up</h3>
					<p><strong>Set up your business details on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ); ?>" target="_blank">business settings page</a>, and invoicing preferences on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ); ?>" target="_blank">invoicing settings page</a>.</strong></p>
					<p>You may need to create two dedicated pages to allow your clients to fully manage their services and invoices. Usually, these pages should be automatically created for you during installation. If not, create them manually and ensure that each page contains the following shortcodes: <strong>[smartwoo_service_page]</strong> for the service page and <strong>[smartwoo_invoice_page]</strong> for the invoice page.</p>
				</div>


				<div id="step2" class="instruction">
					<h3>Create Product</h3>
					<p><strong>Create a <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ); ?>" target="_blank">Service Product</a> specifically dedicated to service subscriptions, and set up the necessary fields.</strong></p>
					<p>Create and publish your services as products. When a client purchases your service, an invoice will be automatically created for them. You'll also have options to manage and set up the subscription for them.</p>
				</div>

				<div id="step3" class="instruction">
					<h3>All Done 🎉🎉</h3>
					<p><strong>Your service product is now listed on the WooCommerce product page. You can view all service orders <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ); ?>">here</a> and process them as needed.</strong></p>
					<?php 	echo wp_kses_post( smartwoo_pro_feature() ); ?>
				</div>

			</div>
		</div>
	</div>
	<?php
}


