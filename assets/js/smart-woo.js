

/**
 * Order Paid Date fetch
 */

jQuery(document).ready(function($) {
    // Add change event handler for order dropdown
    $('#order_id').on('change', function() {
        var order_id = $(this).val();
        var startDateField = $('#start_date');
        var loadingMessage = $('#loading-message'); // Add a loading message element

        // Check if an order is selected
        if (order_id !== '') {
            // Display the loading message
            loadingMessage.text('Fetching date...').show();

            // AJAX request to get order date
            $.ajax({
                type: 'POST',
                url: smart_woo_vars.ajax_url, // Use the localized AJAX URL
                data: {
                    action: 'get_order_date', // Action name to trigger the server-side function
                    order_id: order_id,
                    security: smart_woo_vars.security // Use the localized security nonce
                },
                success: function(response) {
                    if (response.success && response.data.date) {
                        // Set the retrieved date in the start date field
                        startDateField.val(response.data.date);
                    } else {
                        startDateField.val(''); // Clear the field
                        alert('Error: Unable to retrieve order date.');
                    }

                    // Hide the loading message
                    loadingMessage.hide();
                },
                error: function() {
                    startDateField.val(''); // Clear the field
                    alert('Error: Unable to retrieve order date.');

                    // Hide the loading message
                    loadingMessage.hide();
                }
            });
        }
    });
});

/**
 * JavaScript to toggle visibility on click for settings and documentation
 */
document.addEventListener("DOMContentLoaded", function() {
    var topics = document.querySelectorAll(".sw-left-column a");
    topics.forEach(function(topic) {
      topic.addEventListener("click", function(event) {
        event.preventDefault();
        var topicId = topic.getAttribute("href").substring(1);
        var instruction = document.getElementById(topicId);
        var allInstructions = document.querySelectorAll(".instruction");
        allInstructions.forEach(function(item) {
          item.style.display = "none";
        });
        instruction.style.display = "block";
      });
    });
  });
  


document.addEventListener('DOMContentLoaded', function () {
    const attachButton = document.getElementById('attach-button');
    const fileInput = document.getElementById('attachments');
    const selectedFileNames = document.querySelector('.selected-file-names ul');

    if (selectedFileNames) { // Check if the element exists
        attachButton.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            for (const file of fileInput.files) {
                const listItem = document.createElement('li');
                listItem.textContent = file.name;
                selectedFileNames.appendChild(listItem);
            }
        });
    }
});


jQuery(document).ready(function($) {
    $('#billing_cycle').on('change', function() {
        var billingCycle = $(this).val();
        var startDate = new Date($('#start_date').val());
        if (!isNaN(startDate.getTime())) {
            if (billingCycle === 'Monthly') {
                // Calculate the end date by adding 30 days to the start date
                startDate.setDate(startDate.getDate() + 30);
                // Calculate the next payment date as 30 days minus 7 days from the end date
                var nextPaymentDate = new Date(startDate);
                nextPaymentDate.setDate(nextPaymentDate.getDate() - 7);
                $('#end_date').val(formatDate(startDate));
                $('#next_payment_date').val(formatDate(nextPaymentDate));
            } else if (billingCycle === 'Quarterly') {
                // Calculate the end date by adding 4 months to the start date
                startDate.setMonth(startDate.getMonth() + 3);
                // Calculate the next payment date as 7 days before the end date
                var nextPaymentDate = new Date(startDate);
                nextPaymentDate.setDate(nextPaymentDate.getDate() - 7);
                $('#end_date').val(formatDate(startDate));
                $('#next_payment_date').val(formatDate(nextPaymentDate));
            } else if (billingCycle === 'Six Monthly' || billingCycle === 'Yearly') {
                // Calculate the end date by adding 6 months (or 1 year) to the start date
                var monthsToAdd = (billingCycle === 'Six Monthly') ? 6 : 12;
                startDate.setMonth(startDate.getMonth() + monthsToAdd);
                // Calculate the next payment date as 7 days before the end date
                var nextPaymentDate = new Date(startDate);
                nextPaymentDate.setDate(nextPaymentDate.getDate() - 7);
                $('#end_date').val(formatDate(startDate));
                $('#next_payment_date').val(formatDate(nextPaymentDate));
            }
        }
    });

    function formatDate(date) {
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }
});



 

