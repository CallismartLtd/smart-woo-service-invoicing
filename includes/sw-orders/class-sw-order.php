<?php
/**
 * The Smart Woo Order class file
 * 
 * @author Callistus
 * @package SmartWoo\classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Smart Woo Order class handles all service product orders through order items.
 * -An item in a WooCommerce Order becomes a Smart Woo Order if the item has been configured during checkout.
 * 
 * @since 2.3.0
 */
class SmartWoo_Order {
    /**
     * All Smart Woo Orders
     * 
     * @var array $orders An array of order_item_id and order_id
     */
    protected $orders = array(
        'order_item_id' => 0,
        'order_id'      => 0
    );

    /**
     * Service Name
     * 
     * @var string $service_name
     */
    protected $service_name = '';

    /**
     * Service URL
     * 
     * @var string $service_url
     */
    protected $service_url = '';

    /**
     * Sign-up Fee
     * 
     * @var float $sign_up_fee
     */
    protected $sign_up_fee = 0.0;

    /**
     * Date Created
     * 
     * @var WC_DateTime $date_created The date the parent order was created
     */
    protected $date_created;

    /**
     * Date Paid
     * 
     * @var WC_DateTime  $date_paid The date the parent order was paid
     */
    protected $date_paid;

    /**
     * Billing Cycle
     * 
     * @var string $billing_cycle The billing cycle of the purchased product.
     */
    protected $billing_cycle = '';

    /**
     * The client
     * 
     * @var WC_Customer $client The owner of this order.
     */
    protected $client = null;

    /**
     * WooCommerce Order
     * 
     * @var WC_Order $order The WooCommerce order object.
     */
    protected $order;

    /**
     * WooCommerce Order Item
     * 
     * @var WC_Order_Item $order_item
     */
    protected $order_item;

    /**
     * Class constructor
     * 
     * @param int $order_item_id
     */
    public function __construct( $order_item_id = 0 ){
        if ( ! empty( $order_item_id ) ) {
            self::set_props( $order_item_id, $this );
        }
    }


    /**
     * Set up the object property.
     * 
     * @param int $order_item_id
     * @param self $self Smart Woo Order Object
     * @throws Exception Invalid order item.
     */
    private static function set_props( $order_item_id, &$self ) {
        try {
            $self->order_item = new WC_Order_Item_Product( $order_item_id );
        } catch ( Exception $e) {}

        if ( ! $self->order_item ) {
            return $self;
        }

        $self->order    = $self->order_item->get_order();

        if ( ! $self->is_valid() ) {
            return $self;
        }

        $self->orders['order_item_id']  = $order_item_id;
        $self->orders['order_id']       = $self->order->get_id();
        $self->sign_up_fee              = floatval( $self->order_item->get_meta( '_smartwoo_sign_up_fee' ) );
        $self->service_name             = $self->order_item->get_meta( '_smartwoo_service_name' );
        $self->service_url              = $self->order_item->get_meta( '_smartwoo_service_url' );
        $self->date_created             = $self->order->get_date_created();
        $self->date_paid                = $self->order->get_date_paid();
        $self->billing_cycle            = is_a( $self->order_item->get_product(), 'SmartWoo_Product' ) ? $self->order_item->get_product()->get_billing_cycle() : '';
        $self->client                   = new WC_Customer( $self->order->get_user_id() );
    }

    /**
     * Check whether this order is valid
     * 
     * @return bool True when we find our meta data, false otherwise.
     */
    public function is_valid() {
        return smartwoo_check_if_configured( $this->order );
    }

    /*----------------------------------------
    | GETTERS
    |-----------------------------------------
    */

    /**
     * Get Item ID
     * 
     * @return int $order_item_id
     */
    public function get_id() {
        return $this->orders['order_item_id'];
    }

    /**
     * Get Parent order ID
     * 
     * @return int $order_id
     */
    public function get_order_id() {
        return $this->orders['order_id'];
    }

    /**
     * Get the service name.
     * 
     * @return string $service_name
     */
    public function get_service_name() {
        return $this->service_name;
    }

    /**
     * Get the service url
     * 
     * @return string $service_url
     */
    public function get_service_url() {
        return $this->service_url;
    }

    /**
     * Get the Sign-up Fee
     * 
     * @return float $sign_up_fee
     */
    public function get_sign_up_fee() {
        return $this->sign_up_fee;
    }

    /**
     * Get date created
     * 
     * @param string $context The context in which the date is returned
     * -possible values `raw`           = WC_Datetime (default)
     *                  `plain`         = Formatted as plain text according to the site's date and time format.
     *                  `date_format`   = Returned in Y-m-d  format
     * 
     * @return WC_Dateime|string
     */
    public function get_date_created( $context = 'raw' ) {
        if ( 'plain' === $context ) {
            return smartwoo_check_and_format( $this->date_created, true );
        } elseif ( 'date_format' === $context ) {
            return date_i18n( 'Y-m-d', strtotime( $this->date_created ) );
        }
        return $this->date_created;

    }

