<?php

namespace App\Http\Controllers;

use App\Image;
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|min:5|max:25',
            'description' => 'required',
            'location' => 'required',
            'price' => 'required|numeric|min:0',
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg|max:10240' // 'images.*' because there can be multiple imagesMax 10mB
        ]);

        $offer = new Offer();
        $offer->title = $validatedData['title'];
        $offer->description = $validatedData['description'];
        $offer->location = $validatedData['location'];
        $offer->price = $validatedData['price'];
        $offer->owner = Auth::user()->id;
        $offer->save();

        if (array_key_exists('images', $validatedData)) {
            $offer->images = true;
            $offer->save();

            ImageController::uploadImages($validatedData['images'], $offer->id, 'offer_image');
        }

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
        $offer = Offer::findOrFail($id);
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
        $offer = Offer::findOrFail($id);

        if (Auth::user()->id != $offer->owner) {
            return response()->json(['Message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'title' => 'required|min:5|max:25',
            'description' => 'required',
            'location' => 'required',
            'price' => 'required|numeric|min:0',
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg|max:10240' // 'images.*' because there can be multiple imagesMax 10mB
        ]);

        $offer->update($validatedData);

//        $imagesInDatabase = Image::where('resource_type', 'offer_image')
//            ->where('resource_id', $offer->id)
//            ->get();

        if (array_key_exists('images', $validatedData)) {
            $offer->images = true;
            $offer->save();

            ImageController::uploadImages($validatedData['images'], $offer->id, 'offer_image');
        }

//        if ($imageCount = 0 && count($imagesInDatabase) == 0) {
//            $offer->images = false;
//            $offer->save;
//        }

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
        $offer = Offer::findOrFail($id);

        if (Auth::user()->id != $offer->owner) {
            return response()->json(['Message' => 'Unauthorized'], 401);
        }

        $offer->delete();
        return response()->json(null, 204);
    }
}
