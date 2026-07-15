<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The permission model changes from polymorphic per-owner rows to a flat
        // catalog; legacy rows (and any direct grants referencing them) are cleared
        // so the new unique(permission) index applies cleanly. The catalog is
        // rebuilt by PermissionSeeder.
        if (Schema::hasTable('user_permissions')) {
            DB::table('user_permissions')->delete();
        }
        DB::table('permissions')->delete();

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'callable_type')) {
                $table->dropColumn(['callable_type', 'callable_id']);
            }
            $table->string('label')->nullable()->after('permission');
            $table->foreignId('module_id')->nullable()->after('label')->constrained()->nullOnDelete();
            $table->unique('permission');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['permission']);
            $table->dropConstrainedForeignId('module_id');
            $table->dropColumn('label');
            $table->string('callable_type')->nullable();
            $table->unsignedBigInteger('callable_id')->nullable();
        });
    }
};
