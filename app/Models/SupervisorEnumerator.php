<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupervisorEnumerator extends Model
{

    protected $fillable = [
        'supervisor_id',
        'enumerator_id'
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function enumerator()
    {
        return $this->belongsTo(User::class, 'enumerator_id');
    }
}
