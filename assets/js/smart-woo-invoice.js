/**
 * Client Invoice page Scripts.
 * @author Callistus Nwachukwu
 * @since 2.0.15 
 */
function smartwooPrintinvoice() {
    let invoiceContainer = document.querySelector('.sw-invoice-template');
    if (!invoiceContainer) {
        console.error('SmartWoo: Invoice container (.sw-invoice-template) not found for printing. Cannot proceed.');
        return;
    }

    let invoiceContentHtml = invoiceContainer.innerHTML;
    const stylesheetUrl = smart_woo_vars.smartwoo_assets_url + 'css/smart-woo-invoice.css';

    const fullPrintHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Print Invoice</title>
            <link rel="stylesheet" href="${stylesheetUrl}" type="text/css" media="all" />
            <style>
                /* Add any specific print styles or overrides here */
                .sw-invoice-item-table {
                    width: 90% !important;
                    margin: 0 auto;
                }
                .sw-cls-btn-no-print {
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    z-index: 9999;
                    font-size: 20px;
                    cursor: pointer;
                    background: #eee;
                    border: 1px solid #ccc;
                    padding: 5px 10px;
                    border-radius: 5px;
                }
                @media print {
                    .sw-cls-btn-no-print {
                        display: none !important;
                    }
                }
            </style>
        </head>
        <body>
            <div class="smartwoo-print-container">
                ${invoiceContentHtml}
            </div>
            <button class="sw-cls-btn-no-print">X</button>
        </body>
        </html>
    `;

    const htmlBlob  = new Blob( [fullPrintHtml], { type: 'text/html' } );
    const blobUrl   = URL.createObjectURL( htmlBlob );

    // Instead of opening a blank window and writing to it, we open it
    // and immediately navigate it to our Blob URL.
    let printWindow = window.open(blobUrl, '_blank');
    if (!printWindow) {
        showNotification( 'Invoice download failed: Please allow pop-ups to print the invoice.' );
        URL.revokeObjectURL(blobUrl); // Clean up the Blob URL if window fails to open
        return;
    }

    // Now, onload should fire reliably because the browser is loading
    // content from a "URL" (even if it's a Blob URL).
    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
        URL.revokeObjectURL(blobUrl);
    };

    printWindow.addEventListener( 'afterprint', () => {
        printWindow.close();
    });

    // Wait for the new window's document to be available to attach the close button listener
    // This part might still need a small delay or a DOMContentLoaded check,
    // as printWindow.onload is for the *whole window*, not just the DOM ready.
    // However, for elements directly in the HTML string, they should be available.
    // A small timeout for consistency in attaching listeners is often pragmatic.
    setTimeout(() => {
        const closeBtnInPrintWindow = printWindow.document.querySelector('.sw-cls-btn-no-print');
        if (closeBtnInPrintWindow) {
            closeBtnInPrintWindow.addEventListener('click', () => {
                printWindow.close();
            });
        }
    }, 50);
}

async function smartwooDownloadInvoice( url ) {
    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'invoice.pdf'; // Default filename.
        if (contentDisposition) {
            const matches = /filename="([^"]+)"/i.exec(contentDisposition);
            if (matches && matches.length > 1) {
                filename = matches[1];
            }
        }

        const blob      = await response.blob();
        const blobUrl   = window.URL.createObjectURL(blob);
        const           a = document.createElement('a');
        a.href          = blobUrl;
        
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(blobUrl);
        document.body.removeChild(a);
    } catch (error) {
        console.error('Error downloading invoice:', error);
        showNotification('Failed to download invoice. Please try again.', 6000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let invoiceArea = document.querySelector( '.sw-invoice-template' );
    let printBtn    = document.querySelector( '#smartwoo-print-invoice-btn' );
    let downloadBtn = document.querySelector( '#smartwoo-download-invoice-btn' );
    
    if( invoiceArea && smart_woo_vars.is_account_page ) {
        invoiceArea.style.minWidth = "90%";
    }

    if (printBtn) {
        printBtn.addEventListener("click", smartwooPrintinvoice);
    }

    if ( downloadBtn ) {
        downloadBtn.addEventListener( 'click', e => {
            e.preventDefault();
            let packageUrl = downloadBtn.getAttribute( 'data-package-url' );
            smartwooDownloadInvoice( packageUrl );
        });
    }

});
