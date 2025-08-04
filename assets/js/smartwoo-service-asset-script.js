class SmartwooAudioPlayer {

    /**
     * @param {HTMLElement} assetAudioPlayer - The main container for the audio player.
     */
    constructor( assetAudioPlayer ) {
        this.assetAudioPlayer   = assetAudioPlayer;
        this.audio              = document.createElement( 'audio' );
        this.playlist           = this._parsePlaylist();        
        this.currentTrackIndex  = 0;
        this.isPlaying          = false;
        this.isSeeking          = false;
        this.isVolumeDragging   = false;
        this.lastVolume         = localStorage.getItem( 'smartwoo-audio-volume' ) ? parseFloat( localStorage.getItem( 'smartwoo-audio-volume' ) ) : 0.5;
        this.audio.volume       = this.lastVolume;
        
        this._cacheElements();
        this._bindEvents();
        this._initPlayer();
    }

    /**
     * Parse and decode the playlist
     */
    _parsePlaylist() {
        const data  = this.assetAudioPlayer.dataset.playlist;
        return data ? JSON.parse( data.replace( /&quot;/g, '"') ) : []
    }

    /**
     * Cache required html elements
     */
    _cacheElements() {
        // Player controls.
        const el        = this.assetAudioPlayer;
        this.playBtn    = el.querySelector( '.smartwoo-play' );
        this.pauseBtn   = el.querySelector( '.smartwoo-pause' );
        this.prevBtn    = el.querySelector( '.smartwoo-prev' );
        this.nextBtn    = el.querySelector( '.smartwoo-next' );

        // Seek bar elements.
        this.progressContainer  = el.querySelector( '.smartwoo-audio-player__progress' );
        this.progressBar        = el.querySelector( '.smartwoo-progress-bar' );
        this.currentTimeSpan    = el.querySelector( '.smartwoo-time-current' );
        this.durationSpan       = el.querySelector( '.smartwoo-time-duration' );

        // Volume controls.
        this.volumeToggleIcon       = el.querySelector( '.smartwoo-volume-toggle' );
        this.volumeSliderContainer  = el.querySelector( '.smartwoo-volume-slider' );
        this.volumeProgressBar      = el.querySelector( '.smartwoo-volume-progress' );

        // Now Playing info.
        this.currentTitleSpan   = el.querySelector( '.smartwoo-current-title' );
        this.currentArtistSpan  = el.querySelector( '.smartwoo-current-artist' );
        this.thumbnailImg       = el.querySelector( '.smartwoo-thumbnail' );

        // Playlist elements.
        this.playlistItemsContainer = el.querySelector( 'ul.smartwoo-playlist' );
        this.playlistToggle         = el.querySelector( '.smartwoo-audio-player__control-group.smartwoo-playlist-control .dashicons-playlist-audio' );
    }

    /**
     * Formats time from seconds to MM:SS string.
     * @param {number} seconds - The time in seconds.
     * @returns {string} Formatted time string.
     */
    _formatTime( seconds ) {
        if ( isNaN( seconds ) || seconds < 0 ) return '0:00';
        const minutes = Math.floor( seconds / 60 );
        const remainingSeconds = Math.floor( seconds % 60 );
        return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
    }

    /**
     * Escape HTML for safe output.
     * @param {string} unsafe - Unsafe input string.
     * @returns {string} Safe HTML string.
     */
    _escHtml( unsafe ) {
        const div       = document.createElement( 'div' );
        div.textContent = unsafe;
        return div.innerHTML;
    }

    /**
     * Loads a track into the audio element and updates player info.
     * @param {number} index - The index of the track in the playlist.
     */
    loadTrack( index ) {
        if ( index < 0 || index >= this.playlist.length ) {
            console.warn( 'Track index out of bounds:', index );
            return;
        }

        this.currentTrackIndex  = index;
        const track             = this.playlist[this.currentTrackIndex];

        this.audio.src                      = track.url;
        this.currentTitleSpan.textContent   = this._escHtml( track.title );
        this.currentArtistSpan.textContent  = this._escHtml( track.artist );
        this.thumbnailImg.src               = this._escHtml( track.thumbnail );
        this.thumbnailImg.alt               = this._escHtml( track.title || 'Audio thumbnail' );

        // Reset progress bar
        this.progressBar.style.width        = '0%';
        this.currentTimeSpan.textContent    = '0:00';
        this.durationSpan.textContent       = '0:00';

        // Update active class in playlist
        this.playlistItemsContainer.querySelectorAll( '.smartwoo-playlist__item' ).forEach( item => {
            item.classList.remove( 'is-active' );
        });

        const activeItem = this.playlistItemsContainer.querySelector( `[data-index="${this.currentTrackIndex}"]` );

        if ( activeItem ) activeItem.classList.add( 'is-active' );
            
        this.audio.load();

        if ( this.isPlaying ) this.audio.play().catch( e => console.error( "Autoplay prevented:", e ) );
    }

    /**
     * Toggles play/pause state.
     */
    togglePlayPause = () => {
        if (this.isPlaying) {
            this.audio.pause();
            this.isPlaying = false;
            this.playBtn.style.removeProperty('display');
            this.pauseBtn.style.setProperty('display', 'none');
        } else {
            this.audio.play().catch( e => console.error("Autoplay prevented:", e ) );
            this.isPlaying = true;
            this.playBtn.style.setProperty( 'display', 'none' );
            this.pauseBtn.style.removeProperty( 'display' );
        }
    }

    /**
     * Toggle audio mute
     */
    mute = () => {
        if (this.audio.volume > 0) {
            this.lastVolume = this.audio.volume;
            this.audio.volume = 0;
        } else {
            this.audio.volume = this.lastVolume > 0 ? this.lastVolume : 0.5;
        }
        this._updateVolumeBar({ clientX: this.volumeSliderContainer.getBoundingClientRect().width * this.audio.volume + this.volumeSliderContainer.getBoundingClientRect().left });
    }

    /**
     * Play next track
     */
    next = () => {
        const newIndex = this.currentTrackIndex < this.playlist.length - 1 ? this.currentTrackIndex + 1 : 0;
        this.loadTrack(newIndex);
    }

    /**
     * Play the prevous track
     */
    prev = () => {
        const newIndex = this.currentTrackIndex > 0 ? this.currentTrackIndex - 1 : this.playlist.length - 1;
        this.loadTrack(newIndex);
    }
    
    /**
     * Toggle the playlist visibility
     */
    togglePlaylist = () => {        
        this.assetAudioPlayer.classList.toggle( 'playlist-active' );
    }

    /**
     * Updates the seek bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     */
    _updateSeekBar = ( e ) => {
        const rect      = this.progressContainer.getBoundingClientRect();
        const clientX   = e.clientX || ( e.touches && e.touches[0] ? e.touches[0].clientX : 0 );
        let x = clientX - rect.left;
        x = Math.max( 0, Math.min( x, rect.width ) );
        const percent = x / rect.width;

        this.progressBar.style.width = `${percent * 100}%`;
        this.currentTimeSpan.textContent = this._formatTime( percent * this.audio.duration );

        if ( this.isSeeking ) this.audio.currentTime = percent * this.audio.duration;
    }

    /**
     * Updates the volume bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     */
    _updateVolumeBar = ( e ) => {
        const rect      = this.volumeSliderContainer.getBoundingClientRect();
        const clientX   = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        let x           = clientX - rect.left;
        x               = Math.max(0, Math.min( x, rect.width ) );
        const percent   = x / rect.width;
        
        this.audio.volume                   = percent;
        this.volumeProgressBar.style.width  = `${percent * 100}%`;
        
        // Update volume icon
        if (this.audio.volume === 0) {
            this.volumeToggleIcon.className = 'dashicons dashicons-controls-volumeoff smartwoo-volume-toggle';
            this.volumeToggleIcon.title     = 'Unmute';
        } else {
            this.volumeToggleIcon.className = 'dashicons dashicons-controls-volumeon smartwoo-volume-toggle';
            this.volumeToggleIcon.title     = 'Mute';
        }

        localStorage.setItem( 'smartwoo-audio-volume', this.audio.volume );
    }

    /**
     * Playlist click callback
     */
    _onClickPlaylistItem = ( e ) => {
        const item = e.target.closest( '.smartwoo-playlist__item' );
        if (item) {
            const index = parseInt( item.dataset.index );
            if ( index !== this.currentTrackIndex ) {
                this.loadTrack( index );
                if ( ! this.isPlaying ) {
                    this.togglePlayPause();
                }
            }
        }
    }

    /**
     * On loadedmetadata callback to set the audio controls initial state.
     */
    _onLoadedMetadataCallback = () => {
        this.durationSpan.textContent = this._formatTime( this.audio.duration );
        if ( ! this.isPlaying && ! this.audio.autoplay ) {
            this.audio.pause();
        }
        this._updateVolumeBar( { clientX: this.volumeSliderContainer.getBoundingClientRect().width * this.audio.volume + this.volumeSliderContainer.getBoundingClientRect().left } );
    }

    /**
     * Sync the audio progress bar and counters.
     */
    _syncProgress = () =>{
        if (!this.isSeeking) {
            const progress = (this.audio.currentTime / this.audio.duration) * 100;
            this.progressBar.style.width = `${progress}%`;
            this.currentTimeSpan.textContent = this._formatTime(this.audio.currentTime);
        }
    }

    /**
     * Auto loop audio when it ends
     */
    _onEndedCallback = () => {
        if (this.currentTrackIndex < this.playlist.length - 1) {
            this.loadTrack(this.currentTrackIndex + 1);
            this.audio.play();
        } else {
            this.loadTrack(0);
        }
    }

    /**
     * Catch errors
     */
    _catch = ( e ) => {
        console.error( 'Audio error:', e );
        if ( this.isPlaying ) {
            this.isPlaying = false;
            this.playBtn.style.removeProperty( 'display' );
            this.pauseBtn.style.setProperty( 'display', 'none' );
            this.audio.pause();
        }
    }
    
    /**
     * Initializes all event listeners for the player.
     * @private
     */
    _bindEvents() {
        this.playBtn.addEventListener( 'click', this.togglePlayPause );
        this.pauseBtn.addEventListener( 'click', this.togglePlayPause );
        this.playlistToggle.addEventListener( 'click', this.togglePlaylist );
        this.playlistItemsContainer.addEventListener( 'click', this._onClickPlaylistItem );
        this.audio.addEventListener( 'loadedmetadata', this._onLoadedMetadataCallback );
        this.audio.addEventListener( 'timeupdate', this._syncProgress );
        this.audio.addEventListener( 'ended', this._onEndedCallback );
        this.audio.addEventListener( 'error', this._catch );

        // --- Player Controls ---
        this.prevBtn.addEventListener( 'click', this.prev );
        this.nextBtn.addEventListener( 'click', this.next );
        this.volumeToggleIcon.addEventListener('click', this.mute );

        // --- Seek Bar Interaction ---
        this.progressContainer.addEventListener( 'mousedown', (e) => {
            this.isSeeking = true;
            this._updateSeekBar(e);
            this.assetAudioPlayer.addEventListener( 'mousemove', this._updateSeekBar );
            this.assetAudioPlayer.addEventListener( 'mouseup', () => {
                this.isSeeking = false;
                this.assetAudioPlayer.removeEventListener( 'mousemove', this._updateSeekBar );
                if ( this.isPlaying ) this.audio.play();
            }, { once: true });
        });

        this.progressContainer.addEventListener( 'touchstart', ( e ) => {
            this.isSeeking = true;
            this._updateSeekBar( e );
        });
        this.assetAudioPlayer.addEventListener( 'touchmove', ( e ) => {
            if ( this.isSeeking ) {
                this._updateSeekBar( e );
                e.preventDefault();
            }
        }, { passive: false });
        this.assetAudioPlayer.addEventListener( 'touchend', () => {
            if ( this.isSeeking ) {
                this.isSeeking = false;
                if ( this.isPlaying ) this.audio.play();
            }
        });

        // --- Volume Control Interaction ---
        this.volumeSliderContainer.addEventListener( 'mousedown', ( e ) => {
            this.isVolumeDragging = true;
            this._updateVolumeBar( e );
            this.assetAudioPlayer.addEventListener( 'mousemove', this._updateVolumeBar );
            this.assetAudioPlayer.addEventListener( 'mouseup', () => {
                this.isVolumeDragging = false;
                this.assetAudioPlayer.removeEventListener( 'mousemove', this._updateVolumeBar );
            }, { once: true });
        });

        this.volumeSliderContainer.addEventListener( 'touchstart', ( e ) => {
            this.isVolumeDragging = true;
            this._updateVolumeBar( e );
        });
        this.assetAudioPlayer.addEventListener( 'touchmove', ( e ) => {
            if ( this.isVolumeDragging ) {
                this._updateVolumeBar(e);
                e.preventDefault();
            }
        }, { passive: false });
        this.assetAudioPlayer.addEventListener('touchend', () => {
            if (this.isVolumeDragging) {
                this.isVolumeDragging = false;
            }
        });
    }

    /**
     * Initialize the player
     */
    _initPlayer() {
        if (this.playlist.length > 0) {
            this.loadTrack(0);
        } else {
            console.warn('Audio playlist is empty.');
        }
    }
}


