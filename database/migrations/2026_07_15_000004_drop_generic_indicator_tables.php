<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the generic Indicator + IndicatorTier subsystem tables.
     *
     * Result Chain typed indicators are now the only source of truth.
     * Pivots are dropped before their parent tables so foreign keys don't block.
     */
    public function up(): void
    {
        Schema::dropIfExists('department_indicator');
        Schema::dropIfExists('indicator_disagregations');
        Schema::dropIfExists('indicator_sectoral_goal');
        Schema::dropIfExists('indicator_links');
        Schema::dropIfExists('bond_deliverable_indicator');
        Schema::dropIfExists('indicators');
        Schema::dropIfExists('indicator_tiers');
    }

    /**
     * Irreversible: the generic Indicator + IndicatorTier subsystems were removed.
     */
    public function down(): void
    {
        // No-op.
    }
};
