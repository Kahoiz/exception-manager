<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Cause extends Model
{
    use Notifiable;
    protected $fillable = [
        'application',
        'data',
        ];
}
