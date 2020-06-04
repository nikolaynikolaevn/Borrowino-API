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
        $offers = Offer::latest()->paginate(15);
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
            'images.*' => 'image|mimes:jpg,jpeg,gif,png,svg,bmp,webp|max:10240' // 'images.*' because there can be multiple imagesMax 10mB
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

            (new ImageController)->uploadImages($validatedData['images'], $offer->id, 'offer_image');
        }

        $offer->refresh(); // This is to include the default values that are not changed on creation
        return response()->json($offer, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Offer $offer)
    {
//        $offer = Offer::findOrFail($id);
        return response()->json($offer, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Offer $offer)
    {
//        $offer = Offer::findOrFail($id);

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

        if (array_key_exists('images', $validatedData)) {
            (new ImageController)->deleteImages($offer->id, 'offer_image');
            $offer->images = true;
            $offer->save();

            (new ImageController)->uploadImages($validatedData['images'], $offer->id, 'offer_image');
        }

        return response()->json($offer, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Offer $offer)
    {
//        $offer = Offer::findOrFail($id);

        if (Auth::user()->id != $offer->owner) {
            return response()->json(['Message' => 'Unauthorized'], 401);
        }

        (new ImageController)->deleteImages($offer->id, 'offer_image');

        $offer->delete();
        return response()->json(null, 204);
    }

    /**
     * @param \App\Offer $offer
     * @return \Illuminate\Http\JsonResponse
     */
    public function images(Offer $offer)
    {
        $fileNames = (new ImageController)->fetchImages($offer->id, 'offer_image');
        if ($fileNames == null) {
            return response()->json(['Message' => 'No images found'], 404);
        }
        return response()->json(['images' => $fileNames], 200);
    }
}
