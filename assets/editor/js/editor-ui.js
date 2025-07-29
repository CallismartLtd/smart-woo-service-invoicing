
/**
 * Open the WordPress media library.
 *
 * @param {Object} options - Media library options.
 * @returns {Promise<Array>} - Resolves to selected media items.
 */
async function smartwooAssetEditorOpenMediaLibrary( options = {} ) {
    const defaults = {
        title: 'Select Media',
        buttonText: 'Insert Media',
        multiple: false,
        type: 'image', // 'image', 'video', 'audio', etc.
        uploadedTo: null
    };

    const config = Object.assign( {}, defaults, options );

    return new Promise( ( resolve ) => {
        const mediaLibrary = wp.media( {
            title: config.title,
            button: {
                text: config.buttonText
            },
            multiple: config.multiple,
            library: {
                type: config.type,
                uploadedTo: config.uploadedTo
            }
        } );

        mediaLibrary.on( 'select', () => {
            const selected = mediaLibrary.state().get( 'selection' ).toJSON();
            resolve( selected );
        } );

        mediaLibrary.on( 'close', () => {
            const selected = mediaLibrary.state().get( 'selection' );
            if ( !selected || !selected.length ) {
                resolve( [] );
            }
        } );

        mediaLibrary.open();
    } );
}


document.addEventListener( 'DOMContentLoaded', function () {
    const smartwooAllowedElements =  [
        'a[href|target|title|rel|class|style|data-*|aria-*|download]',
        'abbr[title|class|style|data-*|aria-*]',
        'acronym[title|class|style|data-*|aria-*]',
        'b[class|style|data-*|aria-*]',
        'blockquote[cite|class|style|data-*|aria-*]',
        'br[class|style|data-*|aria-*]',
        'code[class|style|data-*|aria-*]',
        'div[id|class|style|title|data-*|aria-*|contenteditable]',
        'em[class|style|data-*|aria-*]',
        'h1[class|style|data-*|aria-*]', 'h2[class|style|data-*|aria-*]', 'h3[class|style|data-*|aria-*|contenteditable]', 
        'h4[class|style|data-*|aria-*]', 'h5[class|style|data-*|aria-*]', 'h6[class|style|data-*|aria-*]',
        'hr[class|style|data-*|aria-*]',
        'i[class|style|data-*|aria-*]',
        'iframe[src|width|height|frameborder|allowfullscreen|class|style|data-*|aria-*]',
        'img[src|alt|title|width|height|class|style|data-*|aria-*|draggable|contenteditable]',
        'li[class|style|title|data-*|aria-*|contenteditable|draggable]',
        'ol[class|style|title|data-*|aria-*|contenteditable]',
        'ul[class|style|title|data-*|aria-*|contenteditable]',
        'p[class|style|title|data-*|aria-*|contenteditable]',
        'pre[class|style|title|data-*|aria-*]',
        'section[class|style|data-*|aria-*|contenteditable]',
        'article[class|style|data-*|aria-*|contenteditable]',
        'small[class|style|data-*|aria-*]',
        'span[class|style|title|data-*|aria-*|contenteditable]',
        'strong[class|style|data-*|aria-*]',
        'sub[class|style|data-*|aria-*]',
        'sup[class|style|data-*|aria-*]',
        'table[border|cellspacing|cellpadding|class|style|data-*|aria-*]',
        'tbody[class|style|data-*|aria-*]',
        'thead[class|style|data-*|aria-*]',
        'tfoot[class|style|data-*|aria-*]',
        'tr[class|style|data-*|aria-*]',
        'td[colspan|rowspan|class|style|data-*|aria-*]',
        'th[colspan|rowspan|scope|class|style|data-*|aria-*]',
        'time[datetime|class|style|data-*|aria-*]',
        'video[src|poster|width|height|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable]',
        'audio[src|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable]',
        'svg[*]',
        'path[*]',
        'g[*]',
        'use[*]'
        ].join(',');

    window.smartwoo_tinymce = tinymce;
    smartwoo_tinymce.init({
        selector: '#smartwoo-asset-editor-ui',
        skin: 'oxide',
        branding: false,
        license_key: 'gpl',
        menubar: 'file edit insert format tools table',
        plugins: 'lists link image media table code preview fullscreen autosave wordcount searchreplace visualblocks insertdatetime emoticons',
        toolbar: 'add_media_button | styles | alignleft aligncenter alignjustify alignright bullist numlist outdent indent | forecolor backcolor | code fullscreen preview | undo redo',
        height: 600,
        promotion: false,
        content_css: [
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap',
            smart_woo_vars.dashicons_asset_url,
            smart_woo_vars.editor_css_url
        
        ],
        extended_valid_elements: smartwooAllowedElements,
        font_formats: 'Inter=Inter, sans-serif; Arial=Arial, Helvetica, sans-serif; Verdana=Verdana, Geneva, sans-serif; Tahoma=Tahoma, Geneva, sans-serif; Trebuchet MS=Trebuchet MS, Helvetica, sans-serif; Times New Roman=Times New Roman, Times, serif; Georgia=Georgia, serif; Palatino Linotype=Palatino Linotype, Palatino, serif; Courier New=Courier New, Courier, monospace',
        toolbar_mode: 'sliding',
        content_style: 'body { font-family: "Inter", sans-serif; font-size: 16px; }',
        setup: function ( editor ) {
            editor.ui.registry.addButton('add_media_button', {
                text: 'Collection',
                icon: 'gallery',
                tooltip: 'Create a collection of media',
                onAction: () => smartwooCollectionManager( editor )
            });

            editor.on('change', function () {
                editor.save();
            });
            
            editor.on( 'SaveContent', smartwooAssetEditorOnSaveCallback );
        }
    });
});

