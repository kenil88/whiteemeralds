@php
    $isDisplayPriceOriginal ??= true;
    $priceWrapperClassName ??= null;
    $priceClassName ??= null;
    $priceOriginalClassName ??= null;
    $priceOriginalWrapperClassName ??= null;

    // Get the category ID for the product
    $category_id = DB::table('ec_product_category_product')
        ->where('product_id', $product->id)
        ->value('category_id');
@endphp

<div class="{{ $priceWrapperClassName === null ? 'bb-product-price mb-3' : $priceWrapperClassName }}">
    @if (in_array($category_id, [35, 36, 37, 38, 39]))
        <span
            class="{{ $priceClassName === null ? 'bb-product-price-text fw-bold' : $priceClassName }}"
            data-bb-value="product-price"
            @if (!in_array($category_id, [36, 37, 38, 39])) 
                id="new-price"
            @endif
        >
            {{ $product->price()->displayAsText() }}
        </span>
    @endif
</div>

<input name="product_id" type="hidden" value="{{ $product->id }}">
