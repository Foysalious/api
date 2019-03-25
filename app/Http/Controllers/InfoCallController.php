<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class InfoCallController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;

            $info = [
                [
                    'service_name' => 'ABC',
                    'created_at' => 'March 20, 2019',
                    'status' => 'In Progress',
                ],
                [
                    'service_name' => 'XYZ',
                    'created_at' => 'March 20, 2019',
                    'status' => 'In Progress',

                ],
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDetails($customer, Request $request)
    {
        try {
            $customer = $request->customer;

            $details = [
                [
                    'code' => '#A-9068947',
                    'created_at' => '18 March, 9.30AM',
                    'status' => 'In Progress',
                    'service_name' => 'ABC',
                    'budget' => '1000',
                ]

            ];
            return api_response($request, $details, 200, ['details' => $details]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $customer = $request->customer;

            $data = [
                'sevice_name' => $request->sevice_name,
                'budget' => $request->budget,
            ];

            return api_response($request, 1, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}