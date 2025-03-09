<?php
/**
 * Class file for Smart Woo Product.
 * 
 * @since 1.0.0
 * @author Callistus.
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Smart Woo Product.
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
	 * @var self Static instance of current class.
	 */
	private static $instance = null;
	
	private $downloadable = false;
	private $downloads = array();


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
			
			if ( ! empty( $this->get_meta( '_smartwoo_product_downloadable_data' ) ) ) {
				$this->set_downloadables( $this->get_meta( '_smartwoo_product_downloadable_data' ) );
				
				if ( ! empty( $this->downloads ) ){
					$this->downloadable = true;
				}
			}

		}

	}

	/**
	 * Run Action hooks.
	 */
	public static function listen() {
		add_filter( 'woocommerce_product_class', array( __CLASS__, 'map_product_class' ), 10, 2 );
		add_filter( 'product_type_selector', array( __CLASS__, 'register_selector' ), 99 );
		add_filter( 'smartwoo_allowed_table_actions', array( __CLASS__, 'register_table_actions' ), 20 );
		
		add_action( 'smartwoo_product_table_actions', array( __CLASS__, 'ajax_table_callback' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'sub_info' ), 10 );
		add_action( 'woocommerce_' . self::instance()->get_type() .'_add_to_cart', array( __CLASS__, 'load_configure_button' ), 15 );
		add_action( 'wp_ajax_smartwoo_delete_product', array( __CLASS__, 'ajax_delete' ) );
	}

	/**
	 * Static instance of current SmartWoo_Product
	 * 
	 * @return SmartWoo_Product
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
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
	
	/**
	 * Get downloads
	 */
	public function get_smartwoo_downloads() {
		return $this->downloads;
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
		$this->sign_up_fee = floatval( $sign_up_fee );
	}

	/**
	 * Set the billing cycle.
	 * 
	 * @param string $billing_cycle The billing cycle.
	 */
	public function set_billing_cycle( $billing_cycle ) {
		if ( in_array( $billing_cycle, array_keys( smartwoo_supported_billing_cycles() ) ) ) {
			$this->billing_cycle = sanitize_text_field( wp_unslash( $billing_cycle ) );
		}
	}

	/**
	 * Set the Grace Period number.
	 * 
	 * @param int $grace_period_number	The grace period number
	 */
	public function set_grace_period_number( $grace_period_number ) {
		$this->grace_period_number = absint( $grace_period_number );
	}

	/**
	 * Set the grace period unit(days, weeks, months, years).
	 * @param string $grace_period_unit	the grace period units.
	 */
	public function set_grace_period_unit( $grace_period_unit ) {
		$this->grace_period_unit = sanitize_text_field( wp_unslash( $grace_period_unit ) );
	}

	/**
	 * Set Downloadable files.
	 * 
	 * @param array $data	An associative array containing file_name => file_url
	 */
	public function set_downloadables( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return;
		}
		$names 	= array_keys( $data );
		$urls	= array_values( $data );

		$file_urls 	= array_map( 'sanitize_url', wp_unslash( $urls ) );
		$file_names	= array_map( 'sanitize_text_field', wp_unslash( $names ) );

		if ( count( $file_urls ) === count( $file_names ) ) {
			$data = array_combine( $file_names, $file_urls );
			$this->downloads = $data;

		}
		$this->downloads = $data;

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
	 * @param array $args Arguments to pass to the query.
	 * @return object All product matching the type property of this class.
	 */
	public static function get_all( $args = array() ) {
		$defaults = array(
			'limit'		=> 10,
			'page'		=> 1,
			'visibility' => '',
		);

		$parsed_args = wp_parse_args( $args, $defaults );
		// Make sure the product type is not overidden.
		$parsed_args['type'] = self::instance()->get_type();

		$query = new WC_Product_Query( $parsed_args );

		return $query->get_products();
	}

	/**
	 * Count all products of this class.
	 * 
	 * @param $visibility The visibility of the product.
	 * @return int The total number of sw_products in the database.
	 */
	public static function count_all( $visibility = '' ) {
		$allowed_visibilities = array( 'publish', 'private', 'draft', 'pending', 'trash' );
		$args = array(
			'type'		=> 'sw_product',
			'limit'		=> -1,
			'return' 	=> 'ids',
		);

		if ( in_array( $visibility, $allowed_visibilities, true ) ) {
			$args['status'] = $visibility;
		}

		$query = new WC_Product_Query( $args );

		return count( $query->get_products() );
	}

	/**
	 * Get Products of this class for migration.
	 * 
	 * @param $type The type of migration defaults to Upgrade
	 * @return object $smart_woo_products Object of SmartWoo_Product | WC_Product.
	 */
	public static function get_migratables( $type = 'Upgrade') {
		$cat_id				= 0;
		$downgrade_cat_id	= absint( get_option( 'smartwoo_downgrade_product_cat', 0 ) );
		$upgrade_cat_id		= absint( get_option( 'smartwoo_upgrade_product_cat', 0 ) );
		if ( 'Downgrade' === $type ) {
			$cat_id = $downgrade_cat_id;
		} elseif ( 'Upgrade' === $type ) {
			$cat_id = $upgrade_cat_id;
		}

		if ( empty( $cat_id ) ) {
			return false; 
		}

		$term	= get_term( $cat_id );
		$slug	= $term ? $term->slug : '';
		$args	= array( 
			'type' 		=> 'sw_product', 
			'status'	=> 'publish',
			'category'	=> $slug,
		);
		$query    			= new WC_Product_Query( $args );
		$products 			= $query->get_products();

		if ( empty( $products ) ) {
			return false;
		}
		
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
		$this->set_sign_up_fee( $fee ); 
		$this->add_meta_data( '_smartwoo_sign_up_fee', $fee );
	}

	/**
	 * Add billing cycle number to new product.
	 * 
	 * @param string $data The billing cycle( Weekly, Monthly, Quarterly, Six Monthly and Yearly allowed).
	 */
	public function add_billing_cycle( $data ) {
		if ( in_array( $data, array_keys( smartwoo_supported_billing_cycles() ) ) ) {
			$this->set_billing_cycle( $data );
			$this->add_meta_data( '_smartwoo_billing_cycle', $data );

		}
	}

	/**
	 * Add grace period number.
	 * 
	 * @param int $number	The number that will correspond to the unit.
	 */
	public function add_grace_period_number( $number ) {
		$this->set_grace_period_number( $number );
		$this->add_meta_data( '_smartwoo_grace_period_number', absint( $number ) );
	}

	/**
	 *  Add grace period unit.
	 * 
	 * @param string $unit The of the grace perion(days, weeks, months, years).
	 */
	public function add_grace_period_unit( $unit ) {
		$this->set_grace_period_unit( $unit );
		$this->add_meta_data( '_smartwoo_grace_period_unit', $unit );
	}

	/**
	 * Add downloadable data to product.
	 * 
	 * @param array $data Associative array of file_name => file_url
	 */
	public function add_downloadable_data( $data ) {
		$this->set_downloadables( $data );
		$this->add_meta_data( '_smartwoo_product_downloadable_data', $this->downloads );
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
		$this->set_sign_up_fee( $fee ); 
		$this->update_meta_data( '_smartwoo_sign_up_fee', $fee );
	}

	/**
	 * Update Billing Cycle.
	 * 
	 * @param string $data The billing cycle(Weekly, Monthly, Quarterly, Semiannually and Yearly).
	 */
	public function update_billing_cycle( $data ) {
		if ( in_array( $data, array_keys( smartwoo_supported_billing_cycles() ) ) ) {
			$this->set_billing_cycle( $data );
			$this->update_meta_data( '_smartwoo_billing_cycle', $data );
		}
	}

	/**
	 * Update Grace Period Number.
	 * 
	 * @param int $number The grace period number.
	 */
	public function update_grace_period_number( $number ) {
		$this->set_grace_period_number( $number );
		$this->update_meta_data( '_smartwoo_grace_period_number', $number );
	}

	/**
	 * Update Grace Perion Unit.
	 * 
	 * @param string $unit	The unit of the grace perion(days, weeks, months, years).
	 */
	public function update_grace_period_unit( $unit ) {
		$this->set_grace_period_unit( $unit );
		$this->update_meta_data( '_smartwoo_grace_period_unit', $unit );
	}

	/**
	 * Update downloadable data.
	 * 
	 * @param array $data Associative array of file_names => file_urls
	 */
	public function update_downloadable_data( $data ) {
		$this->set_downloadables( $data );
		$this->update_meta_data( '_smartwoo_product_downloadable_data', $this->downloads  );
	}

	/*
	|---------------------------------
	|Other Methods for compatibility.
	|---------------------------------
	*/

	/**
	 * Check whether smartwoo product is downloadable.
	 * 
	 * @since 2.0.0
	 */
	public function is_downloadable() {
		return $this->downloadable;
	}

	/**
	 * Product edit link
	 */
	public static  function get_edit_url( $link, $post_id ) {
		$post = get_post( $post_id );

		if ( $post && $post->post_type === 'product' ) {
			$product = wc_get_product( $post_id );
	
			if ( $product && $product->is_type( 'sw_product' ) ) {
	
				$link = smartwoo_admin_product_url( $action = 'edit', $product->get_id() );
			}
		}
	
		return $link;
	}

	/**
	 * Add to cart URL.
	 */
	public function add_to_cart_url() {
		return esc_url( smartwoo_configure_page( $this->get_id() ) );
	}
	
	/**
	 * Add to cart text.
	 */
	public function add_to_cart_text() {
		$text	= smartwoo_product_text_on_shop();
		return apply_filters( 'smartwoo_add_to_cart_text', $text, $this );
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
	 * Register Class in the product selector.
	 * 
	 * @param array $selectors Associative array of product_type => fullname.
	 * @since 2.1.1
	 */
	public static function register_selector( $selectors ){

		$selectors[self::instance()->get_type()] = 'Smart Woo Product';
		return $selectors;
	}

	/**
	 * Single Product add to cart text and url.
	 */
	public static function load_configure_button() {
		global $product;

		if ( $product && self::instance()->get_type() === $product->get_type() ) {
			$product->get_add_to_cart_button();
		}
	}

	/**
	 * Get the add to cart button
	 */
	public function get_add_to_cart_button() {
		?>
		<div class="configure-product-button">
			<a href="<?php echo esc_url( smartwoo_configure_page( $this->get_id() ) ); ?>" class="button product_type_<?php echo esc_attr( self::instance()->get_type() ); ?> add_to_cart_button" data-product_id="<?php echo absint( $this->get_id() ); ?>"><?php echo esc_html( smartwoo_product_text_on_shop() ); ?></a>
		</div>
		<?php
	}
	/**
	 * Subscription Details on single product page.
	 */
	public static function sub_info() {
		global $product;
	
		if ( $product && self::instance()->get_type()  === $product->get_type() ) {
	
			$sign_up_fee	= $product->get_sign_up_fee();
			if ( $sign_up_fee ) {
				$billing_cycle 	= $product->get_billing_cycle();
				$billed_txt		= '';
				if ( ! empty( $billing_cycle ) ) {
					$billed_txt = 'Billed';
				}
				?>
				<div class="smartwoo-sub-info">
					<p class="main-price"><strong><?php echo esc_html( smartwoo_price( $product->get_price() ) ); ?> </strong><?php echo esc_html( $billed_txt ); ?> <strong><?php echo esc_html( ucfirst( $billing_cycle ) ); ?></strong></p>
		
					<?php if ( $sign_up_fee > 0 ) : ?>
						<p class="sign-up-fee">and a one-time sign-up fee of <strong><?php echo esc_html( smartwoo_price( $sign_up_fee ) ); ?></strong></p>
					<?php endif; ?>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Ajax Product deletion.
	 */
	public static function ajax_delete() {
		if ( ! check_ajax_referer( sanitize_text_field( wp_unslash( 'smart_woo_nonce' ) ), 'security', false ) ) {
			wp_send_json_error( array( 'message' => 'Action failed basic authentication.') );
		}
		// Check if the user is logged in and has the necessary capability.
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have the required permission to delete this product.') );
		}

		// Get the product ID from the AJAX request.
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( empty( $product_id ) ) {
			wp_send_json_error( array( 'message' => 'Error deleting the product.' ) );
		}

		$product = wc_get_product( $product_id );
		if ( $product && $product->delete( true ) ) {
			wp_send_json_success( array( 'message' => 'Product deleted successfully.' ) );
		}

		wp_send_json_error( array( 'message' => 'Error deleting the product.' ) );
	}

	/**
	 * Handle Smart Woo Ajax Table actions
	 * 
	 * @param string $selected_action The selected action.
	 * @param array $data The data to be processed.
	 */
	public static function ajax_table_callback( $selected_action, $data ) {
		if ( ! is_array( $data ) ) {
			$data = explode( ',', $data );
		}

		$response = array( 'message' => 'Invalid action' );
		foreach( $data as $id ) {
			$self = wc_get_product( $id );
			if ( ! $self || ( ! $self instanceof self ) ) {
				continue;
			}

			switch( $selected_action ) {
				case 'publish':
					if ( 'publish' !== $self->get_status() ) {
						$self->set_status( 'publish' );
						$self->save();
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been published.';
					}
					break;
				case 'draft':
					if ( 'draft' !== $self->get_status() ) {
						$self->set_status( 'draft' );
						$self->save();
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been drafted.';
					}
					break;
				case 'trash':
					if ( 'trash' !== $self->get_status() ) {
						$self->set_status( 'trash' );
						$self->save();
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been trashed.';
					}
					break;
				case 'pending':
					if ( 'pending' !== $self->get_status() ) {
						$self->set_status( 'pending' );
						$self->save();
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been set to pending.';
					}
					break;
				case 'private':
					if ( 'private' !== $self->get_status() ) {
						$self->set_status( 'private' );
						$self->save();
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been set to private.';
					}
					break;
				case 'delete':
					if ( $self->delete( true ) ) {
						$response['message'] = 'The selected product' . ( count( $data ) > 1 ? 's' : '' ) . ' has been deleted.';
					}
					break;

			}

		}

		wp_send_json_success( $response );
	}

	/**
	 * Add allowed product actions on sw-table.
	 * 
	 * @param array $actions The allowed actions passed by the filter.
	 * @return array $actions The modified actions.
	 */
	public static function register_table_actions( $actions ) {
		$actions[] 	= 'delete';
		$actions[]	= 'publish';
		$actions[]	= 'draft';
		$actions[]	= 'trash';
		$actions[]	= 'pending';
		$actions[]	= 'private';
		return $actions;
	}
}
SmartWoo_Product::listen();