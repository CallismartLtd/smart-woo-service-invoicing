// Global object to store checkbox states
let selectedRowsState = {};

function renderTable(headers, bodyData, rowNames, totalPages, currentPage, index) {
    let bodyContent = document.querySelector('.sw-admin-dash-body');
    
    // Clear existing table before rendering the new one
    removeTable();
    totalItems  = bodyData.length;
    
    // Add pagination controls
    addPaginationControls(bodyContent, totalPages, currentPage, totalItems, index);
    
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
        }
    }
}



function addPaginationControls(bodyContent, totalPages, currentPage, totalItems, index) {
    let paginationDiv = document.createElement('div');
    paginationDiv.classList.add('sw-pagination-buttons');

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
function removeTable() {
    let swTable = document.querySelector('.sw-table');
    let pagenaBtns = document.querySelectorAll('.sw-pagination-buttons');
    if (swTable) {
        jQuery('.sw-table').fadeOut();
        setTimeout(()=>{
            swTable.remove();
        }, 1000);
        
        if (pagenaBtns){
            jQuery(pagenaBtns).fadeOut();
            setTimeout(()=>{
                pagenaBtns.forEach((btns)=>{
                    btns.remove();
                });
            }, 1000);
            
        }  
    }
}

// Example usage of the smartwooShowActionDialog function
function smartwooShowActionDialog(selectedRows) {
    console.log('Selected Rows:', selectedRows);
    // Show the action dialog here with the selected rows' names
    // You can implement your dialog or buttons to perform actions based on the selected rows
}

// Helper function to make AJAX requests and update the DOM
function fetchServiceCount( index, action, label ) {
    let dashContents = document.querySelectorAll( '.sw-dash-content' );

    return jQuery.ajax({
        type: "GET",
        url: smartwoo_admin_vars.ajax_url,
        data: {
            action: 'smartwoo_dashboard',
            real_action: action,
            security: smartwoo_admin_vars.security,
        },
        success: function( response ) {
            if ( response.success ) {
                smartwoo_clear_dash_content( index );
                let divTag  = document.createElement('div');
                let hTag    = document.createElement('h2');
                let spanTag = document.createElement('span');

                divTag.classList.add('sw-dash-count');
                hTag.textContent = response.data[action]; // Dynamically use the action key
                spanTag.textContent = label;
                
                divTag.appendChild(hTag);
                divTag.appendChild(spanTag);
                dashContents[index].append(divTag);
                jQuery('.sw-dash-count').fadeIn().css('display', 'flex');
            }
        },
        error: function( error ) {
            var message  = 'Error fetching data: ';
            if (error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
                message += error.responseJSON.data.message;
            } else if (error.responseText) {
                message += error.responseText;
            } else {
                message += error;
            }
            console.error(message);
        }
    });
}

// Function to clear skeleton content
function smartwoo_clear_dash_content( index ) {
    let dashContents = document.querySelectorAll( '.sw-dash-content' );
    dashContents[index].innerHTML = "";
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
            
            hideLoadingIndicator()
        },
    });
    

}

document.addEventListener('DOMContentLoaded', () => {
    let contentDiv = document.querySelector('.sw-dash-content-container');
    let skeletonContent = document.querySelectorAll('.sw-dash-content');
    let ordersBtn = document.getElementById('dashOrderBtn');
    let invoicesBtn = document.getElementById('dashInvoicesBtn');
    let productsBtn = document.getElementById('dashProductBtn');
    let settingsBtn = document.getElementById('dashSettingsBtn');
    let proBtn = document.querySelector('.sw-upgrade-to-pro');
    let searchField = document.getElementById('sw_service_search');
    let searchbtn = document.getElementById('swSearchBtn');
    const notificationTooltip = document.getElementById('search-notification');

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

        // Use Promise.all to wait for all fetchServiceCount promises to resolve
        Promise.allSettled(dashBoardLoad).finally(() => {
            document.dispatchEvent(new CustomEvent('SmartWooDashboardLoaded'));
        });
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

    if (proBtn) {
        proBtn.addEventListener('click', ()=>{
            window.open(smartwoo_admin_vars.smartwoo_plugin_page, '_blank');
        });
    }

    if (searchField && searchbtn && notificationTooltip) {
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

});

/**
 * Dashboard event listener.
 */
document.addEventListener('SmartWooDashboardLoaded', () => {
    let dashboardCount = document.querySelectorAll('.sw-dash-content');
    let dashboardBtn = document.getElementById('dashboardBtn');
    let contentDiv = document.querySelector('.sw-dash-content-container');

    // Loop through each dashboard statistic container and attach event listener
    dashboardCount.forEach((stat, index) => {
        stat.addEventListener('click', () => {
            removeTable();  // Remove table when any stat is clicked
            fetchDashboardData(index);  // Fetch new data for the clicked stat
        });
    });

    // Add listener to dashboard button
    dashboardBtn.addEventListener('click', () => {
        removeTable();  // Remove table when dashboard button is clicked
        jQuery(contentDiv).fadeIn().css('display', 'flex');// Show dashboard content
    });
});


