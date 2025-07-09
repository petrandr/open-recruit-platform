<?php
declare(strict_types=1);
namespace App\Orchid\Screens\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Support\Facades\Toast;

/**
 * Trait to cancel pending jobs stored in the Laravel jobs table.
 */
trait CancelsPendingJobs
{
    /**
     * Cancel a queued notification job by ID.
     *
     * @param Request $request
     */
    public function cancelJob(Request $request): void
    {
        $id = $request->get('id');
        // Delete the job from the queue table
        DB::table('jobs')->where('id', $id)->delete();
        Toast::info(__('Job #:id cancelled.', ['id' => $id]));
    }
}