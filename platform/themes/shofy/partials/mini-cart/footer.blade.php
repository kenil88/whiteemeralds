@if (Cart::instance('cart')->isNotEmpty() && Cart::instance('cart')->products()->count())
    <div class="cartmini__checkout">
        <div class="d-flex flex-column gap-2 cartmini__checkout-title mb-30">
            <div>
                <h4>{{ __('Subtotal:') }}</h4>
                <span>{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
            </div>
            @if (EcommerceHelper::isTaxEnabled())
                <div>
                    <h4>{{ __('Tax:') }}</h4>
                    <span>{{ format_price(Cart::instance('cart')->rawTax()) }}</span>
                </div>
                <div>
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
                    <h4>{{ __('Making Charge:') }}</h4>
                    @if(session('currency_data') == 'INR')
                    <span>{{ format_price($totalMakingCharges) }}</span>
                    @else
                    <span>{{ format_price($totalMakingCharges) }}</span>
                    @endif
                </div>
                <div>
                    <h4>{{ __('Certificate Charge:') }}</h4>
                    @if(session('currency_data') == 'INR')
                    <span>{{ format_price($certificate_charge) }}</span>
                    @else
                    <span>{{ format_price($certificate_charge) }}</span>
                    @endif
                </div>
                <div>
                    <h4>{{ __('Total:') }}</h4>
                    <span>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</span>
                </div>
            @endif
        </div>
        <div class="cartmini__checkout-btn">
            @if (session('tracked_start_checkout'))
                <a href="{{ route('public.checkout.information', session('tracked_start_checkout')) }}" class="mb-10 tp-btn w-100">
                    {{ __('Checkout') }}
                </a>
            @endif

            <a href="{{ route('public.cart') }}" class="tp-btn tp-btn-border w-100">
                {{ __('View Cart') }}
            </a>
        </div>
    </div>
@endif

