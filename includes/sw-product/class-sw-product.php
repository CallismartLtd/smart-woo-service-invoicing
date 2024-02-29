<?php

/**
 * Register the 'sw_product' product type.
 */


class Sw_Product extends WC_Product {

	// Properties
	private $type = 'sw_product';
	private $sign_up_fee;
	private $billing_cycle;
	private $grace_period_number;
	private $grace_period_unit;

	/**
	 * Constructor.
	 *
	 * @param int $product_id The ID of the product.
	 */
	public function __construct( $product_id ) {
		parent::__construct( $product_id );

		// Set the product type
		wp_set_object_terms( $product_id, $this->type, 'product_type' );

		// Set additional properties specific to the product type
		$this->set_sign_up_fee( get_post_meta( $product_id, 'sign_up_fee', true ) );
		$this->set_billing_cycle( get_post_meta( $product_id, 'billing_cycle', true ) );
		$this->set_grace_period_number( get_post_meta( $product_id, 'grace_period_number', true ) );
		$this->set_grace_period_unit( get_post_meta( $product_id, 'grace_period_unit', true ) );
	}

	// Getter and Setter Methods
	public function get_type() {
		return $this->type;
	}

	public function get_sign_up_fee() {
		return $this->sign_up_fee;
	}

	public function set_sign_up_fee( $sign_up_fee ) {
		update_post_meta( $this->get_id(), 'sign_up_fee', $sign_up_fee );
		$this->sign_up_fee = $sign_up_fee;
	}

	public function get_billing_cycle() {
		return $this->billing_cycle;
	}

	public function set_billing_cycle( $billing_cycle ) {
		update_post_meta( $this->get_id(), 'billing_cycle', $billing_cycle );
		$this->billing_cycle = $billing_cycle;
	}

	public function get_grace_period_number() {
		return $this->grace_period_number;
	}

	public function set_grace_period_number( $grace_period_number ) {
		update_post_meta( $this->get_id(), 'grace_period_number', $grace_period_number );
		$this->grace_period_number = $grace_period_number;
	}

	public function get_grace_period_unit() {
		return $this->grace_period_unit;
	}

	public function set_grace_period_unit( $grace_period_unit ) {
		update_post_meta( $this->get_id(), 'grace_period_unit', $grace_period_unit );
		$this->grace_period_unit = $grace_period_unit;
	}

	/**
	 * Fetch and return all products of type 'sw_product'.
	 *
	 * @return array An array of Sw_Service_Product objects.
	 */
	public static function get_sw_service_products() {
		// Fetch all products of type 'sw_product'
		$products = wc_get_products(
			array(
				'type'   => 'sw_product',
				'status' => 'publish',
			)
		);

		// Initialize an array to store Sw_Service_Product objects
		$sw_service_products = array();

		// Loop through each product and create Sw_Service_Product objects
		foreach ( $products as $product ) {
			// Instantiate Sw_Service_Product and set properties
			$sw_service_product = new self( $product->get_id() );

			// Set additional properties specific to the product type
			$sw_service_product->set_sign_up_fee( get_post_meta( $product->get_id(), 'sign_up_fee', true ) );
			$sw_service_product->set_billing_cycle( get_post_meta( $product->get_id(), 'billing_cycle', true ) );
			$sw_service_product->set_grace_period_number( get_post_meta( $product->get_id(), 'grace_period_number', true ) );
			$sw_service_product->set_grace_period_unit( get_post_meta( $product->get_id(), 'grace_period_unit', true ) );

			// Add the Sw_Service_Product object to the array
			$sw_service_products[] = $sw_service_product;
		}

		return $sw_service_products;
	}

	/**
	 * Create a new Sw_Product.
	 *
	 * @param array $args Array of arguments for the new product.
	 * @return Sw_Product|WP_Error The created product object or WP_Error on failure.
	 */
	public static function create_product( $args ) {
		$defaults = array(
			'post_title'   => '',
			'post_type'    => 'product',
			'post_status'  => 'publish',
			'post_content' => '',
			'post_excerpt' => '',
			'type'         => 'sw_product',
		);

		$args = wp_parse_args( $args, $defaults );

		$product_id = wp_insert_post( $args );

		if ( ! is_wp_error( $product_id ) ) {
			// Set product type
			wp_set_object_terms( $product_id, $args['type'], 'product_type' );

			// Set regular price (main product price)
			update_post_meta( $product_id, '_regular_price', isset( $args['_regular_price'] ) ? floatval( $args['_regular_price'] ) : 0 );
			update_post_meta( $product_id, '_price', isset( $args['_price'] ) ? floatval( $args['_price'] ) : 0 );

			// Set sign-up fee (product metadata)
			$sign_up_fee = isset( $args['sign_up_fee'] ) ? floatval( $args['sign_up_fee'] ) : 0;
			update_post_meta( $product_id, 'sign_up_fee', $sign_up_fee );

			// Set billing circle (product metadata)
			$billing_cycle = isset( $args['billing_cycle'] ) ? sanitize_text_field( $args['billing_cycle'] ) : '';
			update_post_meta( $product_id, 'billing_cycle', $billing_cycle );

			// Set grace period (product metadata)
			$grace_period_number = isset( $args['grace_period_number'] ) ? intval( $args['grace_period_number'] ) : 0;
			$grace_period_unit   = isset( $args['grace_period_unit'] ) ? sanitize_text_field( $args['grace_period_unit'] ) : '';
			update_post_meta( $product_id, 'grace_period_number', $grace_period_number );
			update_post_meta( $product_id, 'grace_period_unit', $grace_period_unit );

			// Set main product image (featured image)
			$product_image_id = isset( $args['product_image_id'] ) ? absint( $args['product_image_id'] ) : 0;
			if ( $product_image_id ) {
				// Set the attached image as the featured image
				set_post_thumbnail( $product_id, $product_image_id );
			}

			// Return the created product object
			return new self( $product_id );
		} else {
			// Return WP_Error on failure
			return $product_id;
		}
	}
}
