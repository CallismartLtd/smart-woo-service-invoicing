<?php
/**
 * Smart Woo Email handler class
 * 
 * @author Callistus
 * @since 2.2.0
 * @package SmartWoo\Emails
 */

defined( 'ABSPATH' ) || exit;

class SmartWoo_Mail {
    /**
     * The email sender name
     * 
     * @var string $sender_name
     */
    protected $sender_name;

    /**
     * Business Name
     * 
     * @var string $business_name The subscription business name, defaults to the blog name.
     */
    protected $business_name;

    /**
     * Sender Email Address
     * 
     * @var string $sender_email The business email
     */
    protected $sender_email;

    /**
     * Subject
     * 
     * @var string $subject The email subject
     */
    protected $subject;

    /**
     * Email recipients.
     * 
     * @var string[] $recipients
     */
    protected $recipients;

    /**
     * Attachments
     * 
     * @var string[] $attachments
     */
    protected $attachments = [];

    /**
     * Headers
     * 
     * @var string[] $headers
     */
    protected $headers;

    /**
     * Email content
     * 
     * @var string $body
     */
    protected $body;

    /**
     * The email ID
     * 
     * @var string $id
     */
    public static $id;

    /**
     * Class constructor
     * 
     * @param string $subject       The email subject
     * @param string $body          The email content
     * @param string[] $recipients  The recipient(s).
     */
    public function __construct( $subject, $body, $recipients, $attachments = [] ) {
        $this->subject      = $subject;
        $this->recipients   = $recipients;
        $this->attachments  = $attachments;
        $this->body         = $body;
        
        /**
         * Set Email Metadata
         */
        $this->set_business_name();
        $this->set_sender_name();
        $this->set_sender_email();
        $this->set_headers();
    }

    /**
     * Set the headers
     */
    public function set_headers() {
        $this->headers = array(
            'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ),
			'From: ' . esc_html( $this->sender_name ) . ' <' . esc_html( $this->sender_email ) . '>',
        );
    }

    /**
     * Set the business name
     */
    public function set_business_name() {
        $this->business_name = sanitize_text_field( wp_unslash( get_option( 'smartwoo_business_name', get_bloginfo( 'name' ) ) ) );
    }

    /**
     * Set sender name
     */
    public function set_sender_name() {
        $this->sender_name = sanitize_text_field( wp_unslash( get_option( 'smartwoo_email_sender_name', get_bloginfo( 'name' ) ) ) );
    }

    /**
     * Set the sender email address
     */
    public function set_sender_email() {
        $this->sender_email = sanitize_text_field( wp_unslash( get_option( 'smartwoo_billing_email', 'billing@' . site_url() ) ) );
    }


    /**
     |-----------------------
     | TEMPLATE FORMATTERS
     |-----------------------
    */

    /**
     * Get the template header.
     */
    protected function get_header() {
        $lang_attr = get_bloginfo( 'language' );
        $charset = get_bloginfo( 'charset' );

        $header = apply_filters(
            'smartwoo_email_template_header',
            '<!DOCTYPE html>
            <html lang="' . esc_attr( $lang_attr ) . '">
            <head>
                <meta charset="' . esc_attr( $charset ) . '">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="x-apple-disable-message-reformatting">
                <title>' . esc_html( $this->subject ) . '</title>'
                . apply_filters( 'smartwoo_maybe_add_script', '' ) . '
                <!--[if mso]> 
                <noscript> 
                <xml> 
                <o:OfficeDocumentSettings> 
                <o:PixelsPerInch>96</o:PixelsPerInch> 
                </o:OfficeDocumentSettings> 
                </xml> 
                </noscript> 
                <![endif]-->
                
            </head>',
            $this
        );

        return $header;
    }

    /**
     * Get email body.
     */
    protected function get_body() {
        $header_image_url   = get_option( 'smartwoo_email_image_header' );
        $header_image_alt   = $this->business_name . ' logo';
        $body_content       = $this->body;

        $body = apply_filters(
            'smartwoo_email_body',
            '<body style="margin: 0; padding: 0; background-color: #f9f9f9; width: 100%">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="80%" style="background-color: #f9f9f9; margin: 0 auto; padding: 0; border-collapse: collapse; width: 90%;">
                    <tr>
                        <td align="center" style="padding: 5px 0; background-color: #f1f1f1; border: 0;">
                                <img src="' . esc_attr( $header_image_url ) . '" alt="' . esc_attr( $header_image_alt ) . '" style="max-width: 350px; display: block; margin: 0 auto; border: 0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; background-color: #ffffff;">
                            <div style="text-decoration:none; font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: auto; border: 1px solid #ddd; padding: 10px;box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); border-radius: 4px;">
                                ' . wp_kses_post( $body_content ) . '
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0; background-color: #f1f1f1;">
                            <footer style="font-size: 0.9em; color: #555555; text-align: center; max-width: 600px; margin: auto;">
                                ' . $this->get_footer_text() . '
                            </footer>
                        </td>
                    </tr>',
            $this
        );

        return $body;
    }


    /**
     * Get the email footer text
     */
    public function get_footer_text() {
        $text = apply_filters( 'smartwoo_email_footer_text',
            '<p>Thank you for the continued business and support. We value you so much.</p>
            <p>Kind regards. </p>
            <p><strong><a style="text-decoration: none; color: black;" href="' . esc_attr( site_url() ) . '">' . esc_html( $this->business_name ) . '</a></strong></p>'
        );

        return $text;
    }

