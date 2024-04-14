<?php

/**
 * Register the 'sw_product' product type.
 */


class Sw_Product extends WC_Product {

	// Properties.
	private $type = 'sw_product';
	private $sign_up_fee = 0;
	private $billing_cycle = '';
	private $grace_period_number = '';
	private $grace_period_unit = '';

	/**
	 * Get the product if ID is passed, otherwise the product is new and empty.
	 * This class should NOT be instantiated, but the wc_get_product() function
	 * should be used. It is possible, but the wc_get_product() is preferred.
	 *
	 * @param int|SW_Product|object $product Product to init.
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );

		if ( is_numeric( $product ) && $product > 0 ) {
			$this->set_id( $product );
		} elseif ( $product instanceof self ) {
			$this->set_id( absint( $product->get_id() ) );
		} elseif ( ! empty( $product->ID ) ) {
			$this->set_id( absint( $product->ID ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( 'product-' . $this->get_type() );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
		$this->set_sign_up_fee( $this->get_meta( '_smartwoo_sign_up_fee' ) );
		$this->set_billing_cycle( $this->get_meta( '_smartwoo_billing_cycle' ) );
		$this->set_grace_period_number( $this->get_meta( '_smartwoo_grace_period_number' ) );
		$this->set_grace_period_unit( $this->get_meta( '_smartwoo_grace_period_unit' ) );

	}

	public static function init() {
		add_filter( 'woocommerce_product_class', array( __CLASS__, 'map_product_class' ), 10, 2 );

	}

	 /**
     * Map custom product type to custom class.
     */
    public static function map_product_class( $classname, $product_type ) {
        if ( 'sw_product' === $product_type ) {
            $classname = __CLASS__;
        }
        return $classname;
		
    }


	/**********************************
	 * 		
	 * 		Getters
	 * 
	 * ******************************
	 */

	/**
	 * Get this product type.
	 */
	public function get_type() {
		return $this->type;
	}
	

	/**
	 * Get Sign Up Fee.
	 */
	public function get_sign_up_fee() {
		return $this->sign_up_fee;
	}

	/**
	 * Get the Billing cycle.
	 */
	public function get_billing_cycle() {
		return $this->billing_cycle;
	}

	/**
	 * Get the grace period number.
	 */
	public function get_grace_period_number() {
		return $this->grace_period_number;
	}

	/**
	 * Get the Grace Period unit(days, weeks, months, years).
	 */
	public function get_grace_period_unit() {
		return $this->grace_period_unit;
	}
	
	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting product data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	*/

	/**
	 * Set sign up fee.
	 * 
	 * @param float $sign_up_fee amount to charge as sign up fee.
	 */
	public function set_sign_up_fee( $sign_up_fee ) {
		$this->sign_up_fee = $sign_up_fee;
	}

	/**
	 * Set the billing cycle.
	 * 
	 * @param string $billing_cycle The billing cycle.
	 */
	public function set_billing_cycle( $billing_cycle ) {
		$this->billing_cycle = $billing_cycle;
	}

	/**
	 * Set the Grace Period number.
	 * 
	 * @param int $grace_period_number	The grace period number
	 */
	public function set_grace_period_number( $grace_period_number ) {
		$this->grace_period_number = $grace_period_number;
	}

	/**
	 * Set the grace period unit(days, weeks, months, years).
	 * @param string $grace_period_unit	the grace period units.
	 */
	public function set_grace_period_unit( $grace_period_unit ) {
		$this->grace_period_unit = $grace_period_unit;
	}
	
	/**
	 * *****************************
	 * CRUD Methods.
	 * 
	 * *****************************
	 */


	/**
	 * Retrieve all products of this class.
	 *
	 * @return object All product matching the type property of this class.
	 */
	public static function get_all_products() {

		$query    			= new WC_Product_Query( array( 'type' => 'sw_product', 'status' => 'publish' ) );
		$products 			= $query->get_products();
		$smart_woo_products = array();

		foreach ( $products as $product ) {
			$smartwoo_products = new self( $product->get_id() );
			$smartwoo_products->set_sign_up_fee( $product->get_meta( '_smartwoo_sign_up_fee' ) );
			$smartwoo_products->set_billing_cycle( $product->get_meta( '_smartwoo_billing_cycle' ) );
			$smartwoo_products->set_grace_period_number( $product->get_meta( '_smartwoo_grace_period_number' ) );
			$smartwoo_products->set_grace_period_unit( $product->get_meta( '_smartwoo_grace_period_unit' ) );

			$smart_woo_products[] = $smartwoo_products;
		}

		return $smart_woo_products;
	}

