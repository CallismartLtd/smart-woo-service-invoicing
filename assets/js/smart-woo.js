document.addEventListener(
    "DOMContentLoaded",
    function () {
        var topics = document.querySelectorAll(".sw-left-column a");
        var image = document.getElementById("first-display");

        topics.forEach(
            function (topic) {
                topic.addEventListener(
                    "click",
                    function (event) {
                        event.preventDefault();
                        var topicId = topic.getAttribute("href").substring(1);
                        var instruction = document.getElementById(topicId);
                        var allInstructions = document.querySelectorAll(".instruction");

                        // Hide all instructions
                        allInstructions.forEach(
                            function (item) {
                                item.style.display = "none";
                            }
                        );

                        // Hide the image
                        image.style.display = "none";

                        // Display the target instruction
                        instruction.style.display = "block";
                    }
                );
            }
        );
    }
);




document.addEventListener(
	'DOMContentLoaded',
	function () {
		const attachButton      = document.getElementById( 'attach-button' );
		const fileInput         = document.getElementById( 'attachments' );
		const selectedFileNames = document.querySelector( '.selected-file-names ul' );

		if (selectedFileNames) { // Check if the element exists
			attachButton.addEventListener(
				'click',
				function () {
					fileInput.click();
				}
			);

			fileInput.addEventListener(
				'change',
				function () {
					for (const file of fileInput.files) {
						const listItem       = document.createElement( 'li' );
						listItem.textContent = file.name;
						selectedFileNames.appendChild( listItem );
					}
				}
			);
		}
	}
);

jQuery( document ).ready(
	function ($) {
		$( '#billing_cycle' ).on(
			'change',
			function () {
				var billingCycle = $( this ).val();
				var startDate    = new Date( $( '#start_date' ).val() );
				if ( ! isNaN( startDate.getTime() )) {
					if (billingCycle === 'Monthly') {
						// Calculate the end date by adding 30 days to the start date
						startDate.setDate( startDate.getDate() + 30 );
						// Calculate the next payment date as 30 days minus 7 days from the end date
						var nextPaymentDate = new Date( startDate );
						nextPaymentDate.setDate( nextPaymentDate.getDate() - 7 );
						$( '#end_date' ).val( formatDate( startDate ) );
						$( '#next_payment_date' ).val( formatDate( nextPaymentDate ) );
					} else if (billingCycle === 'Quarterly') {
						// Calculate the end date by adding 4 months to the start date
						startDate.setMonth( startDate.getMonth() + 3 );
						// Calculate the next payment date as 7 days before the end date
						var nextPaymentDate = new Date( startDate );
						nextPaymentDate.setDate( nextPaymentDate.getDate() - 7 );
						$( '#end_date' ).val( formatDate( startDate ) );
						$( '#next_payment_date' ).val( formatDate( nextPaymentDate ) );
					} else if (billingCycle === 'Six Monthly' || billingCycle === 'Yearly') {
						// Calculate the end date by adding 6 months (or 1 year) to the start date
						var monthsToAdd = (billingCycle === 'Six Monthly') ? 6 : 12;
						startDate.setMonth( startDate.getMonth() + monthsToAdd );
						// Calculate the next payment date as 7 days before the end date
						var nextPaymentDate = new Date( startDate );
						nextPaymentDate.setDate( nextPaymentDate.getDate() - 7 );
						$( '#end_date' ).val( formatDate( startDate ) );
						$( '#next_payment_date' ).val( formatDate( nextPaymentDate ) );
					}
				}
			}
		);

		function formatDate(date) {
			var year  = date.getFullYear();
			var month = String( date.getMonth() + 1 ).padStart( 2, '0' );
			var day   = String( date.getDate() ).padStart( 2, '0' );
			return year + '-' + month + '-' + day;
		}
	}
);





/**
 * Quick Action button on Service page
 *
 * @param {*} serviceName
 * @returns
 */

