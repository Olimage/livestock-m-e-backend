<?php

namespace App\Http\Controllers;

use App\Models\EnumerationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncEnumerationRecordJob;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class EnumerationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = EnumerationRecord::query()->with('enumerator');

        if ($request->filled('form_type')) {
            $query->where('form_type', $request->string('form_type'));
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->string('sync_status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('enumerator_name', 'like', "%{$search}%")
                  ->orWhere('form_type', 'like', "%{$search}%");
            });
        }

        $records = $query->orderByDesc('id')->paginate($request->integer('per_page', 15))->withQueryString();

        return Inertia::render('Enumeration/Index', [
            'records' => $records,
            'filters' => [
                'form_type' => $request->get('form_type'),
                'sync_status' => $request->get('sync_status'),
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page')
            ],
            'formTypes' => EnumerationRecord::FORM_TYPES,
            'syncStatuses' => [
                EnumerationRecord::SYNC_PENDING,
                EnumerationRecord::SYNC_SYNCED,
                EnumerationRecord::SYNC_FAILED
            ]
        ]);
    }

    public function create(string $formType): Response
    {
        abort_unless(in_array($formType, EnumerationRecord::FORM_TYPES, true), 404);
        $component = match($formType) {
            'household' => 'Enumeration/CreateHousehold',
            'market' => 'Enumeration/CreateMarket',
            'commercial_farm' => 'Enumeration/CreateCommercialFarm',
            default => 'Enumeration/Create'
        };
        return Inertia::render($component, [
            'formType' => $formType,
            'formTypes' => EnumerationRecord::FORM_TYPES
        ]);
    }

    public function store(Request $request, string $formType)
    {
        abort_unless(in_array($formType, EnumerationRecord::FORM_TYPES, true), 404);

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'device_id' => ['nullable', 'string', 'max:100'],
            'payload' => ['required', 'array'],
        ]);

        $user = Auth::user();

        $record = EnumerationRecord::create([
            'user_id' => $user->id,
            'enumerator_name' => $user->full_name ?? 'Unknown',
            'form_type' => $formType,
            'sync_status' => EnumerationRecord::SYNC_PENDING,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            // Remove surveyDate from payload if present
            'payload' => collect($validated['payload'])->except(['surveyDate'])->all(),
            'submitted_at' => now(),
        ]);

        // Dispatch async sync job
        SyncEnumerationRecordJob::dispatch($record);

        return redirect()->route('enumerations.index')->with('success', 'Enumeration record saved.');
    }

    public function show(EnumerationRecord $enumerationRecord): Response
    {
        return Inertia::render('Enumeration/Show', [
            'record' => $enumerationRecord->load('enumerator')
        ]);
    }

    public function updateSyncStatus(Request $request, EnumerationRecord $enumerationRecord)
    {
        $request->validate([
            'sync_status' => ['required', 'in:' . implode(',', [
                EnumerationRecord::SYNC_PENDING,
                EnumerationRecord::SYNC_SYNCED,
                EnumerationRecord::SYNC_FAILED,
            ])]
        ]);

        $enumerationRecord->update([
            'sync_status' => $request->string('sync_status')
        ]);

        return back()->with('success', 'Sync status updated');
    }

    public function destroy(EnumerationRecord $enumerationRecord)
    {
        $enumerationRecord->delete();
        return back()->with('success', 'Enumeration record deleted');
    }

    public function export(Request $request, string $format = 'csv')
    {
        $query = EnumerationRecord::query();
        if ($request->filled('form_type')) {
            $query->where('form_type', $request->string('form_type'));
        }
        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->string('sync_status'));
        }
        if ($request->filled('search')) {
            $s = $request->string('search');
            $query->where(function ($q) use ($s) {
                $q->where('enumerator_name', 'like', "%{$s}%")
                  ->orWhere('form_type', 'like', "%{$s}%");
            });
        }
        $records = $query->orderByDesc('id')->get();

        if ($format === 'json') {
            return response()->json($records->map(function ($r) {
                return [
                    'id' => $r->id,
                    'form_type' => $r->form_type,
                    'enumerator' => $r->enumerator_name,
                    'sync_status' => $r->sync_status,
                    'submitted_at' => $r->submitted_at,
                    'latitude' => $r->latitude,
                    'longitude' => $r->longitude,
                    'payload' => $r->payload,
                ];
            }));
        }

        $response = new StreamedResponse(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Form Type','Enumerator','Sync Status','Submitted At','Latitude','Longitude']);
            foreach ($records as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->form_type,
                    $r->enumerator_name,
                    $r->sync_status,
                    $r->submitted_at,
                    $r->latitude,
                    $r->longitude,
                ]);
            }
            fclose($out);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="enumerations_export.csv"');
        return $response;
    }
}
