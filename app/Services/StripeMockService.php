<?php

namespace App\Services;


class StripeMockService
{

    public function charge($card)
    {

        // Validación básica
        if(empty($card) || !is_numeric($card)) {

            return [

                'success'=>false,

                'message'=>'Invalid card'

            ];

        }


        // Tarjeta rechazada de Stripe sandbox
        if($card === '4000000000000002') {

            return [

                'success'=>false,

                'message'=>'Card declined'

            ];

        }


        // Tarjeta aprobada de Stripe sandbox
        if($card === '4242424242424242') {

            return [

                'success'=>true,

                'transaction_id'=>'mock_tx_'.uniqid(),

                'message'=>'Payment successful'

            ];

        }


        return [

            'success'=>false,

            'message'=>'Card not supported'

        ];

    }

}