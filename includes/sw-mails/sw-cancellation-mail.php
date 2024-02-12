<?php
/**
 * Template for the Service Cancellation Email.
 *
 * This template file can be overridden by copying it to your theme's or child theme's folder.
 * Modify the HTML structure and placeholders as needed.
 *
 * @package Smart_Woo_Invoice
 */

?>

<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/sw-email-styles.css'; ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'sw-email-body' ); ?>>
    <div class="sw-email-container">
        <img class="sw-email-img" src="{logo_url}" alt="{business_name} Logo"><br><br>

        <!-- Email Header -->
        <h1><?php _e( 'Service Cancellation', 'smart-woo' ); ?></h1>

        <!-- Greeting -->
        <p><?php printf( __( 'Dear %s,', 'smart-woo' ), '{user_firstname}' ); ?></p>

        <!-- Cancellation Message -->
        <p><?php _e( 'We regret to inform you that your service with', 'smart-woo' ); ?> {business_name} <?php _e( 'has been cancelled as requested. We appreciate your past support and patronage.', 'smart-woo' ); ?></p>

        <!-- Service Details Card -->
        <div class="sw-email-card">
            <p><strong><?php _e( 'Service Details', 'smart-woo' ); ?></strong></p>
            <p>
                <?php _e( 'Service Name:', 'smart-woo' ); ?> {product_name}<br>
                <?php _e( 'Service ID:', 'smart-woo' ); ?> {service_id}<br>
                <?php _e( 'Billing Cycle:', 'smart-woo' ); ?> {billing_cycle}<br>
                <?php _e( 'Start Date:', 'smart-woo' ); ?> {start_date}<br>
                <?php _e( 'End Date:', 'smart-woo' ); ?> {end_date}<br>
            </p>
        </div>

        <!-- Cancellation Date -->
        <p><?php printf( __( 'Date of Cancellation: %s', 'smart-woo' ), '{cancellation_date}' ); ?></p>

        <!-- Contact Information -->
        <p><?php printf( __( 'If you have any further questions or need assistance, please do not hesitate to <a href="mailto:%s">contact us</a>.', 'smart-woo' ), '{sender_email}' ); ?></p>

        <!-- Additional Information -->
        <p><?php _e( 'Kindly note that our refund policy and terms of service apply to this cancellation.', 'smart-woo' ); ?></p>

        <!-- Closing Message -->
        <p><?php printf( __( 'Thank you for choosing %s.', 'smart-woo' ), '{business_name}' ); ?></p>
    </div>

    <?php wp_footer(); ?>
</body>
</html>
