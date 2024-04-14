<?php
// phpcs:ignoreFile

/**
 * File name    :   sw-product-admin-temp.php
 *
 * @author      :   Callistus
 * Description  :   Admin Template file
 */


// Function to display the form for adding a new product
function smartwoo_new_product_form() {

	$page_html = '<div class="wrap"><h2>Add New Service Product</h2>';

	smartwoo_process_new_product();

	echo '<div class="sw-form-container">';
	echo '<form method="post" action="" enctype="multipart/form-data" class="sw-product-form-class">';

	// Add nonce for added security
	wp_nonce_field( 'sw_add_new_product_nonce', 'sw_add_new_product_nonce' );

	// Product Name
	echo '<div class="sw-form-row">';
	echo '<label for="product_name" class="sw-form-label">Product Name</label>';
	echo '<span class="sw-field-description" title="Enter Product Name">?</span>';
	echo '<input type="text" name="product_name" class="sw-form-input" required>';
	echo '</div>';

	// Product Price
	echo '<div class="sw-form-row">';
	echo '<label for="product_price" class="sw-form-label">Product Price</label>';
	echo '<span class="sw-field-description" title="Enter Product Price">?</span>';
	echo '<input type="number" name="product_price" class="sw-form-input" step="0.01" required>';
	echo '</div>';

	// Sign-Up Fee
	echo '<div class="sw-form-row">';
	echo '<label for="sign_up_fee" class="sw-form-label">Sign-Up Fee:</label>';
	echo '<span class="sw-field-description" title="Charge Sign-up fee (optional)">?</span>';
	echo '<input type="number" name="sign_up_fee" class="sw-form-input" step="0.01">';
	echo '</div>';

	// Short Description
	ob_start();
	?>
	<div class="sw-form-row">
		<label for="short_description" class="sw-form-label">Short Description</label>
		<span class="sw-field-description" title="Enter short description for product">?</span>
		<?php
		wp_editor(
			'',
			'short_description',
			array(
				'textarea_name' => 'short_description',
				'textarea_rows' => 5,
				'teeny'         => true,
				'media_buttons' => true,
				'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ),
				'tinymce'       => array(
					'resize'                       => true,
					'browser_spellcheck'           => true,
					'paste_remove_styles'          => true,
					'paste_remove_spans'           => true,
					'paste_strip_class_attributes' => 'all',
					'paste_text_use_dialog'        => true,
					'wp_autoresize_on'             => true,
				),
			)
		);
		?>
	</div>
	<?php
	echo ob_get_clean();

	// Billing Circle
	echo '<div class="sw-form-row">';
	echo '<label for="billing_cycle" class="sw-form-label">Billing Circle:</label>';
	echo '<span class="sw-field-description" title="Set a default billing circle">?</span>';
	echo '<select name="billing_cycle" class="sw-form-input">
    <option value="" selected>Select Billing Cycle</option>
    <option value="Monthly">Monthly</option>
    <option value="Quarterly">Quarterly</option>
    <option value="Six Monthtly">Six Monthtly</option>
    <option value="Yearly">Yearly</option>
    </select>';
	echo '</div>';

	// Grace Period
	echo '<div class="sw-form-row">';
	echo '<label for="grace_period_number" class="sw-form-label">Grace Period</label>';
	echo '<div class="sw-form-input">';
	echo '<p class="description-class">A Service with this product expires after</p>';
	echo '<input type="number" name="grace_period_number" class="grace-period-number" min="1" >';
	echo '<select name="grace_period_unit" class="select-grace period-unit">
            <option value="">Select Grace Period</option>
            <option value="days">Days</option>
            <option value="weeks">Weeks</option>
            <option value="months">Months</option>
            <option value="years">Years</option>
        </select>';
	echo '</div>'; // Close the container
	echo '</div>';

	// Long Description
	ob_start();
	?>
	<div class="sw-form-row">
		<label for="long_description" class="sw-form-label">Long Description</label>
		<span class="sw-field-description" title="Enter detailed description for product">?</span>
		<?php
		wp_editor(
			'',
			'long_description',
			array(
				'textarea_name' => 'long_description',
				'textarea_rows' => 10,
				'teeny'         => false,
				'media_buttons' => true,
				'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ),
				'tinymce'       => array(
					'resize'                       => true,
					'browser_spellcheck'           => true,
					'paste_remove_styles'          => true,
					'paste_remove_spans'           => true,
					'paste_strip_class_attributes' => 'all',
					'paste_text_use_dialog'        => true,
					'wp_autoresize_on'             => true,
				),
			)
		);
		?>
	</div>
	<?php
	echo ob_get_clean();

	// Product Image
	echo '<div class="sw-form-row">';
	echo '<label for="product_image" class="sw-form-label">Product Image</label>';
	echo '<div class="sw-form-input">';
	echo '<input type="hidden" name="product_image_id" id="product_image_id" value="" class="sw-form-input">';
	echo '<div id="image_preview" class="sw-form-image-preview"></div>';
	echo '<input type="button" id="upload_image_button" class="sw-red-button" value="Upload Image">';
	echo '</div>';
	echo '</div>';

	echo '<input type="submit" name="create_sw_product" value="Create Product" class="sw-blue-button">';
	echo '</form></div></div>';
}


