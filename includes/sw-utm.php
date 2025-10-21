<?php
/**
 * File name sw-utm.php
 * 
 * @package SmartWoo\campaigns
 * @since 1.0.3
 */

defined ( 'ABSPATH' ) || exit;
/**
 * Placeholder for Pro features with dynamic trackable URL.
 *
 * @param string $feature The pro feature required.
 * @return string HTML content.
 */
function smartwoo_pro_feature( $feature = '' ) {
    if ( class_exists( 'SmartWooPro' ) ) {
        return '';
    }

    // Define feature details
    $features = array(
        'advanced stats' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoopro-adv-stats.png',
            'description' => 'Statistics and usage data are only available in Smart Woo Pro.',
            'benefits'    => 'Unlock advanced subscription usage analysis!',
            'class'       => 'advanced-stats'
        ),
        'service logs' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoopro-service-log.png',
            'description' => 'Service logs are only available in Smart Woo Pro.',
            'benefits'    => 'Unlock advanced insights into service subscription activities.',
            'class'       => 'service-logs'
        ),
        'invoice logs' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoo-pro-invoice-logs.png',
            'description' => 'Invoice logs are only available in Smart Woo Pro.',
            'benefits'    => 'Access advanced invoice tracking and management tools.',
            'class'       => 'invoice-logs'
        ),
        'migration-options' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoo-pro-option.png',
            'description' => 'Enable additional features available exclusively in Smart Woo Pro.',
            'benefits'    => 'Manage subscription migrations and prorated billing seamlessly.',
            'class'       => 'migration-options'
        ),
        'more-email-options' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoo-pro-email-options.png',
            'description' => 'Enable more features available exclusively in Smart Woo Pro.',
            'benefits'    => 'Edit migration and refund email templates, attach invoice PDF file to emails, prevent default WooCommerce emails and more options only available on Smart Woo Pro.',
            'class'       => 'more-email-options'

        ),
    );

    // Feature-specific content
    if ( isset( $features[ $feature ] ) ) {
        $details = $features[ $feature ];
        ob_start();
        ?>
        <div class="sw-pro-placeholder <?php echo sanitize_html_class( $details['class'] ); ?>">
            <div class="sw-pro-content-overlay">
                <p><?php echo esc_html( $details['description'] ); ?></p>
                <p><?php echo esc_html( $details['benefits'] ); ?></p>
                <a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button">Activate Pro Feature</a>
            </div>
            <img src="<?php echo esc_url( $details['image'] ); ?>" alt="smartwoo-pro">

        </div>
        <?php
        return ob_get_clean();
    }

    // Default content for no specific feature
    ob_start();
    ?>
    <div class="sw-pro-sell">
        <div class="sw-default-overlay">
            <header class="smartwoo-pro-ad-header">
                <img src="<?php echo esc_url( SMARTWOO_DIR_URL . 'assets/images/smart-woo-pro.png' ) ?>" alt="smart woo pro logo" height="82" width="82">
                <h2>Unlock more features exclusively in Smart Woo Pro.</h2>
            </header>
            <ul id="smartwoo-pro-feature-list">
                <li><strong>Advanced Stats</strong>: Gain detailed insights and visual stats on service subscription usage.</li>
                <li><strong>Service Logs</strong>: Track detailed subscription activity logs.</li>
                <li><strong>Invoice Logs</strong>: Manage invoices with detailed logging and tracking.</li>
                <li><strong>Invoice Items</strong>: Add custom items to an invoice.</li>
                <li><strong>Refund Features</strong>: Automate prorated refunds for subscription cancellations.</li>
                <li><strong>Service Migration</strong>: Enable seamless subscription migrations and prorated billing.</li>
                <li><strong>Email Template Customization</strong>: Customize email templates for your business needs.</li>
                <li><strong>REST API Access</strong>: Access subscription data through a powerful REST API.</li>
                <li><strong>PDF Invoice Attachments</strong>: Automatically attach invoices to client emails.</li>
                <li><strong>Dedicated Support</strong>: Get priority support for free and premium versions.</li>
                <li><strong>Automatic Updates</strong>: Stay updated with the latest features and fixes.</li>
            </ul>
            <a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button">Activate Pro Feature</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generate a UTM campaign URL for Smart Woo Pro purchase page links.
 *
 * Builds a privacy-respecting UTM link that avoids sending identifiable
 * user or site information (e.g., domain names).
 *
 * @param string $utm_url Optional. The base URL to append UTM parameters to.
 *                        Defaults to the plugin’s purchase page.
 * @return string The sanitized UTM campaign URL.
 */
