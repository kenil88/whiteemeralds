@php
$isDisplayPriceOriginal ??= true;
$priceWrapperClassName ??= null;
$priceClassName ??= null;
$priceOriginalClassName ??= null;
$priceOriginalWrapperClassName ??= null;
$options ??= null;
@endphp

<div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
    <span
        class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
        data-bb-value="product-price">{{ $product->price()->displayAsTextCart($options) }}</span>
</div>