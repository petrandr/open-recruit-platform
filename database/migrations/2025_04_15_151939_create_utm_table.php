<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_tracking', function (Blueprint $table) {
            $table->id();
            // Example: foreign key to the applications table, if applicable
            $table->unsignedBigInteger('application_id')->unique();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            // Additional fields
            $table->string('utm_id')->nullable();
            $table->string('utm_adgroup')->nullable();
            $table->string('utm_placement')->nullable();
            $table->string('utm_device')->nullable();
            $table->string('utm_creative')->nullable();
            $table->string('utm_referrer')->nullable();
            $table->string('utm_other')->nullable();
            // Optional: gclid and fbclid if you want to store advertising platform identifiers
            $table->string('gclid')->nullable();
            $table->string('fbclid')->nullable();
            $table->timestamps();

            // If you want to set up a foreign key relationship:
            $table->foreign('application_id')->references('id')->on('job_applications')->onDelete('cascade');

            $table->index('utm_source');
            $table->index('utm_campaign');
            $table->index(['utm_source', 'utm_campaign']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_tracking');
    }
};
