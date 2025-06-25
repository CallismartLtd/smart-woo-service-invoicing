<?php
/**
 * Smart Woo Setup Wizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SmartWoo_Setup_Wizard {

	public static function init() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
		add_action( 'admin_post_smartwoo_setup_wizard', array( __CLASS__, 'render_setup_page' ) );
		add_action( 'admin_post_smartwoo_setup_wizard_submit', array( __CLASS__, 'handle_form_submission' ) );
	}

	public static function render_setup_page() {
		?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="shortcut icon" href="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/images/smart-woo-official-icon.png' ); ?>" type="image/x-icon">
            <title>Setup Wizard ‹ Smart Woo </title>
            <link rel="stylesheet" href="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/css/setup-wizard.css' ); ?>">
            <link rel="stylesheet" href="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/css/smart-woo.css' ); ?>">
            <script src="<?php echo esc_url( SMARTWOO_DIR_URL . '/assets/js/setup-wizard.js' ); ?>"></script>
            
        </head>
        <body>
            <div class="wrap smartwoo-setup-wizard">
                <span style="float: right;"><a href="<?php echo esc_url( smartwoo_get_query_param( 'return_url', admin_url( 'admin.php?page=sw-options' ) ) ); ?>" class="button"><?php _e( 'Close', 'smart-woo-service-invoicing' ); ?></a></span>
                <h1><?php esc_html_e( 'Smart Woo Setup Wizard', 'smart-woo-service-invoicing' ); ?></h1>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="smartwoo_setup_wizard_submit">
                    <input type="hidden" name="return_url" value="<?php echo esc_url( smartwoo_get_query_param( 'return_url', admin_url( 'admin.php?page=sw-options' ) ) ); ?>">
                    <?php wp_nonce_field( 'smartwoo_setup_wizard_action', 'smartwoo_setup_nonce' ); ?>

                    <div class="smartwoo-step step-1">
                        <em><?php esc_html_e( 'Step 1: Business Information', 'smart-woo-service-invoicing' ); ?></em>
                        <label for="business-name"><?php _e( 'Business Name', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'This is the business name that will be shown on invoice addresses and emails.' ); ?></label>
                        <input type="text" id="business-name" name="smartwoo_business_name" value="<?php echo esc_attr( get_option( 'smartwoo_business_name' ) ); ?>">
                        
                        <label for="business-phone"><?php _e( 'Business Phones', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( __( 'Enter comma-separated phone numbers, they will be shown on the invoice biller sections.', 'smart-woo-service-invoicing' ) ); ?></label>
                        <input type="text" id="business-phone" name="smartwoo_admin_phone_numbers" value="<?php echo esc_attr( get_option( 'smartwoo_admin_phone_numbers' ) ); ?>">

                        <label for="service-id-prefix"><?php _e( 'Service ID Prefix', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'This is the character(s) that will be placed before all service subscription IDs.' ); ?></label>
                        <input type="text" id="service-id-prefix" name="smartwoo_service_id_prefix" value="<?php echo esc_attr( get_option( 'smartwoo_service_id_prefix', 'SID' ) ); ?>">
                        
                        <label for="service-page"><?php _e( 'Service subscription page', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( __( 'The main client portal where all subscriptions for a client is listed.', 'smart-woo-service-invoicing' ) ); ?></label>
                        <?php
                            wp_dropdown_pages( array(
                                'name'     => 'smartwoo_service_page_id',
                                'id'     => 'service-page',
                                'echo'     => true,
                                'show_option_none' => __( 'Select Service Page', 'smart-woo-service-invoicing' ),
                                'option_none_value' => '',
                                'selected' => get_option( 'smartwoo_service_page_id' )
                            ) );
                        ?>                    </div>

                    <div class="smartwoo-step step-2">
                        <em><?php esc_html_e( 'Step 2: Invoicing Options', 'smart-woo-service-invoicing' ); ?></em>
                        <label for="invoice-page"><?php _e( 'Invoice Page', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( __( 'The page where all invoices for a client is listed.', 'smart-woo-service-invoicing' ) ); ?></label>
                        <?php 
                            wp_dropdown_pages( array(
                                'name'     => 'smartwoo_invoice_page_id',
                                'id'     => 'invoice-page',
                                'echo'     => true,
                                'show_option_none' => __( 'Select Invoice Page', 'smart-woo-service-invoicing' ),
                                'option_none_value' => '',
                                'selected' => get_option( 'smartwoo_invoice_page_id' )
                            ) );
                        ?>   

                        <label for="invoice-id-prefix"><?php _e( 'Invoice ID Prefix', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'This is the character(s) that will be placed before all invoice IDs.' ); ?></label>
                        <input type="text" id="invoice-id-prefix" name="smartwoo_invoice_id_prefix" value="<?php echo esc_attr( get_option( 'smartwoo_invoice_id_prefix' , 'INV' ) ); ?>">
                        
                        <label for="watermark-url"><?php _e( 'Watermark URL', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'Invoice watermark URL' ); ?></label>
                        <input type="text" id="watermark-url" name="smartwoo_invoice_watermark_url" value="<?php echo esc_attr( get_option( 'smartwoo_invoice_watermark_url' ) ); ?>">
                    
                        <label for="logo-url"><?php _e( 'Logo URL' ); ?> <?php smartwoo_help_tooltip( __( 'Invoice logo URL', 'smart-woo-service-invoicing' ) ); ?></label>
                        <input type="text" name="smartwoo_invoice_logo_url" id="logo-url" value="<?php echo esc_attr( get_option( 'smartwoo_invoice_logo_url' ) ); ?>">
                    
                    </div>

                    <div class="smartwoo-step step-3" style="display:none;">
                        <em><?php esc_html_e( 'Step 3: Email Options', 'smart-woo-service-invoicing' ); ?></em>
                        
                        <label for="email-sender"><?php _e( 'Email Sender Name', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'This is the name that will be used to send emails to your clients.' ); ?></label>
                        <input type="text" id="email-sender" name="smartwoo_email_sender_name" value="<?php echo esc_attr( get_option( 'smartwoo_email_sender_name' ) ); ?>">

                        <label for="billing-email"><?php _e( 'Blling Email', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( __( 'This is the email address that will be used to send all subscription and invoice related emails.', 'smart-woo-service-invoicing' ) ); ?></label>
                        <input type="text" id="billing-email" name="smartwoo_billing_email" value="<?php echo esc_attr( get_option( 'smartwoo_billing_email' ) ); ?>">

                        <label for="email-header-url"><?php _e( 'Email Header Image URL', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'This is the image used in email template header.' ); ?></label>
                        <input type="text" id="email-header-url" name="smartwoo_email_image_header" value="<?php echo esc_attr( get_option( 'smartwoo_email_image_header' ) ); ?>">

                    </div>

                    <div class="smartwoo-step step-4" style="display:none;">
                        <em><?php esc_html_e( 'Step 4: Shop Appearance', 'smart-woo-service-invoicing' ); ?></em>
                        <label for="add-to-cart-text"><?php _e( 'Add to Cart Text', 'smart-woo-service-invoicing' ); ?> <?php smartwoo_help_tooltip( 'The "add to cart text" for all subscription products' ); ?></label>
                        <input type="text" id="add-to-cart-text" name="smartwoo_product_text_on_shop" value="<?php echo esc_attr( get_option( 'smartwoo_product_text_on_shop' ) ); ?>">
                    </div>

                    <div class="smartwoo-buttons">
                        <button type="button" class="button button-secondary prev-step">&larr; <?php _e( 'Back', 'smart-woo-service-invoicing' ); ?></button>
                        <button type="button" class="button button-primary next-step"><?php _e( 'Next', 'smart-woo-service-invoicing' ); ?> &rarr;</button>
                        <button type="submit" class="button button-primary finish-step" style="display:none;">
                            <?php _e( 'Finish Setup', 'smart-woo-service-invoicing' ); ?>
                        </button>
                    </div>

                </form>
            </div>
        </body>
        </html>
	
		<?php
	}

	public static function handle_form_submission() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'smartwoo_setup_wizard_action', 'smartwoo_setup_nonce' ) ) {
			wp_die( __( 'Not allowed.', 'smart-woo-service-invoicing' ) );
		}

		$fields = array(
			'smartwoo_business_name',
			'smartwoo_email_sender_name',
            'smartwoo_admin_phone_numbers',
			'smartwoo_email_image_header',
            'smartwoo_billing_email',
			'smartwoo_invoice_page_id',
            'smartwoo_invoice_watermark_url',
            'smartwoo_invoice_logo_url',
			'smartwoo_service_page_id',
			'smartwoo_invoice_id_prefix',
			'smartwoo_service_id_prefix',
			'smartwoo_product_text_on_shop'
		);

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_option( $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		wp_redirect( isset( $_POST['return_url'] ) ? sanitize_url( wp_unslash( $_POST['return_url'] ) ) : admin_url( 'admin.php?page=sw-admin' ) );
		exit;
	}
}

SmartWoo_Setup_Wizard::init();
