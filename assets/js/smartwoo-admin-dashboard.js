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

    _handleSubscriptionSectionEvent( event, sectionEl ) {
        // Filter buttons
        const filterBtn = event.target.closest( '.smartwoo-dasboard-filter-button' );
        if ( filterBtn ) {
            event.preventDefault();
            return this._fetchAndRenderSection( sectionEl, { filter: filterBtn.dataset.action } );
        }

        // Pagination buttons
        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }

        // Row navigation (but avoid navigation on inputs/buttons)
        const row = event.target.closest( '.smartwoo-linked-table-row' );
        if ( row ) {
            return this._maybeNavigateRow( event, row );
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

        const row = event.target.closest( '.smartwoo-linked-table-row' );
        if ( row ) {
            return this._maybeNavigateRow( event, row );
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

        const row = event.target.closest( '.smartwoo-linked-table-row' );
        if ( row ) {
            return this._maybeNavigateRow( event, row );
        }
    }

    _handleRecentInvoicesSectionEvent( event, sectionEl ) {
        const pagBtn = event.target.closest( '.sw-pagination-button' );
        if ( pagBtn ) {
            event.preventDefault();
            return this._handlePaginationClick( sectionEl, pagBtn );
        }

        const row = event.target.closest( '.smartwoo-linked-table-row' );
        if ( row ) {
            return this._maybeNavigateRow( event, row );
        }
    }

    _handleActivitiesSectionEvent( event, sectionEl ) {
        // Activities may contain custom controls — for now, treat click targets generically
        const targetRow = event.target.closest( '.smartwoo-linked-table-row' );
        if ( targetRow ) {
            return this._maybeNavigateRow( event, targetRow );
        }

        // If activities include a refresh button in future, handle it here
        const refreshBtn = event.target.closest( '.smartwoo-activities-refresh' );
        if ( refreshBtn ) {
            event.preventDefault();
            return this._fetchAndRenderSection( sectionEl, { action: 'refreshActivities' } );
        }
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

    /* -------------------------
     * Fetch + render helpers
     * ------------------------- */

    /**
     * Fetch server data for a given section and render it.
     *
     * Server contract (recommended): return either HTML string (rows/markup) OR JSON:
     * {
     *   html: '<tr>...</tr>',
     *   pagination: { prev_disabled: false, next_disabled: false, page: 2, total_pages: 5 },
     *   heading: 'All Subscriptions'
     * }
     *
     * @param  {HTMLElement} sectionEl
     * @param  {Object}      params
     * @return {Promise}
     */
    async _fetchAndRenderSection( sectionEl, params ) {
        const sectionKey = sectionEl.getAttribute( 'data-section' ) || '';
        this._showLoader( sectionEl );

        try {
            const payload = await this._fetchSectionData( sectionKey, params );

            // payload may be string (HTML) or object
            if ( typeof payload === 'string' ) {
                this._replaceSectionBodyHtml( sectionEl, payload );
            } else if ( payload && typeof payload === 'object' ) {
                if ( payload.html ) {
                    this._replaceSectionBodyHtml( sectionEl, payload.html );
                }

                if ( payload.pagination ) {
                    this._updateSectionPagination( sectionEl, payload.pagination );
                }

                if ( payload.heading ) {
                    this._updateSectionHeading( sectionEl, payload.heading );
                }
            }
        } catch ( err ) {
            // Minimal in-UI error reporting: insert a friendly row or use console
            console.error( 'SmartWoo dashboard fetch error:', err );
            this._displaySectionError( sectionEl, 'Could not load data. Please try again.' );
        } finally {
            this._hideLoader( sectionEl );
        }
    }

    /**
     * Performs the actual fetch to admin-ajax.php
     *
     * @param  {string} sectionKey
     * @param  {Object} params
     * @return {Promise<?Object|string>}
     */
    async _fetchSectionData( sectionKey, params ) {
        const url = this.serverConfig.admin_rest_endpoint;
        const body = new FormData();

        body.append( 'action', 'smartwoo_dashboard_fetch' );
        if ( this.serverConfig.nonce ) {
            body.append( '_ajax_nonce', this.serverConfig.nonce );
        }

        body.append( 'section', sectionKey );

        // Append the params object: filter/pagination etc.
        if ( params ) {
            // If params contains an object (pagination), stringify sub-values
            Object.keys( params ).forEach( ( key ) => {
                const val = params[ key ];
                if ( typeof val === 'object' ) {
                    body.append( key, JSON.stringify( val ) );
                } else {
                    body.append( key, String( val ) );
                }
            } );
        }

        const response = await fetch( url, {
            method: 'POST',
            credentials: 'same-origin',
            body: body,
        } );

        if ( ! response.ok ) {
            throw new Error( `HTTP error: ${ response.status }` );
        }

        const contentType = response.headers.get( 'content-type' ) || '';

        if ( contentType.includes( 'application/json' ) ) {
            return await response.json();
        }

        // Fallback to raw text (HTML) — server returns <tr>...</tr> or markup fragment
        return await response.text();
    }

    /* -------------------------
     * DOM update helpers
     * ------------------------- */

    _replaceSectionBodyHtml( sectionEl, html ) {
        const body = sectionEl.querySelector( '.smartwoo-table-content' ) || sectionEl.querySelector( '.sw-dashboard-activities-section' ) || sectionEl;
        if ( body ) {
            body.innerHTML = html;
        }
    }

    _updateSectionPagination( sectionEl, pagination ) {
        // Find pagination container inside section and update button disabled states & data-pagination
        const pagContainer = sectionEl.querySelector( '.sw-dashboard-pagination' );
        if ( ! pagContainer ) {
            return;
        }

        // Example pagination object expected: { prev_disabled: true, next_disabled: false, prev_number: 0, next_number: 2 }
        const prevBtn = pagContainer.querySelector( '.sw-pagination-button[data-pagination*="prev"]' );
        const nextBtn = pagContainer.querySelector( '.sw-pagination-button[data-pagination*="next"]' );

        if ( prevBtn && typeof pagination.prev_disabled !== 'undefined' ) {
            if ( pagination.prev_disabled ) {
                prevBtn.setAttribute( 'disabled', 'true' );
            } else {
                prevBtn.removeAttribute( 'disabled' );
            }
        }

        if ( nextBtn && typeof pagination.next_disabled !== 'undefined' ) {
            if ( pagination.next_disabled ) {
                nextBtn.setAttribute( 'disabled', 'true' );
            } else {
                nextBtn.removeAttribute( 'disabled' );
            }
        }

        // If server returns updated data-pagination numbers, set them
        if ( prevBtn && typeof pagination.prev_number !== 'undefined' ) {
            prevBtn.setAttribute( 'data-pagination', JSON.stringify( { name: 'prev', number: Number( pagination.prev_number ) } ) );
        }
        if ( nextBtn && typeof pagination.next_number !== 'undefined' ) {
            nextBtn.setAttribute( 'data-pagination', JSON.stringify( { name: 'next', number: Number( pagination.next_number ) } ) );
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
     * Row navigation helper
     * ------------------------- */

    _maybeNavigateRow( event, row ) {
        // If the click originated from an interactive control, do not navigate
        if ( event.target.closest( 'input, button, a, label, select' ) ) {
            return;
        }

        // Avatar images or action dots should not trigger row nav if they are clickable elements
        if ( event.target.classList && ( event.target.classList.contains( 'sw-table-avatar' ) || event.target.classList.contains( 'smartwoo-options-dots' ) ) ) {
            return;
        }

        const url = row.getAttribute( 'data-url' );
        if ( url ) {
            window.location.href = url;
        }
    }

    /* -------------------------
     * Loader helpers
     * ------------------------- */

    _showLoader( sectionEl ) {
        // Prefer an overlay loader for the section; fallback to global loader
        if ( sectionEl ) {
            sectionEl.setAttribute( 'aria-busy', 'true' );
        }
        if ( this.globalLoader ) {
            this.globalLoader.style.display = 'block';
        }
    }

    _hideLoader( sectionEl ) {
        if ( sectionEl ) {
            sectionEl.removeAttribute( 'aria-busy' );
        }
        if ( this.globalLoader ) {
            this.globalLoader.style.display = 'none';
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
