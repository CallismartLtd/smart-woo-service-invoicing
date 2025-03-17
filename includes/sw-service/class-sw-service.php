<?php
/**
 * File name class-sw-invoice.php
 * Description  :   Invoice class definition file
 *
 * @author  Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * SmartWoo Service class
 * Represents the service subscription.
 * 
 * @author Callistus
 * @since 1.0.0
 * @package SmartWooService
 */
class SmartWoo_Service {

	/**
	 * The service id in the Database
	 * 
	 * @var int $id
	 */
	private $id;

	/**
	 * The ID of user associated with the service
	 * 
	 * @var int $user_id
	 */
	private $user_id;

	/**
	 * The product id
	 * 
	 * @var int $product_id
	 */
	private $product_id;

	/**
	 * The Service Name
	 * 
	 * @var string $service_name
	 */
	private $service_name;

	/**
	 * Service URL
	 * 
	 * @var string $service_url Service Url when the service is an external service.
	 */
	private $service_url;

	/**
	 * Service type
	 * 
	 * @var string $service_type
	 */
	private $service_type;

	/**
	 * Public service ID
	 * 
	 * @var string $service_id
	 */
	private $service_id;

	/**
	 * Deprecated property
	 * @var string $invoice_id
	 */
	private $invoice_id;

	/**
	 * Service start date
	 * 
	 * @var string $start_date
	 */
	private $start_date;

	/**
	 * Service End Date
	 * 
	 * @var string $end_date
	 */
	private $end_date;

	/**
	 * Service Due date or Next payment date
	 * 
	 * @var string $next_payment_date
	 */
	private $next_payment_date;

	/**
	 * Service billing cycle
	 * 
	 * @var string $billing_cycle
	 */
	private $billing_cycle;

	/**
	 * Service Status
	 * 
	 * @var string $status
	 */
	private $status;

	/**
	 * Service Subscription Assets
	 * 
	 * @since 2.0.0
	 * @var SmartWoo_Service_Assets
	 */
	private $assets = array();

	/**
	 * SmartWoo_Service constructor.
	 *
	 * @since 2.2.0 Object of this class cannot be instanciated with any data.
	 */
	public function __construct() {}

	/*
	|--------------
	| SETTERS
	|--------------
	*/

	/**
	 * Set the ID of the service.
	 *
	 * @param int $id Unique identifier for the service.
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	public function setId( $id ) {
		 $this->set_id( $id );
	}

	/**
	 * Set the user ID associated with the service.
	 *
	 * @param WP_User|WC_Customer|int $user Instace of WP_User or WC_Customer or int.
	 */
	public function set_user_id( $user ) {
		if ( ( $user instanceof WP_User ) ) {
			$this->user_id = $user->ID;
		} elseif( $user instanceof WC_Customer ) {
			$this->user_id = $user->get_id();
		} else {
			$this->user_id = absint( $user );
		}
	}

	public function setUserId( $user_id ) {
		$this->set_user_id( $user_id );
	}

	/**
	 * Set product ID
	 * 
	 * @param SmartWoo_Product|WC_Product|int $id
	 */
	public function set_product_id( $id ) {
		if ( $id instanceof SmartWoo_Product || $id instanceof WC_Product ) {
			$this->product_id = $id->get_id();
		} else {
			$this->product_id = absint( $id );
		}
	}

	public function setProductId( $product_id ){
		$this->set_product_id( $product_id );
	}



	/**
	 * Set the name of the service.
	 *
	 * @param string $service_name Name of the service.
	 */
	public function set_name( $service_name ) {
		$this->service_name = sanitize_text_field( wp_unslash( $service_name ) );
	}

	public function setServiceName( $service_name ) {
		$this->set_name( $service_name );
	}

	/**
	 * Set the URL associated with the service.
	 *
	 * @param string $service_url URL associated with the service.
	 */
	public function set_service_url( $service_url ) {
		$this->service_url = sanitize_url( $service_url, array( 'https', 'http' ) );
	}

