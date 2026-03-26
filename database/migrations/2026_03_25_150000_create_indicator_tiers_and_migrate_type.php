<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create indicator_tiers table
        Schema::create('indicator_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('prefix', 20);
            $table->timestamps();
        });

        // 2. Seed the three standard tiers inline so the data migration can run
        $now = now();
        DB::table('indicator_tiers')->insert([
            ['name' => 'Output',  'prefix' => 'OUT', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Outcome', 'prefix' => 'OC',  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Impact',  'prefix' => 'IMP', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Add indicator_tier_id to indicators (nullable for the data migration step)
        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('indicator_tier_id')
                ->nullable()
                ->after('description')
                ->constrained('indicator_tiers')
                ->nullOnDelete();
        });

        // 4. Migrate existing indicator_type values to indicator_tier_id
        $tiers = DB::table('indicator_tiers')->get()->keyBy(fn($t) => strtolower($t->name));

        foreach ($tiers as $slug => $tier) {
            DB::table('indicators')
                ->where('indicator_type', $slug)
                ->update(['indicator_tier_id' => $tier->id]);
        }

        // Assign any unmatched rows to 'Output' as a safe default
        $outputId = $tiers['output']->id ?? null;
        if ($outputId) {
            DB::table('indicators')
                ->whereNull('indicator_tier_id')
                ->update(['indicator_tier_id' => $outputId]);
        }

        // 5. Make indicator_tier_id not nullable now that all rows have a value
        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('indicator_tier_id')->nullable(false)->change();
        });

        // 6. Drop the old enum column
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('indicator_type');
        });
    }

    public function down(): void
    {
        // Re-add indicator_type enum
        Schema::table('indicators', function (Blueprint $table) {
            $table->enum('indicator_type', ['outcome', 'output', 'impact'])->default('output')->after('description');
        });

        // Restore indicator_type values from the tier name
        $tiers = DB::table('indicator_tiers')->get();
        foreach ($tiers as $tier) {
            DB::table('indicators')
                ->where('indicator_tier_id', $tier->id)
                ->update(['indicator_type' => strtolower($tier->name)]);
        }

        // Drop indicator_tier_id
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('indicator_tier_id');
        });

        Schema::dropIfExists('indicator_tiers');
    }
};
