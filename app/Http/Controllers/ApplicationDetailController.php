<?php
// app/Http/Controllers/ApplicationDetailController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ApplicationDetailController extends Controller
{
    /**
     * Show detailed application info including screening questions.
     */
    public function show(JobApplication $application)
    {
        // Eager load related data
        $application->load([
            'jobListing',
            'candidate',
            'answers.question',
            'jobListing.screeningQuestions',
        ]);
        // Return a concise summary for the
        return view('partials.application-summary', [
            'application' => $application,
        ]);
    }

    /**
     * Preview the CV in a modal.
     *
     * @param JobApplication $application
     * @return \Illuminate\Contracts\View\View
     */
    public function cv(JobApplication $application)
    {
        // Determine storage URL based on disk
        $disk = Storage::disk($application->cv_disk);
        if ($disk->exists($application->cv_path)) {
            try {
                // S3 temporary URL
                $url = $disk instanceof \Illuminate\Filesystem\AwsS3V3Adapter
                    ? $disk->temporaryUrl($application->cv_path, now()->addMinutes(5))
                    : $disk->url($application->cv_path);
            } catch (\Exception $e) {
                $url = $disk->url($application->cv_path);
            }
        } else {
            abort(404, 'CV not found');
        }
        return view('partials.application-cv', [
            'signedUrl'   => $url,
            'application' => $application,
        ]);
    }
}