/**
 * Smart Woo Asset Video player class handles the video player feature in the subscription assets
 */
class SmartwooVideoPlayer {

    /**
     * @param {HTMLElement} assetVideoPlayer - The main container for the video player.
     */
    constructor( assetVideoPlayer ) {
        this.assetVideoPlayer   = assetVideoPlayer;
        /**
         * @type {HTMLVideoElement|undefined} - Video element
         */
        this.video              = this.assetVideoPlayer.querySelector('video.smartwoo-video-player__video');
        this.playlist           = this._parsePlaylist();
        this.currentTrackIndex  = 0;
        this.isPlaying          = false;
        this.isSeeking          = false;
        this.isVolumeDragging   = false;
        this.lastVolume         = localStorage.getItem('smartwoo-audio-volume') ? parseFloat(localStorage.getItem('smartwoo-audio-volume')) : 0.5;
        this.isFullScreenMode   = false;
        this.isPortriate        = false;
        this.controlsTimout     = null;
        this.video.volume       = this.lastVolume;

        this._cacheElements();
        this._bindEvents();
        this._initPlayer();
    }

    /**
     * Parse and decode the playlist
     */
    _parsePlaylist() {
        const data      = this.assetVideoPlayer.dataset.playlist;
        return data ? JSON.parse( data.replace(/&quot;/g, '"' ) ) : [];
    }

