<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnumerationRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnumerationRecordController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'enumerator_name' => ['nullable','string','max:255'],
            'form_type' => ['required','string', Rule::in(EnumerationRecord::FORM_TYPES)],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'device_id' => ['nullable','string','max:255'],
            'form_data' => ['required','array'],
            'submitted_at' => ['nullable','date'],
        ]);

        $record = new EnumerationRecord();
        $record->fill([
            'user_id' => $user?->id,
            'enumerator_name' =>  $user?->full_name ,
            'form_type' => $validated['form_type'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            'payload' => $validated['form_data'],
            'submitted_at' => $validated['submitted_at'] ?? now(),
            'sync_status' => EnumerationRecord::SYNC_SYNCED,
        ]);

        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Enumeration record received',
            'data' => [
                'id' => $record->id,
                'form_type' => $record->form_type,
                'sync_status' => $record->sync_status,
            ],
        ], 201);
    }
}
