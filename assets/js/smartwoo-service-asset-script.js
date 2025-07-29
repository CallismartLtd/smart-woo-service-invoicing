/**
 * Load the asset audio player and initialize its functionality.
 *
 * @param {HTMLElement} assetAudioPlayer - The main container for the audio player.
 * @return {HTMLAudioElement}   audio - The audio element used for playback.
 * @description This function sets up the audio player with controls, playlist management,
 */
function smartWooinitAssetAudioPlayer( assetAudioPlayer ) {
    // --- Get DOM Elements ---
    /**
     * @type {HTMLAudioElement} audio - The audio element used for playback.
     */
    const audio             = document.createElement( 'audio' );
    const playlistDataAttr  = assetAudioPlayer.dataset.playlist;
    const playlist          = playlistDataAttr ? JSON.parse( playlistDataAttr.replace(/&quot;/g, '"') ) : []; // Parse playlist data
    
    // Player controls.
    const playPauseToggleContainer  = assetAudioPlayer.querySelector( '.smartwoo-audio-player.play-pause-toggle' );
    const playBtn                   = playPauseToggleContainer.querySelector( '.smartwoo-play' );
    const pauseBtn                  = playPauseToggleContainer.querySelector( '.smartwoo-pause' );
    const prevBtn                   = assetAudioPlayer.querySelector( '.smartwoo-prev' );
    const nextBtn                   = assetAudioPlayer.querySelector( '.smartwoo-next' );

    // Seek bar elements.
    const progressContainer = assetAudioPlayer.querySelector( '.smartwoo-audio-player__progress' );
    const progressBar       = assetAudioPlayer.querySelector( '.smartwoo-progress-bar' );
    const currentTimeSpan   = assetAudioPlayer.querySelector( '.smartwoo-time-current' );
    const durationSpan      = assetAudioPlayer.querySelector( '.smartwoo-time-duration' );

    // Volume controls.
    const volumeToggleIcon      = assetAudioPlayer.querySelector( '.smartwoo-volume-toggle' );
    const volumeSliderContainer = assetAudioPlayer.querySelector( '.smartwoo-volume-slider' );
    const volumeProgressBar     = assetAudioPlayer.querySelector( '.smartwoo-volume-progress' );

    // Now Playing info.
    const currentTitleSpan      = assetAudioPlayer.querySelector( '.smartwoo-current-title' );
    const currentArtistSpan     = assetAudioPlayer.querySelector( '.smartwoo-current-artist' );
    const thumbnailImg          = assetAudioPlayer.querySelector( '.smartwoo-thumbnail' );

    // Playlist elements.
    const playlistItemsContainer    = assetAudioPlayer.querySelector( 'ul.smartwoo-playlist' );
    const playlistToggle            = assetAudioPlayer.querySelector( '.smartwoo-audio-player__control-group.smartwoo-playlist-control .dashicons-playlist-audio' );
    
    // --- State Variables ---
    let currentTrackIndex   = 0;
    let isPlaying           = false;
    let isSeeking           = false;
    let isVolumeDragging    = false;
    let lastVolume          = localStorage.getItem('smartwoo-audio-volume') ? parseFloat(localStorage.getItem('smartwoo-audio-volume')) : 0.5;
    audio.volume            = lastVolume;
    

    // --- Helper Functions ---

    /**
     * Formats time from seconds to MM:SS string.
     * @param {number} seconds - The time in seconds.
     * @returns {string} Formatted time string.
     */
    function formatTime( seconds ) {
        if (isNaN(seconds) || seconds < 0) return '0:00';
        const minutes           = Math.floor( seconds / 60 );
        const remainingSeconds  = Math.floor( seconds % 60 );
        return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
    }

    /**
     * Escape HTML for safe output.
     * 
     * @param {string} unsafe - Unsafe input string.
     * @returns {string} Safe HTML string.
     */
    function escHtml( unsafe ) {
        let div         = document.createElement( 'div' );
        div.textContent = unsafe;
        return div.innerHTML;
    }

    /**
     * Loads a track into the audio element and updates player info.
     * @param {number} index - The index of the track in the playlist.
     */
    function loadTrack( index ) {
        if ( index < 0 || index >= playlist.length ) {
            console.warn( 'Track index out of bounds:', index );
            return;
        }

        currentTrackIndex   = index;
        const track         = playlist[currentTrackIndex];

        audio.src                       = track.url;
        currentTitleSpan.textContent    = escHtml(track.title);
        currentArtistSpan.textContent   = escHtml(track.artist);
        thumbnailImg.src                = escHtml(track.thumbnail);
        thumbnailImg.alt                = escHtml(track.title || 'Audio thumbnail');

        // Reset progress bar
        progressBar.style.width = '0%';
        currentTimeSpan.textContent = '0:00';
        durationSpan.textContent = '0:00'; // Will be updated on loadedmetadata

        // Update active class in playlist
        playlistItemsContainer.querySelectorAll('.smartwoo-playlist__item').forEach(item => {
            item.classList.remove('is-active');
        });
        playlistItemsContainer.querySelector(`[data-index="${currentTrackIndex}"]`).classList.add('is-active');

        // Load the audio and then play if it was playing
        audio.load();
        if ( isPlaying ) {
            audio.play().catch(e => console.error("Autoplay prevented:", e));
        }
    }

    /**
     * Toggles play/pause state.
     */
    function togglePlayPause() {
        if ( isPlaying ) {
            audio.pause();
            // playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-play dashicons dashicons-controls-play" title="Play"></span>`;
            isPlaying = false;
            playBtn.style.removeProperty( 'display' );
            pauseBtn.style.setProperty('display', 'none');
        } else {
            audio.play().catch(e => console.error("Autoplay prevented:", e));
            // playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-pause dashicons dashicons-controls-pause" title="Pause"></span>`;
            isPlaying = true;
            playBtn.style.setProperty('display', 'none');
            pauseBtn.style.removeProperty( 'display' );
        }
    
    }

    /**
     * Attaches click listeners to play/pause buttons.
     * Needed because their HTML changes on toggle.
     */
    function attachPlayPauseListeners() {
        if (playBtn) {
            playBtn.removeEventListener('click', togglePlayPause); // Prevent multiple listeners
            playBtn.addEventListener('click', togglePlayPause);
        }
        if (pauseBtn) {
            pauseBtn.removeEventListener('click', togglePlayPause); // Prevent multiple listeners
            pauseBtn.addEventListener('click', togglePlayPause);
        }
    }

    /**
     * Updates the seek bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     */
    function updateSeekBar( e ) {
        const rect = progressContainer.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        let x = clientX - rect.left;
        x = Math.max( 0, Math.min( x, rect.width ) ); // Clamp x within container bounds
        const percent = x / rect.width;

        progressBar.style.width = `${percent * 100}%`;
        currentTimeSpan.textContent = formatTime( percent * audio.duration );

        if ( isSeeking ) {
            audio.currentTime = percent * audio.duration;
        }
    }

    /**
     * Updates the volume bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     */
    function updateVolumeBar( e ) {
        const rect = volumeSliderContainer.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        let x = clientX - rect.left;
        x = Math.max( 0, Math.min( x, rect.width ) ); // Clamp x within container bounds
        const percent = x / rect.width;
        
        
        audio.volume = percent;
        volumeProgressBar.style.width = `${percent * 100}%`;
        
        // Update volume icon
        if (audio.volume === 0) {
            volumeToggleIcon.className = 'dashicons dashicons-controls-volumeoff smartwoo-volume-toggle';
            volumeToggleIcon.title = 'Unmute';
        } else {
            volumeToggleIcon.className = 'dashicons dashicons-controls-volumeon smartwoo-volume-toggle';
            volumeToggleIcon.title = 'Mute';
        }

        localStorage.setItem('smartwoo-audio-volume', audio.volume);

    }

    // --- Event Listeners ---

    playlistToggle.addEventListener( 'click', ( e ) => {
        e.preventDefault();
        assetAudioPlayer.classList.toggle( 'playlist-active' );
    });

    audio.addEventListener( 'loadedmetadata', () => {
        durationSpan.textContent = formatTime( audio.duration );
        // If initial load and player is not set to auto-play, pause
        if (!isPlaying && !audio.autoplay) {
            audio.pause(); // Ensure initial state is paused if not auto-playing
        }
        // Update volume bar initially based on default audio volume
        updateVolumeBar({ clientX: volumeSliderContainer.getBoundingClientRect().width * audio.volume + volumeSliderContainer.getBoundingClientRect().left });
    });

    audio.addEventListener( 'timeupdate', () => {
        if ( !isSeeking ) { // Only update if user isn't dragging
            const progress = ( audio.currentTime / audio.duration ) * 100;
            progressBar.style.width = `${progress}%`;
            currentTimeSpan.textContent = formatTime( audio.currentTime );
        }
    });

    audio.addEventListener( 'ended', () => {
        if ( currentTrackIndex < playlist.length - 1 ) {
            loadTrack( currentTrackIndex + 1 );
            audio.play();
        } else {
            loadTrack( 0 );
        }
    });
    
    // Handle cases where network errors or audio loading issues occur
    audio.addEventListener( 'error', (e) => {
        console.error('Audio error:', e);
        if (isPlaying) { // If it was trying to play, stop it.
            isPlaying = false;
            playBtn.style.removeProperty( 'display' );
            pauseBtn.style.setProperty('display', 'none');
            audio.pause();
        }
    });

    attachPlayPauseListeners();

    prevBtn.addEventListener( 'click', () => {
        if ( currentTrackIndex > 0 ) {
            loadTrack( currentTrackIndex - 1 );
        } else {
            loadTrack( playlist.length - 1 ); // Loop to last track
        }
    });

    nextBtn.addEventListener( 'click', () => {
        if ( currentTrackIndex < playlist.length - 1 ) {
            loadTrack( currentTrackIndex + 1 );
        } else {
            loadTrack( 0 );
        }
    });

    // Seek Bar Interaction
    let seekMoveListener = null; // Store move listener to remove it
    let seekUpListener = null;   // Store up listener to remove it

    progressContainer.addEventListener( 'mousedown', (e) => {
        isSeeking = true;
        updateSeekBar(e);
        
        seekMoveListener = (e) => updateSeekBar(e);
        seekUpListener = () => {
            isSeeking = false;
            assetAudioPlayer.removeEventListener('mousemove', seekMoveListener);
            assetAudioPlayer.removeEventListener('mouseup', seekUpListener);
            if (isPlaying) audio.play(); // Resume play after seek if it was playing
        };

        assetAudioPlayer.addEventListener( 'mousemove', seekMoveListener );
        assetAudioPlayer.addEventListener( 'mouseup', seekUpListener );
    });

    // Touch events for mobile
    progressContainer.addEventListener( 'touchstart', (e) => {
        isSeeking = true;
        updateSeekBar(e);
    });
    assetAudioPlayer.addEventListener( 'touchmove', (e) => {
        if (isSeeking) {
            updateSeekBar(e);
            e.preventDefault();
        }
    }, { passive: false });
    assetAudioPlayer.addEventListener( 'touchend', () => {
        if (isSeeking) {
            isSeeking = false;
            if (isPlaying) audio.play();
        }
    });


    // Volume Control Interaction
    let volumeMoveListener = null;
    let volumeUpListener = null;

    volumeSliderContainer.addEventListener( 'mousedown', (e) => {
        isVolumeDragging = true;
        updateVolumeBar(e);

        volumeMoveListener  = (e) => updateVolumeBar(e);
        volumeUpListener    = () => {
            isVolumeDragging = false;
            assetAudioPlayer.removeEventListener('mousemove', volumeMoveListener);
            assetAudioPlayer.removeEventListener('mouseup', volumeUpListener);
        };
        
        assetAudioPlayer.addEventListener( 'mousemove', volumeMoveListener );
        assetAudioPlayer.addEventListener( 'mouseup', volumeUpListener );
    });

    // Touch events for volume
    volumeSliderContainer.addEventListener( 'touchstart', (e) => {
        isVolumeDragging = true;
        updateVolumeBar(e);
    });
    assetAudioPlayer.addEventListener( 'touchmove', (e) => {
        if (isVolumeDragging) {
            updateVolumeBar(e);
            e.preventDefault();
        }
    }, { passive: false });
    assetAudioPlayer.addEventListener( 'touchend', () => {
        if (isVolumeDragging) {
            isVolumeDragging = false;
        }
    });

    volumeToggleIcon.addEventListener('click', () => {
        if ( audio.volume > 0 ) {
            lastVolume      = audio.volume;
            audio.volume    = 0;
        } else {
            audio.volume = lastVolume > 0 ? lastVolume : 0.5;
        }
        // updateVolumeBar will handle icon change based on audio.volume
        updateVolumeBar({ clientX: volumeSliderContainer.getBoundingClientRect().width * audio.volume + volumeSliderContainer.getBoundingClientRect().left });
    });

    playlistItemsContainer.addEventListener('click', (e) => {
        const item = e.target.closest('.smartwoo-playlist__item');
        if (item) {
            const index = parseInt(item.dataset.index);
            if (index !== currentTrackIndex) {
                loadTrack(index);
                
                if ( ! isPlaying ) {
                    togglePlayPause();
                    isPlaying = true;
                }
                

            }
        }
    });

    if ( playlist.length > 0 ) {
        loadTrack( 0 );
    } else {
        console.warn('Audio playlist is empty.');
    }

    return audio;
}

