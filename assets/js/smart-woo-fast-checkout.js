document.addEventListener( 'DOMContentLoaded', smartwooFastCheckout );
/**
 * Perform fast checkout without clicking around the configure product page
 */
function smartwooFastCheckout() {
    let configBtns = document.querySelectorAll( '.product_type_sw_product.add_to_cart_button' );
    if ( configBtns.length ) {
        configBtns.forEach( configBtn =>{
            configBtn.addEventListener( 'click', smartwooOpenModal );
        });
    }
}

/**
 * Open then config modal
 * 
 * @param {Event} event The event listener obj
 */
function smartwooOpenModal( event ) {
    let productLi       = event.target.closest( 'li' );
    let fsConfig        = smart_woo_vars.fast_checkout_config;

    if ( ! productLi ) {
        productLi   = event.target.closest( '.configure-product-button' );
    }

    let configHolder    = productLi.querySelector( '.smartwoo-product-data' );

    if ( ! configHolder ) {
        console.warn( "Product config not found. Fast checkout prevented." );
        return;
    }
    event.preventDefault();

    let configData      = configHolder.getAttribute( 'smartwoo-product-config' );
    let product         = JSON.parse( configData );
    let productID       = product.id;

    let prevDiv = document.querySelector( '.sw-fast-checkout-main' );
    
    let modalRemove = ()=>{
        modal = document.querySelector( '.smartwoo-modal-frame' );
        jQuery( modal ).fadeOut( 'fast', () =>{
            modal.remove();
        });
    }

    if ( prevDiv ) {
        modalRemove();
    }

    let modalFrame  = document.createElement( 'div' );
    let modalDiv    = document.createElement( 'div' );
    let closeBtn    = document.createElement( 'span' );
    let hTag        = document.createElement( 'h3' );
    let inputDiv    = document.createElement( 'div' );

    modalFrame.classList.add( 'smartwoo-modal-frame' );
    modalDiv.setAttribute( 'style', `background-color: ${fsConfig.modal_background_color};` );
    modalFrame.appendChild( modalDiv );

    let titleText = fsConfig.title.replace('{{product_name}}', `"${product.name}"` || '');
    hTag.textContent = titleText;
    hTag.setAttribute( 'style', `padding: 5px; color: ${fsConfig.title_color}`)
    hTag.style.padding = '5px';
    let fields       = `
        <div id="response"></div>
        <div class="smartwoo-fast-checkout-description">
            <img src="${product.image_url}" alt="${product.name}">
            <span>${fsConfig.description}</span>
        </div>
        <input type="text" name="service_name" id="service_name" placeholder="${fsConfig.service_name_placeholder}"/>
        <input type="text" name="service_url" id="service_url" placeholder="${fsConfig.url_placeholder}"/>
        <button class="sw-blue-button" style="background-color: ${fsConfig.button_background_color}; color: ${fsConfig.button_text_color};">${fsConfig.checkout_button_text}</button>
        <div id="loader" style="background-color:rgba(255, 255, 255, 0); text-align: center; display: none;"></div>

    `;
    closeBtn.setAttribute( 'style', 'float: right; color:rgb(248, 100, 100); margin-right: 4px; cursor: pointer; font-size: 24px');
    closeBtn.setAttribute( 'class', 'dashicons dashicons-dismiss' );
    // closeBtn.innerHTML = '&times;';
    modalDiv.classList.add( 'sw-fast-checkout-main' );
    
    inputDiv.classList.add( 'sw-flex-column' );
    inputDiv.style.position = 'relative';
    inputDiv.innerHTML = fields;
    hTag.appendChild(closeBtn);
    modalDiv.appendChild(hTag);
    modalDiv.appendChild(inputDiv);
    document.body.prepend( modalFrame );
    jQuery( modalFrame ).fadeIn( 'slow' );

    closeBtn.addEventListener( 'click', () =>{
        modalRemove();        
    });
    
    modalFrame.addEventListener( 'click', (e) =>{
        if ( e.target === modalFrame ) {
            modalRemove();
        }
    });

    let removeModal = ( e ) => {
        if ( e.key === 'Escape' || e.key === 'Esc') {
            modalRemove();
            document.removeEventListener( 'keydown', removeModal );
        }
    }

    document.addEventListener( 'keydown', removeModal );

    let responseDiv = modalDiv.querySelector( '#response' );
    responseDiv.style.border = "solid 1px #dcdcde";
    responseDiv.style.padding = "5px";
    responseDiv.style.minHeight = "40px";
    responseDiv.style.display = "none";

    let checkoutBtn = modalDiv.querySelector( 'button' );
    let spinner;
    checkoutBtn.addEventListener( 'click', e =>{
        jQuery( responseDiv ).fadeOut();
        checkoutBtn.setAttribute( 'disabled', true );
        let serviceName = modalDiv.querySelector( '#service_name' );
        let serviceURL  = modalDiv.querySelector( '#service_url' );

        if ( ! ( serviceName.value.trim() ).length ) {
            responseDiv.innerHTML = "Please enter a service name";
            jQuery( responseDiv ).fadeIn();
            checkoutBtn.removeAttribute( 'disabled' );
            return;
        }

        let animateBg = ( element ) => {
            let colors = [' #ffffff', ' #e2def5','rgb(129, 122, 122)', ' #b3afaf', 'rgb(61, 196, 250)'];
            return setInterval(() => {
                element.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            }, 200);
        };

        if ( ! spinner ) {
            spinner     = smartWooAddSpinner( 'loader', true );  
        }

        jQuery( spinner ).show();
        
        let animation   = animateBg(modalDiv);
        spinner.style.position = 'absolute';
        spinner.style.top = '20%';
        spinner.style.left = '40%';
        let formData    = new FormData();
        
        formData.append( 'security', smart_woo_vars.security );
        formData.append( 'product_id', productID );
        formData.append( 'service_name', serviceName.value );
        formData.append( 'service_url', serviceURL.value );
        formData.append( 'action', 'smartwoo_configure_product' );
        
        fetch( smart_woo_vars.ajax_url, {
            method: 'POST',
            body: formData

        }).then( response =>{
            if ( ! response.ok ) {
                responseDiv.innerHTML = 'Request cannot be completed';
                responseDiv.style.border = "solid 1px #dcdcde";

                throw new Error( response.statusText , 6000);
            }

            return response.json();
        }).then( responseData =>{
            if ( responseData.success ){
                responseDiv.style.border = "solid 1px #dcdcde";
                responseDiv.innerHTML = responseData.data.message ? responseData.data.message : 'Product configured, redirecting to checkout.';
                window.location.href = responseData.data.checkout;
                jQuery( responseDiv ).show();
            } else {
                responseDiv.innerHTML = responseData.data.message ? responseData.data.message : 'Something went wrong.';
                jQuery( responseDiv ).show();
            }
        }).catch( error =>{
            console.error( error );
            jQuery( responseDiv ).show();
        }).finally( ()=>{
            jQuery( spinner ).hide();
            checkoutBtn.removeAttribute( 'disabled' );
            clearInterval( animation );
        });
    });
}