<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create interview records for all applications with status 'interview_scheduled'.
     * Only the minimum required attributes (application_id, timestamps) are set.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();
        $applicationIds = DB::table('job_applications')
            ->where('status', 'interview_scheduled')
            ->pluck('id');

        if ($applicationIds->isEmpty()) {
            return;
        }

        $records = $applicationIds->map(function ($id) use ($now) {
            return [
                'application_id' => $id,
                'round'          => 'screening',
                'mode'           => 'online',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        })->toArray();

        DB::table('interviews')->insert($records);
    }

    /**
     * Reverse the migrations.
     *
     * Deletes interviews created for applications that were 'interview_scheduled'.
     *
     * @return void
     */
    public function down()
    {
        DB::table('interviews')
            ->whereIn('application_id', function ($query) {
                $query->select('id')
                      ->from('job_applications')
                      ->where('status', 'interview_scheduled');
            })
            ->delete();
    }
};