/**
 * Quick Action button on Service page
 * @param {*} serviceName 
 * @returns 
 */

function openCancelServiceDialog(serviceName) {
    var confirmationMessage = 'You can either opt out of automatic renewal of ' + serviceName + ' by typing cancel billing or opt out of this service by cancelling it, this action cannot be reversed. Please note: our refund and returns policy will apply either way.' +
        '\n\nPlease enter your choice:' +
        '\nType "cancel service" to cancel the service' +
        '\nType "cancel billing" opt out of automatic service renewal';

    var userChoice = prompt(confirmationMessage);

    if (userChoice !== null) {
        var selectedAction = null;

        if (userChoice.toLowerCase() === 'cancel service') {
            selectedAction = 'sw_cancel_service';
        } else if (userChoice.toLowerCase() === 'cancel billing') {
            selectedAction = 'sw_cancel_billing';
        }

        if (selectedAction !== null) {
            // Update the URL with the selected action
            var newUrl = selectedAction;
            location.href = location.href + '&action=' + newUrl;
        } else {
            // Show an error message
            alert('Invalid choice. Please type "cancel service" or "suspend service" as instructed.');
        }
    }

    return false;
}


  
  jQuery(document).ready(function($){
    var mediaUploader;

    $('#upload_image_button').click(function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#product_image_id').val(attachment.id); // Update the hidden input with the image ID
            $('#image_preview').html('<img src="' + attachment.url + '" style="max-width: 100%;" />'); // Optionally display a preview
        });

        mediaUploader.open();
    });
});






jQuery(document).ready(function($) {
    // When the page is loaded, check the initial value of the grace period unit
    checkGracePeriodUnit();

    // Bind a change event to the grace period unit select
    $('select[name="grace_period_unit"]').change(function() {
        checkGracePeriodUnit();
    });

    function checkGracePeriodUnit() {
        var selectedValue = $('select[name="grace_period_unit"]').val();

        // Check if the selected value is the one for 'Never Expire'
        if (selectedValue === smart_woo_vars.never_expire_value) {
            // Clear the number field
            $('input[name="grace_period_number"]').val('');
            // Disable the number field to prevent user input
            $('input[name="grace_period_number"]').prop('disabled', true);
        } else {
            // Enable the number field
            $('input[name="grace_period_number"]').prop('disabled', false);
        }
    }
});




/** Js Code for Services page */

/**
 * Show the loading indicator by displaying the #swloader element.
 * This function sets the display property to 'block', making the loader visible.
 */
function showLoadingIndicator() {
    jQuery('#swloader').show();
}

/**
 * Hide the loading indicator by hiding the #swloader element.
 * This function sets the display property to 'none', making the loader invisible.
 */
function hideLoadingIndicator() {
    jQuery('#swloader').hide();
}


function loadBillingDetails() {
    // Show loading indicator
    showLoadingIndicator();

    // AJAX request to load billing details content
    jQuery.ajax({
        type: 'POST',
        url: smart_woo_vars.ajax_url,
        data: {
            action: 'load_billing_details'
        },
        success: function(response) {
            jQuery('#ajax-content-container').html(response);
        },
        complete: function() {
            // Hide loading indicator after AJAX request is complete
            hideLoadingIndicator();
        }
    });
}


function loadMyDetails() {
       // Show loading indicator
       showLoadingIndicator();
    // AJAX request to load My details content
    jQuery.ajax({
        type: 'POST',
        url: smart_woo_vars.ajax_url,
        data: {
            action: 'load_my_details'
        },
        success: function(response) {
            jQuery('#ajax-content-container').html(response);
        },
        complete: function() {
            // Hide loading indicator after AJAX request is complete
            hideLoadingIndicator();
        }
    });
}

function confirmEditAccount() {
    var confirmAccount = confirm("Are you sure you want to edit your information?");
    if (confirmAccount) {
        window.location.href = "http://localhost/callismart/my-account/edit-account/";
    }
}

function confirmPaymentMethods() {
    var confirmPayment = confirm("Are you sure you want to view your payment methods?");
    if (confirmPayment) {
        window.location.href = "http://localhost/callismart/my-account/payment-methods/";
    }
}

