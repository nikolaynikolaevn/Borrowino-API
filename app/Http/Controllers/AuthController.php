<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'password' => 'required|confirmed', // This means that there needs to be a field called password_confirmation
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg,webp|max:10240' // 'images.*' because there can be multiple imagesMax 10mB
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);

        $user = User::create($validatedData);

        if (array_key_exists('images', $validatedData)) {
            $user->images = true;
            $user->save();

            (new ImageController)->uploadImages($validatedData['images'], $user->id, 'profile_image');
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        $user->refresh(); // This is to include the default values that are not changed on creation
        return response()->json(['user' => $user, 'access_token' => $accessToken]);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required',
        ]);

        if (!auth()->attempt($loginData)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response()->json(['user' => auth()->user(), 'access_token' => $accessToken]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return response()->json(null,204);
    }


}
