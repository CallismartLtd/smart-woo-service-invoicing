# Smart Woo Service Invoicing

**Author:** [Callistus Nwachukwu](https://github.com/CallismartLtd)

![Subscription Plugin](https://img.shields.io/badge/Subscription%20Plugin-blue) ![Billing Plugin](https://img.shields.io/badge/Billing%20Plugin-green) ![WooCommerce Invoice](https://img.shields.io/badge/WooCommerce%20Invoice-yellow) ![Service Invoicing](https://img.shields.io/badge/Service%20Invoicing-orange) ![Automated Billing Plugin](https://img.shields.io/badge/Automated%20Billing%20Plugin-red)  

**Requires at least:** 6.0  
**Tested up to:** 6.7.2  
**Requires WooCommerce:** 8.0  
**WooCommerce Tested:** 9.7.1
**Requires PHP:** 7.4  
**Stable Tag:** 2.3.1
**License:** GPLv3  
**License URI:** [https://www.gnu.org/licenses/gpl-3.0.en.html](https://www.gnu.org/licenses/gpl-3.0.en.html)

Integrate powerful service subscriptions and invoicing directly into your online store!

## Description

Smart Woo Service Invoicing simplifies your subscription-based business by automating invoicing at the end of a billing cycle. Perfect for freelancers and agencies, it offers robust features to efficiently manage recurring services without breaking the bank.

## Features

- **Professional Client Portal**: A modern frontend UI to allows clients to manage subscriptions, view invoices, and easily pay outstanding balances.
- **Automatic Invoice Generation**: Automatically generates and issues invoices at the end of each billing cycle, making the management process seamless.
- **Flexible Payment Options**: Smart Woo works with any payment method supported by WooCommerce, making invoice payments easier for your clients.
- **Flexible Billing Cycles**: Charge for Monthly, Quarterly, Six-Monthly, and Yearly service subscription periods.
- **User-Friendly Interface**: Empower customers to set their service name, billing cycle, and other relevant data during the purchase (sign-up) process.
- **Robust Subscription Assets**: Supports downloadable, remote-protected (resource), digital, and physical asset subscriptions.
- **Customized Notifications**: Choose how to receive notifications about service purchases, renewals, expirations, and stay informed about your subscriptions.
- **Stats and Usage**: Monitor service subscription performance directly from the admin dashboard.
- **Automated Refunds**: Provides efficient refund calculations and automatic refunds.
- **Prorated Service Subscriptions**: Option to prorate subscriptions and reflect this in the invoicing system.
- **Service Subscription Migration**: Flexible options allow clients to migrate from their current subscription to another, directly from their dashboard.

## Every Smart Woo Pro Plan Includes:

### Advanced Stats  
Get detailed insights and visual statistics (bar charts, graphs) on service subscription usage.

### Service Logs  
Track how clients interact with their subscriptions, including detailed activity insights.

### Invoice Logs  
Monitor all invoice interactions, including payment failures and successful transactions.

### Invoice Items  
Add custom items to invoices as needed.

### Refund Feature  
Automatically handle prorated refunds when a client cancels a subscription.

### Service Migration  
Easily manage subscription migrations, including prorated billing adjustments during changes, with detailed logs for tracking.

### Email Template Customization  
Customize email templates to align with your business requirements.

### REST API Access  
Access subscription data via a powerful REST API (currently read-only, with future write support planned).

### PDF Invoice Attachments  
Automatically attach PDF invoices to email notifications for seamless client communication.

### Dedicated Support  
Receive dedicated support for both the free and premium versions of Smart Woo.

### Automatic Updates  
Ensure your plugin remains up to date with the latest features and security enhancements.

#### [Try Smart Woo Pro](https://callismart.com.ng/smart-woo-service-invoicing/#go-pro)


## License

This project is licensed under the GPL-v3.0+ License.

## Screenshots

![Screenshot 1](assets/images/smart-woo-img.png)  
![Screenshot 2](assets/images/service-page.png)  
![Screenshot 3](assets/images/smartwoopro-adv-stats.png)  
![Screenshot 4](assets/images/invoice-sample.png)

## Prerequisites

- **WordPress**: Ensure your WordPress installation is version 6.0 or later.
- **WooCommerce**: Smart Woo Service Invoicing requires WooCommerce to be installed and activated on your WordPress website. For optimal performance, be sure to have WooCommerce version 8.0.0 or later.
- **PHP**: This plugin requires PHP version 7.4 or later. Verify that your hosting environment meets this requirement.
- **SQL**: Ensure that your database supports at least MySQL version 5.6 or MariaDB version 10.0.

## Installation

1. Download the plugin zip file.
2. Upload the plugin to your WordPress site plugin directory.
3. Activate the Smart Woo Service Invoicing Plugin from the WordPress plugins page.

Alternatively, you can install the plugin directly from your WordPress dashboard:
1. Go to 'Plugins' -> 'Add New'.
2. Search for 'Smart Woo Service Invoicing'.
3. Install and activate the plugin.

For more details and updates, visit the [Smart Woo Service Invoicing Plugin page](https://callismart.com.ng/smart-woo-service-invoicing).

## Usage

1. Create a 'Service Product' from the plugin's service product page.
2. Set billing cycles, sign-up fees, and other parameters specific to the service you offer.
3. Customers can now personalize their service details during sign-up.
4. Manage orders effortlessly through the Service Order page.
5. Automatic invoice generation simplifies the renewal process.

## Feedback and Contributions

We welcome and appreciate user suggestions! Feel free to submit your ideas or report issues. Together, we can make Smart Woo the ultimate solution for service billing on WooCommerce.

## Author

- **Author:** Callistus Nwachukwu  
- **Company:** Callismart Tech

## Contributors

- Callistus Nwachukwu

## Changelog
# [2.3.1] 2025-03-27
### Minor bug fix.

# [2.3.0] 2025-03-27

### New Admin UI
- Admin dashicon changed to official Smart Woo icon.
- Invoice admin table now features bulk actions.
- The admin's service subscription view pages UI has been refactored to provide a comprehensive overview of a service subscription and an improved UX.
- The admin UI for viewing clients associated with a service subscription has been refactored for comprehensiveness and a modern look.

### Service Orders Refactored
- Introduced the SmartWoo_Order object.
- Service Order UI refactored and now features: bulk actions, order sorting by status, and order preview.

### Service Product UI
- The service products admin page UI has been enhanced to feature: bulk actions, sorting by product status, and improved UX.
- The product creation and edit page UI has been improved to include nearly every option found in the WooCommerce product form.
- Product form now includes: Upsells, cross-sells, product gallery, visibility, status, and sale options.

### Fixed
- Checkout invoices are now created for service orders made via the WooCommerce block API.
- Client invoice items are now responsive on mobile devices.

## Added
- Fast checkout feature: Allows clients to configure products and proceed to the checkout page on the same page. Go to settings > advanced to configure the fast checkout feature.

# [2.2.3] 2025-02-17

### Fixed
- Asset key verification bug when downloading files associated with a subscription.

### Added
- Option to send new invoice email when creating a new invoice.
- Guest Invoicing Feature: You can now issue invoices to clients who are not registered on your website, all you have to do is to enter their billing details and you are good to go.
Invoice Payment Links: You can now generate an "**auto-login**" payment link or a direct invoice order payment link from the admin "**view invoice**" page.
- New Invoice Admin UI: The admin's "view-invoice" page design has been enhanced to be more sleek and modern, while the invoice creation and update form user interface has been upgraded to give you a seamless ajax experience.

# [2.2.2] 2025-01-28

### Fixed
- Minor bug fixes

For detailed updates and changes, see the [Changelog](https://github.com/CallismartLtd/smart-woo-service-invoicing/blob/main/changelog.md).

## Source Code

The source code for the Smart Woo Service Invoicing plugin can be found on our official [GitHub Public Repository](https://github.com/CallismartLtd/smart-woo-service-invoicing).

## Technical Support

We are dedicated to providing an excellent user experience and invest significant effort to ensure our product is safe and free of bugs. However, we understand that issues may arise, and we are committed to addressing them promptly.

For dedicated support, please visit our [support portal](https://callismart.com.ng/support-portal). This portal is not intended for general inquiries; please use the [WordPress Support](https://wordpress.org/support/plugin/smart-woo-service-invoicing) forum for that purpose.
