<?php

namespace App\Models;

use App\Helper\Slugger;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Indicator extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'title',
        'slug',
        'description',
        'indicator_type',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'target_value',
        'target_year',
        'data_source',
        'collection_frequency',
        'disaggregation_dimensions',
        'reporting_frequency',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'baseline_year' => 'integer',
        'target_year' => 'integer',
        'collection_frequency' => 'array',
        'reporting_frequency' => 'array',
        'disaggregation_dimensions' => 'json',
    ];

 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

              if (empty($model->slug) && !empty($model->title)) {

                $base = Slugger::slugify($model->title, '_', true, );
                $slug = $base;
                $i = 1;
                while (self::where('slug', $slug)->exists()) {
                    $slug = $base . '_' . $i++;
                }
                $model->slug = $slug;
            }

        });
    }

    public function tiers(): MorphToMany
    {
        return $this->morphToMany(Tier::class, 'tierable');
    }


    public function scopeWithDimension($query, $dimension)
{
    return $query->where(function($q) use ($dimension) {
        $q->whereJsonContains('disaggregation_dimensions', $dimension)
          ->orWhereNotNull("disaggregation_dimensions->{$dimension}");
    });
}

public function scopeWithCategory($query, $dimension, $category)
{
    return $query->whereJsonContains("disaggregation_dimensions->{$dimension}", $category);
}

// Usage:
// Indicator::withDimension('gender_of_household_head')->get();
// Indicator::withCategory('livestock_production_system', 'pastoral')->get();
}
