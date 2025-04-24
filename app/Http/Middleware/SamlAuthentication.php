<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class SamlAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('platform.saml_auth')) {
            return $next($request);
        }

        // Redirect if not logged in
        if (!Auth::check()) {
            // Store intended URL if needed
            session(['url.intended' => $request->fullUrl()]);

            return redirect()->route('saml.login');
        }

        // Optional: enforce login only via SAML
        if (!session()->get('SAML_AUTHENTICATED')) {
            Auth::logout();
            return redirect()->route('saml.login');
        }

        return $next($request);
    }
}
