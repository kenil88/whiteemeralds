@php
    EcommerceHelper::registerThemeAssets();
    $version = get_cms_version();
    Theme::asset()->add('lightgallery-css', 'vendor/core/plugins/ecommerce/libraries/lightgallery/css/lightgallery.min.css', version: $version);
    Theme::asset()->add('slick-css', 'vendor/core/plugins/ecommerce/libraries/slick/slick.css', version: $version);
    Theme::asset()->container('footer')->add('lightgallery-js', 'vendor/core/plugins/ecommerce/libraries/lightgallery/js/lightgallery.min.js', ['jquery'], version: $version);
    Theme::asset()->container('footer')->add('slick-js', 'vendor/core/plugins/ecommerce/libraries/slick/slick.min.js', ['jquery'], version: $version);
    Theme::asset()->container('footer')->add('lightgallery-zoom-js', 'vendor/core/plugins/ecommerce/libraries/lightgallery/plugins/zoom/lg-zoom.min.js', ['lightgallery-js'], version: $version);

    Theme::asset()->add('elevatezoom-css', 'https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/3.0.8/css/elevatezoom.css', version: $version);
    Theme::asset()->container('footer')->add('elevatezoom-js', 'https://cdnjs.cloudflare.com/ajax/libs/elevatezoom/3.0.8/jquery.elevatezoom.min.js', ['jquery'], version: $version);


    $galleryStyle = theme_option('ecommerce_product_gallery_image_style', 'vertical');
@endphp

<div class="bb-product-gallery-wrapper">
    <div @class(['bb-product-gallery', 'bb-product-gallery-' . $galleryStyle])>
        <div class="bb-product-gallery-images desktop" id="product-gallery">
            @if (! empty($product->video))
                @foreach($product->video as $video)
                    @continue(! $video['url'])

                    <div class="bb-product-video">
                        @if ($video['provider'] === 'video')
                            <video
                                id="{{ md5($video['url']) }}"
                                playsinline="playsinline"
                                mute="true"
                                preload="auto"
                                class="media-video"
                                aria-label="{{ $product->name }}"
                                poster="{{ $video['thumbnail'] }}" muted>
                                <source src="{{ $video['url'] }}" type="video/{{ File::extension($video['url']) ?: 'mp4' }}">
                                <img src="{{ $video['thumbnail'] }}" alt="{{ $video['url'] }}">
                            </video>
                            <button class="bb-button-trigger-play-video" data-target="{{ md5($video['url']) }}">
                                <x-core::icon name="ti ti-player-play-filled" />
                            </button>
                        @else
                            <iframe
                                data-provider="{{ $video['provider'] }}"
                                src="{{ $video['url'] }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                            </iframe>
                        @endif
                    </div>
                @endforeach
            @endif
            @foreach ($productImages as $image)
                 <img
                    id="zoomed-image-{{ $loop->index }}"
                    src="{{ RvMedia::getImageUrl($image) }}"
                    data-zoom-image="{{ RvMedia::getImageUrl($image) }}"
                    alt="{{ $product->name }}"
                    class="img-zoomable"
                />
            @endforeach
        </div>

         <div class="bb-product-gallery-images mobile" id="product-gallery">
            @if (! empty($product->video))
                @foreach($product->video as $video)
                    @continue(! $video['url'])

                    <div class="bb-product-video">
                        @if ($video['provider'] === 'video')
                            <video
                                id="{{ md5($video['url']) }}"
                                playsinline="playsinline"
                                mute="true"
                                preload="auto"
                                class="media-video"
                                aria-label="{{ $product->name }}"
                                poster="{{ $video['thumbnail'] }}" muted>
                                <source src="{{ $video['url'] }}" type="video/{{ File::extension($video['url']) ?: 'mp4' }}">
                                <img src="{{ $video['thumbnail'] }}" alt="{{ $video['url'] }}">
                            </video>
                            <button class="bb-button-trigger-play-video" data-target="{{ md5($video['url']) }}">
                                <x-core::icon name="ti ti-player-play-filled" />
                            </button>
                        @else
                            <iframe
                                data-provider="{{ $video['provider'] }}"
                                src="{{ $video['url'] }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                            </iframe>
                        @endif
                    </div>
                @endforeach
            @endif
            @foreach ($productImages as $image)
                <a href="{{ RvMedia::getImageUrl($image) }}">
                    {{ RvMedia::image($image, $product->name) }}
                </a>
            @endforeach
        </div>
        <div class="bb-product-gallery-thumbnails" data-vertical="{{ $galleryStyle === 'vertical' ? 1 : 0 }}">
            @foreach($product->video as $video)
                @continue(! $video['url'])

                <div class="video-thumbnail">
                    <img src="{{ $video['thumbnail'] }}" alt="{{ $product->name }}">
                    <x-core::icon name="ti ti-player-play-filled" />
                </div>
            @endforeach
            @foreach ($productImages as $image)
                <div>
                    {{ RvMedia::image($image, $product->name, 'thumb') }}
                </div>
            @endforeach
        </div>
    </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.img-zoomable').forEach(function (img) {
        $(img).elevateZoom({
            zoomType: "window", 
            lensShape: "square",
            lensSize: 200,
            scrollZoom: true,
            responsive: true,
            zoomWindowWidth: 400,
            zoomWindowHeight: 400,
            zoomWindowOffsetX: 0, 
            zoomWindowFadeIn: 500,
            zoomWindowFadeOut: 500,
        });
    });
});

</script>

<style>
    .bb-product-gallery-images {
        position: relative; /* Ensure proper positioning */
        display: flex;
        justify-content: center; /* Center content horizontally */
        align-items: center; /* Center content vertically */
    }

    .img-zoomable {
        cursor: zoom-in; /* Indicates zoom functionality */
        margin: auto; /* Center image in its container */
        display: block;
        max-width: 100%; /* Prevent overflow */
    }

    .zoomContainer {
        pointer-events: none; /* Avoid interaction conflicts */
    }
    .bb-product-gallery-images.desktop {display: block;}
    .bb-product-gallery-images.mobile{display: none;}

    @media (max-width: 768px) {
        .bb-product-gallery-images.desktop {display: none;}
        .bb-product-gallery-images.mobile{display: block;}
    }
</style>

