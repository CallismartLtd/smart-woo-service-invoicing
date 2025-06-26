<?php
/**
 * The knowledge base page template.
 * 
 * @author Callistus
 * @package SmartWoo\templates
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="wrap">
	<h2><?php esc_html_e( 'General Settings and Knowledge Base', 'smart-woo-service-invoicing' ); ?></h2>

	<div class="sw-container smartwoo-admin-knowledgebase">
		<div class="sw-knowledgebase-left-column">
			<ul>
				<li><a class="smartwoo-knowledgebase-nav active" href=""><?php esc_html_e( 'General', 'smart-woo-service-invoicing' ); ?></a></li>
				<li><a class="smartwoo-knowledgebase-nav" href=""><?php esc_html_e( 'Introduction', 'smart-woo-service-invoicing' ); ?></a></li>
				<li><a class="smartwoo-knowledgebase-nav" href=""><?php esc_html_e( 'Step 1', 'smart-woo-service-invoicing' ); ?></a></li>
				<li><a class="smartwoo-knowledgebase-nav" href=""><?php esc_html_e( 'Step 2', 'smart-woo-service-invoicing' ); ?></a></li>
				<li><a class="smartwoo-knowledgebase-nav" href=""><?php esc_html_e( 'Step 3', 'smart-woo-service-invoicing' ); ?></a></li>
			</ul>
		</div>

		<div class="sw-knowledgebase-right-column">
			<div class="sw-knowledgebase-content">
				<h3><?php esc_html_e( 'General Settings', 'smart-woo-service-invoicing' ); ?>  <span style="float: right;"> <span class="dashicons dashicons-setup"></span> <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'smartwoo_setup_wizard', 'return_url' => admin_url( 'admin.php?page=sw-options' ) ), admin_url( 'admin-post.php' )) ); ?>" class="button" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Run Setup Wizard', 'smart-woo-service-invoicing' ); ?></a></span></h3>
				<?php if ( empty( $missing_settings ) ) : ?>
					<p><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Congratulations, all essential options has been set', 'smart-woo-service-invoicing' ); ?></p>
				<?php else: ?>
					<em class="sw-knowledgebase-notice"><span class="dashicons dashicons-warning"></span> You have <code><?php echo absint( count( $missing_settings ) ); ?></code> essential option<?php echo esc_html( count( $missing_settings ) > 1 ? 's' : '' ); ?> that needs to be set.</em>
					<h3><?php esc_html_e( 'Options', 'smart-woo-service-invoicing' ); ?>:</h3>
					<table class="sw-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Option Name', 'smart-woo-service-invoicing' ); ?></th>
								<th><?php esc_html_e( 'Decription', 'smart-woo-service-invoicing' ); ?></th>
								<th><?php esc_html_e( 'Action', 'smart-woo-service-invoicing' ); ?></th>
								
							</tr>
						</thead>
						<tbody>
							<?php foreach( $missing_settings as $id => $data ) : ?>
								<tr>
									<td><?php echo esc_html( $data['title'] ); ?></td>
									<td><?php echo esc_html( $data['description'] ); ?></td>
									<td><a href="<?php echo esc_url( $data['url'] ); ?>" class="button" target="_blank"><?php esc_html_e( 'Set', 'smart-woo-service-invoicing' ); ?></a></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
				<h3><?php esc_html_e( 'Getting started?', 'smart-woo-service-invoicing' ); ?></h3>
				<p><?php esc_html_e( 'Use the tabs on the left to find the three easy steps to get started.', 'smart-woo-service-invoicing' ); ?></p>
			</div>

			<div id="general-concept" class="sw-knowledgebase-content smartwoo-hide">
				<h3><?php esc_html_e( 'Introduction', 'smart-woo-service-invoicing' ); ?></h3>
				<p><?php esc_html_e( 'Smart Woo Service Invoicing is powerful subscription plugin that turns your WordPress/WooCommerce website into an advanced billing engine.', 'smart-woo-service-invoicing' ); ?></p>
				<p><?php esc_html_e( 'This plugin has a modern client portal, advanced billing system, subscription modelling with assets, invoice, tracking, logs e.t.c', 'smart-woo-service-invoicing' ); ?></p>
				<p><?php esc_html_e( 'To get started, there are basically three steps needed to get your subscriptions up and running.', 'smart-woo-service-invoicing' ); ?></p>
			</div>

			<div id="step1" class="sw-knowledgebase-content smartwoo-hide">
				<h3>Step 1: Set up Your Business Info</h3>
				<p><strong>Set up your business details on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=business' ) ); ?>" target="_blank">business settings page</a>, and configure invoicing preferences on the <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-options&tab=invoicing' ) ); ?>" target="_blank">invoicing settings page</a>.</strong></p>
				<p>You may need to create two dedicated pages to allow your clients to fully manage their services and invoices. Usually, these pages are automatically created during installation. If not, create them manually and insert the following shortcodes:
					<ul style="margin-top: 0.5em; margin-bottom: 0;">
						<li><strong>[smartwoo_service_page]</strong> for the service dashboard</li>
						<li><strong>[smartwoo_invoice_page]</strong> for invoice and billing</li>
					</ul>
				</p>
			</div>

			<div id="step2" class="sw-knowledgebase-content smartwoo-hide">
				<h3>Step 2: Create a Service Product</h3>
				<p><strong>Create a <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-products&action=add-new' ) ); ?>" target="_blank">Service Product</a> specifically dedicated to service subscriptions, and configure the necessary service fields.</strong></p>
				<p>Think of this product as a subscription unit. Once published, clients will be able to order it like a regular WooCommerce product. Behind the scenes, Smart Woo handles subscription setup, recurring invoicing, and usage tracking.</p>
			</div>

			<div id="step3" class="sw-knowledgebase-content smartwoo-hide">
				<h3>Step 3: All Set 🎉</h3>
				<p><strong>Your service product is now live. Customers can order it directly from your store, and Smart Woo will handle everything — create invoices, set up reminders, and track usage.</strong></p>
				<p>You can view and manage all your service orders from <a href="<?php echo esc_url( admin_url( 'admin.php?page=sw-service-orders' ) ); ?>" target="_blank">Service Orders</a> in the admin dashboard.</p>
				<p>Need a complete usage guide? <a href="https://callismart.com.ng/smart-woo-usage-guide/" target="_blank">Click here to view the full documentation</a>.</p>
				<?php echo wp_kses_post( smartwoo_pro_feature() ); ?>
			</div>
		</div>
	</div>
</div>
