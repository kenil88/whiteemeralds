<div
    class="form-group mb-3 variant-radio product-option product-option-{{ Str::slug($option->name) }} product-option-{{ $option->id }}">
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
                value="dropdown"
            />
            <input type="hidden" name="ring_category" value="{{ $product->categories[0]->name }}">
            <select
                class="form-select"
                name="options[{{ $option->id }}][values]"
                {{ $option->required ? 'required' : '' }}
            >
                <option value="">{{ __('Select an option') }}</option>
                @php
                    // Sort values by option_value in ascending order
                    $sortedValues = $option->values->sortBy('option_value');
                @endphp
                @foreach ($sortedValues as $value)
                    @php
                        $price = 0;
                        if (!empty($value->affect_price) && doubleval($value->affect_price) > 0) {
                            $price = $value->affect_type == 0 ? $value->affect_price : (floatval($value->affect_price) * $product->price()->getPrice()) / 100;
                        }
                    @endphp
                    <option
                        data-extra-price="{{ $price }}"
                        value="{{ $value->option_value }}"
                        {{ strtolower($product->categories[0]->name) == 'ladies ring' && $value->option_value == 13 ? 'selected' : '' }}
                    >{{ $value->option_value }} {{ $price > 0 ? '+' . format_price($price) : '' }}</option>
                @endforeach
            </select>
            <x-core::size-chart />
        </div>
    </div>
</div>
