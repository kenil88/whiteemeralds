<?php

namespace Botble\CcAvenue\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Support\Services\ProduceServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Razorpay\Api\Api;

abstract class CcAvenuePaymentAbstract implements ProduceServiceInterface
{
    use PaymentErrorTrait;

    protected string $paymentCurrency;

    protected Api $client;

    protected bool $supportRefundOnline;

    protected float $totalAmount;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.payment.currency');

        $this->totalAmount = 0;

        $this->setClient();

        $this->supportRefundOnline = true;
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function setClient(): self
    {
        $merchant_id = get_payment_setting('merchant_id', CCAVENUE_PAYMENT_METHOD_NAME);
        $access_key = get_payment_setting('access_key', CCAVENUE_PAYMENT_METHOD_NAME);
        $working_key = get_payment_setting('working_key', CCAVENUE_PAYMENT_METHOD_NAME);
        $url = get_payment_setting('url', CCAVENUE_PAYMENT_METHOD_NAME);
        $this->client = new Api($merchant_id, $access_key);

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->paymentCurrency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->paymentCurrency;
    }

    public function getPaymentDetails($paymentId)
    {
        try {
            do_action('payment_before_making_api_request', CCAVENUE_PAYMENT_METHOD_NAME, ['payment_id' => $paymentId]);

            // @phpstan-ignore-next-line
            $response = $this->client->payment->fetch($paymentId); // Returns a particular payment

            do_action('payment_after_api_response', CCAVENUE_PAYMENT_METHOD_NAME, ['payment_id' => $paymentId], (array) $response);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }

        return $response;
    }

    public function refundOrder($paymentId, $amount, array $options = []): array
    {
        try {
            $data = [
                'payment_id' => $paymentId,
                'amount' => $amount * 100,
                'notes' => $options,
            ];

            do_action('payment_before_making_api_request', CCAVENUE_PAYMENT_METHOD_NAME, $data);

            // @phpstan-ignore-next-line
            $response = $this->client->refund->create($data);

            do_action('payment_after_api_response', CCAVENUE_PAYMENT_METHOD_NAME, $data, (array) $response);

            $status = $response->status;

            if ($response->status == 'processed') {
                $response = $response->toArray();
                $response = array_merge($response, ['_refund_id' => Arr::get($response, 'id')]);

                return [
                    'error' => false,
                    'message' => $status,
                    'data' => $response,
                ];
            }

            return [
                'error' => true,
                'message' => trans('plugins/payment::payment.status_is_not_completed'),
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function getRefundDetails($refundId): array
    {
        try {
            do_action('payment_before_making_api_request', CCAVENUE_PAYMENT_METHOD_NAME, ['refund_id' => $refundId]);

            // @phpstan-ignore-next-line
            $response = $this->client->refund->fetch($refundId);

            do_action('payment_after_api_response', CCAVENUE_PAYMENT_METHOD_NAME, ['refund_id' => $refundId], (array) $response);

            return [
                'error' => false,
                'message' => $response->status,
                'data' => (array) $response->toArray(),
                'status' => $response->status,
            ];
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return [
                'error' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function execute(Request $request)
    {
        try {
            return $this->makePayment($request);
        } catch (Exception $exception) {
            $this->setErrorMessageAndLogging($exception, 1);

            return false;
        }
    }

    abstract public function makePayment(Request $request);

    /**
     * List currencies supported https://razorpay.com/docs/payments/payments/international-payments/#supported-currencies
     */
    public function supportedCurrencyCodes(): array
    {
        return [
            'INR',
            'USD',
            'EUR',
            'SGD',
        ];
    }

    abstract public function afterMakePayment(Request $request);
}
