/**
 * Smart Woo Admin dashboard script
 *
 * Section-level delegated event handling and AJAX hydrate/render helpers.
 *
 * @since 2.5
 * @package Smart-Woo-Service-Invoicing
 */

class SmartWooAdminDashboard {
    static _events  = {};

    static _instance;
    
    constructor() {
        if ( SmartWooAdminDashboard._instance ) {
            throw new Error("Use SmartWooAdminDashboard.getInstance()");
        }
        SmartWooAdminDashboard._instance = this;
        this.serverConfig = smartwoo_admin_vars || {};
        this._hydrateDashboard();
        this._bindEvents();
        
    }

    static getInstance() {
        if ( ! SmartWooAdminDashboard._instance ) {
            SmartWooAdminDashboard._instance = new SmartWooAdminDashboard();
        }

        return SmartWooAdminDashboard._instance;
    }

    /**
     * Hydrate/cache the dashboard interactivity elements.
     */
    _hydrateDashboard() {
        this.interactivitySection = document.querySelector( '.sw-admin-dashboard-interactivity-section' );
        if ( ! this.interactivitySection ) {
            return;
        }

        this.sections = {
            subscriptionList:   this.interactivitySection.querySelector( '[data-section="subscriptionList"]' ),
            subscribersList:    this.interactivitySection.querySelector( '[data-section="subscribersList"]' ),
            needsAttention:     this.interactivitySection.querySelector( '[data-section="needsAttention"]' ),
            recentInvoices:     this.interactivitySection.querySelector( '[data-section="recentInvoices"]' ),
            activities:         this.interactivitySection.querySelector( '[data-section="activities"]' ),
            modal:              this.interactivitySection.closest( '.sw-admin-dashboard' ).querySelector( '[data-section="modal"]' ),
            search:             this.interactivitySection.closest( '.sw-admin-dashboard' ).querySelector( '.smartwoo-interactivity-dashboard-search-container' )
        };

        this.globalLoader = document.getElementById( 'swloader' );
    }

    /**
     * Bind event listeners at dashboard wrapper level and dispatch to section handlers.
     */
    _bindEvents() {
        if ( ! this.interactivitySection ) {
            return;
        }

        this.interactivitySection.addEventListener( 'click', ( event ) => {
            const sectionEl = event.target.closest( '[data-section]' );

            if ( ! sectionEl ) {
                return;
            }

            const sectionKey = sectionEl.getAttribute( 'data-section' );

            // Dispatch to the dedicated handler for the section
            switch ( sectionKey ) {
                case 'subscriptionList':
                    this._handleSubscriptionSectionEvent( event, sectionEl );
                    break;

                case 'subscribersList':
                    this._handleSubscribersSectionEvent( event, sectionEl );
                    break;

                case 'needsAttention':
                    this._handleNeedsAttentionSectionEvent( event, sectionEl );
                    break;

                case 'recentInvoices':
                    this._handleRecentInvoicesSectionEvent( event, sectionEl );
                    break;

                case 'activities':
                    this._handleActivitiesSectionEvent( event, sectionEl );
                    break;

                default:
                    // Unknown section — ignore
                    break;
            }
        });

        this.sections.search.addEventListener( 'submit', this._performSearch.bind(this) );
        this.sections.search.addEventListener( 'input', this._resetFormInput.bind(this) );

        this.sections.modal.addEventListener( 'click', this._modalEventHandler.bind(this) );
        this.sections.modal.addEventListener( 'submit', this._modalEventHandler.bind(this) );
        this.sections.modal.addEventListener( 'input', this._modalEventHandler.bind(this) );
        this.sections.modal.addEventListener( 'change', this._modalEventHandler.bind(this) );

        this.sections.subscriptionList.addEventListener( 'change', this._tableCheckboxHandler.bind(this) );
        this.sections.subscriptionList.addEventListener( 'submit', this._submitBulkAction.bind(this) );
        
        this.sections.activities.addEventListener( 'submit', this._handleActivitiesSectionEvent.bind(this) );
        this.sections.activities.addEventListener( 'change', this._handleActivitiesSectionEvent.bind(this) );
        this.sections.activities.addEventListener( 'input', this._handleActivitiesSectionEvent.bind(this) );

        document.addEventListener( 'keydown', this._modalEventHandler.bind(this) );
        document.addEventListener( 'keydown', this._restoreInteractitySection.bind(this) );
        document.addEventListener( 'click', this._restoreInteractitySection.bind(this) );

        // Register section-specific event handlers
        SmartWooAdminDashboard.on( 'markAsPaid', 'needsAttention', this._processInvoiceOptions );
        SmartWooAdminDashboard.on( 'sendPaymentReminder', 'needsAttention', this._processInvoiceOptions );
        SmartWooAdminDashboard.on( 'viewInvoiceDetails', 'needsAttention', this._showInvoiceDetails );

        SmartWooAdminDashboard.on( 'composeEmail', 'needsAttention', this._proComposeEmail );
        
        SmartWooAdminDashboard.on( 'autoProcessOrder', 'needsAttention', this._proAutoProcessOrder );
        SmartWooAdminDashboard.on( 'autoRenewService', 'needsAttention', this._proAutoRenewService );

        SmartWooAdminDashboard.on( 'viewOrderDetails', 'needsAttention', this._showOrderDetails );
        SmartWooAdminDashboard.on( 'viewRelatedInvoice', 'needsAttention', this._showInvoiceDetails );
        SmartWooAdminDashboard.on( 'previewServiceDetails', 'needsAttention', this._showServiceDetails );
        
    }

