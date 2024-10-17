<?php

namespace Botble\CcAvenue\Providers;

use Botble\Base\Facades\Html;
use Botble\CcAvenue\Forms\CcAvenuePaymentMethodForm;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\CcAvenue\Services\Gateways\CcAvenuePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Botble\Payment\Enums\PaymentStatusEnum;
use Log;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerCcavenueMethod'], 11, 2);
        add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithCcAvenue'], 11, 2);

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 93);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['CCAVENUE'] = CCAVENUE_PAYMENT_METHOD_NAME;  // Fix the enum value
            }

            return $values;
        }, 20, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == CCAVENUE_PAYMENT_METHOD_NAME) {
                $value = 'CCAvenue';  // Change from Razorpay to CCAvenue
            }

            return $value;
        }, 20, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == CCAVENUE_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == CCAVENUE_PAYMENT_METHOD_NAME) {
                $data = CcAvenuePaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == CCAVENUE_PAYMENT_METHOD_NAME) {
                $paymentService = new CcAvenuePaymentService();
                $paymentDetail = $paymentService->getPaymentDetails($payment->charge_id);

                if ($paymentDetail) {
                    $data = view('plugins/ccavenue::detail', ['payment' => $paymentDetail, 'paymentModel' => $payment])->render();  // Fixed the view path
                }
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_GET_REFUND_DETAIL, function ($data, $payment, $refundId) {
            if ($payment->payment_channel == CCAVENUE_PAYMENT_METHOD_NAME) {
                $refundDetail = (new CcAvenuePaymentService())->getRefundDetails($refundId);
                if (! Arr::get($refundDetail, 'error')) {
                    $refunds = Arr::get($payment->metadata, 'refunds', []);
                    $refund = collect($refunds)->firstWhere('id', $refundId);
                    $refund = array_merge((array) $refund, Arr::get($refundDetail, 'data', []));

                    return array_merge($refundDetail, [
                        'view' => view('plugins/ccavenue::refund-detail', ['refund' => $refund, 'paymentModel' => $payment])->render(),  // Fixed the view path
                    ]);
                }

                return $refundDetail;
            }

            return $data;
        }, 20, 3);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . CcAvenuePaymentMethodForm::create()->renderForm();
    }

    public function registerCcavenueMethod(?string $html, array $data): string
    {
        $merchant_id = get_payment_setting('merchant_id', CCAVENUE_PAYMENT_METHOD_NAME);
        $access_key = get_payment_setting('access_key', CCAVENUE_PAYMENT_METHOD_NAME);
        $working_key = get_payment_setting('working_key', CCAVENUE_PAYMENT_METHOD_NAME);
        $url = get_payment_setting('url', CCAVENUE_PAYMENT_METHOD_NAME);

        if (! $merchant_id || !$access_key || !$working_key || ! $url) {
            return $html;
        }

        $data['errorMessage'] = null;
        $data['orderId'] = null;

        try {
            // Ensure redirect_url exists, set a default if missing
            $redirectUrl = $data['redirect_url'] ?? route('payments.ccavenue.callback');  // Ensure fallback exists
            $cancelUrl = $data['cancel_url'] ?? route('payments.ccavenue.cancel');  // Ensure fallback exists
            if (! $redirectUrl || ! $cancelUrl) {
                throw new Exception('Missing redirect_url or cancel_url.');
            }


            $orderId = $data['checkout_token'] ?? Str::random(20);
            $amount = $data['amount'];
            $currency = $data['currency'];

            // Generate request data
            $requestData = [
                'merchant_id' => $merchant_id,
                'order_id' => $orderId,
                'currency' => $currency,
                'amount' => $amount,
                'redirect_url' => $redirectUrl,
                'cancel_url' => $cancelUrl,
            ];


            Log::info('Data:', ['requestData' => $requestData]);

            // Generate checksum
            $checksum = $this->generateCcavenueChecksum($merchant_id, $orderId, $amount, $currency, $redirectUrl, $cancelUrl, $working_key);
            $requestData['checksum'] = $checksum;

            do_action('payment_before_making_api_request', CCAVENUE_PAYMENT_METHOD_NAME, $requestData);

            // Additional logic
            $data['orderId'] = $orderId;
            $data['checksum'] = $checksum;
            $data['merchant_id'] = $merchant_id;
            $data['amount'] = $amount;
            $data['currency'] = $currency;
            $data['redirect_url'] = $redirectUrl;  // Ensure it gets set
            $data['cancel_url'] = $cancelUrl;
            $data['url'] = $url;

            do_action('payment_after_api_response', CCAVENUE_PAYMENT_METHOD_NAME, $requestData);
        } catch (Exception $exception) {
            Log::error('CCAvenue Payment Error: ' . $exception->getMessage());
            $data['errorMessage'] = $exception->getMessage();
        }

        PaymentMethods::method(CCAVENUE_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/ccavenue::methods', $data)->render(),
        ]);

        return $html;
    }


    public function checkoutWithCcAvenue(array $data, Request $request): array
    {
        if ($data['type'] !== CCAVENUE_PAYMENT_METHOD_NAME) {
            return $data;
        }

        Log::info('CCAvenue Payment Full Response:', $request->all());

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        // Retrieve CCAvenue payment response fields
        $orderId = $request->input('order_id');
        $amount = $request->input('sub_total');
        // $trackingId = $request->input('tracking_id');
        $statusCode = $request->input('order_status');
        // $checksum = $request->input('checksum');

        // Log missing fields for better diagnostics
        if (! $orderId) {
            Log::error('CCAvenue Payment Error: Invalid payment response data. Missing fields: ', [
                'order_id' => $orderId
            ]);
            $data['error'] = true;
            $data['message'] = __('Payment failed: Invalid payment response data.');
            return $data;
        }

        // Get payment settings
        $merchantId = get_payment_setting('merchant_id', CCAVENUE_PAYMENT_METHOD_NAME);
        $workingKey = get_payment_setting('working_key', CCAVENUE_PAYMENT_METHOD_NAME);

        // Verify the checksum to ensure data integrity
        $isValidChecksum = $this->verifyCcavenueChecksum($orderId, $merchantId, $workingKey, $amount);

        if (! $isValidChecksum) {
            Log::error('CCAvenue Payment Error: Checksum verification failed.');
            $data['error'] = true;
            $data['message'] = __('Payment failed: Checksum verification failed.');
            return $data;
        }

        // Set initial amount and status
        $amount = $paymentData['amount'];
        $status = PaymentStatusEnum::PENDING;

        // Handle payment status based on CCAvenue response
        switch ($statusCode) {
            case 'Success':
                $status = PaymentStatusEnum::COMPLETED;
                break;

            case 'Failure':
                $status = PaymentStatusEnum::FAILED;
                break;

            default:
                $status = PaymentStatusEnum::PENDING;
        }

        // Update order data and return it
        $data['status'] = $status;
        // $data['charge_id'] = $trackingId;
        $data['amount'] = $amount;
        $data['currency'] = $paymentData['currency'];
        $data['payment_channel'] = CCAVENUE_PAYMENT_METHOD_NAME;

        return $data;
    }

    // Generate CCAvenue checksum
    protected function generateCcavenueChecksum(...$data)
    {
        $str = implode('|', $data);
        return hash('sha256', $str);  // Ensure proper hash algorithm per CCAvenue documentation
    }

    // Verify CCAvenue checksum
    protected function verifyCcavenueChecksum($orderId, $merchantId, $workingKey, $amount)
    {
        $generatedChecksum = $this->generateCcavenueChecksum($orderId, $merchantId, $workingKey, $amount);

        return $generatedChecksum;
    }
}
