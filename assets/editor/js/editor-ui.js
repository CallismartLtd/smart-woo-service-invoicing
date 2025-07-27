

/**
 * Open the WordPress media library.
 * 
 * @param {Object} options - Options for the media library.
 */
async function smartwooAssetEditorOpenMediaLibrary( options ) {
    return new Promise( ( resolve ) => {
        const mediaLibrary = wp.media( {
            title: options.title || 'Select Media',
            button: {
                text: options.buttonText || 'Insert Media'
            },
            multiple: options.multiple || false,
            library: {
                type: options.type || 'image'
            }
        } );

        mediaLibrary.on( 'select', () => {
            const selectedFiles = mediaLibrary.state().get('selection').toJSON();
            resolve( selectedFiles );
        } );

        mediaLibrary.open();
    } );
}

document.addEventListener( 'DOMContentLoaded', function () {
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
        extended_valid_elements: 'span[class|title|data-*|aria-*],div[class|title|data-*|aria-*]',
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
            
            editor.on('SaveContent', function ( e ) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = e.content;
                tempDiv.querySelectorAll('[draggable], [contenteditable]').forEach(el => {
                    el.removeAttribute('draggable');
                    el.removeAttribute('contenteditable');
                });

                tempDiv.querySelectorAll('.smartwoo-replace-image').forEach(el => {
                    el.remove();
                });

                tempDiv.querySelectorAll('[style]').forEach( el => {
                    const style = el.getAttribute('style');
                    if ( style && style.includes('cursor: move') ) {
                        el.setAttribute('style', style.replace(/cursor:\s*move;?/gi, ''));
                    }
                });


                e.content = tempDiv.innerHTML;
                // console.log(e.content);
                
            });
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
            return smartwooAssetEditorBuildVideoPlaylist;
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
    const escHtml = str => ( str || '' )
        .replace( /&/g, '&amp;' )
        .replace( /</g, '&lt;' )
        .replace( />/g, '&gt;' )
        .replace( /"/g, '&quot;' );

    // Process and normalize audio file data
    const audios = selection.map( file => ( {
        id: file.id || null,
        url: file.url,
        title: file.title || 'Untitled',
        artist: file.meta?.artist || file.artist || file.authorName || 'Unknown Artist',
        duration: file.fileLength || null, // Keeping this if you need raw seconds
        durationHuman: file.fileLengthHumanReadable || '', // Keeping this for display if desired
        thumbnail: file.thumb?.src || file.image?.src || wp.media.defaults.audiosvg_src || 'data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2264%22%20height%3D%2264%22%20viewBox%3D%220%200%2064%2064%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M32%200C14.3%200%200%2014.3%200%2032s14.3%2032%2032%2032%2032-14.3%2032-32S49.7%200%2032%200zm0%2059.2c-14.9%200-27.2-12.3-27.2-27.2S17.1%204.8%2032%204.8s27.2%2012.3%2027.2%2027.2-12.3%2027.2-27.2%2027.2zM27.2%2017.6h9.6v19.2h-9.6zM46.4%2027.2c-.7%200-1.6.4-1.6%201.1v9.6c0%20.7.8%201.1%201.6%201.1.7%200%201.6-.4%201.6-1.1v-9.6c0-.7-.8-1.1-1.6-1.1zM17.6%2027.2c-.7%200-1.6.4-1.6%201.1v9.6c0%20.7.8%201.1%201.6%201.1.7%200%201.6-.4%201.6-1.1v-9.6c0-.7-.8-1.1-1.6-1.1zM32%2012.8c-1.3%200-2.4%201-2.4%202.4V24c0%201.3%201%202.4%202.4%202.4s2.4-1%202.4-2.4v-8.8c0-1.3-1-2.4-2.4-2.4z%22%20fill%3D%22%232C2E35%22%2F%3E%3C%2Fsvg%3E', // Default SVG fallback
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
        <li class="smartwoo-playlist__item" data-index="${index}">
            <span class="smartwoo-playlist__title">${escHtml( audio.title )}</span>
            ${ audio.artist ? `<span class="smartwoo-playlist__artist">${escHtml( audio.artist )}</span>` : '' }
        </li>
    ` ).join( '' );

    // Stringify and escape JSON for the data attribute
    // Note: JSON.stringify already escapes double quotes. We only need to ensure
    // that the attribute's wrapping quotes don't conflict. Since template literals
    // typically use double quotes for attributes if not specified,
    // JSON.stringify's default escaping is usually sufficient.
    const playlistJson = JSON.stringify( audios ).replace( /"/g, '&quot;' );


    const playlistHtml = `
        <div class="smartwoo-audio-playlist" contenteditable="false" data-playlist='${playlistJson}'>
            <div class="smartwoo-audio-player">
                <div class="smartwoo-audio-player__thumbnail">
                    <img class="smartwoo-thumbnail" src="${escHtml(firstTrack.thumbnail)}" alt="${escHtml(firstTrack.title || 'Audio thumbnail')}">
                </div>
                <div class="smartwoo-audio-player__layout">
                    <div class="smartwoo-audio-player__now-playing">
                        <span class="smartwoo-current-title">${escHtml( firstTrack.title )}</span>
                        <span>&#8226;</span>
                        <span class="smartwoo-current-artist">${escHtml( firstTrack.artist )}</span>
                    </div>

                    <div class="smartwoo-audio-player__seek">
                        <audio class="smartwoo-audio" hidden src="${escHtml(firstTrack.url)}" controls></audio>
                        <div class="smartwoo-audio-player__progress">
                            <div class="smartwoo-progress-bar"></div>
                            <div class="smartwoo-progress-bar__scrubber"></div>
                        </div>

                        <div class="smartwoo-audio-player__time">
                            <span class="smartwoo-time-current">0:00</span> / <span class="smartwoo-time-duration">0:00</span>
                        </div>
                    </div>

                    <div class="smartwoo-audio-player__controls">
                        <div class="smartwoo-audio-player__control-group smartwoo-audio-player-volume-container">
                            <span class="dashicons dashicons-controls-volumeon smartwoo-volume-toggle" title="Toggle Volume"></span>
                            <div class="smartwoo-volume-slider">
                                <div class="smartwoo-volume-progress"></div>
                                <div class="smartwoo-volume-scrubber"></div>
                            </div>
                        </div>

                        <div class="smartwoo-audio-player__control-group smartwoo-audio-player-controls">
                            <span class="smartwoo-control smartwoo-prev dashicons dashicons-controls-skipback" title="Previous"></span>
                            <div class="smartwoo-audio-player play-pause-toggle">
                                <span class="smartwoo-control smartwoo-pause" title="Pause"></span>
                            </div>
                            <span class="smartwoo-control smartwoo-next dashicons dashicons-controls-skipforward" title="Next"></span>
                        </div>

                        <div class="smartwoo-audio-player__control-group smartwoo-playlist-control">
                            <span class="dashicons dashicons-playlist-audio" title="Toggle Playlist"></span>
                        </div>
                    </div>
                </div>
                <div class="smartwoo-audio-player__playlist">
                    <ul class="smartwoo-playlist">
                        ${playlistItems}
                    </ul>
                </div>
            </div>
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

/**
 * Audio playlist interaction.
 * 
 * @param {Object} editor - The TinyMCE editor instance.
 */
function smartwooEnableAudioPlaylist( editor ) {
    const audioPlayers = editor.getBody().querySelectorAll( '.smartwoo-audio-playlist' );
    audioPlayers.forEach( player => {
        smartWooinitAssetAudioPlayer( player );
    });
}