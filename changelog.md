## Smart Woo Service Invoicing Changelog

## [1.0.52] 2024-07-05
- Minor Bug fixes

## [1.0.51] 2024-06-08
### Added 
- Support for WP Consent API.

### Tested Upto 
- WordPress Version 6.5.5


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