/**
 * Display the edit form for sw_service product.
 * 
 * @param bool $echo whether or not to render the content.
 */
function smartwoo_product_edit_form( $echo = true) {

	$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( empty( $product_id ) ) {
		echo wp_kses_post( smartwoo_error_notice( 'Product ID Parameter must not be manipulated' ) );
		return;
	}

	$page_html  	= smartwoo_process_product_edit( $product_id );
	$page_html 	   .= '<div class="wrap"><h2>Edit Service Product</h2>';
	$product_data 	=  wc_get_product( $product_id );

	if ( empty( $product_data ) ) {
		echo wp_kses_post( smartwoo_error_notice( 'You are trying to edit a product that doesn\'t exist, maybe it has been deleted' ) );
		return;
	}

	if ( ! $product_data instanceof SW_Product ) {
		echo wp_kses_post( smartwoo_error_notice( ' This is not a service product' ) );
		return;
	}

	$page_html .= '<div class="sw-form-container">';
	$page_html .= '<form method="post" action="" enctype="multipart/form-data">';
	$page_html .= '<input type="submit" name="update_service_product" class="sw-blue-button" value="Update Product">';
	$page_html .= wp_nonce_field( 'sw_edit_product_nonce', 'sw_edit_product_nonce' );
	// Product Name.
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="product_name" class="sw-form-label">Product Name</label>';
	$page_html .= '<span class="sw-field-description" title="Enter the main name of the product.">?</span>';
	$page_html .= '<input type="text" name="product_name" class="sw-form-input" value="' . esc_attr( $product_data->get_name() ) . '" required>';
	$page_html .= '</div>';
	// Product Price.
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="product_price" class="sw-form-label">Product Price</label>';
	$page_html .= '<span class="sw-field-description" title="Enter product price">?</span>';
	$page_html .= '<input type="number" name="product_price" step="0.01" class="sw-form-input" value="' . esc_attr( $product_data->get_price() ) . '" required>';
	$page_html .= '</div>';
	// Sign-up Fee.
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="sign_up_fee" class="sw-form-label">Sign-up Fee</label>';
	$page_html .= '<span class="sw-field-description" title="Charge Sign-up fee">?</span>';
	$page_html .= '<input type="number" name="sign_up_fee" step="0.01" class="sw-form-input" value="' . esc_attr( $product_data->get_sign_up_fee() ) . '">';
	$page_html .= '</div>';
	// Short Description
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="short_description" class="sw-form-label">Short Description</label>';
	$page_html .= '<span class="sw-field-description" title="Enter a brief description of the product.">?</span>';
	ob_start();
	wp_editor(
		esc_textarea( $product_data->get_short_description() ),
		'short_description',
		array(
			'textarea_name' => 'short_description',
			'textarea_rows' => 5,
			'teeny'         => true,
			'media_buttons' => false,
			'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ),
			'tinymce'       => array(
				'resize'                       => true,
				'browser_spellcheck'           => true,
				'paste_remove_styles'          => true,
				'paste_remove_spans'           => true,
				'paste_strip_class_attributes' => 'all',
				'paste_text_use_dialog'        => true,
				'wp_autoresize_on'             => true,
			),
		)
	);
	$page_html .= ob_get_clean();
	$page_html .= '</div>';
	// Billing Circle
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="billing_cycle" class="sw-form-label">Billing Circle:</label>';
	$page_html .= '<span class="sw-field-description" title="Set a default billing circle">?</span>';
	$page_html .= '<select name="billing_cycle" class="sw-form-input"> 
			<option value="" ' . selected( '', $product_data->get_billing_cycle(), false ) . '>Select Billing Cycle</option>
			<option value="Monthly" ' . selected( 'Monthly', $product_data->get_billing_cycle(), false ) . '>Monthly</option>
			<option value="Quarterly" ' . selected( 'Quarterly', $product_data->get_billing_cycle(), false) . '>Quarterly</option>
			<option value="Six Monthly" ' . selected( 'Six Monthly', $product_data->get_billing_cycle(), false ) . '>Six Monthtly</option>
			<option value="Yearly" ' . selected( 'Yearly', $product_data->get_billing_cycle(), false ) . '>Yearly</option>
	</select></div>';
	// Grace Period
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="grace_period_number" class="sw-form-label">Grace Period</label>';
	$page_html .= '<div class="sw-form-input">';
	$page_html .= '<p class="description-class">A Service with this product expires after.</p>';
	$page_html .= '<input type="number" name="grace_period_number" class="grace-period-number input-class" min="1" value="' . esc_attr( $product_data->get_grace_period_number() ) . '">';
	$page_html .= '<select name="grace_period_unit" class="select-class">';
	$page_html .= '<option value="" ' . selected( '', $product_data->get_grace_period_unit(), false ) . '>No Grace Period</option>';
	$page_html .= '<option value="days" ' . selected( 'days', $product_data->get_grace_period_unit(), false ) . '>Days</option>';
	$page_html .= '<option value="weeks" ' . selected( 'weeks', $product_data->get_grace_period_unit(), false ) . '>Weeks</option>';
	$page_html .= '<option value="months" ' . selected( 'months', $product_data->get_grace_period_unit(), false ) . '>Months</option>';
	$page_html .= '<option value="years" ' . selected( 'years', $product_data->get_grace_period_unit(), false ) . '>Years</option>';
	$page_html .= '</select>';
	$page_html .= '</div>';
	$page_html .= '</div>';
	// Description
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="long_description" class="sw-form-label">Long Description:</label>';
	$page_html .= '<span class="sw-field-description" title="Enter detailed description for product">?</span>';
	ob_start();
	wp_editor(
			esc_textarea( $product_data->get_description() ),
			'description',
			array(
				'textarea_name' => 'description',
				'textarea_rows' => 10,
				'teeny'         => false,
				'media_buttons' => true,
				'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,close' ),
				'tinymce'       => array(
					'resize'                       => true,
					'browser_spellcheck'           => true,
					'paste_remove_styles'          => true,
					'paste_remove_spans'           => true,
					'paste_strip_class_attributes' => 'all',
					'paste_text_use_dialog'        => true,
					'wp_autoresize_on'             => true,
				),
			)
		);
	$page_html .= ob_get_clean();
	$page_html .= '</div>';
	// Product Image
	$page_html .= '<div class="sw-form-row">';
	$page_html .= '<label for="product_image" class="sw-form-label">Product Image</label>';
	$page_html .= '<div class="sw-form-input">';
	$page_html .= '<input type="hidden" name="product_image_id" id="product_image_id" value="' . absint( $product_data->get_image_id() ) . '">';
	$page_html .= '<div id="image_preview" class="sw-form-image-preview"><img src="' . esc_url( wp_get_attachment_image_url( $product_data->get_image_id(), 'medium' ) ) . '" style="max-width: 250px;"></div>';
	$page_html .= '<input type="button" id="upload_image_button" class="sw-red-button" value="Upload Image">';
	$page_html .= '</div>';
	$page_html .= '</div>';
	$page_html .= '<input type="submit" name="update_service_product" class="sw-blue-button" value="Update Product">';
	$page_html .= '</form></div></div>';
	
	if ( true === $echo ){
		/**
		 * We need to render the content of this function since wp_editor cannot be run through escaping function.
		 */
		echo $page_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $page_html;
}



