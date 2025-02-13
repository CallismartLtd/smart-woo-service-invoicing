<?php
/**
 * Handles invoice admin page templates.
 *
 * @author      :   Callistus
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Class for handling invoice admin page templates.
 */
class SmartWoo_Invoice_Admin_Templates {
	/**
	 * Handles invoice view page.
	 */
	public static function view_invoice() {
		$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_text_field( wp_unslash( $_GET['invoice_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );
		$args       = isset( $_GET['path'] ) ? sanitize_key( $_GET['path'] ) : 'details'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query_var  =  'tab=view-invoice&invoice_id=' . $invoice_id .'&path';
		$tabs		= array(
			''					=> 'Dashboard',
			'details' 	      	=> __( 'Invoice', 'smart-woo-service-invoicing' ),
			'related-service' 	=> __('Related Service', 'smart-woo-service-invoicing' ),
			'log'             	=> __( 'Logs', 'smart-woo-service-invoicing' ),
		);

		$service = SmartWoo_Service_Database::get_service_by_id( $invoice->get_service_id() );

		switch ( $args ){
			case 'related-service':
				$page_file = SMARTWOO_PATH .'templates/invoice-admin-temp/view-related-services.php';
				break;
	
			case 'log':
				echo wp_kses_post( smartwoo_sub_menu_nav( $tabs, 'Invoice Informations','sw-invoices', $args, $query_var ) );
				$page_file = has_filter( 'smartwoo_invoice_log_template' ) ? apply_filters( 'smartwoo_invoice_log_template', '', $invoice ) :smartwoo_pro_feature_template( 'invoice logs' );
				break;
			default:
				$page_file = SMARTWOO_PATH . 'templates/invoice-admin-temp/view-invoice.php';
		}

		file_exists( $page_file) ? include_once $page_file : '';
	}

}

function smartwoo_invoice_dashboard() {

	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs = array(
		''                => __( 'Invoices', 'smart-woo-service-invoicing' ),
		'add-new-invoice' => __( 'Add New', 'smart-woo-service-invoicing' ),
	);

	$table_html		= smartwoo_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $tab, 'tab' );
	$all_invoices 	= SmartWoo_Invoice_Database::get_all_invoices();
	$all_inv_count 	= SmartWoo_Invoice_Database::count_all();
	$limit = 10;
	$total_pages = ceil( $all_inv_count / $limit );
	$page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$table_html  .= '<div class="sw-table-wrapper">';
	$table_html  .= '<h2>Invoice Dashboard</h2>';
	$table_html  .= smartwoo_count_all_invoices_by_status();

	if ( empty( $all_invoices ) ) {
		$table_html .= smartwoo_notice( 'All invoices will appear here' );
		return $table_html;
	}

	/**
	 * Invoice Table markup.
	 */
	$table_html .= '<table class="sw-table">';
	$table_html .= '<thead>';
	$table_html .= '<tr>';
	$table_html .= '<th>' . esc_html__( 'Invoice ID', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Invoice Type', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Payment Status', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Date Created', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Actions', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '</tr>';
	$table_html .= '</thead>';
	$table_html .= '<tbody>';	

	foreach ( $all_invoices as $invoice ) {
		$download_url = $invoice->download_url( 'admin' );
		$table_html .= '<tr>';
		$table_html .= '<td>' . esc_html( $invoice->get_invoice_id() ) . '</td>';
		$table_html .= '<td>' . esc_html( $invoice->get_type() ) . '</td>';
		$table_html .= '<td>' . esc_html( ucfirst( $invoice->get_status() ) ) . '</td>';
		$table_html .= '<td>' . esc_html( smartwoo_check_and_format( $invoice->get_date_created(), true ) ) . '</td>';
		$table_html .= '<td>
			<a  href="' . esc_url( smartwoo_invoice_preview_url( $invoice->get_invoice_id() ) ) . '"><button title="Preview"><span class="dashicons dashicons-visibility"></span></button></a>
			<a href="' . esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->get_invoice_id() ) ) . '"><button title="Edit Invoice"><span class="dashicons dashicons-edit"></span></button></a>
			<a href="'. esc_url( $download_url ) .'"><button title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>
			' . smartwoo_delete_invoice_button( $invoice->get_invoice_id() ) . '
			<span id="sw-delete-button" style="text-align:center;"></span>
		</td>';
		$table_html .= '</tr>';
	}

	$table_html .= '</tbody>';
	$table_html .= '</table>';
	$table_html .= '<div class="sw-pagination-buttons">';

	$table_html .= '<p>' . count( $all_invoices ) . ' item' . ( count( $all_invoices ) > 1 ? 's' : '' ) . '</p>';

	if ( $page > 1 ) {
		$prev_page = $page - 1;
		$table_html .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $prev_page ) ) . '"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>';
	}

	$table_html .= '<p>'. absint( $page ) . ' of ' . absint( $total_pages ) . '</p>';

	if ( $page < $total_pages ) {
		$next_page = $page + 1;
		$table_html .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $next_page ) ) . '"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>';
	}
	$table_html .= '</div>';

	$table_html .= '</div>';

	return $table_html;
}


