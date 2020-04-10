<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed', // This means that there needs to be a field called password_confirmation
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);

        $user = User::create($validatedData);

        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user' => $user, 'access_token' => $accessToken]);
    }
}