    /**
     * Get date paid
     * 
     * @param string $context The context in which the date is returned
     * -possible values `raw`           = WC_Datetime (default)
     *                  `plain`         = Formatted as plain text according to the site's date and time format.
     *                  `date_format`   = Returned in Y-m-d  format
     * 
     * @return WC_Dateime|string
     */
    public function get_date_paid( $context = 'raw' ) {
        if ( 'plain' === $context ) {
            return smartwoo_check_and_format( $this->date_paid, true );
        } elseif ( 'date_format' === $context ) {
            return date_i18n( 'Y-m-d', strtotime( $this->date_paid ) );
        }
        return $this->date_paid;

    }

    /**
     * Get Billing cycle
     * 
     * @return string $billing
     */
    public function get_billing_cycle() {
        return $this->billing_cycle;
    }

    /**
     * Get the client/user associated with the order.
     */
    public function get_user() {
        return $this->client;
    }

    /**
     * Get the parent Order
     * @return WC_Order|false $order
     */
    public function get_parent_order() {
        return $this->order;
    }

    /**
     * Get the order Item object.
     * 
     * @return WC_Order_Item_Product $order_item
     */
    public function get_order_item() {
        return $this->order_item;
    }

    /**
     * Get the status of an order, we should add a `completed` string value to the order_item meta after processing.
     */
    public function get_status() {
        $status = 'awaiting payment';
        if ( 'completed' !== $this->order_item->get_meta( 'status' ) ) {
            $status = 'awaiting processing';
        } elseif ( $this->order_item->get_meta( 'Service Name' ) ) { // Backwd comp.
            if ( $this->get_order() && 'processing' === $this->get_order()->get_status() ){
                $status = 'awaiting processing';
            }elseif( $this->get_order() && 'complete' === $this->get_order()->get_status() ) {
                $status = 'processed';
            }
        }
        return 'processed';
    }

    /**
    |------------------------
    | CRUD METHODS
    |------------------------
    | Method to create order meta is not implemented, 
    | all interactions with the parent order are synced by default.
    */
    
    /**
     * Delete an order item
     * 
     * @return bool
     */
    public function delete() {
        try {
            return wc_delete_order_item( $this->get_id() );
        } catch ( Exception $e ) {}
        return false;
    }

    /**
     * Update a property and save to the database.
     * 
     * @param string $name Property name.
     * @param mixed $value The new value
     */
    public function update_prop( $name, $value ) {
        $changeables = array( 
            '_smartwoo_service_name', 
            '_smartwoo_service_url', 
            'Service Name', // Backwd compct.
            'Service URL', // Backwd compct.
        );

        if ( ! in_array( $name, $changeables, true ) ) {
            return false;
        }
        try {
            return wc_update_order_item_meta( $this->get_id(), $name, $value  );
        } catch( Exception $e ){}
        return false;
    }

    /**
     * Get all Service Orders
     * 
     * @param int $page The current page(for pagination).
     * @param int $limit The query limit per page.
     * @return self[] An array containing SmartWoo_Order objects
     */
    public static function get_all( $page = 1, $limit = 20 ) {
        global $wpdb;
        // Calculate the offset.
		$offset = ( $page - 1 ) * $limit;

        $query = $wpdb->prepare( 
            "SELECT DISTINCT `order_item_id` FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE 
            `meta_key` = %s OR 
            `meta_key` = %s OR 
            `meta_key` = %s OR 
            `meta_key` = %s OR 
            `meta_key` = %s LIMIT %d OFFSET %d",
            "_smartwoo_sign_up_fee",
            "_smartwoo_service_name", 
            "_smartwoo_service_url",
            "Service Name", // Backwd compt.
            "Service URL", // Backwd compt.
            absint( $limit ),
            $offset
        );

        $data = array();
        $results = $wpdb->get_results( $query, ARRAY_A );
        if ( ! empty( $results ) ) {
            foreach( $results as $result ) {
                $self   = self::convert_to_self( $result );
                if ( ! $self ) {
                    continue;
                }

                $data[] = $self;
            }
        }
        return $data;
    }

    /**
     * Helper method to convert a database result to an object of this class.
     * 
     * @param array $result
     */
    private  static function convert_to_self( $result ) {
        if ( isset( $result['order_item_id'] ) ) {
            $self = new self( absint( $result['order_item_id'] ) );
            if ( $self->is_valid() ) {
                return $self;
            }
        }
        return false;
    }
}