/**
 * Smart Woo asset editor collection callback.
 * 
 * @param {Object} editor - The TinyMCE editor instance.
 */
function smartwooCollectionManager( editor ) {
    editor.windowManager.open({
        title: 'Select Collection Type',
        body: {
            type: 'panel',
            items: [
                {
                    type: 'selectbox',
                    name: 'collectionType',
                    label: 'Collection Type',
                    items: [
                        { text: 'Image Gallery', value: 'image' },
                        { text: 'Video Playlist', value: 'video' },
                        { text: 'Audio Playlist', value: 'audio' }
                    ]
                }
            ]
        },
        buttons: [
            {
                type: 'cancel',
                text: 'Cancel'
            },
            {
                type: 'submit',
                text: 'Next',
                primary: true
            }
        ],
        onSubmit:  async (api) => {
            const data = api.getData();
            api.close();

            let type    = data.collectionType;
            let mediaOptions = {
                    title: 'Select ' + type.charAt(0).toUpperCase() + type.slice(1),
                    buttonText: 'Insert ' + type.charAt(0).toUpperCase() + type.slice(1),
                    multiple: true,
                    type: type 
                };

            let selection   = await smartwooAssetEditorOpenMediaLibrary( mediaOptions );
            let [buildHtml, callbackScript]   = smartwooAssetEditorResolveHtmlBuilder( type );
            let content     = buildHtml( selection );

            editor.insertContent(content);
            callbackScript && callbackScript( editor );
        }
    });
}

/**
 * Resolve html builder callback.
 * 
 * @param {String} type - The type of HTML element to create.
 * @return {[Function]} - A function that builds the correct HTML content for the specified type.
 */
function smartwooAssetEditorResolveHtmlBuilder( type ) {
    switch (type) {
        case 'image':
            return [smartwooAssetEditorBuildGallery, smartwooEnableImageReplacement];
        case 'video':
            return [smartwooAssetEditorBuildVideoPlaylist, null];
        case 'audio':
            return [smartwooAssetEditorBuildAudioPlaylist, smartwooEnableAudioPlaylist];
        default:
            return function() {
                console.warn('Unknown type:', type);
                return '';
            };
    }
}

/**
 * Build HTML for an image gallery.
 */
function smartwooAssetEditorBuildGallery( selection ) {
    if ( ! selection || ! selection.length ) {
        return '';
    }
    // Convert the selection array to an array of image properties
    const images = selection.map( file => {
        return {
            url: file.url,
            alt: file.alt || '',
            title: file.title || ''
        };
    });

    const galleryHtml = `
        <div class="smartwoo-gallery" style="display: flex; flex-wrap: wrap; gap: 12px; margin: 20px 0;">
            ${images.map( (img, index) => `
                <div class="smartwoo-gallery-item" draggable="true" contenteditable="false"
                    style="position: relative; cursor: move; width: calc(33.333% - 12px); min-width: 150px; box-sizing: border-box; background: #fafafa; border: 1px solid #ccc; padding: 8px; border-radius: 6px;">
                    <div class="smartwoo-image-wrapper" style="position: relative; overflow: hidden;">
                        <img src="${img.url}" alt="${img.alt}" title="${img.title}"
                            data-image-index="${index}" draggable="false"
                            style="width: 100%; height: auto; display: block; overflow: auto; max-width: 100%; min-height: 60px;" contenteditable="true" />

                        <button type="button" title="✏️ Replace" class="smartwoo-replace-image" data-image-index="${index}"
                            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                background: rgba(0, 0, 0, 0.5); color: #fff; border: none;
                                border-radius: 4px; padding: 6px 10px; font-size: 14px; cursor: pointer;
                                opacity: 0; transition: opacity 0.2s;">
                            ✏️ Replace
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    return galleryHtml;
}

