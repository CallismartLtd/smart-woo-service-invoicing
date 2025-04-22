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
     * Invoice ID
     * 
     * @var string $invoice_id
     */
    protected $invoice_id = '';

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
     * Action hook runner
     */
    public static function listen() {
        add_action( 'smartwoo_order_table_actions', array( __CLASS__, 'ajax_table_callback' ), 20, 2 );
        add_filter( 'smartwoo_allowed_table_actions', array( __CLASS__, 'register_table_actions' ), 99 );
        add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( __CLASS__, 'display_meta' ), 10, 2);
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
        $self->service_name             = $self->order_item->get_meta( '_smartwoo_service_name' ) ? $self->order_item->get_meta( '_smartwoo_service_name' ) : $self->order_item->get_meta( 'Service Name' ) ;
        $self->invoice_id               = $self->order_item->get_meta( '_sw_invoice_id' );
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
     * Get the invoice ID
     * 
     * @return string $invoice_id
     */
    public function get_invoice_id() {
        return $this->invoice_id;
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
     * Get the product associated with this order
     * 
     * @return SmartWoo_Product|false
     */
    public function get_product() {
        return $this->order_item->get_product();
    }

    /**
     * Get product name
     */
    public function get_product_name() {
        return $this->get_product() ? $this->get_product()->get_name() : 'N/A';
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
     * Get order quantity
     * 
     * @return int
     */
    public function get_quantity() {
        return $this->order_item->get_quantity();
    }

    /**
     * Get the price
     * 
     * @return float
     */
    public function get_price() {
        return $this->order_item->get_subtotal();
    }

    /**
     * Get total
     */
    public function get_total() {
        return $this->order_item->get_total();
    }

    /**
     * Get payment method
     * 
     * @return string
     */
    public function get_payment_method() {
        return $this->order->get_payment_method();
    }

    /**
     * Get payment method title
     * 
     * @return string
     */
    public function get_payment_method_title() {
        return $this->order->get_payment_method_title();
    }

    /**
     * Get the transaction ID
     * 
     * @return string
     */
    public function get_transaction_id() {
        return $this->order->get_transaction_id();
    }

    /**
     * Get billing Email
     */
    public function get_billing_email() {
        return $order->get_billing_email();
    }

    
    /*
    |-------------------------
    | UTILITY METHODS
    |-------------------------
    */

    /**
     * Check whether this order has already been processed.
     */
    public function is_processed() {
        return 'processed' === $this->get_status();
    }

    /**
     * Mark an order as completed - The method should be called when the order processing 
     * is completed
     * 
     * @return bool
     */
    public function processing_complete() {
        $this->order_item->update_meta_data( '_smartwoo_order_item_status', 'complete' );
        $this->order_item->save();
        delete_transient( 'smartwoo_count_unprocessed_orders' );
        return $this->maybe_update_parent_order();
    }

    /**
     * Check whether the parent order still has items not processed, and update
     * the order status when all items has been processed.
     */
    public function maybe_update_parent_order() {
        $has_pending_item   = $this->has_pending_item( $parent );
        if ( ! $has_pending_item ) {
            $parent->set_status( 'completed' );
            $parent->save();
        }

        return !$has_pending_item;
    }

    /**
     * Check whether the parent order still has items not processed, and updates
     * the order status when all items has been processed.
    */
    public function has_pending_item( &$parent ) {
        $parent = wc_get_order( $this->get_order_id() );
        $has_pending_item = false;
        foreach( $parent->get_items() as $item ) {
            if ( ! is_a( $item->get_product(), 'SmartWoo_Product' ) ) {
                continue;
            }

            if ( ! $item->get_meta( '_smartwoo_order_item_status' ) || 'complete' !==  $item->get_meta( '_smartwoo_order_item_status' ) ) {
                $has_pending_item = true;
                break;
            }
        }

        return $has_pending_item;
    }

    /**
     * Get the status of an order, we should add a `completed` string value to the order_item meta after processing.
     */
    public function get_status() {
        $status = 'awaiting payment'; // Assuming order is not paid;
        $parent_order_status =  $this->get_parent_order() ? $this->get_parent_order()->get_status() : '';
        if ( in_array( $parent_order_status, wc_get_is_pending_statuses(), true ) ) {
            return $status;
        } elseif ( in_array( $parent_order_status, wc_get_is_paid_statuses(), true ) ) {
            if ( 'complete' === $this->order_item->get_meta( '_smartwoo_order_item_status' ) ) {
                $status = 'processed';
            } elseif ( $this->order_item->get_meta( 'Service Name' ) ) { // Backwd comp.
                if ( $this->get_parent_order() && 'processing' === $this->get_parent_order()->get_status() ){
                    $status = 'awaiting processing';
                } elseif( $this->get_parent_order() && 'complete' === $this->get_parent_order()->get_status() ) {
                    $status = 'processed';
                }
            } else {
                $status = 'awaiting processing';
            }
        }

        return $status;
    }

    /**
     * Control the display of internal meta data on Order page and tables.
     * 
     * @param array $formatted_meta All formated item meta data.
     * @param WC_Order_Item $order_item The Order item base class.
     */
    public static function display_meta( $formatted_meta, $order_item ){
        if ( ! is_a( $order_item->get_product(), 'SmartWoo_Product' ) ) {
            $formatted_meta;
        }
        
        foreach( $formatted_meta as $id => &$data ) {
            if ( '_smartwoo_sign_up_fee' === $data->key ) {
                $data->display_value = smartwoo_price( $data->value, array( 'currency' => $order_item->get_order()->get_currency() ) );
                $data->display_key = 'Sign-up Fee';
            } elseif ( '_smartwoo_service_name' === $data->key ) {
                $data->display_key = 'Service Name';
            } elseif ( '_smartwoo_service_url' === $data->key ) {
                $data->display_key = 'Service URL';
            } else {
                unset( $formatted_meta[$id] );
            }
        }

        return $formatted_meta;
    }

    /**
     * Ajax callback for order table actions
     * 
     * @param string $selected_action The selected action.
     * @param mixed $data The data to be processed.
     */
    public static function ajax_table_callback( $selected_action, $data ) {
        if ( ! is_array( $data ) ) {
            $data = (array) $data;
        }
        $response = array( 'message' => 'Invalid action' );
        foreach ( $data as $id ) {
            $order = new self( $id );
            if ( ! $order->is_valid() ) {
                continue;
            }

            switch( $selected_action ) {
                case 'delete':
                    $response['message'] = $order->delete() ? 'Order deleted' : 'Failed to delete order';
                    break;
                case 'complete':
                    $response['message'] = $order->processing_complete() ? 'Order processed' : 'Failed to process order';
                    break;
            }

            wp_send_json_success( $response );
        }
    }

    /**
     * Register list of allowed sw-table actions.
     * 
     * @param array $actions The list of actions in the filter.
     */
    public static function register_table_actions( $actions ) {
        $actions[] = 'delete';
        $actions[] = 'complete';
        
        return $actions;
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
        delete_transient( 'smartwoo_count_unprocessed_orders' );
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
     * Get orders for a given user
     * 
     * @param array $args
     */
    public static function get_user_orders( $args = array() ) {
        $default_args = array(
            'customer'	=> get_current_user_id(),
            'status'	=> 'processing'
        );

        $parsed_args = wp_parse_args( $args, $default_args );
        
        $orders = wc_get_orders( $parsed_args );
    
        if ( empty( $orders ) ) {
            return array();
        }
    
        $order_item_ids		= [];
        $smartwoo_orders	= [];
    
        foreach ( $orders as $order ) {
            if ( empty( $order->get_items() ) ) {
                continue;
            }
            
            foreach ( $order->get_items() as $item_id => $item ) {
                $order_item_ids[]['order_item_id'] = $item_id;
            }
            
        }
    
        if ( ! empty( $order_item_ids )  ) {
            foreach( $order_item_ids as $item ) {
                $self = self::convert_to_self( $item );
                if ( ! $self ) {
                    continue;
                }
                $smartwoo_orders[] = $self;
            }
        }

        return $smartwoo_orders;
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
            "SELECT DISTINCT `order_item_id` FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE 
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
        $results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive, query is prepared
        if ( ! empty( $results ) ) {
            usort( $results, function( $a, $b ) {
                return $b['order_item_id'] <=> $a['order_item_id'];
            });
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
     * Count All Service Orders
     * 
     * @return int The total number of service orders.
     */
    public static function count_all() {
        global $wpdb;
        $query = $wpdb->prepare( 
            "SELECT COUNT(DISTINCT `order_item_id`) FROM `{$wpdb->prefix}woocommerce_order_itemmeta` WHERE
            `meta_key` = %s OR
            `meta_key` = %s OR
            `meta_key` = %s OR
            `meta_key` = %s OR
            `meta_key` = %s",
            "_smartwoo_sign_up_fee",
            "_smartwoo_service_name",
            "_smartwoo_service_url",
            "Service Name", // Backwd compt.
            "Service URL" // Backwd compt.
        );

        return $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- False positive, query is prepared
    }

    /**
     * Recommended way to get a SmartWoo_Order
     * 
     * @param int $id The order item id.
     * @return self
     */
    public static function get_order( $id ) {
        $self = new self( $id );

        if ( ! $self->is_valid() ) {
            return false;
        }

        return $self;
    }

    /**
     * Get all valid items from a WC_Order.
     * 
     * @param WC_Order $order The order object.
     * @return self[]
     */
    public static function extract_items( WC_Order $order ) {
        $self = array();
        foreach( $order->get_items() as $item_id => $item ) {
            if ( ! is_a( $item->get_product(), 'SmartWoo_Product' ) ) {
                continue;
            }
            $self[] = self::convert_to_self( array( 'order_item_id' => $item_id ) );
        }

        return $self;
    }

    /**
     * Helper method to convert a database result to an object of this class.
     * 
     * @param array $result
     */
    private static function convert_to_self( $result ) {
        if ( isset( $result['order_item_id'] ) ) {
            $self = new self( absint( $result['order_item_id'] ) );
            if ( $self->is_valid() ) {
                return $self;
            }
        }
        return false;
    }
}

SmartWoo_Order::listen();