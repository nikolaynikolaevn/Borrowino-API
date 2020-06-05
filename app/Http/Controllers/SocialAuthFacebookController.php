<?php

namespace App\Http\Controllers;

use App\Services\FacebookAccountService;
use Socialite;

class SocialAuthFacebookController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleProviderCallback(FacebookAccountService $service)
    {
//        auth()->login($user);

//        $token = $user->token;
//        $refreshToken = $user->refreshToken; // not always provided
//        $expiresIn = $user->expiresIn;

        return $service->createOrGetUser(Socialite::driver('facebook')->stateless()->user());
    }
}
