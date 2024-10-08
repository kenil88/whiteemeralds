@php
    $isDisplayPriceOriginal ??= true;
    $priceWrapperClassName ??= null;
    $priceClassName ??= null;
    $priceOriginalClassName ??= null;
    $priceOriginalWrapperClassName ??= null;
@endphp

<div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
    <span
        class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
        data-bb-value="product-price"
    id="new-price"></span>
</div>
<input name="product_id" type="hidden" value="{{ $product->id}}">