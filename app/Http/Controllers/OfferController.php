<?php

namespace App\Http\Controllers;


use App\Models\OfferShowcase;
use App\Transformers\OfferTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

class OfferController extends Controller
{

    public function index(Request $request)
    {
        try {
            $offers = OfferShowcase::active()->valid()->get();
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Collection($offers, new OfferTransformer());
            $offers = $manager->createData($resource)->toArray()['data'];
            if (count($offers) > 0) return api_response($request, $offers, 200, ['offers' => $offers]);
            else return api_response($request, null, 404);
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