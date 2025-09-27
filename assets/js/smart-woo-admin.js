/**
 * Dynamically renders the list of services in a `sw-table` on admin dashoard page.
 * 
 * @param {Array} headers Table header values.
 * @param {Array} bodyData Table body data.
 * @param {number} totalPages Total available pages
 * @param {number} currentPage The current page in the query.
 * @param {number} index Tracks the index of the div element we are working on.
 */
function renderTable(headers, bodyData, rowNames, totalPages, currentPage, index) {
    let selectedRowsState = {};
    let bodyContent = document.querySelector('.sw-admin-dash-body');
    let searchDiv = document.querySelector('.sw-search-container');
    
    // Clear existing table before rendering the new one
    smartwooRemoveTable();
    totalItems  = bodyData.length;
    
    // Add pagination controls
    addPaginationControls(searchDiv, totalPages, currentPage, totalItems, index);
    
    // Create table element
    let table = document.createElement('table');
    table.classList.add('sw-table', 'widefat', 'striped');

    // Create table header
    let thead = document.createElement('thead');
    let headerRow = document.createElement('tr');
    
    // Add a "Select All" checkbox in the header
    let checkboxHeader = document.createElement('th');
    let selectAllCheckbox = document.createElement('input');
    selectAllCheckbox.type = 'checkbox';

    selectAllCheckbox.addEventListener('change', function() {
        // Get all checkboxes in the body
        let checkboxes = tbody.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            // Update global state for each row
            selectedRowsState[checkbox.name] = this.checked;
        });

        // Trigger the action dialog with selected rows if any
        triggerActionDialog();
    });

    checkboxHeader.appendChild(selectAllCheckbox); // Append the "Select All" checkbox
    headerRow.appendChild(checkboxHeader); // Add it to the header row

    // Add headers for the other columns
    headers.forEach(headerText => {
        let th = document.createElement('th');
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create table body
    let tbody = document.createElement('tbody');

    // Check if there is no data
    if (bodyData.length === 0) {
        // Create a row with a "No data found" message
        let noDataRow = document.createElement('tr');
        let noDataCell = document.createElement('td');
        noDataCell.colSpan = headers.length + 1; // +1 for the checkbox column
        noDataCell.textContent = 'No service found.';
        noDataCell.classList.add('sw-not-found');
        noDataRow.appendChild(noDataCell);
        tbody.appendChild(noDataRow);
    } else {
        bodyData.forEach((rowData, rowIndex) => {
            let row = document.createElement('tr');

            // Add a checkbox cell for each row, using rowNames for the checkbox name
            let checkboxCell = document.createElement('td');
            let checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = rowNames[rowIndex]; // Set the name to the corresponding value from rowNames

            // Set the checkbox state based on the global state
            if (selectedRowsState[checkbox.name]) {
                checkbox.checked = true;
            }

            // Add event listener to each checkbox
            checkbox.addEventListener('change', function() {
                // Update the global state when a checkbox is checked/unchecked
                selectedRowsState[this.name] = this.checked;

                // If any checkbox is unchecked, deselect "Select All"
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                }

                // If all checkboxes are checked, automatically select "Select All"
                let allChecked = Array.from(tbody.querySelectorAll('input[type="checkbox"]')).every(cb => cb.checked);
                if (allChecked) {
                    selectAllCheckbox.checked = true;
                }

                // Trigger the action dialog with selected rows if any
                triggerActionDialog();
            });

            checkboxCell.appendChild(checkbox);
            row.appendChild(checkboxCell);

            // Populate the rest of the row with data
            rowData.forEach((cellData, cellIndex) => {
                let td = document.createElement('td');
                td.textContent = cellData;

                // Add event listener for the Service ID column (Service ID is at index 1)
                if (cellIndex === 1) {
                    td.classList.add('service-id-column');
                    td.style.cursor = 'pointer';
                    td.setAttribute( 'title', 'View service details')
                    
                    // Add click event listener to log the service ID
                    td.addEventListener('click', function() {
                        smartwoo_service_admin_view(cellData); // Log or redirect to the service view page
                    });
                }

                row.appendChild(td);
            });

            tbody.appendChild(row);
        });
    }

    table.appendChild(tbody);

    // Append the table to the body content
    table.style.display = 'none';
    bodyContent.appendChild(table);
    jQuery(table).fadeIn( 'slow' );

    // Add pagination controls
    addPaginationControls(bodyContent, totalPages, currentPage, totalItems, index);

    // Function to trigger the action dialog
    function triggerActionDialog() {
        let selectedRows = [];
        let checkboxes = tbody.querySelectorAll('input[type="checkbox"]:checked');
        checkboxes.forEach(checkbox => selectedRows.push(checkbox.name));

        if (selectedRows.length > 0) {
            smartwooShowActionDialog(selectedRows); // Call the action dialog with selected rows' names
        } else {
            let actionDiv   = document.querySelector('.sw-action-container');

            if(actionDiv) {
                actionDiv.remove();
            }
        }
    }
}

let addedClearBtn = false;

// Add pagination control to table.
function addPaginationControls( bodyContent, totalPages, currentPage, totalItems, index ) {
    let paginationDiv = document.createElement('div');
    paginationDiv.classList.add('sw-pagination-buttons');
    paginationDiv.style.float = "right";
    let itemCountText = document.createElement('p');
    itemCountText.textContent = `${totalItems} items`;
    paginationDiv.appendChild(itemCountText);

    // Previous Button
    if (currentPage > 1) {
        let prevPage = currentPage - 1;
        let prevLink = document.createElement('a');
        prevLink.classList.add('sw-pagination-button');
        
        let prevButton = document.createElement('button');
        prevButton.innerHTML = '<span class="dashicons dashicons-arrow-left-alt2"></span>';
        
        prevLink.appendChild(prevButton);
        prevLink.addEventListener('click', function (event) {
            event.preventDefault();
            fetchDashboardData(index, { paged: prevPage, limit: 25 });
        });

        paginationDiv.appendChild(prevLink);
    }

    // Current page text
    let currentPageText = document.createElement('p');
    currentPageText.textContent = `${currentPage} of ${totalPages}`;
    paginationDiv.appendChild(currentPageText);

    // Next Button
    if (currentPage < totalPages) {
        let nextPage = currentPage + 1;
        let nextLink = document.createElement('a');
        nextLink.classList.add('sw-pagination-button');

        let nextButton = document.createElement('button');
        nextButton.innerHTML = '<span class="dashicons dashicons-arrow-right-alt2"></span>';
        
        nextLink.appendChild(nextButton);
        nextLink.addEventListener('click', function (event) {
            event.preventDefault();
            fetchDashboardData(index, { paged: nextPage, limit: 25  });
        });

        paginationDiv.appendChild(nextLink);
    }

    bodyContent.appendChild(paginationDiv);
    if ( ! addedClearBtn ) {
        paginationDiv.style.float = "";
        paginationDiv.style.marginTop = "0px";
        let removeTableBtn = document.createElement( 'span' );
        removeTableBtn.classList.add( 'dashicons', 'dashicons-dismiss' );
        removeTableBtn.style.color = "#c48f00";
        removeTableBtn.style.marginRight = "20px";
        removeTableBtn.style.fontSize = "23px";
        removeTableBtn.style.cursor = "pointer";
        removeTableBtn.setAttribute( 'title', 'Restore dashboard' );
        bodyContent.appendChild( removeTableBtn );
        addedClearBtn = true;

        let removeClearBtn = () => {
            let dashboardBtn    = document.getElementById('dashboardBtn');
            addedClearBtn = false;
            removeTableBtn.remove();
            dashboardBtn.click();
        }

        document.addEventListener( 'dashboardtableRemoved', ()=>{
            removeTableBtn.remove();
            addedClearBtn = false;

        });
        removeTableBtn.addEventListener( 'click', removeClearBtn );
    }
}

/**
 * Redirect to Service view page at admin.
 */
function smartwoo_service_admin_view(serviceId) {
    let dashUrl = new URL(smartwoo_admin_vars.sw_admin_page);
    dashUrl.searchParams.set('tab', 'view-service');
    dashUrl.searchParams.set('service_id', serviceId);
    window.location.href = dashUrl.href;

}

// Function to remove the table
function smartwooRemoveTable() {
    let swTable = document.querySelector('.sw-table');
    let pagenaBtns = document.querySelectorAll('.sw-pagination-buttons');
    if (swTable) {
       swTable.remove();
    
        if (pagenaBtns){
    
            pagenaBtns.forEach((btns)=>{
                btns.remove();
            });
        
        }
        document.dispatchEvent( new CustomEvent( 'dashboardtableRemoved' ) );
    }
}

function smartwooPostBulkAction(action, values = []) {
    if ('delete' === action) {
        let confirmed = confirm('Warning: You are about to delete the selected service' + (values.length > 1 ? 's' : '') + ', along with all related invoices and assets. Click OK to confirm.');
        if ( ! confirmed ) {
            return;
        }        
    }
    showLoadingIndicator();
    jQuery.ajax({
        type: "POST",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: "smartwoo_dashboard_bulk_action",
            service_ids: values,
            real_action: action,
            security: smartwoo_admin_vars.security,
        },
        success: function(response) {
            if( response.success) {
                showNotification(response.data.message);
                setTimeout(()=>{
                    window.location.reload();
                }, 2000);
            } else {
                showNotification("Oops! " + response.data.message);
            }
            
        },
        error: function(xhr, status, error) {
            console.error('Bulk action failed:', error);
            showNotification("An error occured, please inspect console.")
        },
        complete: function() {
            let formerDiv   = document.querySelector('.sw-action-container');

            if(formerDiv) {
                formerDiv.remove();
            }
            hideLoadingIndicator();
        }
    });
}

// Show bulk action dialogue options.
function smartwooShowActionDialog(selectedRows) {
    let formerDiv   = document.querySelector('.sw-action-container');

    if(formerDiv) {
        formerDiv.remove();
    }

    const actionDiv = document.createElement('div');
    actionDiv.classList.add('sw-action-container');
    
    actionDiv.innerHTML = `
      <select id="sw-action-select" name="dash_bulk_action">
        <option selected>Choose Action</option>
        <option value="auto_calc">Auto Calculate</option>
        <option value="Active">Activate</option>
        <option value="Active (NR)">Disable Renewal</option>
        <option value="Suspended">Suspend Service</option>
        <option value="Cancelled">Cancel Service</option>
        <option value="Due for Renewal">Due for Renewal</option>
        <option value="Expired">Expired</option>
        <option value="delete">Delete</option>
      </select>
      <input type="hidden" name="service_ids" value="${selectedRows}"/>
    `;
    
    const tableDiv = document.querySelector('.sw-table');
    tableDiv.prepend(actionDiv);
    jQuery('.sw-action-container').fadeIn().css('display', 'flex');
    
    actionDiv.addEventListener('change', () => {
        const selectedAction = actionDiv.querySelector('select').value;
        let initBtn = document.querySelector('.sw-action-btn');
        if ( initBtn) {
            initBtn.remove();
        }
        let actionBtn = document.createElement('button');
        actionBtn.classList.add('sw-action-btn');
        actionBtn.textContent = "Apply Action";
        if ( 'Choose Action' !== selectedAction ) {
            actionDiv.append(actionBtn);
            jQuery(actionBtn).fadeIn();

        }
        if (actionBtn) {
            actionBtn.addEventListener('click', ()=>{
                smartwooPostBulkAction(selectedAction, selectedRows);

            });
        }

    });
    
}

