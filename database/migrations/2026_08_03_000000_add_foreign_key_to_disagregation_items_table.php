<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `disagregation_items.disagregation_category_id` was declared via
     * `$table->unsignedBigInteger(...)->constrained()->onDelete('cascade')`
     * in 2026_02_23_110450_create_disagregation_items_table.php. `constrained()`
     * is only meaningful on the ForeignIdColumnDefinition returned by
     * `foreignId()`; called on a plain unsignedBigInteger it is absorbed by
     * Fluent's magic __call and silently does nothing — no FK was ever created.
     * This migration adds the missing constraint for real.
     */
    public function up(): void
    {
        // A foreign key cannot be added while violating rows exist, so clear
        // orphans first: items pointing at a disagregation_category_id that
        // no longer (or never did) exist in disagregation_categories.
        $orphanIds = DB::table('disagregation_items')
            ->whereNotIn('disagregation_category_id', function ($query) {
                $query->select('id')->from('disagregation_categories');
            })
            ->pluck('id');

        if ($orphanIds->isNotEmpty()) {
            DB::table('disagregation_items')->whereIn('id', $orphanIds)->delete();

            $message = "Removed {$orphanIds->count()} orphaned disagregation_items row(s) "
                .'with no matching disagregation_categories.id: ['.$orphanIds->implode(', ').']';

            echo $message.PHP_EOL;
            Log::warning($message);
        }

        // SQLite does not support ALTER TABLE ... ADD CONSTRAINT for foreign
        // keys on an already-created table (foreign keys can only be declared
        // at CREATE TABLE time). The test suite runs on SQLite `:memory:`
        // (see phpunit.xml), so skip the constraint there and keep the
        // pre-existing (unconstrained) behaviour under that driver.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('disagregation_items', function (Blueprint $table) {
            $table->foreign('disagregation_category_id')
                ->references('id')->on('disagregation_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('disagregation_items', function (Blueprint $table) {
            $table->dropForeign(['disagregation_category_id']);
        });
    }
};
