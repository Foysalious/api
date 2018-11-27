<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\OfferShowcase;
use App\Transformers\OfferDetailsTransformer;
use App\Transformers\OfferTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Offer\OfferFilter;

class OfferController extends Controller
{

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'user' => 'numeric',
                'user_type' => 'string|in:customer',
                'remember_token' => 'required_with:user|string',
                'category' => 'numeric'
            ]);
            $user = $category = null;
            if ($request->has('user') && $request->has('user_type') && $request->has('remember_token')) {
                $model_name = "App\\Models\\" . ucwords($request->user_type);
                $user = $model_name::with('orders', 'promotions')->where('id', (int)$request->user)->where('remember_token', $request->remember_token)->first();
            }
            $offers = OfferShowcase::active()->valid()->get();
            if (count($offers) == 0) return api_response($request, null, 404);
            $offer_filter = new OfferFilter($offers);
            if ($user) $offer_filter->setCustomer($user);
            if ($request->has('category')) $offer_filter->setCategory(Category::find((int)$request->category));
            $offers = $offer_filter->filter()->sortByDesc('amount');
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $resource = new Collection($offers, new OfferTransformer());
            $offers = $manager->createData($resource)->toArray()['data'];
            if (count($offers) > 0) return api_response($request, $offers, 200, ['offers' => $offers]);
            else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($offer, Request $request)
    {
        try {

            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $offer = OfferShowcase::active()->where('id', $offer)->first();
            if ($offer) {
                $offer->customer_id = $request->get('customer_id');
                $data = $manager->createData((new Item($offer, new OfferDetailsTransformer())))->toArray();
                return api_response($request, $offer, 200, ['offer' => $data]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }

    }
}