<?php

namespace App\Support;

use App\Models\BondOutputIndicator;
use App\Models\ImpactIndicator;
use App\Models\OutcomeIndicator;
use App\Models\OutputIndicator;
use App\Models\PillarProgramOutputIndicator;

class ResultChainIndicators
{
    /**
     * Map of Result Chain indicator model FQCN => human label.
     * These five models are the only source of truth for indicators.
     *
     * @var array<class-string, string>
     */
    public const TYPES = [
        OutputIndicator::class => 'Output',
        OutcomeIndicator::class => 'Outcome',
        ImpactIndicator::class => 'Impact',
        BondOutputIndicator::class => 'Bond Output',
        PillarProgramOutputIndicator::class => 'Program Output',
    ];

    /**
     * Flat option list across all Result Chain indicator types.
     *
     * Each row: ['type' => FQCN, 'type_label' => 'Impact', 'id' => 1,
     *            'code' => 'IMP-1', 'title' => '...', 'measurement_unit' => '%'|null,
     *            'department_id' => 3|null]
     *
     * `department_id` is the indicator's owning department — the same value
     * IndicatorReportService::canReport() authorizes against, so a client can
     * scope the reportable indicator list without guessing by name. It is null
     * for the types that carry no department column (Bond Output, Program
     * Output).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::TYPES as $class => $label) {
            $fillable = (new $class)->getFillable();
            $hasUnit = in_array('measurement_unit', $fillable, true);
            $hasDepartment = in_array('department_id', $fillable, true);

            $columns = ['id', 'code', 'title'];
            if ($hasUnit) {
                $columns[] = 'measurement_unit';
            }
            if ($hasDepartment) {
                $columns[] = 'department_id';
            }

            foreach ($class::orderBy('code')->get($columns) as $row) {
                $options[] = [
                    'type' => $class,
                    'type_label' => $label,
                    'id' => $row->id,
                    'code' => $row->code,
                    'title' => $row->title,
                    'measurement_unit' => $hasUnit ? $row->measurement_unit : null,
                    'department_id' => $hasDepartment ? $row->department_id : null,
                ];
            }
        }

        return $options;
    }
}
