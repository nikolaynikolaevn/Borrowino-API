<?php

namespace App\Services;

use App\Image;
use App\User;
use Laravel\Socialite\Contracts\User as ProviderUser;

class FacebookAccountService
{
    public function createOrGetUser(ProviderUser $providerUser)
    {
        $user = User::whereProvider('facebook')
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if (!$user) {

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $user = User::create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'password' => bcrypt(rand(1, 10000)),
                    'provider_user_id' => $providerUser->getId(),
                    'provider' => 'facebook',
                ]);

                $user->refresh(); // This is to include the default values that are not changed on creation

                Image::create([
                    'resource_id' => $user->id,
                    'path_to_image' => $providerUser->getAvatar(),
                    'type' => 'profile_image',
                ]);
            }
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json(['user' => $user, 'access_token' => $accessToken]);
    }
}
