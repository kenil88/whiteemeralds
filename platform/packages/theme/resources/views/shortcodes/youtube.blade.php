{{-- @if (!empty($url))
    <div
        @if(! $width && ! $height)
            style="position: relative; display: block; height: 0; padding-bottom: 56.25%; overflow: hidden;"
        @else
            style="margin-bottom: 20px;"
        @endif
    >
        <iframe
            src="{{ $url }}"
            @if(! $width && ! $height)
                style="position: absolute; top: 0; bottom: 0; left: 0; width: 100%; height: 100%; border: 0;"
            @endif
            allowfullscreen
            frameborder="0"
            @if ($height)
                height="360"
            @endif

            @if ($width)
                width="640"
            @endif
            title="Video"
        ></iframe>
    </div>
@endif --}}

<div class="container">
    <div class="row">
        {{-- Video 1 --}}
        <div class="col-md-6 col-lg-4 mb-4"> <!-- Adjust column size for different screen sizes -->
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                    src="{{ $url }}" 
                    class="embed-responsive-item"
                    allowfullscreen
                    frameborder="0"
                    width="400"
                    height="360"
                    title="Video 1"
                ></iframe>
            </div>
        </div>
        
        {{-- Video 2 --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                    src="https://www.youtube.com/embed/U-iKl8WQKas" 
                    class="embed-responsive-item"
                    allowfullscreen
                    frameborder="0"
                    width="400"
                    height="360"
                    title="Video 2"
                ></iframe>
            </div>
        </div>
        
        {{-- Video 3 --}}
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                    src="https://www.youtube.com/embed/zuNVVDGakqk" 
                    class="embed-responsive-item"
                    allowfullscreen
                    frameborder="0"
                    width="400"
                    height="360"
                    title="Video 3"
                ></iframe>
            </div>
        </div>
    </div>
</div>
