=== Smart Woo Service Invoicing ===
Contributors: callismartltd
Tags: subscription billing, automated invoicing, recurring payments, service billing, woocommerce invoicing
Requires at least: 6.4
Tested up to: 6.8
Requires WooCommerce: 8.0
WooCommerce tested up to: 9.9.5
Requires PHP: 7.4
Stable Tag: 2.4.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Automated Service Billing and Subscription Management for WooCommerce.

== Description ==
Smart Woo Service Invoicing is a **subscription-ready invoicing solution** built for service-based businesses. Whether you offer one-off services, recurring retainers, or subscription plans, Smart Woo automates your billing cycle with **auto-generated invoices, payment reminders, and a professional client portal**.

Perfect for **freelancers, agencies, and service providers**, Smart Woo seamlessly integrates with WooCommerce, helping you manage recurring services and invoicing without manual hassle.

With powerful automation, customizable billing rules, and a client-friendly dashboard, Smart Woo lets you focus on delivering services while it handles the **repetitive payment and invoicing tasks**.

== Features ==  

* Intuitive Admin Dashboards  
Manage service subscriptions, invoices, emails, refunds, and more from a clean, organized backend dashboard.

* Professional Client Portal  
Offer your clients a modern, easy-to-use frontend where they can manage subscriptions, view invoices, order services, and pay outstanding balances effortlessly.

* Automatic Invoice Generation  
Automatically create and send invoices at the end of each subscription billing cycle — no more manual chasing or bookkeeping headaches.

* Flexible Payment Options  
Supports all payment gateways you’ve enabled in WooCommerce, ensuring your clients can pay their invoices with ease.

* Customizable Billing Cycles  
Charge clients monthly, quarterly, semi-annually, or annually, depending on your service model.

* User-Friendly Onboarding  
Let customers define service names, select billing cycles, and provide essential details during sign-up.

* Robust Subscription Asset Support  
Handle subscriptions for digital, downloadable, remotely protected, or even physical assets, with full control.

* Guest Invoicing  
Create invoices for non-registered users and send them directly via email.

* Smart Payment Links  
Generate direct payment or auto-login URLs so clients can pay invoices without needing to log into the client portal.

== Smart Woo Pro Features ==  

* Advanced Usage Stats  
Access detailed insights and visual analytics on subscription usage trends.

* Service Interaction Logs  
Track client activity and interactions with their subscriptions for better transparency.

* Detailed Invoice Logs  
Monitor all invoice events, including payment successes, failures, and adjustments.

* Custom Invoice Items  
Add custom charges or items directly to an invoice.

* Prorated Subscriptions  
Enable prorated billing for subscription upgrades or downgrades, automatically reflected in invoices.

* Automated Refunds  
Automatically process prorated refunds when a subscription is canceled mid-cycle.

* Seamless Service Migration  
Easily manage subscription migrations, including prorated adjustments, with detailed change logs.

* Customizable Email Templates  
Tailor notification emails to match your brand and communication style.

* REST API Access  
Access subscription and invoice data programmatically using a robust (currently read-only) REST API — with write support coming soon.

* PDF Invoice Attachments  
Automatically attach professionally formatted PDF invoices to outgoing email notifications.

* Dedicated Support & Updates  
Get premium support and automatic updates to keep your system secure and feature-rich.

