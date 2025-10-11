<?php
/**
 * The Smart Woo support controller class file
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevents direct access.

/**
 * The support page, template and form controller
 */
class SmartWoo_Support_Controller {
	/**
	 * Page controller
	 */
	public static function menu_controller() {
		$tab = smartwoo_get_query_param( 'tab' );
		self::print_header();
		switch ( $tab ) {
			case 'inbox':
				self::inbox();
				break;

			case 'vip-support':
				self::vip_support();
				break;

			case 'tools':
				self::tools();
				break;

			default:
			self::overview();
			
		}
	}

	/**
	 * Print navigation header
	 */
	private static function print_header() {
		$tabs = array(
			'Overview'	=> array(
				'href'		=> admin_url( 'admin.php?page=sw-support' ),
				'active'	=> ''
			),
			'Inbox'  => array(
				'href'		=> admin_url( 'admin.php?page=sw-support&tab=inbox' ),
				'active'	=> 'inbox'
			),
			'VIP Support' => array(
				'href'	=> admin_url( 'admin.php?page=sw-support&tab=vip-support' ),
				'active'	=> 'vip-support'
			),
			'Tools'  => array(
				'href'	=> admin_url( 'admin.php?page=sw-support&tab=tools' ),
				'active'	=> 'tools'
			),

		);

		$title		= ! empty( smartwoo_get_query_param( 'tab' ) ) ? 'Support' : 'Support Overview';
		SmartWoo_Admin_Menu::print_mordern_submenu_nav( $title, $tabs, 'tab' );
	
	}

	/**
	 * Support overview page.
	 */
	private static function overview() {

		include_once SMARTWOO_PATH . 'templates/admin/support/overview.php';
	}

	/**
	 * The inbox page
	 */
	private static function inbox() {
		smartwoo_set_document_title( 'Inbox | Smart Woo' );
		

		include_once SMARTWOO_PATH . 'templates/admin/support/inbox.php';
		
	}

	/**
	 * VIP support page
	 */
	private static function vip_support() {
		smartwoo_set_document_title( 'VIP Support | Smart Woo' );
		

		include_once SMARTWOO_PATH . 'templates/admin/support/vip-support.php';
	}

	/**
	 * Tools page
	 */
	private static function tools() {
		

		include_once SMARTWOO_PATH . 'templates/admin/support/tools.php';
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
}