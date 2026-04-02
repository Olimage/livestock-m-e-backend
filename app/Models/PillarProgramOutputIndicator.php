<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PillarProgramOutputIndicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'title', 'description', 'program_id'];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            $model->updateQuietly(['code' => 'PPOI-' . $model->id]);
        });
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function outcomeIndicators()
    {
        return $this->belongsToMany(OutcomeIndicator::class, 'pillar_program_output_indicator_outcome_indicator');
    }
}
