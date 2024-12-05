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
 */
function smartwoo_pro_feature( $feature = '' ) {
	if ( class_exists( 'SmartWooPro' ) ) {
		return '';
	}


	if ( 'advanced stats' === $feature || 'service logs' === $feature || 'invoice logs' === $feature || 'migration-options' === $feature || 'more-email-options' === $feature ) {


		switch ( $feature ) {
			case 'advanced stats':
				$image_url 		= SMARTWOO_DIR_URL . '/assets/images/smartwoopro-adv-stats.png';
				$description 	= 'Statistics and usage data are only available in Smart Woo Pro.';
				$benefits 		= 'Unlock advanced subscription usage analysis!';
				break;
				
			case 'service logs':
				$image_url 		= SMARTWOO_DIR_URL . '/assets/images/smartwoopro-service-log.png';
				$description 	= 'Service logs are only available in Smart Woo Pro.';
				$benefits 		= 'Unlock advanced insights into service subscription activities.';
				break;
				
			case 'invoice logs':
				$image_url		= SMARTWOO_DIR_URL . '/assets/images/smartwoopro-invoice-log.png';
				$description 	= 'Invoice logs are only available in Smart Woo Pro.';
				$benefits 		= 'Unlock advanced invoice logging features.';
				break;
		
			case 'migration-options':
				$image_url		= SMARTWOO_DIR_URL . '/assets/images/smartwoo-business-pro-options.png';
				$description	= 'Enable additional features available exclusively in Smart Woo Pro.';
				$benefits		= 'Enable service subscription migration and prorated billing exclusively in Smart Woo Pro.';
				break;
		
			case 'more-email-options':
				$image_url		= SMARTWOO_DIR_URL . '/assets/images/smartwoopro-more-email-options.png';
				$description	= 'Enable more features available exclusively in Smart Woo Pro.';
				$benefits		= 'Stop default WooCommerce emails for subscription-related orders, and attach PDF invoices to emails.';
				break;
		
			default:
				// Default case for unknown features
				$image_url = '';
				$description = 'Unlock more features available exclusively in Smart Woo Pro.';
				$benefits = 'Get detailed insights into service usage and more!';
				break;
		}
		
		ob_start();
		?>
		<div class="sw-pro-placeholder" style="background-image: url('<?php echo esc_url( $image_url ); ?>');">
			<div class="sw-pro-content-overlay">
				<p><?php echo esc_html( $description ); ?></p>
				<p><?php echo esc_html( $benefits ); ?></p>
				<a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button">Activate Pro Feature</a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// Default content if no specific feature is provided
	ob_start();
	?>
	<div class="sw-pro-sell">
		<div class="sw-default-overlay">
			<h2>Unlock more features only available on Smart Woo Pro.</h2>
			<ul style="list-style: square;">
				<li><strong>Advanced Stats</strong>: Get detailed insights and visual stats (bar charts, graphs) on service subscription usage.</li>
				<li><strong>Service Logs</strong>: Track how clients interact with their subscriptions, including detailed activity insights.</li>
				<li><strong>Invoice Logs</strong>: Gain insights into all invoice interactions, including payment failures and successful payments.</li>
				<li><strong>Refund Feature</strong>: Automatically handle prorated refunds when a client cancels a subscription.</li>
				<li><strong>Service Migration</strong>: Easily manage subscription migrations, including prorated billing during changes, with detailed logs for tracking.</li>
				<li><strong>Email Template Customization</strong>: Customize the templates for available emails to meet your business requirements.</li>
				<li><strong>REST API Access</strong>: Access subscription data via a powerful REST API (currently read-only, with future write support planned).</li>
				<li><strong>PDF Invoice Attachments</strong>: Automatically attach PDF invoices to email notifications for seamless client communication.</li>
				<li><strong>Dedicated Support</strong>: Receive dedicated support for both the free and premium versions of Smart Woo.</li>
				<li><strong>Automatic Updates</strong>: Ensure your plugin remains up-to-date with the latest features and security fixes.</li>
			</ul>
			<a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button" id="pro-upgrade-button">Activate Pro Feature</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Smart Woo UTM campaign function.
 */
function smartwoo_utm_campaign_url( $utm_url = '' ) {
	$utm_url		= empty( $utm_url ) ? apply_filters( 'smartwoo_pro_purchase_page', 'https://callismart.com.ng/smart-woo-service-invoicing/#go-pro/' ) : $utm_url;
	$utm_source 	= SMARTWOO;
	$utm_medium 	= 'upgrade button';
	$utm_campaign 	= 'pro-upgrade';
	$plugin_version = SMARTWOO_VER;
	$utm_referrer 	= site_url();

	$utm_url .= '?utm_source=' . rawurlencode( $utm_source );
	$utm_url .= '&utm_medium=' . rawurlencode( $utm_medium );
	$utm_url .= '&utm_campaign=' . rawurlencode( $utm_campaign );
	$utm_url .= '&plugin_version=' . rawurlencode( $plugin_version );
	$utm_url .= '&utm_referrer=' . rawurlencode( $utm_referrer );

	return $utm_url;
}