<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set all roles to regular by default
        DB::table('roles')->update(['role_type' => 'regular']);
        // Promote roles with 'admin' in slug to superadmin
        DB::table('roles')
            ->where('slug', 'like', '%admin%')
            ->update(['role_type' => 'superadmin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all roles back to regular
        DB::table('roles')->update(['role_type' => 'regular']);
    }
};