    /* -------------------------
     * Section-specific handlers
     * ------------------------- 
     */

    /**
     * Handles the subscription list section of the dashboard.
     * 
     * @param {Event} event 
     * @param {HTMLElement} sectionEl 
     * @returns void
     */
    async _handleSubscriptionSectionEvent( event, sectionEl ) {
        // Filter buttons
        const filterBtn = event.target.closest( '.smartwoo-dasboard-filter-button' );
        let params = null;
        if ( filterBtn ) {
            event.preventDefault();
            params = {
                filter: filterBtn.getAttribute( 'data-get-filter' ),
                section: 'subscriptionList',
                ...this._parseJSONSafe( filterBtn.getAttribute( 'data-state-args' ) )
            };
        }

        const pagBtn = event.target.closest( '.sw-pagination-button' );

        if ( pagBtn ) {
            event.preventDefault();
            const currentFilter = sectionEl.getAttribute( 'data-current-filter' );
            params = {
                filter: currentFilter,
                section: 'subscriptionList',
                ...this._parseJSONSafe( pagBtn.getAttribute( 'data-pagination' ) )
            };

        }

        if ( ! params ) return;
        
        const response  = await this._fetch( params );
        
        if ( ! response ) return;

        const tableRows = response?.table_rows ?? [];
        const rows      = tableRows.join('');

        this._replaceSectionBodyHtml( sectionEl, rows ); 
        this._updateSectionPagination( sectionEl, response.pagination );
        this._updateSectionHeading( sectionEl, response.title ?? 'Subscriptions' );
        

        if ( ! pagBtn ) {
            this._resetDisabledButtons( sectionEl );
            event.target.closest( '.smartwoo-dasboard-filter-button' )?.setAttribute( 'disabled', true );
        }

        sectionEl.setAttribute( 'data-current-filter', params.filter );
    }

    /**
     * Handles the subscribers list section of the dashboard.
     * 
     * @param {Event} event 
     * @param {HTMLElement} sectionEl 
     * @returns void
     */
    async _handleSubscribersSectionEvent( event, sectionEl ) {
        // We only handle pagination of subscribers list pagination.
        const pagBtn = event.target.closest( '.sw-pagination-button' );

        if ( pagBtn ) {
            event.preventDefault();
            const params = {
                filter: 'subscribersList',
                section: 'subscribersList',
                ...this._parseJSONSafe( pagBtn.getAttribute( 'data-pagination' ) )
            };
            const response  = await this._fetch( params );
                    
            if ( ! response ) return;

            const tableRows = response?.table_rows ?? [];
            const rows      = tableRows.join('');


            this._replaceSectionBodyHtml( sectionEl, rows ); 
            this._updateSectionPagination( sectionEl, response.pagination );
        }
    }

