<?php namespace App\Http\Controllers;

use App\Models\InfoCall;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class InfoCallController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $info_call_lists = collect([]);
            foreach ($customer->infoCalls as $info_call) {
                $info = [
                    'id' => $info_call->id,
                    'code' => $info_call->code(),
                    'service_name' => $info_call->service_name,
                    'status' => $info_call->status,
                    'created_at' => $info_call->created_at->format('F j, Y'),
                    #'created' => $info_call->created_at->format('d F Y'),
                ];
                $info_call_lists->push($info);
            }
            return api_response($request, $info_call_lists, 200, ['info_call_lists' => $info_call_lists]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDetails($customer, $info_call, Request $request)
    {
        try {
            $customer = $request->customer;
            $info_call = InfoCall::find($info_call);
            $details = [
                [
                    'id' => $info_call->id,
                    'code' => $info_call->code(),
                    'service_name' => $info_call->service_name,
                    'status' => $info_call->status,
                    'created_at' => $info_call->created_at->format('F j, h:ia'),
                    'estimated_budget' => $info_call->estimated_budget,
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