    /**
     * Cache required html element
     */
    _cacheElements() {
        const el = this.assetVideoPlayer;
        this.videoFrame     = el.querySelector('.smartwoo-video-player__frame');

        // Seek bar elements.
        this.progressContainer  = el.querySelector('.smartwoo-video-player__progress');
        this.progressBar        = el.querySelector('.smartwoo-progress-bar ');
        this.currentTimeSpan    = el.querySelector('.smartwoo-video-player_timing-current');
        this.durationSpan       = el.querySelector('.smartwoo-video-player_timing-duration');
        this.tooltip            = el.querySelector('.smartwoo-seek-tooltip');

        // Volume controls.
        this.volumeToggleIcon       = el.querySelector('.smartwoo-video-volume-toggle');
        this.volumeSliderContainer  = el.querySelector('.smartwoo-video-volume-slider');
        this.volumeProgressBar      = el.querySelector('.smartwoo-video-volume-progress');
        this.volumeScruber          = el.querySelector('.smartwoo-video-volume-scrubber');

        // Now Playing info.
        this.currentTitleSpan   = el.querySelector('.smartwoo-current-title');
        this.currentArtistSpan  = el.querySelector('.smartwoo-current-artist');

        this.playlistItemsContainer = el.querySelector('ul.smartwoo-video-player-playlist-container');
        this.fullscreenToggle       = el.querySelector('.smartwoo-video-fullscreen-toggle');

        // Player controls.
        this.playBtn        = el.querySelector('.smartwoo-play');
        this.pauseBtn       = el.querySelector('.smartwoo-pause');
        this.prevBtn        = el.querySelector('.smartwoo-prev');
        this.nextBtn        = el.querySelector('.smartwoo-next');

    }

