<?php

use App\Http\Controllers\SamlController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (config('platform.saml_auth')) {
    Route::get('/saml/login', [SamlController::class, 'login'])
        ->name('saml.login')
        ->middleware('guest');

    Route::post('/saml/acs', [SamlController::class, 'acs'])
        ->name('saml.acs')
        ->middleware('guest')
        ->withoutMiddleware(VerifyCsrfToken::class);

    Route::get('/saml/metadata', [SamlController::class, 'metadata'])
        ->name('saml.metadata')
        ->middleware('guest');
}
