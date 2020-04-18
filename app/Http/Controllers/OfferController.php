<?php

namespace App\Http\Controllers;

use App\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $offers = Offer::paginate(15);
        return response()->json($offers, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|min:5|max:25',
            'description' => 'required',
            'location' => 'required',
            'price' => 'required|numeric',
        ]);

        $offer = new Offer();
        $offer->title = $request->title;
        $offer->description = $request->description;
        $offer->location = $request->location;
        $offer->price = $request->price;
        $offer->owner = $request->owner;
        $offer->save();

        return response()->json(null, 204);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $offer = Offer::find($id);
        return response()->json($offer, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $offer = Offer::find($id);
        if (Auth::user()->id != $offer->owner) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }
        $validatedData = $request->validate([
            'title' => 'required|min:3|max:25',
            'description' => 'required',
            'location' => 'required',
            'price' => 'required|numeric|min:0',
        ]);

        $offer->update($validatedData);
        return response()->json(null, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // @TODO: Change this method to include type hinting. For some odd reason this does not work otherwise
        $offer = Offer::find($id);

        if (Auth::user()->id != $offer->owner) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }

        $offer->delete();
        return response()->json(null, 204);
    }
}
