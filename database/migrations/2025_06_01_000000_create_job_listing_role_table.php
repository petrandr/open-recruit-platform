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
        Schema::create('job_listing_role', function (Blueprint $table) {
            $table->unsignedBigInteger('job_listing_id');
            $table->unsignedInteger('role_id');
            $table->primary(['job_listing_id', 'role_id']);
            $table->foreign('job_listing_id')
                ->references('id')
                ->on('job_listings')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listing_role');
    }
};