    /**
     * Formats time from seconds to MM:SS string.
     * @param {number} seconds - The time in seconds.
     * @returns {string} Formatted time string.
     */
    _formatTime( seconds ) {
        if ( isNaN( seconds ) || seconds < 0 ) return '0:00';
        const minutes = Math.floor( seconds / 60 );
        const remainingSeconds = Math.floor( seconds % 60 );
        return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
    }

    /**
     * Escape HTML for safe output.
     * @param {string} unsafe - Unsafe input string.
     * @returns {string} Safe HTML string.
     */
    _escHtml( unsafe ) {
        let div         = document.createElement( 'div' );
        div.textContent = unsafe;
        return div.innerHTML;
    }

    /**
     * Loads a track into the audio element and updates player info.
     * @param {number} index - The index of the track in the playlist.
     */
    loadTrack( index ) {
        if ( index < 0 || index >= this.playlist.length ) {
            console.warn( 'Track index out of bounds:', index );
            return;
        }

        this.currentTrackIndex = index;
        const track = this.playlist[this.currentTrackIndex];

        this.video.src = track.url;
        
        smartwooGetVideoThumbnail( track.url ).then( url => { this.video.poster = url; } );
        
        if ( this.currentTitleSpan ) {
            this.currentTitleSpan.textContent = this._escHtml( track.title );
        }

        if ( this.currentArtistSpan ) {
            this.currentArtistSpan.textContent = this._escHtml( track.artist );
        }

        // Reset progress bar
        if ( this.progressBar ) this.progressBar.style.width = '0%';
        if ( this.currentTimeSpan ) this.currentTimeSpan.textContent = '0:00';
        if ( this.durationSpan ) this.durationSpan.textContent = '0:00';

        // Update active class in playlist (assuming playlist exists)
        if ( this.playlistItemsContainer ) {
            this.playlistItemsContainer.querySelectorAll( '.smartwoo-video-playlist-item' ).forEach( item => {
                item.classList.remove( 'is-active' );
            });
            const activeItem = this.playlistItemsContainer.querySelector( `[data-index="${this.currentTrackIndex}"]` );
            if ( activeItem ) {
                activeItem.classList.add( 'is-active' );
            }
        }
        
        this.video.load();

        if ( this.isPlaying ) {
            this.video.play().catch( e => console.error( "Autoplay prevented:", e ) );
        }
    }

