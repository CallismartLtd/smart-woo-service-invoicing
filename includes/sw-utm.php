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

	if ( 'advanced stats' === $feature || 'service logs' === $feature || 'invoice logs' === $feature ) {


		switch ( $feature ) {
			case 'advanced stats':
				$image_url = SMARTWOO_DIR_URL . '/assets/images/smartwoopro-usage.png';
				$description = 'Stats and usage are only available on Smart Woo Pro.';
				$benefits = 'Unlock advanced subscription usage analysis!';
				break;
			case 'service logs':
				$image_url = SMARTWOO_DIR_URL . '/assets/images/smartwoopro-service-log.png';
				$description = 'Service log is only available on Smart Woo Pro.';
				$benefits = 'Unlock advanced insights into service subscription activities.';
				break;
			case 'invoice logs':
				$image_url = SMARTWOO_DIR_URL . '/assets/images/smartwoopro-invoice-log.png';
				$description = 'Invoice log is only available on Smart Woo Pro.';
				$benefits = 'Unlock advanced invoice logging feature.';
				break;
			default:
				// Default case for unknown features
				$image_url = '';
				$description = 'Unlock more features only available on Smart Woo Pro.';
				$benefits = 'Get detailed insight into service usage and more!';
				break;
		}

		ob_start();
		?>
		<div class="sw-pro-placeholder" style="background-image: url('<?php echo esc_url( $image_url ); ?>');">
			<div class="sw-pro-content-overlay">
				<p><?php echo esc_html( $description ); ?></p>
				<p><?php echo esc_html( $benefits ); ?></p>
				<a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button">Purchase Pro Version</a>
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
			<h4>Unlock more features only available on Smart Woo Pro.</h4>
			<ul>
				<li>Get detailed insight into service usage.</li>
				<li>Access to Service and Invoice logs.</li>
				<li>Robust Refund and Pro-Rata service subscription.</li>
				<li>Allow clients perform service migrations.</li>
				<li>Easy integration of REST APIs for client subscriptions and management.</li>
				<li>Have clients pay outstanding invoices with Smart Woo integration with Tera Wallet.</li>
			</ul>
			<a href="<?php echo esc_url( smartwoo_utm_campaign_url() ); ?>" class="sw-pro-upgrade-button" id="pro-upgrade-button">Purchase Pro Version</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Smart Woo UTM campaign function.
 */
function smartwoo_utm_campaign_url() {
	$utm_url = 'https://callismart.com.ng/smart-woo-service-invoicing';
	$utm_source = SMARTWOO;
	$utm_medium = 'upgrade button';
	$utm_campaign = 'pro-upgrade';
	$plugin_version = SMARTWOO_VER;
	$utm_referrer = site_url();

	$utm_url .= '?utm_source=' . rawurlencode( $utm_source );
	$utm_url .= '&utm_medium=' . rawurlencode( $utm_medium );
	$utm_url .= '&utm_campaign=' . rawurlencode( $utm_campaign );
	$utm_url .= '&plugin_version=' . rawurlencode( $plugin_version );
	$utm_url .= '&utm_referrer=' . rawurlencode( $utm_referrer );

	return $utm_url;
}