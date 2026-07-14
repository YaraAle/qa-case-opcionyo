<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialist;

class AppController extends Controller
{

    public function appointments()
    {
        $specialists = Specialist::where(
            'available',
            true
        )->get();


        return view('appointments', compact('specialists'));
    }


    public function subscription()
    {
        return view('subscription');
    }


    public function meeting()
    {
        return view('meeting');
    }

}