    /**
     * Toggles play/pause state.
     */
    togglePlayPause = () => {
        if ( this.isPlaying ) {
            this.video.pause();
            this.isPlaying = false;
            if ( this.playBtn ) this.playBtn.style.removeProperty( 'display' );
            if (this.pauseBtn) this.pauseBtn.style.setProperty( 'display', 'none' );
            if (this.videoFrame) this.videoFrame.classList.add( 'is-paused' );
        } else {
            this.video.play().catch( e => console.error( "Autoplay prevented:", e ) );
            this.isPlaying = true;
            if ( this.playBtn ) this.playBtn.style.setProperty( 'display', 'none' );
            if ( this.pauseBtn ) this.pauseBtn.style.removeProperty( 'display' );
            if ( this.videoFrame ) this.videoFrame.classList.remove( 'is-paused' );
        }
    }

    /**
     * Play previous video
     */
    prev = () => {
        const newIndex = this.currentTrackIndex > 0 ? this.currentTrackIndex - 1 : this.playlist.length - 1;
        this.loadTrack( newIndex );
    }

    /**
     * Play next video
     */
    next = () => {
        const newIndex = this.currentTrackIndex < this.playlist.length - 1 ? this.currentTrackIndex + 1 : 0;
        this.loadTrack( newIndex );
    }

    /**
     * Toggle video mute state
     */
    mute = () => {
        if ( this.video.volume > 0 ) {
            this.lastVolume = this.video.volume;
            this.video.volume = 0;
        } else {
            this.video.volume = this.lastVolume > 0 ? this.lastVolume : 0.5;
        }
        if ( this.volumeSliderContainer ) {
            this._updateVolumeBar( { clientX: this.volumeSliderContainer.getBoundingClientRect().width * this.video.volume + this.volumeSliderContainer.getBoundingClientRect().left } );
        }
    }

    /**
     * Updates the seek bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     * @private
     */
    _updateSeekBar = ( e ) => {
        if ( ! this.progressContainer || ! this.progressBar || ! this.currentTimeSpan ) return;

        const rect = this.progressContainer.getBoundingClientRect();
        const clientX = e.clientX || ( e.touches && e.touches[0] ? e.touches[0].clientX : 0 );
        let x = clientX - rect.left;
        x = Math.max( 0, Math.min( x, rect.width ) );
        const percent = x / rect.width;
        const time = percent * this.video.duration;

        this.progressBar.style.width = `${percent * 100}%`;
        this.currentTimeSpan.textContent = this._formatTime( time );

        if ( this.isSeeking ) {
            this.video.currentTime = time;
        }
    }
    
    /**
     * Show video timestamp tootip.
     * @param {MouseEvent|TouchEvent} e - The Mouse over event object.
     * @private
     */
    _showTimestampTooltip = ( e ) => {
        if ( !this.progressContainer || !this.tooltip ) return;

        const rect = this.progressContainer.getBoundingClientRect();
        const clientX = e.clientX || ( e.touches && e.touches[0] ? e.touches[0].clientX : 0 );
        let x = clientX - rect.left;
        x = Math.max( 0, Math.min( x, rect.width ) );

        const percent = x / rect.width;
        const time = percent * this.video.duration;

        this.tooltip.textContent = this._formatTime( time );
        this.tooltip.style.left = `${x}px`; // Simplified positioning
        this.tooltip.style.display = 'block';
    }

    /**
     * Hide the video timestamp tooltip.
     * @private
     */
    _hideTimestampTooltip = () => {
        if ( this.tooltip ) this.tooltip.style.display = 'none';
    }

    /**
     * Updates the volume bar based on mouse/touch position.
     * @param {MouseEvent|TouchEvent} e - The event object.
     * @private
     */
    _updateVolumeBar = ( e ) => {
        if ( ! this.volumeSliderContainer || ! this.volumeProgressBar ) return;

        const rect = this.volumeSliderContainer.getBoundingClientRect();
        const clientX = e.clientX || ( e.touches && e.touches[0] ? e.touches[0].clientX : 0 );
        let x = clientX - rect.left;
        x = Math.max(0, Math.min( x, rect.width ) );
        const percent = x / rect.width;

        this.video.volume = percent;
        this.volumeProgressBar.style.width = `${percent * 100}%`;
        if ( this.volumeScruber ) this.volumeScruber.title = `${Math.round( percent * 100 ) }%`;

        // Update volume icon
        if ( this.volumeToggleIcon ) {
            if ( this.video.volume === 0 ) {
                this.volumeToggleIcon.classList.remove( 'dashicons-controls-volumeon' );
                this.volumeToggleIcon.classList.add( 'dashicons-controls-volumeoff' );
                this.volumeToggleIcon.title = 'Unmute';
            } else {
                this.volumeToggleIcon.classList.remove( 'dashicons-controls-volumeoff' );
                this.volumeToggleIcon.classList.add( 'dashicons-controls-volumeon' );
                this.volumeToggleIcon.title = 'Mute';
            }
        }
        localStorage.setItem('smartwoo-audio-volume', this.video.volume);
    }

