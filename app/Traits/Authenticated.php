<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

trait Authenticated
{
    /**
     * Set user session data.
     *
     * @param Request $request
     * @param User $user
     * @return void
     */
    protected function setUserSession(Request $request, User $user): void
    {
        $request->session()->put('USER_ID', $user->id);
        $request->session()->put('USER_NAME', $user->name);
        $request->session()->put('LAST_LOGIN', Date::now());
    }

    protected function updateLastLogin(User $user): void
    {
//        $user->last_login = now();
//        $user->save();
    }

    protected function setSamlSession(Request $request): void
    {
        $request->session()->put('SAML_AUTHENTICATED', true);
    }
}
