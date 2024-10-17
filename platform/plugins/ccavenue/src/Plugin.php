<?php

namespace Botble\CcAvenue;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_ccavenue_name',
            'payment_ccavenue_description',
            'payment_ccavenue_merchant_id',
            'payment_ccavenue_access_key',
            'payment_ccavenue_working_key',
            'payment_ccavenue_url',
            'payment_ccavenue_mode',
            'payment_ccavenue_status',
        ]);
    }
}
