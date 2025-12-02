## Smart Woo Service Invoicing Changelog

### 2.5.2 [2025-12-2]
**Fixed**
* Auto-generated invoices now uses default site currency.
* Broken audio and video player for subscription assets.
* Recommended settings for guest checkout on a subscription system.
* Secure asset editor content sanitization and escape functions.

**Improved**
* Guest invoice handling.
* Guest checkout handling.
* Subscription asset rendering.
* Smart Woo security audit workflow.

### 2.5.1 [2025-10-27]
**Introduced**
* A new support section in the admin area to provide easy access to Smart Woo plugin support services.
* New support inbox feature, to simplify access to support products and services right from the admin area.

**Improved**
* Object caching for service subscriptions
* Object caching for invoices.
* Performance optimizations at automation models.
* Aggressive subscription activity logging(pro).

### 2.5 [2025-10-06]
**Fixed**
* Invoice date display issue.
* Admin notices being hidden by the new UI header.
* Service subscription search not working.
* Edge cases where multiple unpaid invoices could be generated.

**Introduced**
* A new admin dashboard featuring powerful tools to simplify subscription management.

**Improved**
* Plugin security — a full security audit has been performed across the entire codebase.
* Performance optimization, including request pagination, caching, and response limiting.
* Activity logs now provide deeper insights into subscription and invoice activities.


### 2.4.3 2025-08-18
**New Feature**
* Introduced a tinymce for subscription asset editor.
* Image gallery builder in service subscription asset editor, supports: Hover Overlay, Masonry, Card Style and Grid gallery types.
* Audio playlist builder for service subscription assets.
* Video playlist builder for service subscription assets.
* Invoice printing via admin added.

**Improved**
* Client Portal UI.
* Order history replaces transaction history in the client portal.
* Account Settings component replaces the old Settings and tools section in the client portal.
* Full client account management without leaving the client portal.
* The automation handler now rubost, now handles overdue invoices.
* Performance optimization.

**Fixed**
* Infinite payment reminder emails sent even when invoice is overdue.
* Theme styles conflict in the client portals.

**Tested**
* PHP 8.4+

### 2.4.2 - 2025-07-02
**Fixed**
* WooCommerce on demand asset loading that caused some product search input to break.
* Invoice portal dashboard bug.
* Media library won't open.

# [2.4.1] 2025-06-25
### Improved
- Onboarding  process: Introduced new setup wizard to enhance UX on first time installation.

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

# [2.2.3] 2025-02-18

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


# [2.2.1] 2024-12-25
- Fixed Sign-up fee calculation when other items are added in cart.
- Added service processed mail.
- Minor bug fixes

# [2.2.0] 2024-12-07

### Added
- Email Template preview.

### Refactored
- Email handling.
- Email option name: You may need to check your email option if certain mails are not sent.
- Email Template Editing(pro): You can now edit email templates sent by Smart Woo Service Invoicing.

# [2.1.1] 2024-11-04

### Featured
- Minor bug fixes

# [2.1.0] 2024-10-26

### Fixed
- An error of type E_ERROR: "Uncaught TypeError: abs(): Argument #1 ($num) must be of type int|float".

# [2.0.15] 2024-10-25

### Added
- A whole new UI on invoice frontend preview.
- New Invoice preview card.
- New PDF invoice design.
- Awesome print feature for invoices.
- New download buttons.
- New admin invoice and product table UX.
- Admins can download invoices from backend.
- Improved i18n button translations.
- Client's services container UI changed.
- Setting and tools UI changed.
- Unpaid invoice notice on client view subscription page(Improving your invoice revenue collection).


# [2.0.14] 2024-08-10

### Added
- Shortcode for login form [smartwoo_login_form]

### Fixed
- Invoice payment reminder not sending.
- Responsive layout for client menu.

# [2.0.13] 2024-10-05

### Added
- Ajax logout feature on the invoice and service pages.
- Invoices by status filtering for users.

### Fixed

