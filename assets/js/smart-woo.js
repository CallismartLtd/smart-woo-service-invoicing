let spinnerIsactive = false;
function smartWooAddSpinner(targetId, large = false) {
	if ( spinnerIsactive ) return;
	let spinnerImage		= large ? smart_woo_vars.wp_spinner_gif_2x : smart_woo_vars.wp_spinner_gif;
	const loadingSpinner = document.createElement('div');
	loadingSpinner.setAttribute( 'style', 'position: absolute; left: 50%; top: 50%; transform: translate(-50%)' );
	loadingSpinner.innerHTML = '<img src=" ' + spinnerImage +'" alt="Loading...">';
  
	const targetElement = document.getElementById(targetId);

	targetElement.appendChild(loadingSpinner);
	document.body.style.setProperty( 'cursor', 'progress' );
	targetElement.style.display = 'block';
	spinnerIsactive = true;
	return loadingSpinner; // Return the created element for potential removal
}
  
function smartWooRemoveSpinner(spinnerElement) {
	document.body.style.removeProperty( 'cursor' );
	spinnerElement?.remove();
	spinnerIsactive = false;
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
    notification.style.zIndex = '9999999';
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
					let response = await fetch( url, { credentials: 'same-origin' } );
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
    jQuery('body').css('cursor', 'progress');
}

/**
 * Hide the loading indicator by hiding the #swloader element.
 * It resets the cursor to its default state for the body.
 */
function hideLoadingIndicator() {
    jQuery('#swloader').css('display', 'none');
    jQuery('body').css('cursor', ''); // Reset cursor to default for body
}

/**
 * Client settings component cache
 */
const smartwooClientComponentCache = new Map();
/**
 * Fetch an account settings component from the server.
 *
 * @param {String} name - The name of the settings component
 * @return {Promise<String>} The HTML component from the server, or an error HTML string.
 */
async function fetchAccountComponent( name ) {
	// if ( smartwooClientComponentCache.has( name ) ) {
	// 	return smartwooClientComponentCache.get( name );
	// }

	// Match name to action query var
    let components = {
        billingInfo: 'get_billing_details',
		userInfo: 'get_client_details',
		accountLogs: 'get_account_logs',
		orderHistory: 'smartwoo_get_order_history',
		paymentInfo: 'get_payment_details',
		editBilling: 'get_edit_billing_form',
		editPrimaryPayment: 'get_edit_primary_payment_form',
		editBackupPayment: 'get_edit_backup_payment_form',
		editMyInfo: 'get_edit_client_form',
		editPaymentInfo: 'get_edit_payment_form',

    };

    if ( ! components[name] ) {
        return `<div class="sw-error-notice"><p>Error: Invalid component name provided.</p></div>`;
    }

    let spinner = smartWooAddSpinner( 'new-smartwoo-loader', true );

    let url = new URL( smart_woo_vars.ajax_url );
    url.searchParams.set( 'action', components[name] );
    url.searchParams.set( 'security', smart_woo_vars.security );

    try {
        let response = await fetch( url, { credentials: 'same-origin' });
        if ( ! response.ok ) {
            let errorMessage = `Something went wrong: [${response.status}] - ${response.statusText}`;
            let errorDetails = '';

            const contentType = response.headers.get( 'content-type' );
            if ( contentType && contentType.includes( 'application/json' ) ) {
                try {
                    const errorJson = await response.json();
                    errorDetails = JSON.stringify( errorJson );
                } catch (parseError) {
                    errorDetails = await response.text();
                }
            } else {
                errorDetails = await response.text();
            }

            let displayMessage = errorMessage;
            if (errorDetails) {
                displayMessage += `. Details: ${errorDetails.length < 200 ? errorDetails : 'See console for details.'}`;
            }

            throw new Error(displayMessage);
        }

		const result = await response.text();
		// smartwooClientComponentCache.set( name, result );
		// setTimeout( () => smartwooClientComponentCache.delete( name ), 300000 );
        return result;

    } catch (error) {
        let userMessage = 'An unexpected error occurred.';

        if (error instanceof TypeError) {
            userMessage = 'Network error: Please check your internet connection or try again later.';
        } else if (error instanceof Error) {
            userMessage = error.message;
        } else {
            userMessage = `An unknown error occurred: ${String(error)}`;
        }

        return `<div class="sw-error-notice"><p>${userMessage}</p></div>`;

    } finally {
        smartWooRemoveSpinner( spinner );
    }
}

