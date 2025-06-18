<?php
/**
 * Mail sent after a client has successfuly ordered a new service.
 * 
 * @author Callistus Nwachukwu
 * @package SmartWoo\Mails
 */

defined( 'ABSPATH' ) || exit;

class Smartwoo_New_Service_Order extends SmartWoo_Mail {
    /**
     * Mail ID
     * 
     * @var string $id The email id
     */
    public static $id = 'smartwoo_new_service_order';

    /**
     * Static instance
     * 
     * @var Smartwoo_New_Service_Order $instance
     */
    public static $instance = 'Smartwoo_New_Service_Order';

    /**
     * Smart Woo Orders.
     * 
     * @var SmartWoo_Order[] $orders
     */
    protected $orders = array();

    /**
     * Class constructor
     * 
     * @param SmartWoo_Order[] $orders
     */
    public function __construct( $orders ) {
        $this->orders   = $orders;
        self::$instance = $this;
        parent::__construct( $this->get_subject(), self::get_formated_body(), self::get_recipients() );

    }

    /**
     * Get email recipients
     */
    public static function get_recipients() {
        return apply_filters( 'smartwoo_admin_billing_email', get_option( 'smartwoo_billing_email' ) );
    }

    /**
     * Hook Runner
     */
    public static function init() {
        // add_filter( 'smartwoo_service_mail_placeholders_description', array( __CLASS__, 'add_descriptions' ) );
        // add_filter( 'smartwoo_service_email_placeholders', array( __CLASS__, 'add_order_placeholders' ) );
        // add_filter( 'smartwoo_service_mail_placeholder_value', array( __CLASS__, 'placeholder_values' ), 20, 2 );
        // add_filter( 'smartwoo_register_email_templates', array( __CLASS__, 'register_template' ) );

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
     * Get formated email body
     */
    public static function get_formated_body() {
        $body = self::get_template();
        $sustitutes = array();
        
        foreach( self::get_placeholders() as $placeholder ) {
            $sustitutes[$placeholder] = self::placeholder_values( $placeholder );
        }

        $body = str_replace( array_keys( $sustitutes), $sustitutes, $body );
        return $body;
    }

    /**
     * Email template to notify admin.
     */
    public static function get_template() {
        $message  = '<h1>New Service Order Notification</h1>';
        $message .= '<p>Hi <strong>{{business_name}}</strong>,</p>';
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
        $is_edit    = ( ! empty( smartwoo_get_query_param( 'section') ) ) && 'edit' === smartwoo_get_query_param( 'section');
        $is_preview = 'smartwoo_mail_preview' === smartwoo_get_query_param( 'action' );
        $for_this   = smartwoo_get_query_param( 'temp_name' ) === self::$id;

        return ( $is_preview || $is_edit ) && $for_this;
    }

    /**
     * Register order placeholders
     */
    public static function get_placeholders() {
        return apply_filters( 'smartwoo_new_service_order_temp_placeholders', 
            array(
                '{{order_id}}',
                '{{order_date}}',
                '{{payment_method}}',
                '{{payment_amount}}',
                '{{transaction_id}}',
                '{{order_items}}',
                '{{business_name}}',
                '{{client_fullname}}',
                '{{client_billing_email}}',
                '{{client_billing_address}}'
            ) 
        );

    }

    /**
     * Add Placeholder descriptions.
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
            '{{order_items}}'       => 'Order Items',
            '{{business_name}}'     => 'Your business name',
            '{{client_fullname}}'   => 'Client\'s full name',
            '{{client_billing_email}}'  => 'Client\'s billing email',
            '{{client_billing_address}}'    => 'Client\'s billing address'
        );
        return array_merge( $main, $to_add );
    }

    /**
     * Provide placeholder values
     */
    public static function placeholder_values( $placeholder ) {
        $value = $placeholder;

        if ( ! isset( self::$instance->orders[0] ) ) {
            return $value;
        }
        
        $order = self::$instance->orders[0];
        // pretty_print( $order->get_order_item()->get_product() ); exit;

        switch( $placeholder ) {
            case '{{order_id}}':
                $value = $order->get_parent_order()->get_id();
                break;
            case '{{order_date}}':
                $value = smartwoo_check_and_format( $order->get_date_created()->format( 'Y-m-d h' ), true );
                break;
            case '{{payment_method}}':
                $value = $order->get_payment_method_title();
                break;
            case '{{payment_amount}}':
                $value = smartwoo_price( $order->get_total(), array( 'currency' => $order->get_currency() ) );
                break;
            case '{{transaction_id}}':
                $value = $order->get_transaction_id();
                break;
            case '{{business_name}}':
                $value = get_option( 'smartwoo_business_name', get_bloginfo( 'name' ) );
                break;
            case '{{client_billing_address}}':
                $value = smartwoo_get_user_billing_address( $order->get_user()->get_id() );
                break;
            case '{{client_billing_email}}':
                $value = smartwoo_get_client_billing_email( $order->get_user() );
                break;
            case '{{client_fullname}}':
                $value = $order->get_user()->get_billing_first_name() . ' ' . $order->get_user()->get_billing_last_name();
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
     * @param SmartWoo_Order[] $order Smart Woo Order object.
     */
    public static function send_mail( $orders ) {
        if ( ! is_array( $orders ) ) {
            $orders = array( $orders );
        }

        $mail_is_enabled    = get_option( 'smartwoo_new_service_order', false );

        if ( apply_filters( 'smartwoo_new_service_order', $mail_is_enabled ) ) {
            $self = new self( $orders );
            $self->send();
        }
    }

    /**
     * The the order items.
     */
    public static function get_items() {        
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
        foreach ( self::$instance->orders as $order ) {

            $items .= '<tr>';
            $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( $order->get_service_name() . ' - ' . $order->get_product_name() ) . '</td>';
            $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . absint( $order->get_quantity() ) . '</td>';
            $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( smartwoo_price( $order->get_price(), array( 'currency' => $order->get_currency() ) ) ) . '</td>';
            $items .= '<td style="padding: 8px; text-align: left; border: 1px solid #eee;">' . esc_html( smartwoo_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ) . '</td>';
            $items .= '</tr>';
        }

        $items .= '</tbody>';
        $items .= '</table>';

        return $items;
    }

    /**
     * Register email template
     * 
     * @param array $templates
     */
    public static function register_template( $templates ) {
        $templates[self::$id] = __CLASS__;

        return $templates;
    }

}

Smartwoo_New_Service_Order::init();