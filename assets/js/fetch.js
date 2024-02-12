document.addEventListener('DOMContentLoaded', function() {
    var generateServiceIdBtn = document.getElementById('generate-service-id-btn');
    var loader = document.getElementById('swloader');

    generateServiceIdBtn.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default form submission behavior

        // Get the service name from the input
        var serviceName = document.getElementById('service-name').value;

        // Display the animated loader
        loader.style.display = 'inline-block';

        // Perform AJAX request to generate service ID
        var xhr = new XMLHttpRequest();

        xhr.open('POST', smart_woo_vars.ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        xhr.setRequestHeader('X-WP-Nonce', smart_woo_vars.security);

        xhr.onload = function() {
            // Hide the loader when the response is received
            loader.style.display = 'none';

            if (xhr.status >= 200 && xhr.status < 400) {
                // Update the generated service ID input
                document.getElementById('generated-service-id').value = xhr.responseText;
            }
        };

        xhr.send('action=generate_service_id&service_name=' + encodeURIComponent(serviceName));
    });
});
