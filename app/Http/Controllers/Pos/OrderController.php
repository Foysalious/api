<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = [
            [
                'date' => '12 Feb 2019',
                'orders' => [
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'paid',
                    ],
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'due',
                    ]
                ]
            ],
            [
                'date' => '14 Feb 2019',
                'orders' => [
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'paid',
                    ],
                    [
                        'code' => '156412',
                        'time' => '5:20PM',
                        'status' => 'due',
                    ]
                ]
            ]
        ];
        //To do => Filter Collection By Query
        try {
            return api_response($request, $orders, 200, ['orders' => $orders]);
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

    public function show()
    {

    }
}
