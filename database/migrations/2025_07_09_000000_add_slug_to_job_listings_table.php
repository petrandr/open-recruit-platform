<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_listings', function (Blueprint $table) {
            // Add slug column for URL-friendly identifiers
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Populate slug for existing job listings
        $jobs = DB::table('job_listings')->select('id', 'title')->get();
        foreach ($jobs as $job) {
            $baseSlug = Str::slug($job->title);
            $slug = $baseSlug;
            $counter = 1;
            // Ensure slug is unique
            while (DB::table('job_listings')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            DB::table('job_listings')
                ->where('id', $job->id)
                ->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};