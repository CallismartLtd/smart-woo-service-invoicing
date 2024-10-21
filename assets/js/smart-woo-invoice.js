/**
 * Client Invoice page Scripts.
 * @author Callistus Nwachukwu
 * @since 2.0.15 
 */
function smartwooPrintinvoice() {
    let invoiceContainer = document.querySelector('.sw-invoice-template');
    let invoiceArea = invoiceContainer.innerHTML;

    // Open a new blank window.
    var printWindow = window.open('', '_blank');
    var stylesheet = `<link rel="stylesheet" href="${smart_woo_vars.smartwoo_assets_url}css/smart-woo-invoice.css" type="text/css" />`;

    // Write the HTML content and link the external stylesheet
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write(stylesheet); // Add the stylesheet
    printWindow.document.write('</head><body>');
    printWindow.document.write(invoiceArea); // Add the element content
    printWindow.document.querySelector('.sw-invoice-item-table').style.width = "80%";
    printWindow.document.write('</body></html>');
    
    // Close the document to finish writing and initiate the print function
    printWindow.document.close();
    
    // Focus the new window and print the content
    // printWindow.focus();
    printWindow.print();
    
    // Optional: close the print window after printing
    printWindow.addEventListener( 'afterprint', function() {
        // printWindow.close();
    });
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