	public function setServiceUrl( $service_url ) {
		$this->set_service_url( $service_url );
	}

	/**
	 * Set the type of the service.
	 *
	 * @param string $service_type
	 */
	public function set_type( $service_type ) {
		$this->service_type = sanitize_text_field( wp_unslash( $service_type ) );
	}

	public function setServiceType( $service_type ) {
		$this->set_type( $service_type );
	}

	/**
	 * Set the public service id.
	 *
	 * @param string $service_id Unique identifier for the service.
	 */
	public function set_service_id( $service_id ) {
		$this->service_id = sanitize_text_field( wp_unslash( $service_id ) );
	}
	
	public function setServiceId( $service_id ) {
		$this->set_service_id( $service_id );
	}

	/**
	 * Set the invoice ID associated with the service.
	 *
	 * @param string $invoice_id Invoice ID associated with the service.
	 */
	public function set_invoice_id( $invoice_id ) {
		$this->invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
	}

	public function setInvoiceId( $invoice_id ) {
		$this->set_invoice_id( $invoice_id );
	}

	/**
	 * Set the start date of the service.
	 *
	 * @param string $start_date Start date of the service.
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = sanitize_text_field( wp_unslash( $start_date ) );
	}

	public function setStartDate( $start_date ) {
		$this->set_start_date( $start_date );
	}

	/**
	 * Set the end date of the service.
	 *
	 * @param string $end_date End date of the service.
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = sanitize_text_field( wp_unslash( $end_date ) );
	}
	public function setEndDate( $end_date ) {
		$this->set_end_date( $end_date );
	}

	/**
	 * Set the date of the next payment for the service.
	 *
	 * @param string $next_payment_date Date of the next payment for the service.
	 */
	public function set_next_payment_date( $next_payment_date ) {
		$this->next_payment_date = sanitize_text_field( wp_unslash( $next_payment_date ) );
	}

	public function setNextPaymentDate( $next_payment_date ) {
		$this->set_next_payment_date( $next_payment_date );
	}

	/**
	 * Set the billing cycle for the service.
	 *
	 * @param string $billing_cycle Billing cycle for the service.
	 */
	public function set_billing_cycle( $billing_cycle ) {
		$this->billing_cycle = sanitize_text_field( wp_unslash( $billing_cycle ) );
	}

	public function setBillingCycle( $billing_cycle ) {
		$this->set_billing_cycle( $billing_cycle );
	}

	/**
	 * Set the status of the service.
	 *
	 * @param string|null $status Status of the service. Use null to clear the status.
	 */
	public function set_status( $status ) {
		$this->status = ( is_null( $status ) || '' === $status ) ? null : sanitize_text_field( wp_unslash( $status ) );
	}

	public function setStatus( $status ) {
		$this->set_status( $status );
	}

	/*
	|----------------
	| GETTERS
	|----------------
	*/

	/**
	 * Get the ID of the service.
	 *
	 * @return int|null Unique identifier for the service.
	 */
	public function get_id() {
		return $this->id;
	}
	
	public function getId() {
		return $this->get_id();
	}

	/**
	 * Get the user ID associated with the service.
	 *
	 * @return int User ID associated with the service.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	public function getUserId() {
		return $this->get_user_id();
	}

	/**
	 * Get the product ID associated with the service.
	 *
	 * @return int Product ID associated with the service.
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	public function getProductId() {
		return $this->get_product_id();
	}

	/**
	 * Get the name of the service.
	 *
	 * @return string|null Name of the service.
	 */
	public function get_name() {
		return $this->service_name;
	}

	public function getServiceName() {
		return $this->get_name();
	}

	/**
	 * Get the URL associated with the service.
	 *
	 * @return string|null URL associated with the service.
	 */
	public function get_service_url() {
		return $this->service_url;
	}
	
	public function getServiceUrl() {
		return $this->get_service_url();
	}

	/**
	 * Get the type or category of the service.
	 *
	 * @return string|null Type or category of the service.
	 */
	public function get_type() {
		return $this->service_type;
	}
	public function getServiceType() {
		return $this->get_type();
	}

