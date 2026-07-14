<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;


class StripeWebhookController extends Controller
{

    public function handle(Request $request)
    {

        $subscription = Subscription::find(
            $request->subscription_id
        );


        if(!$subscription){

            return response()->json([
                'message'=>'Subscription not found'
            ],404);

        }


        $subscription->update([

            'status'=>$request->status

        ]);


        return response()->json([

            'message'=>'Webhook processed'

        ],200);

    }

}