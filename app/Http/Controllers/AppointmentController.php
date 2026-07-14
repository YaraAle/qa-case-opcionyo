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
            'specialist_id'=>'required',
            'scheduled_at'=>'required'
        ]);


        $exists = Appointment::where(
            'specialist_id',
            $request->specialist_id
        )
        ->where(
            'scheduled_at',
            $request->scheduled_at
        )
        ->where(
            'status',
            'scheduled'
        )
        ->exists();


        if($exists){

            return redirect()
            ->back()
            ->with(
                'message',
                'Horario ocupado'
            );

        }


        $appointment = Appointment::create([

            //'user_id'=>auth()->id(),
            'user_id'=>Auth::id(),

            'specialist_id'=>$request->specialist_id,

            'scheduled_at'=>$request->scheduled_at,

            'status'=>'scheduled'

        ]);


        return redirect()
        ->back()
        ->with(
            'message',
            'Sesión creada correctamente'
        );

    }


    public function cancel(Appointment $appointment)
    {

        $appointment->update([
            'status'=>'cancelled'
        ]);


        return response()->json([
            'message'=>'Sesión cancelada'
        ]);

    }

}