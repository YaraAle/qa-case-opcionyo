<?php

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);


test('usuario puede realizar pago exitoso con tarjeta valida', function () {

    $user = User::factory()->create();


    $response = $this
        ->actingAs($user)
        ->post('/payment', [

            'card'=>'4242424242424242'

        ]);


    $response->assertStatus(302);


    $this->assertDatabaseHas(
        'subscriptions',
        [
            'user_id'=>$user->id,
            'status'=>'active'
        ]
    );

});



test('usuario recibe rechazo con tarjeta declinada', function () {

    $user = User::factory()->create();


    $response = $this
        ->actingAs($user)
        ->post('/payment', [

            'card'=>'4000000000000002'

        ]);


    $response->assertStatus(302);


    $this->assertDatabaseHas(
        'subscriptions',
        [
            'user_id'=>$user->id,
            'status'=>'failed'
        ]
    );

});



test('webhook actualiza suscripcion correctamente', function () {

    $subscription = Subscription::create([

        'user_id'=>User::factory()->create()->id,

        'status'=>'pending',

        'amount'=>100

    ]);


    $response = $this
        ->post('/stripe/webhook', [

            'subscription_id'=>$subscription->id,

            'status'=>'active'

        ]);


    $response->assertStatus(200);


    $this->assertDatabaseHas(
        'subscriptions',
        [
            'id'=>$subscription->id,
            'status'=>'active'
        ]
    );

});