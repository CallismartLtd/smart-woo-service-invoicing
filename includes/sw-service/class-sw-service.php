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
	protected $id;

	/**
	 * The ID of user associated with the service
	 * 
	 * @var int $user_id
	 */
	protected $user_id;

	/**
	 * The product id
	 * 
	 * @var int $product_id
	 */
	protected $product_id;

	/**
	 * The Service Name
	 * 
	 * @var string $service_name
	 */
	protected $service_name;

	/**
	 * Service URL
	 * 
	 * @var string $service_url Service Url when the service is an external service.
	 */
	protected $service_url;

	/**
	 * Service type
	 * 
	 * @var string $service_type
	 */
	protected $service_type;

	/**
	 * Public service ID
	 * 
	 * @var string $service_id
	 */
	protected $service_id;

	/**
	 * Service start date
	 * 
	 * @var string $start_date
	 */
	protected $start_date;

	/**
	 * Service End Date
	 * 
	 * @var string $end_date
	 */
	protected $end_date;

	/**
	 * Service Due date or Next payment date
	 * 
	 * @var string $next_payment_date
	 */
	protected $next_payment_date;

	/**
	 * Date created.
	 * 
	 * @var string $date_created
	 * @since 2.4.3
	 */
	protected $date_created;

	/**
	 * Service billing cycle
	 * 
	 * @var string $billing_cycle
	 */
	protected $billing_cycle;

	/**
	 * Service Status
	 * 
	 * @var string $status
	 */
	protected $status;

	/**
	 * Service Subscription Assets
	 * 
	 * @since 2.0.0
	 * @var SmartWoo_Service_Assets
	 */
	protected $assets = array();

	/**
	 * Meta data
	 * 
	 * @var array $meta_data 
	 */
	protected $meta_data = array();

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

	/**
	 * Set the name of the service.
	 *
	 * @param string $service_name Name of the service.
	 */
	public function set_name( $service_name ) {
		$this->service_name = sanitize_text_field( wp_unslash( $service_name ) );
	}

	/**
	 * Set the URL associated with the service.
	 *
	 * @param string $service_url URL associated with the service.
	 */
	public function set_service_url( $service_url ) {
		$this->service_url = sanitize_url( $service_url, array( 'https', 'http' ) );
	}

	/**
	 * Set the type of the service.
	 *
	 * @param string $service_type
	 */
	public function set_type( $service_type ) {
		$this->service_type = sanitize_text_field( wp_unslash( $service_type ) );
	}

	/**
	 * Set the public service id.
	 *
	 * @param string $service_id Unique identifier for the service.
	 */
	public function set_service_id( $service_id ) {
		$this->service_id = sanitize_text_field( wp_unslash( $service_id ) );
	}

	/**
	 * Set the start date of the service.
	 *
	 * @param string $start_date Start date of the service.
	 */
	public function set_start_date( $start_date ) {
		$this->start_date = sanitize_text_field( wp_unslash( $start_date ) );
	}

	/**
	 * Set the end date of the service.
	 *
	 * @param string $end_date End date of the service.
	 */
	public function set_end_date( $end_date ) {
		$this->end_date = sanitize_text_field( wp_unslash( $end_date ) );
	}

	/**
	 * Set the date of the next payment for the service.
	 *
	 * @param string $next_payment_date Date of the next payment for the service.
	 */
	public function set_next_payment_date( $next_payment_date ) {
		$this->next_payment_date = sanitize_text_field( wp_unslash( $next_payment_date ) );
	}

	/**
	 * Set the date created
	 * 
	 * @param string $date_created
	 */
	public function set_date_created( $date_created ){
		$this->date_created = sanitize_text_field( wp_unslash( $date_created ) );
	}

	/**
	 * Set the billing cycle for the service.
	 *
	 * @param string $billing_cycle Billing cycle for the service.
	 */
	public function set_billing_cycle( $billing_cycle ) {
		if ( in_array( $billing_cycle, array_keys( smartwoo_supported_billing_cycles() ), true ) ) {
			$this->billing_cycle = sanitize_text_field( wp_unslash( $billing_cycle ) );
		}
	}

	/**
	 * Set the status of the service.
	 *
	 * @param string|null $status Status of the service. Use null to clear the status.
	 */
	public function set_status( $status ) {
		$this->status = ( is_null( $status ) || '' === $status ) ? null : sanitize_text_field( wp_unslash( $status ) );
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

	/**
	 * Get the user ID associated with the service.
	 *
	 * @return int User ID associated with the service.
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Get the product ID associated with the service.
	 *
	 * @return int Product ID associated with the service.
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get the name of the service.
	 *
	 * @return string|null Name of the service.
	 */
	public function get_name() {
		return $this->service_name;
	}

	/**
	 * Get the URL associated with the service.
	 *
	 * @return string|null URL associated with the service.
	 */
	public function get_service_url() {
		return $this->service_url;
	}

	/**
	 * Get the type or category of the service.
	 *
	 * @return string|null Type or category of the service.
	 */
	public function get_type() {
		return $this->service_type;
	}

	/**
	 * Get the public id for this service subscription.
	 *
	 * @return string
	 */
	public function get_service_id() {
		return $this->service_id;
	}

	/**
	 * Get the start date of the service.
	 *
	 * @return string|null Start date of the service.
	 */
	public function get_start_date() {
		return $this->start_date;
	}

	/**
	 * Get the end date of the service.
	 *
	 * @return string|null End date of the service.
	 */
	public function get_end_date() {
		return $this->end_date;
	}

	/**
	 * Get the date of the next payment for the service.
	 *
	 * @return string|null Date of the next payment for the service.
	 */
	public function get_next_payment_date() {
		return $this->next_payment_date;
	}

	/**
	 * Get date created
	 * 
	 * @return string Date the subscription was created
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * Get the billing cycle for the service.
	 *
	 * @return string|null Billing cycle for the service.
	 */
	public function get_billing_cycle() {
		return $this->billing_cycle;
	}

	/**
	 * Get the status of the service.
	 *
	 * @return string|null Status of the service.
	 */
	public function get_status() {
		return $this->status;
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
	 * Insert or update this service subscription.
	 * 
	 * @return bool True if saved, false otherwise.
	 */
	public function save() {
		if ( empty( $this->get_service_id() ) ) {
			return false; // Service ID must be generated before saving.
		}
		
		return SmartWoo_Service_Database::create_service( $this );
	}

	/**
	 * Delete this service subscription from the database.
	 * 
	 * @return bool True if deleted, false otherwise.
	 */
	public function delete() {
		if ( empty( $this->get_id() ) ) {
			return false; // Cannot delete a service without an ID.
		}

		return SmartWoo_Service_Database::delete_service( $this );
	}

	/*
	|-----------------
	| META DATA METHODS
	|-----------------
	*/

	/**
	 * Set the value of a metadata
	 * 
	 * @param string $meta_name The name of the metadata.
	 * @param mixed $meta_value The value of the metadata
	 */
	public function set_meta( $meta_name, $meta_value ) {
		$this->meta_data[sanitize_key( $meta_name )] = sanitize_text_field( wp_unslash( $meta_value ) );
	}

	/**
	 * Get the value of a metadata
	 * 
	 * @param string $meta_name The metadata name.
	 * @param mixed $default The default value to return when the metadata is not found
	 * @return mixed
	 */
	public function get_meta( $meta_name, $default = null ) {
		return $this->meta_data[$meta_name] ?? $default;
	}

	/**
	 * Get all the metadata on an service object.
	 * 
	 * @return array An associative array of meta_name => meta_value
	 */
	public function get_all_meta() {
		return $this->meta_data;
	}

	/**
	 * Delete All Meta data from the database.
	 */
	public function delete_all_meta() {
		return SmartWoo_Service_Database::delete_all_meta( $this );
	}

	/**
	 * Delete a single meta data from the invoice object, the caller should save the object to persist the changes
	 *  or pass `true` to the second parameter to delete the meta data from the database.
	 * 
	 * @param string $meta_name The name of the meta data to delete.
	 * @param bool $deep Whether to delete the meta data from the database.
	 * @return bool True if the meta data was deleted, false otherwise.
	 */
	public function delete_meta( $meta_name, $deep = false ) {
		if ( isset( $this->meta_data[$meta_name] ) ) {
			unset( $this->meta_data[$meta_name] );

			if ( true === $deep ) {
				return SmartWoo_Service_Database::delete_meta( $this->get_service_id(), $meta_name );
			}
		}

		return false;
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
		$product	= $this->get_product();
		$price		= $product ? $product->get_price() : 0;
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

	/**
	 * Get all the properties as array
	 * 
	 * @return array
	 */
	public function get_all_props() {
		$data = get_object_vars( $this );

		return apply_filters( 'smartwoo_service_props', $data );
	}

	/**
	 * Convert an associative array to a service subscription object.
	 * 
	 * @param array $data
	 * @return self
	 */
	public static function set_from_array( $data ) {
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
		$self->set_date_created( isset( $data['date_created'] ) ? $data['date_created'] : '' );
		$self->set_billing_cycle( isset( $data['billing_cycle'] ) ? $data['billing_cycle'] : '' );
		$self->set_status( isset( $data['status'] ) ? $data['status'] : null );

		/**
		 * Set up metadata
		 */
		$all_meta = SmartWoo_Service_Database::get_all_metadata( $self );

		foreach( $all_meta as $meta ) {
			$self->set_meta( $meta['meta_name'], $meta['meta_value'] );
		}
		
		return $self;
	}

	/**
	 * Check whether a given product is a SmartWoo_Product
	 * 
	 * @param mixed $product
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_smartwoo_product( $product ) {
		return ( $product instanceof SmartWoo_Product ) ? true : false;
	}

	/**
	 * Get renewal status
	 * 
	 * @return string
	 * @since 2.3.2
	 */
	public function get_renewal_status() {
		$status = smartwoo_service_status( $this );

		$label = 'ON';

		if ( 'Active (NR)' === $status ) {
			$label = 'OFF';
		} elseif ( 'Suspended' === $status ) {
			$label = 'PAUSED';
		}

		return $label;
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
     * @param bool $echo Whether to print or return notice.
	 * @return string|void
     */
    public function print_expiry_notice( $echo = false ) {
		$cache_key = 'smartwoo_print_expiry_notice_' . $this->get_id();
        $output = get_transient( $cache_key );
        if ( false === $output ) {
            $output = '';
            $expiry_date = smartwoo_get_service_expiration_date( $this );
            if ( $expiry_date === smartwoo_extract_only_date( current_time( 'mysql' ) ) ) {
                $output .= smartwoo_notice( 'Expiring Today' );
            } elseif ( $this->is_expiring_tomorrow() ) {
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
	 * @return string|void
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
	 * @return string|void
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
	 * Get all assets for a service subscription.
	 * 
	 * @return SmartWoo_Service_Asset[] $assets Array of SmartWoo_Service_Asset
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
	 * Check whether this subscription owns an asset with the given ID
	 * 
	 * @param int $asset_id The ID of the asset
	 * @return bool True when the asset belongs to this service, false otherwise.
	 * @since 2.4.3
	 */
	public function owns_asset( $asset_id ) {
		if ( ! $this->has_asset() ) {
			return false;
		}
		 
		foreach ( $this->get_assets() as $asset ) {
			if ( absint( $asset_id ) === $asset->get_id() ) {
				return true;
			}
		}

		return false;
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

	/**
	 * Get the current client asset containers
	 */
	public function get_client_asset_containers() {
		if ( ! $this->has_asset() ) {
			return '';
		}
		$service				= $this;
		$assets 				= $service->get_assets();
		$total_assets			= count( $assets );
		$downloadables			= array();
		$additionals			= array();
		$download_asset_object	= null;
		$download_asset_type_id	= 0;
		foreach ( $assets as $asset ) {
			if ( 'downloads' === $asset->get_asset_name() ) {
				foreach ( $asset->get_asset_data() as $file => $url ) {
					$downloadables[$file]	= $url;
				}

				$download_asset_object = $asset;
				$download_asset_type_id = $asset->get_id();
				continue;
			}
			
			$additionals[] = $asset;
		}

		ob_start();
		include_once SMARTWOO_PATH . 'templates/frontend/subscriptions/client-assets.php';
		return ob_get_clean();
	}

    /**
     |---------------------------------------------------------
     | SERVICE SUBSCRIPTION STATUS CALCULATION METHODS
     |---------------------------------------------------------
     | These methods define how a subscription's effective status is determined.
     |
     | The public `get_effective_status()` method is the main entry point. It prioritizes statuses as follows:
     |
     | 1.  **Explicit Override:** A status manually set in the database takes immediate precedence.
     | 2.  **Cached Status:** If no override exists, a recently calculated status from cache is used for performance.
     | 3.  **Calculated Status (Date-Based):** If no override or cache, the status is dynamically calculated.
     |     This calculation uses several **private helper methods**, each checking a very specific condition:
     |
     |     -   `is_active_condition()`: Checks for a perfectly current, paid-up state.
     |     -   `is_due_for_renewal_condition()`: Identifies when payment has matured, even if the service is still active.
     |         **Important:** A service is considered **logically active/usable** even when 'Due for Renewal'.
     |     -   `is_in_grace_period_condition()`: Checks if the service is past its end date but within a grace period.
     |     -   `is_expired_condition()`: Determines if the service has definitively expired (after grace).
     |
     | The `get_effective_status()` method applies these private conditions in a strict order (Active -> Due -> Grace -> Expired)
     | to yield the final, definitive service status.
     */
	public function get_effective_status(): string {
        // 1. Check for explicit status override from the database.
        // This is the raw status property, which acts as the admin override.
        $explicit_db_status = $this->get_status();

        // If a non-empty string is explicitly set in the database, it's an override. Use it directly.
        if ( ! empty( $explicit_db_status ) ) {
            return $explicit_db_status;
        }

        // 2. No explicit override, so try to retrieve from cache (transient).
        $cached_status = get_transient( 'smartwoo_status_' . $this->get_service_id() );

        // If found in cache (and it's not 'false', which indicates a cache miss), return it.
        if ( false !== $cached_status ) {
            return $cached_status;
        }

        // 3. Cache miss and no explicit override, so auto-calculate the status.
        $calculated_status = 'Unknown'; // Default fallback status if no condition matches.

        // Calculate statuses based on date logic in the defined order of precedence.
        // The first condition that evaluates to true determines the status.
        if ( $this->is_active_condition() ) {
            $calculated_status = 'Active';
        } elseif ( $this->is_due_for_renewal_condition() ) {
            $calculated_status = 'Due for Renewal';
        } elseif ( $this->is_in_grace_period_condition() ) {
            $calculated_status = 'Grace Period';
        } elseif ( $this->is_expired_condition() ) {
            $calculated_status = 'Expired';
        }

        // Cache the newly calculated status for future use.
        set_transient( 'smartwoo_status_' . $this->get_service_id(), $calculated_status, 5 * MINUTE_IN_SECONDS );

        return $calculated_status;
    }

    /**
     * Check if a service subscription is in its 'active' (perfectly current, paid-up) condition.
     * This method is an internal helper for status calculation.
     *
     * @return bool True if the subscription is in this specific active condition, false otherwise.
     */
    private function is_active_condition(): bool {
		if ( 'Active' === $this->get_status() ) {
			return true;
		}

        $end_date          = smartwoo_extract_only_date( $this->get_end_date() );
        $next_payment_date = smartwoo_extract_only_date( $this->get_next_payment_date() );
        $current_date      = smartwoo_extract_only_date( current_time( 'mysql' ) );

        return ( $next_payment_date > $current_date && $end_date > $current_date );
    }

    /**
     * Check if a service subscription is in its 'due for renewal' condition.
     * This method is an internal helper for status calculation.
     *
     * @return bool True if the subscription is in this specific 'due for renewal' condition, false otherwise.
     */
    private function is_due_for_renewal_condition(): bool {
		if ( 'Due for Renewal' === $this->get_status() ) {
			return true;
		}
        $end_date          = smartwoo_extract_only_date( $this->get_end_date() );
        $next_payment_date = smartwoo_extract_only_date( $this->get_next_payment_date() );
        $current_date      = smartwoo_extract_only_date( current_time( 'mysql' ) );
        return ( ( $next_payment_date <= $current_date && $end_date > $current_date ) || $end_date === $current_date );
    }

    /**
     * Check if a service is in its 'grace period' condition.
     * This method is an internal helper for status calculation.
     *
     * @return bool True if the subscription is in its grace period, false otherwise.
     */
    private function is_in_grace_period_condition(): bool {
		if ( 'Grace Period' === $this->get_status() ) {
			return true;
		}
        $end_date     = smartwoo_extract_only_date( $this->get_end_date() );
        $current_date = smartwoo_extract_only_date( current_time( 'mysql' ) );

        if ( $current_date >= $end_date ) {
            $product_id        = $this->get_product_id();
            $grace_period_date = smartwoo_get_grace_period_end_date( $product_id, $end_date );

            return ( ! empty( $grace_period_date ) && $current_date <= smartwoo_extract_only_date( $grace_period_date ) );
        }

        return false;
    }

    /**
     * Check if a service is in its 'expired' condition.
     * This method is an internal helper for status calculation.
     *
     * @return bool True if the subscription is expired, false otherwise.
     */
    private function is_expired_condition(): bool {
		if ( 'Expired' === $this->get_status() ) {
			return true;
		}
        $current_date    = current_time( 'Y-m-d' );
        $expiration_date = smartwoo_get_service_expiration_date( $this );

        // Check if the current date has passed the expiration date.
        return ( $current_date > $expiration_date );
    }

	/**
	 * Check if a service subscription is expiring tomorrow.
	 * This can be useful for sending reminders or triggering renewal prompts.
	 *
	 * @return bool True if the service expires tomorrow, false otherwise.
	 * @since 2.4.3
	 */
	public function is_expiring_tomorrow(): bool {
		$expiration_date = smartwoo_get_service_expiration_date( $this );
		$tomorrow_date   = date_i18n( 'Y-m-d', strtotime( '+1 day', current_time( 'timestamp' ) ) );

		return ( $expiration_date === $tomorrow_date );
	}

}
