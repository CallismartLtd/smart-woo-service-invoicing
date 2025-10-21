/**
 * Callismart Tech Support Script
 *
 * @package SmartWoo
 * @subpackage JS
 * @since 1.0.0
 */
class CallismartSupport {
    checkBoxSelection   = new Set();
    /**
     * @param {HTMLElement} supportPage - The support inbox page
     */
    constructor( supportPage ) {
        this.pageBase       = (supportPage instanceof HTMLElement ) ? supportPage : document.querySelector( supportPage );
       
        this.serverConfig   = smartwoo_admin_vars;
        this._cacheElements();
        this._bindEvents();
    }

    /**
     * Cache HTML elements
     */
    _cacheElements() {
        this.inboxLeft      = this.pageBase?.querySelector( '.callismart-app-support-inbox_left' );
        this.inboxRight     = this.pageBase?.querySelector( '.callismart-app-support-inbox_right' );
        this.loader         = this.pageBase?.querySelector( '#loader' );
        this.messageBoxSvg  = this.inboxRight?.querySelector( '#noMessageSVG' );

        /**
         * Consent elements
         */
        this.grantConsentBtn    = document.querySelector( '#callismart-consent-btn' );
        this.withdrawConsentBtn = document.querySelector( '#callismart-withdraw-btn' );
        
        this.supportRadios   = document.querySelectorAll( 'input[name="smartwoo_support_choice"]' );
        this.supportTitle    = document.querySelector( '#smartwoo-support-title' );
        this.supportShort    = document.querySelector( '#smartwoo-support-short' );
        this.supportPrice    = document.querySelector( '#smartwoo-support-price' );
        this.checkoutBtn     = document.querySelector( '#smartwoo-support-checkout-btn' );
        this.modal           = document.querySelector( '.smartwoo-modal-frame' );
        this.modalContent    = this.modal?.querySelector( '.smartwoo-modal-body' );
        this.closeModalBtn   = this.modal?.querySelector( '.smartwoo-modal-close-btn' );

        this.activeCheckoutURL = null;
        this.iframe            = null;
        this.spinner           = null;
    }

    /**
     * Binds Event listeners
     */
    _bindEvents() {
        if ( this.inboxLeft ) {
            this.inboxLeft.addEventListener( 'click', this._handleInboxLeftClicks.bind(this) );
            this.inboxLeft.addEventListener( 'submit', this._submitForm.bind(this) );
            this.inboxLeft.addEventListener( 'change', this._handleInboxLeftChange.bind(this) );
        }

        if ( this.inboxRight ) {
            this.inboxRight.addEventListener( 'click', this._handleInboxRightClicks.bind(this) );
        }

        this.grantConsentBtn?.addEventListener( 'click', this.consentManagement.bind(this) );
        this.withdrawConsentBtn?.addEventListener( 'click', this.consentManagement.bind(this) );
        this.supportRadios?.forEach( radio => radio.addEventListener( 'change', this._handleProductSelection.bind(this) ) );
        this.checkoutBtn?.addEventListener( 'click', this._handleCheckoutAction.bind(this) );
        this.closeModalBtn?.addEventListener( 'click', this._closeModal.bind( this ) );
        this.modal?.addEventListener( 'click', e => e.target === this.modal && this._closeModal() );
        window?.addEventListener( 'message', e => this._handleCheckoutMessage( e ), false );
        document.querySelector( '#smartwoo-copy-json' )?.addEventListener( 'click', this._copyEnvToClipboard.bind( this ) )
    }

    /**
     * Handle support product selection
     * 
     * @param {Event} event - The event object.
     */
    _handleProductSelection( event ) {
        const item = event.target.closest( '.smartwoo-support-item' );
        if ( ! item ) return;

        const product = this.safeJsonParse( item.getAttribute( 'data-product' ) );
        if ( ! product ) return;

        this.supportTitle.innerHTML     = product.name || '';
        this.supportShort.innerHTML     = product.short_description || '';
        this.supportPrice.innerHTML     = product.price_html || '';
        this.checkoutBtn.dataset.url    = product.checkout_url || '';
    }
    
    /**
     * Handle support product checkout.
     * 
     * @param {Event} event - The event object.
     */
    _handleCheckoutAction() {
        const url = this.checkoutBtn.dataset.url;
        if ( url ) {
            this._openCheckoutModal( url );
            this.activeCheckoutURL = url;
        }        
    }

