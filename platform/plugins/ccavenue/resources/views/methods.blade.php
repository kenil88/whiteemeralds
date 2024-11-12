@if (get_payment_setting('status', CCAVENUE_PAYMENT_METHOD_NAME) == 1)
    <x-plugins-payment::payment-method
        :name="CCAVENUE_PAYMENT_METHOD_NAME"
        paymentName="Ccavenue"
        :supportedCurrencies="(new Botble\CcAvenue\Services\Gateways\CcAvenuePaymentService)->supportedCurrencyCodes()"
    >

        @if ($errorMessage)
            <div class="text-danger my-2">
                {!! BaseHelper::clean($errorMessage) !!}
            </div>
        @endif

        <input id="ccavenue_order_id" type="hidden" value="{{ $orderId }}">
    </x-plugins-payment::payment-method>

    @if (EcommerceHelper::isValidToProcessCheckout())
        <script>
            $(document).ready(function() {
                var $paymentCheckoutForm = $(document).find('.payment-checkout-form');

                $paymentCheckoutForm.on('submit', function(e) {
                    if ($paymentCheckoutForm.valid() && $('input[name=payment_method]:checked').val() ===
                        'ccavenue' && !$('input[name=ccavenue_payment_id]').val()) {
                        e.preventDefault();

                        // Redirect to CCAvenue payment page
                        callCcAvenueScript();
                    }
                });

                // Function to handle CCAvenue payment redirect
                var callCcAvenueScript = function() {
                    // Create a form element to post to CCAvenue's payment page
                    var ccavenueForm = $('<form>', {
                        'action': '{{ get_payment_setting("url", CCAVENUE_PAYMENT_METHOD_NAME) }}',
                        'method': 'POST',
                        'id': 'ccavenue-payment-form'
                    });

                    // Add necessary hidden fields
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'order_id',
                        'value': $('#ccavenue_order_id').val()
                    }));
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'merchant_id',
                        'value': '{{ get_payment_setting("merchant_id", CCAVENUE_PAYMENT_METHOD_NAME) }}'
                    }));
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'amount',
                        'value': '{{ $amount }}'  // Set your amount here
                    }));
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'currency',
                        'value': '{{ $currency }}' // Set your currency here
                    }));
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'redirect_url',
                        'value': '{{ route("payments.ccavenue.callback") }}'  // Set callback URL here
                    }));
                    ccavenueForm.append($('<input>', {
                        'type': 'hidden',
                        'name': 'cancel_url',
                        'value': '{{ route("payments.ccavenue.cancel") }}'  // Set cancel URL here
                    }));

                    // Append the form to body and submit
                    $('body').append(ccavenueForm);
                    ccavenueForm.submit();
                }

                $(document).off('click', '.payment-checkout-btn').on('click', '.payment-checkout-btn', function(event) {
                    event.preventDefault();

                    var _self = $(this);
                    var form = _self.closest('form');

                    if (form.valid && !form.valid()) {
                        return;
                    }

                    _self.attr('disabled', 'disabled');
                    var submitInitialText = _self.html();
                    _self.html('<i class="fa fa-gear fa-spin"></i> ' + _self.data('processing-text'));

                    var method = $('input[name=payment_method]:checked').val();

                    if (method === 'ccavenue') {
                        callCcAvenueScript();
                    } else {
                        form.submit();
                    }
                });
            });
        </script>
    @endif
@endif
