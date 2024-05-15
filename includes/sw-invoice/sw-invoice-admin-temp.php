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
	$options = array(
		''                          => __( 'Select Invoice Type', 'smart-woo-service-invoicing' ),
		'New Service Invoice'       => __( 'New Service Invoice', 'smart-woo-service-invoicing' ),
		'Service Renewal Invoice'   => __( 'Service Renewal Invoice', 'smart-woo-service-invoicing' ),
		'Service Upgrade Invoice'   => __( 'Service Upgrade Invoice', 'smart-woo-service-invoicing' ),
		'Service Downgrade Invoice' => __( 'Service Downgrade Invoice', 'smart-woo-service-invoicing' ),
	);

	/**
	 * Option to allow others to add their invoice type using filter
	 *
	 * @param string sw_invoice_type_options        The target filter name
	 * @param array  add an associative array of the custom invoice type
	 */
	$custom_options = apply_filters( 'smartwoo_invoice_type', array() );
	$options 		= array_merge( $options, $custom_options );

	$dropdown = '<select class="sw-form-input" name="invoice_type">';
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
	// Default options.
	$options = array(
		''			=> __( 'Select Payment Status', 'smart-woo-service-invoicing' ),
		'paid'		=> __( 'Paid', 'smart-woo-service-invoicing' ),
		'unpaid'	=> __( 'Unpaid', 'smart-woo-service-invoicing' ),
		'due'		=> __( 'Due', 'smart-woo-service-invoicing' ),
		'cancelled'	=> __( 'Cancel', 'smart-woo-service-invoicing' ),
	);

	/**
	 * Option to allow others to add their invoice payment status using filter.
	 *
	 * @param string sw_invoice_payment_status        The target filter name.
	 * @param array  add an associative array of the custom invoice payment status.
	 */
	$custom_options = apply_filters( 'smartwoo_payment_status', array() );

	// Merge default and custom options
	$options = array_merge( $options, $custom_options );

	// Output the dropdown HTML
	$dropdown = '<select class="sw-form-input" name="payment_status">';
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
	$dropdown_html = '<select class="sw-form-input" name="product_id" ' . ( $required ? 'required' : '' ) . '>';
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
 * Invoice creation form
 */
