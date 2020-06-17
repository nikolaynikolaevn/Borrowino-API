<?php

namespace App\Http\Controllers;


use App\Offer;
use App\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{

    public function showUser(User $user)
    {
        return response()->json($user, 200);
    }

    public function showUsers()
    {
        $users = User::select(['id','name','email','created_at','is_admin'])->paginate(15);
        return response()->json($users, 200);
    }

    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|max:55',
            'email' => 'sometimes|required|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|confirmed', // This means that there needs to be a field called password_confirmation
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg,webp|max:10240', // 'images.*' because there can be multiple imagesMax 10mB
            'is_admin' => 'sometimes|boolean',
        ]);

        if (array_key_exists('password', $validatedData)) {
            $validatedData['password'] = bcrypt($validatedData['password']);
        }

        $user->update($validatedData);

        if (array_key_exists('images', $validatedData)) {
            (new ImageController)->deleteImages($user->id, 'profile_image');
            $user->images = true;
            $user->save();

            (new ImageController)->uploadImages($validatedData['images'], $user->id, 'profile_image');
        }

        return response()->json($user, 200);
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function deleteOffer(Offer $offer)
    {
        $offer->delete();
        return response()->json(null, 204);
    }

    public function viewOffer(Offer $offer)
    {
        return response()->json($offer, 200);
    }

    public function viewOffers()
    {
        $offers = Offer::paginate(15);
        return response()->json($offers, 200);
    }
}
