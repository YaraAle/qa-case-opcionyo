<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Specialist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'specialist_id' => 'required|exists:specialists,id',
            'scheduled_at' => 'required|date|after:now'
        ]);

        try {
            $appointment = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                // Ensure specialist is available
                $specialist = Specialist::find($request->specialist_id);
                if (!$specialist || !$specialist->available) {
                    throw new \Exception('Especialista no disponible');
                }

                // Check and lock slot
                $exists = Appointment::where('specialist_id', $request->specialist_id)
                    ->where('scheduled_at', $request->scheduled_at)
                    ->where('status', 'scheduled')
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw new \Exception('Horario ocupado');
                }

                return Appointment::create([
                    'user_id' => Auth::id(),
                    'specialist_id' => $request->specialist_id,
                    'scheduled_at' => $request->scheduled_at,
                    'status' => 'scheduled'
                ]);
            });

            return redirect()
                ->back()
                ->with('message', 'Sesión creada correctamente');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('message', $e->getMessage());
        }
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'No autorizado para cancelar esta sesión'
            ], 403);
        }

        $appointment->update([
            'status' => 'cancelled'
        ]);

        return response()->json([
            'message' => 'Sesión cancelada'
        ]);
    }

}