    /**
     * Toggle full screen mode.
     */
    toggleFullScreen = ( e ) => {
        if ( ! this.isFullScreenMode ) {
            this.videoFrame.requestFullscreen().catch( e => console.error( "Fullscreen error:", e ) );
            this.fullscreenToggle.classList.remove( 'dashicons', 'dashicons-fullscreen-alt' );
            this.fullscreenToggle.classList.add( 'dashicons', 'dashicons-fullscreen-exit-alt' );
            this.fullscreenToggle.title = 'Exit fullscreen';
        } else {
            document.exitFullscreen();
            this.fullscreenToggle.classList.remove( 'dashicons','dashicons-fullscreen-exit-alt' );
            this.fullscreenToggle.classList.add( 'dashicons','dashicons-fullscreen-alt' );
            this.fullscreenToggle.title = 'Fullscreen mode';
        }
    }

    /**
     * Reset the controls visibility timeout.
     */
    resetControlVisibility = () => {
        clearTimeout( this.controlsTimout );
        if ( this.videoFrame ) this.videoFrame.classList.add( 'is-hovered' );
        if ( this.videoFrame && ! this.video.paused ) this.videoFrame.style.removeProperty( 'cursor' );

        this.controlsTimout = setTimeout( this.hideControls, 3000 );
    }

    /**
     * Hide video controls.
     */
    hideControls = () => {
        if ( this.videoFrame ) this.videoFrame.classList.remove( 'is-hovered' );
        if ( this.videoFrame && ! this.video.paused ) this.videoFrame.style.setProperty( 'cursor', 'none' );
    }

    /**
     * Show a temporary floating message on the video (e.g. "+10s", "Paused").
     *
     * @param {string} message - The message to display in the toast.
     */
    _showToast( message ) {
        // If toast already exists, reuse it
        if ( ! this._toastEl ) {
            this._toastEl = document.createElement( 'div' );
            this._toastEl.className = 'smartwoo-toast';
            this.assetVideoPlayer.prepend( this._toastEl );
        }

        this._toastEl.textContent = message;
        this._toastEl.classList.add( 'visible' );

        // Remove after timeout
        clearTimeout( this._toastTimeout );
        this._toastTimeout = setTimeout( () => {
            this._toastEl.classList.remove( 'visible' );
        }, 1000 );
    }

    /**
     * On loadedmetadata callback to reset the video player controls
     */
    _onLoadedMetadataCallback = () => {
        if ( this.durationSpan ) this.durationSpan.textContent = this._formatTime( this.video.duration );
        this.isPortriate = this.video.videoHeight > this.video.videoWidth;
        if ( this.videoFrame ) this.videoFrame.classList.toggle( 'is-portriate', this.isPortriate );

        if ( ! this.isPlaying && ! this.video.autoplay ) {
            this.video.pause();
            if ( this.videoFrame ) this.videoFrame.classList.add( 'is-paused' );
        }
        if ( this.volumeSliderContainer ) {
            this._updateVolumeBar({ clientX: this.volumeSliderContainer.getBoundingClientRect().width * this.video.volume + this.volumeSliderContainer.getBoundingClientRect().left } );
        }
    }

    /**
     * Sync video progress bar and counters
     */
    _syncProgress = () => {
        if ( !this.isSeeking && this.progressBar ) {
            const progress = ( this.video.currentTime / this.video.duration ) * 100;
            this.progressBar.style.width = `${progress}%`;
            if ( this.currentTimeSpan ) this.currentTimeSpan.textContent = this._formatTime(this.video.currentTime );
        }
    }

    /**
     * Handle click on a playlist
     */
    _onClickPlaylistItem = ( e ) => {
        const item = e.target.closest( '.smartwoo-video-playlist-item' );
        if ( item ) {
            const index = parseInt( item.dataset.index );
            if ( index !== this.currentTrackIndex ) {
                this.loadTrack( index );
                if ( ! this.isPlaying ) {
                    this.togglePlayPause();
                }
            }
            }
    }

    /**
     * Auto loop the video when it ends
     */
    _onEndedCallback = () => {
        if ( this.currentTrackIndex < this.playlist.length - 1 ) {
            this.loadTrack( this.currentTrackIndex + 1 );
        } else {
            this.loadTrack( 0 );
        }
    }

