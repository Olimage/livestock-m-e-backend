<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpactIndicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'title', 'description', 'department_id', 'measurement_unit'];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            $model->updateQuietly(['code' => 'IMP-' . $model->id]);
        });
    }

    public function mainDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function supportingDepartments()
    {
        return $this->belongsToMany(Department::class, 'impact_indicator_supporting_department', 'impact_indicator_id', 'department_id');
    }

    public function outcomeIndicators()
    {
        return $this->belongsToMany(OutcomeIndicator::class, 'outcome_indicator_impact_indicator');
    }

    public function disagregationItems()
    {
        return $this->belongsToMany(DisagregationItem::class, 'impact_indicator_disaggregation', 'impact_indicator_id', 'disagregation_item_id');
    }

    public function presidentialPriorities()
    {
        return $this->belongsToMany(PresidentialPriority::class, 'impact_indicator_presidential_priority');
    }
}
