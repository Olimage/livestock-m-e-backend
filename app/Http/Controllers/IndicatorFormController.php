<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\IndicatorForm;
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

    public function getIndicatorFormFields(Request $request)
    {
        $query = Indicator::query();
        if ($request->has('type')) {
            $type = $request->get('type');
            $query->where('indicator_type', $type);
        }

        $indicators = $query->get(['slug', 'title', 'measurement_unit', 'code', 'indicator_type'])->map(function ($indicator) {
            return [
                'slug' => $indicator->slug,
                'title' => $indicator->title,
                'measurement_unit' => $indicator->measurement_unit,
                'code' => $indicator->code,
                'type' => $indicator->indicator_type,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $indicators,
        ]);
    }

    /**
     * Store a newly created indicator form submission.
     *
     * Expected payload:
     * {
     *   "volume_of_livestock_products_exported_live_animals_meat_dairy_hides_skins": 1,
     *   "number_of_jobs_created": 5
     * }
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

            $slugs = array_keys($formData);
            $indicators = Indicator::whereIn('slug', $slugs)->get()->keyBy('slug');
            $foundSlugs = $indicators->keys()->toArray();

            $invalidSlugs = array_diff($slugs, $foundSlugs);
            if (!empty($invalidSlugs)) {
                throw ValidationException::withMessages([
                    'indicators' => ['Invalid indicator slug(s): ' . implode(', ', $invalidSlugs)],
                ]);
            }

            $rules = [];
            $messages = [];
            $attributes = [];

            foreach ($slugs as $slug) {
                $indicator = $indicators->get($slug);

                $rules[$slug] = 'nullable';


                /*
                 * $numericUnits = ['kg', 'g', 'ml', 'l', 'count', 'number', 'percentage', '%'];
                 * if (in_array(strtolower($indicator->measurement_unit), $numericUnits)) {
                 *     $rules[$slug] = 'nullable|numeric';
                 *     $messages[$slug . '.numeric'] = 'The :attribute must be a numeric value.';
                 * }
                 */

                $attributes[$slug] = str_replace('_', ' ', $slug);
            }

            $validated = $request->validate($rules, $messages, $attributes);

            $validatedData = collect($validated)->reject(function ($value) {
                return is_null($value) || $value === '';
            })->toArray();

            if (empty($validatedData)) {
                throw ValidationException::withMessages([
                    'form' => ['At least one valid indicator value is required.'],
                ]);
            }

            $submittedAt = now();
            $createdForms = [];

            foreach ($validatedData as $slug => $value) {
                $form = IndicatorForm::create([
                    'user_id' => auth()->id(),
                    'indicator_slug' => $slug,
                    'value' => $value,
                    'submitted_at' => $submittedAt,
                ]);

                $createdForms[] = $form;
            }

            $enrichedData = [];
            foreach ($createdForms as $form) {
                $indicator = $indicators->get($form->indicator_slug);
                $enrichedData[$form->indicator_slug] = [
                    'id' => $form->id,
                    'value' => $form->value,
                    'unit' => $indicator->measurement_unit,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->title,
                    'indicator_slug' => $indicator->slug,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Indicator form submitted successfully.',
                'data' => [
                    'submitted_at' => $submittedAt,
                    'count' => count($createdForms),
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
     * Display the specified indicator form.
     */
    public function show(IndicatorForm $indicatorForm)
    {
        // Enrich the form data with indicator details
        $indicators = Indicator::whereIn('slug', array_keys($indicatorForm->data))->get();

        $enrichedData = [];
        foreach ($indicatorForm->data as $slug => $value) {
            $indicator = $indicators->firstWhere('slug', $slug);
            if ($indicator) {
                $enrichedData[$slug] = [
                    'value' => $value,
                    'unit' => $indicator->measurement_unit,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->title,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $indicatorForm->id,
                'user_id' => $indicatorForm->user_id,
                'submitted_at' => $indicatorForm->submitted_at,
                'created_at' => $indicatorForm->created_at,
                'updated_at' => $indicatorForm->updated_at,
                'values' => $enrichedData,
            ],
        ]);
    }

    /**
     * Update the specified indicator form.
     */
    public function update(Request $request, IndicatorForm $indicatorForm)
    {
        $submitted = $request->all();

        // Remove pagination and standard request parameters
        $excludedKeys = ['_token', '_method', 'per_page', 'page'];
        $formData = collect($submitted)->reject(function ($value, $key) use ($excludedKeys) {
            return in_array($key, $excludedKeys);
        })->toArray();

        if (empty($formData)) {
            throw ValidationException::withMessages([
                'form' => 'At least one indicator value is required.',
            ]);
        }

        // Validate all keys are valid indicator slugs
        $slugs = array_keys($formData);
        $indicators = Indicator::whereIn('slug', $slugs)->get();
        $foundSlugs = $indicators->pluck('slug')->toArray();

        $invalidSlugs = array_diff($slugs, $foundSlugs);
        if (!empty($invalidSlugs)) {
            throw ValidationException::withMessages([
                'indicators' => 'Invalid indicator slugs: ' . implode(', ', $invalidSlugs),
            ]);
        }

        // Validate values
        $errors = [];
        foreach ($formData as $slug => $value) {
            $indicator = $indicators->firstWhere('slug', $slug);

            if (is_null($value) || $value === '') {
                unset($formData[$slug]);
                continue;
            }

            if (!is_numeric($value)) {
                $errors[$slug] = "Value must be numeric (Measurement unit: {$indicator->measurement_unit})";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        // Update the form
        $indicatorForm->update([
            'data' => $formData,
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
