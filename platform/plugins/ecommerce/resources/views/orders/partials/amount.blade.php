{!! apply_filters(RENDER_PRODUCTS_IN_CHECKOUT_PAGE, $products) !!}

<div class="mt-2 p-2">
    <div class="row">
        <div class="col-6">
            <p>{{ __('Subtotal') }}:</p>
        </div>
        <div class="col-6">
            <p class="price-text sub-total-text text-end">
                {{ format_price(Cart::instance('cart')->rawSubTotal()) }}
            </p>
        </div>
    </div>
    {{-- @if (EcommerceHelper::isTaxEnabled())
        <div class="row">
            <div class="col-6">
                <p>{{ __('Tax') }} @if (Cart::instance('cart')->rawTax())
                    (<small>{{ Cart::instance('cart')->taxClassesName() }}</small>)
                @endif</p>
            </div>
            <div class="col-6 float-end">
                <p class="price-text tax-price-text">
                    {{ format_price(Cart::instance('cart')->rawTax()) }}
                </p>
            </div>
        </div>
    @endif --}}
    @if (session('applied_coupon_code'))
        <div class="row coupon-information">
            <div class="col-6">
                <p>{{ __('Coupon code') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text coupon-code-text">
                    {{ session('applied_coupon_code') }}
                </p>
            </div>
        </div>
    @endif
    @if ($couponDiscountAmount > 0)
        <div class="row price discount-amount">
            <div class="col-6">
                <p>{{ __('Coupon code discount amount') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text total-discount-amount-text">
                    {{ format_price($couponDiscountAmount) }}
                </p>
            </div>
        </div>
    @endif
    @if ($promotionDiscountAmount > 0)
        <div class="row">
            <div class="col-6">
                <p>{{ __('Promotion discount amount') }}:</p>
            </div>
            <div class="col-6">
                <p class="price-text">
                    {{ format_price($promotionDiscountAmount) }}
                </p>
            </div>
        </div>
    @endif
    {{-- @if (!empty($shipping) && Arr::get($sessionCheckoutData, 'is_available_shipping', true))
        <div class="row">
            <div class="col-6">
                <p>{{ __('Shipping fee') }}:</p>
            </div>
            <div class="col-6 float-end">
                <p class="price-text shipping-price-text">{{ format_price($shippingAmount) }}</p>
            </div>
        </div>
    @endif --}}

     @php
        $originalProducts = Cart::instance('cart')->products();
        $totalMakingCharges = 0;
    @endphp


    @foreach($originalProducts as $product)
        @php
            // Calculate making charges based on weight
            if ($product->weight > 5.00) {
                $making_charges = config('plugins.ecommerce.general.making_charge.India') * Cart::instance('cart')->rawTotalQuantity() * $product->weight;
            } else {
                $making_charges = config('plugins.ecommerce.general.making_charge.India') * Cart::instance('cart')->rawTotalQuantity();
            }

            // Add to total making charges
            $totalMakingCharges += $making_charges;
        @endphp
    @endforeach
            
        @if(session('currency_data') == 'INR')
            @php
                $certificate_charge = config('plugins.ecommerce.general.certificate_charge.India') * Cart::instance('cart')->rawTotalQuantity();
            @endphp
        @else
            @php
                $certificate_charge = config('plugins.ecommerce.general.certificate_charge.Out_of_india') * Cart::instance('cart')->rawTotalQuantity();
            @endphp
        @endif

    {{-- <div class="row">
        <div class="col-6">
            <p>{{ __('Making Charge') }}:</p>
        </div>
        <div class="col-6 float-end">
            <p class="price-text shipping-price-text">{{ format_price($totalMakingCharges) }}</p>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <p>{{ __('Certificate Charge') }}:</p>
        </div>
        <div class="col-6 float-end">
            <p class="price-text shipping-price-text">{{ format_price($certificate_charge) }}</p>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-6">
            <p><strong>{{ __('Total') }}</strong>:</p>
        </div>
        <div class="col-6 float-end">
            <p class="total-text raw-total-text" data-price="{{ format_price($rawTotal, null, true) }}">
                {{ format_price($orderAmount) }}
            </p>
        </div>
    </div>
</div>
