function smartwooPrintinvoice(){let e=document.querySelector(".sw-invoice-template");if(!e){console.error("SmartWoo: Invoice container (.sw-invoice-template) not found for printing. Cannot proceed.");return}let t=e.innerHTML,o=smart_woo_vars.smartwoo_assets_url+"css/smart-woo-invoice.css",n=`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Print Invoice</title>
            <link rel="stylesheet" href="${o}" type="text/css" media="all" />
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
                ${t}
            </div>
            <button class="sw-cls-btn-no-print">X</button>
        </body>
        </html>
    `,i=new Blob([n],{type:"text/html"}),r=URL.createObjectURL(i),a=window.open(r,"_blank");if(!a){showNotification("Invoice download failed: Please allow pop-ups to print the invoice."),URL.revokeObjectURL(r);return}a.onload=()=>{a.focus(),a.print(),URL.revokeObjectURL(r)},a.addEventListener("afterprint",()=>{a.close()}),setTimeout(()=>{let e=a.document.querySelector(".sw-cls-btn-no-print");e&&e.addEventListener("click",()=>{a.close()})},50)}async function smartwooDownloadInvoice(e){try{let t=await fetch(e);if(!t.ok)throw Error(`HTTP error! Status: ${t.status}`);let o=t.headers.get("Content-Disposition"),n="invoice.pdf";if(o){let i=/filename="([^"]+)"/i.exec(o);i&&i.length>1&&(n=i[1])}let r=await t.blob(),a=window.URL.createObjectURL(r),c=document.createElement("a");c.href=a,c.download=n,document.body.appendChild(c),c.click(),window.URL.revokeObjectURL(a),document.body.removeChild(c)}catch(l){console.error("Error downloading invoice:",l),showNotification("Failed to download invoice. Please try again.",6e3)}}document.addEventListener("DOMContentLoaded",()=>{let e=document.querySelector(".sw-invoice-template"),t=document.querySelector("#smartwoo-print-invoice-btn"),o=document.querySelector("#smartwoo-download-invoice-btn");e&&smart_woo_vars.is_account_page&&(e.style.minWidth="90%"),t&&t.addEventListener("click",smartwooPrintinvoice),o&&o.addEventListener("click",e=>{e.preventDefault();smartwooDownloadInvoice(o.getAttribute("data-package-url"))})});