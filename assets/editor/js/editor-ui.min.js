async function smartwooAssetEditorOpenMediaLibrary(t={}){let e=Object.assign({},{title:"Select Media",buttonText:"Insert Media",multiple:!1,type:"image",uploadedTo:null},t);return new Promise(t=>{let a=wp.media({title:e.title,button:{text:e.buttonText},multiple:e.multiple,library:{type:e.type,uploadedTo:e.uploadedTo}});a.on("select",()=>{let e=a.state().get("selection").toJSON();t(e)}),a.on("close",()=>{let e=a.state().get("selection");e&&e.length||t([])}),a.open()})}class SmartWooEditor{static tinyMCE=null;static isLoaded=!1;static loadingPromise=null;static editors=[];constructor(t=".smartwoo-asset-editor-ui",e={}){this.selector=t,this.userConfig=e,SmartWooEditor.observeRemovals()}static loadTinyMCEScript(t){return this.isLoaded?Promise.resolve(this.tinyMCE):(this.loadingPromise||(this.loadingPromise=new Promise((e,a)=>{let o=document.createElement("script");o.src=t,o.onload=()=>{SmartWooEditor.tinyMCE=window.tinymce,SmartWooEditor.tinyMCE.baseURL=`${smart_woo_vars.smartwoo_assets_url}editor/tinymce/`,this.isLoaded=!0,e(this.tinyMCE)},o.onerror=()=>a(Error("Failed to load TinyMCE")),document.body.appendChild(o)})),this.loadingPromise)}static getAllowedElements(){return"a[href|target|title|rel|class|style|data-*|aria-*|download],abbr[title|class|style|data-*|aria-*],acronym[title|class|style|data-*|aria-*],b[class|style|data-*|aria-*],blockquote[cite|class|style|data-*|aria-*],br[class|style|data-*|aria-*],code[class|style|data-*|aria-*],div[id|class|style|title|data-*|aria-*|draggable|contenteditable],em[class|style|data-*|aria-*],h1[class|style|data-*|aria-*],h2[class|style|data-*|aria-*],h3[class|style|data-*|aria-*|contenteditable],h4[class|style|data-*|aria-*],h5[class|style|data-*|aria-*],h6[class|style|data-*|aria-*],hr[class|style|data-*|aria-*],i[class|style|data-*|aria-*],iframe[src|width|height|frameborder|allowfullscreen|class|style|data-*|aria-*],img[src|alt|title|width|height|class|style|data-*|aria-*|draggable|contenteditable],li[class|style|title|data-*|aria-*|contenteditable|draggable],ol[class|style|title|data-*|aria-*|contenteditable],ul[class|style|title|data-*|aria-*|contenteditable],p[class|style|title|data-*|aria-*|contenteditable],pre[class|style|title|data-*|aria-*],section[class|style|data-*|aria-*|contenteditable],article[class|style|data-*|aria-*|contenteditable],small[class|style|data-*|aria-*],span[class|style|title|data-*|aria-*|contenteditable],strong[class|style|data-*|aria-*],sub[class|style|data-*|aria-*],sup[class|style|data-*|aria-*],table[border|cellspacing|cellpadding|class|style|data-*|aria-*],tbody[class|style|data-*|aria-*],thead[class|style|data-*|aria-*],tfoot[class|style|data-*|aria-*],tr[class|style|data-*|aria-*],td[colspan|rowspan|class|style|data-*|aria-*],th[colspan|rowspan|scope|class|style|data-*|aria-*],time[datetime|class|style|data-*|aria-*],video[src|poster|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable],audio[src|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable],svg[*],path[*],g[*],use[*]"}static cleanEditorContent=t=>{let e=new DOMParser,a=e.parseFromString(t.content,"text/html"),o=a.body,s=["draggable","contenteditable"],l=[/cursor:\s*move;?/gi,/user-select:\s*[^;]+;?/gi,/pointer-events:\s*[^;]+;?/gi];s.forEach(t=>{o.querySelectorAll(`[${t}]`).forEach(e=>{e.removeAttribute(t)})}),o.querySelectorAll(".smartwoo-replace-image, .drag-handle, .smartwoo-add-image, .editor-only, .smartwoo-add-to-playlist, .smartwoo-image-actions").forEach(t=>t.remove()),o.querySelectorAll("[style]").forEach(t=>{let e=t.getAttribute("style");e&&(l.forEach(t=>{e=e.replace(t,"")}),t.setAttribute("style",e.trim()))}),o.querySelectorAll(".smartwoo-video-player-container").forEach(t=>{if(t.querySelector(".smartwoo-video-player__frame")?.classList.remove("is-hovered","is-paused","is-portrait"),t.querySelector(".smartwoo-video-player__frame")?.removeAttribute("style"),t.querySelector(".smartwoo-play")?.removeAttribute("style"),t.querySelector(".smartwoo-pause")?.setAttribute("style","display: none"),!t.querySelector(".smartwoo-video-nowplaying-info .smartwoo-video-playlist-toggle")){let e=document.createElement("span");e.className="dashicons dashicons-playlist-video smartwoo-video-playlist-toggle",t.querySelector(".smartwoo-video-nowplaying-info")?.appendChild(e)}if(!t.querySelector(".smartwoo-video-player-right .smartwoo-video-playlist-toggle")){let a=document.createElement("span");a.className="dashicons dashicons-no smartwoo-video-playlist-toggle",t.querySelector(".smartwoo-video-player-right").prepend(a)}}),o.querySelectorAll(".smartwoo-audio-playlist").forEach(t=>{t.classList.remove("playlist-active"),t.querySelector(".smartwoo-play")?.removeAttribute("style"),t.querySelector(".smartwoo-pause")?.setAttribute("style","display: none"),t.querySelector(".smartwoo-volume-toggle").className="dashicons dashicons-controls-volumeon smartwoo-volume-toggle"}),o.querySelectorAll(".smartwoo-gallery").forEach(t=>{t.querySelectorAll(".smartwoo-gallery-item").forEach(t=>t.classList.remove("dragging")),t.querySelectorAll("img").forEach(t=>t.setAttribute("loading","lazy"))}),t.content=o.innerHTML};async init(){let t=await SmartWooEditor.loadTinyMCEScript(`${smart_woo_vars.smartwoo_assets_url}editor/tinymce/tinymce.min.js`),e={selector:this.selector,skin:"oxide",branding:!1,license_key:"gpl",menubar:"file edit insert format table",plugins:"lists link image media table code preview fullscreen autosave wordcount searchreplace visualblocks insertdatetime emoticons",toolbar:"add_media_button | styles | alignleft aligncenter alignjustify alignright bullist numlist outdent indent | forecolor backcolor | code fullscreen preview | undo redo",height:600,relative_urls:!1,remove_script_host:!1,promotion:!1,content_css:[smart_woo_vars.dashicons_asset_url,smart_woo_vars.editor_css_url,smart_woo_vars.subscription_asset_url],extended_valid_elements:SmartWooEditor.getAllowedElements(),valid_children:"+div[div|span],+span[span|div]",font_formats:"Inter=Inter, sans-serif; Arial=Arial, Helvetica, sans-serif; Verdana=Verdana, Geneva, sans-serif; Tahoma=Tahoma, Geneva, sans-serif; Trebuchet MS=Trebuchet MS, Helvetica, sans-serif; Times New Roman=Times New Roman, Times, serif; Georgia=Georgia, serif; Palatino Linotype=Palatino Linotype, Palatino, serif; Courier New=Courier New, Courier, monospace",toolbar_mode:"sliding",content_style:'body { font-family: "Inter", sans-serif; font-size: 16px; }',setup(t){t.ui.registry.addButton("add_media_button",{text:"Collection",icon:"gallery",tooltip:"Create a collection of media",onAction:()=>SmartWooEditor.CollectionManager(t)}),t.on("GetContent",SmartWooEditor.cleanEditorContent),t.on("init",()=>SmartWooEditor.decorateEditor(t))}},a=await t.init(Object.assign({},e,this.userConfig));return SmartWooEditor.editors.push(...a),1===a.length?a[0]:a}static saveAll(){Array.isArray(SmartWooEditor.editors)&&SmartWooEditor.editors.length&&SmartWooEditor.editors.forEach(t=>{t&&"function"==typeof t.save&&t.save()})}static decorateEditor=t=>{let e=t.getBody();e.querySelectorAll(".smartwoo-video-player-container").forEach(e=>SmartWooEditor.restoreVideoPlaylistBlock(e,t)),e.querySelectorAll(".smartwoo-audio-playlist").forEach(e=>SmartWooEditor.restoreAudioPlaylistBlock(e,t)),e.querySelectorAll(".smartwoo-gallery").forEach(e=>SmartWooEditor.restoreImageGalleryBlock(e,t))};static observeRemovals(){!this._observer&&(this._observer=new MutationObserver(t=>{t.forEach(t=>{t.removedNodes.forEach(t=>{1===t.nodeType&&(t.matches&&t.matches(".smartwoo-asset-editor-ui")&&this.removeEditorByElement(t),t.querySelectorAll&&t.querySelectorAll(".smartwoo-asset-editor-ui").forEach(t=>{this.removeEditorByElement(t)}))})})}),this._observer.observe(document.body,{childList:!0,subtree:!0}))}static removeEditorByElement(t){let e=t.id;if(!e)return;let a=this.tinyMCE?.get(e);a&&(a.remove(),this.editors=this.editors.filter(t=>t.id!==e))}static CollectionManager=t=>{t.windowManager.open({title:"Select Collection Type",body:{type:"panel",items:[{type:"selectbox",name:"collectionType",label:"Collection Type",items:[{text:"Image Gallery",value:"image"},{text:"Video Playlist",value:"video"},{text:"Audio Playlist",value:"audio"}]}]},buttons:[{type:"cancel",text:"Cancel"},{type:"submit",text:"Next",primary:!0}],async onSubmit(e){let a=e.getData();e.close();let o=a.collectionType,s=await smartwooAssetEditorOpenMediaLibrary({title:"Select "+o.charAt(0).toUpperCase()+o.slice(1),buttonText:"Insert "+o.charAt(0).toUpperCase()+o.slice(1),multiple:!0,type:o}),[l,r]=smartwooAssetEditorResolveHtmlBuilder(o),i=await l(s);t.insertContent(i),r&&r(t),t.on("init change undo redo SetContent",()=>r&&r(t))}})};static async promptGalleryType(){return new Promise(t=>{SmartWooEditor.tinyMCE.activeEditor.windowManager.open({title:"Select Gallery Type",body:{type:"panel",items:[{type:"htmlpanel",html:`<div class="smartwoo-gallery-type-container">
                                <!-- Hover Overlay -->
                                <label class="smartwoo-gallery-type-option">
                                    <input class="smartwoo-gallery-input" type="radio" name="galleryType" value="hover-overlay" checked>
                                    <div class="smartwoo-gallery-preview">
                                        <svg class="preview-svg" viewBox="0 0 74 46" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="2" y="2" width="30" height="30" rx="3" fill="#d8eefe"/>
                                            <rect x="40" y="2" width="32" height="30" rx="3" fill="#e9f3ff"/>
                                            <rect x="2" y="34" width="70" height="10" rx="2" fill="rgba(0,0,0,0.25)"/>
                                            <circle cx="12" cy="17" r="4" fill="rgba(255,255,255,0.9)"/>
                                        </svg>
                                    </div>
                                    <span class="smartwoo-gallery-label">Hover Overlay</span>
                                </label>

                                <!-- Card Style -->
                                <label class="smartwoo-gallery-type-option">
                                    <input class="smartwoo-gallery-input" type="radio" name="galleryType" value="card-style">
                                    <div class="smartwoo-gallery-preview">
                                        <svg class="preview-svg" viewBox="0 0 74 46" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="2" y="2" width="70" height="26" rx="3" fill="#f6e7ff"/>
                                            <rect x="6" y="32" width="50" height="4" rx="2" fill="#f0ecff"/>
                                            <rect x="6" y="38" width="30" height="4" rx="2" fill="#efe8ff"/>
                                        </svg>
                                    </div>
                                    <span class="smartwoo-gallery-label">Card Style</span>
                                </label>

                                <!-- Masonry -->
                                <label class="smartwoo-gallery-type-option">
                                    <input class="smartwoo-gallery-input" type="radio" name="galleryType" value="masonry">
                                    <div class="smartwoo-gallery-preview">
                                        <svg class="preview-svg" viewBox="0 0 74 46" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="2" y="2" width="30" height="20" rx="3" fill="#e6f7e6"/>
                                            <rect x="36" y="2" width="36" height="10" rx="3" fill="#dff6df"/>
                                            <rect x="2" y="24" width="20" height="20" rx="3" fill="#dfffe6"/>
                                            <rect x="26" y="24" width="46" height="20" rx="3" fill="#e9ffe6"/>
                                        </svg>
                                    </div>
                                    <span class="smartwoo-gallery-label">Masonry</span>
                                </label>

                                <!-- Grid -->
                                <label class="smartwoo-gallery-type-option">
                                    <input class="smartwoo-gallery-input" type="radio" name="galleryType" value="grid">
                                    <div class="smartwoo-gallery-preview">
                                        <svg class="preview-svg" viewBox="0 0 74 46" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="2" y="2" width="20" height="14" rx="2" fill="#fff0e6"/>
                                            <rect x="26" y="2" width="20" height="14" rx="2" fill="#fff4e0"/>
                                            <rect x="50" y="2" width="20" height="14" rx="2" fill="#fff7e6"/>
                                            <rect x="2" y="18" width="20" height="14" rx="2" fill="#fff0e6"/>
                                            <rect x="26" y="18" width="20" height="14" rx="2" fill="#fff4e0"/>
                                            <rect x="50" y="18" width="20" height="14" rx="2" fill="#fff7e6"/>
                                        </svg>
                                    </div>
                                    <span class="smartwoo-gallery-label">Grid</span>
                                </label>
                            </div>`}]},buttons:[{type:"cancel",text:"Cancel"},{type:"submit",text:"OK",primary:!0}],onSubmit(e){let a=document.querySelector(".tox-dialog"),o=a.querySelector('input[name="galleryType"]:checked');e.close(),t(o?o.value:"hover-overlay")}})})}static async buildGallery(t){if(!t||!t.length)return"";let e=t.map(t=>({url:t.url,alt:t.alt||"",title:t.title||"",caption:t.caption||""}));return`
            <div class="smartwoo-gallery smartwoo-gallery-${await SmartWooEditor.promptGalleryType()}" contenteditable="false">
                ${e.map((t,e)=>`
                    <div class="smartwoo-gallery-item" draggable="true" data-item-index="${e}" contenteditable="true">
                        <div class="smartwoo-image-wrapper">
                            <img src="${t.url}" alt="${t.alt}" title="${t.title}" data-image-index="${e}" draggable="false" />
                            
                            <div class="smartwoo-meta">
                                <h4 class="smartwoo-image-title" data-placeholder="Enter title">${t.title}</h4>
                                <p class="smartwoo-image-caption" data-placeholder="Enter caption">${t.caption}</p>
                            </div>

                            <div class="smartwoo-image-actions">
                                <button type="button" class="smartwoo-replace-image" data-image-index="${e}">
                                    <span class="dashicons dashicons-edit"></span> Replace
                                </button>
                                <button type="button" class="smartwoo-delete-image" data-image-index="${e}">
                                    <span class="dashicons dashicons-trash"></span> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                `).join("")}

                <div class="smartwoo-gallery-item editor-only">
                    <div class="smartwoo-add-image" title="Add image">
                        <span class="dashicons dashicons-plus-alt add-icon"></span>
                    </div>
                </div>
            </div>
        `}static restoreImageGalleryBlock(t,e){if(t&&t.classList.contains("smartwoo-gallery")){if(t.setAttribute("contenteditable",!1),t.querySelectorAll(".smartwoo-gallery-item").forEach((t,e)=>{t.setAttribute("contenteditable",!0),t.setAttribute("draggable",!0),t.getAttribute("data-item-index")||t.setAttribute("data-item-index",e),t.querySelector("img")?.setAttribute("draggable",!1);let a=t.querySelector(".smartwoo-gallery-caption");if(a&&a.setAttribute("contenteditable",!0),!t.querySelector(".smartwoo-image-actions")){let o=document.createElement("div");o.setAttribute("class","smartwoo-image-actions"),o.innerHTML=`<button type="button" class="smartwoo-replace-image" data-image-index="${t.getAttribute("data-item-index")}">
                    <span class="dashicons dashicons-edit"></span> Replace
                </button>
                <button type="button" class="smartwoo-delete-image" data-image-index="${t.getAttribute("data-item-index")}">
                    <span class="dashicons dashicons-trash"></span> Delete
                </button>`,t.appendChild(o)}}),!t.querySelector(".smartwoo-add-image")){let a=document.createElement("div");a.setAttribute("class","smartwoo-gallery-item editor-only"),a.innerHTML=`<div class="smartwoo-add-image" title="Add image">
                <span class="dashicons dashicons-plus-alt add-icon"></span>
            </div>`,t.appendChild(a)}smartwooAssetEditorResolveHtmlBuilder("image")[1](e),e.on("init change undo redo SetContent",()=>smartwooAssetEditorResolveHtmlBuilder("image")[1](e))}}static async buildAudioPlaylist(t){let e=t.map(t=>({id:t.id||null,url:t.url,title:t.title||"Untitled",artist:t.meta?.artist||t.artist||t.authorName||"Unknown Artist",duration:t.fileLength||null,durationHuman:t.fileLengthHumanReadable||"",thumbnail:t.thumb?.src||`${smart_woo_vars.smartwoo_assets_url}images/audio-playlist-icon.svg`,mime:t.mime,album:t.meta?.album||""}));if(0===e.length)return"";let a=e[0],o=e.map((t,e)=>`
            <li class="smartwoo-playlist__item" data-index="${e}" draggable="true" contenteditable="true">
                <span class="smartwoo-playlist__title">${escHtml(t.title)}</span>
                ${t.artist?`<span class="smartwoo-playlist__artist">${escHtml(t.artist)}</span>`:""}
                <span class="drag-handle"></span>
            </li>
        `).join(""),s=JSON.stringify(e).replace(/"/g,"&quot;"),l=`
            <div class="smartwoo-audio-playlist" contenteditable="false" data-playlist='${encodeURIComponent(s)}'>
                <div class="smartwoo-audio-player">
                    <div class="smartwoo-audio-player__thumbnail" contenteditable="false">
                        <img class="smartwoo-thumbnail" contenteditable="false" src="${escHtml(a.thumbnail)}" alt="${escHtml(a.title||"Audio thumbnail")}">
                    </div>
                    <div class="smartwoo-audio-player__layout" contenteditable="false">
                        <div class="smartwoo-audio-player__now-playing" contenteditable="false">
                            <span class="smartwoo-current-title">${escHtml(a.title)}</span>
                            <span>&#8226;</span>
                            <span class="smartwoo-current-artist">${escHtml(a.artist)}</span>
                        </div>

                        <div class="smartwoo-audio-player__seek" contenteditable="false">
                            <div class="smartwoo-audio-player__progress" contenteditable="false">
                                <div class="smartwoo-progress-bar" contenteditable="false">&#8203;</div>
                            </div>

                            <div class="smartwoo-audio-player__time" contenteditable="false">
                                <span class="smartwoo-time-current">0:00</span> / <span class="smartwoo-time-duration">0:00</span>
                            </div>
                        </div>

                        <div class="smartwoo-audio-player__controls" contenteditable="false">
                            <div class="smartwoo-audio-player__control-group smartwoo-audio-player-volume-container" contenteditable="false">
                                <span class="dashicons dashicons-controls-volumeon smartwoo-volume-toggle" title="Mute"></span>
                                <div class="smartwoo-volume-slider" contenteditable="false">
                                    <div class="smartwoo-volume-progress" contenteditable="false"></div>
                                </div>
                            </div>

                            <div class="smartwoo-audio-player__control-group smartwoo-audio-player-controls" contenteditable="false">
                                <span class="smartwoo-control smartwoo-prev dashicons dashicons-controls-skipback" title="Previous" contenteditable="false"></span>
                                <div class="smartwoo-audio-player play-pause-toggle" contenteditable="false">
                                    <span class="smartwoo-control smartwoo-pause" style="display: none;" title="Pause"></span>
                                    <span class="smartwoo-control smartwoo-play" title="Play"></span>
                                </div>
                                <span class="smartwoo-control smartwoo-next dashicons dashicons-controls-skipforward" title="Next" contenteditable="false"></span>
                            </div>

                            <div class="smartwoo-audio-player__control-group smartwoo-playlist-control" contenteditable="false">
                                <span class="dashicons dashicons-playlist-audio" title="Toggle Playlist" contenteditable="false"></span>
                            </div>
                        </div>
                    </div>
                    <div class="smartwoo-audio-player__playlist" contenteditable="false">
                        <h3 contenteditable="true"> Playlist</h3>
                        <ul class="smartwoo-playlist" contenteditable="false">
                            ${o}
                            <li class="smartwoo-add-to-playlist" title="Add to playlist"><span class="dashicons dashicons-plus"></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;return l}static restoreAudioPlaylistBlock(t,e){if(!t||!t.classList.contains("smartwoo-audio-playlist"))return;let a=t.getAttribute("data-playlist"),o;try{o=JSON.parse(decodeURIComponent(a).replace(/&quot;/g,'"'))}catch(s){console.warn("Invalid playlist JSON:",s);return}t.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__thumbnail")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-thumbnail")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__layout")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__now-playing")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__seek")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__progress")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-progress-bar")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__time")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__controls")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-volume-slider")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-volume-progress")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-audio-player__playlist")?.setAttribute("contenteditable",!1),t.querySelector(".smartwoo-playlist")?.setAttribute("contenteditable",!1),t.querySelectorAll(".smartwoo-playlist__item").forEach(t=>{t.setAttribute("contenteditable",!0),t.setAttribute("draggable",!0);let e=document.createElement("span");e.className="drag-handle",t.appendChild(e)});let l=document.createElement("li");l.setAttribute("class","smartwoo-add-to-playlist"),l.setAttribute("title","Add to playlist"),l.innerHTML='<span class="dashicons dashicons-plus"></span>',t.querySelector(".smartwoo-playlist")?.appendChild(l),t.querySelectorAll(".smartwoo-audio-player__control-group").forEach(t=>t.setAttribute("contenteditable",!1)),t.querySelectorAll(".smartwoo-control").forEach(t=>t.setAttribute("contenteditable",!1)),smartwooAssetEditorResolveHtmlBuilder("audio")[1](e),e.on("init change undo redo SetContent",()=>smartwooAssetEditorResolveHtmlBuilder("audio")[1](e))}static async buildVideoPlaylist(t){if(!t||!t.length)return"";let e=t.map(t=>({url:t.url,title:t.title||"",mime:t.mime||"video/mp4",desciption:t.description||"",artist:t.meta?.artist||t.artist||"Unknown Artist",album:t.meta?.album||"",duration:t.fileLength||null,durationHuman:t.fileLengthHumanReadable||""})),a=JSON.stringify(e).replace(/"/g,"&quot;"),o=e[0],s=e.map((t,e)=>`
            <li class="smartwoo-video-playlist-item" data-index="${e}" draggable="true" contenteditable="true">
                <img src="${smart_woo_vars.smartwoo_assets_url}images/video-playlist-icon.svg" class="smartwoo-video-playlist-item_image" alt="${escHtml(t.title)}">
                <p class="smartwoo-playlist__title">${escHtml(t.title)}</p>
                <span class="drag-handle" title="Reorder"></span>
            </li>
        `).join(""),l=`
            <div class="smartwoo-video-player-container" contenteditable="false" data-playlist="${encodeURIComponent(a)}">
                <div class="smartwoo-video-player-left" contenteditable="false">
                    <div class="smartwoo-video-player__frame" contenteditable="false">
                        <video src="${o.url}" class="smartwoo-video-player__video" controls preload="auto">
                            Your browser does not support the video format.
                        </video>
                        <div class="smartwoo-video-nowplaying-info">
                            <span class="smartwoo-current-title">${o.title}</span> <span>&#8226;</span> <span class="smartwoo-current-artist">${o.artist}</span>
                        </div>
                        <div class="smartwoo-video-player-controls" contenteditable="false">
                            <div class="smartwoo-video-player_controls-timing" contenteditable="false">
                                <span class="smartwoo-seek-tooltip"></span>
                                <span class="smartwoo-video-player_timing-current smartwoo-control" contenteditable="false">0:00</span>
                                <div class="smartwoo-video-player__progress smartwoo-control" contenteditable="false">
                                    <div class="smartwoo-progress-bar smartwoo-control" contenteditable="false"></div>
                                </div>

                                <span class="smartwoo-video-player_timing-duration smartwoo-control" contenteditable="false">0:00</span>
                            </div>
                            <div class="smartwoo-video-player__controls">
                                <div class="smartwoo-video-player__controls-control">
                                    <span class="dashicons dashicons-controls-skipback smartwoo-control smartwoo-prev" title="Previous"></span>
                                    <span class="dashicons dashicons-controls-play smartwoo-control smartwoo-play" title="Play"></span>
                                    <span class="dashicons dashicons-controls-pause smartwoo-control smartwoo-pause" style="display: none;" title="Pause"></span>
                                    <span class="dashicons dashicons-controls-skipforward smartwoo-control smartwoo-next" title="Next"></span>                            
                                </div>
                                <div class="smartwoo-video-player__controls-right">
                                    <span class="dashicons dashicons-controls-volumeon smartwoo-control smartwoo-video-volume-toggle" title="Mute"></span>
                                    <div class="smartwoo-video-volume-slider smartwoo-control">
                                        <div class="smartwoo-video-volume-progress smartwoo-control"></div>
                                        <div class="smartwoo-video-volume-scrubber smartwoo-control"></div>
                                    </div>
                                    <span class="dashicons dashicons-fullscreen-alt smartwoo-control smartwoo-video-fullscreen-toggle" title="Fullscreen mode"></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="smartwoo-video-player-right">
                    <h3 contenteditable="true">Playlist</h3>
                    <ul class="smartwoo-video-player-playlist-container">
                        ${s}
                        <li class="smartwoo-add-to-playlist" title="Add to playlist"><span class="dashicons dashicons-plus"></span></li>
                    </ul>
                </div>
            </div> 
        `;return l}static restoreVideoPlaylistBlock(t,e){if(!t||!t.classList.contains("smartwoo-video-player-container"))return;let a=t.getAttribute("data-playlist"),o;try{o=JSON.parse(decodeURIComponent(a).replace(/&quot;/g,'"'))}catch(s){console.warn("Invalid playlist JSON:",s);return}t.querySelectorAll(".smartwoo-video-playlist-toggle").forEach(t=>t.remove());let l=t.querySelector(".smartwoo-video-player-playlist-container"),r=t.querySelector(".smartwoo-video-player-right"),i=t.querySelector(".smartwoo-video-player__frame"),n=o[0];t?.setAttribute("contenteditable",!1),l?.setAttribute("contenteditable",!1),i?.setAttribute("contenteditable",!1),r?.setAttribute("contenteditable",!1),l?.querySelector(".smartwoo-video-player-left")?.setAttribute("contenteditable",!1),r?.querySelectorAll("h1, h2, h3, h4, h5, h6, p")?.forEach(t=>t.setAttribute("contenteditable",!0)),i?.querySelector("video")?.setAttribute("contenteditable",!1);let d=t?.querySelector(".smartwoo-current-title"),c=t?.querySelector(".smartwoo-current-artist");d.textContent=n?.title,c.textContent=n?.artist;let m=`
            <div class="smartwoo-video-player-controls" contenteditable="false">
                <div class="smartwoo-video-player_controls-timing" contenteditable="false">
                    <span class="smartwoo-seek-tooltip"></span>
                    <span class="smartwoo-video-player_timing-current smartwoo-control" contenteditable="false">0:00</span>
                    <div class="smartwoo-video-player__progress smartwoo-control" contenteditable="false">
                        <div class="smartwoo-progress-bar smartwoo-control" contenteditable="false"></div>
                    </div>
                    <span class="smartwoo-video-player_timing-duration smartwoo-control" contenteditable="false">0:00</span>
                </div>
                <div class="smartwoo-video-player__controls">
                    <div class="smartwoo-video-player__controls-control">
                        <span class="dashicons dashicons-controls-skipback smartwoo-control smartwoo-prev" title="Previous"></span>
                        <span class="dashicons dashicons-controls-play smartwoo-control smartwoo-play" title="Play"></span>
                        <span class="dashicons dashicons-controls-pause smartwoo-control smartwoo-pause" style="display: none;" title="Pause"></span>
                        <span class="dashicons dashicons-controls-skipforward smartwoo-control smartwoo-next" title="Next"></span>                            
                    </div>
                    <div class="smartwoo-video-player__controls-right">
                        <span class="dashicons dashicons-controls-volumeon smartwoo-control smartwoo-video-volume-toggle" title="Mute"></span>
                        <div class="smartwoo-video-volume-slider smartwoo-control">
                            <div class="smartwoo-video-volume-progress smartwoo-control"></div>
                            <div class="smartwoo-video-volume-scrubber smartwoo-control"></div>
                        </div>
                        <span class="dashicons dashicons-fullscreen-alt smartwoo-control smartwoo-video-fullscreen-toggle" title="Fullscreen mode"></span>
                    </div>
                </div>
            </div>
        `,p=t?.querySelector(".smartwoo-video-player-controls");p?(p.setAttribute("contenteditable","false"),t?.querySelectorAll(".smartwoo-control")?.forEach(t=>t.setAttribute("contenteditable","false"))):i.insertAdjacentHTML("beforeend",m),l?.querySelectorAll(".smartwoo-video-playlist-item")?.forEach(t=>{if(t.setAttribute("draggable","true"),t.setAttribute("contenteditable","true"),!t.querySelector(".drag-handle")){let e=document.createElement("span");e.className="drag-handle",e.title="Reorder",t.appendChild(e)}});let u=document.createElement("li");u.className="smartwoo-add-to-playlist",u.setAttribute("title","Add to playlist"),u.innerHTML='<span class="dashicons dashicons-plus">',l.appendChild(u),smartwooAssetEditorResolveHtmlBuilder("video")[1](e),e.on("init change undo redo SetContent",()=>smartwooAssetEditorResolveHtmlBuilder("video")[1](e))}}function smartwooAssetEditorResolveHtmlBuilder(t){switch(t){case"image":return[SmartWooEditor.buildGallery,smartwooImageGalleryBindEvents];case"video":return[SmartWooEditor.buildVideoPlaylist,smartwooEnableVideoPlaylist];case"audio":return[SmartWooEditor.buildAudioPlaylist,smartwooEnableAudioPlaylist];default:return function(){return console.warn("Unknown type:",t),""}}}document.addEventListener("DOMContentLoaded",async function(){let t=new SmartWooEditor;await t.init()});let draggedItem=null;function smartwooImageGalleryBindEvents(t){let e=t.getBody(),a=e.querySelector(".smartwoo-gallery");if(!a)return;a.addEventListener("click",e=>{let a=e.target.closest(".smartwoo-add-image, .smartwoo-replace-image, .smartwoo-delete-image");if(a){if(e.preventDefault(),a.classList.contains("smartwoo-add-image")){addImageToGallery(t),e.stopImmediatePropagation();return}if(a.classList.contains("smartwoo-replace-image")){let o=a.closest(".smartwoo-gallery-item")?.querySelector("img");if(!o)return;let s=wp.media({title:"Replace Image",multiple:!1,library:{type:"image"},button:{text:"Replace Image"}});s.on("select",()=>{let t=s.state().get("selection").first().toJSON();o.src=t.url,o.setAttribute("alt",t.alt||""),o.setAttribute("title",t.title||"")}),s.open(),e.stopImmediatePropagation();return}if(a.classList.contains("smartwoo-delete-image")){let l=a.closest(".smartwoo-gallery-item");l&&t.undoManager.transact(()=>{t.dom.remove(l)})}}});let o=null;a.addEventListener("dragstart",t=>{let e=t.target.closest(".smartwoo-gallery-item");e&&(o=e,t.dataTransfer.effectAllowed="move",t.dataTransfer.setData("text/plain",e.getAttribute("data-item-index")),e.classList.add("dragging"))}),a.addEventListener("dragover",t=>{t.preventDefault(),t.dataTransfer.dropEffect="move"}),a.addEventListener("drop",t=>{t.preventDefault();let e=t.target.closest(".smartwoo-gallery-item");if(o&&e&&o!==e){let a=o.parentNode,s=parseInt(o.getAttribute("data-item-index"),10),l=parseInt(e.getAttribute("data-item-index"),10);a.insertBefore(o,l<s?e:e.nextSibling)}o=null}),a.addEventListener("dragend",t=>{t.target.classList.remove("dragging")})}async function addImageToGallery(t){let e=t.getBody(),a=e.querySelector(".smartwoo-gallery");if(!a)return;let o=await smartwooAssetEditorOpenMediaLibrary({title:"Add Images to Gallery",multiple:!0,type:"image",buttonText:"Add Selected Images"}),s=o.map(t=>({url:t.url,alt:t.alt||"",title:t.title||"",caption:t.caption||""}));s.forEach(function(t){let e=Date.now(),o=document.createElement("div");o.className="smartwoo-gallery-item",o.setAttribute("draggable","true"),o.setAttribute("contenteditable","true"),o.setAttribute("data-item-index",e),o.innerHTML=`
            <div class="smartwoo-image-wrapper">
                <img src="${t.url}" alt="${t.alt||""}" title="${t.title||""}" draggable="false" />
                <div class="smartwoo-meta">
                    <h4 class="smartwoo-image-title" data-placeholder="Enter title">${t.title}</h4>
                    <p class="smartwoo-image-caption" data-placeholder="Enter caption">${t.caption}</p>
                </div>
                <div class="smartwoo-image-actions">
                    <button type="button" title="Replace" class="smartwoo-replace-image" data-image-index="${e}">
                        <span class="dashicons dashicons-edit"></span>
                        Replace
                    </button>

                    <button type="button" title="Delete" class="smartwoo-delete-image" data-image-index="${e}">
                        <span class="dashicons dashicons-trash"></span>
                        Delete
                    </button>
                </div>
            </div>
        `;let s=a.querySelector(".smartwoo-add-image")?.parentNode;a.insertBefore(o,s)})}function smartwooEnableAudioPlaylist(t){let e=t.getBody().querySelectorAll(".smartwoo-audio-playlist"),a=t.getBody().querySelector(".smartwoo-playlist");e.forEach(t=>{new SmartwooAudioPlayer(t)}),a?.addEventListener("dragstart",t=>{if(t.target.classList.contains("smartwoo-playlist__item"))try{draggedItem=t.target,t.dataTransfer.effectAllowed="move",t.dataTransfer.setData("text/plain",draggedItem.dataset.index),t.target.classList.add("dragging")}catch(e){}}),a?.addEventListener("dragover",t=>{t.target.classList.contains("smartwoo-playlist__item")&&(t.preventDefault(),t.dataTransfer.dropEffect="move")}),a?.addEventListener("drop",t=>{if(t.target.classList.contains("smartwoo-playlist__item")){if(t.preventDefault(),draggedItem&&draggedItem!==t.target)try{let e=parseInt(draggedItem.dataset.index,10),a=parseInt(t.target.dataset.index,10),o=draggedItem.parentNode;o.insertBefore(draggedItem,a<e?t.target:t.target.nextSibling)}catch(s){}draggedItem=null}}),a?.addEventListener("dragend",t=>{t.target.classList.contains("smartwoo-playlist__item")&&t?.target?.classList?.remove("dragging")}),a?.addEventListener("click",e=>{e.target.classList.contains("smartwoo-add-to-playlist")&&(e.stopImmediatePropagation(),addAudioToPlaylist(t))})}async function addAudioToPlaylist(t){let e=t.getBody(),a=e.querySelector(".smartwoo-audio-playlist");if(!a)return;let o=a.querySelector(".smartwoo-playlist"),s=a.getAttribute("data-playlist"),l=s?JSON.parse(decodeURIComponent(s).replace(/&quot;/g,'"')):[],r=l.length,i=await smartwooAssetEditorOpenMediaLibrary({title:"Add Audios to playlist",multiple:!0,type:"audio",buttonText:"Add selected audios"}),n=i.map(t=>({id:t.id||null,url:t.url,title:t.title||"Untitled",artist:t.meta?.artist||t.artist||t.authorName||"Unknown Artist",duration:t.fileLength||null,durationHuman:t.fileLengthHumanReadable||"",thumbnail:t.thumb?.src||`${smart_woo_vars.smartwoo_assets_url}images/audio-playlist-icon.svg`,mime:t.mime,album:t.meta?.album||""}));a.setAttribute("data-playlist",encodeURIComponent(JSON.stringify([...l,...n]).replace(/"/g,"&quot;")));let d=a.querySelector(".smartwoo-add-to-playlist");n.forEach((t,e)=>{let a=document.createElement("li");a.className="smartwoo-playlist__item",a.setAttribute("draggable","true"),a.setAttribute("contenteditable","true"),a.setAttribute("data-index",r+e),a.innerHTML=`
            <span class="smartwoo-playlist__title">${escHtml(t.title)}</span>
            ${t.artist?`<span class="smartwoo-playlist__artist">${escHtml(t.artist)}</span>`:""}
            <span class="drag-handle"></span>
        `,o.insertBefore(a,d)})}function smartwooEnableVideoPlaylist(t){let e=t.getBody(),a=t.getBody().querySelectorAll(".smartwoo-video-player-container"),o=t.getBody().querySelectorAll("video.smartwoo-video-player__video"),s=t.getBody().querySelector(".smartwoo-video-player-playlist-container");a.forEach(async t=>{new SmartwooVideoPlayer(t)}),t.on("execCommand",t=>{"mcePreview"===t.command&&setTimeout(()=>{let t=document.querySelector(".tox-dialog-wrap");if(!t)return;let e=t.querySelector("iframe");if(!e)return;let a=e.contentDocument||e.contentWindow.document,o=a.querySelector(".smartwoo-video-player-container");o&&new SmartwooVideoPlayer(o)},200)}),o.forEach(async t=>{let e=await smartwooGetVideoThumbnail(t.src);t.poster=e,t.removeAttribute("controls"),t.removeAttribute("controlist"),t.removeAttribute("height"),t.removeAttribute("width")}),s?.addEventListener("dragstart",t=>{if(t.target.classList.contains("smartwoo-video-playlist-item"))try{draggedItem=t.target,t.dataTransfer.effectAllowed="move",t.dataTransfer.setData("text/plain",draggedItem.dataset.index),t.target.classList.add("dragging")}catch(e){}}),s?.addEventListener("dragover",t=>{t.target.classList.contains("smartwoo-video-playlist-item")&&(t.preventDefault(),t.dataTransfer.dropEffect="move")}),s?.addEventListener("drop",t=>{if(t.target.classList.contains("smartwoo-video-playlist-item")){if(t.preventDefault(),draggedItem&&draggedItem!==t.target)try{let e=parseInt(draggedItem.dataset.index,10),a=parseInt(t.target.dataset.index,10),o=draggedItem.parentNode;o.insertBefore(draggedItem,a<e?t.target:t.target.nextSibling)}catch(s){}draggedItem=null}}),s?.addEventListener("dragend",t=>{t.target.classList.contains("smartwoo-video-playlist-item")&&t?.target?.classList?.remove("dragging")}),e?.addEventListener("click",e=>{if(e.target.classList.contains("smartwoo-add-to-playlist")){e.stopImmediatePropagation(),smartwooAddVideoToPlaylist(t);return}})}async function smartwooAddVideoToPlaylist(t){let e=t.getBody(),a=e.querySelector(".smartwoo-video-player-container");if(!a)return;let o=a.querySelector(".smartwoo-video-player-playlist-container"),s=a.getAttribute("data-playlist"),l=s?JSON.parse(decodeURIComponent(s).replace(/&quot;/g,'"')):[],r=l.length,i=await smartwooAssetEditorOpenMediaLibrary({title:"Add Videos to Playlist",multiple:!0,type:"video",buttonText:"Add Selected Videos"}),n=i.map(t=>({url:t.url,title:t.title||"",mime:t.mime||"video/mp4",desciption:t.description||"",artist:t.meta?.artist||t.artist||"Unknown Artist",album:t.meta?.album||"",duration:t.fileLength||null,durationHuman:t.fileLengthHumanReadable||""}));a.setAttribute("data-playlist",encodeURIComponent(JSON.stringify([...l,...n]).replace(/"/g,"&quot;")));let d=a.querySelector(".smartwoo-add-to-playlist");n.forEach((t,e)=>{let a=document.createElement("li");a.className="smartwoo-video-playlist-item",a.setAttribute("draggable","true"),a.setAttribute("contenteditable","true"),a.setAttribute("data-index",r+e),a.innerHTML=`
            <img src="${smart_woo_vars.smartwoo_assets_url}images/video-playlist-icon.svg" class="smartwoo-video-playlist-item_image" alt="${escHtml(t.title)}">
            <p class="smartwoo-playlist__title">${escHtml(t.title)}</p>
            <span class="drag-handle" title="Reorder"></span>
        `,o.insertBefore(a,d)})}function escHtml(t){let e=document.createElement("div");return e.textContent=t,e.innerHTML.replace(/"/g,"&quot;")}