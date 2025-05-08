<?php

namespace App\Http\Controllers\Api\Gateway\Stripe;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use UnexpectedValueException;

class StripeWebHookController extends Controller
{
    public function intent(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'     => ['required', 'integer', 'exists:bookings,id']
        ]);

        $booking = Booking::find($request->booking_id);

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $paymentIntent = PaymentIntent::create([
                'amount'   => $booking->total_price * 100,
                'currency' => 'usd',
                'metadata' => [
                    'booking_id'     => $request->booking_id
                ],
            ]);
            $data = [
                'client_secret' => $paymentIntent->client_secret
            ];
            return Helper::jsonResponse(true, 'Payment intent created successfully', 200, $data);
        } catch (ApiErrorException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }


    public function webhook(Request $request): JsonResponse
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload        = $request->getContent();
        $sigHeader      = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (UnexpectedValueException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        } catch (SignatureVerificationException $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 400, []);
        }

        //? Handle the event based on its type
        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->success($event->data->object);
                    return Helper::jsonResponse(true, 'Payment successful', 200, []);

                case 'payment_intent.payment_failed':
                    $this->failure($event->data->object);
                    return Helper::jsonResponse(true, 'Payment failed', 200, []);

                default:
                    return Helper::jsonResponse(true, 'Unhandled event type', 200, []);
            }
        } catch (Exception $e) {
            return Helper::jsonResponse(false, $e->getMessage(), 500, []);
        }
    }

    protected function success($paymentIntent): void
    {
        $trx_id = $paymentIntent->id;
        $booking_id = $paymentIntent->metadata->booking_id;
        $booking = Booking::find($booking_id);
        if ($booking) {
            $booking->update([
                'transaction_id' => $trx_id,
                'payment_status' => 'paid'
            ]);
        }
    }

    protected function failure($paymentIntent): void
    {
        $booking_id = $paymentIntent->metadata->booking_id;
        $booking = Booking::find($booking_id);
        if ($booking) {
            $booking->update([
                'transaction_id' => "",
                'payment_status' => 'failed'
            ]);
        }
    }
}
