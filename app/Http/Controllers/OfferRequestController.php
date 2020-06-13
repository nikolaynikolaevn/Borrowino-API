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
     * @param $offer_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($offer_id)
    {
        $offerRequests = OfferRequest::where('offer', $offer_id)->latest()->paginate(15);
        return response()->json($offerRequests, 206);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param $offer_id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($offer_id, Request $request)
    {
        $request->validate([
            'from' => 'required|date|before_or_equal:until',
            'until' => 'required|date|after_or_equal:from',
            'description' => 'max:1000',
        ]);

        $offerRequest = new OfferRequest();
        $offerRequest->borrower = Auth::user()->id;
        $offerRequest->offer = $offer_id;
        $offerRequest->from = $request->from;
        $offerRequest->until = $request->until;
        $offerRequest->description = $request->description;
        $offerRequest->save();

        return response()->json($offerRequest, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $offer_id
     * @param $offerRequst_id
     * @return OfferRequest
     */
    public function show($offer_id, $offerRequst_id)
    {
        return OfferRequest::where('offer', $offer_id)->findOrFail($offerRequst_id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $offer_id
     * @param \Illuminate\Http\Request $request
     * @param \App\OfferRequest $offerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($offer_id, Request $request, OfferRequest $offerRequest)
    {
        $validatedData = $request->validate([
            'from' => 'sometimes|required|date|before_or_equal:until',
            'until' => 'sometimes|required|date|after_or_equal:from',
            'description' => 'max:1000',
            'status' => 'sometimes|in:accepted,declined',
        ]);

        $offer = Offer::findOrFail($offerRequest->offer);
        $owner = User::find($offer->owner);

        if (Auth::user()->id !== $owner->id && Auth::user()->id !== $offerRequest->borrower) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }

        if (Auth::user()->id === $owner->id) {
            $validatedData->status = $request->status;
        }
        else {
            $validatedData->status = $offerRequest->status;
        }

        $offerRequest->update($validatedData);
        return response()->json($offerRequest, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $offer_id
     * @param \App\OfferRequest $offerRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($offer_id, OfferRequest $offerRequest)
    {
        if (Auth::user()->id !== $offerRequest->borrower) {
            return response()->json(['Message'=>'Unauthorized'],401);
        }

        $offerRequest->delete();
        return response()->json(null, 204);
    }
}