/**
 * Invoice by status template
 */
function smartwoo_invoice_by_status_temp() {
	$payment_status = isset( $_GET['payment_status'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_status'] ) ) : 'pending'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$table_html  = '<div class="sw-table-wrapper">';
	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs = array(
		'dashboard'			=> __( 'Invoices', 'smart-woo-service-invoicing' ),
		'add-new-invoice'	=> __( 'Add New', 'smart-woo-service-invoicing' ),

	);

	$table_html   .= smartwoo_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $tab, 'tab' );

	smartwoo_set_document_title( ucfirst( $payment_status ) . ' Invoices' );

	if ( ! in_array( $payment_status, array( 'due', 'cancelled', 'paid', 'unpaid' ), true ) ) {
		return smartwoo_notice( 'Status Parameter cannot be manipulated!' );
	}

	$invoices 		= SmartWoo_Invoice_Database::get_invoices_by_payment_status( $payment_status );
	$all_inv_count 	= absint( SmartWoo_Invoice_Database::count_this_status( $payment_status ) );
	$limit 			= 10;
	$total_pages 	= ceil( $all_inv_count / $limit );
	$page 			= isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$table_html .= '<h1>' . __( 'Invoices by Payment Status', 'smart-woo-service-invoicing' ) . '</h1>';
	$table_html .= '<h2>Payment Status: ' . esc_html( ucfirst( $payment_status ) ) . '</h2>';
	$table_html .= smartwoo_count_all_invoices_by_status();

	if ( ! empty( $invoices ) ) {
		$table_html .= '<table class="sw-table">';
		$table_html .= '<thead>';
		$table_html .= '<tr>';
		$table_html .= '<th>' . esc_html__( 'Invoice ID', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Invoice Type', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Service ID', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Amount', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Fee', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Total', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '<th>' . esc_html__( 'Action', 'smart-woo-service-invoicing' ) . '</th>';
		$table_html .= '</tr>';
		$table_html .= '</thead>';
		$table_html .= '<tbody>';		

		foreach ( $invoices as $invoice ) {
			$table_html .= '<tr>';
			$table_html .= '<td>' . esc_html( $invoice->get_invoice_id() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->get_type() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->get_service_id() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->getAmount() ) . '</td>';
			$table_html .= '<td>' . smartwoo_price( $invoice->getFee() ) . '</td>';
			$table_html .= '<td>' . smartwoo_price( $invoice->getTotal() ) . '</td>';
			$table_html .= '<td><a class="sw-red-button" href="?page=sw-invoices&tab=view-invoice&invoice_id=' . esc_attr( $invoice->get_invoice_id() ) . '">' . __( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
			$table_html .= '</tr>';
		}

		$table_html .= '</tbody>';
		$table_html .= '</table>';
		$table_html .= '<div class="sw-pagination-buttons">';

		$table_html .= '<p>' . count( $invoices ) . ' item' . ( count( $invoices ) > 1 ? 's' : '' ) . '</p>';
	
		if ( $page > 1 ) {
			$prev_page = $page - 1;
			$table_html .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $prev_page ) ) . '"><button><span class="dashicons dashicons-arrow-left-alt2"></span></button></a>';
		}
	
		$table_html .= '<p>'. absint( $page ) . ' of ' . absint( $total_pages ) . '</p>';
	
		if ( $page < $total_pages ) {
			$next_page = $page + 1;
			$table_html .= '<a class="sw-pagination-button" href="' . esc_url( add_query_arg( 'paged', $next_page ) ) . '"><button><span class="dashicons dashicons-arrow-right-alt2"></span></button></a>';
		}
		$table_html .= '</div>';
	} else {
		$table_html .= smartwoo_notice( 'No ' . ucwords( $payment_status ) . ' invoice found');
	}

	$table_html .= '</div>';
	return $table_html;
}


/**
 * Admin counts for all invoice statuses.
 */
function smartwoo_count_all_invoices_by_status() {

	if ( ! is_admin() ) {
		return '';
	}

	// Get counts for each payment status.
	$status_counts = array(
		'paid'      => SmartWoo_Invoice_Database::count_this_status( 'paid' ),
		'unpaid'    => SmartWoo_Invoice_Database::count_this_status( 'unpaid' ),
		'cancelled' => SmartWoo_Invoice_Database::count_this_status( 'cancelled' ),
		'due'       => SmartWoo_Invoice_Database::count_this_status( 'due' ),
	);

	// Generate the HTML with links.
	$output = '<div class="invoice-status-counts">';
	foreach ( $status_counts as $status => $count ) {
		$url     = admin_url( 'admin.php?page=sw-invoices&tab=invoice-by-status&payment_status=' . $status );
		$output .= '<div class="sw-admin-status-item">';
		$output .= '<h2><a href="' . esc_url( $url ) . '">' . ucfirst( $status ) . ' <small>' . $count . '</small></a></h2>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}