<?php

namespace App\Http\Controllers;

use App\Offer;
use App\OfferRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::select(['id','name','email','images','created_at'])->paginate(15);
        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        if (Auth::guard('api')->user() && Auth::guard('api')->user()->id === $user->id)
            return response()->json($user->only(['id', 'name', 'email', 'images', 'created_at']), 200);
        else
            return response()->json($user->only(['id', 'name', 'images', 'created_at']), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|max:55',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|confirmed', // This means that there needs to be a field called password_confirmation
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg,webp|max:10240' // 'images.*' because there can be multiple imagesMax 10mB
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);

        if (Auth::guard('api')->user()->id === $user->id) {
            $user->update($validatedData);

            if (array_key_exists('images', $validatedData)) {
                (new ImageController)->deleteImages($user->id, 'profile_image');
                $user->images = true;
                $user->save();

                (new ImageController)->uploadImages($validatedData['images'], $user->id, 'profile_image');
            }

            return response()->json($user, 200);
        }
        return response()->json(['Message'=>'Unauthorized'],401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        if (Auth::guard('api')->user()->id === $user->id) {
            (new ImageController)->deleteImages($user->id, 'profile_image');
            $user->delete();
            return response()->json(null, 204);
        }
        return response()->json(['Message'=>'Unauthorized'],401);
    }

    /**
     * @param \App\User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function images(User $user)
    {
        $fileNames = (new ImageController)->fetchImages($user->id, 'profile_image');
        if ($fileNames == null) {
            return response()->json(['Message' => 'No images found'], 404);
        }
        return response()->json(['images' => $fileNames], 200);
    }

    public function getUserOffers(User $user)
    {
        return response()->json(Offer::where('owner', $user->id)->where('active', '1')->latest()->paginate(15));
    }

    public function getReceivedOfferRequests()
    {
        $user = Auth::guard('api')->user();
        return response()->json($user->received_requests()->where('active', '1')->latest()->paginate(15));
    }
}
