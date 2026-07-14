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

    $scheduledAt = now()->addDays(5)->format('Y-m-d H:i:s');

    $response = $this
        ->actingAs($user)
        ->post('/appointments', [
            'specialist_id' => $specialist->id,
            'scheduled_at' => $scheduledAt
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

    $scheduledAt = now()->addDays(5)->format('Y-m-d H:i:s');

    Appointment::create([
        'user_id'=>$user1->id,
        'specialist_id'=>$specialist->id,
        'scheduled_at'=>$scheduledAt,
        'status'=>'scheduled'
    ]);

    $response = $this
        ->actingAs($user2)
        ->post('/appointments',[
            'specialist_id'=>$specialist->id,
            'scheduled_at'=>$scheduledAt
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

    $scheduledAt = now()->addDays(5)->format('Y-m-d H:i:s');

    $appointment = Appointment::create([
        'user_id'=>$user->id,
        'specialist_id'=>$specialist->id,
        'scheduled_at'=>$scheduledAt,
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

test('paciente no puede cancelar la sesión de otro paciente', function () {

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $specialist = Specialist::create([
        'name'=>'Ana Perez',
        'specialty'=>'Psicologia',
        'available'=>true
    ]);

    $scheduledAt = now()->addDays(5)->format('Y-m-d H:i:s');

    $appointment = Appointment::create([
        'user_id'=>$user1->id,
        'specialist_id'=>$specialist->id,
        'scheduled_at'=>$scheduledAt,
        'status'=>'scheduled'
    ]);

    $response = $this
        ->actingAs($user2)
        ->patch(
            "/appointments/{$appointment->id}/cancel"
        );

    $response->assertStatus(403);

    $this->assertDatabaseHas(
        'appointments',
        [
            'id'=>$appointment->id,
            'status'=>'scheduled'
        ]
    );

});

test('paciente no puede agendar una sesión en el pasado', function () {

    $user = User::factory()->create();

    $specialist = Specialist::create([
        'name'=>'Ana Perez',
        'specialty'=>'Psicologia',
        'available'=>true
    ]);

    $scheduledAt = now()->subDays(1)->format('Y-m-d H:i:s');

    $response = $this
        ->actingAs($user)
        ->post('/appointments', [
            'specialist_id' => $specialist->id,
            'scheduled_at' => $scheduledAt
        ]);

    $response->assertSessionHasErrors(['scheduled_at']);

});

test('paciente no puede agendar con un especialista no disponible', function () {

    $user = User::factory()->create();

    $specialist = Specialist::create([
        'name'=>'Ana Perez',
        'specialty'=>'Psicologia',
        'available'=>false
    ]);

    $scheduledAt = now()->addDays(5)->format('Y-m-d H:i:s');

    $response = $this
        ->actingAs($user)
        ->post('/appointments', [
            'specialist_id' => $specialist->id,
            'scheduled_at' => $scheduledAt
        ]);

    $response->assertSessionHas(
        'message',
        'Especialista no disponible'
    );

});