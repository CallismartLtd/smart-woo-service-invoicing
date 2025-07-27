/**
 * Load the asset audio player and initialize its functionality.
 *
 * @param {HTMLElement} assetAudioPlayer - The main container for the audio player.
 */
function smartWooinitAssetAudioPlayer( assetAudioPlayer ) {
    // --- Get DOM Elements ---
    const audio             = assetAudioPlayer.querySelector( '.smartwoo-audio' );
    const playlistDataAttr  = assetAudioPlayer.dataset.playlist;
    const playlist          = playlistDataAttr ? JSON.parse( playlistDataAttr.replace(/&quot;/g, '"') ) : []; // Parse playlist data
    
    // Player controls
    const playPauseToggleContainer  = assetAudioPlayer.querySelector( '.smartwoo-audio-player.play-pause-toggle' );
    const playBtn                   = playPauseToggleContainer.querySelector( '.smartwoo-play' ); // Might be commented out in HTML initially
    const pauseBtn                  = playPauseToggleContainer.querySelector( '.smartwoo-pause' );
    const prevBtn                   = assetAudioPlayer.querySelector( '.smartwoo-prev' );
    const nextBtn                   = assetAudioPlayer.querySelector( '.smartwoo-next' );

    // Seek bar elements (custom div-based)
    const progressContainer = assetAudioPlayer.querySelector( '.smartwoo-audio-player__progress' );
    const progressBar       = assetAudioPlayer.querySelector( '.smartwoo-progress-bar' );
    const progressScrubber  = assetAudioPlayer.querySelector( '.smartwoo-progress-bar__scrubber' );
    const currentTimeSpan   = assetAudioPlayer.querySelector( '.smartwoo-time-current' );
    const durationSpan      = assetAudioPlayer.querySelector( '.smartwoo-time-duration' );

    // Volume controls (custom div-based)
    const volumeToggleIcon      = assetAudioPlayer.querySelector( '.smartwoo-volume-toggle' );
    const volumeSliderContainer = assetAudioPlayer.querySelector( '.smartwoo-volume-slider' );
    const volumeProgressBar     = assetAudioPlayer.querySelector( '.smartwoo-volume-progress' );
    const volumeScrubber        = assetAudioPlayer.querySelector( '.smartwoo-volume-scrubber' );

    // Now Playing info
    const currentTitleSpan      = assetAudioPlayer.querySelector( '.smartwoo-current-title' );
    const currentArtistSpan     = assetAudioPlayer.querySelector( '.smartwoo-current-artist' );
    const thumbnailImg          = assetAudioPlayer.querySelector( '.smartwoo-thumbnail' );

    // Playlist elements
    const playlistItemsContainer    = assetAudioPlayer.querySelector( 'ul.smartwoo-playlist' );
    const playlistToggle            = assetAudioPlayer.querySelector( '.smartwoo-audio-player__control-group.smartwoo-playlist-control .dashicons-playlist-audio' );
    
    // --- State Variables ---
    let currentTrackIndex   = 0;
    let isPlaying           = false;
    let isSeeking           = false; // To prevent timeupdate listener from interfering during drag
    let isVolumeDragging    = false;
    let lastVolume          = 1; // Store volume before muting

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
        progressScrubber.style.left = '0%';
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
            playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-play dashicons dashicons-controls-play" title="Play"></span>`;
            isPlaying = false;
        } else {
            audio.play().catch(e => console.error("Autoplay prevented:", e));
            playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-pause dashicons dashicons-controls-pause" title="Pause"></span>`;
            isPlaying = true;
        }
        // Re-query play/pause buttons after innerHTML update
        playBtn = playPauseToggleContainer.querySelector( '.smartwoo-play' );
        pauseBtn = playPauseToggleContainer.querySelector( '.smartwoo-pause' );
        attachPlayPauseListeners(); // Re-attach listeners
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
        progressScrubber.style.left = `${percent * 100}%`;
        currentTimeSpan.textContent = formatTime( percent * audio.duration );

        if ( isSeeking ) {
            audio.currentTime = percent * audio.duration;
        }
    }

    /**
     * Updates the volume bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     */
    // function updateVolumeBar( e ) {
    //     const rect = volumeSliderContainer.getBoundingClientRect();
    //     const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    //     let x = clientX - rect.left;
    //     x = Math.max( 0, Math.min( x, rect.width ) ); // Clamp x within container bounds
    //     const percent = x / rect.width;

    //     audio.volume = percent;
    //     volumeProgressBar.style.width = `${percent * 100}%`;
    //     volumeScrubber.style.left = `${percent * 100}%`;
        
    //     // Update volume icon
    //     if (audio.volume === 0) {
    //         volumeToggleIcon.className = 'dashicons dashicons-controls-volumeoff smartwoo-volume-toggle';
    //     } else if (audio.volume > 0 && audio.volume <= 0.5) {
    //         volumeToggleIcon.className = 'dashicons dashicons-controls-volumedown smartwoo-volume-toggle';
    //     } else {
    //         volumeToggleIcon.className = 'dashicons dashicons-controls-volumeon smartwoo-volume-toggle';
    //     }
    //     lastVolume = audio.volume; // Store current volume for unmute
    // }

    // --- Event Listeners ---

    // 1. Playlist Toggle (already implemented in your snippet)
    // playlistToggle.addEventListener( 'click', ( e ) => {
    //     e.preventDefault();
    //     assetAudioPlayer.classList.toggle( 'playlist-active' );
    // });

    // 2. Audio Element Events
    // audio.addEventListener( 'loadedmetadata', () => {
    //     durationSpan.textContent = formatTime( audio.duration );
    //     // If initial load and player is not set to auto-play, pause
    //     if (!isPlaying && !audio.autoplay) {
    //         audio.pause(); // Ensure initial state is paused if not auto-playing
    //     }
    //     // Update volume bar initially based on default audio volume
    //     updateVolumeBar({ clientX: volumeSliderContainer.getBoundingClientRect().width * audio.volume + volumeSliderContainer.getBoundingClientRect().left });
    // });

    // audio.addEventListener( 'timeupdate', () => {
    //     if ( !isSeeking ) { // Only update if user isn't dragging
    //         const progress = ( audio.currentTime / audio.duration ) * 100;
    //         progressBar.style.width = `${progress}%`;
    //         progressScrubber.style.left = `${progress}%`;
    //         currentTimeSpan.textContent = formatTime( audio.currentTime );
    //     }
    // });

    // audio.addEventListener( 'ended', () => {
    //     if ( currentTrackIndex < playlist.length - 1 ) {
    //         loadTrack( currentTrackIndex + 1 );
    //         audio.play();
    //     } else {
    //         // End of playlist, reset or loop
    //         isPlaying = false;
    //         playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-play dashicons dashicons-controls-play" title="Play"></span>`;
    //         attachPlayPauseListeners();
    //         audio.currentTime = 0; // Reset to start
    //         progressBar.style.width = '0%';
    //         progressScrubber.style.left = '0%';
    //         currentTimeSpan.textContent = '0:00';
    //         playlistItemsContainer.querySelectorAll('.smartwoo-playlist__item').forEach(item => {
    //             item.classList.remove('is-active');
    //         }); // Remove active class from all
    //     }
    // });
    
    // Handle cases where network errors or audio loading issues occur
    // audio.addEventListener('error', (e) => {
    //     console.error('Audio error:', e);
    //     // You might want to display an error message to the user
    //     // and try to load the next track or stop playback.
    //     if (isPlaying) { // If it was trying to play, stop it.
    //         isPlaying = false;
    //         playPauseToggleContainer.innerHTML = `<span class="smartwoo-control smartwoo-play dashicons dashicons-controls-play" title="Play"></span>`;
    //         attachPlayPauseListeners();
    //     }
    // });

    // // 3. Play/Pause Button (Re-attach in togglePlayPause if HTML changes)
    // attachPlayPauseListeners(); // Initial attachment

    // 4. Previous/Next Buttons
    // prevBtn.addEventListener( 'click', () => {
    //     if ( currentTrackIndex > 0 ) {
    //         loadTrack( currentTrackIndex - 1 );
    //     } else {
    //         // Loop to end or stay at start
    //         loadTrack( playlist.length - 1 ); // Loop to last track
    //     }
    // });

    // nextBtn.addEventListener( 'click', () => {
    //     if ( currentTrackIndex < playlist.length - 1 ) {
    //         loadTrack( currentTrackIndex + 1 );
    //     } else {
    //         // Loop to start or stay at end
    //         loadTrack( 0 ); // Loop to first track
    //     }
    // });

    // 5. Custom Seek Bar Interaction
    let seekMoveListener = null; // Store move listener to remove it
    let seekUpListener = null;   // Store up listener to remove it

    progressContainer.addEventListener( 'mousedown', (e) => {
        isSeeking = true;
        updateSeekBar(e); // Update immediately on click
        
        seekMoveListener = (e) => updateSeekBar(e);
        seekUpListener = () => {
            isSeeking = false;
            document.removeEventListener('mousemove', seekMoveListener);
            document.removeEventListener('mouseup', seekUpListener);
            if (isPlaying) audio.play(); // Resume play after seek if it was playing
        };

        document.addEventListener( 'mousemove', seekMoveListener );
        document.addEventListener( 'mouseup', seekUpListener );
    });

    // Touch events for mobile
    progressContainer.addEventListener( 'touchstart', (e) => {
        isSeeking = true;
        updateSeekBar(e);
    });
    document.addEventListener( 'touchmove', (e) => {
        if (isSeeking) {
            updateSeekBar(e);
            e.preventDefault(); // Prevent scrolling while dragging
        }
    }, { passive: false }); // Use passive: false for preventDefault
    document.addEventListener( 'touchend', () => {
        if (isSeeking) {
            isSeeking = false;
            if (isPlaying) audio.play();
        }
    });


    // 6. Volume Control Interaction
    let volumeMoveListener = null;
    let volumeUpListener = null;

    volumeSliderContainer.addEventListener( 'mousedown', (e) => {
        isVolumeDragging = true;
        updateVolumeBar(e);

        volumeMoveListener = (e) => updateVolumeBar(e);
        volumeUpListener = () => {
            isVolumeDragging = false;
            document.removeEventListener('mousemove', volumeMoveListener);
            document.removeEventListener('mouseup', volumeUpListener);
        };
        
        document.addEventListener( 'mousemove', volumeMoveListener );
        document.addEventListener( 'mouseup', volumeUpListener );
    });

    // Touch events for volume
    volumeSliderContainer.addEventListener( 'touchstart', (e) => {
        isVolumeDragging = true;
        updateVolumeBar(e);
    });
    document.addEventListener( 'touchmove', (e) => {
        if (isVolumeDragging) {
            updateVolumeBar(e);
            e.preventDefault();
        }
    }, { passive: false });
    document.addEventListener( 'touchend', () => {
        if (isVolumeDragging) {
            isVolumeDragging = false;
        }
    });

    // 7. Volume Mute/Unmute Toggle
    volumeToggleIcon.addEventListener('click', () => {
        if (audio.volume > 0) {
            lastVolume = audio.volume; // Store current volume
            audio.volume = 0;
        } else {
            audio.volume = lastVolume > 0 ? lastVolume : 0.5; // Restore or default to 0.5
        }
        // updateVolumeBar will handle icon change based on audio.volume
        updateVolumeBar({ clientX: volumeSliderContainer.getBoundingClientRect().width * audio.volume + volumeSliderContainer.getBoundingClientRect().left });
    });

    // 8. Playlist Item Clicks
    playlistItemsContainer.addEventListener('click', (e) => {
        const item = e.target.closest('.smartwoo-playlist__item');
        if (item) {
            const index = parseInt(item.dataset.index);
            if (index !== currentTrackIndex) {
                loadTrack(index);
                isPlaying = true; // Assume user wants to play when clicking new track
                togglePlayPause(); // Will call play() and update button
            }
        }
    });

    // --- Initial Setup ---
    // Load the first track when the player initializes
    if ( playlist.length > 0 ) {
        loadTrack( 0 );
    } else {
        // Handle empty playlist scenario, e.g., disable controls
        console.warn('Audio playlist is empty.');
        // Optionally disable controls here
        if (playBtn) playBtn.disabled = true;
        if (pauseBtn) pauseBtn.disabled = true;
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        volumeSliderContainer.style.pointerEvents = 'none';
        progressContainer.style.pointerEvents = 'none';
        volumeToggleIcon.style.pointerEvents = 'none';
    }
}

