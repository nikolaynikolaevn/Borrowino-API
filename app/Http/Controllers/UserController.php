<?php

namespace App\Http\Controllers;

use App\Offer;
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
    public function index()
    {
        $users = User::select(['id','name','created_at'])->paginate(15);
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
        return response()->json($user->only(['id', 'name', 'created_at']), 200);
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
        ]);

        if (Auth::user()->id === $user->id) {
            $user->update($validatedData);
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
        if (Auth::user()->id === $user->id) {
            $user->delete();
            return response()->json(null, 204);
        }
        return response()->json(['Message'=>'Unauthorized'],401);
    }

    public function getUserOffers(User $user)
    {
        return response()->json(Offer::where('owner', $user->id)->where('active', '1')->latest()->paginate(15));
    }
}
