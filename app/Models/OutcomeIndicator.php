<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutcomeIndicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'title', 'description', 'department_id', 'measurement_unit'];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            $model->updateQuietly(['code' => 'OUT-' . $model->id]);
        });
    }

    public function mainDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function supportingDepartments()
    {
        return $this->belongsToMany(Department::class, 'outcome_indicator_supporting_department', 'outcome_indicator_id', 'department_id');
    }

    public function outputIndicators()
    {
        return $this->belongsToMany(OutputIndicator::class, 'output_indicator_outcome_indicator');
    }

    public function disagregationItems()
    {
        return $this->belongsToMany(DisagregationItem::class, 'outcome_indicator_disaggregation', 'outcome_indicator_id', 'disagregation_item_id');
    }

    public function impactIndicators()
    {
        return $this->belongsToMany(ImpactIndicator::class, 'outcome_indicator_impact_indicator');
    }

    public function sectoralGoals()
    {
        return $this->belongsToMany(SectoralGoal::class, 'outcome_indicator_sectoral_goal');
    }

    public function bondOutputIndicators()
    {
        return $this->belongsToMany(BondOutputIndicator::class, 'bond_output_indicator_outcome_indicator');
    }

    public function pillarProgramOutputIndicators()
    {
        return $this->belongsToMany(PillarProgramOutputIndicator::class, 'pillar_program_output_indicator_outcome_indicator');
    }
}
