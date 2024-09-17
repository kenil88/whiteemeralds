@php
    // Get the output from the displayAsText() function
    $priceText = $product->price()->displayAsText();

    // Use regex to capture either alphabetic currency codes (like USD) or symbols (like ₹)
    preg_match('/^[^\d]+/', $priceText, $matches);
    $currency = $matches[0] ?? 'Unknown'; // Default to 'Unknown' if no match found

    // Define the allowed purity options for each currency
    $allowedPurities = [];
    if ($currency == '₹') {
        $allowedPurities = ['18k', '14k'];
    } elseif ($currency == 'USD') {
        $allowedPurities = ['14k', '10k'];
    }
@endphp

<div
    class="form-group mb-3 variant-radio product-option product-option-{{ Str::slug($option->name) }} product-option-{{ $option->id }}"
    style="margin-bottom: 10px"
>
    <div class="product-option-item-wrapper">
        <div class="product-option-item-label">
            <label class="{{ $option->required ? 'required' : '' }}">
                {{ $option->name }}
            </label>
        </div>
        <div class="product-option-item-values">
            <input
                name="options[{{ $option->id }}][option_type]"
                type="hidden"
                value="radio"
            />

            <input name="product_id" type="hidden" value="{{ $product->id}}">

            @php
                // Flag to check if the first valid option has been selected
                $firstValidOptionSelected = false;
            @endphp

            @foreach ($option->values as $value)
                @php
                    // Check if this is a "metal purity" option
                    $isMetalPurity = Str::slug($option->name) == 'metal-purity';

                    // Determine if the option should be displayed (for metal purity based on currency)
                    $shouldDisplay = !$isMetalPurity || ($isMetalPurity && in_array(strtolower($value->option_value), $allowedPurities));

                    $price = 0;
                    if (!empty($value->affect_price) && doubleval($value->affect_price) > 0) {
                        $price = $value->affect_type == 0 ? $value->affect_price : (floatval($value->affect_price) * $product->price()->getPrice()) / 100;
                    }
                @endphp

                @if ($shouldDisplay)
                    <div class="{{ $wrapperClass ?? 'form-radio' }}">
                        <input
                            id="option-{{ $option->id }}-value-{{ Str::slug($value->option_value) }}"
                            name="options[{{ $option->id }}][values]"
                            data-extra-price="{{ $price }}" data-option-id={{ $option->id }}
                            type="radio" data-option-value={{ Str::slug($value->option_value)}}
                            value="{{ $value->option_value }}"
                            @if (isset($inputClass)) class="{{ $inputClass }}" @endif

                            {{-- Check if it's the first valid option and set it as checked --}}
                            {{-- @if (!$firstValidOptionSelected)
                                checked
                                @php
                                    $firstValidOptionSelected = true;
                                @endphp
                            @endif --}}
                        >
                        
                        <label for="option-{{ $option->id }}-value-{{ Str::slug($value->option_value) }}" @if (isset($labelClass)) class="{{ $labelClass }}" @endif>
                            &nbsp;{{ $value->option_value }}
                            {{-- @if ($price > 0)
                                <strong class="extra-price">+ {{ format_price($price) }}</strong>
                            @endif --}}
                        </label>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
<style>
.product-option-item-values {
    display: flex;
    gap: 10px; /* Adjust the gap between radio buttons as needed */
}

.form-check-group {
    display: flex;
    align-items: center; /* Align items vertically centered */
}

.form-check {
    margin-right: 20px; /* Adjust spacing between form checks */
}
</style>