    /**
     * Get the email footer.
     */
    public function get_footer() {
        // Close any necessary email content divs or elements for proper structure.
        $footer = '
                </table>
            </body>
        </html>';
        return apply_filters( 'smartwoo_email_footer', $footer );
    }


    /**
     * Print email stylesheet
     */
    protected static function print_styles() {
        global $wp_filesystem;

        // Ensure WP_Filesystem is loaded, else load it.
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        // Initialize the filesystem.
        WP_Filesystem();

        $filtered_style_dir = apply_filters( 'smartwoo_email_style_dir', '' );

        if ( ! empty( $filtered_style_dir ) && $wp_filesystem->exists( $filtered_style_dir ) ) {
            $style_dir = $filtered_style_dir;
        } else {
            // Fall back to the default style directory.
            $style_dir = SMARTWOO_PATH . 'assets/css/sw-email-styles.css';
        }

        $raw_styles = $wp_filesystem->get_contents( $style_dir );

        // Wrap the raw styles in a <style> block
        $style = '<style>';
        $style .= $raw_styles !== false ? $raw_styles : '';
        $style .= '</style>';

        return $style;
    }


    /**
     * Send Email
     */
    protected function send() {
        $to         = $this->recipients;
        $subject    = $this->subject;
        $message    = $this->get_header();
        $message   .= $this->get_body();
        $message   .= $this->get_footer();
        $headers    = $this->headers;
        $attachments = $this->attachments;

        if ( apply_filters( 'smartwoo_send_mail', true ) ) {
            if ( wp_mail( $to, $subject, $message, $headers, $attachments ) ) {
                do_action( 'smartwoo_mail_sent', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
            }
        }
    }

    /**
     * Print the entire template with set properties for preview.
     */
    public function preview_template() {
        // Only output the preview if the script is being run in the admin area.
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You don\'t have the required permission to view this page.', 'Permission denied', array( 'response' => 401 ) );
        }

        $header   = $this->get_header();
        $body     = $this->get_body();
        $footer   = $this->get_footer();
        echo ( $header ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped       
        echo ( $body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  
        echo ( $footer ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  
         
    }

    /**
     * Get email preview url
     * 
     * @param string $id Mail option name
     */
    public static function get_preview_url( $id ) {

        return wp_nonce_url( admin_url( 'admin-post.php?action=smartwoo_mail_preview&temp=' . $id ) );
    }

    /**
     * Get a random product ID email template preview.
     *
     * @return int|false The random product ID, or false if no products are found.
     */
    public static function get_random_product_id() {
        $args = array(
            'status'    => 'publish',
            'type'      => 'sw_product',
            'limit'     => 1,
            'return'    => 'ids',
            'orderby'   => 'rand',
        );

        // Create the product query
        $product_query = new WC_Product_Query( $args );

        // Get the product IDs
        $products = $product_query->get_products();

        // If there are no products, return false
        if ( empty( $products ) ) {
            wp_die( 'No product found, please <a href="'. esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ) .'">create a product</a> first', 'Product not found', array( 'response' => 404 ) );
        }

        // Return the single random product ID
        return $products[0];
    }

/**
 * Create a pseudo WooCommerce order with pre-populated properties.
 *
 * @return WC_Order The pseudo WooCommerce order object.
 */
public static function create_pseudo_wc_order() {

    // Create a new WC_Order object.
    $order = new WC_Order();
    $user_id = get_current_user_id();
    $user = new WC_Customer( $user_id );

    // Populate order properties.
    $order->set_customer_id( $user->get_id() );
    $order->set_billing_first_name( $user->get_billing_first_name() );
    $order->set_billing_last_name( $user->get_billing_last_name() );
    $order->set_billing_address_1( $user->get_billing_address_1() );
    $order->set_billing_city( $user->get_billing_city() );
    $order->set_billing_postcode( $user->get_billing_postcode() );
    $order->set_billing_country( $user->get_billing_country() );
    $order->set_billing_email( $user->get_billing_email() );
    $order->set_payment_method( 'bacs' );
    $order->set_payment_method_title( 'Bank Transfer' );
    $order->set_currency( get_woocommerce_currency() );
    $order->set_created_via( SMARTWOO );
    $order->set_date_created( new WC_DateTime() );

    // Add a dummy line item to the order.
    $product_id = self::get_random_product_id();
    if ( $product_id ) {
        $The_product = wc_get_product( $product_id );
        if ( $The_product ) {
            $item = new WC_Order_Item_Product();
            $item->set_product( $The_product );
            $item->set_quantity( 1 ); // Set quantity.
            $item->set_total( $The_product->get_price() ); // Set total based on price.
            $order->add_item( $item );

            if ( ( $The_product instanceof SmartWoo_Product ) ) {
                $fee = new WC_Order_Item_Fee();
                $fee->set_props(
                    array(
                        'name'      => 'Sign-up Fee',
                        'tax_class' => '',
                        'total'     => $The_product->get_sign_up_fee(),
                    )
                );
                $order->add_item( $fee );
            }
        }
    }

    // Set total order price based on line items.
    $order->calculate_totals();

    // Generate a pseudo transaction ID for testing.
    $order->set_transaction_id( 'WC|' . wp_rand( 1, 10000 ) . '|' . time() );

    return $order;
}


}