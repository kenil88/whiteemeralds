<section class="tp-brand-area">
    <div class="container">
        @if($shortcode->title || $shortcode->subtitle)
            <div class="mb-40">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>
        @endif

        <div class="row row-cols-4 row-cols-sm-3 row-cols-lg-4 row-cols-xl-6 g-3" style="margin-left: 10%;">
            <div></div>
            @foreach($brands as $brand)
                <div class="col">
                    <div class="tp-brand-item text-center">
                        <a href="{{ $brand->url }}" title="{{ $brand->name }}">
                            {{ RvMedia::image($brand->logo, $brand->name) }}
                        </a>
                    </div>

                    @if ($shortcode->show_brand_name)
                        <h6 class="mt-3 text-center">
                            <a href="{{ $brand->url }}" title="{{ $brand->name }}">{{ $brand->name }}</a>
                        </h6>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