function smartwoo_new_invoice_form() {

	/**
	 * Capture output buffer and return appropraitely.
	 */
	ob_start();
	?>
	<div class="sw-form-container">
	<form method="post" action="">

		<?php
		// Add nonce for added security
		wp_nonce_field( 'sw_create_invoice_nonce', 'sw_create_invoice_nonce' );
		?>
		<!-- Choose a Client -->
		<div class="sw-form-row">
		<label for="user_id" class="sw-form-label">Choose a Client *</label>
		<span class="sw-field-description" title="Choose a user from WordPress.(required)">?</span>
		<?php
		// WordPress User Dropdown
		wp_dropdown_users(
			array(
				'name'             => 'user_id',
				'show_option_none' => 'Select User',
				'class'            => 'sw-form-input',
			)
		);
		?>
		</div>

			<!-- Service Products -->
		<div class="sw-form-row">
			<label for="service_products" class="sw-form-label">Add Product *</label>
			<span class="sw-field-description" title="Select one product. This product price and fees will be used to create next invoice. Only Service Products will appear here.">?</span>
			<?php
			// Custom Function: Dropdown for Service Products
			smartwoo_product_dropdown();
			?>
		</div>

		<!-- Invoice Type -->
		<div class="sw-form-row">
			<label for="service_type" class="sw-form-label">Invoice Type *</label>
			<span class="sw-field-description" title="Enter the service type (optional)">?</span>
			<?php
			smartwoo_invoice_type_dropdown();
			?>
		</div>

		<!-- Service ID-->
		<div class="sw-form-row">
			<label for="service_id" class="sw-form-label">Service ID (optional)</label>
			<span class="sw-field-description" title="associate this invoice with service.">?</span>
			<input type="text" class="sw-form-input" name="service_id" id="service_id">
		</div>

		<!-- Fee -->
		<div class="sw-form-row">
			<label for="fee" class="sw-form-label">Fee (optional)</label>
			<span class="sw-field-description" title="charge a fee for the invoice">?</span>
			<input type="number" class="sw-form-input" name="fee" id="fee" step="0.01">
		</div>

		<!-- Payment status -->
		<div class="sw-form-row">
			<label for="payment_status" class="sw-form-label">Payment Status *</label>
			<span class="sw-field-description" title="Choose a payment status. If the status is unpaid, a new order will be created.">?</span>
			<?php
			smartwoo_invoice_payment_status_dropdown();
			?>
		</div>

		<!-- Input field for Due Date -->
		<div class="sw-form-row">
		<label for="due_date" class="sw-form-label">Date Due *</label>
		<span class="sw-field-description" title="Choose the date due.">?</span>
		<input type="datetime-local" class="sw-form-input" name="due_date" id="due_date">
		</div>

		<input type="submit" class="sw-blue-button" name ="create_invoice" value="Create Invoice">
	</form>
	</div>
	<?php
	return ob_get_clean();
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
		<?php
		// WordPress User Dropdown
		wp_dropdown_users(
			array(
				'name'             => 'user_id',
				'show_option_none' => esc_html__( 'Select User', 'smart-woo-service-invoicing' ),
				'selected'         => $existingInvoice->getUserId(),
				'class'            => 'sw-form-input',
			)
		);
		?>
		</div>

		<!-- Service Products -->
		<div class="sw-form-row">
		<label for="service_products" class="sw-form-label"><?php esc_html_e( 'Service Products:', 'smart-woo-service-invoicing' ); ?></label>
		<span class="sw-field-description" title="<?php esc_attr_e( 'Edit product. This product price and fees will be used to create next invoice. Only Service Products will appear here.', 'smart-woo-service-invoicing' ); ?>">?</span>
		<?php
		// Custom Function: Dropdown for Service Products
	    smartwoo_product_dropdown( $existingInvoice->getProductId() );
		?>
		</div>

		<!-- Service Type -->
		<div class="sw-form-row">
			<label for="service_type" class="sw-form-label"><?php esc_html_e( 'Invoice Type', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Enter the service type (optional)', 'smart-woo-service-invoicing' ); ?>">?</span>
		<?php
		smartwoo_invoice_type_dropdown( $existingInvoice->getInvoiceType() );
		?>
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

		<!-- Payment status -->
		<div class="sw-form-row">
			<label for="payment_status" class="sw-form-label"><?php esc_html_e( 'Payment Status', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose a payment status. If the status is unpaid, a new order will be created.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<?php
			smartwoo_invoice_payment_status_dropdown( $existingInvoice->getPaymentStatus() );
			?>
		</div>

		<!-- Input field for Due Date -->
		<div class="sw-form-row">
			<label for="due_date" class="sw-form-label"><?php esc_html_e( 'Date Due', 'smart-woo-service-invoicing' ); ?></label>
			<span class="sw-field-description" title="<?php esc_attr_e( 'Choose the date due.', 'smart-woo-service-invoicing' ); ?>">?</span>
			<input type="datetime-local" class="sw-form-input" name="due_date" id="due_date" value="<?php echo esc_attr( $existingInvoice->getDateDue() ); ?>">
		</div>


		<input type="submit" class="sw-blue-button" name="sw_update_invoice" value="Update Invoice">
	</form>
	<?php
	return ob_get_clean();
}


/**
 * Dropdown for Invoice ID with filter for custom options.
 *
 * @param string $selected_invoice_id The selected invoice ID (optional).
 *
 * @since 1.0.0
 */
function smartwoo_invoice_id_dropdown( $selected_invoice_id = null, $echo = true ) {
    // Fetch invoice IDs from database.
    $invoices = SmartWoo_Invoice_Database::get_all_invoices();
    if ( empty( $invoices ) ) {
        // Handle case where no invoices are available
        $dropdown = '<select class="sw-form-input" name="invoice_id">';
        $dropdown .= '<option value="" selected="selected">' . __( 'No invoices available', 'smart-woo-service-invoicing' ) . '</option>';
        $dropdown .= '</select>';

        if ( true === $echo ) {
            echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
        }

        return $dropdown;
    }

    // Output the dropdown HTML.
    $dropdown  = '<select class="sw-form-input" name="invoice_id">';
    $dropdown .= '<option value="" selected="selected">' . __( 'Select Invoice ID', 'smart-woo-service-invoicing' ) . '</option>';

    foreach ( $invoices as $invoice ) {
        // Check if the method to get the invoice ID exists.
        if ( method_exists( $invoice, 'getInvoiceId' ) ) {
            $invoice_id  = $invoice->getInvoiceId();
            $is_selected = ( $invoice_id === $selected_invoice_id ) ? 'selected="selected"' : '';
            $dropdown   .= "<option value='$invoice_id' $is_selected>$invoice_id</option>";
        }
    }

    $dropdown .= '</select>';
    if ( true === $echo ) {
        echo wp_kses( $dropdown, smartwoo_allowed_form_html() );
    }

    return $dropdown;
}



/**
 * Invoice Adnin main page.
 */

// Default function for the "Invoices" dashboard
function smartwoo_invoice_dashboard() {

	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs = array(
		''                => __( 'Invoices', 'smart-woo-service-invoicing' ),
		'add-new-invoice' => __( 'Add New', 'smart-woo-service-invoicing' ),

	);

	$table_html   = smartwoo_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $tab, 'tab' );
	$all_invoices = SmartWoo_Invoice_Database::get_all_invoices();
	$table_html  .= '<div class="sw-table-wrapper">';
	$table_html  .= '<h2>Invoice Dashboard</h2>';
	$table_html  .= smartwoo_count_all_invoices_by_status();

	if ( empty( $all_invoices ) ) {
		$table_html .= smartwoo_notice( 'All invoices will appear here' );
		return $table_html;
	}

	/**
	 * Invoice Table markeup.
	 */
	$table_html .= '<table class="sw-table">';
	$table_html .= '<thead>';
	$table_html .= '<tr>';
	$table_html .= '<th>' . esc_html__( 'Invoice ID', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Invoice Type', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Payment Status', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Date Created', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '<th>' . esc_html__( 'Action', 'smart-woo-service-invoicing' ) . '</th>';
	$table_html .= '</tr>';
	$table_html .= '</thead>';
	$table_html .= '<tbody>';	

	foreach ( $all_invoices as $invoice ) {
		$table_html .= '<tr>';
		$table_html .= '<td>' . esc_html( $invoice->getInvoiceId() ) . '</td>';
		$table_html .= '<td>' . esc_html( $invoice->getInvoiceType() ) . '</td>';
		$table_html .= '<td>' . esc_html( ucfirst( $invoice->getPaymentStatus() ) ) . '</td>';
		$table_html .= '<td>' . esc_html( smartwoo_check_and_format( $invoice->getDateCreated(), true ) ) . '</td>';
		$table_html .= '<td><a class="sw-red-button" href="' . esc_url( add_query_arg( array( 'page' => 'sw-invoices', 'tab' => 'view-invoice', 'invoice_id' => $invoice->getInvoiceId() ), admin_url( 'admin.php' ) ) ) . '">' . esc_html__( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
		$table_html .= '</tr>';
	}

	$table_html .= '</tbody>';
	$table_html .= '</table>';
	$table_html .= '<p class="sw-table-count">' . count( $all_invoices ) . ' items</p>';
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
		'details' 	      => __( 'Invoice', 'smart-woo-service-invoicing' ),
		'related-service' => __('Related Service', 'smart-woo-service-invoicing' ),
		'log'             => __( 'Logs', 'smart-woo-service-invoicing' )
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
	$page_html  = '<h2>Invoice Details</h2>';
	$page_html .= '<div class="invoice-details">';

	$user_full_name = get_user_meta( $invoice->getUserId(), 'first_name', true ) . ' ' . get_user_meta( $invoice->getUserId(), 'last_name', true );
	$product_name   = wc_get_product( $invoice->getProductId() ) ? wc_get_product( $invoice->getProductId() )->get_name() : 'Not Available';
	$paymentStatus  = esc_html( $invoice->getPaymentStatus() );
	$dateCreated    = $invoice->getDateCreated();
	$datePaid       = $invoice->getDatePaid();
	$dateDue        = $invoice->getDateDue();
	// Format the dates or display 'Not Available'
	$formattedDateCreated = smartwoo_check_and_format( $dateCreated, true );
	$formattedDatePaid    = smartwoo_check_and_format( $datePaid, true );
	$formattedDateDue     = smartwoo_check_and_format( $dateDue, true );
	/**
	 * Start page markup.
	 */
	$page_html .= '<h2>' . esc_html( $invoice->getInvoiceType() ) . '</h2>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Payment Status:', 'smart-woo-service-invoicing' ) . '</span> <span style="background-color: red; color: white; font-weight: bold; padding: 4px; border-radius: 4px;">' . esc_html__( ucfirst( $paymentStatus ), 'smart-woo-service-invoicing' ) . '</span></p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Invoice ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getInvoiceId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'User Name:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $user_full_name ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Product Name:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $product_name ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Amount:', 'smart-woo-service-invoicing' ) . '</span>' . wc_price( $invoice->getAmount() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Invoice Type:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getInvoiceType() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Service ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getServiceId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Fee:', 'smart-woo-service-invoicing' ) . '</span>' . wc_price( $invoice->getFee() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Order ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getOrderId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Payment Gateway:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getPaymentGateway() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Transaction ID:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getTransactionId() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Created:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDateCreated ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Paid:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDatePaid ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Date Due:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $formattedDateDue ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Total:', 'smart-woo-service-invoicing' ) . '</span>' . wc_price( $invoice->getTotal() ) . '</p>';
	$page_html .= '<p class="smartwoo-container-item"><span>' . esc_html__( 'Billing Address:', 'smart-woo-service-invoicing' ) . '</span>' . esc_html( $invoice->getBillingAddress() ) . '</p>';
	$page_html .= '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=sw-invoices&tab=edit-invoice&invoice_id=' . $invoice->getInvoiceId() ) ) . '">' . esc_html__( 'Edit Invoice', 'smart-woo-service-invoicing' ) . '</a>';
	$page_html .= smartwoo_delete_invoice_button( $invoice->getInvoiceId());
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
		$page_html .= '<a class="sw-blue-button" href="' . esc_url( admin_url( 'admin.php?page=sw-admin&action=view-service&service_id=' . $service_id ) ) . '">';
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
	$payment_status = isset( $_GET['payment_status'] ) ? sanitize_key( $_GET['payment_status'] ) : 'pending'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$table_html  = '<div class="sw-table-wrapper">';
	$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs = array(
		'dashboard'			=> __( 'Invoices', 'smart-woo-service-invoicing' ),
		'add-new-invoice'	=> __( 'Add New', 'smart-woo-service-invoicing' ),

	);

	$table_html   .= smartwoo_sub_menu_nav( $tabs, 'Invoice', 'sw-invoices', $tab, 'tab' );

	if( ! in_array( $payment_status, array( 'due', 'cancelled', 'paid', 'unpaid', ) ) ) {
		return smartwoo_notice( 'Status Parameter cannot be manipulated!' );
	}
	$invoices = SmartWoo_Invoice_Database::get_invoices_by_payment_status( $payment_status );

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
			$table_html .= '<td>' . wc_price( $invoice->getFee() ) . '</td>';
			$table_html .= '<td>' . wc_price( $invoice->getTotal() ) . '</td>';
			$table_html .= '<td><a class="sw-red-button" href="?page=sw-invoices&tab=view-invoice&invoice_id=' . esc_attr( $invoice->getInvoiceId() ) . '">' . __( 'View', 'smart-woo-service-invoicing' ) . '</a></td>';
			$table_html .= '</tr>';
		}

		$table_html .= '</tbody>';
		$table_html .= '</table>';
		$table_html .= '<p style="text-align: right;">' . count( $invoices ) . ' items</p>';

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
		$output .= '<div class="status-item">';
		$output .= '<h2><a class="sw-blue-button" href="' . esc_url( $url ) . '">' . ucfirst( $status ) . ' (' . $count . ')</a></h2>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}