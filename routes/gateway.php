<?php

use App\Http\Controllers\Api\Gateway\Stripe\StripeCallBackController;
use App\Http\Controllers\Api\Gateway\Stripe\StripeWebHookController;
use Illuminate\Support\Facades\Route;

//stripe callback
Route::controller(StripeCallBackController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
    Route::get('/checkout/{booking_id}', [StripeCallBackController::class, 'checkout'])->name('checkout');
    Route::get('/success', [StripeCallBackController::class, 'success'])->name('success');
    Route::get('/cancel', [StripeCallBackController::class, 'cancel'])->name('cancel');
});

//stripe webhook
/* Route::controller(StripeWebHookController::class)->prefix('payment/stripe')->name('payment.stripe.')->group(function () {
    Route::post('/intent', [StripeWebHookController::class, 'intent']);
    Route::post('/webhook', [StripeWebHookController::class, 'webhook'])->name('webhook');
}); */
