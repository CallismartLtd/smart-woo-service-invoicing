function renderTable(e,t,a,n,s,o){let r={},l=document.querySelector(".sw-admin-dash-body"),c=document.querySelector(".sw-search-container");smartwooRemoveTable(),addPaginationControls(c,n,s,totalItems=t.length,o);let i=document.createElement("table");i.classList.add("sw-table");let d=document.createElement("thead"),p=document.createElement("tr"),u=document.createElement("th"),h=document.createElement("input");h.type="checkbox",h.addEventListener("change",function(){m.querySelectorAll('input[type="checkbox"]').forEach(e=>{e.checked=this.checked,r[e.name]=this.checked}),y()}),u.appendChild(h),p.appendChild(u),e.forEach(e=>{let t=document.createElement("th");t.textContent=e,p.appendChild(t)}),d.appendChild(p),i.appendChild(d);let m=document.createElement("tbody");if(0===t.length){let v=document.createElement("tr"),f=document.createElement("td");f.colSpan=e.length+1,f.textContent="No service found.",f.classList.add("sw-not-found"),v.appendChild(f),m.appendChild(v)}else t.forEach((e,t)=>{let n=document.createElement("tr"),s=document.createElement("td"),o=document.createElement("input");o.type="checkbox",o.name=a[t],r[o.name]&&(o.checked=!0),o.addEventListener("change",function(){r[this.name]=this.checked,this.checked||(h.checked=!1);Array.from(m.querySelectorAll('input[type="checkbox"]')).every(e=>e.checked)&&(h.checked=!0),y()}),s.appendChild(o),n.appendChild(s),e.forEach((e,t)=>{let a=document.createElement("td");a.textContent=e,1===t&&(a.classList.add("service-id-column"),a.style.cursor="pointer",a.addEventListener("click",function(){smartwoo_service_admin_view(e)})),n.appendChild(a)}),m.appendChild(n)});function y(){let e=[];if(m.querySelectorAll('input[type="checkbox"]:checked').forEach(t=>e.push(t.name)),e.length>0)smartwooShowActionDialog(e);else{let t=document.querySelector(".sw-action-container");t&&t.remove()}}i.appendChild(m),l.appendChild(i),addPaginationControls(l,n,s,totalItems,o)}function addPaginationControls(e,t,a,n,s){let o=document.createElement("div");o.classList.add("sw-pagination-buttons"),o.style.float="right";let r=document.createElement("p");if(r.textContent=`${n} items`,o.appendChild(r),a>1){let l=a-1,c=document.createElement("a");c.classList.add("sw-pagination-button");let i=document.createElement("button");i.innerHTML='<span class="dashicons dashicons-arrow-left-alt2"></span>',c.appendChild(i),c.addEventListener("click",function(e){e.preventDefault(),fetchDashboardData(s,{paged:l})}),o.appendChild(c)}let d=document.createElement("p");if(d.textContent=`${a} of ${t}`,o.appendChild(d),a<t){let p=a+1,u=document.createElement("a");u.classList.add("sw-pagination-button");let h=document.createElement("button");h.innerHTML='<span class="dashicons dashicons-arrow-right-alt2"></span>',u.appendChild(h),u.addEventListener("click",function(e){e.preventDefault(),fetchDashboardData(s,{paged:p})}),o.appendChild(u)}e.appendChild(o)}function smartwoo_service_admin_view(e){let t=new URL(smartwoo_admin_vars.sw_admin_page);t.searchParams.set("action","view-service"),t.searchParams.set("service_id",e),t.searchParams.set("tab","details"),window.location.href=t.href}function smartwooRemoveTable(){let e=document.querySelector(".sw-table"),t=document.querySelectorAll(".sw-pagination-buttons");e&&(jQuery(".sw-table").fadeOut(),setTimeout(()=>{e.remove()},1e3),t&&t.forEach(e=>{e.remove()}))}function smartwooPostBulkAction(e,t=[]){if("delete"!==e||confirm("Warning: You are about to delete the selected service"+(t.length>1?"s":"")+", along with all related invoices and assets. Click OK to confirm."))showLoadingIndicator(),jQuery.ajax({type:"POST",url:smartwoo_admin_vars.ajax_url,data:{action:"smartwoo_dashboard_bulk_action",service_ids:t,real_action:e,security:smartwoo_admin_vars.security},success:function(e){e.success?(showNotification(e.data.message),setTimeout(()=>{window.location.reload()},2e3)):showNotification("Oops! "+e.data.message)},error:function(e,t,a){console.error("Bulk action failed:",a),showNotification("An error occured, please inspect console.")},complete:function(){let e=document.querySelector(".sw-action-container");e&&e.remove(),hideLoadingIndicator()}})}function smartwooShowActionDialog(e){let t=document.querySelector(".sw-action-container");t&&t.remove();let a=document.createElement("div");a.classList.add("sw-action-container"),a.innerHTML=`
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
      <input type="hidden" name="service_ids" value="${e}"/>
    `;let n=document.querySelector(".sw-table");n.prepend(a),jQuery(".sw-action-container").fadeIn().css("display","flex"),a.addEventListener("change",()=>{let t=a.querySelector("select").value,n=document.querySelector(".sw-action-btn");n&&n.remove();let s=document.createElement("button");s.classList.add("sw-action-btn"),s.textContent="Apply Action",s.style.backgroundColor="#f1f1f1f1",s.style.marginLeft="-2px",s.style.height="30px",s.style.border="solid .5px blue","Choose Action"!==t&&(a.append(s),jQuery(s).fadeIn()),s&&s.addEventListener("click",()=>{smartwooPostBulkAction(t,e)})})}let fetchIntervals={};function fetchServiceCount(e,t,a){let n=document.querySelectorAll(".sw-dash-content");return jQuery.ajax({type:"GET",url:smartwoo_admin_vars.ajax_url,data:{action:"smartwoo_dashboard",real_action:t,security:smartwoo_admin_vars.security},success:function(s){if(s.success){smartwoo_clear_dash_content(e);let o=document.createElement("div"),r=document.createElement("h2"),l=document.createElement("span");o.classList.add("sw-dash-count"),r.textContent=s.data[t],l.textContent=a,o.appendChild(r),o.appendChild(l),n[e].append(o),jQuery(".sw-dash-count").fadeIn().css("display","flex")}else console.log(s),smartwooAddRetryBtn(e,t,a)},error:function(n){let s="Error fetching data: ";n.responseJSON&&n.responseJSON.data&&n.responseJSON.data.message?s+=n.responseJSON.data.message:n.responseText?s+=n.responseText:s+=n,console.error(s),smartwooAddRetryBtn(e,t,a)},complete:function(){fetchIntervals[e]&&clearInterval(fetchIntervals[e]),fetchIntervals[e]=setInterval(()=>{fetchServiceCount(e,t,a)},9e5)}})}function smartwooAddRetryBtn(e,t,a){let n=document.querySelectorAll(".sw-dash-content");smartwoo_clear_dash_content(e);let s=document.createElement("div");s.classList.add("sw-dash-count");let o=document.createElement("h3");o.textContent="Error Occurred",s.append(o);let r=document.createElement("button");r.classList.add("sw-red-button"),r.textContent="retry",r.setAttribute("onclick",`fetchServiceCount(${e}, '${t}', '${a}')`),s.append(r),n[e].append(s),jQuery(".sw-dash-count").fadeIn().css("display","flex"),r.addEventListener("click",()=>{r.style.cursor="progress"})}function smartwoo_clear_dash_content(e){let t=document.querySelectorAll(".sw-dash-content");t&&(t[e].innerHTML="")}function fetchDashboardData(e,t={}){let a=document.querySelector(".sw-dash-content-container");switch(showLoadingIndicator(),e){case 0:realAction="all_services_table";break;case 1:realAction="all_pending_services_table";break;case 2:realAction="all_active_services_table";break;case 3:realAction="all_active_nr_services_table";break;case 4:realAction="all_due_services_table";break;case 5:realAction="all_on_grace_services_table";break;case 6:realAction="all_expired_services_table";break;case 7:realAction="all_cancelled_services_table";break;case 8:realAction="all_suspended_services_table";break;default:realAction="sw_search"}if("all_pending_services_table"===realAction){window.location.href=smartwoo_admin_vars.admin_order_page;return}let n=t.limit||10,s=t.paged||1;jQuery.ajax({type:"GET",url:smartwoo_admin_vars.ajax_url,data:{action:"smartwoo_dashboard",security:smartwoo_admin_vars.security,real_action:realAction,limit:n,paged:s,search_term:"sw_search"===realAction?t.search:""},success:function(t){if(t.success){let a=t.data.all_services_table,n=a.table_header,s=a.table_body,o=a.row_names,r;renderTable(n,s,o,a.total_pages,a.current_page,e)}},error:function(e){var t="Error fetching data: ";e.responseJSON&&e.responseJSON.data&&e.responseJSON.data.message?t+=e.responseJSON.data.message:e.responseText?t+=e.responseText:t+=e,console.error(t)},complete:function(){a&&(a.style.display="none"),hideLoadingIndicator()}})}function smartwooDeleteInvoice(e){confirm("Do you realy want to delete this invoice? This action cannot be reversed!")&&(spinner=smartWooAddSpinner("sw-delete-button"),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"delete_invoice",invoice_id:e,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.admin_invoice_page):alert(e.data.message)},error:function(e){console.error("Error deleting invoice:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))}function smartwooProBntAction(e){let t=smartWooAddSpinner("sw-loader");jQuery.ajax({type:"GET",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_pro_button_action",security:smart_woo_vars.security,real_action:e},success(e){if(e.success){let t=document.querySelector(".sw-pro-sell-content");t.innerHTML="";let a=document.createElement("span");a.className="dashicons dashicons-yes-alt";let n=document.createElement("h2");n.textContent=e.data.message,t.append(a),t.append(n),setInterval(()=>a.classList.toggle("loaded"),1e3),setTimeout(()=>{jQuery(t.parentElement).fadeOut(()=>t.remove())},3e3)}else showNotification(e.data.message,3e3),window.location.reload()},error:function(e){let t="Error fetching data: ";e.responseJSON&&e.responseJSON.data&&e.responseJSON.data.message?t+=e.responseJSON.data.message:e.responseText?t+=e.responseText:t+=e,console.error(t)},complete(){smartWooRemoveSpinner(t)}})}function smartwooDeleteProduct(e){confirm("Are you sure you want to permanently delete this product? This action cannot be reversed!")&&(spinner=smartWooAddSpinner("sw-delete-button"),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_delete_product",product_id:e,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.sw_product_page):alert(e.data.message)},error:function(e){console.error("Error deleting product:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))}function smartwooDeleteService(e){confirm("Are you sure you want to delete this service? All invoices and assets alocated to it will be lost forever.")&&(spinner=smartWooAddSpinner("sw-delete-button"),jQuery.ajax({type:"POST",url:smart_woo_vars.ajax_url,data:{action:"smartwoo_delete_service",service_id:e,security:smart_woo_vars.security},success:function(e){e.success?(alert(e.data.message),window.location.href=smart_woo_vars.sw_admin_page):alert(e.data.message)},error:function(e){alert("Error deleting service:",e)},complete:function(){smartWooRemoveSpinner(spinner)}}))}function smartwoo_pro_ad(e,t){let a=document.querySelector(".sw-pro-div");a&&a.remove();let n=document.querySelector(".inv-settings-form"),s=document.createElement("div");s.classList.add("sw-pro-div");let o=document.createElement("span");o.classList.add("dashicons","dashicons-dismiss"),o.setAttribute("title","close"),o.style.position="absolute",o.style.right="5px",o.style.top="2px",o.style.color="red",o.style.cursor="pointer";let r=document.createElement("h2");r.textContent=e;let l=document.createElement("div");l.classList.add("sw-pro-body"),l.innerHTML=t;let c=document.createElement("span");c.classList.add("sw-pro-action-btn"),c.textContent="Activate Pro Feature",s.append(r),s.append(o),s.append(l),s.append(c),n.prepend(s),jQuery(s).fadeIn("slow").css("display","flex"),o.addEventListener("click",()=>{s.remove()}),c.addEventListener("click",()=>{window.open(smartwoo_admin_vars.smartwoo_pro_page,"_blank")})}document.addEventListener("DOMContentLoaded",()=>{let e=document.querySelector(".sw-dash-content-container"),t=document.querySelectorAll(".sw-dash-content"),a=document.getElementById("dashOrderBtn"),n=document.getElementById("dashAddNew"),s=document.getElementById("dashInvoicesBtn"),o=document.getElementById("dashProductBtn"),r=document.getElementById("dashSettingsBtn"),l=document.querySelectorAll(".sw-upgrade-to-pro"),c=document.getElementById("sw_service_search"),i=document.getElementById("swSearchBtn"),d=document.getElementById("search-notification"),p=document.querySelector(".sw-admin-menu-icon"),u=document.querySelectorAll(".delete-invoice-button"),h=document.querySelectorAll(".sw-delete-product"),m=document.querySelector(".delete-service-button"),v=document.querySelector(".sw-admin-dash-header"),f=document.querySelectorAll(".sw-edit-mail-nopro"),y=document.querySelectorAll(".sw-checkboxes"),w=document.getElementById("sw-hide"),E=document.querySelectorAll(".smartwoo-prevent-default"),b=document.querySelector("#smartwoo-pro-remind-later"),g=document.querySelector("#smartwoo-pro-dismiss-fornow");if(e){let S=document.getElementById("contextual-help-link-wrap"),L=document.getElementById("contextual-help-wrap"),x=document.getElementById("contextual-help-columns");S&&(S.style.zIndex="9999",L.style.zIndex="9999",S.style.top="110px",S.style.right="1px",x.style.backgroundColor="#f9f9f9",x.style.border="solid blue 1px",S.style.position="absolute");for(let k=0;k<8;k++)e.append(t[0].cloneNode(!0));let C=[fetchServiceCount(0,"total_services","All Services"),fetchServiceCount(1,"total_pending_services","Pending Service Orders"),fetchServiceCount(2,"total_active_services","Active Services"),fetchServiceCount(3,"total_active_nr_services","Active No Renewal"),fetchServiceCount(4,"total_due_services","Due for Renewal"),fetchServiceCount(5,"total_on_grace_services","Grace Period"),fetchServiceCount(6,"total_expired_services","Expired Services"),fetchServiceCount(7,"total_cancelled_services","Cancelled Services"),fetchServiceCount(8,"total_suspended_services","Suspended Services")];Promise.allSettled(C).finally(()=>{document.dispatchEvent(new CustomEvent("SmartWooDashboardLoaded"))})}if(n&&n.addEventListener("click",()=>{window.location.href=smartwoo_admin_vars.new_service_page}),a&&a.addEventListener("click",()=>{window.location.href=smartwoo_admin_vars.admin_order_page}),s&&s.addEventListener("click",()=>{window.location.href=smartwoo_admin_vars.admin_invoice_page}),o&&o.addEventListener("click",()=>{window.location.href=smartwoo_admin_vars.sw_product_page}),r&&r.addEventListener("click",()=>{window.location.href=smartwoo_admin_vars.sw_options_page}),l&&l.forEach(e=>{e.addEventListener("click",()=>{window.open(smartwoo_admin_vars.smartwoo_pro_page,"_blank")})}),c&&i&&d&&(c.addEventListener("input",()=>{let e=c.value.trim();0==e.length?i.style.cursor="not-allowed":(i.style.cursor="pointer",d.style.display="none")}),i.addEventListener("click",()=>{let e=c.value.trim();e.length>0?(fetchDashboardData("sw_search",{search:e}),d.style.display="none"):(d.textContent="Search field cannot be empty.",d.style.display="block"),d.addEventListener("click",()=>{d.style.display="none"})})),p){let A=!1;p.addEventListener("click",()=>{let e=document.querySelector(".sw-admin-dash-nav");A?jQuery(e).fadeIn().css("display","none"):jQuery(e).fadeIn().css("display","flex"),A=!A})}if(u&&0!==u.length&&u.forEach(e=>{let t=e.parentElement.querySelectorAll("a button"),a=e.getAttribute("data-invoice-id");e.classList.add("sw-icon-button-admin"),t.forEach(e=>{e.classList.add("sw-icon-button-admin")}),e.addEventListener("click",()=>{smartwooDeleteInvoice(a)})}),h&&0!==h.length&&h.forEach(e=>{let t=e.parentElement.querySelectorAll("a button"),a=e.getAttribute("data-product-id");e.classList.add("sw-icon-button-admin"),t.forEach(e=>{e.classList.add("sw-icon-button-admin")}),e.addEventListener("click",()=>{smartwooDeleteProduct(a)})}),m){let $=m.parentElement.querySelectorAll("a button"),q=m.getAttribute("data-service-id");$.forEach(e=>{e.classList.add("sw-icon-button-admin")}),m.classList.add("sw-icon-button-admin"),m.addEventListener("click",()=>{smartwooDeleteService(q)})}v&&window.innerWidth<=600&&document.addEventListener("scroll",()=>{window.scrollY>0?(v.style.top="0",v.style.padding="-5px"):v.style.top="20px"}),f&&f.forEach(e=>{e.addEventListener("click",()=>{smartwoo_pro_ad("Email Template Edit","Email template editing is exclusively available in Smart Woo Pro")})}),y&&y.forEach(e=>{e.addEventListener("mouseover",()=>{e.checked?e.setAttribute("title","disable"):e.setAttribute("title","enable")})}),w&&(w.style.cursor="pointer",w.addEventListener("click",e=>{e.preventDefault(),jQuery(w.parentElement).fadeOut()})),E&&E.forEach(e=>{let t=null;e.addEventListener("click",a=>{a.preventDefault(),(t=window.open("","_blank")).location.href=e.getAttribute("href"),e.disabled=!0;let n=()=>{t&&t.closed&&(e.disabled=!1,e.style.cursor="pointer",document.removeEventListener("scroll",n))};document.addEventListener("scroll",n),e.disabled&&(e.style.cursor="not-allowed")})}),b&&b.addEventListener("click",()=>{smartwooProBntAction("remind_later")}),g&&g.addEventListener("click",()=>{smartwooProBntAction("dismiss_fornow")})}),document.addEventListener("SmartWooDashboardLoaded",()=>{let e=document.querySelectorAll(".sw-dash-content"),t=document.getElementById("dashboardBtn"),a=document.querySelector(".sw-dash-content-container"),n=document.querySelector(".sw-dash-pro-sell-bg");e.forEach((e,t)=>{e.addEventListener("click",e=>{!e.target.matches(".sw-red-button")&&(smartwooRemoveTable(),fetchDashboardData(t))})}),t.addEventListener("click",()=>{smartwooRemoveTable(),jQuery(a).fadeIn().css("display","flex")}),n&&jQuery(n).fadeIn()});