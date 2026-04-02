<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BondOutputIndicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'title', 'description'];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            $model->updateQuietly(['code' => 'BOI-' . $model->id]);
        });
    }

    public function outcomeIndicators()
    {
        return $this->belongsToMany(OutcomeIndicator::class, 'bond_output_indicator_outcome_indicator');
    }
}
