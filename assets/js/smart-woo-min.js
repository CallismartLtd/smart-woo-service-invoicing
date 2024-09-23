function smartWooAddSpinner(e){let t=document.createElement("div");t.classList.add("loading-spinner"),t.innerHTML='<img src=" '+smart_woo_vars.wp_spinner_gif_loader+'" alt="Loading...">';let n=document.getElementById(e);return n.appendChild(t),t}function smartWooRemoveSpinner(e){e.remove()}function showNotification(e,t){let n=document.createElement("div");n.classList.add("notification"),n.innerHTML=`
    <div class="notification-content">
        <span style="float:right; cursor:pointer; font-weight:bold;" class="close-btn" onclick="this.parentElement.parentElement.remove()">&times;</span>
        <p>${e}</p>
    </div>
`,n.style.position="fixed",n.style.top="40px",n.style.left="50%",n.style.width="30%",n.style.fontWeight="bold",n.style.transform="translateX(-50%)",n.style.padding="15px",n.style.backgroundColor="#fff",n.style.color="#333",n.style.border="1px solid #ccc",n.style.borderRadius="5px",n.style.boxShadow="0 2px 5px rgba(0, 0, 0, 0.5)",n.style.zIndex="9999",n.style.textAlign="center",document.body.appendChild(n),t&&setTimeout(()=>{n.remove()},t)}function openCancelServiceDialog(e,t){var n=prompt("You can either opt out of automatic renewal of "+e+' by typing "cancel billing" or opt out of this service by typing "cancel service". This action cannot be reversed. Please note: our refund and returns policy will apply either way.\n\nPlease enter your choice:\nType "cancel service" to cancel the service\nType "cancel billing" to opt out of automatic service renewal');if(null!==n){var a=null;"cancel service"===n.toLowerCase()?a="sw_cancel_service":"cancel billing"===n.toLowerCase()&&(a="sw_cancel_billing"),null!==a?(showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_cancel_or_optout",security:smart_woo_vars.security,service_id:t,selected_action:a},success:function(){jQuery("#sw-service-quick-action").fadeIn("fast",function(){jQuery(this).text("Done!").slideDown("fast")}),location.reload()},complete:function(){hideLoadingIndicator()}})):alert('Oops! you mis-typed it. Please type "cancel service" or "cancel billing" as instructed.')}return!1}function showLoadingIndicator(){jQuery("#swloader").css("display","block"),jQuery("body").css("cursor","progress")}function hideLoadingIndicator(){jQuery("#swloader").css("display","none"),jQuery("body").css("cursor","")}function confirmEditAccount(){confirm("Are you sure you want to edit your information?")&&(window.location.href=smart_woo_vars.woo_my_account_edit)}function confirmPaymentMethods(){confirm("Are you sure you want to view your payment methods?")&&(window.location.href=smart_woo_vars.woo_payment_method_edit)}function confirmEditBilling(){confirm("Are you sure you want to edit your billing address?")&&(window.location.href=smart_woo_vars.woo_billing_eddress_edit)}function loadBillingDetails(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_billing_details",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e);var t=document.getElementById("edit-billing-address");t&&t.addEventListener("click",function(){confirmEditBilling()})},complete:function(){hideLoadingIndicator()}})}function loadMyDetails(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_my_details",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e);var t=document.getElementById("edit-account-button"),n=document.getElementById("view-payment-button");t&&t.addEventListener("click",function(){confirmEditAccount()}),n&&n.addEventListener("click",function(){confirmPaymentMethods()})},complete:function(){hideLoadingIndicator()}})}function loadAccountLogs(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_account_logs",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e)},complete:function(){hideLoadingIndicator()}})}function loadTransactionHistory(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_transaction_history",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e)},complete:function(){hideLoadingIndicator()}})}function redirectBasedOnServiceAction(e){var t,n=window.location.href;switch(e){case"upgrade":t="service_upgrade";break;case"downgrade":t="service_downgrade";break;case"buy_new":t="buy_new_service";break;default:t=""}var a=updateQueryStringParameter(n,"service_page",t);a=updateQueryStringParameter(a,"service_action",e),window.location.href=a}function updateQueryStringParameter(e,t,n){var a=RegExp("([?&])"+t+"=.*?(&|$)","i"),o=-1!==e.indexOf("?")?"&":"?";return e.match(a)?e.replace(a,"$1"+t+"="+n+"$2"):e+o+t+"="+n}function deleteProduct(e){if(confirm("Are you sure you want to delete this product?")){var t={action:"delete_sw_product",security:smart_woo_vars.security,product_id:e};jQuery.post(ajaxurl,t,function(e){e.success?(alert("Product deleted successfully!"),location.reload()):alert("Error deleting the product. Please try again.")})}}document.addEventListener("DOMContentLoaded",function(){var e=document.querySelectorAll(".sw-left-column a"),t=document.getElementById("first-display");e.forEach(function(e){e.addEventListener("click",function(n){n.preventDefault();var a=e.getAttribute("href").substring(1),o=document.getElementById(a);document.querySelectorAll(".instruction").forEach(function(e){e.style.display="none"}),t.style.display="none",o.style.display="block"})})}),jQuery(document).ready(function(e){e("#billing_cycle").on("change",function(){var n=e(this).val(),a=new Date(e("#start_date").val());if(!isNaN(a.getTime())){if("Monthly"===n){a.setDate(a.getDate()+30);var o=new Date(a);o.setDate(o.getDate()-7),e("#end_date").val(t(a)),e("#next_payment_date").val(t(o))}else if("Quarterly"===n){a.setMonth(a.getMonth()+3);var o=new Date(a);o.setDate(o.getDate()-7),e("#end_date").val(t(a)),e("#next_payment_date").val(t(o))}else if("Six Monthly"===n||"Yearly"===n){a.setMonth(a.getMonth()+("Six Monthly"===n?6:12));var o=new Date(a);o.setDate(o.getDate()-7),e("#end_date").val(t(a)),e("#next_payment_date").val(t(o))}}});function t(e){var t,n=e.getFullYear();return n+"-"+String(e.getMonth()+1).padStart(2,"0")+"-"+String(e.getDate()).padStart(2,"0")}}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-service-quick-action");e&&e.addEventListener("click",function(){var t;openCancelServiceDialog(e.dataset.serviceName,e.dataset.serviceId)})}),jQuery(document).ready(function(e){var t;e("#upload_sw_product_image").click(function(n){if(n.preventDefault(),t){t.open();return}(t=wp.media.frames.file_frame=wp.media({title:"Choose Product Image",button:{text:"insert image"},multiple:!1})).on("select",function(){var n=t.state().get("selection").first().toJSON();e("#product_image_id").val(n.id),e("#image_preview").html('<img src="'+n.url+'" style="max-width: 100%;" />')}),t.open()})}),jQuery(document).ready(function(e){function t(){e('select[name="grace_period_unit"]').val()===smart_woo_vars.never_expire_value?(e('input[name="grace_period_number"]').val(""),e('input[name="grace_period_number"]').prop("disabled",!0)):e('input[name="grace_period_number"]').prop("disabled",!1)}t(),e('select[name="grace_period_unit"]').change(function(){t()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-billing-details");e&&e.addEventListener("click",function(){loadBillingDetails()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-load-user-details");e&&e.addEventListener("click",function(){loadMyDetails()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-account-log");e&&e.addEventListener("click",function(){loadAccountLogs()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-load-transaction-history");e&&e.addEventListener("click",function(){loadTransactionHistory()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("service-action-dropdown");e&&e.addEventListener("change",function(){redirectBasedOnServiceAction(this.value)})}),jQuery(document).ready(function(e){e(document).on("click",".delete-invoice-button",function(){var t=e(this).data("invoice-id");confirm("Are you sure you want to delete this invoice?")&&(spinner=smartWooAddSpinner("sw-delete-button"),e.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"delete_invoice",invoice_id:t,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.admin_invoice_page):alert(e.data.message)},error:function(e){console.error("Error deleting invoice:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))})}),jQuery(document).ready(function(e){e(document).on("click",".delete-service-button",function(){var t=e(this).data("service-id");confirm("Are you sure you want to delete this service? All invoices and assets alocated to it will be lost forever.")&&(spinner=smartWooAddSpinner("sw-delete-button"),e.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_delete_service",service_id:t,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.sw_admin_page):alert(e.data.message)},error:function(e){alert("Error deleting service:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))})}),jQuery(document).ready(function(e){e(document).on("click",".sw-delete-product",function(){var t=e(this).data("product-id");confirm("Are you sure you want to delete this product?")&&(spinner=smartWooAddSpinner("sw-delete-button"),e.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_delete_product",product_id:t,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.sw_product_page):alert(e.data.message)},error:function(e){console.error("Error deleting product:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))})}),document.addEventListener("DOMContentLoaded",function(){for(var e=document.querySelectorAll(".sw-accordion-btn"),t=0;t<e.length;t++)e[t].addEventListener("click",function(){this.classList.toggle("active");var e=this.nextElementSibling;"block"===e.style.display?e.style.display="none":e.style.display="block"})}),jQuery(document).ready(function(e){var t=e("#generate-service-id-btn"),n=e("#swloader");t.length&&t.on("click",function(t){t.preventDefault();var a=e("#service-name").val();n.css("display","inline-block"),e.ajax({url:smart_woo_vars.ajax_url,type:"POST",dataType:"text",data:{action:"smartwoo_service_id_ajax",service_name:a,security:smart_woo_vars.security},success:function(t){n.css("display","none"),e("#generated-service-id").val(t)},error:function(e,t,a){n.css("display","none"),console.error(a)}})})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwooConfigureProduct"),t=document.querySelector(".sw-blue-button");e&&t&&e.addEventListener("submit",function(n){n.preventDefault();var a=t.textContent;t.textContent="Processing...";var o=new FormData(e);o.append("action","smartwoo_configure_product"),o.append("security",smart_woo_vars.security),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:o,processData:!1,contentType:!1,success:function(e){if(e.success&&e.data){var n=e.data.checkout;t.textContent="Product is configured, redirecting to checkout page....",window.location.href=n}else jQuery("#error-container").html(e.data.message),t.textContent=a},error:function(e,n,o){console.error(o),t.textContent=a}})})}),document.addEventListener("DOMContentLoaded",function(){var e=document.querySelector(".sw-menu-icon"),t=document.querySelector(".service-navbar");if(e){var n=e.querySelector(".dashicons-menu");e.addEventListener("click",function(){t.classList.toggle("active"),t.classList.contains("active")?(n.classList.remove("dashicons-menu"),n.classList.add("dashicons-no")):(n.classList.remove("dashicons-no"),n.classList.add("dashicons-menu"))})}}),jQuery(document).ready(function(e){var t;e("#is-smartwoo-downloadable").on("change",function(){e(this).is(":checked")?(e(".sw-assets-div").fadeIn().css("display","flex"),e(".sw-product-download-field-container").fadeIn(),e(".sw-product-download-fields").fadeIn(),e("#add-field").fadeIn()):(e(".sw-assets-div").fadeOut(),e(".sw-product-download-field-container").fadeOut(),e(".sw-product-download-fields").fadeOut(),e("#add-field").fadeOut())}),e(document).on("click",".upload_image_button",function(n){n.preventDefault();var a=e(this).siblings(".fileUrl");if(t){t.open(),t.off("select"),t.on("select",function(){var e=t.state().get("selection").first().toJSON();a.val(e.url)});return}(t=wp.media.frames.file_frame=wp.media({title:"Select a file",button:{text:"Add to asset"},multiple:!1})).on("select",function(){var e=t.state().get("selection").first().toJSON();a.val(e.url)}),t.open()}),e("#add-field").on("click",function(){var t=e(".sw-product-download-fields:first").clone();t.find("input").val(""),t.find(".upload_image_button").val("Choose file"),t.insertBefore("#add-field")}),e(document).on("click",".swremove-field",function(){e(this).closest(".sw-product-download-fields").remove()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwoo-assets-sub-nav");if(e){var t=document.getElementById("smartwoo-sub-info"),n=document.getElementById("smartwoo-sub-assets"),a=e.textContent,o=!1;e.addEventListener("click",function(i){o?(e.textContent=a,t.style.display="flex",n.style.display="none"):(e.textContent="Subscriptions",t.style.display="none",n.style.display="block"),o=!o})}}),addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwooUpdateBtn");e&&e.addEventListener("click",function(){var t=document.getElementById("smartwooNoticeDiv"),n=document.createElement("div"),a=document.createElement("p");n.className="notice notice-success is-dismissible",e.textContent="";var o=smartWooAddSpinner("smartwooUpdateBtn");jQuery.ajax({type:"GET",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_db_update",security:smart_woo_vars.security},success:function(e){a.textContent=e.success?e.data.message:"Background update started",n.appendChild(a),t.replaceWith(n)},error:function(e){var t="Error updating the database: ";e.responseJSON&&e.responseJSON.data&&e.responseJSON.data.message?t+=e.responseJSON.data.message:e.responseText?t+=e.responseText:t+=e,console.error(t)},complete:function(){smartWooRemoveSpinner(o)}})})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("more-addi-assets"),t=document.getElementById("additionalAssets"),n=document.getElementById("isExternal");if(e&&t&&(e.addEventListener("click",function(n){n.preventDefault();var a=document.createElement("div");a.classList.add("sw-additional-assets-field"),a.innerHTML=`
            <p><strong>Add More Assets</strong></p>
            <input type="text" name="add_asset_types[]" placeholder="Asset Type" />
            <input type="text" name="add_asset_names[]" placeholder="Asset Name" />
            <input type="text" name="add_asset_values[]" placeholder="Asset Value" />
            <input type="number" name="access_limits[]" class="sw-form-input" min="-1" placeholder="Limit (optional).">

            <button class="remove-field" title="Remove this field">&times;</button>
        `,t.insertBefore(a,e)}),t.addEventListener("click",function(e){if(e.target.classList.contains("remove-field")){e.preventDefault();var t=e.target.parentElement,n=e.target.dataset.removedId,a=n?confirm("This asset will be deleted from the database, click okay to continue."):0;if(n&&a){var o=smartWooAddSpinner("smartSpin");console.log(n),jQuery.ajax({type:"GET",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_asset_delete",security:smart_woo_vars.security,asset_id:n},success:function(e){e.success?(alert(e.data.message),t.remove()):alert(e.data.message)},error:function(e){var t="Error deleting asset: ";e.responseJSON&&e.responseJSON.data&&e.responseJSON.data.message?t+=e.responseJSON.data.message:e.responseText?t+=e.responseText:t+=e,console.error(t)},complete:function(){smartWooRemoveSpinner(o)}})}n||t.remove()}})),n){var a=document.getElementById("auth-token-div");n.addEventListener("change",function(e){"yes"===n.value?(a.classList.remove("smartwoo-hide"),a.classList.add("sw-form-row")):(a.classList.remove("sw-form-row"),a.classList.add("smartwoo-hide"))})}});