<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Drop existing foreign key to allow modifying column
        Schema::table('job_screening_questions', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });
        // Make job_id nullable and re-add foreign key with set null on delete
        Schema::table('job_screening_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('job_id')->nullable()->change();
            $table->foreign('job_id')
                  ->references('id')->on('job_listings')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Drop modified foreign key
        Schema::table('job_screening_questions', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
        });
        // Revert job_id to non-nullable and re-add original foreign key
        Schema::table('job_screening_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('job_id')->nullable(false)->change();
            $table->foreign('job_id')
                  ->references('id')->on('job_listings');
        });
    }
};