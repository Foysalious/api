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
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'gender' => $profile->gender,
            ];
            return api_response($request, $info, 200, ['info' => $info]);
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