document.addEventListener( 'DOMContentLoaded', function() {
    var quickActionButton = document.getElementById( 'sw-service-quick-action' );

    if ( quickActionButton ) {
        quickActionButton.addEventListener( 'click', function() {
            var serviceName = quickActionButton.dataset.serviceName;
            openCancelServiceDialog( serviceName );
        });
    }
});

function openCancelServiceDialog( serviceName ) {
    var confirmationMessage = 'You can either opt out of automatic renewal of ' + serviceName + ' by typing "cancel billing" or opt out of this service by typing "cancel service". This action cannot be reversed. Please note: our refund and returns policy will apply either way.' +
        '\n\nPlease enter your choice:' +
        '\nType "cancel service" to cancel the service' +
        '\nType "cancel billing" to opt out of automatic service renewal';

    var userChoice = prompt( confirmationMessage );

    if ( userChoice !== null ) {
        var selectedAction = null;

        if ( userChoice.toLowerCase() === 'cancel service' ) {
            selectedAction = 'sw_cancel_service';
        } else if ( userChoice.toLowerCase() === 'cancel billing' ) {
            selectedAction = 'sw_cancel_billing';
        }

        if ( selectedAction !== null ) {
            // Update the URL with the selected action
            var newUrl = updateQueryStringParameter( window.location.href, 'action', selectedAction );
            window.location.href = newUrl;
        } else {
            // Show an error message
            alert( 'Oops! you mis-typed it. Please type "cancel service" or "cancel billing" as instructed.' );
        }
    }

    return false;
}



