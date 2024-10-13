<div class="container">
    <div class="row">
        {{-- Video 1 --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="embed-responsive embed-responsive-16by9">
                <div class="youtube-video" data-video-id="2fofGX6rngg" style="position: relative;" onclick="loadVideo('2fofGX6rngg', this)">
                    <img src="https://img.youtube.com/vi/2fofGX6rngg/hqdefault.jpg" alt="Video 1" class="img-fluid" />
                    <button class="play-button">
                        <img src="{{ asset('storage/youtube.png') }}" alt="Play" class="play-icon">
                    </button>
                </div>
            </div>
        </div>

        {{-- Video 2 --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="embed-responsive embed-responsive-16by9">
                <div class="youtube-video" data-video-id="U-iKl8WQKas" style="position: relative;" onclick="loadVideo('U-iKl8WQKas', this)">
                    <img src="https://img.youtube.com/vi/U-iKl8WQKas/hqdefault.jpg" alt="Video 2" class="img-fluid" />
                    <button class="play-button">
                        <img src="{{ asset('storage/youtube.png') }}" alt="Play" class="play-icon">
                    </button>
                </div>
            </div>
        </div>

        {{-- Video 3 --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="embed-responsive embed-responsive-16by9">
                <div class="youtube-video" data-video-id="zuNVVDGakqk" style="position: relative;" onclick="loadVideo('zuNVVDGakqk', this)">
                    <img src="https://img.youtube.com/vi/zuNVVDGakqk/hqdefault.jpg" alt="Video 3" class="img-fluid" />
                    <button class="play-button">
                        <img src="{{ asset('storage/youtube.png') }}" alt="Play" class="play-icon">
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS -->
<style>
.youtube-video {
    position: relative;
    cursor: pointer; /* Makes the whole container clickable */
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: none;
    border: none;
    cursor: pointer;
    outline: none;
    padding: 0;
}

.play-icon {
    width: 60px; /* Adjust size as needed */
    height: 60px; /* Adjust size as needed */
}

.embed-responsive {
    position: relative;
    padding-bottom: 0;
    height: auto;
}

.embed-responsive iframe {
    height: 300px !important; 
    width: 400px !important;   
}

</style>
