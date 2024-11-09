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
            'Content-Type: text/html; charset=UTF-8',
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
     * Get the template header
     */
    protected function get_header() {
        $header = apply_filters( 'smartwoo_email_template_header',
           '<html><head>' . self::print_styles() . '</head>'
        );

        return $header;
    }

    /**
     * Get email body
     */
    protected function get_body() {
        $body = apply_filters( 'smartwoo_email_body',
            '<body>
                <div class="sw-email-image-container">
                    <img src="'. esc_attr( get_option( 'smartwoo_email_image_header' ) ) . '" alt=" '. $this->business_name .' logo" />
                </div>
                <div class="sw-email-body-content">
                    ' . $this->body . '
                </div>

                <footer class="sw-email-footer">' . $this->get_footer_text() . '</footer>
            '
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
            <p>' . esc_html( $this->business_name ) . '</p>'
        );

        return $text;
    }

    /**
     * Get the footer
     */
    public function get_footer() {
        return apply_filters( 'smartwoo_email_footer', '</body></html>' );
    }

    /**
     * Print email stylesheet
     */
    protected static function print_styles() {
        global $wp_filesystem;
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }
        WP_Filesystem();

        $style_dir  = apply_filters( 'smartwoo_email_style_dir', SMARTWOO_PATH . 'assets/css/sw-email-styles.css' );
        $raw_styles = $wp_filesystem->get_contents( $style_dir );
        $style = '<style>';
        if ( false !== $raw_styles ) {
            $style .= $raw_styles;
        }

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

        if ( apply_filters( 'smartwoo_send_mail', true ) ) {
            wp_mail( $to, $subject, $message, $headers, $this->attachments );
        }
    }

    /**
     * Print the entire template with psudo data
     */
    public function preview_template() {
        ?>
        $to         = $this->recipients;
        $subject    = $this->subject;
        $message    = $this->get_header();
        $message   .= $this->get_body();
        $message   .= $this->get_footer();

        <?php
    }
}