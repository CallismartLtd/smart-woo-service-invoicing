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
    table.classList.add('sw-table');

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
    bodyContent.appendChild(table);

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

// Add pagination control to table.
function addPaginationControls(bodyContent, totalPages, currentPage, totalItems, index) {
    let paginationDiv = document.createElement('div');
    paginationDiv.classList.add('sw-pagination-buttons');
    paginationDiv.style.float = "right";
    // Item count placeholder (optional if needed for the frontend)
    let itemCountText = document.createElement('p');
    itemCountText.textContent = `${totalItems} items`; // Example item count; you can adjust it based on real data
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
            fetchDashboardData(index, { paged: prevPage });
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
            fetchDashboardData(index, { paged: nextPage });
        });

        paginationDiv.appendChild(nextLink);
    }

    // Append pagination controls to the body content
    bodyContent.appendChild(paginationDiv);
}

/**
 * Redirect to Service view page at admin.
 */
function smartwoo_service_admin_view(serviceId) {
    let dashUrl = new URL(smartwoo_admin_vars.sw_admin_page);
    dashUrl.searchParams.set('action', 'view-service');
    dashUrl.searchParams.set('service_id', serviceId);
    dashUrl.searchParams.set('tab', 'details');
    window.location.href = dashUrl.href;

}

// Function to remove the table
function smartwooRemoveTable() {
    let swTable = document.querySelector('.sw-table');
    let pagenaBtns = document.querySelectorAll('.sw-pagination-buttons');
    if (swTable) {
        jQuery('.sw-table').fadeOut();
        setTimeout(()=>{
            swTable.remove();
        }, 1000);
        
        if (pagenaBtns){
    
            pagenaBtns.forEach((btns)=>{
                btns.remove();
            });
        
        }  
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
            // console.log('Bulk action success:', response);
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
        actionBtn.style.backgroundColor = "#f1f1f1f1";
        actionBtn.style.marginLeft = "-2px";
        actionBtn.style.height = "30px";
        actionBtn.style.border = "solid .5px blue";
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
                console.log(response);
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

            // Perform auto data update every five minutes.
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
    showLoadingIndicator();
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

    if ('all_pending_services_table' === realAction) {
        window.location.href = smartwoo_admin_vars.admin_order_page;
        return;
    }

    // Default pagination vars if not provided
    let limit = queryVars.limit || 10;
    let paged = queryVars.paged || 1;

    jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            security: smartwoo_admin_vars.security,
            real_action: realAction,
            limit: limit,
            paged: paged,
            search_term: 'sw_search' === realAction ? queryVars.search: '',
        },
        success: function(response) {
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
        },
        error: function(error) {
            var message  = 'Error fetching data: ';
            if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
                message += error.responseJSON.data.message;
            } else if (error.responseText) {
                message += error.responseText;
            } else {
                message += error;
            }
            console.error(message);
        },
        complete: function() {
            if (dashContents ) {
                dashContents.style.display = "none";
            }
            
            hideLoadingIndicator();
        },
    });
}

