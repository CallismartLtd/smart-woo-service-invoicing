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



	// Getter methods
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

	/**
	 * Get the Service product name.
	 */
	public function get_product_name() {
		$product = new SmartWoo_Product( $this->getProductId() );
		$product_name = ! empty( $product ) ? $product->get_name() : 'N/A';
		return $product_name;
	}

	/** 
	 * Get the cost of service ( Excluding sign up fee)
	 */
	public function get_pricing() {
		$product = new SmartWoo_Product( $this->getProductId() );
		$price = ! empty( $product ) ? $product->get_price() : 0;
		return $price;
	}

	/**
	 * Get sign up fee
	 */
	public function get_sign_up_fee() {
		$product = new SmartWoo_Product( $this->getProductId() );
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
}
