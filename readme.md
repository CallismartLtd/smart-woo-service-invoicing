# Smart Woo Service Invoicing

**Contributors:** callismartltd  
![Subscription Plugin](https://img.shields.io/badge/Subscription%20Plugin-blue) ![Billing Plugin](https://img.shields.io/badge/Billing%20Plugin-green) ![WooCommerce Invoice](https://img.shields.io/badge/WooCommerce%20Invoice-yellow) ![Service Invoicing](https://img.shields.io/badge/Service%20Invoicing-orange) ![Automated Billing Plugin](https://img.shields.io/badge/Automated%20Billing%20Plugin-red)  
**Requires at least:** 6.0  
**Tested up to:** 6.6.1  
**Requires WooCommerce:** 8.0  
**WooCommerce Tested:** 9.2.3  
**Requires PHP:** 7.4  
**Stable Tag:** 2.0.11  
**License:** GPLv3  
**License URI:** [https://www.gnu.org/licenses/gpl-3.0.en.html](https://www.gnu.org/licenses/gpl-3.0.en.html)

Integrate powerful service subscriptions and invoicing directly into your online store!

## Description

The smart way to charge recurring service subscriptions through automated invoicing. **Smart Woo Service Invoicing** is a billing plugin that serves the purpose of issuing invoices at the end of a client's billing cycle. This plugin is designed as a robust, cost-effective billing solution tailored for freelancers offering diverse services.

## Features

- **Dedicated Product Type:** Has a dedicated product type designed for subscription.
- **Flexible Billing Cycles:** Charge monthly, quarterly, six-monthly, and yearly service subscription periods.
- **User-Friendly Interface:** Empower customers to set their service name, billing cycle, and other relevant data during the purchase (sign-up) process.
- **Easy Service Order Management:** All orders for this product type are considered New Service Orders, allowing easy processing via the Service Orders page in the admin.
- **Automatic Invoice Generation:** Auto generates new invoice(s) at the end of each billing cycle, making the whole management process seamless.
- **User-Friendly Pages:** Dedicated frontend pages for Service Management and Invoice Management for clients using shortcodes.
- **Admin Notifications:** Get notified of service purchases, renewals, expirations, and stay informed about your subscriptions.
- **Stats and Usage:** You are in charge; monitor subscription stats and usage right from the admin dashboard. Your clients can also view the same stats for transparency.
- **Prorate Service Subscriptions:** You have the option to prorate your subscription and reflect the same in your invoicing system.
- **Service Subscription Migration:** Flexible options allow clients to migrate from their current subscription to another right from their dashboard.
- **Mini Containers:** Show invoices and subscriptions of the current user in mini cards or containers using shortcodes.

## License

This project is licensed under the GPL-v3.0+ License.

## Screenshots

![Screenshot 1](assets/images/smart-woo-img.png)  
![Screenshot 2](assets/images/service-page.png)  
![Screenshot 3](assets/images/invoice-sample.png)

## Prerequisites

- **WordPress:** Ensure that your WordPress installation is version 6.0 or later.
- **WooCommerce:** Smart Woo Service Invoicing requires WooCommerce to be installed and activated on your WordPress website. For optimal performance, be sure to have WooCommerce version 8.0.0 or later.
- **PHP:** This plugin requires PHP version 7.4 or later. Verify that your hosting environment meets this requirement.
- **SQL:** Ensure that your database supports at least MySQL version 5.6 or MariaDB version 10.0.

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

# [2.0.11] 2024-09-02
### Fixed
- PDF invoice download resulting in a fatal error due to PHP 8.0 compatibility with mPDF library.
- Minor bug fixes.

### Tested
- Tested with WordPress 6.6.2.
- Tested with WooCommerce 9.2.3.

# [2.0.1] 2024-08-25
### Added
- Support for downloading assets from protected resource servers.
- Option to set custom assets for a service subscription.
- Edit Assets option for services.
- Automatic updates for Smart Woo database.

### Fixed
- Responsiveness for product configuration form.
- Synchronization of service and invoice after order is processed.

### Compatibility
- WooCommerce tested up to 9.2.2.

For detailed updates and changes, see the [Changelog](https://github.com/CallismartLtd/smart-woo-service-invoicing/blob/main/changelog.md).

## Source Code

The source code for the Smart Woo Service Invoicing plugin can be found on our official [GitHub Public Repository](https://github.com/CallismartLtd/smart-woo-service-invoicing).

## Technical Support

We are dedicated to providing an excellent user experience and invest significant effort to ensure our product is safe and free of bugs. However, we understand that issues may arise, and we are committed to addressing them promptly.

For dedicated support, please visit our [support portal](https://callismart.com.ng/support-portal). This portal is not intended for general inquiries; please use the [WordPress Support](https://wordpress.org/support/plugin/smart-woo-service-invoicing) forum for that purpose.
