function smartWooAddSpinner(targetId) {
	const loadingSpinner = document.createElement('div');
	loadingSpinner.classList.add('loading-spinner');
	loadingSpinner.innerHTML = '<img src=" ' + smart_woo_vars.wp_spinner_gif_loader +'" alt="Loading...">';
  
	const targetElement = document.getElementById(targetId);

	targetElement.appendChild(loadingSpinner);
  
	return loadingSpinner; // Return the created element for potential removal
}
  
function smartWooRemoveSpinner(spinnerElement) {
spinnerElement.remove();
}

function showNotification(message, duration) {
    // Create a div element for the notification
    const notification = document.createElement('div');
    notification.classList.add('notification');
    
    // Set the notification message
    notification.innerHTML = `
        <div class="notification-content">
            <span style="float:right; cursor:pointer; font-weight:bold;" class="close-btn" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <p>${message}</p>
        </div>
    `;
    
    // Apply styles to the notification
    notification.style.position = 'fixed';
    notification.style.top  = '40px';
    notification.style.left = '50%';
    notification.style.width = '30%';
    notification.style.fontWeight = 'bold';
    notification.style.transform = 'translateX(-50%)';
    notification.style.padding = '15px';
    notification.style.backgroundColor = '#fff'; // White background
    notification.style.color = '#333'; // Black text color
    notification.style.border = '1px solid #ccc';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.5)';
    notification.style.zIndex = '9999';
    notification.style.textAlign = 'center';
    
    // Append the notification to the body
    document.body.appendChild(notification);
    
    // Automatically remove the notification after a specified duration (in milliseconds)
    if (duration) {
        setTimeout(() => {
            notification.remove();
        }, duration);
    }
}

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


/**
 * Auto calculate subscription dates.
 */
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
 */
document.addEventListener( 'DOMContentLoaded', function() {
    let quickActionButton = document.getElementById( 'sw-service-quick-action' );
	let forgotPwdBtn = document.getElementById('sw-forgot-pwd-btn')

    if ( quickActionButton ) {
        quickActionButton.addEventListener( 'click', function() {
            let serviceName = quickActionButton.dataset.serviceName;
            let serviceId   = quickActionButton.dataset.serviceId;
            openCancelServiceDialog( serviceName, serviceId );
        });
    }

	if ( forgotPwdBtn ) {
		forgotPwdBtn.addEventListener('click', ()=>{

		});
	}
});

function openCancelServiceDialog( serviceName, serviceId ) {
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
			showLoadingIndicator();

			// AJAX request to post service cancellation
			jQuery.ajax({
				type: 'POST',
				url: smart_woo_vars.ajax_url,
				data: {
					action: 'smartwoo_cancel_or_optout',
					security: smart_woo_vars.security,
					service_id: serviceId,
					selected_action: selectedAction
				},
				success: function () {
					// Animate the text change
					jQuery('#sw-service-quick-action').fadeIn('fast', function() {
						jQuery(this).text('Done!').slideDown('fast');
					});
					location.reload();
				},
				complete: function () {
					hideLoadingIndicator();
				}
			});
			
        } else {
            // Show an error message
            alert( 'Oops! you mis-typed it. Please type "cancel service" or "cancel billing" as instructed.' );
        }
    }

    return false;
}

/**
 * Subscription product image selector.
 */
