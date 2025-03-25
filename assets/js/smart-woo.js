function smartWooAddSpinner(targetId, large = false) {
	let spinnerImage		= large ? smart_woo_vars.wp_spinner_gif_2x : smart_woo_vars.wp_spinner_gif;
	const loadingSpinner = document.createElement('div');
	loadingSpinner.classList.add('loading-spinner');
	loadingSpinner.innerHTML = '<img src=" ' + spinnerImage +'" alt="Loading...">';
  
	const targetElement = document.getElementById(targetId);

	targetElement.appendChild(loadingSpinner);
	targetElement.parentElement.style.cursor = 'progress';
	targetElement.style.display = 'block';
  
	return loadingSpinner; // Return the created element for potential removal
}
  
function smartWooRemoveSpinner(spinnerElement) {
	spinnerElement.parentElement.parentElement.style.cursor = '';
	spinnerElement.remove();
}

function showNotification(message, duration = 1000) {
    // Create a div element for the notification
    const notification = document.createElement('div');
    notification.classList.add('notification');
    
    // Set the notification message
    notification.innerHTML = `
        <div class="notification-content">
            <span style="float:right; cursor:pointer; font-weight:bold; color: red;" class="dashicons dashicons-dismiss" onclick="this.parentElement.parentElement.remove()"></span>
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
		forgotPwdBtn.addEventListener( 'click', ()=>{
			let formTitle			= document.querySelector( '.sw-notice' ).querySelector( 'p' );
			let resetBtn			= document.querySelector( '#sw-login-btn' );
			formTitle.textContent	= 'Password Reset';
			resetBtn.textContent	= 'Reset Password';

			let usernameField	= document.querySelector( '#sw-user-login' );
			let pwdField		= document.querySelector( '#sw-user-password' );
			let rememberMeField = document.querySelector( '#remember_me' );
			usernameField ? usernameField.parentElement.remove() : '';
			pwdField ? pwdField.parentElement.remove() : '';
			rememberMeField ? rememberMeField.parentElement.remove() : '';

			let parentDiv = document.querySelector( '.smartwoo-login-form-notice' );
			let emailField		= document.createElement( 'div' );
			let theLabel		= document.createElement( 'label' );
			let textInput		= document.createElement( 'input' );
			let erroDiv			= this.documentElement.querySelector( '#sw-error-div' );

			emailField.classList.add( 'smartwoo-login-form-body' );
			theLabel.classList.add( 'smartwoo-login-form-label' );
			theLabel.setAttribute( 'for', 'sw-user-login' );
			theLabel.textContent = 'Email Address';
			textInput.setAttribute( 'type', 'text' );
			textInput.setAttribute( 'id', 'sw-user-login' );
			textInput.setAttribute( 'class', 'smartwoo-login-input' );
			textInput.setAttribute( 'name', 'user_login' );
			
			emailField.appendChild( theLabel );
			emailField.appendChild( textInput );
			parentDiv.append( emailField );
			erroDiv.innerHTML = `Enter your email address to request a password reset or you can <a id="sw-login-instead">Login</a> instead.`;
			loginInstead = document.getElementById( 'sw-login-instead' );
			if ( loginInstead ) {
				loginInstead.addEventListener( 'click', ()=>{ window.location.reload() });
			}

			resetBtn.addEventListener( 'click', async (e)=>{
				e.preventDefault();

				if ( ! textInput.value.length ){
					showNotification( 'Email should not be empty' );
					return;
				}
				let url = new URL( smart_woo_vars.ajax_url );
				url.searchParams.set( 'action', 'smartwoo_password_reset' );
				url.searchParams.set( 'user_login', textInput.value );
				url.searchParams.set( 'security', smart_woo_vars.security );
				formTitle.innerHTML = `Password Reset processed <span class="dashicons dashicons-yes-alt" style="color: green; font-size: 25px;"></span>`
				emailField.remove();
				erroDiv.innerHTML = "A password reset email will be sent to the account if it exists.";
				resetBtn.remove();
				try {
					let response = await fetch( url, {method: 'GET'} );
					if ( ! response.ok ) {
						throw new Error(`Response status: ${response.status}`);
					}
				} catch {}

			});
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
	let adminAssetsToggle       = document.querySelectorAll( '.sw-admin-service-assets-button, .sw-client-service-assets-button' );


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

	if ( adminAssetsToggle.length ) {
        let assetContents = document.querySelectorAll( '.sw-admin-assets-body-content, .sw-client-assets-body-content' );
        let removeAll = () =>{
            adminAssetsToggle.forEach(btn =>{
                btn.classList.remove( 'active' )
            });
            assetContents.forEach( div =>{
                div.classList.add( 'smartwoo-hide' );
            });
        }
        adminAssetsToggle.forEach( ( button, index ) =>{
            button.addEventListener( 'click', (e)=>{
                removeAll();
                if ( ! e.target.classList.contains( 'active' ) ) {
                    e.target.classList.add( 'active' );
                }
                assetContents[index].classList.toggle( 'smartwoo-hide' );
            });
        })
    }
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