jQuery( document ).ready(
	function ($) {
		var mediaUploader;

		$( '#upload_image_button' ).click(
			function (e) {
				e.preventDefault();

				if (mediaUploader) {
						mediaUploader.open();
						return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media(
					{
						title: 'Choose Image',
						button: {
							text: 'Choose Image'
						},
						multiple: false
					}
				);

				mediaUploader.on(
					'select',
					function () {
						var attachment = mediaUploader.state().get( 'selection' ).first().toJSON();
						$( '#product_image_id' ).val( attachment.id ); // Update the hidden input with the image ID
						$( '#image_preview' ).html( '<img src="' + attachment.url + '" style="max-width: 100%;" />' ); // Optionally display a preview
					}
				);

				mediaUploader.open();
			}
		);
	}
);






jQuery( document ).ready(
	function ($) {
		// When the page is loaded, check the initial value of the grace period unit
		checkGracePeriodUnit();

		// Bind a change event to the grace period unit select
		$( 'select[name="grace_period_unit"]' ).change(
			function () {
				checkGracePeriodUnit();
			}
		);

		function checkGracePeriodUnit() {
			var selectedValue = $( 'select[name="grace_period_unit"]' ).val();

			// Check if the selected value is the one for 'Never Expire'
			if (selectedValue === smart_woo_vars.never_expire_value) {
				// Clear the number field
				$( 'input[name="grace_period_number"]' ).val( '' );
				// Disable the number field to prevent user input
				$( 'input[name="grace_period_number"]' ).prop( 'disabled', true );
			} else {
				// Enable the number field
				$( 'input[name="grace_period_number"]' ).prop( 'disabled', false );
			}
		}
	}
);




/** Js Code for Services page */

/**
 * Show the loading indicator by displaying the #swloader element.
 * This function sets the display property to 'block', making the loader visible.
 */
function showLoadingIndicator() {
    jQuery('#swloader').css('display', 'inline-block');
}


/**
 * Hide the loading indicator by hiding the #swloader element.
 * This function sets the display property to 'none', making the loader invisible.
 */
function hideLoadingIndicator() {
		jQuery( '#swloader' ).hide();
}


function confirmEditAccount() {
		var confirmAccount = confirm( "Are you sure you want to edit your information?" );
	if (confirmAccount) {
		window.location.href = smart_woo_vars.woo_my_account_edit;
	}
}

function confirmPaymentMethods() {
		var confirmPayment = confirm( "Are you sure you want to view your payment methods?" );
	if (confirmPayment) {
		window.location.href = smart_woo_vars.woo_payment_method_edit;
	}
}

function confirmEditBilling() {
		var confirmBilling = confirm( "Are you sure you want to edit your billing address?" );
	if (confirmBilling) {
		window.location.href = smart_woo_vars.woo_billing_eddress_edit;
	}
}

/**
 * Event listener for billing details button.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	var billingButton = document.getElementById( 'sw-billing-details' );

	if ( billingButton ) {
		billingButton.addEventListener( 'click', function() {
			loadBillingDetails();
		} );
	}
});

/**
 * Show modal for User's billing details
 */
function loadBillingDetails() {
	// Show loading indicator
	showLoadingIndicator();

	// AJAX request to load billing details content
	jQuery.ajax(
		{
			type: 'POST',
			url: smart_woo_vars.ajax_url,
			data: {
				action: 'load_billing_details',
				security: smart_woo_vars.security
			},
			success: function (response) {
				jQuery( '#ajax-content-container' ).html( response );

				var editBilling	  = document.getElementById( 'edit-billing-address' );
				
				if ( editBilling ) {
					editBilling.addEventListener( 'click', function() {
						confirmEditBilling();
					});
				}

			},
			complete: function () {
				
				// Hide loading indicator after AJAX request is complete
				hideLoadingIndicator();
			}
			
		}
	);
}

/**
 * Event Listener for my details button
 */
document.addEventListener('DOMContentLoaded', function() {
	var detailsButton = document.getElementById( 'sw-load-user-details' );

	if ( detailsButton ) {
		detailsButton.addEventListener( 'click', function() {
			loadMyDetails();
		});
	}
});
/**
 * Show modal for User's details
 */
function loadMyDetails() {
	// Show loading indicator
	showLoadingIndicator();
	// AJAX request to load My details content
	jQuery.ajax(
		{
			type: 'POST',
			url: smart_woo_vars.ajax_url,
			data: {
				action: 'load_my_details',
				security: smart_woo_vars.security
			},
			success: function (response) {
				jQuery( '#ajax-content-container' ).html( response );

				var editDetailsButton = document.getElementById( 'edit-account-button' );
				var viewPaymentButton = document.getElementById( 'view-payment-button' );

				if ( editDetailsButton ) {
					editDetailsButton.addEventListener( 'click', function() {
						confirmEditAccount();
					});
				}

				if ( viewPaymentButton ) {
					viewPaymentButton.addEventListener( 'click', function(){
						confirmPaymentMethods();
					});
				}
			},
			complete: function () {
				// Hide loading indicator after AJAX request is complete
				hideLoadingIndicator();
			}
		}
	);
}

/**
 * Event listener for account log button
 */
document.addEventListener( 'DOMContentLoaded', function() {
    var accountLogButton = document.getElementById( 'sw-account-log' );

    if ( accountLogButton ) {
        accountLogButton.addEventListener( 'click', function() {
            loadAccountLogs();
        });
    }
});

/**
 * Show a modal for account logs.
 * 
 */
function loadAccountLogs() {
		// Show loading indicator
		showLoadingIndicator();

		// AJAX request to load Account Logs content
		jQuery.ajax(
			{
				type: 'POST',
				url: smart_woo_vars.ajax_url,
				data: {
					action: 'load_account_logs',
					security: smart_woo_vars.security
				},
				success: function (response) {
					jQuery( '#ajax-content-container' ).html( response );
				},
				complete: function () {
					// Hide loading indicator after AJAX request is complete
					hideLoadingIndicator();
				}
			}
		);
}

/**
 * Event listener for transaction history
 */
document.addEventListener('DOMContentLoaded', function() {
	var trButton = document.getElementById('sw-load-transaction-history');

	if (trButton) {
		trButton.addEventListener('click', function () {
			loadTransactionHistory();
		});
	}
});

/**
 * Show a modal for transaction history.
 */
function loadTransactionHistory() {
		// Show loading indicator
		showLoadingIndicator();
		// AJAX request to load Transaction History content
		jQuery.ajax(
			{
				type: 'POST',
				url: smart_woo_vars.ajax_url,
				data: {
					action: 'load_transaction_history',
					security: smart_woo_vars.security
				},
				success: function (response) {
					jQuery( '#ajax-content-container' ).html( response );
				},
				complete: function () {
					// Hide loading indicator after AJAX request is complete
					hideLoadingIndicator();
				}
			}
		);
}

/**
 * Add event listener to the service action dropdown
 */
document.addEventListener('DOMContentLoaded', function() {
    var dropdown = document.getElementById('service-action-dropdown');

    if (dropdown) {
        dropdown.addEventListener('change', function() {
            var selectedAction = this.value;
            redirectBasedOnServiceAction(selectedAction);
        });
    }
});

/**
 * Redirect based on the selected service action
 *
 * @param {*} selectedAction Selected service action
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

/**
 * Function to update or add a parameter to a URL
 *
 * @param {string} uri   The URL
 * @param {string} key   The parameter key
 * @param {string} value The parameter value
 * @returns {string}     The updated URL
 */
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
jQuery( document ).ready(
	function ($) {
		// Event listener for the delete button
		$( document ).on(
			'click',
			'.delete-invoice-button',
			function () {
				// Get the invoice ID from the data attribute
				var invoiceId = $( this ).data( 'invoice-id' );

				// Display a confirmation dialog
				var isConfirmed = confirm( 'Are you sure you want to delete this invoice?' );

				// If the user confirms, initiate the deletion process
				if (isConfirmed) {
					// Perform an Ajax request to delete the invoice
					$.ajax(
						{
							type: 'POST',
							url: smart_woo_vars.ajax_url,
							data: {
								action: 'delete_invoice',
								invoice_id: invoiceId,
								security: smart_woo_vars.security
							},
							success: function () {
								// Display a success message
								alert( 'Invoice deleted successfully!' );
								window.location.href = smart_woo_vars.admin_invoice_page;
							},

							error: function (error) {
								// Handle the error
								console.error( 'Error deleting invoice:', error );
							}
						}
					);
				}
			}
		);
	}
);


// Function to delete service
jQuery( document ).ready(
	function ($) {
		// Event listener for the delete button
		$( document ).on(
			'click',
			'.delete-service-button',
			function () {
				// Get the service ID from the data attribute
				var serviceId = $( this ).data( 'service-id' );

				// Display a confirmation dialog
				var isConfirmed = confirm( 'Are you sure you want to delete this service?' );

				// If the user confirms, initiate the deletion process
				if (isConfirmed) {
					// Perform an Ajax request to delete the invoice
					$.ajax(
						{
							type: 'POST',
							url: smart_woo_vars.ajax_url,
							data: {
								action: 'smartwoo_delete_service',
								service_id: serviceId,
								security: smart_woo_vars.security
							},
							success: function () {
								// Display a success message
								alert( 'Service deleted successfully!' );
								window.location.href = smart_woo_vars.sw_admin_page;
							},

							error: function (error) {
								// Handle the error
								console.error( 'Error deleting service:', error );
							}
						}
					);
				}
			}
		);
	}
);


// Add click event listener to toggle the accordion
document.addEventListener('DOMContentLoaded', function() {
    var acc = document.querySelectorAll('.sw-accordion-btn');
    for (var i = 0; i < acc.length; i++) {
        acc[i].addEventListener('click', function() {
            this.classList.toggle('active');
            var panel = this.nextElementSibling;
            if (panel.style.display === 'block') {
                panel.style.display = 'none';
            } else {
                panel.style.display = 'block';
            }
        });
    }
});


document.addEventListener('DOMContentLoaded', function() {
    var generateServiceIdBtn = document.getElementById('generate-service-id-btn');
    var loader = document.getElementById('swloader');
		if ( generateServiceIdBtn ){
		generateServiceIdBtn.addEventListener('click', function(event) {
			event.preventDefault();

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
	}
});