// Delete an invoice
function smartwooDeleteInvoice(invoiceId) {
    let isConfirmed = confirm('Do you realy want to delete this invoice? This action cannot be reversed!');
	if (isConfirmed) {
		spinner = smartWooAddSpinner( 'sw-delete-button' );

		jQuery.ajax(
			{
				type: 'POST',
				url: smart_woo_vars.ajax_url,
				data: {
					action: 'delete_invoice',
					invoice_id: invoiceId,
					security: smart_woo_vars.security
				},
				success: function ( response ) {
					if ( response.success ) {
						alert( response.data.message );
						window.location.href = smart_woo_vars.admin_invoice_page;	
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
function smartwooProBntAction(action_name) {
    let spinner = smartWooAddSpinner('sw-loader');
    jQuery.ajax({
        type: 'GET',
        url: smart_woo_vars.ajax_url,
        data: {
            action: 'smartwoo_pro_button_action',
            security: smart_woo_vars.security,
            real_action: action_name
        },

        success: (response)=>{
            if (response.success) {
                let adDiv = document.querySelector('.sw-pro-sell-content');
                adDiv.innerHTML = ''; // Clear existing content
                
                // Create and append the check icon
                let checkIcon = document.createElement('span');
                checkIcon.className = 'dashicons dashicons-yes-alt';
                let respMessage = document.createElement('h2');
                respMessage.textContent = response.data.message;
                adDiv.append(checkIcon);
                adDiv.append(respMessage);
                
                // Toggle the 'loaded' class every second
                setInterval(() => checkIcon.classList.toggle('loaded'), 1000);
                
                // Fade out and remove the adDiv
                setTimeout(() => {
                    jQuery(adDiv.parentElement).fadeOut(() => adDiv.remove());
                }, 3000);
            } else {
                showNotification(response.data.message, 3000);
                window.location.reload();
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
        },
        complete: ()=>{
            smartWooRemoveSpinner(spinner);
        }
    });
}

function smartwooDeleteProduct(productId) {
    let isConfirmed = confirm( 'Are you sure you want to permanently delete this product? This action cannot be reversed!' );
    if (isConfirmed) {
        spinner = smartWooAddSpinner( 'sw-delete-button' );

        jQuery.ajax(
            {
                type: 'POST',
                url: smart_woo_vars.ajax_url,
                data: {
                    action: 'smartwoo_delete_product',
                    product_id: productId,
                    security: smart_woo_vars.security
                },
                success: function ( response ) {
                    
                    if ( response.success ) {
                        alert( response.data.message );
                        window.location.href = smart_woo_vars.sw_product_page;
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
        spinner = smartWooAddSpinner( 'sw-delete-button' );

        // Perform an Ajax request to delete the invoice
        jQuery.ajax(
            {
                type: 'POST',
                url: smart_woo_vars.ajax_url,
                data: {
                    action: 'smartwoo_delete_service',
                    service_id: serviceId,
                    security: smart_woo_vars.security
                },
                success: function (response) {
                    if ( response.success) {

                        alert( response.data.message );
                        window.location.href = smart_woo_vars.sw_admin_page;
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
        initDiv.remove();
    }

    let mainDiv         = document.querySelector('.inv-settings-form');
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
        proDiv.remove();
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

document.addEventListener('DOMContentLoaded', () => {
    let contentDiv          = document.querySelector('.sw-dash-content-container');
    let skeletonContent     = document.querySelectorAll('.sw-dash-content');
    let ordersBtn           = document.getElementById('dashOrderBtn');
    let addNewBtn           = document.getElementById('dashAddNew');
    let invoicesBtn         = document.getElementById('dashInvoicesBtn');
    let productsBtn         = document.getElementById('dashProductBtn');
    let settingsBtn         = document.getElementById('dashSettingsBtn');
    let proBtns             = document.querySelectorAll('.sw-upgrade-to-pro');
    let searchField         = document.getElementById('sw_service_search');
    let searchbtn           = document.getElementById('swSearchBtn');
    const notificationTooltip = document.getElementById('search-notification');
    let menuButton          = document.querySelector('.sw-admin-menu-icon');
    let deleteInvoiceBtns   = document.querySelectorAll('.delete-invoice-button');
    let deleteProductIds    = document.querySelectorAll('.sw-delete-product' );
    let deleteServiceBtn    = document.querySelector('.delete-service-button');
    let adminDashHeader     = document.querySelector('.sw-admin-dash-header');
    let editMailBtns        = document.querySelectorAll('.sw-edit-mail-nopro');
    let swCheckBoxes        = document.querySelectorAll('.sw-checkboxes');
    let swHideBtn           = document.getElementById('sw-hide');
    let noSbmtBtn           = document.querySelectorAll('.smartwoo-prevent-default' );
    let proRemindLaterBtn   = document.querySelector('#smartwoo-pro-remind-later');
    let proDismissFornow    = document.querySelector('#smartwoo-pro-dismiss-fornow');
    let userDataDropDown    = document.querySelector( '#user_data' );
    let theInvoiceAdminForm   = document.querySelector( '#smartwooInvoiceForm' );

    if ( contentDiv ) {
        let wpHelpTab = document.getElementById('contextual-help-link-wrap');
        let wpHelpDiv = document.getElementById('contextual-help-wrap');
        let wpScreen  = document.getElementById('contextual-help-columns');
        if (wpHelpTab) {
            
            wpHelpTab.style.zIndex = '9999';
            wpHelpDiv.style.zIndex = '9999';
            wpHelpTab.style.top = '110px';
            wpHelpTab.style.right = '1px';
            wpScreen.style.backgroundColor = '#f9f9f9';
            wpScreen.style.border = 'solid blue 1px';
            wpHelpTab.style.position = 'absolute';
        }
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
        let toggled = false;
        menuButton.addEventListener('click', ()=>{
            let navDiv = document.querySelector('.sw-admin-dash-nav');
            
            if (!toggled) {
                
                jQuery(navDiv).fadeIn().css('display', 'flex');

            } else {
                // navDiv.style.display = "none";
                jQuery(navDiv).fadeIn().css('display', 'none');

            }

            toggled = !toggled;
            
        });
    }

    if (deleteInvoiceBtns && deleteInvoiceBtns.length !== 0) {
        deleteInvoiceBtns.forEach((deleteInvoiceBtn)=>{
            let siblings = deleteInvoiceBtn.parentElement.querySelectorAll('a button');
            let invoiceId = deleteInvoiceBtn.getAttribute('data-invoice-id');
            deleteInvoiceBtn.classList.add('sw-icon-button-admin');
            siblings.forEach((Btn)=>{
                Btn.classList.add('sw-icon-button-admin');
    
            });
            deleteInvoiceBtn.addEventListener('click', ()=>{
                smartwooDeleteInvoice(invoiceId);
            });
        });

    }

    if (deleteProductIds && deleteProductIds.length !== 0) {
        deleteProductIds.forEach((deleteProductId)=>{
            let siblings = deleteProductId.parentElement.querySelectorAll('a button');
            let productId = deleteProductId.getAttribute('data-product-id');
            deleteProductId.classList.add('sw-icon-button-admin');
            siblings.forEach((Btn)=>{
                Btn.classList.add('sw-icon-button-admin');
    
            });
            deleteProductId.addEventListener('click', ()=>{
                smartwooDeleteProduct(productId);
            });
        });

    }

    if (deleteServiceBtn) {
        let siblings = deleteServiceBtn.parentElement.querySelectorAll('a button');
        let serviceId = deleteServiceBtn.getAttribute('data-service-id');
        siblings.forEach((Btn)=>{
            Btn.classList.add('sw-icon-button-admin');

        });
        deleteServiceBtn.classList.add('sw-icon-button-admin');
        deleteServiceBtn.addEventListener('click', ()=>{
            smartwooDeleteService(serviceId);
        } );

    }

    if (adminDashHeader && window.innerWidth <= 600 ) {
       
        document.addEventListener('scroll', ()=>{
            let scrollUp = window.scrollY > 0;
            if( scrollUp ) {
                adminDashHeader.style.top = "0";
                adminDashHeader.style.padding = "-5px";

            } else {
                adminDashHeader.style.top = "20px";

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
                let enableBtnFnc = ()=>{
                    if (newWindow && newWindow.closed) {
                        btn.disabled = false;
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
        }

        userDataDropDown.addEventListener( 'change', async (e)=>{
            if ( ! e.target.value.length || ( customOption && customOption.value ) === e.target.value ) {
                return;
            }

            if ( 'smartwoo_guest' !== e.target.value ) {
                removeElement();
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
            }
            

        });
    }

    if ( theInvoiceAdminForm ) {
        theInvoiceAdminForm.addEventListener( 'submit', (e)=>{
            e.preventDefault();
            let loader = smartWooAddSpinner( 'swloader', true);
            // Remove existing error messages before adding new ones.
            let existingErrors = document.getElementById('invoice-errors');
            if (existingErrors) {
                existingErrors.remove();
            }
            let formData = new FormData( theInvoiceAdminForm );
            formData.append( 'security', smartwoo_admin_vars.security );
            fetch( smartwoo_admin_vars.ajax_url, { 'method': 'POST', 'body': formData } )
                .then( response =>{
                    if ( ! response.ok ) {
                        showNotification( response.statusText, 6000 );
                        throw new Error(`Error: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then( responseData=>{
                    if ( ! responseData.success ) {
                        // Create a wrapper div for errors
                        let errorContainer = document.createElement('div');
                        errorContainer.id = 'invoice-errors';
                        errorContainer.innerHTML = responseData.data.htmlContent;
        
                        // Insert error messages above the form
                        theInvoiceAdminForm.parentElement.insertBefore(errorContainer, theInvoiceAdminForm);
                        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                    } else {
                        showNotification( responseData.data.message ? responseData.data.message : 'Invoice Created', 3000 );
                        setTimeout( ()=>{ window.location.href = responseData.data.redirect_url}, 3000)
                    }
                })
                .catch( error => console.error('Fetch error:', error))
                .finally( ()=>{
                    smartWooRemoveSpinner(loader);
                });

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
    let proDiv          = document.querySelector('.sw-dash-pro-sell-bg');

    // Loop through each dashboard statistic container and attach event listener
    dashboardCount.forEach((stat, index) => {
        stat.addEventListener('click', (e) => {
            if (e.target.matches('.sw-red-button') ) {
                return;
            }
            smartwooRemoveTable();
            fetchDashboardData(index);
        });
    });

    // Add listener to dashboard button
    dashboardBtn.addEventListener('click', () => {
        smartwooRemoveTable();  // Remove table when dashboard button is clicked
        jQuery(contentDiv).fadeIn().css('display', 'flex');// Show dashboard content
    });

    if (proDiv) {
        jQuery(proDiv).fadeIn();
    }
});
