<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/appointments', [AppController::class, 'appointments'])
        ->name('appointments');

    Route::get('/subscription', [AppController::class, 'subscription'])
        ->name('subscription');

    Route::get('/meeting', [AppController::class, 'meeting'])
        ->name('meeting');

    Route::post(
        '/appointments',
        [AppointmentController::class,'store']
    );
    
    Route::patch(
        '/appointments/{appointment}/cancel',
        [AppointmentController::class,'cancel']
    );

    Route::post(
        '/payment',
        [PaymentController::class,'pay']
    )->name('payment');

});

Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class,'handle']
);

require __DIR__.'/auth.php';
