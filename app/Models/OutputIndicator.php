<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutputIndicator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'title', 'description', 'department_id', 'measurement_unit'];

    protected static function booted(): void
    {
        static::created(function (self $model) {
            $model->updateQuietly(['code' => 'OPT-' . $model->id]);
        });
    }

    public function mainDepartment()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function supportingDepartments()
    {
        return $this->belongsToMany(Department::class, 'output_indicator_supporting_department', 'output_indicator_id', 'department_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_output_indicator');
    }

    public function disagregationItems()
    {
        return $this->belongsToMany(DisagregationItem::class, 'output_indicator_disaggregation', 'output_indicator_id', 'disagregation_item_id');
    }

    public function outcomeIndicators()
    {
        return $this->belongsToMany(OutcomeIndicator::class, 'output_indicator_outcome_indicator');
    }
}
