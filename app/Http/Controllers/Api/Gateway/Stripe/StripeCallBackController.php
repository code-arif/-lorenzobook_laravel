<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Enums\StripPayment;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

class StripeCallBackController extends Controller
{
    public function checkout($booking_id)
    {
        /* $validatedData = $request->validate([
            'booking_id' => ['required', 'integer', 'exists:bookings,id']
        ]); */

        try {
            $booking = Booking::findOrFail($booking_id);

            if ($booking->total_price <= 0) {
                return Helper::jsonResponse(false, 'Invalid total price', 400, []);
            }

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $redirectUrl = route('payment.stripe.success') . '?token={CHECKOUT_SESSION_ID}&order=' . $booking->id;
            $cancelUrl = route('payment.stripe.cancel');

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $booking->currency == 'USD' ? 'usd' : 'gbp',
                        'product_data' => [
                            'name' => 'Order ' . $booking->id,
                        ],
                        'unit_amount' => $booking->total_price * 100, // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $redirectUrl,
                'cancel_url' => $cancelUrl,
            ]);

            return Helper::jsonResponse(true, 'Checkout session created successfully', 200, $session->url);
        } catch (ModelNotFoundException $e) {
            return redirect()->to(StripPayment::FAIL->value);
        } catch (ApiErrorException $e) {
            return redirect()->to(StripPayment::FAIL->value);
        }
    }

    public function success(Request $request)
    {
        
        $validatedData = $request->validate([
            'token' => ['required', 'string'],
            'order' => ['required', 'integer', 'exists:bookings,id']
        ]);

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session = Session::retrieve($validatedData['token']);
            if ($session->payment_status === 'paid') {
                $booking = Booking::findOrFail($validatedData['order']);
                $booking->payment_status = 'paid';
                $booking->transaction_id = $session->payment_intent;
                $booking->save();

                /* return Helper::jsonResponse(true, 'Payment successful', 200, [
                    'booking_id' => $booking->unique_id,
                    'payment_status' => $booking->payment_status,
                ]); */

                return redirect()->to(StripPayment::SUCCESS->value);
            }

            if ($session->payment_status === 'unpaid' || $session->payment_status === 'no_payment_required') {
                return redirect()->to(StripPayment::FAIL->value);
            }

            return redirect()->to(StripPayment::FAIL->value);
        } catch (ApiErrorException $e) {
            return redirect()->to(StripPayment::FAIL->value);
        } catch (ModelNotFoundException $e) {
            return redirect()->to(StripPayment::FAIL->value);
        }
    }


    public function failure(Request $request)
    {
        $orderId = $request->query('order');

        if (!$orderId) {
            return redirect()->to(StripPayment::FAIL->value);
        }

        try {
            $booking = Booking::findOrFail($orderId);
            $booking->update(['payment_status' => 'failed']);

            /* return Helper::jsonResponse(false, 'Payment canceled', 200, [
                'booking_id' => $booking->unique_id,
                'payment_status' => $booking->payment_status,
            ]); */

            return redirect()->to(StripPayment::FAIL->value);

        } catch (ModelNotFoundException $e) {
            return redirect()->to(StripPayment::FAIL->value);
        }
    }

}
