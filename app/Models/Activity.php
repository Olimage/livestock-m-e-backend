<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['program_id', 'code', 'title', 'description'];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function outputIndicators()
    {
        return $this->belongsToMany(OutputIndicator::class, 'activity_output_indicator');
    }
}
