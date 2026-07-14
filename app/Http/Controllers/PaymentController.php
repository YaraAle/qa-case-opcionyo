<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Services\StripeMockService;
use Illuminate\Support\Facades\Auth;


class PaymentController extends Controller
{


    public function pay(
        Request $request,
        StripeMockService $stripe
    )
    {

        $result = $stripe->charge(
            $request->card
        );


        if(!$result['success']){


            Subscription::create([

                //'user_id'=>auth()->id(),
                'user_id'=>Auth::id(),

                'status'=>'failed',

                'amount'=>100

            ]);


            return back()
                ->with(
                    'message',
                    'Pago rechazado'
                );

        }



        Subscription::create([

            //'user_id'=>auth()->id(),
            'user_id'=>Auth::id(),

            'stripe_id'=>$result['transaction_id'],

            'status'=>'active',

            'amount'=>100

        ]);



        return back()
            ->with(
                'message',
                'Pago exitoso'
            );


    }


}