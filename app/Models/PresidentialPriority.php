<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PresidentialPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'description'
    ];


}