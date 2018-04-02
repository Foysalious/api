<?php
/**
 * Created by PhpStorm.
 * User: pasha
 * Date: 3/31/2018
 * Time: 7:23 PM
 */

namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\PushSubscription;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function store(Request $request)
    {
        try {
            $this->validate($request, ['category' => 'required', 'location' => 'required',]);
            $service_request = new ServiceRequest();
            $service_request->services = $request->has('services') ? $request->services : null;
            $service_request->customer_id = $request->has('customer') ? $request->customer : null;
            if ($service_request->customer_id == null) {
                $service_request->customer_name = $request->has('customer_name') ? $request->customer_name : null;
                $service_request->customer_mobile = $request->has('customer_mobile') ? $request->customer_mobile : null;
                $service_request->customer_email = $request->has('customer_email') ? $request->customer_email : null;
            }
            $service_request->category_id = $request->category;
            if ($request->category_id == null) {
                $service_request->category_name = $request->has('category_name') ? $request->category_name : null;
            }
            if ($request->location_id == null) {
                $service_request->location_name = $request->has('location_name') ? $request->location_name : null;
            }
            $service_request->location_id = $request->location;
            $service_request->save();
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function savePushSubscription($service_request)
    {
        $push_sub = new PushSubscription();
        $push_sub->subscriber_type = "App\Models\Customer";
        $push_sub->subscriber_id = $service_request->customer_id;
        $push_sub->save();
    }
}