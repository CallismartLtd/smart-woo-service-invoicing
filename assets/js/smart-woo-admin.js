function originalDashboard() {
    const originalDashboard = `
      <div class="sw-dash-content">
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
        <div class="sw-skeleton sw-skeleton-text"></div>
      </div>
    `;
  
    return originalDashboard;
  }

document.addEventListener( 'DOMContentLoaded', ()=> {
    let contentDiv      = document.querySelector( '.sw-dash-content-container' );
    let skeletonContent = document.getElementsByClassName( 'sw-dash-content' );
    for( let i= 0; i < 8; i++) {
        contentDiv.append(skeletonContent[0].cloneNode(true))
    }
    
    smartwoo_get_total_services_count();
    smartwoo_get_pending_service_orders_count();
    smartwoo_get_all_active_count();
    
  
} );

/**
 * Fetch count for all services
 */
function smartwoo_get_total_services_count() {
    let  dashContents = document.querySelectorAll( '.sw-dash-content' );
    jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: 'total_services',
            security: smartwoo_admin_vars.security,

        },
        success: function( response ) {
            if (response.success) {
                smartwoo_clear_dash_content(0);
                let divTag  = document.createElement('div');
                let hTag    = document.createElement('h2');
                let spanTag = document.createElement('span');
                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data.total_services;
                spanTag.textContent = 'Total Services';
                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[0].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');


            } else {
                
            }
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
            
        }
    });
}

function smartwoo_get_all_active_count() {
    let  dashContents = document.querySelectorAll( '.sw-dash-content' );
    jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: 'total_active_services',
            security: smartwoo_admin_vars.security,

        },
        success: function( response ) {
            if (response.success) {
                smartwoo_clear_dash_content(2);
                let divTag  = document.createElement('div');
                let hTag    = document.createElement('h2');
                let spanTag = document.createElement('span');
                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data.total_active_services;
                spanTag.textContent = 'Active Services';
                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[2].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');

            } else {
                
            }
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
            
        }
    });
}

/**
 * Fetch count for all pending service orders
 */
function smartwoo_get_pending_service_orders_count() {
    let  dashContents = document.querySelectorAll( '.sw-dash-content' );
    jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: 'total_pending_services',
            security: smartwoo_admin_vars.security,

        },
        success: function( response ) {
            if (response.success) {
                smartwoo_clear_dash_content(1);
                let divTag  = document.createElement('div');
                let hTag    = document.createElement('h2');
                let spanTag = document.createElement('span');
                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data.total_pending_services;
                spanTag.textContent = 'Pending Service Orders';
                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[1].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');


            } else {
                
            }
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
            
        }
    });
}

/**
 * Fetch count for all pending service orders
 */
function smartwoo_get_due_services_count() {
    let  dashContents = document.querySelectorAll( '.sw-dash-content' );
    jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: 'total_pending_services',
            security: smartwoo_admin_vars.security,

        },
        success: function( response ) {
            if (response.success) {
                smartwoo_clear_dash_content(1);
                let divTag  = document.createElement('div');
                let hTag    = document.createElement('h2');
                let spanTag = document.createElement('span');
                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data.total_pending_services;
                spanTag.textContent = 'Pending Service Orders';
                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[1].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');


            } else {
                
            }
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
            
        }
    });
}

function smartwoo_clear_dash_content( index ) {
    let dashContents    = document.querySelectorAll( '.sw-dash-content' );
    dashContents[index].innerHTML = "";
}