/**
 * Builds HTML for an audio playlist block with thumbnail + controls.
 *
 * @param {Array} selection - Array of selected audio files from wp.media.
 * @returns {String} HTML markup.
 */
function smartwooAssetEditorBuildAudioPlaylist( selection ) {
    // Helper function for HTML escaping, ensuring robustness for attributes and content
    const escHtml = ( str ) => {
        let div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML.replace(/"/g, '&quot;');
    }

    // Process and normalize audio file data
    const audios = selection.map( file => ( {
        id: file.id || null,
        url: file.url,
        title: file.title || 'Untitled',
        artist: file.meta?.artist || file.artist || file.authorName || 'Unknown Artist',
        duration: file.fileLength || null,
        durationHuman: file.fileLengthHumanReadable || '',
        thumbnail: file.thumb?.src || file.image?.src || wp.media.defaults.audiosvg_src || 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2264%22%20height%3D%2264%22%20viewBox%3D%220%200%2064%2064%22%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22g%22%20x1%3D%220%25%22%20y1%3D%220%25%22%20x2%3D%22100%25%22%20y2%3D%22100%25%22%3E%3Cstop%20offset%3D%220%25%22%20stop-color%3D%22%2366ccff%22/%3E%3Cstop%20offset%3D%22100%25%22%20stop-color%3D%22%236600ff%22/%3E%3C/linearGradient%3E%3C/defs%3E%3Ccircle%20cx%3D%2232%22%20cy%3D%2232%22%20r%3D%2230%22%20fill%3D%22url(%23g)%22%20stroke%3D%22%23fff%22%20stroke-width%3D%222%22/%3E%3Cpath%20d%3D%22M44%2016v26.2a6.8%206.8%200%201%201-2-4.8V24h-14v18.2a6.8%206.8%200%201%201-2-4.8V20a2%202%200%200%201%202-2h16a2%202%200%200%201%202%202z%22%20fill%3D%22%23fff%22/%3E%3C/svg%3E',        mime: file.mime,
        album: file.meta?.album || '',
    } ) );

    // Ensure there's at least one track
    if ( audios.length === 0 ) {
        return ''; // Or handle as an error
    }

    const firstTrack = audios[0];

    // Build individual playlist items
    const playlistItems = audios.map( ( audio, index ) => `
        <li class="smartwoo-playlist__item" data-index="${index}" draggable="true" contenteditable="true">
            <span class="smartwoo-playlist__title">${escHtml( audio.title )}</span>
            ${ audio.artist ? `<span class="smartwoo-playlist__artist">${escHtml( audio.artist )}</span>` : '' }
            <span class="drag-handle"></span>
        </li>
    ` ).join( '' );

    const playlistJson = JSON.stringify( audios ).replace( /"/g, '&quot;' );
    const playlistHtml = `
        <div class="smartwoo-audio-playlist" contenteditable="false" data-playlist='${playlistJson}'>
            <div class="smartwoo-audio-player">
                <div class="smartwoo-audio-player__thumbnail" contenteditable="false">
                    <img class="smartwoo-thumbnail" contenteditable="false" src="${escHtml(firstTrack.thumbnail)}" alt="${escHtml(firstTrack.title || 'Audio thumbnail')}">
                </div>
                <div class="smartwoo-audio-player__layout" contenteditable="false">
                    <div class="smartwoo-audio-player__now-playing" contenteditable="false">
                        <span class="smartwoo-current-title">${escHtml( firstTrack.title )}</span>
                        <span>&#8226;</span>
                        <span class="smartwoo-current-artist">${escHtml( firstTrack.artist )}</span>
                    </div>

                    <div class="smartwoo-audio-player__seek" contenteditable="false">
                        <div class="smartwoo-audio-player__progress" contenteditable="false">
                            <div class="smartwoo-progress-bar" contenteditable="false"></div>
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
                        ${playlistItems}
                    </ul>
                </div>
            </div>
        </div>
    `;

    return playlistHtml;
}

/**
 * Build HTML for a video playlist block in the editor.
 *
 * @param {Array} selection - Array of selected video file objects from wp.media.
 * @returns {string} - HTML string to be inserted into TinyMCE editor.
 */
function smartwooAssetEditorBuildVideoPlaylist( selection ) {
    if ( ! selection || ! selection.length ) {
        return '';
    }

    const videos = selection.map( file => {
        return {
            url: file.url,
            title: file.title || '',
            mime: file.mime || 'video/mp4'
        };
    });

    const playlistHtml = `
        <div class="smartwoo-video-playlist">
            ${videos.map( ( video, index ) => `
                <div class="smartwoo-playlist__item" data-index="${index}" draggable="true" contenteditable="false">
                    <video controls preload="metadata" class="smartwoo-playlist__video">
                        <source src="${video.url}" type="${video.mime}" />
                        Your browser does not support the video tag.
                    </video>
                    <div class="smartwoo-playlist__title">${video.title}</div>
                </div>
            `).join('')}
        </div>
    `;

    return playlistHtml;
}











/**
 * Replace an image in the gallery.
 */
function smartwooEnableImageReplacement( editor ) {
    const container = editor.getBody().querySelector( '.smartwoo-gallery' );
    if ( ! container ) return;
    container.querySelectorAll( '.smartwoo-replace-image').forEach( button => {
        button.addEventListener( 'click', async ( e ) => {
            e.preventDefault();
            const imgEl = button.closest( '.smartwoo-gallery-item' )?.querySelector('img');
            if ( ! imgEl ) return;

            const mediaFrame = wp.media({
                title: 'Replace Image',
                multiple: false,
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Replace Image'
                }
            });

            mediaFrame.on( 'select', function () {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                imgEl.src = attachment.url;
                imgEl.setAttribute('alt', attachment.alt || '');
                imgEl.setAttribute('title', attachment.title || '');
            });

            mediaFrame.open();
        });
    });
 
}

let draggedItem = null;

function smartwooEnableAudioPlaylist( editor ) {
    const audioPlayers  = editor.getBody().querySelectorAll( '.smartwoo-audio-playlist' );
    const playlistItems = editor.getBody().querySelectorAll( '.smartwoo-playlist__item' );

    audioPlayers.forEach( player => {
        smartWooinitAssetAudioPlayer( player );
    });

    playlistItems.forEach( item => {      
        item.addEventListener( 'dragstart', ( e ) => {
            try {
                draggedItem = e.target;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData( 'text/plain', draggedItem.dataset.index );
                e.target.classList.add( 'dragging' );
            } catch (error) {}
            
        });

        item.addEventListener( 'dragover', ( e ) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });

        item.addEventListener( 'drop', ( e ) => {
            e.preventDefault();
            if ( draggedItem && draggedItem !== e.target ) {
                try {
                    const fromIndex = parseInt( draggedItem.dataset.index, 10 );
                    const toIndex   = parseInt( e.target.dataset.index, 10 );

                    const parent    = draggedItem.parentNode;
                    parent.insertBefore( draggedItem, toIndex < fromIndex ? e.target : e.target.nextSibling );
                } catch (error) {}
            }

            draggedItem = null;
        });

        item.addEventListener( 'dragend', ( e ) => {
            e?.target?.classList?.remove( 'dragging' );
        });
    });
}

