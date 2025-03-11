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
    let productID   = event.target.getAttribute( 'data-product_id' );

    // If no product ID, search within the closest product container
    if ( ! productID ) {
        let productContainer = event.target.closest('[data-product_id]');
        if ( productContainer ) {
            productID = productContainer.dataset.product_id;
        }
    }

    // If product ID is still missing, exit
    if ( ! productID ) {
        console.warn("Product ID not found. Fast checkout prevented.");
        return;
    }

    event.preventDefault();
    let modalRemove = ( element )=>{
        element.classList.remove( 'active' );
        setTimeout(() => {
            element.remove();
        }, 1000);
    }

    let prevDiv = document.querySelector( '.sw-fast-checkout-main' );

    if ( prevDiv ) {
        modalRemove( prevDiv );
    }

    let modalDiv    = document.createElement( 'div' );
    let closeBtn    = document.createElement( 'span' );
    let hTag    = document.createElement( 'h3' );
    let inputDiv    = document.createElement( 'div' );

    hTag.textContent = `Configure ${event.target.getAttribute( 'data-product_name' ) ? `"${event.target.getAttribute( 'data-product_name' )}"` : ''}`;
    hTag.style.padding = '5px';
    let fields       = `
        <div id="response"></div>
        <input type="text" name="service_name" id="service_name" placeholder="Service Name (required)"/>
        <input type="text" name="service_url" id="service_url" placeholder="URL (optional)"/>
        <button class="sw-blue-button">Checkout</button>
        <div id="loader" style="background-color:rgba(255, 255, 255, 0);"></div>

    `;
    closeBtn.setAttribute( 'style', 'height: 20px; float: right; text-align: right; color: red; margin-right: 10px; cursor: pointer; font-weight: 900;');
    closeBtn.textContent = 'x';
    modalDiv.classList.add( 'sw-fast-checkout-main' );
    
    inputDiv.classList.add( 'sw-flex-column' );
    inputDiv.style.position = 'relative';
    inputDiv.innerHTML = fields;
    modalDiv.appendChild(closeBtn);
    modalDiv.appendChild(hTag);
    modalDiv.appendChild(inputDiv);
    document.body.prepend( modalDiv );
    setTimeout( () => {
        modalDiv.classList.add( 'active' );

    }, 50);
    closeBtn.addEventListener( 'click', () =>{
        modalRemove( modalDiv );
        
    });

    checkoutBtn = modalDiv.querySelector( 'button' );
    checkoutBtn.addEventListener( 'click', e =>{
        let serviceName = modalDiv.querySelector( '#service_name' );
        let serviceURL  = modalDiv.querySelector( '#service_url' );
        let responseDiv = modalDiv.querySelector( '#response' );
        responseDiv.style.border = "solid 1px #dcdcde";
        responseDiv.style.padding = "5px";
        responseDiv.style.minHeight = "40px";

        let clearForm = () =>{
            responseDiv.innerHTML = '';
            responseDiv.style.border = "none";
        }

        if ( ! ( serviceName.value.trim() ).length ) {
            responseDiv.innerHTML = "Please enter a service name";
            return;
        }
        clearForm();
        let animateBg = ( element ) => {
            let colors = [' #ffffff', '#e2def5', '#b3afaf', '#e0f6ff'];
            return setInterval(() => {
                element.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            }, 200);
        };
        
        let spinner     = smartWooAddSpinner( 'loader', true );
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
            } else {
                responseDiv.style.border = "solid 1px #dcdcde";
                responseDiv.innerHTML = responseData.data.message ? responseData.data.message : 'Something went wrong.';
                
            }
        }).catch( error =>{
            console.error( error );
        }).finally( ()=>{
            smartWooRemoveSpinner( spinner );
            clearInterval( animation );
        });
    });


}