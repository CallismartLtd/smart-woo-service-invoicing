<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function check_and_disable_expired_services() {
    // Get the services with end_date due today
    $services = sw_get_service(null, null, null, null, null);

    foreach ($services as $service) {
        // Check if the service status is 'Expired' or 'Suspended'
        $service_status = sw_service_status( $service->service_id);

        if ($service_status === 'Expired' || $service_status === 'Suspended'|| $service_status === 'Cancelled') {
            // Construct the URL to disable the website
            $service_url = $service->service_url; 
            $service_id = $service->service_id;

            // Construct the remote URL
            $remote_url = $service_url . '?serviceid=' . urlencode($service_id) . '&status=Expired';

            // Example using cURL to remotely visit the URL and disable the website
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $remote_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            // Log the response or perform other actions based on the result
            // For example, check if the remote operation was successful and update your records accordingly.
        }
    }
}
add_action('deactivate_expired_service', 'check_and_disable_expired_services');







// Hook the function to execute after service activation
add_action('sw_expired_service_activated', 'check_and_activate_paid_service');

function check_and_activate_paid_service() {
    // Get the services with start_date matching today
    $services = sw_get_service(null, null, null, null);

    foreach ($services as $service) {
        // Check if the service status is 'Active'
        $service_status = sw_service_status( $service->service_id);

        if ($service_status === 'Active') {
            // Get the user's billing email using the user ID
            $user_id = $service->user_id;
            $user_info = get_userdata($user_id);

            if ($user_info) {
                $user_email = $user_info->user_email;

                // Construct the URL to activate the service
                $service_url = $service->service_url;
                $service_id = $service->service_id;

                // Construct the remote URL with user_email
                $remote_url = $service_url . '/access.php?email=' . urlencode($user_email) . '&serviceid=' . urlencode($service_id) . '&status=Active';

                // Initialize cURL session
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $remote_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Execute cURL request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    // Handle cURL error
                    error_log('cURL Error: ' . curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                if ($response === false) {
                    // Handle the case where the request failed
                    error_log('cURL Request Failed');
                } else {
                    // Process the response from the remote URL, if needed
                    // You can handle the response data here
                }
            }
        }
    }
}


