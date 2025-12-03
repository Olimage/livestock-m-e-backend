<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnumerationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enumerator_name',
        'form_type',
        'sync_status',
        'latitude',
        'longitude',
        'device_id',
        'payload',
        'submitted_at',
        'zone_id',
        'state_id',
        'lga_id'
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7'
    ];

    public const FORM_TYPES = [
        'household',
        'market',
        'commercial_farm'
    ];

    public const SYNC_PENDING = 'pending';
    public const SYNC_SYNCED = 'synced';
    public const SYNC_FAILED = 'failed';

    public function enumerator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeFormType($query, string $type)
    {
        return $query->where('form_type', $type);
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', self::SYNC_PENDING);
    }

    public function markSynced(): void
    {
        $this->fill([
            'sync_status' => self::SYNC_SYNCED,
            'last_sync_at' => now(),
            'sync_error' => null,
        ])->save();
    }

    public function markFailed(string $error): void
    {
        $this->fill([
            'sync_status' => self::SYNC_FAILED,
            'last_sync_at' => now(),
            'sync_error' => $error,
        ])->save();
    }

    public function zone(): BelongsTo{
        return $this->belongsTo(Zone::class);
    }

    public function state(): BelongsTo{
        return $this->belongsTo(State::class);
    }

    public function lga(): BelongsTo{
        return $this->belongsTo(Lga::class);
    }
}
