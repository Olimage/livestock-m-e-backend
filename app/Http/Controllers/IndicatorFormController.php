<?php

namespace App\Http\Controllers;

use App\Models\IndicatorForm;
use App\Support\ResultChainIndicators;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IndicatorFormController extends Controller
{
    /**
     * Display a listing of all indicator forms.
     */
    public function index(Request $request)
    {
        $forms = IndicatorForm::with('user')
            ->latest('submitted_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $forms,
        ]);
    }

    /**
     * Return the collectable indicator fields, unioned across the Result Chain
     * indicator types and keyed by each indicator's globally-unique code.
     */
    public function getIndicatorFormFields(Request $request)
    {
        $type = $request->get('type'); // optional: matches a type_label (e.g. "Impact")

        $data = collect(ResultChainIndicators::options())
            ->when($type, fn ($c) => $c->filter(fn ($i) => strcasecmp($i['type_label'], $type) === 0))
            ->map(fn ($i) => [
                'title' => $i['title'],
                'code' => $i['code'],
                'measurement_unit' => $i['measurement_unit'],
                'type' => $i['type_label'],
                'type_prefix' => explode('-', (string) $i['code'])[0],
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store indicator value submissions.
     *
     * Expected payload keys are Result Chain indicator codes, e.g.:
     * { "IMP-3": 12, "OUT-1": 5 }
     */
    public function store(Request $request)
    {
        try {
            $excludedKeys = ['_token', 'per_page', 'page', '_method'];
            $formData = collect($request->all())->reject(function ($value, $key) use ($excludedKeys) {
                return in_array($key, $excludedKeys);
            })->toArray();

            if (empty($formData)) {
                throw ValidationException::withMessages([
                    'form' => ['At least one indicator value is required.'],
                ]);
            }

            $byCode = collect(ResultChainIndicators::options())->keyBy('code');

            $codes = array_keys($formData);
            $invalidCodes = array_diff($codes, $byCode->keys()->all());
            if (! empty($invalidCodes)) {
                throw ValidationException::withMessages([
                    'indicators' => ['Invalid indicator code(s): '.implode(', ', $invalidCodes)],
                ]);
            }

            $validatedData = collect($formData)->reject(function ($value) {
                return is_null($value) || $value === '';
            })->toArray();

            if (empty($validatedData)) {
                throw ValidationException::withMessages([
                    'form' => ['At least one valid indicator value is required.'],
                ]);
            }

            $submittedAt = now();
            $enrichedData = [];

            foreach ($validatedData as $code => $value) {
                $form = IndicatorForm::create([
                    'user_id' => auth()->id(),
                    'indicator_code' => $code,
                    'value' => $value,
                    'submitted_at' => $submittedAt,
                ]);

                $indicator = $byCode->get($code);
                $enrichedData[$code] = [
                    'id' => $form->id,
                    'value' => $form->value,
                    'unit' => $indicator['measurement_unit'],
                    'indicator_code' => $code,
                    'indicator_name' => $indicator['title'],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Indicator form submitted successfully.',
                'data' => [
                    'submitted_at' => $submittedAt,
                    'count' => count($enrichedData),
                    'values' => $enrichedData,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified indicator form submission, enriched with the
     * Result Chain indicator it references.
     */
    public function show(IndicatorForm $indicatorForm)
    {
        $indicator = collect(ResultChainIndicators::options())
            ->firstWhere('code', $indicatorForm->indicator_code);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $indicatorForm->id,
                'user_id' => $indicatorForm->user_id,
                'submitted_at' => $indicatorForm->submitted_at,
                'created_at' => $indicatorForm->created_at,
                'updated_at' => $indicatorForm->updated_at,
                'value' => $indicatorForm->value,
                'indicator_code' => $indicatorForm->indicator_code,
                'indicator_name' => $indicator['title'] ?? null,
                'unit' => $indicator['measurement_unit'] ?? null,
            ],
        ]);
    }

    /**
     * Update the value of the specified indicator form submission.
     */
    public function update(Request $request, IndicatorForm $indicatorForm)
    {
        $data = $request->validate([
            'value' => 'required',
        ]);

        $indicatorForm->update([
            'value' => $data['value'],
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Indicator form updated successfully.',
            'data' => [
                'id' => $indicatorForm->id,
                'submitted_at' => $indicatorForm->submitted_at,
            ],
        ]);
    }

    /**
     * Remove the specified indicator form from storage.
     */
    public function destroy(IndicatorForm $indicatorForm)
    {
        $indicatorForm->delete();

        return response()->json([
            'success' => true,
            'message' => 'Indicator form deleted successfully.',
        ]);
    }
}
