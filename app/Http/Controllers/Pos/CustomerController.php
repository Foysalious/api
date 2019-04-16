<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = collect([
            [
                'name' => 'Ahsan Habib',
                'phone' => '01912122121',
                'email' => 'ahsan@gmail.com',
                'note' => 'Test Note @ test',
                'address' => 'Muradpur, Ctg',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1554041284_best_deal_for_beauty_.png'
            ],
            [
                'name' => 'Sabbir Ahmed',
                'phone' => '01881961993',
                'email' => 'sabbir25112@gmail.com',
                'note' => 'Rising CEO',
                'address' => 'Mahakhali',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1554041284_best_deal_for_beauty_.png'
            ],
        ]);
        try {
            return api_response($request, $customers, 200, ['customers' => $customers]);
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
        $customer =  [
            'name' => 'Sabbir Ahmed',
            'phone' => '01881961993',
            'email' => 'sabbir25112@gmail.com',
            'note' => 'Rising CEO',
            'address' => 'Mahakhali',
            'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1554041284_best_deal_for_beauty_.png'
        ];

        try {
            return api_response($request, $customer, 200, ['customer' => $customer]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