    /**
     * Handles the needs attention section of the dashboard.
     * 
     * @param {Event} event 
     * @param {HTMLElement} sectionEl 
     * @returns void
     */
    async _handleNeedsAttentionSectionEvent( event, sectionEl ) {
        // Filter buttons
        const filterBtn = event.target.closest( '.smartwoo-dasboard-filter-button' );
        let params = null;
        if ( filterBtn ) {
            event.preventDefault();
            params = {
                filter: filterBtn.getAttribute( 'data-get-filter' ),
                section: 'needsAttention',
                ...this._parseJSONSafe( filterBtn.getAttribute( 'data-state-args' ) )
            };
        }

        const pagBtn = event.target.closest( '.sw-pagination-button' );

        if ( pagBtn ) {
            event.preventDefault();
            const currentFilter = sectionEl.getAttribute( 'data-current-filter' );
            params = {
                filter: currentFilter,
                section: 'needsAttention',
                ...this._parseJSONSafe( pagBtn.getAttribute( 'data-pagination' ) )
            };

        }

        if ( params ) {
            const response  = await this._fetch( params );
            
            if ( ! response ) return;

            const tableRows = response?.table_rows ?? [];
            const rows      = tableRows.join('');


            this._replaceSectionBodyHtml( sectionEl, rows ); 
            this._updateSectionPagination( sectionEl, response.pagination );
            this._updateSectionHeading( sectionEl, response.title ?? 'Subscriptions' );
            

            if ( ! pagBtn ) {
                this._resetDisabledButtons( sectionEl );
                event.target.closest( '.smartwoo-dasboard-filter-button' )?.setAttribute( 'disabled', true );
            }

            sectionEl.setAttribute( 'data-current-filter', params.filter );
            return;         
        }

        const optionsBtn    = event.target.closest( '.smartwoo-options-dots' );
        const targetOption  = optionsBtn?.querySelector( '.smartwoo-options-dots-items' );
        
        sectionEl.querySelectorAll( '.smartwoo-options-dots-items' ).forEach( el =>{
            if ( el.classList.contains( 'active' ) && el !== targetOption ) {
                el.classList.remove( 'active' );
            }
        });

        if ( targetOption && ! event.target.closest( '.smartwoo-options-dots-items' ) ) {
            targetOption.classList.toggle( 'active' );
        }

        const optionsAction = event.target.closest( '.smartwoo-options-dots-items li' );

        if ( optionsAction ) {
            const action    = optionsAction.getAttribute( 'data-action' );
            let args      = this._parseJSONSafe( optionsAction.getAttribute( 'data-args' ) );
            
            if ( SmartWooAdminDashboard._events['needsAttention'][action] ) {
                SmartWooAdminDashboard._events['needsAttention'][action].forEach( handler => handler( args, optionsAction ) )
            }
        }
    }

