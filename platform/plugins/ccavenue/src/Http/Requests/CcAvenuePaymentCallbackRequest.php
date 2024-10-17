<?php

namespace Botble\CcAvenue\Http\Requests;

use Botble\Support\Http\Requests\Request;

class CcAvenuePaymentCallbackRequest extends Request
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'currency' => 'required',
        ];
    }
}
