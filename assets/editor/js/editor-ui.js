
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

/**
 * Smart Woo Asset editor class.
 */
class SmartWooEditor {
    static tinyMCE  = null;
    static isLoaded = false;
    static loadingPromise = null;
    static editors  = [];

    /**
     * @param {String} selector - The editor selector.
     * @param {Object} config   - Addtional configuration for the editor.
     */
    constructor ( selector = '.smartwoo-asset-editor-ui', config = {} ) {
        this.selector = selector;
        this.userConfig = config;
    }

    static loadTinyMCEScript( src ) {
        if ( this.isLoaded ) {
            return Promise.resolve( this.tinyMCE );
        }

        if ( this.loadingPromise ) {
            return this.loadingPromise;
        }

        this.loadingPromise = new Promise( ( resolve, reject ) => {
            const script = document.createElement( 'script' );
            script.src = src;
            script.onload = () => {
                SmartWooEditor.tinyMCE = window.tinymce;
                SmartWooEditor.tinyMCE.baseURL = `${smart_woo_vars.smartwoo_assets_url}editor/tinymce/`;
                this.isLoaded = true;
                resolve( this.tinyMCE );
            };
            script.onerror = () => reject( new Error( 'Failed to load TinyMCE' ) );
            document.body.appendChild( script );
        } );

        return this.loadingPromise;
    }

    static getAllowedElements() {
        return [
            'a[href|target|title|rel|class|style|data-*|aria-*|download]',
            'abbr[title|class|style|data-*|aria-*]',
            'acronym[title|class|style|data-*|aria-*]',
            'b[class|style|data-*|aria-*]',
            'blockquote[cite|class|style|data-*|aria-*]',
            'br[class|style|data-*|aria-*]',
            'code[class|style|data-*|aria-*]',
            'div[id|class|style|title|data-*|aria-*|draggable|contenteditable]',
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
            'video[src|poster|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable]',
            'audio[src|controls|autoplay|loop|muted|preload|class|style|data-*|aria-*|draggable|contenteditable]',
            'svg[*]', 'path[*]', 'g[*]', 'use[*]'
        ].join(',');
    }

    /**
     * Parses and cleans the dirty editor content.
     */
    static cleanEditorContent = ( e ) => {
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
        body.querySelectorAll( '.smartwoo-replace-image, .drag-handle, .smartwoo-add-image, .editor-only' ).forEach( el => el.remove() );

        // Sanitize inline styles
        body.querySelectorAll( '[style]' ).forEach( el => {
            let style = el.getAttribute( 'style' );
            if ( ! style ) return;

            styleCleanupPatterns.forEach( pattern => {
                style = style.replace( pattern, '' );
            } );

            el.setAttribute( 'style', style.trim() );
        } );

        // clean video block
        body.querySelectorAll( '.smartwoo-video-player-container' ).forEach( el => {
            el.querySelector( '.smartwoo-video-player__frame' )?.classList.remove( 'is-hovered', 'is-paused', 'is-portrait' );
            el.querySelector( '.smartwoo-video-player__frame' )?.removeAttribute( 'style' );
            el.querySelector( '.smartwoo-play' )?.removeAttribute( 'style' );
            el.querySelector( '.smartwoo-pause' )?.setAttribute( 'style', 'display: none' );
        });

        e.content = body.innerHTML;        
    }
    /**
     * Initialize a TinyMCE editor instance on the specified selector.
     *
     * Loads the TinyMCE script (if not already loaded), merges the default config
     * with user-supplied options, registers custom buttons and event hooks,
     * and stores the created editor(s) in a static class property.
     *
     * @async
     * @returns {Promise<tinymce.Editor|tinymce.Editor[]>} The initialized editor instance,
     *          or an array of editors if multiple were created for the selector.
     *
     * @example
     * const editor = await smartWooEditorInstance.init();
     *
     * @see SmartWooEditor.editors – Stores all initialized editor instances.
     */
    async init() {
        const tinyMCE = await SmartWooEditor.loadTinyMCEScript(
            `${smart_woo_vars.smartwoo_assets_url}editor/tinymce/tinymce.min.js`
        );

        const defaultConfig = {
            selector: this.selector,
            skin: 'oxide',
            branding: false,
            license_key: 'gpl',
            menubar: 'file edit insert format table',
            plugins: 'lists link image media table code preview fullscreen autosave wordcount searchreplace visualblocks insertdatetime emoticons',
            toolbar: 'add_media_button | styles | alignleft aligncenter alignjustify alignright bullist numlist outdent indent | forecolor backcolor | code fullscreen preview | undo redo',
            height: 600,
            relative_urls: false,
            remove_script_host: false,
            promotion: false,
            content_css: [
                smart_woo_vars.dashicons_asset_url,
                smart_woo_vars.editor_css_url
            ],
            extended_valid_elements: SmartWooEditor.getAllowedElements(),
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

                editor.on( 'GetContent', SmartWooEditor.cleanEditorContent );
                editor.on( 'init', () => SmartWooEditor.decorateEditor( editor ) );
            }
        };
        const editors = await tinyMCE.init( Object.assign( {}, defaultConfig, this.userConfig ) );

