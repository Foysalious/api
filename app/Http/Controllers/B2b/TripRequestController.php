<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\BusinessTripRequest;
use Illuminate\Http\Request;

class TripRequestController extends Controller
{

    public function getTripRequests(Request $request)
    {

        try {
            $list = [];
            $business_trip_requests = BusinessTripRequest::with(['member.profile'])->get();
            foreach ($business_trip_requests as $business_trip_request) {
                array_push($list, [
                    'id' => $business_trip_request->id,
                    'member' => [
                        'name' => $business_trip_request->member->profile->name
                    ],
                    'vehicle_type' => ucfirst($business_trip_request->vehicle_type),
                    'status' => ucfirst($business_trip_request->status),
                    'created_date' => $business_trip_request->created_at->format('Y-m-d'),
                ]);
            }
            if (count($business_trip_requests) > 0) return api_response($request, $business_trip_requests, 200, ['trip_requests' => $list]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getTrips(Request $request)
    {
        try {
            $list = [];
            $business_trips = BusinessTrip::with(['vehicle.basicInformation', 'driver.profile'])->get();
            foreach ($business_trips as $business_trip) {
                array_push($list, [
                    'id' => $business_trip->id,
                    'vehicle' => [
                        'name' => $business_trip->vehicle->basicInformation->model_name
                    ],
                    'driver' => [
                        'name' => $business_trip->driver->profile->name,
                        'image' => $business_trip->driver->profile->pro_pic,
                    ],
                    'start_date' => $business_trip->start_date,
                    'end_date' => $business_trip->end_date
                ]);
            }
            if (count($business_trips) > 0) return api_response($request, $business_trips, 200, ['trips' => $list]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function createTrip(Request $request)
    {
        try {
            if ($request->has('trip_request_id')) $business_trip_request = BusinessTripRequest::find((int)$request->trip_request_id);
            else $business_trip_request = $this->storeTripRequest($request);
            $business_trip_request->vehicle_id = $request->vehicle_id;
            $business_trip_request->driver_id = $request->driver_id;
            $business_trip = $this->storeTrip($business_trip_request);
            return api_response($request, $business_trip, 200, ['id' => $business_trip->id]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function createTripRequests(Request $request)
    {
        try {
            $business_trip_request = $this->storeTripRequest($request);
            return api_response($request, $business_trip_request, 200, ['id' => $business_trip_request->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function storeTrip(BusinessTripRequest $business_trip_request)
    {
        $business_trip = new BusinessTrip();
        $business_trip->business_trip_request_id = $business_trip_request->id;
        $business_trip->member_id = $business_trip_request->member_id;
        $business_trip->driver_id = $business_trip_request->driver_id;
        $business_trip->vehicle_id = $business_trip_request->vehicle_id;
        $business_trip->pickup_geo = $business_trip_request->pickup_geo;
        $business_trip->dropoff_geo = $business_trip_request->dropoff_geo;
        $business_trip->pickup_address = $business_trip_request->pickup_address;
        $business_trip->dropoff_address = $business_trip_request->dropoff_address;
        $business_trip->start_date = $business_trip_request->start_date;
        $business_trip->end_date = $business_trip_request->end_date;
        $business_trip->trip_type = $business_trip_request->trip_type;
        $business_trip->reason = $business_trip_request->reason;
        $business_trip->details = $business_trip_request->details;
        $business_trip->save();
        return $business_trip;
    }

    private function storeTripRequest(Request $request)
    {
        $business_trip_request = new BusinessTripRequest();
        $business_trip_request->member_id = $request->member_id;
        $business_trip_request->driver_id = $request->has('driver_id') ? $request->driver_id : null;
        $business_trip_request->vehicle_id = $request->has('vehicle_id') ? $request->vehicle_id : null;
        $business_trip_request->pickup_geo = $request->has('pickup_lat') ? json_encode(['lat' => $request->pickup_lat, 'lng' => $request->pickup_lng]) : null;
        $business_trip_request->dropoff_geo = $request->has('dropoff_lat') ? json_encode(['lat' => $request->dropoff_lat, 'lng' => $request->dropoff_lng]) : null;
        $business_trip_request->pickup_address = $request->pickup_address;
        $business_trip_request->dropoff_address = $request->dropoff_address;
        $business_trip_request->start_date = $request->start_date;
        $business_trip_request->end_date = $request->end_date;
        $business_trip_request->trip_type = $request->trip_type;
        $business_trip_request->reason = $request->reason;
        $business_trip_request->details = $request->details;
        $business_trip_request->no_of_seats = $request->no_of_seats;
        $business_trip_request->save();
        return $business_trip_request;
    }
}