function confirmEditBilling() {
    var confirmBilling = confirm("Are you sure you want to edit your billing address?");
    if (confirmBilling) {
        window.location.href = "http://localhost/callismart/my-accont/edit-address/billing/";
    }
}



function loadAccountLogs() {
     // Show loading indicator
     showLoadingIndicator();

    // AJAX request to load Account Logs content
    jQuery.ajax({
        type: 'POST',
        url: smart_woo_vars.ajax_url,
        data: {
            action: 'load_account_logs'
        },
        success: function(response) {
            jQuery('#ajax-content-container').html(response);
        },
        complete: function() {
            // Hide loading indicator after AJAX request is complete
            hideLoadingIndicator();
        }
    });
}

function loadTransactionHistory() {
     // Show loading indicator
     showLoadingIndicator();
    // AJAX request to load Transaction History content
    jQuery.ajax({
        type: 'POST',
        url: smart_woo_vars.ajax_url,
        data: {
            action: 'load_transaction_history'
        },
        success: function(response) {
            jQuery('#ajax-content-container').html(response);
        },
        complete: function() {
            // Hide loading indicator after AJAX request is complete
            hideLoadingIndicator();
        }
    });
}

/**
 * 
 * @param {*} selectedAction 
 */

function redirectBasedOnServiceAction(selectedAction) {
    // Get the current URL
    var currentUrl = window.location.href;

    // Determine the selected page based on the action
    var selectedPage;
    switch (selectedAction) {
        case 'upgrade':
            selectedPage = 'service_upgrade';
            break;
        case 'downgrade':
            selectedPage = 'service_downgrade';
            break;
        case 'buy_new':
            selectedPage = 'buy_new_service';
            break;
        // Add more cases as needed
        default:
            selectedPage = '';
            break;
    }

    // Update the URL with the selected action and page
    var updatedUrl = updateQueryStringParameter(currentUrl, 'service_page', selectedPage);
    updatedUrl = updateQueryStringParameter(updatedUrl, 'service_action', selectedAction);

    // Redirect to the updated URL
    window.location.href = updatedUrl;
}

// Function to update or add a parameter to a URL
function updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";

    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
}







// Function to delete invoice
jQuery(document).ready(function($) {
    // Event listener for the delete button
    $(document).on('click', '.delete-invoice-button', function() {
        // Get the invoice ID from the data attribute
        var invoiceId = $(this).data( 'invoice-id' );

        // Display a confirmation dialog
        var isConfirmed = confirm('Are you sure you want to delete this invoice?');

        // If the user confirms, initiate the deletion process
        if (isConfirmed) {
            // Perform an Ajax request to delete the invoice
            $.ajax({
                type: 'POST',
                url: smart_woo_vars.ajax_url,
                data: {
                    action: 'delete_invoice',
                    invoice_id: invoiceId,
                    security: smart_woo_vars.security
                },
                success: function() {
                    // Display a success message
                    alert('Invoice deleted successfully!');
                    window.location.href = smart_woo_vars.admin_invoice_page;
                },
                
                error: function(error) {
                    // Handle the error
                    console.error('Error deleting invoice:', error);
                }
            });
        }
    });
});


// Function to delete service
jQuery(document).ready(function($) {
    // Event listener for the delete button
    $(document).on('click', '.delete-service-button', function() {
        // Get the service ID from the data attribute
        var serviceId = $(this).data( 'service-id' );

        // Display a confirmation dialog
        var isConfirmed = confirm('Are you sure you want to delete this service?');

        // If the user confirms, initiate the deletion process
        if (isConfirmed) {
            // Perform an Ajax request to delete the invoice
            $.ajax({
                type: 'POST',
                url: smart_woo_vars.ajax_url,
                data: {
                    action: 'delete_service',
                    service_id: serviceId,
                    security: smart_woo_vars.security
                },
                success: function() {
                    // Display a success message
                    alert('Service deleted successfully!');
                    window.location.href = smart_woo_vars.sw_admin_page;
                },
                
                error: function(error) {
                    // Handle the error
                    console.error('Error deleting service:', error);
                }
            });
        }
    });
});
