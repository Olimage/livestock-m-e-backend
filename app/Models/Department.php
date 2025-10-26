<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_technical'
    ];

    protected $casts =[
        'is_technical' => 'boolean'
    ];

    public function users(){

        return $this->hasManyThrough( User::class, UserDepartment::class);
    }

    public function permissions()
{
    return $this->morphMany(Permission::class, 'callable');
}
}
