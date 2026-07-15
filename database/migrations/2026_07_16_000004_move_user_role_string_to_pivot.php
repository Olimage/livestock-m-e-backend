<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        $rolesBySlug = Role::pluck('id', 'slug');

        DB::table('users')->whereNotNull('role')->orderBy('id')->each(function ($u) use ($rolesBySlug) {
            $roleId = $rolesBySlug[$u->role] ?? null;
            if ($roleId && ! DB::table('user_role')->where(['user_id' => $u->id, 'role_id' => $roleId])->exists()) {
                DB::table('user_role')->insert([
                    'user_id' => $u->id,
                    'role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('role')->default('user'));
    }
};
