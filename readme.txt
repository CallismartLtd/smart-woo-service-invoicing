=== Smart Woo Service Invoicing ===
Contributors: callismartltd  
Tags: subscription plugin, billing plugin, woocommerce invoice, service invoicing, automated billing plugin  
Requires at least: 6.4
Tested up to: 6.7.2
Requires WooCommerce: 8.0  
WooCommerce tested up to: 9.7.1
Requires PHP: 7.4  
Stable Tag: 2.3.0
License: GPLv3  
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

More Than Just A Subscription Plugin!

== Description ==  
Smart Woo Service Invoicing simplifies your subscription-based business by automating invoicing at the end of a billing cycle. Perfect for freelancers and agencies, it offers robust features to efficiently manage recurring services without breaking the bank.  

== Features ==  
- **Intuitive Admin Dashboards**: A comprehensive admin dashboard to manage service subscriptions, invoices, emails, refunds, and more.  
- **Professional Client Portal**: A modern and extensible frontend UI that allows clients to manage subscriptions, view invoices, order new services, and easily pay outstanding balances.  
- **Automatic Invoice Generation**: Automatically generates and issues new invoices at the end of a subscription billing cycle, streamlining both subscription and invoice management.  
- **Flexible Payment Options**: Smart Woo supports all payment methods available in WooCommerce, making invoice payments more convenient for your clients.  
- **Flexible Billing Cycles**: Charge for monthly, quarterly, semi-annual, and annual subscription periods.  
- **User-Friendly Interface**: Allows customers to define their service name, billing cycle, and other relevant details during the sign-up process.  
- **Robust Subscription Assets**: Supports subscriptions for downloadable, remotely protected, digital, and physical assets.  
- **Guest Invoicing**: Create invoices for users who are not registered on your website and have the invoice details emailed to them.  
- **Payment Links**: Generate auto-login or direct payment URLs for invoices, allowing clients to pay without accessing the client portal.  


== Every Smart Woo Pro Plan Includes ==

- **Advanced Stats**: Get detailed insights and visual stats (bar charts, graphs) on service subscription usage.  
- **Service Logs**: Track how clients interact with their subscriptions, including detailed activity insights.  
- **Invoice Logs**: Gain insights into all invoice interactions, including payment failures and successful payments.  
- **Invoice Items**: Add custom items to an invoice.  
- **Prorated Service Subscriptions**: Option to prorate subscriptions and reflect this in the invoicing system.  
- **Refund Feature**: Automatically handle prorated refunds when a client cancels a subscription.  
- **Service Migration**: Easily manage subscription migrations, including prorated billing during changes, with detailed logs for tracking.  
- **Email Template Customization**: Customize the templates for available emails to meet your business requirements.  
- **REST API Access**: Access subscription data via a powerful REST API (currently read-only, with future write support planned).  
- **PDF Invoice Attachments**: Automatically attach PDF invoices to email notifications for seamless client communication.  
- **Dedicated Support**: Receive dedicated support for both the free and premium versions of Smart Woo.  
- **Automatic Updates**: Ensure your plugin remains up-to-date with the latest features and security fixes.

