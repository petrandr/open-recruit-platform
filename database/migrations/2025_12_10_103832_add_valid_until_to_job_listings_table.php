<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->date('valid_until')->nullable()->after('date_opened');
        });
        
        // Update existing job listings with a default valid_until date (30 days from now)
        DB::table('job_listings')
            ->whereNull('valid_until')
            ->update(['valid_until' => now()->addDays(30)->toDateString()]);
            
        // Now make the column required
        Schema::table('job_listings', function (Blueprint $table) {
            $table->date('valid_until')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn('valid_until');
        });
    }
};
