<?php
/**
 * The knowledge base page template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="wrap">
	<h2>Smart Woo Settings and Knowledgebase</h2>

	<div class="sw-container">
		<div class="sw-left-column">
			<h3>Quick Set-up Guides</h3>
			<ul>
				<li><a class="settings-nav" href="#general-concept">General</a></li>
				<li><a class="settings-nav" href="#step1">Step 1</a></li>
				<li><a class="settings-nav" href="#step2">Step 2</a></li>
				<li><a class="settings-nav" href="#step3">Step 3</a></li>
			</ul>
		</div>

		<div class="sw-right-column">
			<div id="first-display" class="image-section">
				<h3> Smart Woo Service Invoicing</h3>
				<img src="<?php echo esc_url( SMARTWOO_DIR_URL . 'assets/images/smart-woo-img.png' ); ?>" alt="plugin screenshot" style="width: 50%;">
				<p>Here you will find useful information to get you started.</p>
			</div>

			<div id="general-concept" class="instruction">
				<h3>Introduction</h3>
				<p><strong>Smart Woo Service Invoicing integrates powerful service subscription capabilities into your WooCommerce store. It enables you to define service-based products, auto-generate invoices, send timely reminders, and track payments effortlessly.</strong></p>
				<p>To get started, there are basically three steps needed to get your subscriptions up and running.</p>
			</div>

			<div id="step1" class="instruction">
				<h3>Step 1: Set up Your Business Info</h3>
				<p><strong>Set up your business details on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ); ?>" target="_blank">business settings page</a>, and configure invoicing preferences on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ); ?>" target="_blank">invoicing settings page</a>.</strong></p>
				<p>You may need to create two dedicated pages to allow your clients to fully manage their services and invoices. Usually, these pages are automatically created during installation. If not, create them manually and insert the following shortcodes:
					<ul style="margin-top: 0.5em; margin-bottom: 0;">
						<li><strong>[smartwoo_service_page]</strong> for the service dashboard</li>
						<li><strong>[smartwoo_invoice_page]</strong> for invoice and billing</li>
					</ul>
				</p>
			</div>

			<div id="step2" class="instruction">
				<h3>Step 2: Create a Service Product</h3>
				<p><strong>Create a <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ); ?>" target="_blank">Service Product</a> specifically dedicated to service subscriptions, and configure the necessary service fields.</strong></p>
				<p>Think of this product as a subscription unit. Once published, clients will be able to order it like a regular WooCommerce product. Behind the scenes, Smart Woo handles subscription setup, recurring invoicing, and usage tracking.</p>
			</div>

			<div id="step3" class="instruction">
				<h3>Step 3: All Set 🎉</h3>
				<p><strong>Your service product is now live. Customers can order it directly from your store, and Smart Woo will handle everything — create invoices, set up reminders, and track usage.</strong></p>
				<p>You can view and manage all your service orders from <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ); ?>">Service Orders</a> in the admin dashboard.</p>
				<p>Need a complete usage guide? <a href="https://callismart.com.ng/smart-woo-usage-guide/" target="_blank">Click here to view the full documentation</a>.</p>
				<?php echo wp_kses_post( smartwoo_pro_feature() ); ?>
			</div>
		</div>
	</div>
</div>