    /**
     * Open the checkout modal.
     * 
     * @param {URL} url - The checkout URL.
     * @returns void
     */
    _openCheckoutModal( url ) {
        let sourceURL;
        try {
            sourceURL = new URL( url );
            sourceURL.searchParams.set( 'LSCWP_CTRL', 'before_optm' );
            sourceURL.searchParams.set( 'utm', 'smartwoo_plugin' );
        } catch ( e ) {
            showNotification( e.message );
            return;
        }

        if ( this.iframe ) this.iframe.remove();

        const iframe  = document.createElement( 'iframe' );
        const loading = document.createElement( 'div' );
        this.spinner  = smartWooAddSpinner( loading );

        this.modalContent.innerHTML = '';

        jQuery( this.modal ).fadeOut( 'fast', () => {
            iframe.src             = sourceURL;
            iframe.width           = '100%';
            iframe.height          = '500';
            iframe.loading         = 'eager';
            iframe.referrerPolicy  = 'no-referrer-when-downgrade';
            iframe.allowFullscreen = true;
            iframe.className       = 'smartwoo-support-checkout';
            iframe.style.display   = 'none';

            this.modalContent.appendChild( loading );
            this.modalContent.appendChild( iframe );

            jQuery( this.modal ).fadeIn( 'slow' );
        });

        iframe.onload = () => {
            loading.remove();
            iframe.style.display = 'block';
            smartWooRemoveSpinner( this.spinner );
            iframe.contentWindow.postMessage({ action: 'callismart_support_init', appName: 'Smart Woo' }, sourceURL.origin );
        }

        this.iframe = iframe;
    }

    _closeModal() {
        jQuery( this.modal ).fadeOut( 'fast', () => {
            this.iframe?.remove();
            this.modalContent.innerHTML = '';
            this.iframe = null;
            smartWooRemoveSpinner( this.spinner );
        });
    }

    /**
     * 
     * @param {MessageEvent} event - Message event
     * @returns 
     */
    _handleCheckoutMessage( event ) {
        if ( ! event.data || event.data.action !== 'callismart_checkout_complete' ) return;
        if ( ! this.activeCheckoutURL ) return;

        const expectedOrigin = new URL( this.activeCheckoutURL ).origin;
        if ( event.origin !== expectedOrigin ) {
            console.warn( 'Unexpected event origin' );
            return;
        }

        const payload = new FormData;
        payload.set( 'action', 'smartwoo_verify_support_order' );
        payload.set( 'security', smartwoo_admin_vars.security );
        payload.set( 'order_id', event.data.order_id );
        payload.set( 'order_key', event.data.order_key );
        payload.set( 'token', event.data.token );

        fetch( smartwoo_admin_vars.ajax_url,
            {
                method: 'POST',
                body: payload,
                credentials: 'same-origin'
            }
        )
        .then( response => response.json() )
        .then( result => {
            let message = 'Order was not successful';
            if ( result.success && result.data.is_valid ) {
                message = 'Order was successful';
            }

            showNotification( `${message}, please check your support inbox for more information.`, 5000 )

        });
        
    }