// Helper function to make AJAX requests and update the DOM
let fetchIntervals = {}; // Store intervals to avoid multiple intervals for the same index

function fetchServiceCount(index, action, label) {
    let dashContents = document.querySelectorAll('.sw-dash-content');

    return jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: action,
            security: smartwoo_admin_vars.security,
        },
        success: function(response) {
            if (response.success) {
                smartwoo_clear_dash_content(index);

                let divTag = document.createElement('div');
                let hTag = document.createElement('h2');
                let spanTag = document.createElement('span');

                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data[action]; // Dynamically use the action key
                spanTag.textContent = label;

                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[index].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');
            } else {
                smartwooAddRetryBtn(index, action, label);
            }
        },
        error: function(error) {
            let message = 'Error fetching data: ';
            if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
                message += error.responseJSON.data.message;
            } else if (error.responseText) {
                message += error.responseText;
            } else {
                message += error;
            }
            console.error(message);
            smartwooAddRetryBtn(index, action, label);
        },
        complete: function() {
            // Clear any existing interval for this index before setting a new one
            if (fetchIntervals[index]) {
                clearInterval(fetchIntervals[index]);
            }

            // Perform auto data update every fifteen minutes.
            fetchIntervals[index] = setInterval(() => {
                fetchServiceCount(index, action, label);
            }, 900000); // 900,000 ms = 15 minutes
        }
    });
}


// Add retry button when count fetch fails.
function smartwooAddRetryBtn(index, action, label) {
    let dashContents = document.querySelectorAll('.sw-dash-content');
    smartwoo_clear_dash_content(index);

    // Add a retry button
    let divTag  = document.createElement('div');
    divTag.classList.add('sw-dash-count');
    let h3Tag   = document.createElement('h3');
    h3Tag.textContent = "Error Occurred";
    divTag.append(h3Tag);

    let retryBtn = document.createElement('button');
    retryBtn.classList.add('sw-red-button');
    retryBtn.textContent = "retry";

    // Assign an onclick attribute with the function and parameters
    retryBtn.setAttribute('onclick', `fetchServiceCount(${index}, '${action}', '${label}')`);

    divTag.append(retryBtn);
    dashContents[index].append(divTag);
    jQuery('.sw-dash-count').fadeIn().css('display', 'flex');
    retryBtn.addEventListener('click', ()=>{
        retryBtn.style.cursor = "progress";
    });
}


// Function to clear skeleton content
function smartwoo_clear_dash_content( index ) {
    let dashContents = document.querySelectorAll( '.sw-dash-content' );
    if (dashContents) {
        dashContents[index].innerHTML = "";
    }
   
}

function fetchDashboardData(index, queryVars = {}) {
    let dashContents = document.querySelector( '.sw-dash-content-container' );
    let spinner = smartWooAddSpinner( 'swloader', true );
    switch(index) {
        case 0:
            realAction = 'all_services_table';
            break;
        case 1:
            realAction = 'all_pending_services_table';
            break;
        case 2:
            realAction = 'all_active_services_table';
            break;
        case 3:
            realAction = 'all_active_nr_services_table';
            break;
        case 4:
            realAction = 'all_due_services_table';
            break;
        case 5:
            realAction = 'all_on_grace_services_table';
            break;
        case 6:
            realAction = 'all_expired_services_table';
            break;
        case 7:
            realAction = 'all_cancelled_services_table';
            break;
        case 8:
            realAction = 'all_suspended_services_table';
            break;
        default: 
            realAction = 'sw_search';
    }

    if ( 'all_pending_services_table' === realAction ) {
        window.location.href = smartwoo_admin_vars.admin_order_page;
        return;
    }

    // Default pagination vars if not provided
    let limit   = queryVars.limit || 10;
    let paged   = queryVars.paged || 1;
    let url     = new URL( smartwoo_admin_vars.ajax_url );
    url.searchParams.set( 'action', 'smartwoo_dashboard' );
    url.searchParams.set( 'security', smartwoo_admin_vars.security );
    url.searchParams.set( 'real_action', realAction );
    url.searchParams.set( 'limit', limit );
    url.searchParams.set( 'paged', paged );

    if ( 'sw_search' === realAction ) {
        url.searchParams.set( 'search_term', queryVars.search );
    }

    fetch( url )
    .then( response => {
        if ( response.ok ) {
            return response.json();
        }
    }).then( response =>{
        if (response.success) {
            let tableStructure = response.data.all_services_table;
            let tableHeaders = tableStructure.table_header;
            let tableBody = tableStructure.table_body;
            let rowIds = tableStructure.row_names;
            let totalPages = tableStructure.total_pages;
            let currentPage = tableStructure.current_page;

            // Pass the data to the table rendering function, with pagination info
            renderTable(tableHeaders, tableBody, rowIds, totalPages, currentPage, index);

        } 
    }).catch( error =>{
        var message  = 'Error fetching data: ';
        if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
            message += error.responseJSON.data.message;
        } else if (error.responseText) {
            message += error.responseText;
        } else {
            message += error;
        }
        console.error(message);
    }).finally( () =>{
        if (dashContents ) {
            dashContents.style.display = "none";
        }
        
        smartWooRemoveSpinner( spinner );
    });
}

// Delete an invoice
function smartwooDeleteInvoice(invoiceId) {
    let isConfirmed = confirm('Do you realy want to delete this invoice? This action cannot be reversed!');
	if (isConfirmed) {
		spinner = smartWooAddSpinner( 'swloader', true );

		jQuery.ajax(
			{
				type: 'POST',
				url: smartwoo_admin_vars.ajax_url,
				data: {
					action: 'delete_invoice',
					invoice_id: invoiceId,
					security: smartwoo_admin_vars.security
				},
				success: function ( response ) {
					if ( response.success ) {
						alert( response.data.message );
						window.location.href = smartwoo_admin_vars.admin_invoice_page;	
					} else {
						alert( response.data.message );
					}
					
				},

				error: function (error) {
					// Handle the error
					console.error( 'Error deleting invoice:', error );
				}, 
				complete: function() {
					smartWooRemoveSpinner( spinner );
				}
			}
		);
	}
}

/**
 * Handles pro button actions
 */
function smartwooProBntAction( action_name ) {
    let adDiv = document.querySelector('.sw-pro-sell-content');
    adDiv.innerHTML = '';
    
    // Create and append the check icon
    let checkIcon = document.createElement('span');
    checkIcon.className = 'dashicons dashicons-yes-alt';
    let respMessage = document.createElement('h2');
    respMessage.textContent = ( 'dismiss_fornow' === action_name ) ? 'Dismissed.' : 'Reminder has been set.';
    adDiv.append(checkIcon);
    adDiv.append(respMessage);
    setInterval(() => checkIcon.classList.toggle('loaded'), 500 );
    
    // Fade out and remove the adDiv
    setTimeout(() => {
        jQuery(adDiv.parentElement).fadeOut(() => adDiv.remove());
    }, 2000);
    let url = new URL( smartwoo_admin_vars.ajax_url );
    url.searchParams.set( 'action', 'smartwoo_pro_button_action' );
    url.searchParams.set( 'security', smartwoo_admin_vars.security );
    url.searchParams.set( 'real_action', action_name );
    fetch( url )
    .then( response =>{
        if ( ! response.ok ) {
            throw new Error( response.text() );
        }
        return response.json();
    }).then( data => {
        if ( ! data.success ) {
            window.location.reload();
        }
    }).catch( error =>{
        showNotification( 'Error occured' );
        window.location.reload();
    });
}

function smartwooDeleteProduct(productId) {
    let isConfirmed = confirm( 'Are you sure you want to permanently delete this product? This action cannot be reversed!' );
    if (isConfirmed) {
        spinner = smartWooAddSpinner( 'swloader', true );

        jQuery.ajax(
            {
                type: 'POST',
                url: smartwoo_admin_vars.ajax_url,
                data: {
                    action: 'smartwoo_delete_product',
                    product_id: productId,
                    security: smartwoo_admin_vars.security
                },
                success: function ( response ) {
                    
                    if ( response.success ) {
                        alert( response.data.message );
                        window.location.href = smartwoo_admin_vars.sw_product_page;
                    } else {
                        alert( response.data.message );
                    }
                    
                },

                error: function (error) {
                    // Handle the error
                    console.error( 'Error deleting product:', error );
                },
                complete: function() {
                    smartWooRemoveSpinner( spinner);
                }
            }
            
        );
    }
}

// Delete a service subscription.
function smartwooDeleteService(serviceId) {
    let isConfirmed = confirm( 'Are you sure you want to delete this service? All invoices and assets alocated to it will be lost forever.' );

    if (isConfirmed) {
        spinner = smartWooAddSpinner( 'sw-admin-spinner', true );

        // Perform an Ajax request to delete the invoice
        jQuery.ajax(
            {
                type: 'POST',
                url: smartwoo_admin_vars.ajax_url,
                data: {
                    action: 'smartwoo_delete_service',
                    service_id: serviceId,
                    security: smartwoo_admin_vars.security
                },
                success: function (response) {
                    if ( response.success) {

                        alert( response.data.message );
                        window.location.href = smartwoo_admin_vars.sw_admin_page;
                    } else {
                        alert( response.data.message );
                    }

                },

                error: function (error) {
                    // Handle the error
                    alert( 'Error deleting service:', error );
                },
                complete: function() {
                    smartWooRemoveSpinner( spinner );
                }
            }
        );
    }

}

/**
 * Fetch pro feature template
 */
function smartwoo_pro_ad(title, message) {
    let initDiv = document.querySelector('.sw-pro-div');
    if (initDiv) {
        jQuery( initDiv ).fadeOut( 'fast', () =>{
            initDiv.remove();
        });
    }

    let mainDiv         = document.querySelector('.smartwoo-settings-form') ?? document.querySelector( '#pro-target' );
    let proDiv          = document.createElement('div');
    proDiv.classList.add('sw-pro-div');
    let close           = document.createElement('span');
    close.classList.add('dashicons', 'dashicons-dismiss');
    close.setAttribute('title', 'close');
    close.style.position   = 'absolute';
    close.style.right   = '5px';
    close.style.top   = '2px';
    close.style.color   = 'red';
    close.style.cursor   = 'pointer';
    let h2              = document.createElement('h2');
    h2.textContent      = title;
    let bodyDiv         = document.createElement('div');
    bodyDiv.classList.add('sw-pro-body');
    bodyDiv.innerHTML   = message;
    let actionBtn    = document.createElement('span');
    actionBtn.classList.add('sw-pro-action-btn');
    actionBtn.textContent = 'Activate Pro Feature';

    proDiv.append(h2);
    proDiv.append(close);
    proDiv.append(bodyDiv);
    proDiv.append(actionBtn);
    mainDiv.prepend(proDiv);
    jQuery(proDiv).fadeIn('slow').css('display', 'flex');

    close.addEventListener('click', ()=>{
        jQuery( proDiv ).fadeOut( 'fast', () =>{
            proDiv.remove();
        });
    });

    actionBtn.addEventListener('click', ()=>{
        window.open(smartwoo_admin_vars.smartwoo_pro_page, '_blank');
    });
}

