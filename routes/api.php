<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JobListingController;
use App\Http\Controllers\Api\ApplicationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the ApplicationBuilder routing configuration
| with the "api" middleware group and "api" prefix.
|
*/
// List all active jobs
Route::get('/jobs', [JobListingController::class, 'index']);
// Fetch job info (active, inactive)
Route::get('/jobs/{job}', [JobListingController::class, 'show']);
// Create a new application
Route::post('/applications', [ApplicationController::class, 'store']);