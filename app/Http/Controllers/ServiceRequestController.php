<?php

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\PushSubscription;
use App\Models\ServiceRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        try {
            $service_request = $this->save($request);
            return $service_request ? api_response($request, null, 200) : api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function save($request)
    {
        $service_request = new ServiceRequest();
        try {
            DB::transaction(function () use ($request, $service_request) {
                $this->validate($request, ['category' => 'required', 'location' => 'required',]);
                $service_request->services = $request->filled('services') ? $request->services : null;
                $service_request->customer_id = $request->filled('customer') ? $request->customer : null;
                if ($service_request->customer_id == null) {
                    $service_request->customer_name = $request->filled('customer_name') ? $request->customer_name : null;
                    $service_request->customer_mobile = $request->filled('customer_mobile') ? $request->customer_mobile : null;
                    $service_request->customer_email = $request->filled('customer_email') ? $request->customer_email : null;
                }
                $service_request->category_id = $request->category;
                if (empty($request->category_id)) {
                    $service_request->category_name = $request->filled('category_name') ? $request->category_name : null;
                }
                if (empty($request->location_id)) {
                    $service_request->location_name = $request->filled('location_name') ? $request->location_name : null;
                }
                $service_request->location_id = $request->location;
                $service_request->save();
                $this->savePushSubscription($service_request);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return false;
        }
        return $service_request;
    }

    private function savePushSubscription($service_request)
    {
        $push_sub = new PushSubscription();
        $push_sub->subscriber_type = "App\Models\Customer";
        $push_sub->subscriber_id = $service_request->customer_id;
        $push_sub->save();
    }

}