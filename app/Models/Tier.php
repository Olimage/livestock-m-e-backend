<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    protected $fillable = [
        'tier',
        'level',
        'measurement_frequency',
        'attribution',
    ];
}