    /**
     * Perform a HTTP POST request to the ajax support API endpoint.
     * 
     * @param {Object|FormData} params
     */
    async _fetch( params, showSpinner = true ) {
        const url = new URL( this.serverConfig.ajax_url );

        const formData = new FormData();

        if ( params instanceof FormData ) {
            for ( const [ key, value ] of params.entries() ) {
                formData.append( key, value );
            }
        } else if ( typeof params === 'object' && params !== null ) {
            Object.entries( params ).forEach( ( [ key, value ] ) => {
                if ( Array.isArray( value ) ) {
                    value.forEach( v => formData.append( `${key}[]`, v ) );
                } else if ( value !== undefined && value !== null ) {
                    formData.append( key, value );
                }
            });
        } else {
            throw new Error( 'Invalid params type. Must be Object or FormData.' );
        }

        formData.set( 'action', 'smartwoo_support_inbox_actions' );
        formData.set( 'security', this.serverConfig.security );

        showSpinner && this.showSpinner();

        try {
            const response = await fetch( url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            });

            const contentType = response.headers.get( 'content-type' ) || '';
            let result;

            if ( contentType.includes( 'application/json' ) ) {
                result = await response.json();
            } else {
                result = await response.text();
            }

            if ( ! response.ok ) {
                const errorMessage = contentType.includes( 'application/json' )
                    ? result?.data?.message || 'Unexpected error'
                    : result;
                throw new Error( errorMessage );
            }

            return result;

        } catch ( error ) {
            if ( error instanceof TypeError ) {
                showNotification( 'Please check your internet connection and try again later.', 5000 );
            } else {
                showNotification( error.message, 5000 );
                console.error( 'Unexpected error:', error );
            }
        } finally {
            this.hideSpinner();
        }
    }

    /**
     * Handle bulk selection form submission
     * 
     * @param {Event} event
     */
    async _submitForm( event ) {
        event.preventDefault();

        const form          = event.target.closest( '.callismart-app-support-inbox_left-header-form' );
        const bulkSelect    = form.querySelector( '#bulk-action-select' );

        if ( ! bulkSelect.value.trim() ) {
            showNotification( 'Please select a bulk action', 5000 );
            return;
        }

        if ( ! this.checkBoxSelection.size ) {
            showNotification( 'Please select at least one message!', 5000 );
            return;
        }

        const actionType = bulkSelect.value;

        const isDelete  = 'delete' === actionType;

        if ( isDelete && ! confirm( 'Are you sure you want to delete the selected message(s)?' ) ) {
            return;
        }
        
        const params = {
            action_type: actionType,
            message_ids: [ ...this.checkBoxSelection ]
        };

        this.inboxLeft.querySelector( '.masterCheckBox' ).classList.remove( 'active' );
        const result = await this._fetch( params );

        bulkSelect.value    = '';
        form.querySelector( 'button[type="submit"]' ).disabled = true;
        this.checkBoxSelection.clear();

        // Handle deletion
        if ( isDelete ) {
            this._reRenderMessageList( result.data.messages );
            const mobileCloseBtn = this.inboxRight.querySelector( '#mobile-close' );
            mobileCloseBtn && mobileCloseBtn.click();
            return;
        }

        // Determine which classes to add/remove
        const toAdd     = 'unread' === actionType ? 'unread' : 'read';
        const toRemove  = 'unread' === actionType ? 'read' : 'unread';

        params.message_ids.forEach( ( id ) => {
            const messageLi = this.inboxLeft.querySelector( `#message-id-${id}` );

            if ( messageLi ) {
                messageLi.classList.remove( toRemove );
                messageLi.classList.add( toAdd );
                const checkbox = messageLi.querySelector( 'input[type="checkbox"]' );
                if ( checkbox ) {
                    checkbox.checked = false;
                }
            }
        });
        
        showNotification( `Marked ${ params.message_ids.length } message(s) as ${ toAdd }`, 4000 );
    }


    /**
     * Handle change event on the left section of the inbox.
     * 
     * @param {Event} event
     */
    _handleInboxLeftChange( event ) {
        const checkbox    = event.target.closest( '.callismart-app-support-checkbox' );
        if ( checkbox ) {
            const masterCheckLi    = this.inboxLeft.querySelector( '.masterCheckBox' );
            let checkboxes = Array.from( this.inboxLeft.querySelectorAll( '.callismart-app-support-checkbox' ) );
            let allChecked = checkboxes.every( cb => cb.checked );
            let oneChecked = checkboxes.some( cb => cb.checked );
            
            const messageID = checkbox.id;

            if ( checkbox.checked ) {
                this.checkBoxSelection.add( messageID );
            } else {
                this.checkBoxSelection.delete( messageID );
            }            

            if ( oneChecked ) {
                masterCheckLi.classList.add( 'active' );
            } else {
                masterCheckLi.classList.remove( 'active' );
            }

            masterCheckLi.querySelector( 'button.selectAll' ).disabled      = allChecked;
            masterCheckLi.querySelector( 'button.unSelectAll' ).disabled    = ! oneChecked;
            
            return;
        }

        const bulkSelect    = event.target.closest( '#bulk-action-select' );
        const form          = event.target.closest( '.callismart-app-support-inbox_left-header-form' );

        if ( bulkSelect ) {            
            form.querySelector( 'button[type="submit"]' ).disabled = '' === bulkSelect.value ;
        }
    }

    /**
     * Handles click events on the left inbox section
     * 
     * @param {Event} event
     */
    async _handleInboxLeftClicks( event ) {
        const checkbox    = event.target.closest( '.callismart-app-support-checkbox' );
        if ( checkbox ) {
            return;
        }

        const masterCheckBox    = event.target.closest( '.masterCheckBox button.selectAll, .masterCheckBox button.unSelectAll' );

        if ( masterCheckBox ) {
            this.inboxLeft.querySelectorAll( '.callismart-app-support-checkbox' )
            .forEach( cb => {
                cb.checked = masterCheckBox.classList.contains( 'selectAll' );
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            });
            masterCheckBox.disabled = true;
            return;
        }

        const readMessage   = event.target.closest( '.callismart-app-support_message' );
        if ( readMessage ) {
            const messageJson   = this.safeJsonParse( readMessage.getAttribute( 'data-message-json' ) );

            if ( ! messageJson ) return;
            
            this._renderMessage( messageJson, readMessage );
            return;
        }

        const button        = event.target.closest( '.callismart-app-support-inbox-markAllRead, .callismart-app-support-inbox-refresh' );
        let requestParams   = null;
        
        if ( button ) {
            event.preventDefault();
            requestParams  = this.safeJsonParse( button.getAttribute( 'data-args' ) );
            button.setAttribute( 'disabled', true );
            button.classList.add( 'active' );
        }

        if ( requestParams ) {
            const result = await this._fetch( requestParams );

            if ( ! result ) return;

            showNotification( result.data.message, 10000 );

            button?.classList.remove( 'active' );
            button?.removeAttribute( 'disabled' );

            if ( 'all_read' === requestParams.action_type ) {
                this.inboxLeft.querySelectorAll( '.callismart-app-support_message:not(.empty-messages)' )
                .forEach( el => el.classList.add( 'read' ) ?? el.classList.remove( 'unread' ) );
            } else {
                this._reRenderMessageList( result.data.messages );
            }
        }
    }

    /**
     * Handles click events on the right inbox section
     * 
     * @param {Event} event
     */
    async _handleInboxRightClicks( event ) {

        const mobileCloseBtn = event.target.closest( '#mobile-close' );

        if ( mobileCloseBtn ) {
            this.inboxRight.classList.remove( 'has-content' );
            const headerSection = this.inboxRight.querySelector( '.callismart-app-support-inbox_right-header' );
            const messageBody   = this.inboxRight.querySelector( '.callismart-app-support-inbox_right-message-body' );
            const activeMessage = this.inboxLeft.querySelector( '.callismart-app-support_message.active' );
            
            headerSection.innerHTML = '';
            messageBody.innerHTML   = '';
            messageBody.appendChild( this.messageBoxSvg );
            activeMessage?.classList.remove( 'active' );
            
            return;
        }

        const messageAction = event.target.closest( '.button.unread-action, .button.delete-action' );

        if ( messageAction ) {
            const params = this.safeJsonParse( messageAction.getAttribute( 'data-args' ) );
            
            if ( ! params ) return;

            let isDelete    = 'delete' === params.action_type;
            let markUnread  = 'unread' === params.action_type;
            const messageLi = this.inboxLeft.querySelector( `#message-id-${params.message_id}`);

            if ( isDelete && ! confirm( 'Are you sure you want to delete this message?' ) ) {
                return;
            }

            if ( markUnread && messageLi.classList.contains( 'unread' ) ) {
                return;
            }

            const result = await this._fetch( params );

            if ( ! result ) return;

            if ( isDelete && result.success ) {
                const mobileCloseBtn = this.inboxRight.querySelector( '#mobile-close' );
                
                mobileCloseBtn && mobileCloseBtn.click();
                messageLi?.style.setProperty( 'opacity', '0' );
                setTimeout( () => messageLi?.remove(), 1100 );
                
            }

            if ( markUnread ) {
                messageLi.classList.add( 'unread' );
            }
            
            return;
        }

        const orderStatus   = event.target.closest( '#callismart-support-verify-order' );

        if ( orderStatus ) {
            const url = orderStatus.getAttribute( 'href' );

            if ( url ) {
                event.preventDefault();
                this.showSpinner();
                fetch( url, { credentials: 'include'})
                .then( response => response.json() )
                .then( result => {
                    const message = 
                        result?.data?.status ? `Order status "${result.data.status}"`
                        : `Error: "${result?.data?.message ?? 'Unable to check order status'}"`

                    showNotification( message, 5000 )

                })
                .catch( err => console.warn( err ) )
                .finally( this.hideSpinner.bind(this) );

                return;
            }
        }

    }

    /**
     * Render message object in the right hand side of the support inbox
     * 
     * @param {Object} message      - The message object.
     * @param {HTMLLIElement} liEl    - The clicked list element
     */
    _renderMessage( message, liEl ) {

        if ( liEl.classList.contains( 'active' ) ) {
            return;
        }
        
        const headerSection     = this.inboxRight.querySelector( '.callismart-app-support-inbox_right-header' );
        const messageBody       = this.inboxRight.querySelector( '.callismart-app-support-inbox_right-message-body' );
        const subjectSection    = document.createElement( 'div' );
        const subject           = document.createElement( 'h2' );
        const dateP             = document.createElement( 'p' );
        const mobileCloseBtn    = document.createElement( 'span' );

        mobileCloseBtn.id       = 'mobile-close';

        subject.textContent     = message.subject ?? 'No subject';
        dateP.textContent       = this._getRelativeTime( message.created_at );

        subjectSection.classList.add( 'subject' );
        subjectSection.appendChild( subject );
        subjectSection.appendChild( dateP );

        const actionDiv         = document.createElement( 'div' );
        const markUnreadBtn     = document.createElement( 'button' );
        const deleteBtn         = document.createElement( 'button' );

        actionDiv.className     = 'actions';
        
        markUnreadBtn.className    = 'button unread-action';
        markUnreadBtn.type         = 'button';
        markUnreadBtn.textContent  = 'Mark as unread';
        markUnreadBtn.setAttribute( 'data-args', JSON.stringify( {action_type: 'unread', message_id: message.id} ) );


        deleteBtn.textContent       = 'Delete';
        deleteBtn.className         = 'button delete-action';
        deleteBtn.type              = 'button';

        deleteBtn.setAttribute( 'data-args', JSON.stringify( {action_type: 'delete', message_id: message.id} ) );
 
        actionDiv.appendChild( markUnreadBtn );
        actionDiv.appendChild( deleteBtn );

        headerSection.innerHTML = '';
        headerSection.appendChild( mobileCloseBtn );
        headerSection.appendChild( subjectSection );
        headerSection.appendChild( actionDiv );

        messageBody.innerHTML = message.body;

        if ( liEl && liEl.classList.contains( 'unread' ) ) {
            this._fetch( { action_type: 'read', message_id: message.id }, false );
            liEl.classList.remove( 'unread' );
        }
        
        this.inboxRight.classList.add( 'has-content' );
        this.inboxLeft.querySelectorAll( '.callismart-app-support_message' ).forEach( el => el.classList.remove( 'active' ) );
        liEl.classList.add( 'active' );
        
    }

    /**
     * Re-render the message list after bulk actions or refresh.
     *
     * @param {Object|Array} messages - The updated messages returned from the server.
     */
    _reRenderMessageList( messages ) {
        const listContainer = this.inboxLeft.querySelector( '.callismart-app-support-inbox_left-messages-list' );
        this.inboxLeft.querySelector( '.masterCheckBox' ).classList.remove( 'active' );

        if ( ! listContainer ) return;

        const emptyState = listContainer.querySelector( '.empty-messages' );

        // Fade out the list for a smooth update
        listContainer.style.opacity = '0.4';

        // Clear current message items but keep the empty-state node
        listContainer.querySelectorAll( '.callismart-app-support_message:not(.empty-messages)' )
        .forEach( node => node.remove() );

        const hasMessages = messages && Object.keys( messages ).length > 0;
        

        // Toggle empty-state visibility
        if ( emptyState ) {
            emptyState.style.display = hasMessages ? 'none' : '';
        }

        if ( hasMessages ) {
            Object.entries( messages ).forEach( ( [ id, data ] ) => {
                const li = document.createElement( 'li' );
                li.id = `message-id-${id}`;
                li.className = `callismart-app-support_message ${data.read ? 'read' : 'unread'}`;
                li.dataset.messageJson = JSON.stringify( data );

                const checkbox = document.createElement( 'input' );
                checkbox.type = 'checkbox';
                checkbox.id = id;
                checkbox.className = 'callismart-app-support-checkbox';

                const infoDiv = document.createElement( 'div' );
                infoDiv.className = 'callismart-app-support_message-info';

                const subjectP = document.createElement( 'p' );
                subjectP.className = 'subject';
                subjectP.textContent = data.subject || 'No Subject';

                const timeSpan = document.createElement( 'span' );
                timeSpan.className = 'message-time';
                subjectP.appendChild( timeSpan );

                const excerptSpan = document.createElement( 'span' );
                excerptSpan.className = 'exerpt';
                excerptSpan.textContent = this.#trimWords( data.body, 6 );

                infoDiv.appendChild( subjectP );
                infoDiv.appendChild( excerptSpan );

                li.appendChild( checkbox );
                li.appendChild( infoDiv );

                listContainer.appendChild( li );
            } );
        }

        
        // Fade back in
        setTimeout( () => {
            listContainer.style.transition = 'opacity 0.25s ease-in';
            listContainer.style.opacity = '1';
        }, 100 );
    }

    /**
     * Inbox consent management
     * 
     * @param {Event} event
     */
    async consentManagement( event ) {
        const grant     = event.target  === this.grantConsentBtn;
        const revoke    = event.target === this.withdrawConsentBtn;
        const params    = { action_type: 'consent' };

        if ( grant ) {
            params.consent  = true;
        } else if ( revoke ) {
            params.consent  = false;
        }


        if ( ! grant && ! revoke ) {
            return;
        }

        const result = await this._fetch( params );

        showNotification( result.data.message, 5000 );
        setTimeout( () => window.location.reload(), 5000 );
    }

    /**
     * Safely parse the given value to JSON format
     * 
     * @param {any} value
     */
    safeJsonParse( value ) {
        let data = null;
        try {
            data = JSON.parse( value );
        } catch( e ){}

        return data;
    }

    /**
     * Trim words
     * 
     * @param {String} value
     */
    #trimWords( html, numWords = 6  ) {

        const div = document.createElement( 'div' );
        div.innerHTML = html || '';
        const text = div.textContent || div.innerText || '';

        // 2. Trim by words (like wp_trim_words)
        const words = text.trim().split( /\s+/ );
        const excerpt = words.slice( 0, numWords ).join( ' ' );

        // 3. Append ellipsis if text was longer
        const ellipsis = words.length > numWords ? '…' : '';

        return excerpt + ellipsis;

    }

    /**
     * Calculates a readable relative time string (e.g., "5 days ago").
     * @param {string} dateString - MySQL date string.
     * @returns {string} Relative time.
     */
    _getRelativeTime(dateString) {
        if (!dateString) return 'Unknown date';

        const now = new Date();
        const past = new Date(dateString.replace(' ', 'T'));
        const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000);

        if (diffInSeconds < 60) return 'Just now';

        if (diffInSeconds < 3600) {
            const mins = Math.floor(diffInSeconds / 60);
            return `${mins} ${mins === 1 ? 'minute' : 'minutes'} ago`;
        }

        if (diffInSeconds < 86400) {
            const hrs = Math.floor(diffInSeconds / 3600);
            return `${hrs} ${hrs === 1 ? 'hour' : 'hours'} ago`;
        }

        if (diffInSeconds < 2592000) {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} ${days === 1 ? 'day' : 'days'} ago`;
        }

        return past.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    /**
     * Copy environment diagnosis to clipboard
     */
    _copyEnvToClipboard() {
        const json = document.querySelector( '#smartwoo-tools-json' ).value;
        navigator.clipboard.writeText(json)
        .then( () => showNotification( 'System JSON report copied!' ) )
        .catch( err => showNotification( err.message ) )
    }

    /**
     * Show loading indicator
     */
    showSpinner() {
        this.spinner = smartWooAddSpinner( this.loader, true );
    }

    /**
     * Hide loading spinner
     */
    hideSpinner() {
        if ( this.loader ) {
            smartWooRemoveSpinner( this.spinner );
            this.loader.querySelectorAll( 'img' ).forEach( loader => loader.remove() );
        }
    }
}


document.addEventListener( 'DOMContentLoaded', () => new CallismartSupport( '.callismart-app-support-inbox' ) );