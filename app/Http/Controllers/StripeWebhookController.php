<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Webhook;
use Stripe\Stripe;
use Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                    // TODO: Mark your order as paid, update DB, send emails, etc.
                    Log::info('PaymentIntent succeeded: ' . $paymentIntent->id);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    Log::warning('PaymentIntent failed: ' . $paymentIntent->id);
                    // TODO: Handle failed payments, notify user, etc.
                    break;

                // Add other event types as needed

                default:
                    Log::info('Received unknown event type ' . $event->type);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }
    }
}