[Get Smart Woo Pro](https://callismart.com.ng/smart-woo-service-invoicing/#go-pro)


== License ==
This project is licensed under the [GPL v3.0 or later](https://www.gnu.org/licenses/gpl-3.0.en.html).

== Prerequisites ==

- **WordPress**: Ensure your WordPress installation is version 6.4 or higher.
- **WooCommerce**: This plugin requires WooCommerce to be installed and activated. Tested and optimized for WooCommerce 8.0+.
- **PHP**: A PHP version of 7.4 or later is required. Ensure your hosting environment meets this requirement.
- **Database**: Ensure your database is running MySQL version 5.6+ or MariaDB version 10.0+ for compatibility. Most modern WordPress hosting providers already meet these requirements.

== Installation ==

= Install from WordPress Dashboard (Recommended) =
1. In your WordPress admin, go to **Plugins → Add New**.
2. Search for **Smart Woo Service Invoicing**.
3. Click **Install Now**, then **Activate**.
4. After activation, you’ll have the option to **launch the Setup Wizard** from the plugin settings page by clicking the **“Run Setup Wizard”** button.

= Install from Downloaded ZIP =
1. Download the plugin zip from WordPress.org or our website.
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**.
3. Select the zip file, click **Install Now**, then **Activate**.
4. After activation, you can launch the **Setup Wizard** from the plugin settings page (via the **“Run Setup Wizard”** button).

= WP-CLI (Advanced / Developers) =
If you have WP-CLI access, you can install and activate Smart Woo with:

    wp plugin install smart-woo-service-invoicing --activate

After activation, launch the Setup Wizard from the plugin settings page by clicking the "Run Setup Wizard" button.

**Next Steps:**  
Follow our detailed setup article: [Smart Woo Usage Guide](https://callismart.com.ng/smart-woo-usage-guide/).

For more information and updates, visit the [Smart Woo Service Invoicing Plugin page](https://callismart.com.ng/smart-woo-service-invoicing).


== Usage ==

Getting started with Smart Woo is easy — follow these simple steps to create services, manage subscriptions, and start automated invoicing:

1. **Create a Service Product**  
   Go to the plugin's **Service Product** page and create a product for the service you offer.  

2. **Configure Billing Options**  
   Set billing cycles (monthly, yearly, etc.), sign-up fees, and other service-specific options.  

3. **Customer Sign-Up**  
   Customers can configure their service details during checkout or sign-up.  

4. **Manage Service Orders**  
   Process and manage all service orders through the **Service Order** page. Once processed, the service subscription is automatically activated.  

5. **Automated Renewals & Invoicing**  
   Smart Woo will automatically generate invoices and send them via email at the end of each billing cycle.  

6. **Easy Payments**  
   Customers can pay invoices directly via the email payment link or by logging into the client portal.  

You can also read our detailed setup article: [Smart Woo Usage Guide](https://callismart.com.ng/smart-woo-usage-guide/).

== Feedback and Contributions ==

Your feedback helps shape the future of Smart Woo!  
- Have a suggestion or feature request? Let us know.  
- Found a bug or issue? Report it so we can fix it quickly.  

For release notes, upcoming features, and future updates, visit the [official release page](http://callismart.com.ng/smart-woo-service-invoicing-release-notes/).


== Changelog ==

= 2.4.2 - 2025-07-02 =
**Fixed**
 - WooCommerce on demand asset loading that caused some product search input to break.
 - Invoice portal dashboard bug.
 - Media library won't open.

= 2.4.1 - 2025-06-25 =

**Introduced**
    * New invoice editor: The invoice editor has been refactored to improve UX and seamless performance.

**Improved**
    * Onboarding process: Introduced new setup wizard to enhance user experience during first-time installation.

**Fixed**
    * Minor bug fixes and performance optimization.
    
= 2.4.0 - 2025-05-31 =

**Fixed**
    * Product page subscription banner price when product is on sale.

**Improved**
    * Improved the dashboard subscription searches by refactoring the `SmartWoo_Service_Database::search()` method to support more secure and flexible LIKE queries, better pagination, and improved caching.
    * Search queries now properly use `$wpdb->esc_like()` combined with wildcards (`%`) to safely handle user-provided search terms.
    * The fast checkout feature UI & UX has been refactored.

**Service Subscription Status Refactored**
    * The core logic for determining service subscription status has been encapsulated within the `SmartWoo_Service` class.
    * Introduced `SmartWoo_Service::get_effective_status()`, a new public method that provides the definitive service status, adhering to a strict precedence of explicit database overrides, cached values, and dynamically calculated date-based conditions.
    * Private helper methods (e.g., `is_active_condition()`, `is_due_for_renewal_condition()`, `is_in_grace_period_condition()`, `is_expired_condition()`) are now used internally by `get_effective_status()` for specific condition checks.
    * The global `smartwoo_service_status()` function has been simplified to delegate status determination to `SmartWoo_Service::get_effective_status()`.
    * Naming consistency for private status condition methods has been improved for better clarity.

**Introduced**
    * A simplified subscription analysis and stats for non-techies.
    * New Admin UI for service subscription usage statistics template in Smart Woo Pro.
    * New Admin UI for service subscription and invoice log template in Smart Woo Pro.
    * Service migration email template in Smart Woo Pro.
    * Subscription refund email template in Smart Woo Pro.

= 2.3.1 - 2025-04-23 =
* Minor bug fix.

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

For detailed updates and changes, see the [Changelog](https://github.com/CallismartLtd/smart-woo-service-invoicing/blob/main/changelog.md).

== Frequently Asked Questions ==

= 1. What is Smart Woo Service Invoicing? =
Smart Woo is a powerful WooCommerce extension that allows you to charge both recurring and one-time payments on your WordPress website. Think of it as a subscription plugin with the added flexibility to generate professional invoices for your clients — automatically at the end of their billing cycle.

**Who is Smart Woo for?**  
Smart Woo is perfect for businesses that collect payments through invoices. Whether you’re a freelancer, property manager, IT service provider, consultant, or run a physical or virtual service-based business, Smart Woo gives you the tools to streamline invoicing and payment collection effortlessly.

= 2. Do I need WooCommerce Subscriptions to use Smart Woo? =
No. Smart Woo is a standalone alternative to WooCommerce Subscriptions, featuring a robust service subscription and invoicing model — and much more.

= 3. Can I offer one-off services instead of recurring subscriptions? =
Absolutely! You can set a subscription status to **“Active (NR)”**, which means “not renewable” after the initial billing cycle expires.

= 4. Does Smart Woo work with my existing WooCommerce payment gateways? =
Yes. Smart Woo works seamlessly with any payment gateway that integrates with WooCommerce.

= 5. Can customers pay invoices without logging in? =
Yes. Payment reminder emails and new invoice emails include **auto-login payment links**, allowing customers to pay with a single click.

= 6. Does Smart Woo send automated payment reminders? =
Yes. Smart Woo automatically sends invoice payment reminders, reducing late payments and improving cash flow.

= 7. Can I create invoices for customers who are not registered on my site? =
Yes. Smart Woo supports **guest invoicing**, enabling you to bill non-registered users via email.

= 8. Do I need coding skills to set up or use Smart Woo? =
No coding skills are required! Smart Woo is designed to be user-friendly and includes a **Setup Wizard** to walk you through the initial configuration.

= 9. Does Smart Woo work with physical products, or just services? =
Yes. As long as the product or service has a start, due, and end date, Smart Woo can model it as a subscription. It includes an **asset feature**, making it easy to handle physical items (houses, apartments, water billing) as well as virtual or downloadable assets.

= 10. What’s the difference between the free and Pro version? =
The free version includes all the core features needed to manage subscriptions, automate invoicing, and collect payments.  
**Smart Woo Pro** adds advanced features like prorated billing, detailed analytics, automated refunds, custom invoice items, professional PDF attachments, and premium support.  
[View the Pro features here.](https://callismart.com.ng/smart-woo-service-invoicing/#go-pro)

== Screenshots ==

1. Client Portal dashboard view.
2. Subscription usage analytics.
3. Admin view of all client services and invoices.
4. Subscription and accounting logs.
5. Additional client information screen.
6. Invoice logs and history.


== Source Code ==

You can access the source code for the Smart Woo Service Invoicing plugin on our official [GitHub Public Repository](https://github.com/CallismartLtd/smart-woo-service-invoicing).

== Technical Support ==

We are committed to delivering a high-quality user experience and ensuring that our product is safe and bug-free. However, if you encounter any issues, we are dedicated to resolving them swiftly.

For dedicated support, please visit our [support portal](https://callismart.com.ng/support-portal). For general inquiries, use the [WordPress Support Forum](https://wordpress.org/support/plugin/smart-woo-service-invoicing).