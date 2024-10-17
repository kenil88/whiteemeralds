<?php

use Botble\CcAvenue\Http\Controllers\CcAvenueController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => CcAvenueController::class, 'middleware' => ['web', 'core']], function () {
    Route::get('payment/ccavenue/status', 'getCallback')->name('payments.ccavenue.status');
    Route::post('/payments/ccavenue/callback', [CcAvenueController::class, 'getCallback'])->name('payments.ccavenue.callback');

    Route::get('/payments/ccavenue/cancel', function () {
        // Return the view for canceled payments or handle the cancel logic
        return view('payments.cancel');
    })->name('payments.ccavenue.cancel');
});