/**
 * Get guest data for `GUEST` invoice.
 * 
 * @param heading The Input form heading.
 */
async function smartwooPromptGuestInvoiceData(heading) {
    return new Promise( (resolve)=>{
        let data    = { 
            "first_name": "",
            "last_name": "",
            "billing_email": "",
            "billing_company": "",
            "billing_phone": "",
            "billing_address": "",
        };
        let form        = document.createElement('div');
        let formFields  = 
            `<span class="smartwoo-remove dashicons dashicons-no" title="Close"></span>
            <h2>${heading}</h2>
            <div class="sw-guest-name-row">
                <input type="text" name="first_name" placeholder="First Name" id="first_name"/>
                <input type="text" name="last_name" placeholder="Last Name" id="last_name"/>
            </div>
            <div class="sw-guest-other-row">
                <input type="text" name="billing_email" placeholder="Billing Email" id="billing_email"/>
                <input type="text" name="billing_company" placeholder="Billing Company" id="billing_company"/>
                <input type="text" name="billing_phone" placeholder="Billing Phone" id="billing_phone">
                <input type="text" name="billing_address" placeholder="Full address" id="billing_address"/>
                <button class="sw-blue-button" style="width: 80%; margin: 10px;">Add Guest</button>

            </div>
        `;
        form.classList.add( 'sw-guest-invoice-container' );
      
        form.innerHTML = formFields;
        let mainDiv = document.querySelector('#smartwooInvoiceForm').parentElement;
        let mainForm = document.querySelector('#smartwooInvoiceForm');
        mainDiv.insertBefore(form, mainForm);
        let removebtn = form.querySelector('.smartwoo-remove');
        removebtn.addEventListener('click', ()=>{
            form.remove();
            resolve(false);

        });
        
        let submitBtn = form.querySelector('.sw-blue-button');

        submitBtn.addEventListener('click', (event) => {
            event.preventDefault();
            data.first_name         = form.querySelector('input[name="first_name"]').value;
            data.last_name          = form.querySelector('input[name="last_name"]').value;
            data.billing_address    = form.querySelector('input[name="billing_address"]').value;
            data.billing_phone      = form.querySelector('input[name="billing_phone"]').value;
            data.billing_company    = form.querySelector('input[name="billing_company"]').value;
            data.billing_email      = form.querySelector('input[name="billing_email"]').value;

            resolve(data);
            form.remove();
        });

    })
}

/**
 * Post the Smart Woo Table Bulk Action.
 * 
 * @param {Object} actions The action to perform and the related hook name.
 * @param {Array} values The values to perform the action on.
 */
function smartwooPostswTableBulkAction( actions = {hook_name: '', value: ''}, values = [] ) {
    if ( 'delete' === actions.value ) {
        let confirmed = confirm( `You are about to delete the selected item${ ( values.length > 1 ? 's' : '' ) }! click ok to confirm.` );
        if ( ! confirmed ) {
            return;
        }
    }
    let loader = smartWooAddSpinner( 'swloader', true );
    url = new URL( smartwoo_admin_vars.ajax_url );
    url.searchParams.append( 'action', 'smartwoo_table_bulk_action' );
    let body = new FormData();
    body.append( 'payload', values );
    body.append( 'security', smartwoo_admin_vars.security );
    body.append( 'real_action', actions.hook_name );
    body.append( 'selected_action', actions.value );

    fetch( url, {
        method: 'POST',
        body: body
    }).then( response => {
        if ( ! response.ok ) {
            showNotification( `An error occured: [${response.statusText}]`, 6000 );
            throw new Error( 'Network response was not ok' );
        }
        return response.json()
    })
    .then( data => {
        if ( data.success ) {
            showNotification( data.data.message, 3000 );
            setTimeout(()=>{
                window.location.reload();
            }, 3000);
        } else {
            showNotification( data.data.message, 6000 );
        }
    }).catch( error => {
        console.error( 'Error:', error );
    }).finally( ()=>{
        smartWooRemoveSpinner( loader );
    });

}
/**
 * Smart Woo Utility function to add a bulk action to the sw-table.
 * @param {Object} params The parameters for the bulk action.
 * @param {Array} params.options The options for the bulk action.
 * @param {Array} params.selectedRows The selected rows for the bulk action.
 * @param {String} params.hookName The hook name that will be used to process action submission.
 * @param {HTMLTableElement} tableEl The table element to which the bulk action will be added.
 * @returns void
 */
function smartwooBulkActionForTable( params = { options: [], selectedRows: [], hookName }, tableEl ) {
    let prevDiv = document.querySelector('.sw-action-container');
    let swTable = ( tableEl instanceof HTMLElement ) ? tableEl : document.querySelector('.sw-table');
    
    // Remove previouse divs
    if( prevDiv ) {
        prevDiv.remove();
    }

    const actionDiv = document.createElement('div');
    actionDiv.classList.add('sw-action-container');
    let selectElement  = document.createElement('select');
    selectElement.id   = 'sw-action-select';
    selectElement.name = 'dash_bulk_action';

    selectElement.innerHTML = '<option value="">Choose Action</option>';
    params.options.forEach( ( option )=>{
        optElement          = document.createElement( 'option' );
        optElement.value    = option.value;
        optElement.text     = option.text;
        selectElement.appendChild( optElement );
    });

    let values = params.selectedRows;
    let applyBtn = document.createElement('button');
    applyBtn.classList.add('sw-action-btn');
    applyBtn.textContent = 'Apply Action';
    actionDiv.append( selectElement );
    swTable.prepend( actionDiv );
    jQuery( actionDiv ).fadeIn().css('display', 'flex');

    selectElement.addEventListener( 'change', () => {
        if ( ! selectElement.value ) {
            applyBtn.remove();
        }else {
            actionDiv.append( applyBtn );
            jQuery( applyBtn ).fadeIn();
        }
    });

    applyBtn.addEventListener( 'click', ()=>{
        smartwooPostswTableBulkAction( { hook_name: params.hookName, value: selectElement.value }, values );
    });

    return actionDiv;
}

/**
 * Eventlistener callback to open the Wordpress media library and set the id and url of the
 * selected media to the defined elements in this function.
 * @param {Event} event 
 */
function smartwooopenWPMediaOnClick( event ) {
    event.preventDefault();
    // We need the parent element and the input fields with these attributes.
    let perentDiv       = event.target.parentElement;
    let mediaIdInput    = perentDiv.querySelector( '[smartwoo-media-id]' );
    let mediaUrlInput   = perentDiv.querySelector( '[smartwoo-media-url]' );

    let wpMedia;

    if ( wpMedia ) {
        wpMedia.open();
        return;
    }
    // Create the media frame
    wpMedia =  wp.media({
        title: 'Select File',
        button: {
            text: 'Insert'
        },
        multiple: false
    });

    wpMedia.on( 'select', () => {
        let file    = wpMedia.state().get( 'selection' ).first().toJSON();

        if ( mediaIdInput ) {
            mediaIdInput.value  = '';
            mediaIdInput.value  = file.id;

        }

        if ( mediaUrlInput ) {
            mediaUrlInput.value = '';
            mediaUrlInput.value = file.url;
        }

    });

    wpMedia.open();
}

/**
 * Date input field handler
 */
