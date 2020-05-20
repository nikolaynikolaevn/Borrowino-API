<?php

namespace App\Http\Controllers;

use App\Offer;
use App\OfferRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $offerRequests = OfferRequest::paginate(15);
        return response()->json($offerRequests, 206);
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
            'offer' => 'required|numeric',
            'from' => 'required|date|before_or_equal:until',
            'until' => 'required|date|after_or_equal:from',
            'description' => 'max:1000',
        ]);

        $offerRequest = new OfferRequest();
        $offerRequest->borrower = Auth::user()->id;
        $offerRequest->offer = $request->offer;
        $offerRequest->from = $request->from;
        $offerRequest->until = $request->until;
        $offerRequest->description = $request->description;
        $offerRequest->save();

        return response()->json(null, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\OfferRequest
     * @return OfferRequest
     */
    public function show(OfferRequest $offerRequest)
    {
        return $offerRequest;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\OfferRequest $offerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, OfferRequest $offerRequest)
    {
        $validatedData = $request->validate([
            'from' => 'required|date|before_or_equal:until',
            'until' => 'required|date|after_or_equal:from',
            'description' => 'max:1000',
        ]);

        $offer = Offer::findOrFail($offerRequest->offer);
        $owner = User::find($offer->owner);

        if (Auth::user()->id !== $owner->id && Auth::user()->id !== $offerRequest->borrower) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }

        if (Auth::user()->id === $owner->id) {
            $validatedData->status = $request->status;
        }

        $offerRequest->update($validatedData);
        return response()->json($offerRequest, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OfferRequest $offerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(OfferRequest $offerRequest)
    {
        if (Auth::user()->id !== $offerRequest->borrower) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }

        $offerRequest->delete();
        return response()->json(null, 204);
    }
}
