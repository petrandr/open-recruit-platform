<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 2. Drop the existing constraint
        DB::statement('ALTER TABLE job_applications DROP CONSTRAINT IF EXISTS job_applications_status_check');

        // 1. Update existing data
        DB::table('job_applications')
            ->where('status', 'under review')
            ->update(['status' => 'under_review']);
        DB::table('job_applications')
            ->where('status', 'accepted')
            ->update(['status' => 'hired']);

        // 4. Add new constraint
        DB::statement("ALTER TABLE job_applications ADD CONSTRAINT job_applications_status_check CHECK (status IN (
            'submitted',
            'under_review',
            'shortlisted',
            'interview_scheduled',
            'interviewed',
            'offer_sent',
            'hired',
            'rejected',
            'withdrawn'
        ))");
    }

    public function down()
    {
        // Revert the changes in reverse
        DB::table('job_applications')
            ->where('status', 'under_review')
            ->update(['status' => 'under review']);
        DB::table('job_applications')
            ->where('status', 'hired')
            ->update(['status' => 'accepted']);
        DB::table('job_applications')
            ->whereIn('status', [
                'shortlisted',
                'interview_scheduled',
                'interviewed',
                'offer_sent',
                'withdrawn',
            ])
            ->update(['status' => 'submitted']);

        DB::statement('ALTER TABLE job_applications DROP CONSTRAINT IF EXISTS job_applications_status_check');

        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('status', 255)->default('submitted')->change();
        });

        DB::statement("ALTER TABLE job_applications ADD CONSTRAINT job_applications_status_check CHECK (status IN (
            'submitted',
            'under review',
            'accepted',
            'rejected'
        ))");
    }
};
