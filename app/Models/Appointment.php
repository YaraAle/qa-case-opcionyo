<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'specialist_id',
        'scheduled_at',
        'status'
    ];


    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}