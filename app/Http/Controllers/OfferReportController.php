<?php

namespace App\Http\Controllers;

use App\Offer;
use App\OfferReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $offerReports = OfferReport::paginate(15);
        return response()->json($offerReports, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Offer $offer
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Offer $offer, Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required',
        ]);

        $offerReport = new OfferReport();
        $offerReport->offer_id = $offer->id;
        $offerReport->description = $validatedData['description'];
        $offerReport->reporter_id = Auth::user()->id;
        $offerReport->save();

        $offerReport->refresh();
        return response()->json($offerReport, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\OfferReport $offerReport
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(OfferReport $offerReport)
    {
        return response()->json($offerReport, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\OfferReport $offerReport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OfferReport $offerReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\OfferReport $offerReport
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(OfferReport $offerReport)
    {
        $offerReport->delete();
        return response()->json(null, 204);
    }
}
