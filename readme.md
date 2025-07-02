# Smart Woo Service Invoicing

**Author:** [Callistus Nwachukwu](https://github.com/CallismartLtd)

![Subscription Plugin](https://img.shields.io/badge/Subscription%20Plugin-blue) ![Billing Plugin](https://img.shields.io/badge/Billing%20Plugin-green) ![WooCommerce Invoice](https://img.shields.io/badge/WooCommerce%20Invoice-yellow) ![Service Invoicing](https://img.shields.io/badge/Service%20Invoicing-orange) ![Automated Billing Plugin](https://img.shields.io/badge/Automated%20Billing%20Plugin-red)  

**Requires at least:** 6.0  
**Tested up to:** 6.8.1  
**Requires WooCommerce:** 8.0  
**WooCommerce Tested:** 9.9.5
**Requires PHP:** 7.4  
**Stable Tag:** 2.4.2
**License:** GPLv3  
**License URI:** [https://www.gnu.org/licenses/gpl-3.0.en.html](https://www.gnu.org/licenses/gpl-3.0.en.html)

Integrate powerful service subscriptions and invoicing directly into your online store!


**Smart Woo Service Invoicing** supercharges your service-based business by automating invoicing at the end of each billing cycle. Built to integrate seamlessly with your WooCommerce-powered store, it’s the perfect solution for freelancers, agencies, and service providers looking to streamline subscription management without straining their budget.

With powerful automation, a professional client portal, and flexible billing tools, Smart Woo helps you focus on growing your business while it takes care of the repetitive tasks behind the scenes.

---

## Features

- **Intuitive Admin Dashboards**  
  Manage service subscriptions, invoices, emails, refunds, and more from a clean, organized backend dashboard.

- **Professional Client Portal**  
  Offer your clients a modern, easy-to-use frontend where they can manage subscriptions, view invoices, order services, and pay outstanding balances effortlessly.

- **Automatic Invoice Generation**  
  Automatically create and send invoices at the end of each subscription billing cycle — no more manual chasing or bookkeeping headaches.

- **Flexible Payment Options**  
  Supports all payment gateways you’ve enabled in WooCommerce, ensuring your clients can pay their invoices with ease.

- **Customizable Billing Cycles**  
  Charge clients monthly, quarterly, semi-annually, or annually, depending on your service model.

- **User-Friendly Onboarding**  
  Let customers define service names, select billing cycles, and provide essential details during sign-up.

- **Robust Subscription Asset Support**  
  Handle subscriptions for digital, downloadable, remotely protected, or even physical assets, with full control.

- **Guest Invoicing**  
  Create invoices for non-registered users and send them directly via email.

- **Smart Payment Links**  
  Generate direct payment or auto-login URLs so clients can pay invoices without needing to log into the client portal.

---

## Every Smart Woo Pro Plan Includes

- **Advanced Usage Stats**  
  Access detailed insights and visual analytics on subscription usage trends.

- **Service Interaction Logs**  
  Track client activity and interactions with their subscriptions for better transparency.

- **Detailed Invoice Logs**  
  Monitor all invoice events, including payment successes, failures, and adjustments.

- **Custom Invoice Items**  
  Add custom charges or items directly to an invoice.

- **Prorated Subscriptions**  
  Enable prorated billing for subscription upgrades or downgrades, automatically reflected in invoices.

- **Automated Refunds**  
  Automatically process prorated refunds when a subscription is canceled mid-cycle.

- **Seamless Service Migration**  
  Easily manage subscription migrations, including prorated adjustments, with detailed change logs.

- **Customizable Email Templates**  
  Tailor notification emails to match your brand and communication style.

- **REST API Access**  
  Access subscription and invoice data programmatically using a robust (currently read-only) REST API — with write support coming soon.

- **PDF Invoice Attachments**  
  Automatically attach professionally formatted PDF invoices to outgoing email notifications.

- **Dedicated Support & Updates**  
  Get premium support and automatic updates to keep your system secure and feature-rich.

---

👉 **[Get Smart Woo Pro](https://callismart.com.ng/smart-woo-service-invoicing/#go-pro)**




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
# 2.4.2 - 2025-07-02
### Fixed
 - WooCommerce on demand asset loading that caused some product search input to break.
 - Invoice portal dashboard bug.
 - Media library wont open.

# [2.4.1] 2025-06-25
### Improved
- Onbording process: Introduced new setup wizard to enhance UX on first time installation.

### Fixed
- Minor bug fixes and performance optimization.

### Introduced
- New invoice editor: The invoice editor has now been refactored to improve UX and seemless performance.

# [2.4.0] 2025-05-31
### Fixed
- Product page subscription banner price when product is on sale.

### Improved
- Improved the dashboard subscription searches by refactoring the `SmartWoo_Service_Database::search()` method to support more secure and flexible LIKE queries, better pagination, and improved caching.
- Search queries now properly use `$wpdb->esc_like()` combined with wildcards (`%`) to safely handle user-provided search terms.
- The fast checkout feature UI & UX has been refactored.

### Service Subscription Status Refactored
- The core logic for determining service subscription status has been encapsulated within the `SmartWoo_Service` class.
- Introduced `SmartWoo_Service::get_effective_status()`, a new public method that provides the definitive service status, adhering to a strict precedence of explicit database overrides, cached values, and dynamically calculated date-based conditions.
- Private helper methods (e.g., `is_active_condition()`, `is_due_for_renewal_condition()`, `is_in_grace_period_condition()`, `is_expired_condition()`) are now used internally by `get_effective_status()` for specific condition checks.
- The global `smartwoo_service_status()` function has been simplified to delegate status determination to `SmartWoo_Service::get_effective_status()`.
- Naming consistency for private status condition methods has been improved for better clarity.

### Introduced
- A simplified subscription analysis and stats for non-techies.
- New Admin UI for service subscription usage statistics template in Smart Woo Pro.
- New Admin UI for service subscription and invoice log template in Smart Woo Pro.
- Service migration email template in Smart Woo Pro.
- Subscription refund email template in Smart Woo Pro.

# [2.3.1] 2025-03-27
### Minor bug fix.



For detailed updates and changes, see the [Changelog](https://github.com/CallismartLtd/smart-woo-service-invoicing/blob/main/changelog.md).

## Source Code

The source code for the Smart Woo Service Invoicing plugin can be found on our official [GitHub Public Repository](https://github.com/CallismartLtd/smart-woo-service-invoicing).

## Technical Support

We are dedicated to providing an excellent user experience and invest significant effort to ensure our product is safe and free of bugs. However, we understand that issues may arise, and we are committed to addressing them promptly.

For dedicated support, please visit our [support portal](https://callismart.com.ng/support-portal). This portal is not intended for general inquiries; please use the [WordPress Support](https://wordpress.org/support/plugin/smart-woo-service-invoicing) forum for that purpose.