/**
 * Post client settings form
 * 
 * @param {HTMLFormElement} form - The settings form
 */
async function smartwooSubmitSettingsForm( form ) {
	if ( ! form ) return console.warn( 'Invalid form element' );
	
	let payload = new FormData( form );
	payload.set( 'security', smart_woo_vars.security );
    
	let spinner = smartWooAddSpinner( 'new-smartwoo-loader', true );

    try {
        let response = await fetch( smart_woo_vars.ajax_url, {method: 'POST', body: payload, credentials: 'same-origin'} );
        if ( ! response.ok ) {
            let errorMessage = `Something went wrong: [${response.status}] - ${response.statusText}`;
            let errorDetails = '';

            const contentType = response.headers.get( 'content-type' );
            if ( contentType && contentType.includes( 'application/json' ) ) {
                try {
                    const errorJson = await response.json();
                    errorDetails = JSON.stringify( errorJson );
                } catch (parseError) {
                    errorDetails = await response.text();
                }
            } else {
                errorDetails = await response.text();
            }

            let displayMessage = errorMessage;
            if (errorDetails) {
                displayMessage += `. Details: ${errorDetails.length < 200 ? errorDetails : 'See console for details.'}`;
            }

            throw new Error(displayMessage);
        }

        return await response.text();

    } catch (error) {
        let userMessage = 'An unexpected error occurred.';

        if (error instanceof TypeError) {
            userMessage = 'Network error: Please check your internet connection or try again later.';
        } else if (error instanceof Error) {
            userMessage = error.message;
        } else {
            userMessage = `An unknown error occurred: ${String(error)}`;
        }

        return `<div class="sw-error-notice"><p>${userMessage}</p></div>`;

    } finally {
        smartWooRemoveSpinner( spinner );
		smartwooClientComponentCache.clear(); // Changes, invalidate the cache.
    }
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
	let theSpin = smartWooAddSpinner('spinnerDiv' );

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

document.addEventListener( 'DOMContentLoaded', function() {
    let hamburger			= document.querySelector('.sw-menu-icon');
    let navbar				= document.querySelector('.service-navbar');
	let logoutBtn			= document.querySelector( '.smart-woo-logout' );
	let loginPWDVisible		= document.getElementById('smartwoo-login-form-visible');
	let loginPWDHidden		= document.getElementById('smartwoo-login-form-invisible');
	let loginPWDInput		= document.getElementById('sw-user-password');
	let adminAssetsToggle	= document.querySelectorAll( '.sw-admin-service-assets-button, .sw-client-service-assets-button' );
	let renewalButton		= document.querySelector( '.smartwoo-service-renew-button' );
	let allSortDivs			= document.querySelectorAll( '.sw-user-status-item' );
	let miniCardContent		= document.querySelector( '.mini-card-content' );
	let accountSettings		= document.querySelector( '#smartwooSettingsContainer' );
	let assetSubBtn = document.getElementById( 'smartwoo-assets-sub-nav' );

    if (hamburger) {
		let menuIcon	= hamburger.querySelector('.dashicons-menu');
		let innerIcon	= document.querySelector( '.dashicons.dashicons-no-alt.sw-close-icon' );
        
		hamburger.addEventListener('click', function() {
            navbar.classList.toggle('active');
            if (navbar.classList.contains('active')) {
				jQuery( innerIcon ).show();
				innerIcon.focus();
                menuIcon.classList.remove('dashicons-menu');
                menuIcon.classList.add('dashicons-no');
            } else {
                menuIcon.classList.remove('dashicons-no');
                menuIcon.classList.add('dashicons-menu');
				jQuery( innerIcon ).hide();
            }
        });

		innerIcon.addEventListener( 'click', ( e ) =>{
			e.preventDefault();
			hamburger.click();
		} );
    }
	    
	if ( allSortDivs.length ) {
        allSortDivs.forEach( sortDiv =>{
            sortDiv.style.cursor = 'pointer';
            let url = sortDiv.querySelector( 'a' ).getAttribute( 'href' );
            sortDiv.addEventListener( 'click', ()=>{
                window.location.href = url;
            });            
        });
    }

	if ( logoutBtn ) {
		let clicked = false;
		logoutBtn.addEventListener('click', function(){
			if (clicked) {
				jQuery('.smartwoo-logout-frame').fadeOut( 'slow', () =>{
					document.querySelector('.smartwoo-logout-frame').remove();
				});
				
			} else {
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
				navbar.insertAdjacentElement('afterend', actionModalframe );
				jQuery(actionModalframe).fadeIn().css('display', 'block')

				noBtn.addEventListener('click', ()=>{
					jQuery( actionModalframe ).fadeOut( 'slow', () =>{
						actionModalframe.remove();
					} );
					
					clicked = !clicked;
				});

				yesBtn.addEventListener('click', smartwoo_ajax_logout );

				actionModalframe.addEventListener( 'click', ( e ) => {
					if ( e.target === actionModalframe ) {
						noBtn.click();
					}
				});
			}

			clicked = !clicked;
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

	if ( renewalButton ) {
		renewalButton.addEventListener( 'click', (e) => {
			e.preventDefault();
			jQuery( '#swloader' ).show().css( 'background-color', 'rgba(129, 128, 128, 0)' ).text( '' );
			let loader = smartWooAddSpinner( 'swloader', true );
			let service_id = renewalButton.getAttribute( 'data-service-id' );
			let url			= new URL( smart_woo_vars.ajax_url );
			url.searchParams.set( 'action', 'smartwoo_manual_renew' );
			url.searchParams.set( 'service_id', service_id );
			url.searchParams.set( 'security', smart_woo_vars.security );
			
			fetch( url, { credentials: 'same-origin' })
			.then( ( response ) =>{
				if ( ! response.ok ) {
					console.error( response.statusText );
					showNotification( 'Something went wrong...', 6000 );
				}
				return response.json();
			}).then( response =>{
				if ( response.success ) {
					showNotification( response.data.message, 3000 );
					setTimeout( () =>{
						window.location.href = response.data.redirect_url;
					}, 3000 );
				} else {
					showNotification( response.data.message ?? 'Operation was not successful', 3000 );
					setTimeout( () =>{
						window.location.reload();
					}, 3000);
				}
			}).catch( (error ) => {
				console.error(error.message);
				
			}).finally( () =>{
				smartWooRemoveSpinner( loader );
				jQuery( '#swloader' ).hide();
			});
		});
	}

	if ( miniCardContent ) {
		let paginationContainer	= document.querySelector( '.sw-mini-card-pagination' );
		let buttons = paginationContainer.querySelectorAll( 'button' );
		let prevBtn	= buttons[0];
		let nextBtn = buttons[1];
		let context = 'any';
		let page;
		let limit	= miniCardContent.getAttribute( 'limit' );


		if ( smart_woo_vars.is_account_page ) {
			context = 'myaccount';
		}

		let getSubs = ( page, limit ) => {
			page = Number( page );
			miniCardContent.innerHTML = `
				<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text"></span></li>
				<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text"></span></li>
				<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text"></span></li>
				<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text"></span></li>
				<li class="smartwoo-skeleton"><span class="smartwoo-skeleton-text"></span></li>
			`;
			let url = new URL( smart_woo_vars.ajax_url );
			url.searchParams.set( 'action', 'get_subscriptions' );
			url.searchParams.set( 'limit', limit );
			url.searchParams.set( 'security', smart_woo_vars.security );
			url.searchParams.set( 'context', context );
			url.searchParams.set( 'page', page );
			fetch( url, { credentials: 'same-origin' })
			.then( response =>{
				if ( ! response.ok ) {
					throw new Error( `Error unable to fetch service subscriptions [${response.statusText}]`)
				}

				return response.json();
			}).then( responseJSON => {
				let subscriptions	= responseJSON.data.subscriptions ?? [];
				let pagination		= responseJSON.data.pagination
				miniCardContent.innerHTML = '';
				if ( ! subscriptions.length ) {
					miniCardContent.innerHTML = `<li>${responseJSON.data.message}</li>`;
				} else {
					subscriptions.forEach( sub => {
						let li = document.createElement( 'li' );
						li.innerHTML = `<a href="${sub.view_url}" data-status="(${sub.status})">${sub.name}</a>`;

						miniCardContent.appendChild( li );
					})
				}

				if ( pagination.total_items >= 1 ) {
					paginationContainer.classList.add( 'has-more' );
					document.querySelector( '#sw-card-counter' )
					.textContent = `${pagination.total_items} items ${page} of ${pagination.total_pages}`;

					if ( page > 1 ) {
						let nextNumber = page - 1;
						prevBtn.setAttribute( 'page', nextNumber );
						jQuery( prevBtn ).show();
					} else {
						jQuery( prevBtn ).hide();
					}


					if ( page < pagination.total_pages ) {
						
						let nextNumber = page + 1;
						nextBtn.setAttribute( 'page', nextNumber );
						jQuery( nextBtn ).show();
					} else {
						jQuery( nextBtn ).hide();
					}
					
				}				
			})
		}

		nextBtn.addEventListener( 'click', () => {
			let page = nextBtn.getAttribute( 'page' );
			getSubs( page, limit);
		});

		prevBtn.addEventListener( 'click', () => {
			let page = prevBtn.getAttribute( 'page' );
			getSubs( page, limit);
		});

		getSubs( 1, limit);
	}

	if ( accountSettings ) {
		let currentAction		= null;
		let responseContainer	= document.querySelector( '#ajax-content-container' );
		accountSettings.addEventListener( 'click', async ( e ) => {
			if ( e.target.closest( '.clear-radio' ) ) {
				e.target.closest( 'form' ).querySelectorAll( 'input[type="radio"]' ).forEach( radio => radio.checked = false );
				return;
			}
			const clickedBtn	= e.target.closest( '#sw-billing-details, #sw-load-user-details, #sw-account-log, #sw-load-order-history, #edit-billing-address, #edit-account-button, #view-payment-button, .smartwoo-inline-edit' );			
			if ( ! clickedBtn ) return;

			const action		= clickedBtn.getAttribute( 'data-action' );
			if ( ! action ) return console.warn( 'button action not found' );
			if ( action === currentAction ) return;
			currentAction = action;
			
			let response = await fetchAccountComponent( action );
			jQuery( responseContainer ).fadeOut( 'slow', () => {
				jQuery( responseContainer ).html( response );
				
				jQuery( responseContainer ).fadeIn( 'slow', () => {
					if ( 'editBilling' === action ) {
						jQuery( document.body ).trigger( 'wc_country_select_init' );
						jQuery( document.body ).trigger( 'wc-enhanced-select-init' );
						jQuery( document.body ).trigger( 'country_to_state_changed' );
					}					
				});
			});
			
		});

		accountSettings.addEventListener( 'submit', async (e) => {
			currentAction = null;
			e.preventDefault();
			e.target.querySelector( 'button[type="submit"]')?.setAttribute( 'disabled', true );
			let response = await smartwooSubmitSettingsForm( e.target );
			e.target.querySelector( 'button[type="submit"]' )?.removeAttribute( 'disabled' );
			jQuery( responseContainer ).fadeOut( 'slow', () => {
				jQuery( responseContainer ).html( response );
				jQuery( responseContainer ).fadeIn();
			});
		})
	}

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
});

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