// --- Usage Example (How to call this function) ---
// You would typically call this when the DOM is ready,
// and after your HTML has been inserted (e.g., by TinyMCE).

/*
// Example of how you might call this for all players on the page:
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.smartwoo-audio-playlist').forEach(player => {
        smartWooinitAssetAudioPlayer(player);
    });
});
*/

/*
// If used within a TinyMCE setup, you'd likely call this after the content is inserted,
// perhaps within a TinyMCE custom plugin init or a setup function.
// For example, if you insert the HTML into a TinyMCE editor, you might run this
// after the editor's content has been updated, or on an editor content load event.
// This is a simplified example, actual TinyMCE integration might vary.

function initTinyMCEPlayer(editor) {
    editor.on('SetContent', function() {
        // Find players within the editor content
        const players = editor.contentDocument.querySelectorAll('.smartwoo-audio-playlist');
        players.forEach(player => {
            // Need to pass the actual HTMLElement from the iframe
            smartWooinitAssetAudioPlayer(player);
        });
    });
    // For already existing content when the editor loads
    editor.on('LoadContent', function() {
        const players = editor.contentDocument.querySelectorAll('.smartwoo-audio-playlist');
        players.forEach(player => {
            smartWooinitAssetAudioPlayer(player);
        });
    });
}

// Then in your TinyMCE config:
// tinymce.init({
//    // ... other config
//    setup: function(editor) {
//        initTinyMCEPlayer(editor);
//    }
// });
*/

addEventListener( 'DOMContentLoaded', () => {
    let assetAudioPlayers = document.querySelectorAll( '.smartwoo-audio-playlist' );
    
    assetAudioPlayers.forEach( ( player ) => {
        smartWooinitAssetAudioPlayer( player );
    });
    
});
