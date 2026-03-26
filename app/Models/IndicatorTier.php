<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorTier extends Model
{
    protected $fillable = [
        'name',
        'prefix',
    ];

    public function indicators()
    {
        return $this->hasMany(Indicator::class);
    }
}
