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
            <input name="product_id" type="hidden" value="{{ $product->id}}">
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
                    @if($product->categories[0]->id != 27)
                    <option
                        data-extra-price="{{ $price }}"
                        value="{{ $value->option_value }}"
                        {{-- Set default selected value for 'ladies ring' as 13 and 'gents ring' as 20 --}}
                        {{ strtolower($product->categories[0]->name) == 'gents ring' ? ($value->option_value == 20 ? 'selected' : '') : ($value->option_value == 13 ? 'selected' : '') }}
                    >{{ $value->option_value }} {{ $price > 0 ? '+' . format_price($price) : '' }}</option>
                    @else
                    <option
                        data-extra-price="{{ $price }}"
                        value="{{ $value->option_value }}"
                        {{-- Set default selected value for 'ladies ring' as 13 and 'gents ring' as 20 --}}
                        {{ ($value->option_value == 2.4 || $value->option_value == 7.5) ? 'selected' : '' }}
                    >{{ $value->option_value }} {{ $price > 0 ? '+' . format_price($price) : '' }}</option>
                    @endif
                @endforeach
            </select>
            @if($product->categories[0]->id != 27 && $product->categories[0]->id != 36)
            <x-core::size-chart />
            @endif
        </div>
    </div>
</div>
