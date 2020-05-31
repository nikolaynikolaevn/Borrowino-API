<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class WorkaroundUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }

    /**
     * Return the specified resource image url
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function images(User $user)
    {
        $fileNames = (new ImageController)->fetchImages($user->id, 'profile_image');
        if ($fileNames == null) {
            return response()->json(['Message' => 'No images found'], 404);
        }
        return response()->json(['images' => $fileNames], 200);
    }
}
