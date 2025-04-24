<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Authenticated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use OneLogin\Saml2\Auth as SamlAuth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\ValidationError;

/**
 * Class SAMLController
 *
 * This controller handles SAML authentication actions such as
 * initiating the SAML login flow, handling the assertion consumer
 * service (ACS) response, returning metadata, and managing logout.
 */
class SamlController extends Controller
{

    use Authenticated;

    /**
     * Initiates the SAML login process by redirecting the user
     * to the Identity Provider (IdP) for authentication.
     *
     * @return void
     * @throws Error
     */
    public function login(): void
    {
        $auth = new SamlAuth(config('saml'));
        // Redirects the user to the IdP login page
        $auth->login();
    }

    /**
     * Handles the Assertion Consumer Service (ACS) response
     * from the Identity Provider. If successful, logs the user
     * into the application. If not successful or user not found,
     * returns an error response.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Error
     * @throws ValidationError
     */
    public function acs(Request $request): RedirectResponse
    {
        $auth = new SamlAuth(config('saml'));
        $auth->processResponse();

        if ($auth->getErrors()) {
            abort(403, 'SAML ACS Error: ' . implode(', ', $auth->getErrors()));
        }

        $attributes = $auth->getAttributes();

        // Ensure the 'email' attribute exists and has at least one value
        if (!isset($attributes['email']) || !isset($attributes['email'][0])) {
            abort(500, 'Email attribute not found in SAML response');
        }

        $email = $attributes['email'][0];

        $user = User::where('email', $email)->first();

        // If no user is found, return a response indicating invalid login.
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        Auth::login($user);

        $this->setUserSession($request, $user);
        $this->setSamlSession($request);
        $this->updateLastLogin($user);

        return redirect()->route('platform.main');
    }

    /**
     * Returns the Service Provider (SP) metadata XML so that the Identity Provider
     * can be configured to trust this SP.
     *
     * @return Response
     * @throws Error
     */
    public function metadata(): Response
    {
        $settings = new Settings(config('saml'), true);
        $metadata = $settings->getSPMetadata();

        return response($metadata, 200, ['Content-Type' => 'application/xml']);
    }

}
