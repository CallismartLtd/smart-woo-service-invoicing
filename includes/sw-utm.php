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
        ),
        'service logs' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoopro-service-log.png',
            'description' => 'Service logs are only available in Smart Woo Pro.',
            'benefits'    => 'Unlock advanced insights into service subscription activities.',
        ),
        'invoice logs' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoopro-invoice-log.png',
            'description' => 'Invoice logs are only available in Smart Woo Pro.',
            'benefits'    => 'Access advanced invoice tracking and management tools.',
        ),
        'migration-options' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoo-business-pro-options.png',
            'description' => 'Enable additional features available exclusively in Smart Woo Pro.',
            'benefits'    => 'Manage subscription migrations and prorated billing seamlessly.',
        ),
        'more-email-options' => array(
            'image'       => SMARTWOO_DIR_URL . '/assets/images/smartwoopro-more-email-options.png',
            'description' => 'Enable more features available exclusively in Smart Woo Pro.',
            'benefits'    => 'Stop default WooCommerce emails and attach PDF invoices.',
        ),
    );

    // Feature-specific content
    if ( isset( $features[ $feature ] ) ) {
        $details = $features[ $feature ];
        ob_start();
        ?>
        <div class="sw-pro-placeholder" style="background-image: url('<?php echo esc_url( $details['image'] ); ?>');">
            <div class="sw-pro-content-overlay">
                <p><?php echo esc_html( $details['description'] ); ?></p>
                <p><?php echo esc_html( $details['benefits'] ); ?></p>
                <a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button">Activate Pro Feature</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Default content for no specific feature
    ob_start();
    ?>
    <div class="sw-pro-sell">
        <div class="sw-default-overlay">
            <h2>Unlock more features exclusively in Smart Woo Pro.</h2>
            <ul style="list-style: square;">
                <li><strong>Advanced Stats</strong>: Gain detailed insights and visual stats on service subscription usage.</li>
                <li><strong>Service Logs</strong>: Track detailed subscription activity logs.</li>
                <li><strong>Invoice Logs</strong>: Manage invoices with detailed logging and tracking.</li>
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
 * @param string $utm_url The base URL to append UTM parameters to. If empty, defaults to the plugin's purchase page.
 * @return string The full UTM campaign URL with appended parameters.
 */
function smartwoo_utm_campaign_url( $utm_url = '' ) {
    // Define the default URL using a filter for extensibility.
    $default_url = esc_url_raw( apply_filters( 'smartwoo_pro_purchase_page', 'https://callismart.com.ng/smart-woo-service-invoicing/' ) );

    // Validate and assign the base URL.
    $utm_url = wp_http_validate_url( $utm_url ) ? $utm_url : $default_url;

    // Define UTM parameters.
    $utm_params = array(
        'utm_source'    => defined( 'SMARTWOO' ) ? SMARTWOO : 'smartwoo',
        'utm_medium'    => 'upgrade button',
        'utm_campaign'  => 'pro-upgrade',
        'plugin_version'=> defined( 'SMARTWOO_VER' ) ? SMARTWOO_VER : 'unknown',
        'utm_referrer'  => site_url(),
    );

    // Append UTM parameters to the URL.
    $utm_url = add_query_arg( array_map( 'rawurlencode', $utm_params ), $utm_url );

    return $utm_url . '#go-pro';
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
		<p>For dedicated support, please visit our <a href="https://callismart.com.ng/support-portal/">support portal</a>. For general inquiries, use the WordPress <a href="https://wordpress.org/support/plugin/smart-woo-service-invoicing/">Support Forum</a>.</p>
    </div>';
	if ( $echo ) {
		echo wp_kses_post( $content );
	} else {
		return $content;
	}
}