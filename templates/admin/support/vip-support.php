<?php
/**
 * VIP Support Page Template
 *
 * Displays a VIP support notice.
 * - When SmartWoo Pro is inactive: encourages upgrade for premium support.
 * - When SmartWoo Pro is active: welcomes user as VIP and links to support portal.
 *
 * @package SmartWoo\Support
 * @author  Callistus
 * @since   1.0.3
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="smartwoo-admin-page-content">

	<?php if ( ! class_exists( 'SmartWooPro' ) ) : ?>

		<div class="notice notice-info callismart-vip-support callismart-vip-support--free">
			<h2 class="callismart-vip-support__title">
				<?php esc_html_e( 'Need Priority Help? Upgrade to SmartWoo Pro 💎', 'smart-woo-service-invoicing' ); ?>
			</h2>

			<p class="callismart-vip-support__text">
				<?php esc_html_e(
					'As a free user, you currently have access to our community support. But with SmartWoo Pro, you’ll receive direct VIP access — priority responses, personalized guidance, and developer-backed solutions whenever you need assistance.',
					'smart-woo-service-invoicing'
				); ?>
			</p>

			<p class="callismart-vip-support__text">
				<?php esc_html_e(
					'Upgrade today to unlock the VIP lane — where your questions move to the top of our queue and our team gives your business the premium attention it deserves.',
					'smart-woo-service-invoicing'
				); ?>
			</p>
		</div>

		<?php
		// Existing CTA and feature listing.
		if ( function_exists( 'smartwoo_pro_feature' ) ) {
			echo wp_kses_post( smartwoo_pro_feature() );
		}
		?>

	<?php else : ?>

		<div class="notice notice-success callismart-vip-support callismart-vip-support--pro">
			<h2 class="callismart-vip-support__title">
				<?php esc_html_e( 'Welcome to SmartWoo VIP Support 🚀', 'smart-woo-service-invoicing' ); ?>
			</h2>

			<p class="callismart-vip-support__text">
				<?php esc_html_e(
					'You’re part of our VIP circle — SmartWoo Pro users enjoy priority support, faster resolution times, and direct developer assistance.',
					'smart-woo-service-invoicing'
				); ?>
			</p>

			<p class="callismart-vip-support__text">
				<?php esc_html_e(
					'For quick access, visit your private VIP support portal below.',
					'smart-woo-service-invoicing'
				); ?>
			</p>

			<p class="callismart-vip-support__cta">
				<a href="https://support.callismart.com.ng/?utm_source=smartwoo&utm_medium=vip_notice&utm_campaign=vip_support"
					class="button button-primary callismart-vip-support__button"
					target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Go to VIP Support Portal', 'smart-woo-service-invoicing' ); ?>
				</a>
			</p>
		</div>

	<?php endif; ?>
</div>
