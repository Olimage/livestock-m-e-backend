<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'indicator_type',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'target_value',
        'target_year',
        'data_source',
        'collection_frequency'
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable');
    }
}
