<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the Presidential Priorities and Tiers feature tables.
     *
     * These modules were removed from the application. Pivot tables are
     * dropped first so their foreign keys don't block the parent drops.
     */
    public function up(): void
    {
        Schema::dropIfExists('impact_indicator_presidential_priority');
        Schema::dropIfExists('tierables');
        Schema::dropIfExists('presidential_priorities');
        Schema::dropIfExists('tiers');
    }

    /**
     * These features were intentionally removed; there is nothing to restore.
     */
    public function down(): void
    {
        // Irreversible: the Presidential Priorities and Tiers features were removed.
    }
};