/**
 * Display a table of sw_service products.
 */
function smartwoo_product_table() {

	$products_data 	= Sw_Product::get_all_products();
	$page_html 		= '<div class="wrap"><h2>Service Products</h2>';

	// Check if there are any products
	if ( ! $products_data ) {
		$page_html .= '<a href="' . admin_url( 'admin.php?page=sw-products&action=add-new' ) . '" class="sw-blue-button">Add Product</a>';
		$page_html .= smartwoo_notice( 'No Service product found.' );
		return $page_html;
	}

	/**
	 * Start table markep.
	 */
	$page_html .= '<a href="' . admin_url( 'admin.php?page=sw-products&action=add-new' ) . '" class="sw-blue-button">Add Product</a>';
	$page_html .= '</div>';

	$page_html .= '<table class="sw-table">';
	$page_html .= '<thead><tr>';
	$page_html .= '<th>Product</th>';
	$page_html .= '<th>Product Price</th>';
	$page_html .= '<th>Sign Up Fee</th>';
	$page_html .= '<th>Billing Circle</th>';
	$page_html .= '<th>Action</th>';
	$page_html .= '</tr></thead>';
	$page_html .= '<tbody>';

	foreach ( $products_data as $product ) {
		$page_html .= '<tr>';
		$page_html .= '<td>' . esc_html( $product->get_name() ) . '</td>';
		$page_html .= '<td>' . wc_price( $product->get_price() ) . '</td>';
		$page_html .= '<td>' . wc_price( $product->get_sign_up_fee() ) . '</td>';
		$page_html .= '<td>' . esc_html( $product->get_billing_cycle() ) . '</td>';
		$page_html .= '<td>';
		$page_html .= '<a href="' . esc_url( admin_url( 'admin.php?page=sw-products&action=edit&product_id=' . $product->get_id() ) ) . '" class="button">Edit</a>';
		$page_html .= '<button class="sw-delete-product" data-product-id="'. esc_attr( $product->get_id() ) . '">' . __( 'Delete', 'smart-woo-service-invoicing' ) . '</button>';
		$page_html .= '</td>';
		$page_html .= '</tr>';
	}

	$page_html .= '</tbody></table>';
	$page_html .= '<p style="text-align: right;">' . count( $products_data ) . ' items</p>';

	return $page_html;
}

