<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['name', 'code'];

    function states()
    {
        return $this->hasMany(State::class);
    }
}
