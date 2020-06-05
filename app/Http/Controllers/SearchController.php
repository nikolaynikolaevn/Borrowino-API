<?php

namespace App\Http\Controllers;

use App\Offer;
use App\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchOffer(Request $request) {
        $query = $request->input('q');
        $type = $request->input('type');

        if ($type === "users") {
            $result = User::where('name', 'LIKE', "%{$query}%")->paginate(15);
        }
        else {
            $result = Offer::where('title', 'LIKE', "%{$query}%")->orWhere('description', 'LIKE', "%{$query}%")->paginate(15);
        }
        return response()->json($result, 200);
    }
}
