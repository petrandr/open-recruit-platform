<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Add separate city and country columns
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('city')->nullable()->after('location');
            $table->string('country')->nullable()->after('city');
        });

        // Backfill data from existing location column
        DB::table('job_applications')->get()->each(function ($row) {
            if (!empty($row->location)) {
                $parts = explode(',', $row->location);
                $city = trim($parts[0]);
                $country = trim($parts[count($parts) - 1]);
                DB::table('job_applications')
                    ->where('id', $row->id)
                    ->update([
                        'city'    => $city,
                        'country' => $country,
                    ]);
            }
        });

        // Drop old location column
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Restore location as nullable
            $table->string('location')->nullable()->after('cv_disk');
            $table->dropColumn(['city', 'country']);
        });
    }
};