<?php
/**
 * File name    :   sw-product-functions.php
 *
 * @author      :   Callistus
 * Description  :   Functions file for SmartWoo_Product
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * The text for SmartWoo_Product type in the shop page
 */

function smartwoo_product_text_on_shop() {	
	$pruduct_text = get_option( 'smartwoo_product_text_on_shop', 'Configure' );
	return apply_filters( 'smartwoo_product_text_on_shop', $pruduct_text );
}

/**
 * Get url to the admin product page endpoints.
 * 
 * @param $action The action that determines which page to load.
 * @param $product_id	The ID of product the action is being performed on.
 * @return string $page	URL of the matching page, defaults to the product dashboard.
 * @since 2.0.0
 */
function smartwoo_admin_product_url( $action = '', $product_id = 0 ) {
	$allowed_actions 	= array( 'add-new', 'edit' );
	$page				= admin_url( 'admin.php?page=sw-products' );

	if ( ! in_array( $action, $allowed_actions, true ) ) {
		return $page;
	}

	if ( 'add-new' === $action ) {
		$page = $page. '&action=' . $action;
	} elseif ( 'edit' === $action && ! empty( $product_id ) ) {
		$page = $page. '&tab=' . $action . '&product_id=' . $product_id;
	}

	return $page;
}

/**
 * Dropdown for Smart Woo Product with filter for custom options.
 *
 * @param int $product_id The selected Product ID (optional).
 *
 * @since 1.0.0
 */
function smartwoo_product_dropdown( $selected_product_id = null, $required = false ) {
	
	$products		= smartwoo_get_products( array( 'type'   => 'sw_product' ) );
	$product_data	= array();
	?>
		<select class="sw-form-input" field-name="A service product" name="product_id" <?php echo esc_attr( $required ? 'required' : '' ) ?> id="service_products">
			<option value="">Select a Product</option>
			<?php foreach ( $products as $product ) : 
				$product_id   = $product->get_id();
				$product_name = $product->get_name();
				$product_data[$product_id] = array(
					'name'			=> $product_name,
					'price'			=> $product->get_price(),
					'sign_up_fee'	=> $product->get_sign_up_fee()

				); 
				?>
				<option value="<?php echo esc_attr( $product_id ); ?>" <?php selected( $product_id, $selected_product_id ) ?>><?php echo esc_html( $product_name )?></option>
			<?php endforeach; ?>

		</select>
		<span id="product_dropdown_json" data-json="<?php echo esc_attr( wp_json_encode( $product_data ) ); ?>" style="display: none;"></span>
	<?php

}

/**
 * Get the frontend product configuration query variable.
 * 
 * @return string
 * @since 2.4.1
 */
function smartwoo_get_product_config_query_var() {
	$var = null;
	if( is_null( $var ) ) {
		$var = get_option( 'smartwoo_product_config_var', 'product-config' );
	}
	return $var;
}

/**
 * Get Smart Woo Products
 * 
 * @param array $args @see `wc_get_products`
 */
function smartwoo_get_products( array $args ) {
	$args['type']	= 'sw_product';
	return wc_get_products( $args );
}