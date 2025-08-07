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
        Schema::table('job_listings', function (Blueprint $table) {
            $table->unsignedBigInteger('application_received_template_id')
                  ->nullable()
                  ->after('industry_id');
            $table->foreign('application_received_template_id')
                  ->references('id')
                  ->on('notification_templates')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['application_received_template_id']);
            $table->dropColumn('application_received_template_id');
        });
    }
};