	/**
	 * Get the unique identifier for the service.
	 *
	 * @return string Unique identifier for the service.
	 */
	public function get_service_id() {
		return $this->service_id;
	}

	public function getServiceId() {
		return $this->get_service_id();
	}

	/**
	 * Get the invoice ID associated with the service.
	 *
	 * @return string|null Invoice ID associated with the service.
	 */
	public function get_invoice_id() {
		return $this->invoice_id;
	}

	public function getInvoiceId() {
		return $this->get_invoice_id();
	}

	/**
	 * Get the start date of the service.
	 *
	 * @return string|null Start date of the service.
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	public function getStartDate() {
		return $this->get_start_date();
	}

	/**
	 * Get the end date of the service.
	 *
	 * @return string|null End date of the service.
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	public function getEndDate() {
		return $this->get_end_date();
	}

	/**
	 * Get the date of the next payment for the service.
	 *
	 * @return string|null Date of the next payment for the service.
	 */
	public function get_next_payment_date() {
		return $this->next_payment_date;
	}

	public function getNextPaymentDate() {
		return $this->get_next_payment_date();
	}

	/**
	 * Get the billing cycle for the service.
	 *
	 * @return string|null Billing cycle for the service.
	 */
	public function get_billing_cycle() {
		return $this->billing_cycle;
	}
	
	public function getBillingCycle() {
		return $this->get_billing_cycle();
	}

	/**
	 * Get the status of the service.
	 *
	 * @return string|null Status of the service.
	 */
	public function get_status() {
		return $this->status;
	}

	public function getStatus() {
		return $this->get_status();
	}

	/**
	 * Get the service expiry date
	 * 
	 * @since 2.2.0
	 */
	public function get_expiry_date() {
		return smartwoo_get_service_expiration_date( $this );
	}

	/*
	|-----------------
	| CRUD METHODS
	|-----------------
	*/

	/**
	 * Insert into the database.
	 */
	public function save() {
		if ( empty( $this->get_service_id() ) ) {
			return false; // Service ID must be generated before saving.
		}
		$this->id = SmartWoo_Service_Database::create_service( $this );
		
		return $this->id;
	}

	/*
	|-------------------
	| UTILITY METHODS
	|-------------------
	*/

	/**
	 * Get the WC_Customer object of the service owner
	 * 
	 * @since 2.2.0
	 * @return WC_Customer
	 */
	public function get_user() {
		return new WC_Customer( $this->get_user_id() );
	}

	/**
	 * Retrieve customer's billing email.
	 * 
	 * @since 2.2.0
	 */
	public function get_billing_email() {
		return smartwoo_get_client_billing_email( $this->get_user_id() );
	}

	/**
	 * Get billing address.
	 * @return string.
	 */
	public function get_billing_address() {
		return smartwoo_get_user_billing_address( $this->user_id );

	}

	/**
	 * Get preview URL
	 * 
	 * @since 2.1.1
	 */
	public function preview_url() {
		return smartwoo_service_preview_url( $this->get_service_id() );
	}


	/**
	 * Get the Service product name.
	 */
	public function get_product_name() {
		$product 		= $this->get_product();
		$product_name 	= ! empty( $product ) ? $product->get_name() : 'N/A';
		return $product_name;
	}

	/**
	 * Get the product.
	 * 
	 * @return SmartWoo_Product|false
	 */
	public function get_product() {
		return wc_get_product( $this->get_product_id() );
	}

	/** 
	 * Get the cost of service ( Excluding sign up fee)
	 */
	public function get_pricing() {
		$product = wc_get_product( $this->get_product_id() );
		$price = ! empty( $product ) ? $product->get_price() : 0;
		return $price;
	}

	/**
	 * Get sign up fee
	 */
	public function get_sign_up_fee() {
		$product 	= wc_get_product( $this->get_product_id() );
		$fee 		= ! empty( $product ) ? $product->get_sign_up_fee() : 0;
		return $fee;
	}

