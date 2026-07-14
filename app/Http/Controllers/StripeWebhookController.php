<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;


class StripeWebhookController extends Controller
{

    public function handle(Request $request)
    {
        // Simulate Stripe Webhook Signature Verification
        $signature = $request->header('X-Stripe-Signature');
        if (!$signature || $signature !== 'mock_stripe_signature_secret') {
            return response()->json([
                'message' => 'Invalid or missing webhook signature'
            ], 401);
        }

        // Find subscription by stripe_id (Stripe transaction/subscription reference)
        // Fallback to internal ID for backwards compatibility
        $subscription = Subscription::where('stripe_id', $request->stripe_id)
            ->orWhere('id', $request->subscription_id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found'
            ], 404);
        }

        $subscription->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Webhook processed'
        ], 200);
    }

}