## Smart Woo Service Invoicing Changelog

All notable changes to the "Smart Woo Service and Invoicing" plugin will be documented in this file.

## [1.0.2] - 20224-03-25

### Fixed
- Security Vulnerability.
- Tested up to WordPress version 6.5


## [1.0.1] - 20224-03-18

### Added
- Implemented the `get_refund_by_id` method in the `Sw_Refund` class to fetch refund data by log ID.
- Introduced the `sw_refund_completed` procedural function to initiate the refund process.
- Added comments and documentation to enhance code readability and understanding.

### Changed
- Updated the `get_refund` method to accept additional arguments for filtering refund data.
- Modified the `get_refund_by_id` method to allow specifying the status of the refund log to fetch.
- Refactored the `refunded` method in the `Sw_Refund` class to use the parent class method for updating refund status.
- Adjusted the comment block for the `sw_refund_completed` function to provide clear documentation.

### Fixed
- Addressed potential SQL injection vulnerability by using `$wpdb->prepare` in database queries.

## [1.0.0] - 2024-03-09

### Initial Plugin Release
- Basic functionality implemented for logging and managing invoicing data.
## Key Highlights:
- Seamless Invoicing: Smart Woo now empowers your WooCommerce store with robust invoicing capabilities.
- Commitment to Excellence: Our team is fully dedicated to the ongoing development and improvement of Smart Woo.