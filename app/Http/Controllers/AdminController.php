<?php

namespace App\Http\Controllers;


use App\Offer;
use App\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;

class adminController extends Controller
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
