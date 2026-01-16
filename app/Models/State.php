<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class State extends Model
{
    protected $fillable = ['name', 'zone_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function lgas()
    {
        return $this->hasMany(Lga::class);
    }
}
