<?php

namespace Botble\CcAvenue\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Supports\PaymentHelper;
use Botble\CcAvenue\Http\Requests\CcAvenuePaymentCallbackRequest;
use Botble\CcAvenue\Services\Gateways\CcAvenuePaymentService;

class CcAvenueController extends BaseController
{
    public function getCallback(
        CcAvenuePaymentCallbackRequest $request,
        CcAvenuePaymentService $ccavenuePaymentService,
        BaseHttpResponse $response
    ) {
        // Log incoming request data
        Log::info('CCAvenue Callback Request:', $request->all());

        $status = $ccavenuePaymentService->getPaymentStatus($request);

        if (!$status) {
            Log::error('Payment status check failed');
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage(__('Payment failed!'));
        }

        // Log successful payment processing
        Log::info('Payment successful:', $request->input());
        $ccavenuePaymentService->afterMakePayment($request->input());

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL())
            ->setMessage(__('Checkout successfully!'));
    }

    public static function getCancelURL()
    {
        return route('payments.ccavenue.cancel');
    }
}
