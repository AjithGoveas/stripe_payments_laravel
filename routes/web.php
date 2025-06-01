<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;  
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\StripeWebhookController;



Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('stripe', [StripePaymentController::class, 'stripe'])->name('stripe');
Route::post('stripe.post', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
Route::post('/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
Route::get('/payment-success', function () {
    return redirect()->route('stripe')->with('success', 'Payment completed!');
})->name('stripe.success');

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);


Route::post('/stripe/webhook', function (Request $request) {
    $payload = $request->getContent();
    $event = json_decode($payload);

    Log::info('Stripe Webhook received: ' . $event->type);

    switch ($event->type) {
        case 'payment_intent.succeeded':
            Log::info('Payment succeeded for PaymentIntent: ' . $event->data->object->id);
            break;

        case 'payment_intent.payment_failed':
            Log::info('Payment failed for PaymentIntent: ' . $event->data->object->id);
            break;


        default:
            Log::info('Received unhandled event type: ' . $event->type);
    }

    return response()->json(['status' => 'success']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
