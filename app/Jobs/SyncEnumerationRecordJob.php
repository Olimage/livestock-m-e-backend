<?php

namespace App\Jobs;

use App\Models\EnumerationRecord;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEnumerationRecordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public EnumerationRecord $record)
    {
        $this->onQueue('sync');
    }

    public function handle(): void
    {
        // Increment attempts
        $this->record->increment('sync_attempts');

        try {
            // Simulate external push (replace with real integration)
            // For example: Http::post(config('services.enumerations.endpoint'), $this->record->payload);
            if (random_int(1, 10) === 1) { // Simulate occasional failure
                throw new Exception('Mock upstream error');
            }
            $this->record->markSynced();
        } catch (Exception $e) {
            if ($this->record->sync_attempts >= $this->tries) {
                $this->record->markFailed($e->getMessage());
            } else {
                throw $e; // Re-queue for retry
            }
        }
    }
}
