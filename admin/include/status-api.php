<?php
 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function check_url_for_parameter() {
    // Check if the current URL contains the 'get_status' parameter
    if (isset($_GET['get_status'])) {
        // Get the email and service_id from the query parameters
        $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        $service_id = isset($_GET['serviceid']) ? sanitize_text_field($_GET['serviceid']) : '';

        // Check if email and service_id are provided
        if (empty($email) || empty($service_id)) {
            $response = array('error' => 'Missing or invalid "email" or "serviceid" parameter.');
        } else {
            // Map email to user ID using the WordPress database
            $user_id = get_user_id_by_email($email);

            if ($user_id !== 0) {
                // Call the sw_service_status function to retrieve the status
                $status = sw_service_status( $service_id );
                
                // Call the sw_get_service function to retrieve service details
                $service_details = sw_get_service($user_id, $service_id);

                // Check if the service details were found
                if ($service_details !== false) {
                    // Prepare the response with status and service details
                    $response = array(
                        'status' => $status,
                        'service_name' => $service_details->service_name,
                        'service_id' => $service_details->service_id,
                        'billing_cycle' => $service_details->billing_cycle,
                        'start_date' => $service_details->start_date,
                        'next_payment_date' => $service_details->next_payment_date,
                        'end_date' => $service_details->end_date,
                    );
                } else {
                    // Service details not found
                    $response = array('error' => 'Service details not found.');
                }
            } else {
                // User not found with the provided email
                $response = array('error' => 'User not found with the provided email.');
            }
        }
   
        // Output the response as JSON
        header('Content-Type: application/json');
     
        echo json_encode($response);
        exit; // End processing to prevent further output
    }
}
add_action('wp', 'check_url_for_parameter');

// Function to get user ID by email using WordPress database
function get_user_id_by_email($email) {
    global $wpdb;
    $user_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_email = %s", $email));
    return $user_id;
}












