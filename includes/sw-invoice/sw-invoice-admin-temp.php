<?php
/**
 * File name    :   sw-invoice-admin-temp.php
 *
 * @author      :   Callistus
 * Description  :   Functions file for invoice admin page templates
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Dropdown for Invoice Type with filter for custom options.
 *
 * @param string $selected The selected invoice type (optional).
 * @param bool $echo Whether to print or return output.
 *
 * @since 1.0.0
 */
function smartwoo_invoice_type_dropdown( $selected = null, $echo = true ) {
	// Default options
	$options = apply_filters( 'smartwoo_invoice_type_dropdown',
		array(
			''                          => __( 'Select Invoice Type', 'smart-woo-service-invoicing' ),
			'New Service Invoice'       => __( 'New Service Invoice', 'smart-woo-service-invoicing' ),
			'Service Renewal Invoice'   => __( 'Service Renewal Invoice', 'smart-woo-service-invoicing' ),
		)
	);

	$dropdown = '<select class="sw-form-input" name="invoice_type" id="service_type">';
	foreach ( $options as $value => $label ) {
		$is_selected = ( $value === $selected ) ? 'selected="selected"' : '';
		$dropdown   .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $is_selected ) . '>' . esc_html( $label ) . '</option>';
	}
	$dropdown .= '</select>';
	if ( true === $echo ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	}
	return $dropdown;
}




/**
 * Dropdown for Invoice Payment Status with filter for custom options.
 *
 * @param string $selected The selected invoice status (optional).
 * @param bool 	$echo		Whether or not to print to screen (Defaults to true).
 *
 * @since 1.0.0
 */
function smartwoo_invoice_payment_status_dropdown( $selected = null, $echo = true ) {
	
	$options = apply_filters( 'smartwoo_payment_status',
		array(
			''			=> __( 'Select Payment Status', 'smart-woo-service-invoicing' ),
			'paid'		=> __( 'Paid', 'smart-woo-service-invoicing' ),
			'unpaid'	=> __( 'Unpaid', 'smart-woo-service-invoicing' ),
			'due'		=> __( 'Due', 'smart-woo-service-invoicing' ),
			'cancelled'	=> __( 'Cancel', 'smart-woo-service-invoicing' ),
		)
	);

	$dropdown = '<select class="sw-form-input" name="payment_status" id="payment_status">';
	foreach ( $options as $value => $label ) {
		$is_selected = ( $value === $selected ) ? 'selected="selected"' : '';
		$dropdown .= '<option value="' . esc_attr( $value ) . '" ' . esc_attr( $is_selected ) . '>' . esc_html( $label ) . '</option>';
	}
	$dropdown .= '</select>';
	if ( true === $echo ) {
		echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
	}
	return $dropdown;
}


/**
 * Dropdown for Smart Woo Product with filter for custom options.
 *
 * @param int $product_id The selected Product ID (optional).
 *
 * @since 1.0.0
 */
function smartwoo_product_dropdown( $selected_product_id = null, $required = false, $echo = true ) {
	
	$products = wc_get_products(
		array(
			'type'   => 'sw_product',
			'status' => 'publish',
		)
	);

	// Initialize the dropdown HTML.
	$dropdown_html = '<select class="sw-form-input" name="product_id" ' . ( $required ? 'required' : '' ) . ' id="service_products">';
	$dropdown_html .= '<option value="">Select Service Product</option>';

	foreach ( $products as $product ) {
		// Get the product ID and name
		$product_id   = $product->get_id();
		$product_name = $product->get_name();

		// Check if the current product is selected
		$selected = ( $product_id == $selected_product_id ) ? 'selected' : '';

		// Add the option to the dropdown
		$dropdown_html .= '<option value="' . esc_attr( $product_id ) . '" ' . $selected . '>' . esc_html( $product_name ) . '</option>';
	}

	$dropdown_html .= '</select>';

	if ( true === $echo ) {
		echo wp_kses( $dropdown_html, smartwoo_allowed_form_html() );
	} 
	return $dropdown_html;

}

/**
 * Invoice Edit form
 */
