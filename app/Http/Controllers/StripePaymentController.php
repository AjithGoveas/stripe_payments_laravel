<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Stripe;

class StripePaymentController extends Controller
{
    public function stripe(): View
    {
        return view('stripe'); 
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $request->amount, // must be in cents, e.g., 1000 = $10
            'currency' => 'usd',
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
        ]);
    }
}
