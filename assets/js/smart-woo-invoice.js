/**
 * Client Invoice page Scripts.
 * @author Callistus Nwachukwu
 * @since 2.0.15 
 */
function smartwooPrintinvoice() {
    let invoiceContainer = document.querySelector('.sw-invoice-template');
    let closeIcon = `X`;
    let closeBtn    = document.createElement('button');
    closeBtn.classList.add('sw-cls-btn-no-print');
    closeBtn.textContent = closeIcon;
    let invoiceArea = invoiceContainer.innerHTML;


    // Open a new blank window.
    let printWindow = window.open('', '_blank');
    let stylesheet = `<link rel="stylesheet" href="${smart_woo_vars.smartwoo_assets_url}css/smart-woo-invoice.css" type="text/css" />`;

    // Write the HTML content and link the external stylesheet
    printWindow.document.write('<html><head><title>Print Invoice</title>');
    printWindow.document.write(stylesheet); // Add the stylesheet.
    printWindow.document.write('</head><body>');
    printWindow.document.write(invoiceArea);
    printWindow.document.querySelector('body').append(closeBtn);
    printWindow.document.querySelector('.sw-invoice-item-table').style.width = "90%";
    printWindow.document.write('</body></html>');
    
    // Close the document to finish writing and initiate the print function
    printWindow.document.close();
    
    // Focus the new window and print the content.
    printWindow.focus();
    printWindow.print();
    
    printWindow.addEventListener( 'afterprint', ()=> {
        printWindow.close();
    });

    closeBtn.addEventListener('click', ()=>{
        printWindow.close();

    })
}


document.addEventListener('DOMContentLoaded', () => {
    let invoiceArea = document.querySelector('.sw-invoice-template');
    let printBtn    = document.getElementById('smartwoo-print-invoice-btn');
    
    if( invoiceArea && smart_woo_vars.is_account_page ) {
        invoiceArea.style.minWidth = "90%";
    }

    if (printBtn) {
        printBtn.addEventListener("click", smartwooPrintinvoice);
    }

});