function smartwoo_edit_invoice_form( $existingInvoice ) {

	/**
	 * Capture output buffer and return appropraitely.
	 */
	ob_start();
	?>
	<div class="sw-form-container">
	<form method="post" action="">
		<?php
		// Add nonce for added security
		wp_nonce_field( 'sw_edit_invoice_nonce', 'sw_edit_invoice_nonce' );
		?>
		<!-- Populate existing data in the form -->
		<input type="hidden" name="invoice_id" value="<?php echo esc_attr( $existingInvoice->getInvoiceId() ); ?>">
					
		<!-- Choose a Client -->
		<div class="sw-form-row">
			<label for="user_id" class="sw-form-label"><?php esc_html_e( 'Choose a Client:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose a user from WordPress.(required)', 'smart-woo-service-invoicing' ); ?>">?</span>
			
			<?php wp_dropdown_users(
				array(
					'name'             => 'user_id',
					'show_option_none' => esc_html__( 'Select User', 'smart-woo-service-invoicing' ),
					'selected'         => $existingInvoice->getUserId(),
					'class'            => 'sw-form-input',
				)
			);?>		
		</div>

		<!-- Service Products -->
		<div class="sw-form-row">
			<label for="service_products" class="sw-form-label"><?php esc_html_e( 'Service Products:', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Edit product. This product price and fees will be used to create next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php smartwoo_product_dropdown( $existingInvoice->getProductId() );?>	
		</div>

		<!-- Service Type -->
		<div class="sw-form-row">
			<label for="service_type" class="sw-form-label"><?php esc_html_e( 'Invoice Type', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php smartwoo_invoice_type_dropdown( $existingInvoice->getInvoiceType() );?>
		</div>

		<!-- Service ID -->
		<div class="sw-form-row">
			<label for="service_id" class="sw-form-label"><?php esc_html_e( 'Service ID (optional)', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'associate this invoice with service.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="text" name="service_id" class="sw-form-input" id="service_id" value="<?php echo esc_attr( $existingInvoice->getServiceId() ); ?>">
		</div>

		<!-- Fee -->
		<div class="sw-form-row">
			<label for="fee" class="sw-form-label"><?php esc_html_e( 'Fee', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'charge a fee for the invoice', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="number" name="fee" class="sw-form-input" id="fee" step="0.01" value="<?php echo esc_attr( $existingInvoice->getFee() ); ?>">
		</div>

		<?php do_action( 'smartwoo_invoice_form_item_section' ); ?>


		<!-- Payment status -->
		<div class="sw-form-row">
			<label for="payment_status" class="sw-form-label"><?php esc_html_e( 'Payment Status', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose a payment status. If the status is unpaid, a new order will be created.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php smartwoo_invoice_payment_status_dropdown( $existingInvoice->getPaymentStatus() );?>
		</div>

		<!-- Input field for Due Date -->
		<div class="sw-form-row">
			<label for="due_date" class="sw-form-label"><?php esc_html_e( 'Date Due', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the date due.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="datetime-local" class="sw-form-input" name="due_date" id="due_date" value="<?php echo esc_attr( $existingInvoice->getDateDue() ); ?>">
		</div>

		<input type="submit" class="sw-blue-button" name="sw_update_invoice" value="Update Invoice">
	</form>
	
	<?php return ob_get_clean();
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
		$table_html .= '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
		$table_html .= '<td>' . esc_html( $invoice->getInvoiceType() ) . '</td>';
		$table_html .= '<td>' . esc_html( ucfirst( $invoice->getPaymentStatus() ) ) . '</td>';
		$table_html .= '<td>' . esc_html( smartwoo_check_and_format( $invoice->getDateCreated(), true ) ) . '</td>';
		$table_html .= '<td>
			<a  href="' . esc_url( smartwoo_invoice_preview_url( $invoice->getInvoiceId() ) ) . '"><button title="Preview"><span class="dashicons dashicons-visibility"></span></button></a>
			<a href="' . esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->getInvoiceId() ) ) . '"><button title="Edit Invoice"><span class="dashicons dashicons-edit"></span></button></a>
			<a href="'. esc_url( $download_url ) .'"><button title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>
			' . smartwoo_delete_invoice_button( $invoice->getInvoiceId() ) . '
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
 * Invoice Details page(Admin).
 */
function smartwoo_view_invoice_page() {
	$invoice_id = isset( $_GET['invoice_id'] ) ? sanitize_key( $_GET['invoice_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $invoice_id ) ) {
		return smartwoo_error_notice( 'Invoice id parameter should not be manipulated', 'smart-woo-service-invoicing' ) ;
	}

	$page_html = '';
	$invoice    = SmartWoo_Invoice_Database::get_invoice_by_id( $invoice_id );

	if ( empty( $invoice ) ) {
		$page_html .= smartwoo_notice( 'Invoice not found', 'smart-woo-service-invoicing' );
		return $page_html;
	}

	$args       = isset( $_GET['path'] ) ? sanitize_key( $_GET['path'] ) : 'details'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$query_var  =  'tab=view-invoice&invoice_id=' . $invoice->getInvoiceId() .'&path';
	$tabs		= array(
		''					=> 'Dashboard',
		'details' 	      	=> __( 'Invoice', 'smart-woo-service-invoicing' ),
		'related-service' 	=> __('Related Service', 'smart-woo-service-invoicing' ),
		'log'             	=> __( 'Logs', 'smart-woo-service-invoicing' ),
	);
	$page_html .= smartwoo_sub_menu_nav( $tabs, 'Invoice Informations','sw-invoices', $args, $query_var );

	switch ( $args ){
		case 'related-service':
			$page_html .= smartwoo_invoice_service_related( $invoice );
			break;

		case 'log':
			if ( class_exists( 'SmartWooPro', false ) ) {
				$maybe_content	= apply_filters( 'smartwoo_invoice_log', array(), $invoice_id );
				foreach( (array) $maybe_content as $content ) {
					$page_html .= $content;
				}
			} else {
				$page_html .= smartwoo_pro_feature( 'invoice logs');
			}
			break;
		default:
			$page_html .= smartwoo_invoice_details_admin_temp( $invoice );
	}
	return $page_html;
}

function smartwoo_invoice_details_admin_temp( $invoice ) {
	smartwoo_set_document_title( 'Invoice Details');
	$download_url = $invoice->download_url( 'admin' );
	$page_html  = '<h1>Invoice Details</h1>';
	$page_html = '<div style="margin: 20px;">';
	$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->getInvoiceId() ) ) . '"><button title="Edit Invoice"><span class="dashicons dashicons-edit"></span></button></a>';
	// $page_html .= '<button title="Print Invoice" id="smartwoo-print-invoice-btn"><span class="dashicons dashicons-printer"></span></button>';
	$page_html .= '<a href="'. esc_url( $download_url ) .'"><button title="Download Invoice"><span class="dashicons dashicons-download"></span></button></a>';
	$page_html .= smartwoo_delete_invoice_button( $invoice->getInvoiceId() );
	$page_html .= '<span id="sw-delete-button" style="text-align:center;"></span>';
	$page_html .= '</div>';

	$page_html .= '<div class="invoice-details">';

	$user_full_name = get_user_meta( $invoice->getUserId(), 'first_name', true ) . ' ' . get_user_meta( $invoice->getUserId(), 'last_name', true );
	$product		= $invoice->get_product();
	$product_name   = $product ? $product->get_name() : 'Not Available';
	$paymentStatus  = $invoice->getPaymentStatus();
	$dateCreated    = $invoice->getDateCreated();
	$datePaid       = $invoice->getDatePaid();
	$dateDue        = $invoice->getDateDue();
	// Format the dates or display 'Not Available'
	$formattedDateCreated = smartwoo_check_and_format( $dateCreated, true );
	$formattedDatePaid    = smartwoo_check_and_format( $datePaid, true );
	$formattedDateDue     = smartwoo_check_and_format( $dateDue, true );

	$invoice_items	= apply_filters( 'smartwoo_invoice_items_display', array( 'Amount' => $invoice->getAmount(), 'Fee' => $invoice->getFee() ), $invoice );
	/**
	 * Start page markup.
	 */
	$page_html .= '<h2>' . esc_html( $invoice->getInvoiceType() ) . '</h2>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Payment Status:', 'smart-woo-service-invoicing' ) . '</span> <span style="text-align: center;background-color: red; color: white; font-weight: bold; padding: 5px; border-radius: 20px;width: 80px;">' . esc_html( ucfirst( $paymentStatus ) ) . '</span></p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Invoice ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getInvoiceId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'User Name:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $user_full_name ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Product Name:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $product_name ) . '</p>';
	
	foreach( $invoice_items as $item_name => $item_value ) {
		$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html( $item_name ) . '</span>' . esc_html( smartwoo_price( $item_value ) ) . '</p>';
	}

	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getInvoiceType() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Service ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getServiceId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Order ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getOrderId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Payment Gateway:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getPaymentGateway() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getTransactionId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Created:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDateCreated ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Paid:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDatePaid ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Due:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDateDue ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Total:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( smartwoo_price( apply_filters( 'smartwoo_display_invoice_total', $invoice->getTotal(), $invoice ) ) ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Billing Address:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getBillingAddress() ) . '</p>';
	$page_html .= '</div>';

	return $page_html;
}
function smartwoo_invoice_service_related( $invoice ){
	$service_details = SmartWoo_Service_Database::get_service_by_id( $invoice->getServiceId() );
	$page_html = '<div class="serv-details-card">';

	if ( $service_details ) {
		$service_name  = $service_details->getServiceName();
		$billing_cycle = $service_details->getBillingCycle();
		$end_date      = smartwoo_check_and_format( $service_details->getEndDate() ) ;
		$service_id    = $invoice->getServiceId();
		$page_html .= '<h3>' . esc_html__( 'Related Service Details', 'smart-woo-service-invoicing' ) . '</h3>';
		$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Service Name:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $service_name ) . '</p>';
		$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Billing Cycle:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $billing_cycle ) . '</p>';
		$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'End Date:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $end_date ) . '</p>';
		$page_html .= '<a class="sw-blue-button" href="' . esc_url( smartwoo_service_preview_url( $service_id ) ) . '">';
		$page_html .= esc_html__( 'More about Service ', 'smart-woo-service-invoicing' );
		$page_html .= '<span class="dashicons dashicons-controls-forward"></span>';
		$page_html .= '</a>';
	} else {
		$page_html .= smartwoo_notice( __( 'Not associated with any service yet.', 'smart-woo-service-invoicing' ) ); 
	}
	$page_html .= '</div>';
	return $page_html;
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
			$table_html .= '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->getInvoiceType() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->getServiceId() ) . '</td>';
			$table_html .= '<td>' . esc_html( $invoice->getAmount() ) . '</td>';
			$table_html .= '<td>' . smartwoo_price( $invoice->getFee() ) . '</td>';
			$table_html .= '<td>' . smartwoo_price( $invoice->getTotal() ) . '</td>';
			$table_html .= '<td><a class="sw-red-button" href="?page=sw-invoices&tab=view-invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '">' . __( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
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