    /**
     * Keyboard shortcut
     * 
     * @param {Event} e 
     */
    _keyboardShortcuts  = ( e ) => {
        const key = e.key.toLowerCase();

        switch ( key ) {
            case ' ':
            case 'k':
                e.preventDefault();
                this.togglePlayPause();
                break;

            case 'arrowright':
                this.video.currentTime += 5;
                break;
            case 'l':
                this.video.currentTime += 10;
                break;

            case 'arrowleft':
                this.video.currentTime -= 5;
                break;
            case 'j':
                this.video.currentTime -= 10;
                break;

            case 'arrowup':
                this.video.volume = Math.min( 1, this.video.volume + 0.1 );
                break;

            case 'arrowdown':
                this.video.volume = Math.max( 0, this.video.volume - 0.1 );
                break;

            case 'm':
                this.volumeToggleIcon?.click();
                break;

            case 'f':
                this.fullscreenToggle?.click();
                break;

            case 'c':
                this._toggleCaptions?.();
                break;

            case '0':
                this.video.currentTime = 0;
                break;

            case '1': case '2': case '3': case '4':
            case '5': case '6': case '7': case '8': case '9':
                const percent = parseInt( key ) * 0.1;
                this.video.currentTime = this.video.duration * percent;
                break;

            case '.':
                if ( this.video.paused ) {
                    this.video.currentTime += 1 / 30; // frame step forward
                }
                break;

            case ',':
                if ( this.video.paused ) {
                    this.video.currentTime -= 1 / 30; // frame step backward
                }
                break;

            case '>':
                this.video.playbackRate = Math.min( 4, this.video.playbackRate + 0.25 );
                break;

            case '<':
                this.video.playbackRate = Math.max( 0.25, this.video.playbackRate - 0.25 );
                break;

            case 'home':
                this.video.currentTime = 0;
                break;

            case 'end':
                this.video.currentTime = this.video.duration;
                break;
        }
    }

    /**
     * Video error handler
     */
    _catch = ( e ) => {
        console.error( 'Video error:', e );
        if ( this.isPlaying ) {
            this.isPlaying = false;
            if ( this.playBtn ) this.playBtn.style.removeProperty( 'display' );
            if ( this.pauseBtn ) this.pauseBtn.style.setProperty( 'display', 'none' );
            this.video.pause();
            if ( this.videoFrame ) this.videoFrame.classList.add( 'is-paused' );
        }
    }
    
