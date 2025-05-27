<?php
/**
 * Mail sent after a client has successfuly ordered a new service.
 * 
 * @author Callistus Nwachukwu
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class Smartwoo_New_Service_Order extends SmartWoo_Service_Mails {
    /**
     * Mail ID
     * 
     * @var string $id The email id
     */
    public static $id = 'smartwoo_new_service_order';

    /**
     * @var SmartWoo_Service $service
     */
    protected $service;

    /**
     * Static instance
     * 
     * @var Smartwoo_New_Service_Order $instance
     */
    public static $instance = 'Smartwoo_New_Service_Order';

    /**
     * WooCommerce Order
     * 
     * @var WC_Order $order
     */
    protected $order;

    /**
     * Class constructor
     * 
     * @param SmartWoo_Service $service
     * @param SmartWoo_Order $order
     */
    public function __construct( $service, $order ) {
        $this->service      = $service;
        $this->order        = $order;
        self::$instance     = $this;
        $this->recipients   = apply_filters( 'smartwoo_admin_billing_email', get_option( 'smartwoo_billing_email' ) );
        parent::__construct( $this->get_subject(), self::get_template(), $service );

    }

    /**
     * Hook Runner
     */
    public static function init() {
        add_filter( 'smartwoo_service_mail_placeholders_description', array( __CLASS__, 'add_descriptions' ) );
        add_filter( 'smartwoo_service_email_placeholders', array( __CLASS__, 'add_order_placeholders' ) );
        add_filter( 'smartwoo_service_mail_placeholder_value', array( __CLASS__, 'placeholder_values' ), 20, 2 );
        add_action( 'smartwoo_new_service_purchase_complete', array( __CLASS__, 'send_mail' ), 999, 2 );
    }

    /**
     * Get the mail subject
     * 
     * @return string
     */
    public function get_subject() {
        return 'New Service Order';
    }

    /**
     * Template for notifying the admin about a new service order.
     */
    public static function get_template() {
        $message  = '<h1>New Service Order Notification</h1>';
        $message .= '<p>Hi admin,</p>';
        $message .= '<p>A new service subscription order has been placed. Please find the order details below:</p>';
        
        $message .= '<h3>Order Details</h3>';

        $message .= '{{order_items}}';

        $message .= '</tbody>';
        $message .= '</table>';

        // Payment details.
        $message .= '<h3>Payment Details</h3>';
        $message .= '<div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 20px;">';
        $message .= '<p><strong>Payment Method:</strong> {{payment_method}}</p>';
        $message .= '<p><strong>Amount Paid:</strong> {{payment_amount}}</p>';
        $message .= '<p><strong>Transaction ID:</strong> {{transaction_id}}</p>';
        $message .= '</div>';
        
        // Customer details.
        $message .= '<h3>Customer Details</h3>';
        $message .= '<div style="border: 1px solid #ccc; border-radius: 5px; padding: 10px; margin-top: 20px;">';
        $message .= '<p><strong>Name:</strong> {{client_fullname}}</p>';
        $message .= '<p><strong>Email:</strong> {{client_billing_email}}</p>';
        $message .= '<p><strong>Address:</strong> {{client_billing_address}}</p>';
        $message .= '</div>';
        
        $message .= '<p>Sent on <strong>' . smartwoo_check_and_format( current_time( 'mysql' ), true ) . '</strong>.</p>';

        return apply_filters( 'smartwoo_new_service_order_template', $message, self::$instance );
    }

    /**
     * Check whether we are either previewing or editing this template.
     * 
     * @return bool
     */
    public static function is_preview() {
        $is_edit    = isset( $_GET['tab'], $_GET['section'] ) && 'edit' === $_GET['section'];
        $is_preview = isset( $_GET['action'] ) && 'smartwoo_mail_preview' === $_GET['action'];
        $for_this   = isset( $_GET['temp_name'] ) && $_GET['temp_name'] === self::$id;

        return ( $is_preview || $is_edit ) && $for_this;
    }

    /**
     * Add order placeholder value
     */
    public static function add_order_placeholders( $placeholders ) {
        $mail_default = apply_filters( 'smartwoo_new_service_order_temp_placeholders', 
            array(
                '{{order_id}}',
                '{{order_date}}',
                '{{payment_method}}',
                '{{payment_amount}}',
                '{{transaction_id}}',
                '{{order_items}}'
            ) 
        );
        foreach ( $mail_default as $new ) {
            $placeholders[] = $new;
        }

        return $placeholders;

    }

    /**
     * Add Placeholders decription
     * 
     * @param array $main description The default description for the parent class.
     */
    public static function add_descriptions( $main ) {
        if ( ! self::is_preview() ) {
            return $main;
        }

        $to_add = array(
            '{{order_id}}'          => 'Order ID',
            '{{order_date}}'        => 'Order creation date',
            '{{payment_method}}'    => 'Payment gateway used for order payment',
            '{{payment_amount}}'    => 'Amount paid',
            '{{transaction_id}}'    => 'Transaction ID',
            '{{order_items}}'       => 'Order Items'
        );
        return array_merge( $main, $to_add );
    }

    /**
     * Provide placeholder values
     */
    public static function placeholder_values( $value, $placeholder ) {
        
        if ( ! isset( self::$instance->order ) ) {
            return $value;
        }
        switch( $placeholder ) {
            case '{{order_id}}':
                $value = self::$instance->order->get_id();
                break;
            case '{{order_date}}':
                $value = smartwoo_check_and_format( self::$instance->order->get_date_created(), true );
                break;
            case '{{payment_method}}':
                $value = self::$instance->order->get_payment_method_title();
                break;
            case '{{payment_amount}}':
                $value = smartwoo_price( self::$instance->order->get_total() );
                break;
            case '{{transaction_id}}':
                $value = self::$instance->order->get_transaction_id();
                break;
            case "{{order_items}}":
                $value = self::get_items();
        }


        return $value;
    }

    /**
     * Send email
     * 
     * @param string $invoice_id the invoice used for new order.
     * @param WC_Order $order The order object.
     */
    public static function send_mail( $invoice_id, $order ) {
        if ( ! smartwoo_check_if_configured( $order ) ) {
            return;
        }

        if ( 'completed' === $order->get_status() ) {
            return; // Mail already sent before processing.
        }

        $mail_is_enabled    = get_option( 'smartwoo_new_service_order', false );
        if ( apply_filters( 'smartwoo_new_service_order', $mail_is_enabled ) ) {
        
            $service = new SmartWoo_Service();
            $service->set_status( 'Pending' );
            $service->set_user_id( $order->get_user() ? $order->get_user()->ID: 0 );
            $service->set_product_id( $order->get_product()->get_id() );

            $self = new self( $service, $order );
            $self->send();
        }
    }

    /**
     * The the order items.
     */
    public static function get_items() {
        $order_items = self::$instance->order->get_items();
        
        // Initialize the table
        $items = '<table style="width: 80%; border-collapse: collapse;" align="center">';
        $items .= '<thead>
                    <tr>
                        <th style="height: 45px; text-align: left; border-top-left-radius: 9px; border-bottom: 1px solid #ccc; background-color: #ffe1f5; padding-left: 10px;">Item(s)</th>
                        <th style="height: 45px; text-align: left; border-bottom: 1px solid #ccc; background-color: #ffe1f5;">Qty</th>
                        <th style="height: 45px; text-align: left; border-bottom: 1px solid #ccc; background-color: #ffe1f5;">Unit Price</th>
                        <th style="height: 45px; text-align: left; border-top-right-radius: 9px; border-bottom: 1px solid #ccc; background-color: #ffe1f5;">Total</th>
                    </tr>
                </thead>';
        $items .= '<tbody>';

        // Add each item as a row
        foreach ( $order_items as $item ) {
            if ( ! is_a( $item->get_product(), 'SmartWoo_Product' ) && ! parent::is_preview() ) {
                continue;
            }

        $items .= '<tr>';
        $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( ( $item->get_meta( '_smartwoo_service_name' ) ? $item->get_meta( '_smartwoo_service_name' )  . ' - ' : '' ) . $item->get_name() ) . '</td>';
        $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . absint( $item->get_quantity() ) . '</td>';
        $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( smartwoo_price( $item->get_subtotal(), array( 'currency' => self::$instance->order->get_currency() ) ) ) . '</td>';
        $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( smartwoo_price( $item->get_total(), array( 'currency' => self::$instance->order->get_currency() ) ) ) . '</td>';
        $items .= '</tr>';
        }

        $items .= '</tbody>';
        $items .= '</table>';

        return $items;
    }

}

Smartwoo_New_Service_Order::init();