    _handleRecentInvoicesSectionEvent( event, sectionEl ) {
        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }
    }

    /**
     * 
     * @param {Event} event 
     * @param {HTMLElement} sectionEl 
     * @returns 
     */
    _handleActivitiesSectionEvent( event, sectionEl ) {    
        //This section is handled by Smart Woo Pro.
        if ( ! this.serverConfig.smartwoo_pro_is_installed ) return;
        // Pro should register event types and handlers.
        const sectionEvents = SmartWooAdminDashboard._events['activities'] || {};
        const eventType     = event.type;
        if ( ! sectionEl ) {
            sectionEl = this.sections.activities;
        }
        
        if ( sectionEvents[eventType] ) {
            sectionEvents[eventType].forEach( handler => handler( event, sectionEl ) );
        }
    }

    /**
     * Reset all disabled section button
     * 
     * @param {HTMLElement} sectionEl - The section element
     */
    _resetDisabledButtons( sectionEl ) {
        sectionEl?.querySelectorAll( '.smartwoo-dasboard-filter-button[disabled="true"]').forEach( btn => btn.disabled = false );
    }

    /**
     * Fetches dashboard data from our `wp-json/smartwoo-admin/v1/` REST endpoint.
     *
     * @param  {Object} params
     * @return {Promise<?Object|string>}
     */
    async _fetch( params ) {
        const url = new URL( this.serverConfig.restApi.admin_url );
        url.pathname += 'dashboard';
        const payload = {};

        if ( params ) {
            Object.keys( params ).forEach( ( key ) => {
                const val = params[ key ];
                payload[key] = typeof val === 'object' ? val : String( val );
            });
        }

        this._showLoader();

        try {
            const response = await fetch( url, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': this.serverConfig.restApi.WP_API_nonce,
                    'Content-Type': `application/json; charset=${this.serverConfig.charset}`,
                },
                credentials: 'same-origin',
                body: JSON.stringify( payload ),
            } );

            const responseJson = await response.json(); // REST always returns JSON

            if ( ! response.ok ) {
                // Distinguish auth/cookie problems
                if ( response.status === 401 || response.status === 403 ) {
                    const error = new Error( responseJson?.message ?? 'Authentication failed. Please refresh current page.' );
                    error.type = 'AUTH_ERROR';
                    throw error;
                }

                const error = new Error( responseJson?.message ?? `HTTP error: ${ response.status }` );
                error.type = 'SMARTWOO_REST_ERROR';
                throw error;
            }

            return responseJson;            
        } catch ( error ) {
            if ( error instanceof TypeError ) {
                showNotification( 'Please check your internet connection and try again later.', 5000 );
            } else if ( error.type === 'AUTH_ERROR' ) {
                showNotification( error.message, 5000 );
            } else if ( error.type === 'SMARTWOO_REST_ERROR' ) {
                showNotification( error.message, 5000 );
            } else {
                console.error('Unexpected error:', error);
            }
        } finally {
            this._hideLoader();
        }
    }

    /* -------------------------
     * DOM update helpers
     * ------------------------- */

    _replaceSectionBodyHtml( sectionEl, html ) {
        const tBody = sectionEl.querySelector( '.smartwoo-table-content' ) || sectionEl.querySelector( '.sw-dashboard-activities-section' ) || sectionEl;
        if ( tBody ) {
            jQuery( tBody ).fadeOut( 'slow', () => {
                tBody.innerHTML = html;

                if ( tBody.querySelector( 'tr td.sw-not-found' ) ) {
                    tBody.closest( 'table' )?.querySelector( 'thead' )?.classList.add( 'smartwoo-hide' );
                    sectionEl.querySelector( '.sw-dashboard-pagination' )?.classList.add( 'smartwoo-hide' );
                
                } else {
                    tBody.closest( 'table' )?.querySelector( 'thead' )?.classList.remove( 'smartwoo-hide' );
                    sectionEl.querySelector( '.sw-dashboard-pagination' )?.classList.remove( 'smartwoo-hide' );
                }

                jQuery( tBody ).fadeIn();
            });
            
        }
    }

    /**
     * Updates the pagination buttons for the given section.
     * 
     * @param {HTMLElement} sectionEl 
     * @param {Object} pagination 
     * @returns 
     */
    _updateSectionPagination( sectionEl, pagination ) {
        // Find pagination container inside section and update button disabled states & data-pagination
        const pagContainer = sectionEl.querySelector( '.sw-dashboard-pagination' );
        if ( ! pagContainer ) {
            return;
        }

        // Example pagination object expected: {"current_page": 2, "limit": 25, "total_items": 53, "total_pages": 3, "prev_page": 1, "next_page": 3}
        const buttons   = pagContainer.querySelectorAll( 'button' );
        const prevBtn   = buttons[0];
        const nextBtn   = buttons[1];

        prevBtn.setAttribute( 'data-pagination', JSON.stringify( { page: pagination.prev_page, limit: pagination.limit} ) );
        nextBtn.setAttribute( 'data-pagination', JSON.stringify( { page: pagination.next_page, limit: pagination.limit} ) );

        if ( ! pagination.next_page ) {
            nextBtn.setAttribute( 'disabled', true );
        } else {
            nextBtn.removeAttribute( 'disabled' );

        }

        if ( ! pagination.prev_page ) {
            prevBtn.setAttribute( 'disabled', true );
        } else {
            prevBtn.removeAttribute( 'disabled' );

        }
    }

    _updateSectionHeading( sectionEl, headingText ) {
        const headingEl = sectionEl.querySelector( '.sw-service-subscription-lists_current-heading' );
        if ( headingEl ) {
            headingEl.textContent = headingText;
        }
    }

    /* -------------------------
     * Loader helpers
     * ------------------------- */

    _showLoader() {
        if ( this.globalLoader ) {
            this.spinner = smartWooAddSpinner( this.globalLoader, true );
        }
    }

    _hideLoader() {

        if ( this.globalLoader ) {
            smartWooRemoveSpinner( this.spinner );
            this.globalLoader.querySelectorAll( 'img' ).forEach( loader => loader.remove() );
        }
    }

    /* -------------------------
     * Utilities
     * ------------------------- */

    _parseJSONSafe( raw ) {
        if ( ! raw ) {
            return null;
        }
        try {
            return JSON.parse( decodeURIComponent( raw ) );
        } catch ( e ) {
            return null;
        }
    }

    /**
     * Register event listener and callback handler for a given section of the dashboard.
     * 
     * @param {String} eventName - The name of the event
     * @param {String} section  - The name of the dashboard section.
     * @param {Function} func - The function to call on the event.
     */
    static on( eventName, section, func ) {
        if ( typeof func !== 'function' ) {
            console.warn( 'Even handler must be a valid callback function.' );
            return;
        }

        if ( ! this._events[section] ) {
            this._events[section] = {};
        }

        if ( ! this._events[section][eventName] ) {
            this._events[section][eventName] = [];
        }

        const boundFunc = func.bind(this._instance || SmartWooAdminDashboard.getInstance() );
        this._events[section][eventName].push(boundFunc);

    }

    /**
     * Trigger an event for a given section of the dashboard.
     * 
     * @param {String} eventName - The name of the event
     * @param {String} section  - The name of the dashboard section.
     * @param {any} args - Arguments to pass to the event handler.
     */
    static trigger( eventName, section, args ) {
        if ( this._events[section] && this._events[section][eventName] ) {
            this._events[section][eventName].forEach( handler => handler( args ) );
        }
    }

    /**
     * Process actions on invoice table such as marking invoice as paid, and sending payment reminder
     * 
     * @param {object} args
     * @param {HTMLElement} el
     */
    async _processInvoiceOptions( args, el ) {
        const params = {
            'section': 'needsAttention_options',
            ...args
        }

        const response = await this._fetch( params );

        if ( ! response ) return;

        showNotification( response.message ?? 'Something went wrong', 10000 );
        const row = el.closest( 'tr' );
        if ( row ) {
            jQuery( row ).fadeOut( 2000, () => {
                row.remove();
            });
        }
    }

    /**
     * Show pro ads for email compose feature.
     */
    _proComposeEmail() {
        if ( this.serverConfig.smartwoo_pro_is_installed ) return;
        smartwoo_pro_ad(
        'Compose Email',
        'Craft and send personalized emails to your clients directly from your dashboard. Elevate your communication with the professional power of Smart Woo Pro.'
        );
    }

    /**
     * Show pro ads for auto process order feature.
     */
    _proAutoProcessOrder() {
        if ( this.serverConfig.smartwoo_pro_is_installed ) return;
        smartwoo_pro_ad(
        'Hands-Free Order Processing',
        'Eliminate the endless cycle of manual reviews. Smart Woo Pro automatically processes subscription orders, sets up services, and notifies customers — saving you time and effort.'
        );

    }

    /**
     * Show pro ads for auto renew service feature.
     */
    _proAutoRenewService() {
        if ( this.serverConfig.smartwoo_pro_is_installed ) return;
        smartwoo_pro_ad(
        'Auto Renew Service',
        'Skip the manual work. With Smart Woo Pro, admins can instantly trigger a full subscription renewal — update invoices, mark services renewed, and send client emails — all in one click. Perfect for offline payments or fixing failed renewals.'
        );

    }

    /**
     * Show order details in modal.
     * @param {object} args
     */
    _showOrderDetails( args ) {
        const orderDetails = args.order_details;

        if ( ! orderDetails ) {
            this._openModal( '<p>No order details found.</p>' );
            return;
        }
                
        this._openModal( orderDetails.heading, orderDetails.body, orderDetails.footer  );
    }

    /**
     * Show related invoice in modal.
     * 
     * @param {object} args
     */
    _showInvoiceDetails( args ) {
        const invoiceDetails = args.invoice_details;
        if ( ! invoiceDetails ) {
            this._openModal( '<p>No invoice details found.</p>' );
            return;
        }
        this._openModal( invoiceDetails.heading, invoiceDetails.body, invoiceDetails.footer  );
    }

    /**
     * Show service details in modal.
     * @param {object} args
     */
    _showServiceDetails( args ) {
        const serviceDetails = args.service_details;
        if ( ! serviceDetails ) {
            this._openModal( '<p>No service details found.</p>' );
            return;
        }
        this._openModal( serviceDetails.heading, serviceDetails.body, serviceDetails.footer  );
    }

    /**
     * Open modal.
     * 
     * @param {String} heading - HTML content for the modal heading section.
     * @param {String} body - HTML content for the modal body section.
     * @param {String} footer - HTML content for the modal footer section.
     */
    async _openModal( heading, body, footer ) {
        try {
            jQuery( this.sections.modal ).fadeOut( 'fast', () => {
                const modalHeading = this.sections.modal.querySelector( '.smartwoo-modal-heading' );
                const modalBody    = this.sections.modal.querySelector( '.smartwoo-modal-body' );
                const modalFooter  = this.sections.modal.querySelector( '.smartwoo-modal-footer' );

                modalHeading.innerHTML = heading || '<h2>Modal</h2>';
                modalBody.innerHTML    = body || '<p>No content</p>';
                modalFooter.innerHTML  = footer || '';

                // If modal body contains <object>, handle loader
                const objectEl = modalBody.querySelector( 'object' );
                if ( objectEl ) {
                    this._awaitInvoicePreview( objectEl, modalBody );
                }

                jQuery( this.sections.modal ).fadeIn( 'slow' );
            });
        } catch ( error ) {
            return false;
        } finally {
            const event = new CustomEvent( 'SmartWooDashboardModalOpen', {
                detail: {
                    modal: this.sections.modal
                }
            });
            document.dispatchEvent( event );
        }
    }

    /**
     * Close modal
     */
    _closeModal() {
        jQuery( this.sections.modal ).fadeOut( 'slow', () => {
            // Dispatch close event after fadeOut completes
            const event = new CustomEvent( 'SmartWooDashboardModalClose', {
                detail: {
                    modal: this.sections.modal
                }
            });
            event.modal = this.sections.modal;
            document.dispatchEvent( event );
        });
    }
    
    /**
     * Handle modal events.
     * 
     * @param {Event} event
     */
    _modalEventHandler( event ) {
        if ( 'Escape' === event.key || this.sections.modal === event.target || event.target.classList?.contains( 'smartwoo-modal-close-btn' ) ) {
            this._closeModal();
            return;
        }
        
        SmartWooAdminDashboard.trigger(event.type, 'modal', event )
    }

    /**
     * Handles the loading preview in the modal while invoice data is being fetched.
     * 
     * @param {HTMLObjectElement} objectEl - The object element.
     * @param {HTMLElement} modalBody - The body section of the dashboard body.
     */
    _awaitInvoicePreview( objectEl, modalBody ) {
        objectEl.style.opacity = '0';

        const spinDiv = document.createElement( 'div' );
        modalBody.insertBefore( spinDiv, objectEl );

        const spinner = smartWooAddSpinner( spinDiv );

        let resolved = false;

        const cleanup = () => {
            if (resolved) return;
            resolved = true;
            smartWooRemoveSpinner( spinner );
            objectEl.style.removeProperty( 'opacity' );
        };

        // Success
        objectEl.addEventListener( 'load', cleanup, { once: true });

        // Some browsers fire error
        objectEl.addEventListener( 'error', cleanup, { once: true });

        // Fallback after 30 seconds: ping the URL
        setTimeout(() => {
            if (!resolved) {
                fetch(objectEl.data, { method: 'HEAD' })
                    .then(res => {
                        if (!res.ok) throw new Error('Bad response');
                        // Resource reachable → let spinner continue until load/error
                    })
                    .catch(() => {
                        // Replace <object> with error notice
                        const errorNotice = document.createElement('div');
                        errorNotice.className = 'sw-error-notice';
                        errorNotice.innerHTML = `
                            <div class="sw-notice"><p>Error!</p></div>
                            <p>It appears your browser cannot display PDF files. 
                            Please use the buttons below to manage, print, or download the invoice.</p>
                        `;
                        smartWooRemoveSpinner( spinner );
                        objectEl.replaceWith(errorNotice);
                        resolved = true;
                    });
            }
        }, 30000); // 30 secs
    }

    /**
     * Clear custom validity on form inputs
     * 
     * @param {Event} event - Even object.
     */
    _resetFormInput( event ) {
        const input = event.target.closest( 'input' );
        if ( input ) {            
            input.setCustomValidity( '' );
        }        
    }

    /**
     * Handle table checkbox events.
     * 
     * @param {Event} event
     */
    _tableCheckboxHandler( event ) {
        const masterCheckbox = event.target.closest( '.serviceListMasterCheckbox' );
        if ( masterCheckbox ) {
            const allCheckboxes = this.sections.subscriptionList.querySelectorAll( 'tbody .serviceListCheckbox' );
            const isChecked     = masterCheckbox.checked;
            allCheckboxes.forEach( checkbox => {
                checkbox.checked = isChecked;
                
            });
            return;
        }
        const rowCheckbox = event.target.closest( '.serviceListCheckbox' );
        if ( rowCheckbox ) {
            const allCheckboxes     = this.sections.subscriptionList.querySelectorAll( 'tbody .serviceListCheckbox' );
            const allChecked        = Array.from( allCheckboxes ).every( checkbox => checkbox.checked );
            const masterCheckbox    = this.sections.subscriptionList.querySelector( '.serviceListMasterCheckbox' );
            if ( masterCheckbox ) {
                masterCheckbox.checked = allChecked;
            }
        }
    }

    /**
     * Submit bulk actions
     * 
     * @param {Event} event
     */
    async _submitBulkAction( event ) {
        event.preventDefault();
        const allCheckboxes = this.sections.subscriptionList.querySelectorAll( 'tbody .serviceListCheckbox' );
        const selectedRows  = Array.from( allCheckboxes ).filter( checkbox => checkbox.checked ).map( checkbox => checkbox.getAttribute( 'data-value' ) );

        if ( selectedRows.length === 0 ) {
            showNotification( 'Please select at least one subscription to perform bulk action.', 5000 );
            return;
        }

        const actionSelect  = this.sections.subscriptionList.querySelector( 'form.sw-table-bulk-action-container select[name="selected_action"]' );
        const selectedAction = actionSelect?.value;

        if ( ! selectedAction ) {
            showNotification( 'Please select a bulk action to perform.', 5000 );
            return;
        }

        if ( 'delete' === selectedAction && ! confirm( `Are you sure you want to delete the selected subscription${ selectedRows.length > 1 ? 's' : '' }? This action cannot be undone.` ) ) {
            return;
        }

        const params = {
            filter: 'bulkActions',
            section: 'subscriptionList_bulk_action',
            action: selectedAction,
            service_ids: selectedRows
        };
        
        const response  = await this._fetch( params );
                
        if ( ! response ) return;
        showNotification( response.message ?? 'Bulk action completed.', 10000 );

        // Refresh the table
        const currentFilter = this.sections.subscriptionList.getAttribute( 'data-current-filter' ) || 'allServices';
        // Get current pagination by parsing the previous pagination button data attributes and adding 1 to the page number.
        const paginationBtns = this.sections.subscriptionList.querySelectorAll( '.sw-dashboard-pagination button' );
        const prevBtn = paginationBtns[0];
        const args = this._parseJSONSafe( prevBtn.getAttribute( 'data-pagination' ) );
        const currentPage = args?.page ? parseInt( args.page, 10 ) + 1 : 1;
        const currentLimit = args?.limit ? parseInt( args.limit, 10 ) : 10;

        const refreshParams = {
            filter: currentFilter,
            section: 'subscriptionList',
            page: currentPage,
            limit: currentLimit
        };
        const refreshResponse  = await this._fetch( refreshParams );
        if ( ! refreshResponse ) return;
        const tableRows = refreshResponse?.table_rows ?? [];
        const rows      = tableRows.join('');


        this._replaceSectionBodyHtml( this.sections.subscriptionList, rows ); 
        this._updateSectionPagination( this.sections.subscriptionList, refreshResponse.pagination );
        this._updateSectionHeading( this.sections.subscriptionList, refreshResponse.title ?? 'Subscriptions' );
        this.sections.subscriptionList.setAttribute( 'data-current-filter', refreshParams.filter );

        // Reset bulk action form
        actionSelect.value = '';
        const masterCheckbox = this.sections.subscriptionList.querySelector( '.serviceListMasterCheckbox' );
        if ( masterCheckbox ) {
            masterCheckbox.checked = false;
        }
        
    }

    /**
     * Perform search
     * 
     * @param {Event} event
     */
    async _performSearch( event ){
        const form = event.target.closest( 'form.smartwoo-interactivity-dashboard-search-container' );

        if ( ! form ) return;
        event.preventDefault();

        const searchTerm    = form.querySelector( 'input#smartwoo-search-input' );
        const searchType    = form.querySelector( 'select#search-select' );

        if ( ! searchTerm.value.trim().length ) {
            searchTerm.setCustomValidity( 'Please enter a search term' );
        }

        if ( ! searchType.value.trim().length ) {
            searchTerm.setCustomValidity( 'Please select a search type' );
        }

        if ( ! form.reportValidity() ) {
            return;
        }

        const params = {
            search_term: searchTerm.value.trim(),
            search_type: searchType.value.trim(),
            filter: 'search',
            section: 'search'
        };

        const response = this._fetch( params );

        if ( response || ! response.table_rows ) return;



    }

    /**
     * Restore interactivity section.
     */
    _restoreInteractitySection( event ) {
        if ( 'Escape' === event.key || event.target.classList?.contains( 'smartwoo-modal-close-btn' ) ) {
            if ( this.interactivitySection.classList.contains( 'smartwoo-hide' ) ) {
               this.interactivitySection.classList.remove( 'smartwoo-hide' ); 
            }
            return;
        }
    }
}

addEventListener( 'DOMContentLoaded', () => {
    // eslint-disable-next-line no-new
    SmartWooAdminDashboard.getInstance();
} );