    /**
     * Initializes all event listeners for the player.
     * @private
     */
    _bindEvents() {
        this.playBtn.addEventListener( 'click', this.togglePlayPause );
        this.pauseBtn.addEventListener( 'click', this.togglePlayPause );

        this.video.addEventListener( 'loadedmetadata', this._onLoadedMetadataCallback );
        this.video.addEventListener( 'timeupdate', this._syncProgress );
        this.video.addEventListener( 'ended', this._onEndedCallback );
        this.video.addEventListener( 'error', this._catch );

        this.videoFrame.addEventListener( 'mousemove', this.resetControlVisibility );
        this.videoFrame.addEventListener( 'mouseleave', this.hideControls );
        
        this.progressContainer.addEventListener( 'mousemove', this._showTimestampTooltip );
        this.progressContainer.addEventListener( 'mouseleave', this._hideTimestampTooltip );

        this.prevBtn.addEventListener( 'click', this.prev );
        this.nextBtn.addEventListener( 'click', this.next );
        this.fullscreenToggle.addEventListener( 'click', this.toggleFullScreen );
        this.volumeToggleIcon.addEventListener( 'click', this.mute );

        this.playlistItemsContainer.addEventListener('click', this._onClickPlaylistItem );
        this.assetVideoPlayer.addEventListener( 'keydown', this._keyboardShortcuts );

        // Seek Bar Interaction
        this.progressContainer.addEventListener( 'mousedown', (e) => {
            this.isSeeking = true;
            this._updateSeekBar(e);
            this.assetVideoPlayer.addEventListener( 'mousemove', this._updateSeekBar );
            this.assetVideoPlayer.addEventListener( 'mouseup', () => {
                this.isSeeking = false;
                this.assetVideoPlayer.removeEventListener( 'mousemove', this._updateSeekBar );
                // if (this.isPlaying) this.video.play();
            }, { once: true });
        });

        // Touch events for mobile
        this.progressContainer.addEventListener( 'touchstart', ( e ) => {
            this.isSeeking = true;
            this._updateSeekBar( e );
        });
        this.assetVideoPlayer.addEventListener( 'touchmove', ( e ) => {
            if (this.isSeeking) {
                this._updateSeekBar( e );
                e.preventDefault();
            }
        }, { passive: false });
        this.assetVideoPlayer.addEventListener( 'touchend', () => {
            this.isSeeking = false;
            if ( this.isPlaying ) this.video.play();
        });
        
        // Volume Control Interaction
        this.volumeSliderContainer.addEventListener( 'mousedown', ( e ) => {
            this.isVolumeDragging = true;
            this._updateVolumeBar( e );
            this.assetVideoPlayer.addEventListener( 'mousemove', this._updateVolumeBar );
            this.assetVideoPlayer.addEventListener('mouseup', () => {
                this.isVolumeDragging = false;
                this.assetVideoPlayer.removeEventListener( 'mousemove', this._updateVolumeBar );
            }, { once: true });
        });

        // Touch events for volume
        this.volumeSliderContainer.addEventListener( 'touchstart', (e) => {
            this.isVolumeDragging = true;
            this._updateVolumeBar( e );
        });
        this.assetVideoPlayer.addEventListener( 'touchmove', (e) => {
            if ( this.isVolumeDragging ) {
                this._updateVolumeBar( e );
                e.preventDefault();
            }
        }, { passive: false });
        this.assetVideoPlayer.addEventListener( 'touchend', () => {
            this.isVolumeDragging = false;
        });

        // Fullscreen state management
        document.addEventListener( 'fullscreenchange', () => {
            this.isFullScreenMode = !!document.fullscreenElement;
            if ( this.videoFrame ) this.videoFrame.classList.toggle( 'is-fullscreen', this.isFullScreenMode );
        });

        this.assetVideoPlayer.addEventListener( 'dblclick', ( e ) => {
            const rect = this.video.getBoundingClientRect();
            const clickX = e.clientX;
            const width = rect.width;
            const relativeX = clickX - rect.left;

            const third = width / 3;

            if ( relativeX < third ) {
                // Left third — rewind
                this.video.currentTime = Math.max( 0, this.video.currentTime - 10 );
                this._showToast( '-10s' );
            } else if ( relativeX > 2 * third ) {
                // Right third — fast forward
                this.video.currentTime = Math.min( this.video.duration, this.video.currentTime + 10 );
                this._showToast( '+10s' );
            } else {
                // Middle third — toggle play/pause
                this.togglePlayPause();
            }
        });

        let lastTap = 0;
        this.assetVideoPlayer.addEventListener( 'touchend', ( e ) => {
            const now = Date.now();
            const timeSince = now - lastTap;

            if ( timeSince < 300 && timeSince > 0 ) {
                // Double tap detected
                const touch = e.changedTouches[0];
                const rect = this.video.getBoundingClientRect();
                const touchX = touch.clientX;
                const width = rect.width;
                const relativeX = touchX - rect.left;

                const third = width / 3;

                if ( relativeX < third ) {
                    this.video.currentTime = Math.max( 0, this.video.currentTime - 10 );
                    this._showToast( '-10s' );
                } else if ( relativeX > 2 * third ) {
                    this.video.currentTime = Math.min( this.video.duration, this.video.currentTime + 10 );
                    this._showToast( '+10s' );
                } else {
                    this.togglePlayPause();
                }
            }

            lastTap = now;
        });
    }

    /**
     * Initialize the video playe
     */
    _initPlayer() {
        if ( this.playlist.length > 0 ) {
            this.loadTrack( 0 );
        } else {
            console.warn('Audio playlist is empty.');
        }

        this.video.removeAttribute('controls');
        this.video.removeAttribute('controlist');
    }
    
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
                    video.currentTime = Math.min( 2, video.duration - 1 );
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
    let assetAudioPlayers   = document.querySelectorAll( '.smartwoo-audio-playlist' );
    let videoPlayers        = document.querySelectorAll( '.smartwoo-video-player-container' );
    let allPlaylistImage    = document.querySelectorAll( '.smartwoo-video-playlist-item_image' );
    
    assetAudioPlayers.forEach( ( player ) => {
        new SmartwooAudioPlayer( player );
    });
    videoPlayers.forEach( async ( player ) =>{
        new SmartwooVideoPlayer( player )
    });

    allPlaylistImage.forEach( image => {        
        image.src = image.src || `data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2264%22%20height%3D%2248%22%20viewBox%3D%220%200%2064%2048%22%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22g%22%20x1%3D%220%25%22%20y1%3D%220%25%22%20x2%3D%22100%25%22%20y2%3D%22100%25%22%3E%3Cstop%20offset%3D%220%25%22%20stop-color%3D%22%2366ccff%22/%3E%3Cstop%20offset%3D%22100%25%22%20stop-color%3D%22%236600ff%22/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect%20x%3D%222%22%20y%3D%222%22%20width%3D%2260%22%20height%3D%2244%22%20rx%3D%229%22%20fill%3D%22url(%23g)%22%20stroke%3D%22%23fff%22%20stroke-width%3D%222%22/%3E%3Cpath%20d%3D%22M24%2016l16%208-16%208z%22%20fill%3D%22%23fff%22/%3E%3C/svg%3E`;
    });

});