jQuery( document ).ready(
	function ($) {
		var mediaUploader;

		$( '#upload_sw_product_image' ).click(
			function (e) {
				e.preventDefault();

				if (mediaUploader) {
						mediaUploader.open();
						return;
				}

				mediaUploader = wp.media.frames.file_frame = wp.media(
					{
						title: 'Choose Product Image',
						button: {
							text: 'insert image'
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

/**
 * Downloadable product file selector.
 */


/**
 * Grace period field change checker
 */
jQuery( document ).ready(
	function ($) {
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
 * It sets the cursor to 'progress' for the body, affecting all its children.
 */
function showLoadingIndicator() {
    jQuery('#swloader').css('display', 'block');
    jQuery('body').css('cursor', 'progress'); // Apply progress cursor to body
}

/**
 * Hide the loading indicator by hiding the #swloader element.
 * It resets the cursor to its default state for the body.
 */
function hideLoadingIndicator() {
    jQuery('#swloader').css('display', 'none');
    jQuery('body').css('cursor', ''); // Reset cursor to default for body
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
	let detailsButton = document.getElementById( 'sw-load-user-details' );
	if ( detailsButton ) {
		detailsButton.addEventListener( 'click', function() {
			loadMyDetails();
		});
	}

	let accountLogButton = document.getElementById( 'sw-account-log' );

    if ( accountLogButton ) {
        accountLogButton.addEventListener( 'click', function() {
            loadAccountLogs();
        });
    }

	let trButton = document.getElementById('sw-load-transaction-history');

	if (trButton) {
		trButton.addEventListener('click', function () {
			loadTransactionHistory();
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

jQuery(document).ready(function($) {
    var generateServiceIdBtn = $('#generate-service-id-btn');
    var loader = $('#swloader');

    if (generateServiceIdBtn.length) {
        generateServiceIdBtn.on('click', function(event) {
            event.preventDefault();

            // Get the service name from the input
            var serviceName = $('#service-name').val();
            // Display the animated loader
            loader.css('display', 'inline-block');

            // Perform AJAX request to generate service ID
            $.ajax({
                url: smart_woo_vars.ajax_url,
                type: 'POST',
                dataType: 'text',
                data: {
                    action: 'smartwoo_service_id_ajax',
                    service_name: serviceName,
                    security: smart_woo_vars.security
                },
                success: function(response) {
                    loader.css('display', 'none');
                    $('#generated-service-id').val(response);
                },
                error: function(xhr, status, error) {
                    // Hide the loader on error
                    loader.css('display', 'none');
                    // Handle error
                    console.error(error);
                }
            });
        });
    }
});

/**
 * Configure Product client Ajax handler.
 */
document.addEventListener('DOMContentLoaded', function () {
    var configureProductForm = document.getElementById('smartwooConfigureProduct');
    var buttonText = document.querySelector('.sw-blue-button');

    if (configureProductForm && buttonText) {
        configureProductForm.addEventListener('submit', function (event) {
            event.preventDefault();
			var originalBtnText = buttonText.textContent;
            buttonText.textContent = 'Processing...';
			buttonText.disabled = true;
            var formData = new FormData(configureProductForm);
            formData.append('action', 'smartwoo_configure_product');
            formData.append('security', smart_woo_vars.security);

            // Send AJAX request
            jQuery.ajax({
                type: 'POST',
                url: smart_woo_vars.ajax_url,
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success && response.data) {
                        var checkoutUrl = response.data.checkout;
						buttonText.textContent = 'Product is configured, redirecting to checkout page....';
                        window.location.href = checkoutUrl;
                    } else {
						jQuery( '#error-container' ).html( response.data.message );
						buttonText.textContent = originalBtnText;
                    }
					
                },
                error: function (xhr, status, error) {
                    console.error(error);
					buttonText.textContent = originalBtnText;
                },
            });
        });
    }
});

function smartwoo_ajax_logout() {
	let actionModalframe = document.querySelector('.smartwoo-logout-contaner');
	let spinnerDiv		= document.createElement('div')
	spinnerDiv.id = 'spinnerDiv';
	actionModalframe.appendChild(spinnerDiv);
	let theSpin = smartWooAddSpinner('spinnerDiv');

	jQuery.ajax({
		type: 'GET',
		url: smart_woo_vars.ajax_url,
		data: {
			security: smart_woo_vars.security,
			action: 'smartwoo_ajax_logout'
		},
		complete: function() {
			theSpin.remove();
			window.location.reload();
		}
	});
}

document.addEventListener('DOMContentLoaded', function() {
    let hamburger 	= document.querySelector('.sw-menu-icon');
    let navbar 		= document.querySelector('.service-navbar');
	let logoutBtn	= document.querySelector( '.smart-woo-logout' );
	let loginPWDVisible = document.getElementById('smartwoo-login-form-visible');
	let loginPWDHidden 	= document.getElementById('smartwoo-login-form-invisible');
	let loginPWDInput	= document.getElementById('sw-user-password');

    if (hamburger) {
		var menuIcon = hamburger.querySelector('.dashicons-menu');
        hamburger.addEventListener('click', function() {
            navbar.classList.toggle('active');
            if (navbar.classList.contains('active')) {
                menuIcon.classList.remove('dashicons-menu');
                menuIcon.classList.add('dashicons-no');
            } else {
                menuIcon.classList.remove('dashicons-no');
                menuIcon.classList.add('dashicons-menu');
            }
        });
    }
	if (logoutBtn) {
		let clicked = false;
		logoutBtn.addEventListener('click', function(){
			let actionModalframe	= document.createElement('div');
			actionModalframe.classList.add('smartwoo-logout-frame');
			let actionModal = document.createElement('div');
			actionModal.classList.add('smartwoo-logout-contaner');
			actionModalframe.append(actionModal);

			let pTag	= document.createElement('p');
			pTag.textContent = "Are sure you want to logout?";

			actionModal.append(pTag);

			let btnDiv	= document.createElement('div');
			btnDiv.classList.add('smartwoo-logout-btn-container');

			let yesBtn = document.createElement('button');
			yesBtn.classList.add('sw-blue-button');
			yesBtn.innerHTML = `<span class="dashicons dashicons-yes"></span>`;
			let noBtn  = document.createElement('button');
			noBtn.classList.add('sw-red-button');
			noBtn.innerHTML = `<span class="dashicons dashicons-no-alt"></span>`;
			btnDiv.append(noBtn, yesBtn);
			actionModal.append(btnDiv);
			if (clicked) {
				jQuery('.smartwoo-logout-frame').fadeOut();
				setTimeout(()=>{
					document.querySelector('.smartwoo-logout-frame').remove();

				}, 200);
			} else {
				navbar.insertAdjacentElement('afterend',actionModalframe);
				jQuery(actionModalframe).fadeIn().css('display', 'block')
			}
			clicked = !clicked;

			noBtn.addEventListener('click', ()=>{
				jQuery('.smartwoo-logout-frame').fadeOut();
				setTimeout(()=>{
					document.querySelector('.smartwoo-logout-frame').remove();

				}, 200);
				clicked = !clicked;
			});

			yesBtn.addEventListener('click', smartwoo_ajax_logout );
			

		});
	}

	if (loginPWDVisible && loginPWDHidden && loginPWDInput) {
		loginPWDVisible.addEventListener('click', ()=> {
			loginPWDVisible.style.display = "none";
			loginPWDHidden.style.display	= "block";
			loginPWDInput.setAttribute('type', 'text');
		});
		loginPWDHidden.addEventListener('click', ()=>{
			loginPWDHidden.style.display = "none";
			loginPWDVisible.style.display = "block";
			loginPWDInput.setAttribute('type', 'password');
		});
	}
});

jQuery(document).ready(function($) {
    // 1. Toggle display of download fields and Add Field button
    $('#is-smartwoo-downloadable').on('change', function() {
        if ($(this).is(':checked')) {
			$('.sw-assets-div').fadeIn().css('display', 'flex');;
			$('.sw-product-download-field-container').fadeIn();
            $('.sw-product-download-fields').fadeIn();
            $('#add-field').fadeIn();
        } else {
			$('.sw-assets-div').fadeOut();
			$('.sw-product-download-field-container').fadeOut();
            $('.sw-product-download-fields').fadeOut();
            $('#add-field').fadeOut();
        }
    });

    // 2. Open WordPress media library
    var mediaUploader;
    $(document).on('click', '.upload_image_button', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $fileUrlField = $button.siblings('.fileUrl');
        
        if (mediaUploader) {
            mediaUploader.open();
            mediaUploader.off('select');
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $fileUrlField.val(attachment.url);
            });
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Select a file',
            button: {
                text: 'Add to asset'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $fileUrlField.val(attachment.url);
        });

        mediaUploader.open();
    });

    // 3. Add new field set above the Add Field button
    $('#add-field').on('click', function() {
        var $fieldContainer = $('.sw-product-download-fields:first').clone();
        $fieldContainer.find('input').val('');
        $fieldContainer.find('.upload_image_button').val('Choose file');
        $fieldContainer.insertBefore('#add-field');
    });

    // 4. Remove field set
    $(document).on('click', '.swremove-field', function() {
        $(this).closest('.sw-product-download-fields').remove();
    });	
});

/**
 * Toggle visility of assets and subscription tabs in frontend
 */
document.addEventListener('DOMContentLoaded', function() {
	var assetSubBtn = document.getElementById( 'smartwoo-assets-sub-nav' );

	if ( assetSubBtn ) {
		var subContainer	= document.getElementById( 'smartwoo-sub-info' );
		var assetsContainer = document.getElementById( 'smartwoo-sub-assets' );
		var originalBtnText	= assetSubBtn.innerHTML;
		var isClicked		= false;
		
		assetSubBtn.addEventListener( 'click', function( event ) {
			if ( isClicked ) {
				assetSubBtn.innerHTML			= originalBtnText;
				jQuery(subContainer).fadeIn().css("display", "flex");
				assetsContainer.style.display 	= "none";
			} else {
				assetSubBtn.innerHTML			= `<span class="dashicons dashicons-info-outline"></span> Sub Info`;
				subContainer.style.display		= "none";
				jQuery(assetsContainer).fadeIn().css("display", "block");

			}
			isClicked = !isClicked;
		
		} );
	}
} );

/**
 * Database AJAX update handler
 */
addEventListener( 'DOMContentLoaded', function() {
	var updateBtn = document.getElementById( 'smartwooUpdateBtn' );
	if ( updateBtn ) {
		updateBtn.addEventListener( 'click', function() {
			var noticeDiv = document.getElementById( 'smartwooNoticeDiv' );
			var newDiv = document.createElement( 'div' );
			var pTag = document.createElement( 'p' );
			newDiv.className = 'notice notice-success is-dismissible';
			updateBtn.textContent = '';
			var spinner = smartWooAddSpinner( 'smartwooUpdateBtn' );
			jQuery.ajax({
				type: 'GET',
				url: smart_woo_vars.ajax_url,
				data: {
					action: 'smartwoo_db_update',
					security: smart_woo_vars.security
				},
				success: function( response ) {
					pTag.textContent = response.success ? response.data.message : 'Background update started';
					newDiv.appendChild( pTag );
					noticeDiv.replaceWith( newDiv );
					
				},
				error: function ( error ) {
					var message  = 'Error updating the database: ';
					// Handle the error
					if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
						message = message + error.responseJSON.data.message;
					} else if (error.responseText) {
						message = message + error.responseText;
					} else {
						message = message + error;
					}

					console.error( message );
				},
				complete: function() {
					smartWooRemoveSpinner( spinner );
				}
			});
		});
	}
});


document.addEventListener('DOMContentLoaded', function() {
    var moreAddiAssetsButton 	= document.getElementById('more-addi-assets');
    var mainContainer 			= document.getElementById('additionalAssets');
	var isExternal				= document.getElementById( 'isExternal' )
	
    if (moreAddiAssetsButton && mainContainer) {
        moreAddiAssetsButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent form submission or any default action of the button

            var newField = document.createElement('div');
            newField.classList.add('sw-additional-assets-field');

            newField.innerHTML = `
				<p><strong>Add More Assets</strong></p>
                <input type="text" name="add_asset_types[]" placeholder="Asset Type" />
                <input type="text" name="add_asset_names[]" placeholder="Asset Name" />
                <input type="text" name="add_asset_values[]" placeholder="Asset Value" />
				<input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="Limit (optional).">

                <button class="remove-field" title="Remove this field">&times;</button>
            `;

            mainContainer.insertBefore(newField, moreAddiAssetsButton);
        });

        // Event delegation to handle click events on the dynamically added remove buttons
        mainContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-field')) {
                event.preventDefault(); // Prevent default button action.
                var fieldToRemove = event.target.parentElement;
				var removedId = event.target.dataset.removedId;
				var confirmed = removedId ? confirm( 'This asset will be deleted from the database, click okay to continue.' ) : 0;
				var removeEle = removedId ? false : true;
				if ( removedId && confirmed ) {
					var spinner = smartWooAddSpinner( 'smartSpin' );
					console.log( removedId );
					jQuery.ajax({
						type: 'GET',
						url: smart_woo_vars.ajax_url,
						data: {
							action: 'smartwoo_asset_delete',
							security: smart_woo_vars.security,
							asset_id: removedId
						},
						success: function( response ) {
							if ( response.success ) {
								alert( response.data.message );
								fieldToRemove.remove(); // Remove the parent div of the clicked remove button.
							} else {
								alert( response.data.message );
							}
						},
						error: function ( error ) {
							var message  = 'Error deleting asset: ';
							// Handle the error
							if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
								message = message + error.responseJSON.data.message;
							} else if (error.responseText) {
								message = message + error.responseText;
							} else {
								message = message + error;
							}
		
							console.error( message );
						},
						complete: function() {
							smartWooRemoveSpinner( spinner );
							
						}
					});
				}
				if ( removeEle ) {
					fieldToRemove.remove();
				}
				
            }
        });
    }

	if ( isExternal ) {
		var inputField = document.getElementById( 'auth-token-div' );
		isExternal.addEventListener( 'change', function( e ) {
			if ( 'yes' === isExternal.value ) {
				inputField.classList.remove( 'smartwoo-hide' );
				inputField.classList.add( 'sw-form-row' );
			
			} else {
				inputField.classList.remove( 'sw-form-row' );
				inputField.classList.add( 'smartwoo-hide' );

			}
			
		} );

	}
});
