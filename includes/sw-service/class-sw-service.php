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

	// Properties.
	private $id;
	private $user_id;
	private $product_id;
	private $service_name;
	private $service_url;
	private $service_type;
	private $service_id;
	private $invoice_id;
	private $start_date;
	private $end_date;
	private $next_payment_date;
	private $billing_cycle;
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
	 * @param int         $user_id            User ID associated with the service.
	 * @param int         $product_id         Product ID for the service
	 * @param string      $service_id         Unique identifier for the service.
	 * @param string|null $service_name       Name of the service (optional).
	 * @param string|null $service_url        URL associated with the service (optional).
	 * @param string|null $service_type       Type or category of the service (optional).
	 * @param string|null $invoice_id         Invoice ID associated with the service (optional).
	 * @param string|null $start_date         Start date of the service (optional).
	 * @param string|null $end_date           End date of the service (optional).
	 * @param string|null $next_payment_date  Date of the next payment for the service (optional).
	 * @param string|null $billing_cycle      Billing cycle for the service (optional).
	 * @param string|null $status             Status of the service (optional).
	 */
	public function __construct(
		int $user_id,
		int $product_id,
		string $service_id,
		?string $service_name = null,
		?string $service_url = null,
		?string $service_type = null, 
		?string $invoice_id = null,
		?string $start_date = null,
		?string $end_date = null,
		?string $next_payment_date = null,
		?string $billing_cycle = null,
		?string $status = null
	) {
		$this->user_id           = $user_id;
		$this->product_id        = $product_id;
		$this->service_id        = $service_id;
		$this->service_name      = $service_name;
		$this->service_url       = $service_url;
		$this->service_type      = $service_type;
		$this->invoice_id        = $invoice_id;
		$this->start_date        = $start_date;
		$this->end_date          = $end_date;
		$this->next_payment_date = $next_payment_date;
		$this->billing_cycle     = $billing_cycle;
		$this->status            = $status;
	}

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
	public function setId( int $id ): void {
		$this->id = $id;
	}

	/**
	 * Set the user ID associated with the service.
	 *
	 * @param int $user_id User ID associated with the service.
	 */
	public function setUserId( int $user_id ): void {
		$this->user_id = $user_id;
	}

	/**
	 * Set the product ID associated with the service.
	 *
	 * @param int $product_id Product ID associated with the service.
	 */
	public function setProductId( int $product_id ): void {
		$this->product_id = $product_id;
	}



	/**
	 * Set the name of the service.
	 *
	 * @param string $service_name Name of the service.
	 */
	public function setServiceName( string $service_name ): void {
		$this->service_name = $service_name;
	}

	/**
	 * Set the URL associated with the service.
	 *
	 * @param string $service_url URL associated with the service.
	 */
	public function setServiceUrl( string $service_url ): void {
		$this->service_url = $service_url;
	}

	/**
	 * Set the type or category of the service.
	 *
	 * @param string $service_type Type or category of the service.
	 */
	public function setServiceType( string $service_type ): void {
		$this->service_type = $service_type;
	}

	/**
	 * Set the unique identifier for the service.
	 *
	 * @param string $service_id Unique identifier for the service.
	 */
	public function setServiceId( string $service_id ): void {
		$this->service_id = $service_id;
	}

	/**
	 * Set the invoice ID associated with the service.
	 *
	 * @param string $invoice_id Invoice ID associated with the service.
	 */
	public function setInvoiceId( string $invoice_id ): void {
		$this->invoice_id = $invoice_id;
	}

	/**
	 * Set the start date of the service.
	 *
	 * @param string $start_date Start date of the service.
	 */
	public function setStartDate( string $start_date ): void {
		$this->start_date = $start_date;
	}

	/**
	 * Set the end date of the service.
	 *
	 * @param string $end_date End date of the service.
	 */
	public function setEndDate( string $end_date ): void {
		$this->end_date = $end_date;
	}

	/**
	 * Set the date of the next payment for the service.
	 *
	 * @param string $next_payment_date Date of the next payment for the service.
	 */
	public function setNextPaymentDate( string $next_payment_date ): void {
		$this->next_payment_date = $next_payment_date;
	}

	/**
	 * Set the billing cycle for the service.
	 *
	 * @param string $billing_cycle Billing cycle for the service.
	 */
	public function setBillingCycle( string $billing_cycle ): void {
		$this->billing_cycle = $billing_cycle;
	}

	/**
	 * Set the status of the service.
	 *
	 * @param string|null $status Status of the service. Use null to clear the status.
	 */
	public function setStatus( ?string $status ): void {
		$this->status = $status;
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
	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * Get the user ID associated with the service.
	 *
	 * @return int User ID associated with the service.
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * Get the product ID associated with the service.
	 *
	 * @return int Product ID associated with the service.
	 */
	public function getProductId(): int {
		return $this->product_id;
	}

	/**
	 * Get the name of the service.
	 *
	 * @return string|null Name of the service.
	 */
	public function getServiceName(): ?string {
		return $this->service_name;
	}

	/**
	 * Get the URL associated with the service.
	 *
	 * @return string|null URL associated with the service.
	 */
	public function getServiceUrl(): ?string {
		return $this->service_url;
	}

	/**
	 * Get the type or category of the service.
	 *
	 * @return string|null Type or category of the service.
	 */
	public function getServiceType(): ?string {
		return $this->service_type;
	}

	/**
	 * Get the unique identifier for the service.
	 *
	 * @return string Unique identifier for the service.
	 */
	public function getServiceId(): string {
		return $this->service_id;
	}

	/**
	 * Get the invoice ID associated with the service.
	 *
	 * @return string|null Invoice ID associated with the service.
	 */
	public function getInvoiceId(): ?string {
		return $this->invoice_id;
	}

	/**
	 * Get the start date of the service.
	 *
	 * @return string|null Start date of the service.
	 */
	public function getStartDate(): ?string {
		return $this->start_date;
	}

	/**
	 * Get the end date of the service.
	 *
	 * @return string|null End date of the service.
	 */
	public function getEndDate(): ?string {
		return $this->end_date;
	}

	/**
	 * Get the date of the next payment for the service.
	 *
	 * @return string|null Date of the next payment for the service.
	 */
	public function getNextPaymentDate(): ?string {
		return $this->next_payment_date;
	}

	/**
	 * Get the billing cycle for the service.
	 *
	 * @return string|null Billing cycle for the service.
	 */
	public function getBillingCycle(): ?string {
		return $this->billing_cycle;
	}

	/**
	 * Get the status of the service.
	 *
	 * @return string|null Status of the service.
	 */
	public function getStatus(): ?string {
		return $this->status;
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
		if ( empty( $this->getServiceId() ) ) {
			return false; // Service ID must be generated be saving.
		}
		$id = SmartWoo_Service_Database::create_service( $this ); 
		return $id;
	}

	/*
	|-------------------
	| UTILITY METHODS
	|-------------------
	*/

	/**
	 * Get the Service product name.
	 */
	public function get_product_name() {
		$product 		= wc_get_product( $this->getProductId() );
		$product_name 	= ! empty( $product ) ? $product->get_name() : 'N/A';
		return $product_name;
	}

	/** 
	 * Get the cost of service ( Excluding sign up fee)
	 */
	public function get_pricing() {
		$product = wc_get_product( $this->getProductId() );
		$price = ! empty( $product ) ? $product->get_price() : 0;
		return $price;
	}

	/**
	 * Get sign up fee
	 */
	public function get_sign_up_fee() {
		$product = wc_get_product( $this->getProductId() );
		$fee = ! empty( $product ) ? $product->get_sign_up_fee() : 0;
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
		// Create a new SmartWoo_Service instance with the provided data
		return new SmartWoo_Service(
			$data['user_id'],
			$data['product_id'],
			$data['service_id'],
			$data['service_name'],
			$data['service_url'],
			$data['service_type'],
			$data['invoice_id'],
			$data['start_date'],
			$data['end_date'],
			$data['next_payment_date'],
			$data['billing_cycle'],
			$data['status']
		);
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

        $output = get_transient( 'smartwoo_print_expiry_notice' );
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
			set_transient( 'smartwoo_print_expiry_notice', $output, 2 * MINUTE_IN_SECONDS );
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
		$assets_obj->set_service_id( $this->getServiceId() );
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
				$this->assets['service_id'] 	= $this->getServiceId();
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
