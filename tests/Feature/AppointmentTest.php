<?php

use App\Models\User;
use App\Models\Specialist;
use App\Models\Appointment;


test('paciente puede agendar una sesión con especialista disponible', function () {

    $user = User::factory()->create();


    $specialist = Specialist::create([
        'name' => 'Ana Perez',
        'specialty' => 'Psicologia',
        'available' => true
    ]);


    $response = $this
        ->actingAs($user)
        ->post('/appointments', [

            'specialist_id' => $specialist->id,

            'scheduled_at' => '2026-07-20 10:00'

        ]);


    $response->assertStatus(302);


    $this->assertDatabaseHas('appointments', [

        'user_id' => $user->id,

        'specialist_id' => $specialist->id,

        'status' => 'scheduled'

    ]);

});

test('otro usuario no puede agendar el mismo horario ocupado', function () {


    $user1 = User::factory()->create();

    $user2 = User::factory()->create();


    $specialist = Specialist::create([
        'name'=>'Ana Perez',
        'specialty'=>'Psicologia',
        'available'=>true
    ]);



    Appointment::create([

        'user_id'=>$user1->id,

        'specialist_id'=>$specialist->id,

        'scheduled_at'=>'2026-07-20 10:00',

        'status'=>'scheduled'

    ]);



    $response = $this
        ->actingAs($user2)
        ->post('/appointments',[

            'specialist_id'=>$specialist->id,

            'scheduled_at'=>'2026-07-20 10:00'

        ]);



    $response->assertSessionHas(
        'message',
        'Horario ocupado'
    );



});

test('paciente puede cancelar una sesión y liberar horario', function () {


    $user = User::factory()->create();


    $specialist = Specialist::create([

        'name'=>'Ana Perez',

        'specialty'=>'Psicologia',

        'available'=>true

    ]);



    $appointment = Appointment::create([

        'user_id'=>$user->id,

        'specialist_id'=>$specialist->id,

        'scheduled_at'=>'2026-07-20 10:00',

        'status'=>'scheduled'

    ]);



    $response = $this
        ->actingAs($user)
        ->patch(
            "/appointments/{$appointment->id}/cancel"
        );


    $response->assertStatus(200);



    $this->assertDatabaseHas(
        'appointments',
        [
            'id'=>$appointment->id,
            'status'=>'cancelled'
        ]
    );


});