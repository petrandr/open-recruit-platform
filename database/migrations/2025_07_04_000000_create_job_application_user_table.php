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
        Schema::create('job_application_user', function (Blueprint $table) {
            $table->id();
            // Foreign key to job_applications table
            $table->foreignId('job_application_id')
                  ->constrained('job_applications')
                  ->cascadeOnDelete();
            // Foreign key to users table
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('job_application_user');
    }
};