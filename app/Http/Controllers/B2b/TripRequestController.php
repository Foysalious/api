<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\BusinessTripRequest;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{

    public function store(Request $request)
    {
        try {
            $business_trip_request = new BusinessTripRequest();
            $business_trip_request->member_id = $request->member_id;
            $business_trip_request->driver_id = $request->driver_id;
            $business_trip_request->pickup_geo = json_encode(['lat' => $request->pickup_lat, 'lng' => $request->pickup_lng]);
            $business_trip_request->dropoff_geo = json_encode(['lat' => $request->dropoff_lat, 'lng' => $request->dropoff_lng]);
            $business_trip_request->pickup_address = $request->pickup_address;
            $business_trip_request->dropoff_address = $request->dropoff_address;
            $business_trip_request->start_date = $request->start_date;
            $business_trip_request->trip_type = $request->trip_type;
            $business_trip_request->vehicle_type = $request->vehicle_type;
            $business_trip_request->save();
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}