/**
 * Callback for sanitizing content in the TinyMCE editor before save.
 *
 * @param {Object} e - The event object.
 * @param {Object} editor - The TinyMCE editor instance.
 */
function smartwooAssetEditorOnSaveCallback( e, editor ) {
    const parser = new DOMParser();
    const doc = parser.parseFromString( e.content, 'text/html' );
    const body = doc.body;

    // Attributes to strip globally
    const stripAttributes = [ 'draggable', 'contenteditable' ];

    // Unwanted inline styles (pattern or exact match)
    const styleCleanupPatterns = [
        /cursor:\s*move;?/gi,
        /user-select:\s*[^;]+;?/gi,
        /pointer-events:\s*[^;]+;?/gi
    ];

    // Remove unwanted attributes
    stripAttributes.forEach( attr => {
        body.querySelectorAll( `[${ attr }]` ).forEach( el => {
            el.removeAttribute( attr );
        } );
    } );

    // Remove control elements (e.g. overlay buttons)
    body.querySelectorAll( '.smartwoo-replace-image' ).forEach( el => el.remove() );

    // Sanitize inline styles
    body.querySelectorAll( '[style]' ).forEach( el => {
        let style = el.getAttribute( 'style' );
        if ( ! style ) return;

        styleCleanupPatterns.forEach( pattern => {
            style = style.replace( pattern, '' );
        } );

        el.setAttribute( 'style', style.trim() );
    } );

    e.content = body.innerHTML;
}