function smartwoo_utm_campaign_url( $utm_url = '' ) {

	// Default SmartWoo Pro purchase page, filterable for flexibility.
	$default_url = esc_url_raw(
		apply_filters( 'smartwoo_pro_purchase_page', 'https://callismart.com.ng/smart-woo-service-invoicing/' )
	);

	// Use provided URL if valid, else fallback to default.
	$utm_url = ! empty( $utm_url ) ? esc_url_raw( $utm_url ) : $default_url;

	// Define privacy-safe UTM parameters.
	$utm_params = array(
		'utm_source'   => 'smartwoo-plugin',
		'utm_medium'   => 'admin-upgrade',
		'utm_campaign' => 'pro-upgrade',
		'utm_content'  => defined( 'SMARTWOO_VER' ) ? sanitize_text_field( SMARTWOO_VER ) : 'unknown',
	);

	// Append query arguments securely.
	$utm_url = add_query_arg( $utm_params, $utm_url );

	return esc_url_raw( $utm_url . '#go-pro' );
}



/**
 * Generate HTML content to encourage Smart Woo Pro purchase.
 *
 * @param bool $echo Whether to print or return content.
 * @return string HTML content.
 */
function smartwoo_support_our_work_container( $echo = true ) {
    $content = '<div class="sw-help-tab-content">';

    if ( class_exists( 'SmartWooPro', false ) ) {
        // Message for Pro users
        $content .= '<p>Thank you for being such a wonderful partner! Your support enables us to sponsor the development of Smart Woo Service Invoicing, offer improved and modern invoice features, and make the free version useful for everyone.</p>';
        $content .= '<p>We priotize your support request, please feel free to reach to us if you encounter any issues, we will ensure these issues are fixed as soon as possible!.</p>';
    } else {
        // Message for Free users
        $content .= '<p>Our team puts in a lot of effort, time, and resources to make Smart Woo Service Invoicing free for everyone. We will continue to improve, support, and maintain this plugin.</p>';
        $content .= '<p>You can be part of this project by getting the PRO VERSION. This will help us cover development, maintenance, and security expenses while ensuring this version remains free for everybody.</p>';
        $content .= smartwoo_pro_feature();
    }

    $content .= '</div>';

    if ( $echo ) {
        echo wp_kses_post( $content );
    } else{
		return $content;

	}
}

/**
 * Generate HTML content for upsell accordion for Bug Report.
 *
 * @param bool $echo		Whether to print or return content.
 * @return string $content	HTML content.
 */
function smartwoo_bug_report_container( $echo = true) {
	$content = '<div class="sw-help-tab-content">
		<p>' . esc_html__( 'If you encounter any bugs or issues while using Smart Woo Service Invoicing Plugin, please report them to help us improve the plugin. Your feedback is valuable in enhancing the plugin\'s functionality and stability.', 'smart-woo-service-invoicing' ) . '</p>
		<a href="' . esc_url( 'https://wordpress.org/support/plugin/smart-woo-service-invoicing/' ) . '" target="_blank" class="sw-red-button">Report a Bug</a>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	}

	return $content;
}

/**
 * Generate HTML content for upsell accordion for User Help.
 *
 * @param bool $echo	 Whether to print or return content.
 * @return string HTML content.
 */
function smartwoo_help_container( $echo = true) {
	$content = '<div class="sw-help-tab-content">
		<p>We are committed to delivering a high-quality user experience and ensuring that our product is safe and bug-free. However, if you encounter any issues, we are dedicated to resolving them swiftly.</p>
		<p>For dedicated support, please use our <a href="https://callismart.com.ng/support-portal/">support portal</a>. For general inquiries, use the community <a href="https://wordpress.org/support/plugin/smart-woo-service-invoicing/">Forum</a>.</p>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	} else {
		return $content;
	}
}

/**
 * Function to return pro feature template file.
 */
function smartwoo_pro_feature_template() {
    return SMARTWOO_PATH . 'templates/pro-feature.php';
}
