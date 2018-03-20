<?php

namespace App\Http\Controllers;


use App\Models\OfferShowcase;
use Illuminate\Http\Request;

class OfferController extends Controller
{

    public function index(Request $request)
    {
        try {
            $offers = OfferShowcase::active()->select('id', 'thumb', 'title', 'short_description', 'target_link')->get();
            return count($offers) > 0 ? api_response($request, $offers, 200, ['offers' => $offers]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($offer, Request $request)
    {
        try {
            $offer = OfferShowcase::active()->select('id', 'thumb', 'title', 'banner', 'short_description', 'detail_description', 'target_link')->where('id', $offer)->first();
            return ($offer) ? api_response($request, $offer, 200, ['offer' => $offer]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }

    }
}