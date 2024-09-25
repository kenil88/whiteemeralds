@if (Cart::instance('cart')->isNotEmpty() && Cart::instance('cart')->products()->count())
    <div class="cartmini__checkout">
        <div class="d-flex flex-column gap-2 cartmini__checkout-title mb-30">
            @if (EcommerceHelper::isTaxEnabled())
                <div>
                <div>
                    <h4>{{ __('Total:') }}</h4>
                    <span>{{ format_price(Cart::instance('cart')->rawSubTotal()) }}</span>
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

