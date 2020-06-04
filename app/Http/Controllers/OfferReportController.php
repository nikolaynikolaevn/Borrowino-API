<?php

namespace App\Http\Controllers;

use App\Offer;
use App\OfferReport;
use Illuminate\Http\Request;

class OfferReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'offer_id' => 'required|numeric|min:1',
            'description' => 'required',
            'reporter_id' => 'required|numeric|min:1'
        ]);

        $offerReport = new OfferReport();
        $offerReport->offer_id = $validatedData['offer_id'];
        $offerReport->description = $validatedData['description'];
        $offerReport->reporter_id = $validatedData['reporter_id'];
        $offerReport->save();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\OfferReport $offerReport
     * @return \Illuminate\Http\Response
     */
    public function show(OfferReport $offerReport)
    {
        //
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
     * @return \Illuminate\Http\Response
     */
    public function destroy(OfferReport $offerReport)
    {
        //
    }
}
