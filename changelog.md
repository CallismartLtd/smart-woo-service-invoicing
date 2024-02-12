# Changelog

## [1.0.0] - 2023-01-01

- Added `sw_create_service` function to enhance service creation.
- Added action hook (`sw_new_service_created`) for new service creation.
- Suspension Email now uses the correct payment link.
- Added service status to the dashboard page.
- Fixed Payment Link in suspended service mail not working.
- Fixed unable to update services.
- Security fix: Payment links now include wpnonce in the URL parameter.
- Security fix: Service Status and Order status are now validated before setting the current user.

## [1.0.1]

- Added Service Products.
- Fixed issue with Service renewal dates.
- Added grace period as an option before service suspension.
- Added action hooks Add Action Hook Before Updating Service Information for early renewed service  "do_action('sw_before_update_service', $service_id, $user_id, $new_order_id, $service)"
- Added action hooks Add Action Hook After Updating Service Information for early renewed service  "do_action('sw_after_service_renewed', $service_id, $user_id, $new_order_id, $service)"
- Added action hooks Add Action Hook Before Updating Service Information for late renewed service  "do_action('sw_before_activate_expired_service', $service_id, $user_id, $new_order_id, $service)"
- Added action hooks Add Action Hook after Updating Service Information for late renewed service  "do_action('sw_expired_service_activated', $service_id, $user_id, $new_order_id, $service)"

## [1.0.2]

- Noted: Product purchase (new service order metadata) is affecting renewal service metadata.

## New Feature

- Invoice Page will now accept more url two parameters with the default as the invoice table page.
- Unpaid Invoice notice on the Service and Invoice page.
- Will fix Remote Website Activation hook