function smartwooDatesInputsHandler() {
    
    const formatDateTime = (date) => {
        var year   = date.getFullYear();
        var month  = String( date.getMonth() + 1 ).padStart( 2, '0' );
        var day    = String( date.getDate() ).padStart( 2, '0' );
    
        return `${year}-${month}-${day}`;
    };

    // Get input fields
    let startDateField      = document.querySelector( '#sw_start_date' );
    let nextPayDateField    = document.querySelector( '#sw_next_payment_date' );
    let endDateField        = document.querySelector( '#sw_end_date' );
    let billingCycleField   = document.querySelector( '#sw_billing_cycle' );

    if ( billingCycleField && startDateField && nextPayDateField && endDateField ) {
        billingCycleField.addEventListener( 'change', () => {
            if ( ! startDateField.value.length ) {
                startDateField.reportValidity();
                billingCycleField.value = '';
                return;
            } else if ( !billingCycleField.value ) {
                return;
            }

            let startDate = new Date( startDateField.value );
            if ( isNaN( startDate.getTime() ) ) {
                showNotification( 'Invalid start date', 3000 );
                return;
            }

            let newEndDate = new Date( startDate );
            let nextPayDate;

            switch( billingCycleField.value ) {
                case 'Weekly':
                    newEndDate.setDate(newEndDate.getDate() + 7);
                    break;
                case 'Monthly':
                    newEndDate.setMonth(newEndDate.getMonth() + 1);
                    break;
                case 'Quarterly':
                    newEndDate.setMonth(newEndDate.getMonth() + 3);
                    break;
                case 'Semiannually':
                    newEndDate.setMonth(newEndDate.getMonth() + 6);
                    break;
                case 'Yearly':
                    newEndDate.setFullYear(newEndDate.getFullYear() + 1);
                    break;
                default:
                    showNotification( 'Invalid billing cycle', 3000 );
                    return;
            }
            // Get the interval setting from localized script
            let globalNextPayInterval = smartwoo_admin_vars.global_nextpay_date || { operator: '-', number: 7, unit: 'days' };

            // Extract values safely
            let operator = globalNextPayInterval.operator || '-';
            let num = parseInt(globalNextPayInterval.number) || 7;
            let unit = globalNextPayInterval.unit || "days";

            nextPayDate = new Date(newEndDate);

            switch (unit) {
                case "days":
                    nextPayDate.setDate(nextPayDate.getDate() + (operator === '-' ? -num : num));
                    break;
                case "weeks":
                    nextPayDate.setDate(nextPayDate.getDate() + (operator === '-' ? -num * 7 : num * 7));
                    break;
                case "months":
                    nextPayDate.setMonth(nextPayDate.getMonth() + (operator === '-' ? -num : num));
                    break;
                case "years":
                    nextPayDate.setFullYear(nextPayDate.getFullYear() + (operator === '-' ? -num : num));
                    break;
                default:
                    console.error("Unsupported time unit:", unit);
            }

            // Autofill input fields
            endDateField.value      = formatDateTime(newEndDate);
            nextPayDateField.value  = formatDateTime(nextPayDate);

            endDateField.dispatchEvent( new Event('input', { bubbles: true }))
            nextPayDateField.dispatchEvent( new Event('input', { bubbles: true }))
        });
    }

    // Initialize jQuery Datepicker.
    let dateFields = document.querySelectorAll( '#sw_start_date, #sw_next_payment_date, #sw_end_date, #date_on_sale_from, #date_on_sale_to, #invoice_due_date, #smartwoo_update_creation_date' );
    if (dateFields.length) {
        dateFields.forEach(input => {
            let options = {
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                closeText: 'Done',
                currentText: 'Today',
                nextText: 'Next',
                prevText: 'Previous',
                onSelect: function () {
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            };
            
            if ( 'Invoices' === smartwoo_admin_vars.currentScreen ) {
                jQuery( input ).on( 'focus', function () {
                    requestAnimationFrame( () => {
                        const $input = jQuery( this );
                        const offset = $input.offset();
                        const height = $input.outerHeight();

                        jQuery( '#ui-datepicker-div' ).css({
                            'top': ( offset.top + height ) + 'px',
                            'left': offset.left + 'px',
                            'z-index': 9999
                        });
                    });
                });
            }


            if (input.getAttribute( 'smartwoo-datetime-picker' )) {
                jQuery(input).datetimepicker({
                    ...options,
                    timeFormat: 'HH:mm:ss',
                });
            } else {
                jQuery(input).datepicker(options);
            }
        });
    }
}

/**
 * Initialize invoice item calculator based on selected SmartWoo product.
 *
 * @param {Element} smartwooProductDropdown The dropdown <select> element.
 */
function smartwooInvoiceEditorItemCalculator( smartwooProductDropdown ) {
	const productsJsonElement = document.getElementById( 'product_dropdown_json' );
	const editorItemsBody     = document.querySelector( '#swInvoiceItemsBody' );
	const subTotalInput       = document.querySelector( 'input#subtotal-value' );
	const grandTotalInput     = document.querySelector( 'input#grandTotal' );
    let proAddItemBtn         = document.querySelector( '#smartwoo-no-pro-add-item-btn' );

	if ( ! productsJsonElement ) {
		console.warn( 'Product JSON data element not found.' );
		return;
	}

	const json     = productsJsonElement.getAttribute( 'data-json' );
	const products = JSON.parse( json );
	let removedItems = 0;
	let hasItem = false;

	/**
	 * Calculate totals for each row and update subtotal/grand total.
	 */
	const calculateTotals = () => {
		let rows      = editorItemsBody.querySelectorAll( 'tr' );
		let subTotal  = 0;

		rows.forEach( row => {
			const qtyInput  = row.querySelector( 'input.sw-invoice-editor-quantity-input' );
			const priceInput = row.querySelector( 'input.sw-invoice-editor-unit-price-input' );
			const lineTotalCell = row.querySelector( '.sw-invoice-editor-line-total-input' );

			if ( qtyInput && priceInput && lineTotalCell ) {
				let quantity = parseFloat( qtyInput.value ) || 0;
				let unitPrice = parseFloat( priceInput.value ) || 0;
				let lineTotal = quantity * unitPrice;

				lineTotalCell.textContent = lineTotal.toFixed(2);
				subTotal += lineTotal;
			}
		});

		if ( subTotalInput ) {
			subTotalInput.value = subTotal.toFixed(2);
		}
		if ( grandTotalInput ) {
			grandTotalInput.value = subTotal.toFixed(2);
		}
	};

	/**
	 * Clear all invoice rows.
	 */
	const clearFormerRows = () => {
		editorItemsBody.innerHTML = '';
		hasItem = false;
	};

	/**
	 * Remove a row and update state.
	 */
	const removeClickedRow = ( e ) => {
		e.preventDefault();

		e.target.removeEventListener( 'click', removeClickedRow );
		jQuery( e.target.closest( 'tr' ) ).fadeOut( 'fast', () => {
			e.target.closest( 'tr' ).remove();
			removedItems++;

			if ( editorItemsBody.querySelectorAll( 'tr' ).length === 0 ) {
				smartwooProductDropdown.value = '';
				editorItemsBody.innerHTML = `<tr><td colspan="4" class="sw-not-found">No items</td></tr>`;
				hasItem = false;
			}

			calculateTotals();
		});
	};

	/**
	 * Add selected product and optional signup fee.
	 */
	const addItems = ( e ) => {
		clearFormerRows();

		const selectedId = e.target.value;

		if ( products[ selectedId ] ) {
			const product = products[ selectedId ];

			const itemTemplate = `
			<tr>
				<td><input type="text" class="sw-invoice-editor-item-name-input" value="${product.name}" id="${selectedId}" autocomplete="off"></td>
				<td><input type="number" class="sw-invoice-editor-quantity-input" value="1" step="1" min="1" disabled></td>
				<td><input type="number" class="sw-invoice-editor-unit-price-input" value="${product.price}" step="0.01" disabled></td>
				<td class="sw-invoice-editor-line-total-input">${parseFloat(product.price).toFixed(2)}</td>
				<td><span class="dashicons dashicons-trash sw-remove" title="Remove item" style="cursor: pointer; color: red;"></span></td>
			</tr>
			${product.sign_up_fee ? `
			<tr>
				<td><input type="text" class="sw-invoice-editor-item-name-input" value="Sign-up Fee" id="${String(Math.random()).replace('0.', '')}" autocomplete="off"></td>
				<td><input type="number" class="sw-invoice-editor-quantity-input" value="1" step="1" min="1" disabled></td>
				<td><input type="number" name="fee" class="sw-invoice-editor-unit-price-input" value="${product.sign_up_fee}" step="0.01"></td>
				<td class="sw-invoice-editor-line-total-input">${parseFloat(product.sign_up_fee).toFixed(2)}</td>
				<td><span class="dashicons dashicons-trash sw-remove" title="Remove item" style="cursor: pointer; color: red;"></span></td>
			</tr>` : '' }
			`;

			editorItemsBody.innerHTML = itemTemplate;

			editorItemsBody.querySelectorAll( '.sw-remove' ).forEach( btn => {
				btn.addEventListener( 'click', removeClickedRow );
			});

			editorItemsBody.querySelectorAll( 'input.sw-invoice-editor-quantity-input, input.sw-invoice-editor-unit-price-input' ).forEach( input => {
				input.addEventListener( 'input', calculateTotals );
			});

			hasItem = true;
			calculateTotals();
		}
	};

    if ( proAddItemBtn ) {
        proAddItemBtn.addEventListener( 'click', event =>{
            event.preventDefault();
            smartwoo_pro_ad( 'Add Custom Items', 'Add unlimited items to your invoice, track payment logs, make quotes with Smart Woo Pro' );
        });
    }
    
    editorItemsBody.querySelectorAll( 'input.sw-invoice-editor-quantity-input, input.sw-invoice-editor-unit-price-input' ).forEach( input => {
        input.addEventListener( 'input', calculateTotals );
    });

    editorItemsBody.querySelectorAll( '.sw-remove' ).forEach( btn => {
        btn.addEventListener( 'click', removeClickedRow );
    });
    calculateTotals();
	smartwooProductDropdown.addEventListener( 'change', addItems );
}


document.addEventListener('DOMContentLoaded', () => {
    smartwooDatesInputsHandler();

    let contentDiv              = document.querySelector('.sw-dash-content-container');
    let skeletonContent         = document.querySelectorAll('.sw-dash-content');
    let ordersBtn               = document.getElementById('dashOrderBtn');
    let addNewBtn               = document.getElementById('dashAddNew');
    let invoicesBtn             = document.getElementById('dashInvoicesBtn');
    let productsBtn             = document.getElementById('dashProductBtn');
    let settingsBtn             = document.getElementById('dashSettingsBtn');
    let proBtns                 = document.querySelectorAll('.sw-upgrade-to-pro');
    let searchField             = document.getElementById('sw_service_search');
    let searchbtn               = document.getElementById('swSearchBtn');
    const notificationTooltip   = document.getElementById('search-notification');
    let menuButton              = document.querySelector('.sw-admin-menu-icon');
    let deleteInvoiceBtns       = document.querySelectorAll('.delete-invoice-button');
    let deleteProductIds        = document.querySelectorAll('.sw-delete-product' );
    let deleteServiceBtn        = document.querySelector('.delete-service-button');
    let adminDashHeader         = document.querySelector('.sw-admin-dash-header');
    let editMailBtns            = document.querySelectorAll('.sw-edit-mail-nopro');
    let swCheckBoxes            = document.querySelectorAll('.sw-checkboxes');
    let swHideBtn               = document.getElementById('sw-hide');
    let noSbmtBtn               = document.querySelectorAll('.smartwoo-prevent-default' );
    let proRemindLaterBtn       = document.querySelector('#smartwoo-pro-remind-later');
    let proDismissFornow        = document.querySelector('#smartwoo-pro-dismiss-fornow');
    let userDataDropDown        = document.querySelector( '#user_data' );
    let theInvoiceAdminForm     = document.querySelector( '#smartwooInvoiceForm' );
    let invoicePageToggle       = document.querySelectorAll( '.sw-toggle-btn' );
    let invoiceActionBtns       = document.querySelectorAll( '.smartwoo-admin-invoice-action-div button' );
    let invoiceLinkActions      = document.querySelector( '.smartwoo-admin-invoice-action-div' );
    let invoiceLinksToggle      = document.querySelector( '.smartwoo-admin-invoice-actions' );
    let swTable                 = document.querySelector('.sw-table');
    let gracePeriodSelect       = document.querySelector( '#grace_period' );
    let addProductImageBtn      = document.querySelector( '#upload_sw_product_image' );
    let uploadProductImages     = document.querySelector( '#add-product-galleryBtn' );
    let productdataTabs         = document.querySelector( '.sw-product-data-tabs-menu' );
    let theProductForm          = document.querySelector( '#sw-product-form' );
    let isDownloadableCheck     = document.querySelector( '#is-smartwoo-downloadable' );
    let removeBtn               = document.querySelectorAll( '.swremove-field' );
    let adminViewServiceDivs    = document.querySelectorAll( '.sw-view-details-service-product, .admin-view-service-invoices-items, .sw-admin-subinfo, .sw-admin-client-billing-info-tab, .sw-admin-client-info-essentials, .sw-admin-client-info-pro-data, .sw-admin-client-service-invoice-pro-sell' );
    let serviceFormUserDropdown = document.querySelector( '#smartwooServiceUserDropdown' );
    const serviceForm           = document.querySelector( '#smartwooServiceForm' );
    let resetFastCheckoutBtn    = document.getElementById( 'resetFastCheckoutOptions' );
    let adminAutoRenewBtn       = document.querySelector( '#auto-renew-btn' );
    let smartwooProductDropdown = document.querySelector( '#service_products' );
    let smartwooKnowledgeBase   = document.querySelector( '.smartwoo-admin-knowledgebase' );
    let proDiv                  = document.querySelector('.sw-dash-pro-sell-bg');
    let allLinkedTableRow       = document.querySelectorAll( '.smartwoo-linked-table-row' );
    
    if (proDiv) {
        setTimeout( () =>{
            jQuery(proDiv).fadeIn();
        }, 5000 );
    }
    
    if ( allLinkedTableRow.length ) {
        const boundTables = new Set();

        allLinkedTableRow.forEach( element => {
            const mainTable = element.closest( 'table' );

            if ( ! boundTables.has( mainTable ) ) {
                mainTable.addEventListener( 'click', ( e ) => {
                    // Ignore clicks on inputs/options
                    if ( e.target.closest( '.smartwoo-options-dots, input, select' ) ) return;

                    // Ignore clicks when user is selecting text
                    if ( window.getSelection().toString().length > 0 ) return;

                    const mainRow = e.target.closest( 'tr' );
                    const url     = mainRow?.dataset?.url;

                    if ( url ) {
                        if ( e.ctrlKey || e.metaKey ) {
                            // Ctrl+Click or Cmd+Click → new tab
                            window.open( url, '_blank' );
                        } else {
                            // Normal click → same tab
                            window.location.href = url;
                        }
                    }
                });

                boundTables.add( mainTable );
            }
        });
    }
    
    /**
     * The assets is downloadable checkbox.
     */
    if ( isDownloadableCheck ) {
        let addProductDownloadsfieldsBtn = document.querySelector( '#add-field' );
        isDownloadableCheck.addEventListener( 'change', (e)=>{
            e.preventDefault();
            if ( e.target.checked ) {
                jQuery('.sw-assets-div').fadeIn().css('display', 'flex');
                jQuery('.sw-product-download-field-container, .sw-service-assets-downloads-container, .sw-service-additional-assets-container' ).fadeIn();
                jQuery('.sw-product-download-fields').fadeIn();
                jQuery('#add-field').fadeIn();
                jQuery( '.sw-no-download-text' ).hide()
                
            } else {
                jQuery('.sw-assets-div').fadeOut();
                jQuery('.sw-product-download-field-container, .sw-service-assets-downloads-container, .sw-service-additional-assets-container' ).fadeOut();
                jQuery('.sw-product-download-fields').fadeOut();
                jQuery('#add-field').fadeOut();
                jQuery( '.sw-no-download-text' ).fadeIn( 1000 )

            }
        });

        let uploadBtns = document.querySelectorAll( '.smartwooOpenWpMedia' );
        uploadBtns.forEach( btn =>{
            btn.addEventListener( 'click', smartwooopenWPMediaOnClick );
        } );

        addProductDownloadsfieldsBtn.addEventListener( 'click', e =>{
            e.preventDefault();
            let parentContainer     = document.querySelector( '.sw-product-download-field-container' );
            parentContainer         = parentContainer ? parentContainer : document.querySelector( '.sw-service-assets-downloads-container' );
            let newDownloadsField   = document.createElement( 'div' );
            let removeBtn           = document.createElement( 'span' );
            newDownloadsField.classList.add( 'sw-product-download-fields' );
            newDownloadsField.innerHTML = `<input type="text" class="sw-filename" name="sw_downloadable_file_names[]" placeholder="File Name"/>
                <input type="url" class="fileUrl" name="sw_downloadable_file_urls[]" smartwoo-media-url placeholder="File URL" />
                <button class="smartwooOpenWpMedia button">Choose file</button>
            `
            removeBtn.classList.add( 'dashicons', 'dashicons-dismiss' );
            removeBtn.style.color   = 'red';
            removeBtn.style.cursor   = 'pointer';
            removeBtn.setAttribute( 'title', 'remove' );
            newDownloadsField.appendChild( removeBtn );
            parentContainer.insertBefore( newDownloadsField, addProductDownloadsfieldsBtn );

            removeBtn.addEventListener( 'click', e =>{
                e.preventDefault();
                jQuery( newDownloadsField ).fadeOut();
                setTimeout( ()=>{ newDownloadsField.remove(); }, 1000 );

            });

            let uploadBtn = newDownloadsField.querySelector( '.smartwooOpenWpMedia' );
            uploadBtn.addEventListener( 'click', smartwooopenWPMediaOnClick );


        });
    }

    if ( contentDiv ) {
        // Clone the skeleton loader for each statistic
        for (let i = 0; i < 8; i++) {
            contentDiv.append(skeletonContent[0].cloneNode(true));
        }

        // Create an array of promises
        const dashBoardLoad = [
            fetchServiceCount(0, 'total_services', 'All Services'),
            fetchServiceCount(1, 'total_pending_services', 'Pending Service Orders'),
            fetchServiceCount(2, 'total_active_services', 'Active Services'),
            fetchServiceCount(3, 'total_active_nr_services', 'Active No Renewal'),
            fetchServiceCount(4, 'total_due_services', 'Due for Renewal'),
            fetchServiceCount(5, 'total_on_grace_services', 'Grace Period'),
            fetchServiceCount(6, 'total_expired_services', 'Expired Services'),
            fetchServiceCount(7, 'total_cancelled_services', 'Cancelled Services'),
            fetchServiceCount(8, 'total_suspended_services', 'Suspended Services')
        ];

        // Using Promise.all to wait for all fetchServiceCount promises to resolve.
        Promise.allSettled(dashBoardLoad).finally(() => {
            document.dispatchEvent(new CustomEvent('SmartWooDashboardLoaded'));
        });
    }

    if (addNewBtn) {
        addNewBtn.addEventListener('click', ()=>{
            window.location.href = smartwoo_admin_vars.new_service_page;
        } );
    }

    if (ordersBtn) {
        ordersBtn.addEventListener('click', ()=>{
            window.location.href = smartwoo_admin_vars.admin_order_page;
        });
    }

    if (invoicesBtn) {
        invoicesBtn.addEventListener('click', ()=>{
            window.location.href = smartwoo_admin_vars.admin_invoice_page;
        });
    }

    if (productsBtn) {
        productsBtn.addEventListener('click', ()=>{
            window.location.href = smartwoo_admin_vars.sw_product_page;
        });
    }

    if (settingsBtn) {
        settingsBtn.addEventListener('click', ()=>{
            window.location.href = smartwoo_admin_vars.sw_options_page;
        });
    }

    if (proBtns) {
        proBtns.forEach((proBtn)=>{
            proBtn.addEventListener('click', ()=>{
                window.open(smartwoo_admin_vars.smartwoo_pro_page, '_blank');
            });
        })

    }

    if (searchField && searchbtn && notificationTooltip) {
        searchField.addEventListener('input', ()=>{
            const searchValue = searchField.value.trim();
            if (searchValue.length == 0) {
                searchbtn.style.cursor = "not-allowed";
            } else {
                searchbtn.style.cursor = "pointer";
                notificationTooltip.style.display = 'none';
            }
        })
        
        searchbtn.addEventListener('click', () => {
            const searchValue = searchField.value.trim();

            if (searchValue.length > 0) {
                fetchDashboardData('sw_search', {search: searchValue});
                
                notificationTooltip.style.display = 'none';
                } else {
                notificationTooltip.textContent = 'Search field cannot be empty.';
                notificationTooltip.style.display = 'block';
                
            }

            notificationTooltip.addEventListener('click', ()=>{
                notificationTooltip.style.display = 'none'; // Hide the tooltip
            } );
        });
    }

    if (menuButton) {
        let navDiv = document.querySelector('.sw-admin-dash-nav');
        menuButton.addEventListener('click', ()=>{
            navDiv.classList.toggle( 'show' );
        });
    }

    if (deleteInvoiceBtns && deleteInvoiceBtns.length !== 0) {
        deleteInvoiceBtns.forEach((deleteInvoiceBtn)=>{
            deleteInvoiceBtn.addEventListener('click', ()=>{
                smartwooDeleteInvoice(deleteInvoiceBtn.getAttribute( 'data-invoice-id' ) );
            });
        });

    }

    if (deleteProductIds && deleteProductIds.length !== 0) {
        deleteProductIds.forEach((deleteProductId)=>{
            let productId = deleteProductId.getAttribute('data-product-id');
 
            deleteProductId.addEventListener('click', ()=>{
                smartwooDeleteProduct(productId);
            });
        });

    }

    if ( deleteServiceBtn ) {
        let siblings = deleteServiceBtn.parentElement.querySelectorAll( 'a button' );
        let serviceId = deleteServiceBtn.getAttribute( 'service-id' );
        siblings.forEach((Btn)=>{
            Btn.classList.add( 'sw-icon-button-admin' );

        });
        deleteServiceBtn.classList.add( 'sw-icon-button-admin' );
        deleteServiceBtn.addEventListener( 'click', ()=>{
            smartwooDeleteService( serviceId );
        });

    }

    if (adminDashHeader ) {
        document.addEventListener('scroll', ()=>{
            let scrollUp = window.scrollY > 0;
            if( scrollUp ) {
                adminDashHeader.classList.add( 'is-scrolled' );
            } else {
                adminDashHeader.classList.remove( 'is-scrolled' );
            }

            if ( window.matchMedia( `( min-width: 19px) and (max-width: 600px )`).matches ) {
                let scrollUp = window.scrollY > 20;
                if( scrollUp ) {
                    adminDashHeader.style.top = "0";
                } else {
                    adminDashHeader.style.top = "35px";
                }
            }
        });
    }

    if (editMailBtns) {
        editMailBtns.forEach((editMailBtn)=>{
            editMailBtn.addEventListener('click', ()=>{
                smartwoo_pro_ad('Email Template Edit', 'Email template editing is exclusively available in Smart Woo Pro');
            });
        });

    }

    if ( adminAutoRenewBtn ) {
        adminAutoRenewBtn.addEventListener( 'click', () =>{
            smartwoo_pro_ad( 'Auto Renew Service', 'Skip the manual work. With Smart Woo Pro, admins can instantly trigger a full subscription renewal — update invoices, mark services renewed, and send client emails — all in one click. Perfect for offline payments or fixing failed renewals.' );
        })
    }

    if (swCheckBoxes) {
        swCheckBoxes.forEach((checkbox)=>{
            checkbox.addEventListener('mouseover', ()=>{
                if(!checkbox.checked) {
                    checkbox.setAttribute('title', 'enable');
                } else {
                    checkbox.setAttribute('title', 'disable');
                }
            });
        });
    }

    if (swHideBtn) {
        swHideBtn.style.cursor = 'pointer';
        swHideBtn.addEventListener('click', (e)=>{
            e.preventDefault();
            jQuery(swHideBtn.parentElement).fadeOut();
        });
    }

    if (noSbmtBtn) {
        noSbmtBtn.forEach((btn)=>{
            let newWindow = null;
            btn.addEventListener('click', (e)=>{
                e.preventDefault();
                newWindow = window.open('', '_blank');
                newWindow.location.href = btn.getAttribute( 'href' );
                btn.disabled = true;
                btn.setAttribute( 'title', "Tab is open." );
                let enableBtnFnc = ()=>{
                    if (newWindow && newWindow.closed) {
                        btn.disabled = false;
                        btn.removeAttribute( 'title' );

                        btn.style.cursor = 'pointer';
                        document.removeEventListener('scroll', enableBtnFnc);

                    }
                }
                document.addEventListener('scroll', enableBtnFnc);

                if(btn.disabled) {
                    btn.style.cursor = 'not-allowed';
                }
            });
            
        });
    }

    if(proRemindLaterBtn) {
        proRemindLaterBtn.addEventListener('click', ()=>{
            smartwooProBntAction('remind_later');
        });
    }

    if(proDismissFornow) {
        proDismissFornow.addEventListener('click', ()=>{
            smartwooProBntAction('dismiss_fornow');
        });
    }

    if ( userDataDropDown ){
        let addedElement    = false;
        let customOption    = document.querySelector( '.sw-guest-option' );
        let metaDiv         = document.querySelector( '.sw-invoice-form-meta' );
        

        let removeElement = () =>{
            metaDiv.querySelectorAll('input').forEach(input=>{
                if ( 'is_guest_invoice' === input.name ) {
                    input.value = '';
                    input.value = 'no';
                } else {
                    input.value = '';
                }
            });

            if ( addedElement ) {
                let element = userDataDropDown.querySelector( `option[value="${addedElement}"]` );
                element.remove();
                addedElement = !addedElement;
            }

            if ( customOption ) {
                customOption.remove();
                showNotification('The origial invoice owner has been changed', 7000);
            }
                
            if ( theInvoiceAdminForm ) {
                theInvoiceAdminForm.querySelector('#fullname').textContent          = '';
                theInvoiceAdminForm.querySelector('#billingAddress').textContent    = '';
                theInvoiceAdminForm.querySelector('#billingPhone').textContent      = '';
                theInvoiceAdminForm.querySelector('#companyName').textContent       = '';
                theInvoiceAdminForm.querySelector('#billingEmail').textContent      = '';
            }
        }

        let updateInvoiceUserData = () =>{
            if ( theInvoiceAdminForm ) {
                let selectedUser = userDataDropDown.value;
                if ( ! selectedUser ) {                    
                    return;
                } else {
                    let userID  = selectedUser.split( '|' )[0];
                    if ( userID < 1 ){
                        showNotification( 'This user does not exist.', 5000 );
                        return;
                    }
                    let url = new URL( smartwoo_admin_vars.get_user_data );
                    let spinner = smartWooAddSpinner( 'swloader' );
                    url.searchParams.append( 'user_id', userID )
                    url.searchParams.append( 'security', smartwoo_admin_vars.security )

                    fetch(url, {
                        method: 'GET'
                    }).then(response =>{
                        if ( ! response.ok ) {
                            showNotification( `Error: ${response.statusText}`, 6000 );
                            throw new Error( response.statusText );
                        }
                        return  response.json()
                    }).then(responseData => {
                        if ( ! responseData.success ) {
                            showNotification( responseData.data.message, 6000 );
                        } else {
                            let userData = responseData.data.user;
                            theInvoiceAdminForm.querySelector('#fullname').textContent          = userData.full_name;
                            theInvoiceAdminForm.querySelector('#billingAddress').textContent    = userData.formated_address;
                            theInvoiceAdminForm.querySelector('#billingPhone').textContent      = userData.billing_phone;
                            theInvoiceAdminForm.querySelector('#companyName').textContent       = userData.billing_company;
                            theInvoiceAdminForm.querySelector('#billingEmail').textContent      = userData.billing_email;
                        }
                    }).catch(error =>{
                        console.error('Error fetching user data:', error)
                    }).finally(()=>{
                        smartWooRemoveSpinner( spinner );
                    })
                    
                }
                
            }
        }

        userDataDropDown.addEventListener( 'change', async (e)=>{
            let customOption    = document.querySelector( '.sw-guest-option' );
            if ( ! e.target.value.length || ( customOption && customOption.value ) === e.target.value ) {
               return;
            }

            
            if ( 'smartwoo_guest' !== e.target.value ) {
                removeElement();
                updateInvoiceUserData();
            }
  
            if ( 'smartwoo_guest' === e.target.value ) {
                let guestData   = await smartwooPromptGuestInvoiceData( 'Enter Guest Details' );
    
                if ( ! guestData ) {
                    if ( customOption ) {
                        userDataDropDown.value = customOption.value;
                        return;
                    }
                    removeElement();
                    userDataDropDown.value = "";
                    return;
                }

                if ( ! guestData.billing_email.length ) {
                    showNotification('The billing email is required.', 5000);
                    if ( customOption ) {
                        userDataDropDown.value = customOption.value;
                        return;
                    }
                    userDataDropDown.value = "";
                    return;
                }
    
                let optionText = `${guestData.first_name ? guestData.first_name + ' ' + guestData.last_name: 'Guest' } (${guestData.billing_email})`;
                let optionValue = `-1|${guestData.billing_email }`;

                removeElement();
                let newOption = document.createElement( 'option' );
                newOption.value = optionValue;
                newOption.text = optionText;
                newOption.classList.add( 'sw-guest-option' );
                userDataDropDown.prepend(newOption);
                userDataDropDown.value = newOption.value;
                addedElement = optionValue;
                
                metaDiv.querySelector('input[name="first_name"]').value         = guestData.first_name;
                metaDiv.querySelector('input[name="last_name"]').value          = guestData.last_name;
                metaDiv.querySelector('input[name="billing_address"]').value    = guestData.billing_address;
                metaDiv.querySelector('input[name="billing_phone"]').value      = guestData.billing_phone;
                metaDiv.querySelector('input[name="billing_company"]').value    = guestData.billing_company;
                metaDiv.querySelector('input[name="billing_email"]').value      = guestData.billing_email;
                metaDiv.querySelector('input[name="is_guest_invoice"]').value   = 'yes';

                if ( theInvoiceAdminForm ) {
                    theInvoiceAdminForm.querySelector('#fullname').textContent          = `${guestData.first_name} ${guestData.last_name}`;
                    theInvoiceAdminForm.querySelector('#billingAddress').textContent    = guestData.billing_address;
                    theInvoiceAdminForm.querySelector('#billingPhone').textContent      = guestData.billing_phone;
                    theInvoiceAdminForm.querySelector('#companyName').textContent       = guestData.billing_company;
                    theInvoiceAdminForm.querySelector('#billingEmail').textContent      = guestData.billing_email;
                }
            }
            
        });
    }

    if ( theInvoiceAdminForm ) {
        if ( smartwooProductDropdown ) {
            smartwooInvoiceEditorItemCalculator( smartwooProductDropdown );
        }

        let allInputs = theInvoiceAdminForm.querySelectorAll( '[required]' );
        allInputs.forEach( input =>{
            let fieldName = input.getAttribute( 'field-name' );
            if ( ! input.value.trim().length ) {
                input.setCustomValidity( `${ fieldName ? fieldName :'This field'} is required` );
            }

            input.addEventListener( 'input', () =>{
                if ( input.value.trim().length ) {
                    input.setCustomValidity( '' );
                }
            });
        });
        theInvoiceAdminForm.addEventListener( 'submit', (e)=>{
            e.preventDefault();
            let submitBtn = theInvoiceAdminForm.querySelector('button[type="submit"]');
            submitBtn.setAttribute( 'disabled', true );
            let loader = smartWooAddSpinner( 'swloader', true);
            // Remove existing error messages before adding new ones.
            let existingErrors = document.getElementById('invoice-errors');
            if (existingErrors) {
                existingErrors.remove();
            }
            let formData = new FormData( theInvoiceAdminForm );
            formData.append( 'security', smartwoo_admin_vars.security );
            fetch( smartwoo_admin_vars.ajax_url, { method: 'POST', 'body': formData } )
                .then( response =>{
                    if ( ! response.ok ) {
                        showNotification( response.statusText, 6000 );
                        throw new Error(`Error: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then( responseData=>{
                    if ( ! responseData.success ) {
                        
                        let errorContainer = document.createElement('div');
                        errorContainer.id = 'invoice-errors';
                        errorContainer.innerHTML = responseData.data.htmlContent ? responseData.data.htmlContent : responseData.data.message;
                        let removeErrorRmv = errorContainer.querySelector( '.swremove-field' );
                        if ( removeErrorRmv ) {
                            removeErrorRmv.addEventListener( 'click', e => e.target.parentElement.remove() );
                        }

                        // Insert error messages above the form
                        theInvoiceAdminForm.parentElement.insertBefore(errorContainer, theInvoiceAdminForm);
                        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                        submitBtn.removeAttribute( 'disabled' );

                    } else {
                        showNotification( responseData.data.message ? responseData.data.message : 'Invoice Created', 3000 );
                        setTimeout( ()=>{ window.location.href = responseData.data.redirect_url}, 3000)
                    }
                })
                .catch( error =>{
                    console.error('Fetch error:', error);
                    submitBtn.removeAttribute( 'disabled' );
                })
                .finally( ()=>{
                    smartWooRemoveSpinner(loader);
                });

        });
    }

    if ( invoicePageToggle.length ) {
        let billingBtn      = invoicePageToggle[1];
        let invoiceItemsBtn = invoicePageToggle[0];
        let billingTable    = document.querySelector( '.smartwoo-admin-invoice-billing-info' );
        let invoiceItemDiv  = document.querySelector( '.smartwoo-admin-invoice-items' );
        
        billingBtn.addEventListener( 'click', ()=>{
            if ( billingTable.classList.contains( 'smartwoo-hide' ) ) {
                billingTable.classList.remove( 'smartwoo-hide' );
                billingBtn.style.borderBottom = "solid #000000";
            } else {
                billingTable.classList.add( 'smartwoo-hide' );
                billingBtn.style.borderBottom = "none";

            }
        });
        invoiceItemsBtn.addEventListener( 'click', ()=>{
            if ( invoiceItemDiv.classList.contains( 'smartwoo-hide' ) ) {
                invoiceItemDiv.classList.remove( 'smartwoo-hide' );
                invoiceItemsBtn.style.borderBottom = "solid #000000";
            } else {
                invoiceItemDiv.classList.add( 'smartwoo-hide' );
                invoiceItemsBtn.style.borderBottom = "none";

            }
        });
    }

    if ( invoiceActionBtns.length ) {
        invoiceActionBtns.forEach( (btn)=>{
            let responseDiv     = document.querySelector( '#response-div' );

            btn.addEventListener( 'click', ()=>{
                
                let invoiceID       = responseDiv.getAttribute( 'data-invoice-id' );
                let action          = btn.getAttribute( 'data-value' );
                responseDiv.innerHTML = '';
                if ( 'send_payment_reminder' === action ) {
                    let confirmed = confirm( 'Are you sure you want to send a payment reminder email to the user?' );
                    if ( ! confirmed ) {
                        return;
                    }
                }
                if ( 'send_new_email' === action ) {
                    let confirmed = confirm( 'Are you sure you want to send a new invoice email to the user?' );
                    if ( ! confirmed ) {
                        return;
                    }
                }

                spinner = smartWooAddSpinner( 'swSpinner', true );
                let url = new URL( smartwoo_admin_vars.ajax_url );
                url.searchParams.append( 'action', 'smartwoo_admin_invoice_action' );
                url.searchParams.append( 'real_action', action );
                url.searchParams.append( 'invoice_id', invoiceID );
                url.searchParams.append( 'security', smartwoo_admin_vars.security );
                fetch( url, { method: 'GET' } )
                    .then( response =>{
                        if ( ! response.ok ) {
                            showNotification( response.statusText, 6000 );
                            throw new Error(`Error: ${response.status} ${response.statusText}`);
                        }
                        return response.json();
                    }).then( responseData =>{
                        if ( ! responseData.success ) {
                            responseDiv.innerHTML = responseData.data.message;
                            showNotification( responseData.data.message, 6000 );
                        } else {

                            if ( 'checkout_order_pay' === action || 'paymen_url' === action ) {
                                let heading = document.createElement( 'h3' );
                                let inputField  = document.createElement( 'input' );
                                let h3title = 'checkout_order_pay' === action ? 'Checkout Link' : 'Payment Link';
                                
                                heading.textContent         = h3title;
                                heading.style.textAlign     = "center";
                                inputField.readOnly         = true;
                                inputField.id               = 'smartwoo-invoice-links-input'
                                let copyBtn                 = document.createElement( 'span' );
                                copyBtn.classList.add( 'dashicons', 'dashicons-admin-page' );
                                copyBtn.setAttribute( 'title', 'copy to clipboard' );
                                copyBtn.style.cursor    = "pointer";
                                copyBtn.style.marginTop = "10px";
                                inputField.value = responseData.data.message;
                                
                                responseDiv.appendChild(heading);
                                responseDiv.appendChild(inputField);
                                responseDiv.appendChild(copyBtn);

                                copyBtn.addEventListener( 'click', async ()=>{
                                    navigator.clipboard.writeText( inputField.value );
                                    showNotification( 'Copied' );
                                   
                                });

                            } else{
                                responseDiv.innerHTML = responseData.data.message;
                            }
                        }
                    }).catch( (error) =>{
                        console.error('Fetch error:', error)
                    }).finally(()=>{
                        smartWooRemoveSpinner(spinner);
                    });
            });
        });
    }

    if ( invoiceLinksToggle && invoiceLinkActions ) {
        invoiceLinksToggle.addEventListener( 'click', ()=>{
            invoiceLinkActions.classList.toggle( 'active' );
        });
    }

    if ( swTable ) {
        let masterCheckbox  = swTable.querySelector( '#swTableCheckMaster' );
        let checkboxes      = swTable.querySelectorAll( '.sw-table-body-checkbox' );
        let actionData       = [];
        let removeActionDiv = ()=>{
            let actionDiv = document.querySelector( '.sw-action-container' );
            if ( actionDiv ) {
                actionDiv.remove();
            }
        }

        let dispatchEvent = ()=>{
            if ( actionData.length ) {
                actionData = [...new Set( actionData )];
                document.dispatchEvent( new CustomEvent( 'smartwooTableChecked', { detail: actionData } ) );
            }
        }

        if ( masterCheckbox ) {
            masterCheckbox.addEventListener( 'change', ()=>{
                removeActionDiv();
                checkboxes.forEach( ( checkbox ) =>{
                    isChecked = masterCheckbox.checked;
                    if ( isChecked ) {
                        checkbox.checked = true;
                        actionData.push( checkbox.getAttribute( 'data-value' ) );
                        dispatchEvent();
                    } else {
                        checkbox.checked = false;
                        actionData = actionData.filter( ( row )=> row !== checkbox.getAttribute( 'data-value' ) );
                    }
                    
                });
            });
        }

        if ( checkboxes.length ) {
            checkboxes.forEach( ( checkbox )=>{
                checkbox.addEventListener( 'change', ()=>{
                    removeActionDiv();
                    if ( checkbox.checked ) {
                        actionData.push( checkbox.getAttribute( 'data-value' ) );
                        dispatchEvent();
                    } else {
                        actionData = actionData.filter( ( row )=> row !== checkbox.getAttribute( 'data-value' ) );
                    }
    
                    if ( checkboxes.length === actionData.length ) {
                        masterCheckbox.checked = true;
                    } else {
                        masterCheckbox.checked = false;
                    }
                });
            });
        }

    }

    if ( gracePeriodSelect ) {
        gracePeriodSelect.addEventListener( 'change', ()=>{
            let gracePeriodUnit = document.querySelector( '.grace-period-number' );
            let descriptionClause = document.querySelector( '.description-class span' );
            if ( ! gracePeriodSelect.value.length ) {
                gracePeriodUnit.value = '';
                descriptionClause.textContent = '';
                gracePeriodUnit.readOnly = true;
            } else {
                gracePeriodUnit.readOnly = false;
                descriptionClause.textContent = " after";
                if ( ! gracePeriodUnit.value.length ) {
                    gracePeriodUnit.value = 7;
                }
            }
        });
    }

    if ( addProductImageBtn ) {
        let wpGallery;
        let imageIdInput = document.querySelector( '#product_image_id' );
        let PreviewDiv   = document.querySelector( '#image_preview' );
    
        addProductImageBtn.addEventListener( 'click', (e) => {
            e.preventDefault();

            if ( 'remove' === addProductImageBtn.value ) {
                jQuery( PreviewDiv ).fadeOut();
                setTimeout( ()=>{
                    PreviewDiv.innerHTML    = '';
                }, 1000 );
                addProductImageBtn.value =  'Upload'
                imageIdInput.value      = '';

                return;
            }
            
            // If the modal instance exists, reuse it
            if ( wpGallery ) {
                wpGallery.open();
                return;
            }
    
            // Create the media frame
            wpGallery = wp.media({
                title: 'Product Image',
                button: {
                    text: 'Set Product Image'
                },
                multiple: false
            });
    
            wpGallery.on( 'select', () => {
                // Clear previous content
                PreviewDiv.innerHTML = '';
                imageIdInput.value   = '';
    
                let image = wpGallery.state().get( 'selection' ).first().toJSON();
                let imTag = document.createElement( 'img' );
    
                imageIdInput.value  = image.id;
                imTag.src           = image.url;
                imTag.style.maxWidth = "100%";
    
                PreviewDiv.appendChild( imTag );
                jQuery( PreviewDiv ).fadeIn();
                addProductImageBtn.value =  'remove'
            });
    
            wpGallery.open();
        });
    }

    if ( uploadProductImages ){
        let defaultImgDivs  = document.querySelectorAll( '.sw-image-img' );
        if ( defaultImgDivs.length ){
            defaultImgDivs.forEach( div =>{
                let clsBtn = div.querySelector( 'span' );
                div.addEventListener( 'mouseover', ()=>{
                    clsBtn.classList.add( 'active' );
                });
                div.addEventListener( 'mouseleave', ()=>{
                    clsBtn.classList.remove( 'active' );
                });

                clsBtn.addEventListener( 'click', (e)=>{
                    e.preventDefault();
                    jQuery( div ).fadeOut();
                    setTimeout( () => {
                        div.remove();
                    }, 2000);
                });
            });
        }

        let wpGallery;
        let previewDiv      = document.querySelector( '#sw-product-gallery-preview' );
        uploadProductImages.addEventListener( 'click', ( e )=>{
            e.preventDefault();
            // If the modal exists, reopen it
            if ( wpGallery ) {
                wpGallery.open();
                return;
            }

            // Create the media frame
            wpGallery = wp.media({
                title: 'Select Images',
                button: { text: 'Use these images' },
                multiple: true // Allow multiple selection
            });

            // When images are selected
            wpGallery.on( 'select', () => {

                let selection = wpGallery.state().get( 'selection' );

                selection.each( (attachment) => {
                    let image       = attachment.toJSON();
                    let imgDiv      = document.createElement( 'div' );
                    let closeBtn    = document.createElement( 'span' );
                    let imgTag      = document.createElement( 'img' );
                    let idInput     = document.createElement( 'input' );
                    
                    imgDiv.classList.add( 'sw-image-img' );
                    closeBtn.classList.add( 'dashicons', 'dashicons-dismiss' );
                    imgTag.src      = image.url;
                    idInput.name    = "product_gallery_ids[]";
                    idInput.type    = 'hidden';
                    idInput.value   = image.id;
                    
                    imgDiv.appendChild( closeBtn );
                    imgDiv.appendChild( idInput );
                    imgDiv.appendChild( imgTag );
                    previewDiv.appendChild( imgDiv );

                    closeBtn.addEventListener( 'click', (e)=>{
                        e.preventDefault();
                        jQuery( imgDiv ).fadeOut();
                        setTimeout( () => {
                            imgDiv.remove();
                        }, 2000);
                    });

                    imgDiv.addEventListener( 'mouseover', ()=>{
                        closeBtn.classList.add( 'active' );
                    });
                    imgDiv.addEventListener( 'mouseleave', ()=>{
                        closeBtn.classList.remove( 'active' );
                    });
                });

                
            });

            wpGallery.open();
        });
    }

    if ( productdataTabs ) {
        let allbtns     = productdataTabs.querySelectorAll( 'li' );
        let menuContent = document.querySelector( '.sw-product-data-tabs-content' );
        let allcontents = menuContent.querySelectorAll( 'div' );

        let closeAll = () =>{
            allcontents.forEach( div =>{
                if ( ! div.classList.contains( 'smartwoo-hide' ) ) {
                    div.classList.add( 'smartwoo-hide' );
                }
            });
            allbtns.forEach(btn=>{
                btn.classList.remove( 'active' );
            });

        }

        allbtns.forEach( ( btn, index ) => {
            btn.addEventListener( 'click', e =>{
                closeAll();
                e.target.classList.add( 'active' );
                allcontents[index].classList.remove( 'smartwoo-hide' );
            });
        });
    }

    if ( theProductForm ) {
        theProductForm.addEventListener( 'submit', (e)=>{
            e.preventDefault();
            if ( window.tinymce ) {
                window.tinymce.editors.forEach( editor => editor.save() );
            }
            let noticeDiv   = document.querySelector( '#response-container' );
            noticeDiv.innerHTML = '';
            let spinner     = smartWooAddSpinner( 'swloader', true );
            let formData    = new FormData( theProductForm );
            let url         = new URL( smartwoo_admin_vars.ajax_url );
            formData.append( 'security', smartwoo_admin_vars.security );

            fetch( url, {
                method : 'POST',
                body: formData
            }).then( response =>{
                if ( ! response.ok ) {
                    showNotification( response.statusText, 6000 );
                    throw new Error(`Error: ${response.status} ${response.statusText}`);
                }
                return response.json();
            }).then( responseData=>{
                if ( ! responseData.success ) {
                    showNotification( responseData.data.message, 6000 );
                } else {
                    showNotification( responseData.data.message, 3000 );
                }

                if ( window.location.search.includes( 'tab=edit' ) && responseData.success ) {
                    window.location.reload();
                    return;
                }

                noticeDiv.innerHTML = responseData.data.htmlContent ? responseData.data.htmlContent : responseData.data.message;
                window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                noticeDiv.querySelector( '.swremove-field' ).addEventListener( 'click', e => e.target.parentElement.remove() );


            }).catch( error=>{
                console.error( 'Error:', error);
            }).finally(()=>{
                smartWooRemoveSpinner( spinner );
            });

        });
    }

    if ( removeBtn ) {
        removeBtn.forEach( btn =>{
            btn.addEventListener( 'click', e =>{
                e.preventDefault();
                btn.parentElement.remove();
            });
        });
    }

    if ( adminViewServiceDivs.length ) {
        adminViewServiceDivs.forEach( div =>{
            div.addEventListener( 'mouseover', ()=>{
                div.classList.add( 'active' )
            });

            div.addEventListener( 'mouseleave', ()=>{
                div.classList.remove( 'active' )
            });
        });
    }

    if ( serviceFormUserDropdown ) {
        let clientMeta      = document.querySelector( '.sw-service-client-info' );
        let userFullName    = clientMeta.querySelector( '.sw-user-fullname' );
        let userEmail       = clientMeta.querySelector( '.sw-user-email' );
        let image           = clientMeta.querySelector( 'img' );
        
        let defaultAvatar   = smartwoo_admin_vars.default_avatar_url;
        let defaultText     = 'No user selected';
        serviceFormUserDropdown.addEventListener( 'change', (e) =>{
            if ( ! e.target.value.length ) {
                userFullName.textContent    = defaultText;
                userEmail.textContent       = '';
                image.src                   = defaultAvatar;
                return;
            }

            let spinner = smartWooAddSpinner( 'spinner', false );
            spinner.style.position = 'absolute';
            spinner.style.left = '50%';
            spinner.style.top = '50%';
            let userID  = e.target.value.split( '|' )[0];
            
            let url             = new URL( smartwoo_admin_vars.get_user_data );
            url.searchParams.append( 'user_id', userID )
            url.searchParams.append( 'security', smartwoo_admin_vars.security )

            fetch(url, {
                method: 'GET'
            }).then(response =>{
                if ( ! response.ok ) {
                    showNotification( `Error: ${response.statusText}`, 6000 );
                    throw new Error( response.statusText );
                }
                return  response.json()
            }).then(responseData => {
                if ( ! responseData.success ) {
                    showNotification( responseData.data.message, 6000 );
                } else {
                    userFullName.innerHTML  = `<strong>Full name</strong>: ${responseData.data.user.full_name}`;
                    userEmail.innerHTML     = `<strong>Email</strong>: ${responseData.data.user.email}`;
                    image.src               = responseData.data.user.avatar_url;
                }
            }).catch(error =>{
                console.error('Error fetching user data:', error)
            }).finally(()=>{
                smartWooRemoveSpinner( spinner );
            })

        });
    }

    if ( serviceForm ) {
        let requiredFields = serviceForm.querySelectorAll("[required]");
        requiredFields.forEach((field) => {
            field.addEventListener("invalid", ()=> {
                let fieldName = field.getAttribute("field-name") || "This field";
                field.setCustomValidity(`${fieldName} is required.`);
            
            });

            field.addEventListener("input", function () {
                field.setCustomValidity("");
            });
        });
        reponseDiv          = document.querySelector( '#response-container' );
        serviceForm.addEventListener( 'submit', (e)=>{
            e.preventDefault();
            SmartWooEditor.saveAll();
            reponseDiv.innerHTML = '';
            let spinner         = smartWooAddSpinner( 'swloader', true );
            let sbmtBtn         = serviceForm.querySelector( 'button[type="submit"]' );
            let theFormData     = new FormData( serviceForm );
            let url             = new URL( smartwoo_admin_vars.ajax_url );
            theFormData.append( 'security', smartwoo_admin_vars.security );
            sbmtBtn.disabled    = true;

            fetch( url, {
                method: 'POST',
                body:   theFormData
            }).then( response =>{
                if ( ! response.ok ){
                    showNotification( `Error: ${response.statusText}`, 6000 );
                    throw new Error( `Error: ${response.statusText}`)
                }

                return response.json();
            }).then( responseData =>{
                if ( ! responseData.success ) {
                    showNotification( `Error: ${responseData.data.message}`, 6000 );
                    reponseDiv.innerHTML = responseData.data.htmlContent;
                    window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                    reponseDiv.querySelector( '.swremove-field' ).addEventListener( 'click', e => e.target.parentElement.remove() );
                } else {
                    showNotification( responseData.data.message, 3000 );
                    setTimeout(()=>{
                        window.location.href = responseData.data.redirect_url;
                    }, 3000)

                }
            }).catch( error =>{
                console.error( error )
            }).finally(()=>{
                smartWooRemoveSpinner( spinner );
                sbmtBtn.disabled    = false;

            })

        });
    }

    if ( resetFastCheckoutBtn ) {
        resetFastCheckoutBtn.addEventListener( 'click', e =>{
            e.preventDefault();
            let confirmed = confirm( 'All your customizations will be lost!');

            if ( ! confirmed ) {
                return;
            }

            let url = new URL( smartwoo_admin_vars.ajax_url );
            url.searchParams.set( 'action', 'smartwoo_reset_fast_checkout' );
            url.searchParams.set( 'security', smartwoo_admin_vars.security );
            
            fetch( url )
            .then( response => {
                if ( response.ok ) {
                    window.location.reload();
                } else {
                    showNotification( `Error: ${response.statusText}` );
                }
            })
        })
    }

    if ( smartwooKnowledgeBase ) {
        let navButtons  = smartwooKnowledgeBase.querySelectorAll( '.smartwoo-knowledgebase-nav' );
        let allContents = document.querySelectorAll( '.sw-knowledgebase-content' );

        let closeAll = () => {
            allContents.forEach( content =>{
                if ( ! content.classList.contains( 'smartwoo-hide' ) ) {
                    content.classList.add( 'smartwoo-hide' );
                }
            });

            navButtons.forEach( button =>{
                if ( button.classList.contains( 'active' ) ) {
                    button.classList.remove( 'active' );
                }
            });
        }

        navButtons.forEach( ( button, index ) =>{
            button.addEventListener( 'click', (e) =>{
                e.preventDefault();
                closeAll();
                e.target.classList.add( 'active' );
                allContents[index].classList.remove( 'smartwoo-hide' );
            })
        });
    }
    
});

/**
 * Dashboard event listener.
 */
document.addEventListener('SmartWooDashboardLoaded', () => {
    let dashboardCount  = document.querySelectorAll('.sw-dash-content');
    let dashboardBtn    = document.getElementById('dashboardBtn');
    let contentDiv      = document.querySelector('.sw-dash-content-container');

    // Loop through each dashboard statistic container and attach event listener
    dashboardCount.forEach((stat, index) => {
        stat.addEventListener('click', (e) => {
            if (e.target.matches('.sw-red-button') ) {
                return;
            }
            smartwooRemoveTable();
            fetchDashboardData(index, { limit: 25 });
        });
    });

    // Add listener to dashboard button
    dashboardBtn.addEventListener('click', () => {
        smartwooRemoveTable();  // Remove table when dashboard button is clicked
        jQuery(contentDiv).fadeIn().css('display', 'flex');// Show dashboard content
    });
});

/**
 * Smart Woo Table Checkbox event listener.
 */
document.addEventListener( 'smartwooTableChecked', (e)=>{
    // Get the page where this action is fired.
    let adminPage = smartwoo_admin_vars.currentScreen;
    if ( 'Service Orders' === adminPage ) {
        smartwooBulkActionForTable({
            options: [
                { value: 'delete', text: 'Delete' },
                { value: 'complete', text: 'Complete' },
            ],
            selectedRows: e.detail,
            hookName: 'order_table_actions'
        });
    }

    if ( 'Invoices' === adminPage ) {
        smartwooBulkActionForTable({
            options: [
                {value: 'paid', text: 'Paid'},
                {value: 'unpaid', text: 'Unpaid'},
                {value: 'due', text: 'Due'},
                {value: 'cancelled', text: 'Cancelled'},
                {value: 'delete', text: 'Delete'},

            ],
            selectedRows: e.detail,
            hookName: 'invoice_table_actions'
        });
    }

    if ( 'Service Products' === adminPage ) {
        smartwooBulkActionForTable({
            options: [
                {value: 'publish', text: 'Publish'},
                {value: 'private', text: 'Private'},
                {value: 'pending', text: 'Pending'},
                {value: 'draft', text: 'Draft'},
                {value: 'trash', text: 'Move to trash'},
                {value: 'delete', text: 'Delete'},
            ],
            selectedRows: e.detail,
            hookName: 'product_table_actions'
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
			newField.style.display = "none";
            const uniqueID = `sw-editor-${Date.now()}-${Math.floor(Math.random() * 1000)}`;

            newField.innerHTML = `
				<hr>
				<h4>
					Asset type:
					<input type="text" name="additional_asset_types[]" placeholder="eg. water billing asset..." />
				</h4>
				<input type="text" name="additiional_asset_names[]" placeholder="Asset Name" />
				<input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="Limit (optional)">
				<textarea id="${uniqueID}" class="smartwoo-asset-editor-ui" name="additional_asset_values[]" placeholder="Start building: rich text, immersive audio & video playlists, stunning image galleries, custom HTML, or shortcodes."></textarea>
                <span class="dashicons dashicons-dismiss remove-field" title="Remove this field"></span>
            `;

            mainContainer.insertBefore( newField, moreAddiAssetsButton );
            const editorInstance = new SmartWooEditor( `#${uniqueID}` );
            editorInstance.init().then( () => jQuery( newField ).fadeIn() );
			
        });

        mainContainer.addEventListener( 'click', ( event ) => {
            if ( event.target.classList.contains( 'remove-field' ) ) {
                event.preventDefault();
                let fieldToRemove   = event.target.parentElement;
				let assetID         = event.target.getAttribute( 'data-asset-id' );
				let confirmed       = assetID && confirm( 'This asset will be deleted from the database, click okay to continue.' );
                
                if ( confirmed ) {
					let spinner     = smartWooAddSpinner( 'swloader', true );
                    const payload   = new FormData();
                    const url       = smartwoo_admin_vars.ajax_url;
                    payload.set( 'action', 'smartwoo_asset_delete' );
                    payload.set( 'security', smartwoo_admin_vars.security );
                    payload.set( 'asset_id', assetID );

                    fetch( url, {
                        method: 'POST',
                        body: payload,
                        credentials: 'same-origin'
                    }).then( response => {
                        if ( ! response.ok ) {
                            throw new Error( `Fetch Error: ${response.statusText}` );
                        }

                        return response.json();
                    }).then( responseJSON => {
                        if ( responseJSON.success ) {
                            jQuery( fieldToRemove ).fadeOut( 'slow', () => fieldToRemove.remove() );
                        }
                        
                        showNotification( responseJSON.data.message, 5000 );
                    }).catch( error => {
                        console.error( error );
                    }).finally( () => smartWooRemoveSpinner( spinner ) );

				} else if ( ! assetID ) {
					jQuery( fieldToRemove ).fadeOut( 'slow', () => fieldToRemove.remove() );
				}
				
            }
        });
    }

	if ( isExternal ) {
		var inputField = document.getElementById( 'auth-token-div' );
		isExternal.addEventListener( 'change', function( e ) {
			e.preventDefault()
			if ( 'yes' === isExternal.value ) {
				jQuery( inputField ).fadeIn();		
			} else {
				jQuery( inputField ).fadeOut();		

			}
			
		});
	}

});