	/**
	 * Get Total cost.
	 */
	public function get_total_cost() {
		$total_cost = $this->get_pricing() + $this->get_sign_up_fee();
		return $total_cost;
	}

	// Helper method to convert database results to SmartWoo_Service objects
	public static function convert_array_to_service( $data ) {
		$self = new self();
		$self->set_id( isset( $data['id'] ) ? $data['id'] : 0 );
		$self->set_user_id( isset( $data['user_id'] ) ? $data['user_id'] : 0 );
		$self->set_product_id( isset( $data['product_id'] ) ? $data['product_id'] : 0 );
		$self->set_service_id( isset( $data['service_id'] ) ? $data['service_id'] : '' );
		$self->set_name( isset( $data['service_name'] ) ? $data['service_name'] : '' );
		$self->set_service_url( isset( $data['service_url'] ) ? $data['service_url'] : '' );
		$self->set_type( isset( $data['service_type'] ) ? $data['service_type'] : '' );
		$self->set_start_date( isset( $data['start_date'] ) ? $data['start_date'] : '' );
		$self->set_end_date( isset( $data['end_date'] ) ? $data['end_date'] : '' );
		$self->set_next_payment_date( isset( $data['next_payment_date'] ) ? $data['next_payment_date'] : '' );
		$self->set_billing_cycle( isset( $data['billing_cycle'] ) ? $data['billing_cycle'] : '' );
		$self->set_status( isset( $data['status'] ) ? $data['status'] : null );
		return $self;
	}

	/**
	 * Check whether a given product is a SmartWoo_Product
	 * 
	 * @param mixed $product
	 * @since 2.0.0
	 */
	public function is_smartwoo_product( $product ) {
		return ( $product instanceof SmartWoo_Product ) ? true : false;
	}

