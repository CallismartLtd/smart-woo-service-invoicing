<?php
/**
 * Smart Woo Product class file
 * 
 * @author Callistus Nwachukwu
 * @package SmartWooProduct.
 * @since 1.0.0
 */

class SmartWoo_Product extends WC_Product {

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
	 * @param int|SmartWoo_Product|object $product Product to init.
	 */
	public function __construct( $product = 0 ) {
		parent::__construct( $product );
		
		if ( ! empty( $product ) ) {

		$this->set_sign_up_fee( $this->get_meta( '_smartwoo_sign_up_fee' ) );
		$this->set_billing_cycle( $this->get_meta( '_smartwoo_billing_cycle' ) );
		$this->set_grace_period_number( $this->get_meta( '_smartwoo_grace_period_number' ) );
		$this->set_grace_period_unit( $this->get_meta( '_smartwoo_grace_period_unit' ) );

		}


	}

	public static function init() {
		$product_instance = new self();
		$product_type = $product_instance->get_type();
		add_filter( 'woocommerce_product_class', array( __CLASS__, 'map_product_class' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'sub_info' ), 10 );
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'calculate_sign_up_fee_cart_totals' ) );
		add_action( 'woocommerce_' . $product_type .'_add_to_cart', array( __CLASS__, 'load_configure_button' ), 15 );
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
	 * @return object $smart_woo_products Object of SmartWoo_Product | WC_Product.
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
	 * Create a new SmartWoo_Product.
	 *
	 * @return SmartWoo_Product|WP_Error The created product object or WP_Error on failure.
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
	|These `add` methods should be used when instantiating
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
	|possible, but somehow may introduce duplicate meta data.
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

	/*
	|---------------------------------
	|Other Methods for compatibility.
	|---------------------------------
	*/

	/**
	 * Add to cart URL.
	 */
	public function add_to_cart_url() {
		return esc_url( esc_attr( smartwoo_configure_page( $this->get_id() ) ) );
	}
	
	/**
	 * Add to cart text.
	 */
	public function add_to_cart_text() {
		$text	= smartwoo_product_text_on_shop();
		return apply_filters( 'woocommerce_product_add_to_cart_text', $text , $this );
	}

	/**
	 * Make product purchasable.
	 */
	public function is_purchasable() {
		return true;
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

	/**
	 * Single Product add to cart text and url.
	 */

	public static function load_configure_button() {
		global $product;

		if ( $product && 'sw_product' === $product->get_type() ) {

			$button  = '<div class="configure-product-button">';
			$button .= '<a href="' . esc_attr( smartwoo_configure_page( $product->get_id() ) ) . '" class="sw-blue-button alt">' . esc_html__( smartwoo_product_text_on_shop(), 'smart-woo-service-invoicing' ) . '</a>';
			$button .= '</div>';
			echo wp_kses_post( $button );
		}
	}

	/**
	 * Subscription Details on single product page.
	 */
	public static function sub_info() {
		global $product;
	
		if ( $product && 'sw_product'  === $product->get_type() ) {
	
			$sign_up_fee   = $product->get_sign_up_fee();
			$billing_cycle = $product->get_billing_cycle();
	
			$notice_banner  = '<div class="mini-card">';
			$notice_banner .= '<p class="main-price"> You will be charged  <strong>' . wc_price( $product->get_price() ) . ' ' . esc_html( ucfirst( $billing_cycle ) ) . '</strong></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	
			if ( $sign_up_fee > 0 ) {
				$notice_banner .=  '<p class="sign-up-fee">and a one-time sign-up fee of <strong>' . wc_price( $sign_up_fee ) . '</strong></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$total_price = $product->get_price() + $sign_up_fee;
	
				$product->set_price( $total_price );
			}
			$notice_banner .=  '</div>';
			echo wp_kses_post( $notice_banner );
	
		}
	}

	/**
	 * Calculate the sum of sign-up fee and product price and set as cart subtotal
	 *
	 * @param object $cart the woocommerce cart object
	 * @return null stops execution when cart is empty
	 */
	public static function calculate_sign_up_fee_cart_totals( $cart ) {
		if ( $cart->is_empty() ) {
			return;
		}
	
		$total_sign_up_fee = 0;
	
		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];
	
			if ( $product && $product instanceof SmartWoo_Product ) {
				$quantity = $cart_item['quantity'];
	
				$sign_up_fee = (float) $product->get_sign_up_fee() * $quantity;
	
				// Add the sign-up fee for the current product to the total sign-up fee
				$total_sign_up_fee += $sign_up_fee;
			}
		}
	
		// Add total sign-up fee to cart total.
		$cart->add_fee( 'Sign-up Fee', $total_sign_up_fee );
	}	
}