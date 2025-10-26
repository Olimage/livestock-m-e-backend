<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['name', 'zone_id'];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }
}