	/**
	 * Determine wether current user can access a service
	 * 
	 * @return bool True if yes, False otherwise.
	 * @since 2.0.1
	 */
	public function current_user_can_access() {
		if ( empty( $this->user_id ) ) {
			return false;
		}

		if ( is_admin() || current_user_can( 'manage_options' ) ) {
			return true;
		}

		if ( $this->user_id === get_current_user_id() ) {
			return true;
		}

		return false;
	}
    /**
     * Service Expiry notice
     * 
     * @param bool $echo Whether to print or return notice
     */
    public function print_expiry_notice( $echo = false ) {
		$cache_key = 'smartwoo_print_expiry_notice_' . $this->get_id();
        $output = get_transient( $cache_key );
        if ( false === $output ) {
            $output = '';
            $expiry_date = smartwoo_get_service_expiration_date( $this );
            if ( $expiry_date === smartwoo_extract_only_date( current_time( 'mysql' ) ) ) {
                $output .= smartwoo_notice( 'Expiring Today' );
            } elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '+1 day' ) ) ) {
                $output .= smartwoo_notice( 'Expiring Tomorrow' );
            } elseif ( $expiry_date === date_i18n( 'Y-m-d', strtotime( '-1 day' ) ) ) {
                $output .= smartwoo_notice( 'Expired Yesterday' );
            }
			set_transient( $cache_key, $output, 2 * MINUTE_IN_SECONDS );
        }

        if ( true === $echo ) {
            echo wp_kses_post( $output );
        } else {
            return $output;
        }

    }

	/**
	 * Check and print notice of unpaid invoices for this service.
	 * 
	 * @param bool $echo Whether to print or return notice.
	 */
	public function unpaid_invoices_notice( $echo = false ) {
		$notice = get_transient( 'smartwoo_unpaid_invoices_notice' );
		if ( false === $notice ) {
			$notice = '';
			$args	= array(
				'service_id' 		=> $this->service_id,
				'payment_status'	=> 'unpaid'
			);
			$unpaid_invs	= SmartWoo_Invoice_Database::query( $args, 'all' );
	
			if ( ! empty( $unpaid_invs ) ) {
				$count		= count( $unpaid_invs );
				$pay_url 	= apply_filters( 'smartwoo_service_mass_pay_url', $unpaid_invs[0]->pay_url(), $unpaid_invs );
				$notice 	= sprintf(
					'<div class="sw-service-notice"><p>%s</p><a href="%s" class="pay-button">%s</a></div>',
					sprintf(
						/* translators: 1: number of unpaid invoices, 2: plural or singular form */
						_n(
							'You have %d unpaid invoice for this service.',
							'You have %d unpaid invoices for this service.',
							$count,
							'smart-woo-service-invoicing'
						),
						$count
					),
					esc_url( $pay_url ),
					__( 'Pay Now', 'smart-woo-service-invoicing' )
				);
				
				set_transient( 'smartwoo_unpaid_invoices_notice', $notice, 2 * MINUTE_IN_SECONDS );
			}
		}

		if ( true === $echo ) {
			echo wp_kses_post( $notice );
		} else {
			return $notice;
		}

	}

	/**
	 * Print possible notice regarding a service.
	 * 
	 * @param string $type The notice type.
	 * @param bool $echo Whether to print or return the notice.
	 * @return string $notice.
	 */
	public function print_notice( $type = '', $echo = false ) {
		$allowed_types = apply_filters( 'smartwoo_service_allowed_notice_types', array( 'expiry', 'unpaid_invoice', 'due_invoice' ) );
		$notice = '';

		if ( ! in_array( $type, $allowed_types ) ) {
			return $notice;
		}

		if ( 'expiry' === $type ) {
			$notice = $this->print_expiry_notice();
		} elseif ( 'unpaid_invoice' ) {
			$notice = $this->unpaid_invoices_notice();
		}

		if ( true === $echo ) {
			echo wp_kses_post( $notice );
		} else {
			return $notice;
		}
	}

	/*
	|------------------------------
	| SUBSCRIPTION ASSETS METHODS
	|------------------------------
	*/

	/**
	 * Get all assets for this class.
	 * 
	 * @return array $assets Array of SmartWoo_Service_Asset
	 * @since 2.0.0
	 */
	public function get_assets() {
		if ( ! $this->has_asset() ) {
			return $this->assets;
		}

		$assets_obj		= new SmartWoo_Service_Assets();
		$assets_obj->set_service_id( $this->get_service_id() );
		$this->assets	= $assets_obj->get_service_assets();
		return $this->assets;	
	}

	/**
	 * Check whether the current subscription has an asset.
	 * 
	 * @return bool True if has assets, false otherwise.
	 * @since 2.0.0
	 */
	public function has_asset() {
		global $wpdb;
		$query 	= $wpdb->prepare( "SELECT `service_id` FROM " . SMARTWOO_ASSETS_TABLE . " WHERE `service_id` = %s", $this->service_id ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result	= $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $result !== null;
	}

	/**
	 * Sync Downloadable Product items to subscription.
	 * 
	 * @param string $asset_name The name of the asset.
	 * @since 2.0.0
	 */
	public function save_assets( $asset_name = '' ) {
		if ( 'downloads' === $asset_name ) {
			if ( empty( $this->getProductId() ) ) {
				return false;
			}

			$product = wc_get_product( $this->getProductId() );

			if ( $this->is_smartwoo_product( $product ) && $product->is_downloadable() ) {
				$this->assets['asset_name']		= ucfirst( $asset_name );
				$this->assets['service_id'] 	= $this->get_service_id();
				$this->assets['asset_data'] 	= $product->get_smartwoo_downloads();
				$this->assets['access_limit'] 	= -1; // Will allow users to set access limit in later updates.
				$this->assets['expiry'] 		= $this->getEndDate();
				$obj = SmartWoo_Service_Assets::convert_arrays( $this->assets );
				return $obj->save();
			}

			return false;
		}

	}

	/**
	 * Get all assets in container.
	 */
	public function get_assets_containers() {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		$assets = $this->get_assets();
		$service = $this;
		ob_start();
		include_once SMARTWOO_PATH . 'templates/service-admin-temp/service-assets.php';
		return ob_get_clean();
	}
	
}