        SmartWooEditor.editors.push( ...editors );

        return editors.length === 1 ? editors[0] : editors;
    }

    /**
     * Save the contents of all initialized TinyMCE editors.
     *
     * This method loops through the static `editors` array and
     * triggers the save operation for each instance.
     *
     * Useful before form submission or manual content processing.
     *
     * @static
     */
    static saveAll() {
        if ( Array.isArray( SmartWooEditor.editors ) && SmartWooEditor.editors.length ) {
            SmartWooEditor.editors.forEach( ( editor ) => {
                if ( editor && typeof editor.save === 'function' ) {
                    editor.save();
                }
            } );
        }
    }

    /**
     * Restores cleaned html content for all collections
     */
    static decorateEditor = ( editor ) => {
        const editorBody = editor.getBody();
        editorBody.querySelectorAll( '.smartwoo-video-player-container' ).forEach( ( el ) => restoreVideoPlaylistBlock( el, editor ) );

    }
}

document.addEventListener( 'DOMContentLoaded', async function () {
    const editorInstance = new SmartWooEditor();
    await editorInstance.init();
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
            // editor.on( 'SetContent', () => callbackScript && callbackScript( editor ) );
            // editor.on( 'Undo', () => callbackScript && callbackScript( editor ) );
            // editor.on( 'Redo', () => callbackScript && callbackScript( editor ) );
            editor.on( 'init change undo redo SetContent', () => callbackScript && callbackScript( editor ) );
            
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
            return [smartwooAssetEditorBuildGallery, smartwooImageGalleryBindEvents];
        case 'video':
            return [smartwooAssetEditorBuildVideoPlaylist, smartwooEnableVideoPlaylist];
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
        <div class="smartwoo-gallery" style="" contenteditable="false">
            ${images.map( (img, index) => `
                <div class="smartwoo-gallery-item" draggable="true" data-item-index="${index}" contenteditable="true"
                    style="cursor: move; background: #fafafa; border: 1px solid #ccc;">
                    <div class="smartwoo-image-wrapper">
                        <img src="${img.url}" alt="${img.alt}" title="${img.title}" 
                            data-image-index="${index}" draggable="false"
                            style="width: 100%; height: auto; display: block; overflow: auto; max-width: 100%; min-height: 60px;" contenteditable="true" />

                        <div class="smartwoo-image-actions" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0; transition: opacity 0.2s;
                            display: flex; gap: 10px; justify-content: center; width: 100%;" contenteditable="false">
                            <button type="button" title="Replace" class="smartwoo-replace-image" data-image-index="${index}"
                                style="background: rgba(0, 0, 0, 0.5); color: #fff; border: none;
                                    border-radius: 4px; padding: 6px 10px; font-size: 14px; cursor: pointer;" contenteditable="false">
                                <span class="dashicons dashicons-edit" contenteditable="false"></span>
                                Replace
                            </button>

                            <button type="button" title="Delete" class="smartwoo-delete-image" data-image-index="${index}"
                                style="background: rgba(0, 0, 0, 0.5); color: #fff; border: none;
                                    border-radius: 4px; padding: 6px 10px; font-size: 14px; cursor: pointer;">
                                <span class="dashicons dashicons-trash"></span>
                                Delete
                            </button>
                        </div>
                        
                    </div>
                </div>

            `).join('')}
            <div class="editor-only" contenteditable="false" style="position: relative; cursor: pointer; width: calc(33.333% - 12px); min-width: 150px; box-sizing: border-box; background: #fafafa; border: 1px solid #ccc; padding: 8px; border-radius: 6px;">
                <div class="smartwoo-add-image" title="Add image">
                    <span class="dashicons dashicons-plus-alt add-icon"></span>
                </div>
            </div>
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
    // Process and normalize audio file data
    const audios = selection.map( file => ( {
        id: file.id || null,
        url: file.url,
        title: file.title || 'Untitled',
        artist: file.meta?.artist || file.artist || file.authorName || 'Unknown Artist',
        duration: file.fileLength || null,
        durationHuman: file.fileLengthHumanReadable || '',
        thumbnail: file.thumb?.src || file.image?.src || 'data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2264%22%20height%3D%2264%22%20viewBox%3D%220%200%2064%2064%22%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22g%22%20x1%3D%220%25%22%20y1%3D%220%25%22%20x2%3D%22100%25%22%20y2%3D%22100%25%22%3E%3Cstop%20offset%3D%220%25%22%20stop-color%3D%22%2366ccff%22/%3E%3Cstop%20offset%3D%22100%25%22%20stop-color%3D%22%236600ff%22/%3E%3C/linearGradient%3E%3C/defs%3E%3Ccircle%20cx%3D%2232%22%20cy%3D%2232%22%20r%3D%2230%22%20fill%3D%22url(%23g)%22%20stroke%3D%22%23fff%22%20stroke-width%3D%222%22/%3E%3Cpath%20d%3D%22M44%2016v26.2a6.8%206.8%200%201%201-2-4.8V24h-14v18.2a6.8%206.8%200%201%201-2-4.8V20a2%202%200%200%201%202-2h16a2%202%200%200%201%202%202z%22%20fill%3D%22%23fff%22/%3E%3C/svg%3E',
        mime: file.mime,
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
            mime: file.mime || 'video/mp4',
            desciption: file.description || '',
            artist: file.meta?.artist || file.artist || 'Unknown Artist',
            album: file.meta?.album || '',
            duration: file.fileLength || null,
            durationHuman: file.fileLengthHumanReadable || '',
        };
    });

    const playlistJson = JSON.stringify( videos ).replace( /"/g, '&quot;' );
    let firstVideo = videos[0];

    // Build individual playlist items
    const playlistItems = videos.map( ( video, index ) => `
        <li class="smartwoo-video-playlist-item" data-index="${index}" draggable="true" contenteditable="true">
            <img src="${smart_woo_vars.smartwoo_assets_url}images/video-playlist-icon.svg" class="smartwoo-video-playlist-item_image" alt="${escHtml( video.title )}">
            <p class="smartwoo-playlist__title">${escHtml( video.title )}</p>
            <span class="drag-handle" title="Reorder"></span>
        </li>
    ` ).join( '' );

    const playlistHtml = `
        <div class="smartwoo-video-player-container" contenteditable="false" data-playlist="${encodeURIComponent( playlistJson )}">
            <div class="smartwoo-video-player-left" contenteditable="false">
                <div class="smartwoo-video-player__frame" contenteditable="false">
                    <video src="${firstVideo.url}" class="smartwoo-video-player__video" controls preload="auto">
                        Your browser does not support the video format.
                    </video>
                    <div class="smartwoo-video-nowplaying-info">
                        <span class="smartwoo-current-title">${firstVideo.title}</span> | <span class="smartwoo-current-artist">${firstVideo.artist}</span>
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
                    ${playlistItems}
                </ul>
            </div>
        </div> 
    `;

    return playlistHtml;
}

/**
 * Restore interactive video playlist functionality from cleaned HTML block.
 *
 * @param {HTMLElement} container - The root `.smartwoo-video-player-container` element in the editor DOM.
 * @param {tinymce.Editor} editor - The editor instance
 */
function restoreVideoPlaylistBlock( container, editor ) {
    if ( ! container || ! container.classList.contains( 'smartwoo-video-player-container' ) ) {
        return;
    }

    const playlistData = container.getAttribute( 'data-playlist' );    

    let videos;
    try {
        videos = JSON.parse( decodeURIComponent( playlistData ).replace(/&quot;/g, '"' ) );
    } catch ( e ) {
        console.warn( 'Invalid playlist JSON:', e );
        return;
    }

    const playlistEl = container.querySelector( '.smartwoo-video-player-playlist-container' );
    const videoPlayerRight = container.querySelector( '.smartwoo-video-player-right' );
    const videoFrame = container.querySelector( '.smartwoo-video-player__frame' );
    const firstVideo = videos[0];

    container?.setAttribute( 'contenteditable', false );
    playlistEl?.setAttribute( 'contenteditable', false );
    videoFrame?.setAttribute( 'contenteditable', false );
    videoPlayerRight?.setAttribute( 'contenteditable', false );
    playlistEl?.querySelector( '.smartwoo-video-player-left' )?.setAttribute( 'contenteditable', false );
    videoPlayerRight?.querySelectorAll( 'h1, h2, h3, h4, h5, h6, p' )?.forEach( el => el.setAttribute( 'contenteditable', true ) );
    videoFrame?.querySelector( 'video' )?.setAttribute( 'contenteditable', false );
    // --- Restore Current Video Title and Artist ---
    const titleEl = container?.querySelector( '.smartwoo-current-title' );
    const artistEl = container?.querySelector( '.smartwoo-current-artist' );
    titleEl.textContent = firstVideo?.title;
    artistEl.textContent = firstVideo?.artist;

    // --- Restore Control Buttons and Timing ---
    const controlsHtml = `
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
    `;

    const existingControls = container?.querySelector( '.smartwoo-video-player-controls' );
    if ( ! existingControls ) {
        videoFrame.insertAdjacentHTML( 'beforeend', controlsHtml );
    } else {
        existingControls.setAttribute( 'contenteditable', 'false' );
        container?.querySelectorAll( '.smartwoo-control' )?.forEach( el => el.setAttribute( 'contenteditable', 'false' ) );
    }

    // --- Restore Dragging and Title Editing on Playlist Items ---
    playlistEl?.querySelectorAll( '.smartwoo-video-playlist-item' )?.forEach( ( item, index ) => {
        item.setAttribute( 'draggable', 'true' );
        item.setAttribute( 'contenteditable', 'true' );
        item.dataset.index = index;

        // Restore drag handle if missing
        if ( ! item.querySelector( '.drag-handle' ) ) {
            const dragHandle = document.createElement( 'span' );
            dragHandle.className = 'drag-handle';
            dragHandle.title = 'Reorder';
            item.appendChild( dragHandle );
        }
    } );

    // --- Bind JS Events ---
    smartwooAssetEditorResolveHtmlBuilder( 'video' )[1]( editor );
}






let draggedItem = null;

/**
 * Bind gallery event handlers using event delegation.
 *
 * @param {tinymce.Editor} editor
 */
function smartwooImageGalleryBindEvents( editor ) {
    const body = editor.getBody();
    const galleryContainer = body.querySelector( '.smartwoo-gallery' );

    if ( ! galleryContainer ) return;

    // Delegate click events
    galleryContainer.addEventListener( 'click', ( event ) => {
        const target = event.target.closest( '.smartwoo-add-image, .smartwoo-replace-image, .smartwoo-delete-image' );
        if ( ! target ) return;

        event.preventDefault();

        if ( target.classList.contains( 'smartwoo-add-image' ) ) {
            addImageToGallery( editor );
            event.stopImmediatePropagation();
            return;
        }

        if ( target.classList.contains( 'smartwoo-replace-image' ) ) {
            const imgEl = target.closest( '.smartwoo-gallery-item' )?.querySelector( 'img' );
            if ( ! imgEl ) return;
            
            const mediaFrame = wp.media({
                title: 'Replace Image',
                multiple: false,
                library: { type: 'image' },
                button: { text: 'Replace Image' }
            });

            mediaFrame.on( 'select', () => {
                const attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
                imgEl.src = attachment.url;
                imgEl.setAttribute( 'alt', attachment.alt || '' );
                imgEl.setAttribute( 'title', attachment.title || '' );
            });

            mediaFrame.open();
            event.stopImmediatePropagation();
            return;
        }

        if ( target.classList.contains( 'smartwoo-delete-image' ) ) {
            const imageItem = target.closest( '.smartwoo-gallery-item' );
            if ( imageItem ) {
                editor.undoManager.transact( () => {
                    editor.dom.remove( imageItem );
                });
            }
        }

    });

    // Delegate hover (mouseover/mouseleave) for replace button opacity
    galleryContainer.addEventListener( 'mouseover', ( event ) => {
        const btn = event.target.closest( '.smartwoo-image-actions' );
        if ( btn ){
            btn.style.opacity = '1';
            setTimeout( () => btn.style.opacity = '0', 3000 )
        }
    });

    galleryContainer.addEventListener( 'mouseleave', ( event ) => {
        const btn = event.target.closest( '.smartwoo-image-actions' );
        if ( btn ) btn.style.opacity = '0';
    });

    // Delegate drag events for gallery items
    let draggedItem = null;

    galleryContainer.addEventListener( 'dragstart', ( event ) => {
        const item = event.target.closest( '.smartwoo-gallery-item' );
        if ( item ) {
            draggedItem = item;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData( 'text/plain', item.getAttribute( 'data-item-index' ) );
            item.classList.add( 'dragging' );
        }
    });

    galleryContainer.addEventListener( 'dragover', ( event ) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    });

    galleryContainer.addEventListener( 'drop', ( event ) => {
        event.preventDefault();
        const target = event.target.closest( '.smartwoo-gallery-item' );

        if ( draggedItem && target && draggedItem !== target ) {
            const parent = draggedItem.parentNode;
            const fromIndex = parseInt( draggedItem.getAttribute( 'data-item-index' ), 10 );
            const toIndex = parseInt( target.getAttribute( 'data-item-index' ), 10 );

            parent.insertBefore( draggedItem, toIndex < fromIndex ? target : target.nextSibling );
        }

        draggedItem = null;
    });

    galleryContainer.addEventListener( 'dragend', ( event ) => {
        event.target.classList.remove( 'dragging' );
    });
}

/**
 * Helper function to add more images to the existing gallery.
 * @param {tinymce.Editor} editor 
 * @returns {void}
 */
function addImageToGallery( editor ) {
    const body = editor.getBody();
    const galleryContainer = body.querySelector( '.smartwoo-gallery' );
    if ( ! galleryContainer ) return;

    const frame = wp.media({
        title: 'Add Images to Gallery',
        multiple: true,
        library: { type: 'image' },
        button: { text: 'Add Selected Images' }
    });

    frame.on( 'select', () => {
        const selection = frame.state().get( 'selection' );

        selection.each( function( attachment ) {
            attachment = attachment.toJSON();

            const newItem   = document.createElement( 'div' );
            const index     = Date.now();
            newItem.className = 'smartwoo-gallery-item';
            newItem.setAttribute( 'draggable', 'true' );
            newItem.setAttribute( 'contenteditable', 'true' );
            newItem.setAttribute( 'data-item-index', index );
            newItem.style = `
                position: relative; cursor: move; width: calc(33.333% - 12px); min-width: 150px;
                box-sizing: border-box; background: #fafafa; border: 1px solid #ccc;
                padding: 8px; border-radius: 6px;
            `;

            newItem.innerHTML = `
                <div class="smartwoo-image-wrapper" style="position: relative; overflow: hidden;">
                    <img src="${attachment.url}" alt="${attachment.alt || ''}" title="${attachment.title || ''}"
                        draggable="false"
                        style="width: 100%; height: auto; display: block; max-width: 100%; min-height: 60px;" />
                    <div class="smartwoo-image-actions" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0; transition: opacity 0.2s;
                        display: flex; gap: 10px; justify-content: center; width: 100%;" contenteditable="false">
                        <button type="button" title="Replace" class="smartwoo-replace-image" data-image-index="${index}"
                            style="background: rgba(0, 0, 0, 0.5); color: #fff; border: none;
                                border-radius: 4px; padding: 6px 10px; font-size: 14px; cursor: pointer;" contenteditable="false">
                            <span class="dashicons dashicons-edit" contenteditable="false"></span>
                            Replace
                        </button>

                        <button type="button" title="Delete" class="smartwoo-delete-image" data-image-index="${index}"
                            style="background: rgba(0, 0, 0, 0.5); color: #fff; border: none;
                                border-radius: 4px; padding: 6px 10px; font-size: 14px; cursor: pointer;" contenteditable="false">
                            <span class="dashicons dashicons-trash" contenteditable="false"></span>
                            Delete
                        </button>
                    </div>
                </div>
            `;

            const plusBlock = galleryContainer.querySelector( '.smartwoo-add-image' )?.parentNode;
            galleryContainer.insertBefore( newItem, plusBlock );
        });
    });

    frame.open();
}


function smartwooEnableAudioPlaylist( editor ) {
    const audioPlayers  = editor.getBody().querySelectorAll( '.smartwoo-audio-playlist' );
    const playlistItems = editor.getBody().querySelectorAll( '.smartwoo-playlist__item' );

    audioPlayers.forEach( player => {
        new SmartwooAudioPlayer( player );
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

function smartwooEnableVideoPlaylist( editor ) {
    let allVideoPlaylist    = editor.getBody().querySelectorAll( '.smartwoo-video-player-container' );
    let allVideos           = editor.getBody().querySelectorAll( 'video.smartwoo-video-player__video' );
    let playlistItems       = editor.getBody().querySelectorAll( '.smartwoo-video-playlist-item' );
    
    allVideoPlaylist.forEach( async ( playlist ) => {
        new SmartwooVideoPlayer(playlist);

    });

    editor.on( 'execCommand', (e) => {
        if ( 'mcePreview' === e.command ) {
            // Delay to wait for the preview dialog to be rendered
            setTimeout(() => {
                const dialog = document.querySelector( '.tox-dialog-wrap' );
                if ( ! dialog ) return;
                
                const iframe = dialog.querySelector( 'iframe' );
                if ( ! iframe ) return;

                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                const currentPlayer = iframeDoc.querySelector( '.smartwoo-video-player-container' );
                if ( currentPlayer ) {
                    new SmartwooVideoPlayer( currentPlayer );
                }
            }, 200);
        }
    });

    allVideos.forEach( async ( player ) => {
        let thumbnail   = await smartwooGetVideoThumbnail( player.src );
        player.poster   = thumbnail;
        player.removeAttribute( 'controls' );
        player.removeAttribute( 'controlist' );
        player.removeAttribute( 'height' );
        player.removeAttribute( 'width' );
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
 * Helper function to escape HTML character
 * 
 * @param {String} str - The unsafe string to escape.
 */
function escHtml( str ) {
    let div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML.replace(/"/g, '&quot;');
}