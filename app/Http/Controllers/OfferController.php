<?php

namespace App\Http\Controllers;


use App\Models\OfferShowcase;
use App\Transformers\OfferDetailsTransformer;
use App\Transformers\OfferTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
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
            $type = $offer == 1 ? 'App\\Models\\Voucher' : 'App\\Models\\Category';
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $offer = OfferShowcase::active()->where('target_type', $type)->first();
            return ($offer) ? api_response($request, $offer, 200, ['offer' => $manager->createData(( new Item($offer, new OfferDetailsTransformer())))->toArray()]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            dd($e);
            return api_response($request, null, 500);
        }

    }
}