- Admin search feature not working for mariaDB users.
- New service purchase template now available for logged out users.
- WooCommerce account menu bug.
- Minor bug fixes.

# [2.0.12] 2024-09-23
### Added 
- Logout button on service page.

### Fixed
- Hardened Security.
- New UI and UX for the admin dashboard.
- Paginated invoice table on both admin and client portal.

### Tested
- With WordPress 6.6.2
- With WooCommerce 9.3.2
- Tested with over 12,000 subscription data and page speed is proven to be fast, consistent and efficient.


### Optimized
- Database queries now faster than ever.

# [2.0.11] 2024-09-02
### Fixed
- PDF invoice download resulting to fatal error due to PHP 8.0 compatibility with mPDF library.
- Minor bug fixes

### Tested
- Tested with WordPress 6.6.2
- Tested with WooCommerce 9.2.3

# [2.0.1] 2024-08-25
### Added
- Support for downloading assets from protected resource servers.
- Option to set custom assets for a service subscription.
- Edit Assets option for services.
- Automatic updates for smart woo database.

### Fixed
- Responsiveness for product configuration form.
- Syncronization of service and invoice after order is processed.

### Compatibility
- WooCommerce tested up 9.2.2

## [2.0.0] 2024-08-09
### Added
- Service Subscription Assets.
- Downloadable Feature for service products.
- Dedicated login form for client portal.
- Notification bubble couter for New Service Orders.

### Fixed
- Hardened Security
- Deletions returning incorrect messages during Ajax

### Tested
- Tested with WooCommerce 9.1.4

## [1.1.0] 2024-07-24
- Tested with WordPress 6.6.1
- Added dedicated login form for service subscription and invoice page.
- Improved security.

## [1.0.52] 2024-06-08
### Added 
- Support for WP Consent API.

### Tested Upto 
- WordPress Version 6.5.5

## [1.0.51] 2024-07-05
- Minor Bug fixes

## [1.0.5] 2024-05-21

### Fixed
- Automation issue for some sites
- Streamlined loading process
- Revamped uninstallation process


## [1.0.4] 2024-04-15
- Fixed WooCommerce menu issue
- Streamlined loading process.
- Fixed translation related issues.
- Enhanced plugin Security.
- Tested upto WordPress 6.5.3
- Tested upto WooCommerce 8.8.3

## [1.0.3] - 2024-05-07

### Fixed 
- incorrect expiration date bug.
- Duplicate invoice creation due to sanitization logic.
- Fixed Security vulnerability.
- Added clean up feature during plugin uninstall

## [1.0.2] - 2024-04-24

### Fixed
- Security Vulnerabilities.
- Terra Wallet integration updated.
- Menu Name changed to "Smart Woo".
- Menu Priority Changed

### Added
- Option to set custom product text on shop

### Confirmed
- Tested up to WordPress version 6.5


## [1.0.1] - 20224-03-18

### Added
- Implemented the `get_refund_by_id` method in the `SmartWoo_Refund` class to fetch refund data by log ID.
- Introduced the `smartwoo_refund_completed` procedural function to initiate the refund process.
- Added comments and documentation to enhance code readability and understanding.

### Changed
- Updated the `get_refund` method to accept additional arguments for filtering refund data.
- Modified the `get_refund_by_id` method to allow specifying the status of the refund log to fetch.
- Refactored the `refunded` method in the `SmartWoo_Refund` class to use the parent class method for updating refund status.
- Adjusted the comment block for the `smartwoo_refund_completed` function to provide clear documentation.

### Fixed
- Addressed potential SQL injection vulnerability by using `$wpdb->prepare` in database queries.

## [1.0.0] - 2024-03-09

### Initial Plugin Release
- Basic functionality implemented for logging and managing invoicing data.
## Key Highlights:
- Seamless Invoicing: Smart Woo now empowers your WooCommerce store with robust invoicing capabilities.
- Commitment to Excellence: Our team is fully dedicated to the ongoing development and improvement of Smart Woo.
