<?php

namespace App\Http\Controllers;

use App\Offer;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function searchOffer(Request $request) {
        $query = $request->input('q');
        $offers = Offer::where('title', 'LIKE', "%{$query}%")->orWhere('description', 'LIKE', "%{$query}%")->get();
        return response()->json($offers, 200);
    }
}
