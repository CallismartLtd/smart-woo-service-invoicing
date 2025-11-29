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
     * @var SmartWoo_Date_Helper $date_created The date the parent order was created
     */
    protected $date_created;

    /**
     * Date Paid
     * 
     * @var SmartWoo_Date_Helper  $date_paid The date the parent order was paid
     */
    protected $date_paid;

    /**
     * Billing Cycle
     * 
     * @var string $billing_cycle The billing cycle of the purchased product.
     */
    protected $billing_cycle = '';

    /**
     * The client/user
     * 
     * @var WC_Customer $user The owner of this order.
     */
    protected $user = null;

    /**
     * WooCommerce Order
     * 
     * @var WC_Order $order The WooCommerce order object.
     */
    protected $order;

    /**
     * WooCommerce Order Item
     * 
     * @var WC_Order_Item_Product $order_item
     */
    protected $order_item;

    
    const STATUS_AWAITING_PROCESSING = 'awaiting_processing';
    const STATUS_ACTIVE              = 'active';
    const STATUS_COMPLETED           = 'completed';
    const STATUS_CANCELED            = 'canceled';
    const STATUS_EXPIRED             = 'expired';
    const STATUS_FAILED              = 'failed';

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

        $date_created                   = $self->order->get_date_created();
        $date_paid                      = $self->order->get_date_paid();
        $self->orders['order_item_id']  = $order_item_id;
        $self->orders['order_id']       = $self->order->get_id();
        $self->sign_up_fee              = floatval( $self->order_item->get_meta( '_smartwoo_sign_up_fee' ) );
        $self->service_name             = $self->order_item->get_meta( '_smartwoo_service_name' ) ? $self->order_item->get_meta( '_smartwoo_service_name' ) : $self->order_item->get_meta( 'Service Name' ) ;
        $self->invoice_id               = $self->order_item->get_meta( '_sw_invoice_id' );
        $self->service_url              = $self->order_item->get_meta( '_smartwoo_service_url' );
        $self->date_created = $date_created
            ? SmartWoo_Date_Helper::create_from( $date_created->__toString() )->set_timezone()
            : null;

        $self->date_paid = $date_paid
            ? SmartWoo_Date_Helper::create_from( $date_paid->__toString() )->set_timezone()
            : null;

        $self->billing_cycle            = is_a( $self->order_item->get_product(), SmartWoo_Product::class ) ? $self->order_item->get_product()->get_billing_cycle() : '';
        $self->user                     = new WC_Customer( $self->order->get_user_id() );
    }

    /**
     * Check whether this order is valid.
     * Performs metadata checks on the parent order, to find our configuration data.
     * 
     * @return bool True when we find our meta data, false otherwise.
     */
    public function is_valid() {
        return smartwoo_check_if_configured( $this->order );
    }
    /**
     |------------------
     | SETTERS
     |------------------
     */
    
    /**
     * Set orders prop
     * 
     * @param array $data
     */
    public function set_orders( array $data ) {
        $this->orders   = array_map( 'absint', array_intersect_assoc( $this->orders, $data ) );
    }

    /**
     * Set service name
     * 
     * @param string $name
     */
    public function set_service_name( $name ) {
        $this->service_name = sanitize_text_field( wp_unslash( $name ) );
    }

    /**
     * Set invoice ID
     * 
     * @param string $invoice_id The public invoice ID
     */
    public function set_invoice_id( $invoice_id ){
        $this->invoice_id = sanitize_text_field( wp_unslash( $invoice_id ) );
    }

    /**
     * Set service URL
     * 
     * @param string $service_url
     */
    public function set_service_url( $service_url ) {
        $this->service_url = sanitize_url( $service_url );
    }

    /**
     * Set Sign-Up fee
     * 
     * @param int|float
     */
    public function set_sign_up_fee( $fee ) {
        $this->sign_up_fee = round( $fee, 2 );
    }

    /**
     * Set Date created
     * 
     * @param string $dateTimeString
     */
    public function set_date_created( $dateTimeString ) {
        $this->date_created = new SmartWoo_Date_Helper( $dateTimeString );
    }

    /**
     * Set user
     * 
     * @param WC_Customer|WP_User|int $user
     */
    public function set_user( $user ) {
        if ( is_a( $user, 'WC_Customer' ) ) {
            $this->user = $user;
        } elseif ( is_numeric( $user ) || is_a( $user, 'WP_User' ) ) {
            $this->user = new WC_Customer( is_object( $user ) ? $user->ID : absint( $user ) );
        }
    }

    /**
     * Set parent Order
     * 
     * @param WC_Order $order
     */
    public function set_parent_order( WC_Order $order ) {
        $this->order = $order;
    }

    /**
     * Set order item property, this is the fundamental property that makes a SmartWoo_Order Object.
     * 
     * @param WC_Order_Item_Product $item
     */
    public function set_order_item( WC_Order_Item_Product $order_item ) {
        $this->order_item = $order_item;
    }

    /**
     * Set date paid
     */
    public function set_date_paid( $dateTimeString ){
        $this->date_paid = new SmartWoo_Date_Helper( $dateTimeString );
    }

    /**
     * Set billing cycle
     * 
     * @param string $value
     */
    public function set_billing_cycle( $value ) {
        $this->billing_cycle = sanitize_text_field( wp_unslash( $value ) );
    }


    /**
     |--------------------
     | GETTERS
     |--------------------
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
     * -possible values `raw`           = SmartWoo_Date_Helper (default)
     *                  `plain`         = Formatted as plain text according to the site's date and time format.
     *                  `date_format`   = Returned in Y-m-d  format
     * 
     * @return SmartWoo_Date_Helper|string
     */
    public function get_date_created() {
        return $this->date_created;
    }

    /**
     * Get date paid
     * 
     * @param string $context The context in which the date is returned
     * -possible values `raw`           = SmartWoo_Date_Helper (default)
     *                  `plain`         = Formatted as plain text according to the site's date and time format.
     *                  `date_format`   = Returned in Y-m-d  format
     * 
     * @return SmartWoo_Date_Helper|string
     */
    public function get_date_paid() {
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
     * 
     * @return WC_Customer
     */
    public function get_user() {
        return $this->user;
    }

    /**
     * Get user ID
     * 
     * @return int $user_id
     */
    public function get_user_id() {
        return $this->user ? $this->user->get_id() : 0;
    }

    /**
     * Get the parent Order
     * @return WC_Order|false $order
     */
    public function get_parent_order() {
        return $this->order;
    }

    /**
     * Get order currency
     * 
     * @return string $currency
     */
    public function get_currency() {
        return $this->get_parent_order()->get_currency();
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
     * 
     * @return string
     */
    public function get_product_name() {
        return $this->get_product() ? $this->get_product()->get_name() : 'N/A';
    }

    /**
     * Get product ID
     * 
     * @return int
     */
    public function get_product_id() {
        return $this->get_product() ? $this->get_product()->get_id() : 0;
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
     * Get payment url
     * 
     * @return string
     */
    public function get_payment_url() {
        return $this->order->get_checkout_payment_url();
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
        return $this->order->get_billing_email();
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
        $parent = wc_get_order( $this->get_order_id() );
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
     * 
     * @param WC_Order $parent The parent order
    */
    public function has_pending_item( WC_Order $parent ) {
        $has_pending_item = false;
        foreach( $parent->get_items() as $item ) {
            
            if ( ! is_a( $item->get_product(), SmartWoo_Product::class ) ) {
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
        $status = 'awaiting payment';
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
        if ( ! is_a( $order_item, 'WC_Order_Item_Product' ) || ! is_a( $order_item->get_product(), SmartWoo_Product::class ) ) {
            return $formatted_meta;
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
     * Ajax callback for order table actions in admin area.
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

            
        }
        wp_send_json_success( $response );
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
     * Get all service orders belonging to a specific user.
     *
     * @param array $args An associative array of key => value arguments
     *
     * @return self[] Array of SmartWoo_Order objects.
     */
    public static function get_user_orders( $args = array() ) {
        global $wpdb;

        $default_args = array(
            'customer_id'   => get_current_user_id(),
            'page'          => 1,
            'limit'         => 25,
            'status'        => ''
        );

        $parsed_args    = wp_parse_args( $args, $default_args );
        $limit          = intval( $parsed_args['limit'] );
        $offset         = ( intval( $parsed_args['page'] ) - 1 ) * $limit;

        $orders_table           = $wpdb->prefix . 'wc_orders';
        $order_items_table      = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table   = $wpdb->prefix . 'woocommerce_order_itemmeta';

        $base_query = 
        "SELECT DISTINCT oim.order_item_id
            FROM {$order_itemmeta_table} AS oim
            INNER JOIN {$order_items_table} AS oi
                ON oim.order_item_id = oi.order_item_id
            INNER JOIN {$orders_table} AS o
                ON oi.order_id = o.id
            WHERE o.customer_id = %d
            AND (
                    oim.meta_key = %s OR 
                    oim.meta_key = %s OR 
                    oim.meta_key = %s OR 
                    oim.meta_key = %s OR 
                    oim.meta_key = %s
            )";
        $param = array(
            absint( $parsed_args['customer_id'] ),
            '_smartwoo_sign_up_fee',
            '_smartwoo_service_name',
            '_smartwoo_service_url',
            'Service Name', // Backward compatibility
            'Service URL',  // Backward compatibility
        );

        if ( ! empty( $parsed_args['status'] ) ) {
            $status = strpos( $parsed_args['status'], 'wc-' ) === 0 ? $parsed_args['status'] : 'wc-' . $parsed_args['status'];
            $base_query .= " AND o.status = %s";
            $param[] = $status;
        }

        $base_query .= " ORDER BY `order_item_id` DESC";

        if ( $limit > 0 ) {
            $base_query .= " LIMIT %d OFFSET %d";
            $param[]    = $limit;
            $param[]    = $offset;
        }
        
        $query = $wpdb->prepare( $base_query, $param ); // phpcs:ignore WordPress.DB

        $data    = array();
        $results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB

        if ( ! empty( $results ) ) {
            foreach ( $results as $result ) {
                $self = self::convert_to_self( $result );
                if ( $self ) {
                    $data[] = $self;
                }
            }
        }

        return $data;
    }

    /**
     * Get the total count of service orders belonging to a specific user.
     * This method mirrors the filtering logic of get_user_orders but returns a count.
     *
     * @param array $args {
     * Optional arguments.
     *
     * @type int $customer_id User ID. Default current user ID.
     * @type string $status    Order status to include. Default empty (all statuses for relevant items).
     * }
     * @return int The total number of SmartWoo_Order items (service orders).
     */
    public static function count_user_orders( $args = array() ) {
        global $wpdb;

        $default_args = array(
            'customer_id' => get_current_user_id(),
            'status'      => '', // Empty string means all statuses
        );

        $parsed_args = wp_parse_args( $args, $default_args );

        $orders_table         = $wpdb->prefix . 'wc_orders';
        $order_items_table    = $wpdb->prefix . 'woocommerce_order_items';
        $order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';

        // Base SELECT for counting distinct order_item_ids based on your logic
        $base_query = "
            SELECT COUNT(DISTINCT oim.order_item_id)
            FROM {$order_itemmeta_table} AS oim
            INNER JOIN {$order_items_table} AS oi
                ON oim.order_item_id = oi.order_item_id
            INNER JOIN {$orders_table} AS o
                ON oi.order_id = o.id
            WHERE o.customer_id = %d
            AND (
                oim.meta_key = %s OR
                oim.meta_key = %s OR
                oim.meta_key = %s OR
                oim.meta_key = %s OR
                oim.meta_key = %s
            )
        ";

        // Parameters for the base query
        $param = array(
            absint( $parsed_args['customer_id'] ),
            '_smartwoo_sign_up_fee',
            '_smartwoo_service_name',
            '_smartwoo_service_url',
            'Service Name', // Backward compatibility
            'Service URL',  // Backward compatibility
        );

        // Add status condition if provided, mirroring your get_user_orders
        if ( ! empty( $parsed_args['status'] ) ) {
            $status = strpos( $parsed_args['status'], 'wc-' ) === 0 ? $parsed_args['status'] : 'wc-' . $parsed_args['status'];
            $base_query .= " AND o.status = %s";
            $param[] = $status;
        }

        // Prepare the SQL query with all parameters
        $query = $wpdb->prepare( $base_query, ...$param ); // phpcs:ignore WordPress.DB

        // Get the count result
        $count = $wpdb->get_var( $query );// phpcs:ignore WordPress.DB

        return (int) $count;
    }

    /**
     * Get all Smart Woo Orders
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
     * Get orders that are awaiting payment.
     * 
     * @param array $args Array of arguments.
     * @return self[]
     */
    public static function get_awaiting_processing_orders() {
        $data = array();
        $query_args = array(
            'limit'  => 10,
            'status' => 'processing',
        );

        if ( smartwoo_is_frontend() ) {
            $query_args['customer'] = get_current_user_id();
        }

        $wc_orders = wc_get_orders( $query_args );

        foreach ( $wc_orders as $wc_order ) {
            $smartwoo_orders = self::extract_items( $wc_order );
            foreach ( $smartwoo_orders as $order ) {
                if ( 'awaiting processing' === $order->get_status() ) {
                    $data[] = $order;
                }
            }
        }

        return $data;
    }

    /**
     * Count orders awaiting processing.
     * 
     * @return int
     */
    public static function count_awaiting_processing() {
        $count	= 0;

		$args = array(
			'limit'		=> -1,
			'status'	=> 'processing',
		);

		if ( smartwoo_is_frontend() ) {
			$args['customer'] = get_current_user_id();
		}
	
		$wc_orders = wc_get_orders( $args );

        foreach ( $wc_orders as $wc_order ) {
            $smartwoo_orders = self::extract_items( $wc_order );
            foreach( $smartwoo_orders as $order ) {
                if ( 'awaiting processing' === $order->get_status() ) {
                    $count++;
                }
            }
        }

        return $count;
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
    |-----------------
    | UTILITY METHODS
    |----------------- 
    */

    /**
     * Get all valid items from a WC_Order.
     * 
     * @param WC_Order $order The order object.
     * @return self[]
     */
    public static function extract_items( WC_Order $order ) {
        $self = array();
        foreach( $order->get_items() as $item_id => $item ) {
            if ( ! is_a( $item->get_product(), SmartWoo_Product::class ) ) {
                continue;
            }

            $order = self::convert_to_self( array( 'order_item_id' => $item_id ) );
            if ( ! $order ) {
                continue;
            }
            $self[] = $order;
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