<?php
// app/Http/Controllers/ApplicationDetailController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\JobApplication;
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
        return view('partials.application-details', [
            'application' => $application,
        ]);
    }
}