@if (setting('payment_ccavenue_status') == 1)
    <x-plugins-payment::payment-method
        :name="CCAVENUE_PAYMENT_METHOD_NAME"
        paymentName="ccavenue"
        :supportedCurrencies="(new Botble\CcAvenue\Services\Gateways\CcAvenuePaymentService)->supportedCurrencyCodes()"
    >
        <x-slot name="currencyNotSupportedMessage">
            <p class="mt-1 mb-0">
                {{ __('Learn more') }}:
                {{ Html::link('https://developer.paypal.com/docs/api/reference/currency-codes', attributes: ['target' => '_blank', 'rel' => 'nofollow']) }}.
            </p>
        </x-slot>
    </x-plugins-payment::payment-method>
@endif
