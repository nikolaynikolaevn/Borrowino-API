<?php

namespace App\Http\Controllers;


use App\Offer;
use App\User;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;

class adminController extends Controller
{

    public function showUserDetailsById($id)
    {
        $User = User::findOrFail($id);
        return response()->json($User, 200);

    }
    public function showUserDetails()
    {

        $name= DB::select('select name from users');
        $email=DB::select('select email from users');
        $userDetails= array($name, $email);
        return response()->json($userDetails, 200);
    }

    public function deleteUser($id)
    {
        $user= User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }

    public function deleteOffer($id)
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();
        return response()->json(null, 204);
    }

    public function viewOffer($id)
    {
        $offer = Offer::findOrFail($id);
        return response()->json($offer, 200);
    }

    public function viewALLOffer()
    {
        $id = DB::select('select id from offers');
        $title=DB::select('select title from offers');
        $description=DB::select('select description from offers');
        $location=DB::select('select location from offers');
        $price=DB::select('select price from offers');
        $owner=DB::select('select owner from offers');

        $offers= array($id, $title, $description,$location,$price,$owner);
        return response()->json($offers, 200);

    }
}