[Try Smart Woo Pro](https://callismart.com.ng/smart-woo-service-invoicing/#go-pro)

== License ==
This project is licensed under the GPL v3.0+ License.

== Prerequisites ==

- **WordPress**: Ensure your WordPress installation is version 6.4 or higher.
- **WooCommerce**: This plugin requires WooCommerce to be installed and activated. For best performance, use WooCommerce version 8.0 or newer.
- **PHP**: A PHP version of 7.4 or later is required. Ensure your hosting environment meets this requirement.
- **Database**: Ensure your database is running MySQL version 5.6+ or MariaDB version 10.0+ for compatibility.

== Installation ==

1. Download the plugin's zip file.
2. Upload the zip file to your WordPress plugin directory.
3. Activate **Smart Woo Service Invoicing** from the WordPress plugins page.

Alternatively, install it directly from your WordPress dashboard:
1. Navigate to ‘Plugins’ -> ‘Add New’.
2. Search for ‘Smart Woo Service and Invoicing’.
3. Install and activate the plugin.

For more information and updates, visit the [Smart Woo Service Invoicing Plugin page](https://callismart.com.ng/smart-woo-service-invoicing).

== Usage ==

1. Create a product from the plugin's service product page.  
2. Set billing cycles, sign-up fees, and other options specific to the service you offer.  
3. Customers can now set up their service details during sign-up.  
4. Manage orders effortlessly through the Service Order page.  
5. After processing an order from the service order page, the service subscription will be up and running.  
6. Automatic invoice generation simplifies the renewal process.  
7. Customers can pay their invoices through the invoice email sent to them or manually log in to the portal to pay.

== Feedback and Contributions ==

We welcome and appreciate user suggestions! Feel free to submit your ideas or report issues. Together, we can make Smart Woo the ultimate solution for service billing on WooCommerce.
For more information on future releases, release notes, and feature requests, visit the [official release page](http://callismart.com.ng/smart-woo-service-invoicing-release-notes/).

== Author ==

- **Author:** Callistus Nwachukwu
- **Company:** Callismart Tech

== Contributors ==

- Callistus Nwachukwu

== Changelog ==

= 2.3.0 - 2025-03-27 =
* New Admin UI
    * Admin dashicon changed to official Smart Woo icon.
    * Invoice admin table now features bulk actions.
    * The admin's service subscription view pages UI has been refactored to provide a comprehensive overview of a service subscription and an improved UX.
    * The admin UI for viewing clients associated with a service subscription has been refactored for comprehensiveness and a modern look.
* Service Orders Refactored
    * Introduced the SmartWoo_Order object.
    * Service Order UI refactored and now features: bulk actions, order sorting by status, and order preview.
* Service Product UI
    * The service products admin page UI has been enhanced to feature: bulk actions, sorting by product status, and improved UX.
    * The product creation and edit page UI has been improved to include nearly every option found in the WooCommerce product form.
    * Product form now includes: Upsells, cross-sells, product gallery, visibility, status, and sale options.
* Fixed
    * Checkout invoices are now created for service orders made via the WooCommerce block API.
    * Client invoice items are now responsive on mobile devices.
* Added
    * Fast checkout feature: Allows clients to configure products and proceed to the checkout page on the same page. Go to settings > advanced to configure the fast checkout feature.

= 2.2.3 - 2025-02-18 =
* Fixed
    * Asset key verification bug when downloading files associated with a subscription.
* Added
    * Option to send new invoice email when creating a new invoice.
    * Guest Invoicing Feature: You can now issue invoices to clients who are not registered on your website, all you have to do is to enter their billing details and you are good to go.
    * Invoice Payment Links: You can now generate an "auto-login" payment link or a direct invoice order payment link from the admin "view invoice" page.

= [2.2.3] 2025-02-17 =
- **Fixed**
  - Asset key verification bug when downloading files associated with a subscription.

- **Added**
  - Option to send new invoice email when creating a new invoice.
  - Guest Invoicing Feature: You can now issue invoices to clients who are not registered on your website, all you have to do is to enter their billing details and you are good to go.
  Invoice Payment Links: You can now generate an "**auto-login**" payment link or a direct invoice order payment link from the admin "**view invoice**" page.
  - New Invoice Admin UI: The admin's "view-invoice" page design has been enhanced to be more sleek and modern, while the invoice creation and update form user interface has been upgraded to give you a seamless ajax experience.

For detailed updates and changes, see the [Changelog](https://github.com/CallismartLtd/smart-woo-service-invoicing/blob/main/changelog.md).

== Source Code ==

You can access the source code for the Smart Woo Service Invoicing plugin on our official [GitHub Public Repository](https://github.com/CallismartLtd/smart-woo-service-invoicing).

== Technical Support ==

We are committed to delivering a high-quality user experience and ensuring that our product is safe and bug-free. However, if you encounter any issues, we are dedicated to resolving them swiftly.

For dedicated support, please visit our [support portal](https://callismart.com.ng/support-portal). For general inquiries, use the [WordPress Support Forum](https://wordpress.org/support/plugin/smart-woo-service-invoicing).

