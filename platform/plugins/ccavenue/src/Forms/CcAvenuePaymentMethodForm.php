<?php

namespace Botble\CcAvenue\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Forms\PaymentMethodForm;

class CcAvenuePaymentMethodForm extends PaymentMethodForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(CCAVENUE_PAYMENT_METHOD_NAME)
            ->paymentName('CcAvenue')
            ->paymentDescription(trans('plugins/payment::payment.ccavenue_description'))
            ->paymentLogo(url('vendor/core/plugins/ccavenue/images/ccavenue.png'))
            ->paymentUrl('https://www.ccavenue.com/')
            ->defaultDescriptionValue(__('You will be redirected to :name to complete the payment.', ['name' => 'CcAvenue']))
            ->paymentInstructions(view('plugins/ccavenue::instructions')->render())
            ->add(
                sprintf('payment_%s_merchant_id', CCAVENUE_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/payment::payment.merchant_id'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_id', 'ccavenue'))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_access_key', CCAVENUE_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/payment::payment.access_key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('access_key', 'ccavenue'))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_working_key', CCAVENUE_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/payment::payment.working_key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('working_key', 'ccavenue'))
                    ->toArray()
            )->add(
                sprintf('payment_%s_url', CCAVENUE_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(trans('plugins/payment::payment.url'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('url', 'ccavenue'))
                    ->toArray()
            )
            ->add(
                sprintf('payment_%s_mode', CCAVENUE_PAYMENT_METHOD_NAME),
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/payment::payment.live_mode'))
                    ->value(get_payment_setting('mode', CCAVENUE_PAYMENT_METHOD_NAME, true))
                    ->toArray()
            );
    }
}