const smartwooVideoThumbCache = new Map();

/**
 * Get thumbnail for a given video URL.
 *
 * @param {string} url - The video URL.
 * @return {Promise<string>} - The thumbnail URL or fallback image.
 */
async function smartwooGetVideoThumbnail( url ) {
    const fallback = 'data:image/svg+xml;base64,' + btoa(`
        <svg width="640" height="360" viewBox="0 0 640 360" xmlns="http://www.w3.org/2000/svg">
        <rect width="100%" height="100%" fill="#1e1e1e"/>
        <circle cx="320" cy="180" r="60" fill="#ffffff20" stroke="#ffffff60" stroke-width="4"/>
        <polygon points="310,150 350,180 310,210" fill="#ffffffb0"/>
        <text x="50%" y="85%" text-anchor="middle" font-family="Arial, sans-serif"
                font-size="16" fill="#cccccc">Video Thumbnail Unavailable</text>
        </svg>
    `);


    const filename = url.split( '/' ).pop();
    if ( smartwooVideoThumbCache.has( filename ) ) {
        return smartwooVideoThumbCache.get( filename );
    }

    try {
        const blobUrl = await new Promise( ( resolve, reject ) => {
            const video = document.createElement( 'video' );
            video.crossOrigin = 'anonymous';
            video.preload     = 'auto';
            video.muted       = true;
            video.playsInline = true;
            video.src         = url;

            video.addEventListener( 'error', () => reject( 'Video failed to load' ) );

            video.addEventListener( 'loadeddata', () => {
                try {
                    video.currentTime = 1;
                } catch (e) {
                    reject( 'Seeking failed: ' + e.message );
                }
            });

            video.addEventListener( 'seeked', () => {
                try {
                    const canvas = document.createElement( 'canvas' );
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;

                    const ctx = canvas.getContext( '2d' );
                    ctx.drawImage( video, 0, 0, canvas.width, canvas.height );

                    canvas.toBlob( blob => {
                        if ( blob ) {
                            const thumbnailUrl = URL.createObjectURL( blob );
                            resolve( thumbnailUrl );
                        } else {
                            reject( 'Canvas to blob failed' );
                        }
                    }, 'image/jpeg', 0.92 );
                } catch (err) {
                    reject( 'Drawing thumbnail failed: ' + err.message );
                }
            });
        });

        smartwooVideoThumbCache.set( filename, blobUrl );
        return blobUrl;

    } catch (error) {
        console.warn( 'Thumbnail generation failed:', error );
        return fallback;
    }
}

addEventListener( 'DOMContentLoaded', async () => {
    let assetAudioPlayers = document.querySelectorAll( '.smartwoo-audio-playlist' );
    
    assetAudioPlayers.forEach( ( player ) => {
        smartWooinitAssetAudioPlayer( player );
    });
    
smartwooGetVideoThumbnail('https://callismart.local/wp-content/uploads/2025/07/5493992aacd436b9faebba03af4f43c.mp4').then(url => {
    const img = new Image();
    img.src = url;
    document.body.appendChild(img);
}).catch(console.error);

});
