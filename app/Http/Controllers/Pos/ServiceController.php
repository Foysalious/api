<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->search_param;
        $services = collect([
            [
                'name' => 'Barcelona',
                'image' => 'https://southolespaintours.com/wp-content/uploads/2018/10/barcelona.jpg',
                'regular_price' => 1300.00,
                'discounted_price' => 1254.00,
                'category' => 'Foods',
                'sub_category' => 'Breakfast',
                'inventory' => true,
                'quantity' => 300,
                'purchase_cost' => 20,
                'vat_applicable' => true,
                'vat' => 0.20,
                'discount_applicable' => true,
                'discount_amount' => 46,
                'discount_end_time' => Carbon::parse('11-08-2019')
            ],
            [
                'name' => 'Flower Kit',
                'image' => 'https://lh6.googleusercontent.com/proxy/u8eiKX1ZnFRzptzjIcyehRP-wH1_GbB9P0uog4dh5wrsXmx57m7H97yRAjjTmqSaAWwtWGHyYqI9dFYsvL-L75RrMF_bIeUPmgwqRjAtRWop_PrcMNXoeUcHWfdqvLuzPURmnYlAOSeZYOcOpyYrDYpXleM=w100-h134-n-k-no',
                'regular_price' => 1300.00,
                'discounted_price' => 1254.00,
                'category' => 'Foods',
                'sub_category' => 'Breakfast',
                'inventory' => true,
                'quantity' => 300,
                'purchase_cost' => 20,
                'vat_applicable' => true,
                'vat' => 0.20,
                'discount_applicable' => true,
                'discount_amount' => 46,
                'discount_end_time' => Carbon::parse('11-08-2019')
            ]
        ]);
        //To do => Filter Collection By Query
        try {
            return api_response($request, $services, 200, ['services' => $services]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function create()
    {

    }

    public function store()
    {

    }

    public function edit()
    {

    }

    public function update()
    {

    }

    public function show(Request $request)
    {
        $service = [
            'name' => 'Flower Kit',
            'image' => 'https://lh6.googleusercontent.com/proxy/u8eiKX1ZnFRzptzjIcyehRP-wH1_GbB9P0uog4dh5wrsXmx57m7H97yRAjjTmqSaAWwtWGHyYqI9dFYsvL-L75RrMF_bIeUPmgwqRjAtRWop_PrcMNXoeUcHWfdqvLuzPURmnYlAOSeZYOcOpyYrDYpXleM=w100-h134-n-k-no',
            'regular_price' => 1300.00,
            'discounted_price' => 1254.00,
            'category' => 'Foods',
            'sub_category' => 'Breakfast',
            'inventory' => true,
            'quantity' => 300,
            'purchase_cost' => 20,
            'vat_applicable' => true,
            'vat' => 0.20,
            'discount_applicable' => true,
            'discount_amount' => 46,
            'discount_end_time' => Carbon::parse('11-08-2019')
        ];
        try {
            return api_response($request, $service, 200, ['service' => $service]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