	/**
	 * Get Products of this class for migration.
	 * 
	 * @param $type The type of migration defaults to Upgrade
	 * @return object $smart_woo_products Object of Sw_Product | WC_Product.
	 */
	public static function get_migratables( $type = 'Upgrade') {
		$cat_id = ( 'Downgrade' === $type ) ? absint( get_option( 'smartwoo_downgrade_product_cat', 0 ) ) : absint( get_option( 'smartwoo_upgrade_product_cat', 0 ) );
		$term	= get_term( $cat_id );
		$slug	= $term ? $term->slug : '';
		$args	= array( 
			'type' 		=> 'sw_product', 
			'status'	=> 'publish',
			'category'	=> $slug,
		);
		$query    			= new WC_Product_Query( $args );
		$products 			= $query->get_products();
		$smart_woo_products = array();

		foreach ( $products as $product ) {
			
			$smartwoo_products = new self( $product->get_id() );
			$smartwoo_products->set_sign_up_fee( $product->get_meta( '_smartwoo_sign_up_fee' ) );
			$smartwoo_products->set_billing_cycle( $product->get_meta( '_smartwoo_billing_cycle' ) );
			$smartwoo_products->set_grace_period_number( $product->get_meta( '_smartwoo_grace_period_number' ) );
			$smartwoo_products->set_grace_period_unit( $product->get_meta( '_smartwoo_grace_period_unit' ) );

			$smart_woo_products[] = $smartwoo_products;
		}

		return $smart_woo_products;
	
	}

	/**
	 * Create a new Sw_Product.
	 *
	 * @return Sw_Product|WP_Error The created product object or WP_Error on failure.
	*/
	public function save() {
		try {
			parent::save();
		} catch ( Exception $e ) {
			$error_message = $e->getMessage();
			$error_code = $e->getCode();
			$error = new WP_Error( 'product_save_error', $error_message );
			return $error;
		}
		// If no exception occurred, return the product object.
		return $this;
	}

	/*
	|-----------------------------------------------------------------------------
	|These `add` methods should be used when creating instantiating
	|a new product of this class, should not be used when updating existing 
	|product.
	|-----------------------------------------------------------------------------
	*/

	/**
	 * Add Sign Up fee to new product.
	 * 
	 * @param float $fee The fee to add.
	 */
	public function add_sign_up_fee( $fee ) {
		$this->sign_up_fee = $this->add_meta_data( '_smartwoo_sign_up_fee', $fee );
	}

	/**
	 * Add billing cycle number to new product.
	 * 
	 * @param string $data The billing cycle(Monthly, Quarterly, Six Monthly and Yearly allowed).
	 */
	public function add_billing_cycle( $data ) {
		$this->billing_cycle = $this->add_meta_data( '_smartwoo_billing_cycle', $data );
	}

	/**
	 * Add grace period number.
	 * 
	 * @param int $number	The number that will correspond to the unit.
	 */
	public function add_grace_period_number( $number ) {
		$this->grace_period_number = $this->add_meta_data( '_smartwoo_grace_period_number', absint( $number ) );
	}

	/**
	 *  Add grace period unit.
	 * 
	 * @param string $unit The of the grace perion(days, weeks, months, years).
	 */
	public function add_grace_period_unit( $unit ) {
		$this->grace_period_unit = $this->add_meta_data( '_smartwoo_grace_period_unit', $unit );
	}

	/*
	|--------------------------------------------------------------------------------
	|These `update` methods should be used when an existing product of this class has
	|been instanciated, should not be used when creating new product of this class, it's
	|possible but somehow may introduce duplicate meta data.
	|--------------------------------------------------------------------------------
	*/

	/**
	 * Update the Sign-up Fee.
	 * 
	 * @param float $fee The sign-up fee.
	 */
	public function update_sign_up_fee( $fee ) {
		$this->sign_up_fee = $this->update_meta_data( '_smartwoo_sign_up_fee', $fee );
	}

	/**
	 * Update Billing Cycle.
	 * 
	 * @param string $data The billing cycle(Monthly, Quarterly, Six Monthly and Yearly).
	 */
	public function update_billing_cycle( $data ) {
		$this->billing_cycle = $this->update_meta_data( '_smartwoo_billing_cycle', $data );
	}

	/**
	 * Update Grace Period Number.
	 * 
	 * @param int $number The grace period number.
	 */
	public function update_grace_period_number( $number ) {
		$this->grace_period_number = $this->update_meta_data( '_smartwoo_grace_period_number', $number );
	}

	/**
	 * Update Grace Perion Unit.
	 * 
	 * @param string $unit	The unit of the grace perion(days, weeks, months, years).
	 */
	public function update_grace_period_unit( $unit ) {
		$this->grace_period_unit = $this->update_meta_data( '_smartwoo_grace_period_unit', $unit );
	}
}


