<?php

use App\Models\Indicator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove all tierable entries that belonged to Indicator records,
     * since indicator classification is now handled solely by indicator_type.
     */
    public function up(): void
    {
        DB::table('tierables')
            ->where('tierable_type', Indicator::class)
            ->delete();
    }

    public function down(): void
    {
        // Re-attaching indicator tierables on rollback is not feasible
        // without the original data. Re-run TierableSeeder if needed.
    }
};
