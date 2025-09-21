/**
 * Smart Woo Admin dashboard script
 *
 * Section-level delegated event handling and AJAX hydrate/render helpers.
 *
 * @since 2.5
 * @package Smart-Woo-Service-Invoicing
 */

class SmartWooAdminDashboard {
    constructor() {
        this.serverConfig = smartwoo_admin_vars || {};
        this._hydrateDashboard();
        this._bindEvents();
    }

    /**
     * Hydrate/cache the dashboard interactivity elements.
     */
    _hydrateDashboard() {
        this.dashboardContainer = document.querySelector( '.sw-admin-dashboard-interactivity-section' );
        if ( ! this.dashboardContainer ) {
            return;
        }

        this.sections = {
            subscriptionList: this.dashboardContainer.querySelector( '[data-section="subscriptionList"]' ),
            subscribersList:  this.dashboardContainer.querySelector( '[data-section="subscribersList"]' ),
            needsAttention:   this.dashboardContainer.querySelector( '[data-section="needsAttention"]' ),
            recentInvoices:   this.dashboardContainer.querySelector( '[data-section="recentInvoices"]' ),
            activities:       this.dashboardContainer.querySelector( '[data-section="activities"]' ),
        };

        this.globalLoader = document.getElementById( 'swloader' );
    }

    /**
     * Bind event listeners at dashboard wrapper level and dispatch to section handlers.
     */
    _bindEvents() {
        if ( ! this.dashboardContainer ) {
            return;
        }

        this.dashboardContainer.addEventListener( 'click', ( event ) => {
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
        } );
    }

    /* -------------------------
     * Section-specific handlers
     * ------------------------- 
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

        if ( params ) {
            const response  = await this._fetch( params );
            
            if ( ! response ) return;

            const tableRows = response?.table_rows ?? [];
            
            let rows = ``;

            tableRows.map( row => {
                rows += row;
            });

            this._replaceSectionBodyHtml( sectionEl, rows ); 
            this._updateSectionPagination( sectionEl, response.pagination );

            sectionEl.setAttribute( 'data-current-filter', params.filter );
        }
    }

    _handleSubscribersSectionEvent( event, sectionEl ) {
        // This section shares similar interaction patterns as subscriptionList
        const filterBtn = event.target.closest( '.smartwoo-dasboard-filter-button' );
        if ( filterBtn ) {
            event.preventDefault();
            return this._fetchAndRenderSection( sectionEl, { filter: filterBtn.dataset.action } );
        }

        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }
    }

    _handleNeedsAttentionSectionEvent( event, sectionEl ) {
        const filterBtn = event.target.closest( '.smartwoo-dasboard-filter-button' );
        if ( filterBtn ) {
            event.preventDefault();
            return this._fetchAndRenderSection( sectionEl, { filter: filterBtn.dataset.action } );
        }

        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }
    }

    _handleRecentInvoicesSectionEvent( event, sectionEl ) {
        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }
    }

    _handleActivitiesSectionEvent( event, sectionEl ) {

    }

    /**
     * Reset all disabled section button
     * 
     * @param {HTMLElement} sectionEl - The section element
     */
    _resetDisabledButtons( sectionEl ) {
        sectionEl?.querySelectorAll( '.smartwoo-dasboard-filter-button[dasabled="true"]').forEach( btn => btn.disabled = false );
    }

    /* -------------------------
     * Pagination handler
     * ------------------------- */

    _handlePaginationClick( sectionEl, pagBtn ) {
        const raw = pagBtn.getAttribute( 'data-pagination' );
        let pagination = this._parseJSONSafe( raw );
        if ( ! pagination ) {
            // If data-pagination is not a JSON string, try data attributes directly
            pagination = {
                name: pagBtn.dataset.name || null,
                number: pagBtn.dataset.number || null,
            };
        }

        return this._fetchAndRenderSection( sectionEl, { pagination: pagination } );
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
                if ( typeof val === 'object' ) {
                    payload[key] = val;
                } else {
                    payload[key] = String( val );
                }
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
            
            const responseJson  = await response.json(); // Our REST endpoint always returns JSON Object.

            if ( ! response.ok ) {
                let errorMessage = `HTTP error: ${ response.status }`;
                const error = new Error( responseJson?.message ?? errorMessage );

                error.type = 'SMARTWOO_REST_ERROR';
                throw error;
            }

            return responseJson;            
        } catch (error) {
            if ( error instanceof TypeError ) {
                showNotification( 'Please check your internet connection and try again later.', 3000 );
            } else if ( 'SMARTWOO_REST_ERROR' === error.type ) {
                showNotification( error.message, 5000 );
            }

            console.error(error);
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
                jQuery( tBody ).fadeIn();
            })
            
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

    _displaySectionError( sectionEl, message ) {
        const tbody = sectionEl.querySelector( '.smartwoo-table-content' );
        if ( tbody ) {
            tbody.innerHTML = `<tr><td class="sw-not-found" colspan="99">${ message }</td></tr>`;
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
            return JSON.parse( raw );
        } catch ( e ) {
            return null;
        }
    }
}

addEventListener( 'DOMContentLoaded', () => {
    // eslint-disable-next-line no-new
    new SmartWooAdminDashboard();
} );
