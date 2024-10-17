<?php

namespace Botble\CcAvenue\Services\Gateways;

use Botble\CcAvenue\Services\Abstracts\CcAvenuePaymentAbstract;
use Illuminate\Http\Request;

class CcAvenuePaymentService extends CcAvenuePaymentAbstract
{
    public function makePayment(Request $request) {}

    public function afterMakePayment(Request $request) {}
}
