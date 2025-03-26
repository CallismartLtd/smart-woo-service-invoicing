function smartWooAddSpinner(e,t=!1){let n=t?smart_woo_vars.wp_spinner_gif_2x:smart_woo_vars.wp_spinner_gif,o=document.createElement("div");o.classList.add("loading-spinner"),o.innerHTML='<img src=" '+n+'" alt="Loading...">';let s=document.getElementById(e);return s.appendChild(o),s.parentElement.style.cursor="progress",s.style.display="block",o}function smartWooRemoveSpinner(e){e.parentElement.parentElement.style.cursor="",e.remove()}function showNotification(e,t=1e3){let n=document.createElement("div");n.classList.add("notification"),n.innerHTML=`
    <div class="notification-content">
        <span style="float:right; cursor:pointer; font-weight:bold; color: red;" class="dashicons dashicons-dismiss" onclick="this.parentElement.parentElement.remove()"></span>
        <p>${e}</p>
    </div>
`,n.style.position="fixed",n.style.top="40px",n.style.left="50%",n.style.width="30%",n.style.fontWeight="bold",n.style.transform="translateX(-50%)",n.style.padding="15px",n.style.backgroundColor="#fff",n.style.color="#333",n.style.border="1px solid #ccc",n.style.borderRadius="5px",n.style.boxShadow="0 2px 5px rgba(0, 0, 0, 0.5)",n.style.zIndex="9999",n.style.textAlign="center",document.body.appendChild(n),t&&setTimeout(()=>{n.remove()},t)}function openCancelServiceDialog(e,t){var n=prompt("You can either opt out of automatic renewal of "+e+' by typing "cancel billing" or opt out of this service by typing "cancel service". This action cannot be reversed. Please note: our refund and returns policy will apply either way.\n\nPlease enter your choice:\nType "cancel service" to cancel the service\nType "cancel billing" to opt out of automatic service renewal');if(null!==n){var o=null;"cancel service"===n.toLowerCase()?o="sw_cancel_service":"cancel billing"===n.toLowerCase()&&(o="sw_cancel_billing"),null!==o?(showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_cancel_or_optout",security:smart_woo_vars.security,service_id:t,selected_action:o},success:function(){jQuery("#sw-service-quick-action").fadeIn("fast",function(){jQuery(this).text("Done!").slideDown("fast")}),location.reload()},complete:function(){hideLoadingIndicator()}})):alert('Oops! you mis-typed it. Please type "cancel service" or "cancel billing" as instructed.')}return!1}function showLoadingIndicator(){jQuery("#swloader").css("display","block"),jQuery("body").css("cursor","progress")}function hideLoadingIndicator(){jQuery("#swloader").css("display","none"),jQuery("body").css("cursor","")}function confirmEditAccount(){confirm("Are you sure you want to edit your information?")&&(window.location.href=smart_woo_vars.woo_my_account_edit)}function confirmPaymentMethods(){confirm("Are you sure you want to view your payment methods?")&&(window.location.href=smart_woo_vars.woo_payment_method_edit)}function confirmEditBilling(){confirm("Are you sure you want to edit your billing address?")&&(window.location.href=smart_woo_vars.woo_billing_eddress_edit)}function loadBillingDetails(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_billing_details",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e);var t=document.getElementById("edit-billing-address");t&&t.addEventListener("click",function(){confirmEditBilling()})},complete:function(){hideLoadingIndicator()}})}function loadMyDetails(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_my_details",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e);var t=document.getElementById("edit-account-button"),n=document.getElementById("view-payment-button");t&&t.addEventListener("click",function(){confirmEditAccount()}),n&&n.addEventListener("click",function(){confirmPaymentMethods()})},complete:function(){hideLoadingIndicator()}})}function loadAccountLogs(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_account_logs",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e)},complete:function(){hideLoadingIndicator()}})}function loadTransactionHistory(){showLoadingIndicator(),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"load_transaction_history",security:smart_woo_vars.security},success:function(e){jQuery("#ajax-content-container").html(e)},complete:function(){hideLoadingIndicator()}})}function smartwoo_ajax_logout(){let e=document.querySelector(".smartwoo-logout-contaner"),t=document.createElement("div");t.id="spinnerDiv",e.appendChild(t);let n=smartWooAddSpinner("spinnerDiv");jQuery.ajax({type:"GET",url:smart_woo_vars.ajax_url,data:{security:smart_woo_vars.security,action:"smartwoo_ajax_logout"},complete:function(){n.remove(),window.location.reload()}})}document.addEventListener("DOMContentLoaded",function(){var e=document.querySelectorAll(".sw-left-column a"),t=document.getElementById("first-display");e.forEach(function(e){e.addEventListener("click",function(n){n.preventDefault();var o=e.getAttribute("href").substring(1),s=document.getElementById(o);document.querySelectorAll(".instruction").forEach(function(e){e.style.display="none"}),t.style.display="none",s.style.display="block"})})}),document.addEventListener("DOMContentLoaded",function(){let e=document.getElementById("sw-service-quick-action"),t=document.getElementById("sw-forgot-pwd-btn");e&&e.addEventListener("click",function(){let t;openCancelServiceDialog(e.dataset.serviceName,e.dataset.serviceId)}),t&&t.addEventListener("click",()=>{let e=document.querySelector(".sw-notice").querySelector("p"),t=document.querySelector("#sw-login-btn");e.textContent="Password Reset",t.textContent="Reset Password";let n=document.querySelector("#sw-user-login"),o=document.querySelector("#sw-user-password"),s=document.querySelector("#remember_me");n&&n.parentElement.remove(),o&&o.parentElement.remove(),s&&s.parentElement.remove();let a=document.querySelector(".smartwoo-login-form-notice"),i=document.createElement("div"),r=document.createElement("label"),c=document.createElement("input"),l=this.documentElement.querySelector("#sw-error-div");i.classList.add("smartwoo-login-form-body"),r.classList.add("smartwoo-login-form-label"),r.setAttribute("for","sw-user-login"),r.textContent="Email Address",c.setAttribute("type","text"),c.setAttribute("id","sw-user-login"),c.setAttribute("class","smartwoo-login-input"),c.setAttribute("name","user_login"),i.appendChild(r),i.appendChild(c),a.append(i),l.innerHTML='Enter your email address to request a password reset or you can <a id="sw-login-instead">Login</a> instead.',(loginInstead=document.getElementById("sw-login-instead"))&&loginInstead.addEventListener("click",()=>{window.location.reload()}),t.addEventListener("click",async n=>{if(n.preventDefault(),!c.value.length){showNotification("Email should not be empty");return}let o=new URL(smart_woo_vars.ajax_url);o.searchParams.set("action","smartwoo_password_reset"),o.searchParams.set("user_login",c.value),o.searchParams.set("security",smart_woo_vars.security),e.innerHTML='Password Reset processed <span class="dashicons dashicons-yes-alt" style="color: green; font-size: 25px;"></span>',i.remove(),l.innerHTML="A password reset email will be sent to the account if it exists.",t.remove();try{let s=await fetch(o,{method:"GET"});if(!s.ok)throw Error(`Response status: ${s.status}`)}catch{}})})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("sw-billing-details");e&&e.addEventListener("click",function(){loadBillingDetails()})}),document.addEventListener("DOMContentLoaded",function(){let e=document.getElementById("sw-load-user-details");e&&e.addEventListener("click",function(){loadMyDetails()});let t=document.getElementById("sw-account-log");t&&t.addEventListener("click",function(){loadAccountLogs()});let n=document.getElementById("sw-load-transaction-history");n&&n.addEventListener("click",function(){loadTransactionHistory()})}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwooConfigureProduct"),t=document.querySelector(".sw-blue-button");e&&t&&e.addEventListener("submit",function(n){n.preventDefault();var o=t.textContent;t.textContent="Processing...",t.disabled=!0;var s=new FormData(e);s.append("action","smartwoo_configure_product"),s.append("security",smart_woo_vars.security),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:s,processData:!1,contentType:!1,success:function(e){if(e.success&&e.data){var n=e.data.checkout;t.textContent="Product is configured, redirecting to checkout page....",window.location.href=n}else jQuery("#error-container").html(e.data.message),t.textContent=o},error:function(e,n,s){console.error(s),t.textContent=o}})})}),document.addEventListener("DOMContentLoaded",function(){let e=document.querySelector(".sw-menu-icon"),t=document.querySelector(".service-navbar"),n=document.querySelector(".smart-woo-logout"),o=document.getElementById("smartwoo-login-form-visible"),s=document.getElementById("smartwoo-login-form-invisible"),a=document.getElementById("sw-user-password"),i=document.querySelectorAll(".sw-admin-service-assets-button, .sw-client-service-assets-button");if(e){var r=e.querySelector(".dashicons-menu");e.addEventListener("click",function(){t.classList.toggle("active"),t.classList.contains("active")?(r.classList.remove("dashicons-menu"),r.classList.add("dashicons-no")):(r.classList.remove("dashicons-no"),r.classList.add("dashicons-menu"))})}if(n){let c=!1;n.addEventListener("click",function(){let e=document.createElement("div");e.classList.add("smartwoo-logout-frame");let n=document.createElement("div");n.classList.add("smartwoo-logout-contaner"),e.append(n);let o=document.createElement("p");o.textContent="Are sure you want to logout?",n.append(o);let s=document.createElement("div");s.classList.add("smartwoo-logout-btn-container");let a=document.createElement("button");a.classList.add("sw-blue-button"),a.innerHTML='<span class="dashicons dashicons-yes"></span>';let i=document.createElement("button");i.classList.add("sw-red-button"),i.innerHTML='<span class="dashicons dashicons-no-alt"></span>',s.append(i,a),n.append(s),c?(jQuery(".smartwoo-logout-frame").fadeOut(),setTimeout(()=>{document.querySelector(".smartwoo-logout-frame").remove()},200)):(t.insertAdjacentElement("afterend",e),jQuery(e).fadeIn().css("display","block")),c=!c,i.addEventListener("click",()=>{jQuery(".smartwoo-logout-frame").fadeOut(),setTimeout(()=>{document.querySelector(".smartwoo-logout-frame").remove()},200),c=!c}),a.addEventListener("click",smartwoo_ajax_logout)})}if(o&&s&&a&&(o.addEventListener("click",()=>{o.style.display="none",s.style.display="block",a.setAttribute("type","text")}),s.addEventListener("click",()=>{s.style.display="none",o.style.display="block",a.setAttribute("type","password")})),i.length){let l=document.querySelectorAll(".sw-admin-assets-body-content, .sw-client-assets-body-content"),d=()=>{i.forEach(e=>{e.classList.remove("active")}),l.forEach(e=>{e.classList.add("smartwoo-hide")})};i.forEach((e,t)=>{e.addEventListener("click",e=>{d(),e.target.classList.contains("active")||e.target.classList.add("active"),l[t].classList.toggle("smartwoo-hide")})})}}),document.addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwoo-assets-sub-nav");if(e){var t=document.getElementById("smartwoo-sub-info"),n=document.getElementById("smartwoo-sub-assets"),o=e.innerHTML,s=!1;e.addEventListener("click",function(a){s?(e.innerHTML=o,jQuery(t).fadeIn().css("display","flex"),n.style.display="none"):(e.innerHTML='<span class="dashicons dashicons-info-outline"></span> Sub Info',t.style.display="none",jQuery(n).fadeIn().css("display","block")),s=!s})}}),addEventListener("DOMContentLoaded",function(){var e=document.getElementById("smartwooUpdateBtn");e&&e.addEventListener("click",function(){var t=document.getElementById("smartwooNoticeDiv"),n=document.createElement("div"),o=document.createElement("p");n.className="notice notice-success is-dismissible",e.textContent="";var s=smartWooAddSpinner("smartwooUpdateBtn");jQuery.ajax({type:"GET",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_db_update",security:smart_woo_vars.security},success:function(e){o.textContent=e.success?e.data.message:"Background update started",n.appendChild(o),t.replaceWith(n)},error:function(e){var t="Error updating the database: ";e.responseJSON&&e.responseJSON.data&&e.responseJSON.data.message?t+=e.responseJSON.data.message:e.responseText?t+=e.responseText:t+=e,console.error(t)},complete:function(){smartWooRemoveSpinner(s)}})})});