<?php

namespace App\Models;

use App\Helper\Slugger;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'indicator_tier_id',
        'measurement_unit',
        'baseline_value',
        'baseline_year',
        'collection_frequency',
        'disaggregation_dimensions',
        'reporting_frequency',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:2',
        'baseline_year'  => 'integer',
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
                $base = Slugger::slugify(
                    $model->title,
                    '_',
                    true,
                );
                $slug = $base;
                $i = 1;
                while (self::where('slug', $slug)->exists()) {
                    $slug = $base . '_' . $i++;
                }
                $model->slug = $slug;
            }
        });
    }

    public function indicatorTier()
    {
        return $this->belongsTo(IndicatorTier::class);
    }

    public function scopeWithDimension($query, $dimension)
    {
        return $query->where(function ($q) use ($dimension) {
            $q
                ->whereJsonContains('disaggregation_dimensions', $dimension)
                ->orWhereNotNull("disaggregation_dimensions->{$dimension}");
        });
    }

    public function scopeWithCategory($query, $dimension, $category)
    {
        return $query->whereJsonContains("disaggregation_dimensions->{$dimension}", $category);
    }

    public function baseline()
    {
        return $this->hasMany(IndicatorBaselineYear::class);
    }

    public function sectoralGoals()
    {
        return $this->belongsToMany(SectoralGoal::class, 'indicator_sectoral_goal');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_indicator')->withPivot('role');
    }

    public function mainDepartment()
    {
        return $this->belongsToMany(Department::class, 'department_indicator')
                    ->wherePivot('role', 'main');
    }

    public function supportingDepartments()
    {
        return $this->belongsToMany(Department::class, 'department_indicator')
                    ->wherePivot('role', 'supporting');
    }

    public function disagregation()
    {
        return $this->belongsToMany(
            DisagregationItem::class,
            'indicator_disagregations',
            'indicator_id',
            'disagregation_item_id'
        );
    }

    public function linkedIndicators()
    {
        return $this->belongsToMany(
            Indicator::class,
            'indicator_links',
            'indicator_id',
            'linked_indicator_id'
        );
    }

    public function disagregationCategories()
    {
        return DisagregationCategory::whereHas('items', function ($q) {
            $q->whereHas('indicators', function ($q2) {
                $q2->where('indicators.id', $this->id);
            });
        });
    }

    public function bondDeliverables()
    {
        return $this->belongsToMany(BondDeliverable::class, 'bond_deliverable_indicator');
    }

    // Usage:
    // Indicator::withDimension('gender_of_household_head')->get();
    // Indicator::withCategory('livestock_production_system', 'pastoral')->get();
}
