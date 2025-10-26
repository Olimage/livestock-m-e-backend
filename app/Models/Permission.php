<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
     protected $fillable = [
        'callable_type',
        'callable_id',
        'permission',
        'description',
    ];

    public function callable()
    {
        return $this->morphTo();
    }
}
