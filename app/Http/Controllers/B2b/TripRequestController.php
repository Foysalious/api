<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use App\Models\BusinessTripRequest;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\Scheduler\VehicleScheduler;

class TripRequestController extends Controller
{

    public function getTripRequests(Request $request)
    {

        try {
            $list = [];
            $business = $request->business->load(['businessTripRequests' => function ($q) {
                $q->with('member.profile');
            }]);
            $business_trip_requests = $business->businessTripRequests;
            foreach ($business_trip_requests as $business_trip_request) {
                array_push($list, [
                    'id' => $business_trip_request->id,
                    'member' => [
                        'name' => $business_trip_request->member->profile->name,
                        "designation" => 'Manager'
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

    public function getTrips($member, Request $request)
    {
        try {
            $list = [];
            $business = $business = $request->business->load(['businessTrips' => function ($q) {
                $q->with(['vehicle.basicInformation', 'driver.profile']);
            }]);
            $business_trips = $business->businessTrips;
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

    public function tripRequestInfo($member, $trip_request, Request $request)
    {

        try {
            $trip_request = BusinessTripRequest::find((int)$trip_request);
            if (!$trip_request) return api_response($request, null, 404);
            $comments = [];
            foreach ($trip_request->comments as $comment) {
                array_push($comments, [
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            $info = [
                'id' => $trip_request->id,
                'reason' => $trip_request->reason,
                'details' => $trip_request->details,
                'member' => [
                    'name' => $trip_request->member->profile->name,
                    "designation" => 'Manager'
                ],
                'status' => $trip_request->status,
                'comments' => $comments,
                'vehicle_type' => ucfirst($trip_request->vehicle_type),
                'trip_type' => $trip_request->trip_readable_type,
                'pickup_address' => $trip_request->pickup_address,
                'dropoff_address' => $trip_request->dropoff_address,
                'start_date' => $trip_request->start_date,
                'end_date' => $trip_request->end_date,
                'no_of_seats' => $trip_request->no_of_seats,
                'created_at' => $trip_request->created_at->toDateTimeString(),
            ];

            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function tripInfo($member, $trip, Request $request)
    {

        try {
            $trip = BusinessTrip::find((int)$trip);
            if (!$trip) return api_response($request, null, 404);
            $comments = [];
            foreach ($trip->comments as $comment) {
                array_push($comments, [
                    'comment' => $comment->comment,
                    'user' => [
                        'name' => $comment->commentator->profile->name,
                        'image' => $comment->commentator->profile->pro_pic
                    ],
                    'created_at' => $comment->created_at->toDateTimeString()
                ]);
            }
            $info = [
                'id' => $trip->id,
                'reason' => $trip->reason,
                'details' => $trip->details,
                'member' => [
                    'name' => $trip->member->profile->name,
                    'image' => $trip->member->profile->pro_pic,
                    'designation' => 'Manager'
                ],
                'comments' => $comments,
                'driver' => [
                    'name' => $trip->driver->profile->name,
                    'mobile' => $trip->driver->profile->mobile,
                    'image' => $trip->driver->profile->pro_pic
                ],
                'vehicle' => [
                    'name' => $trip->vehicle->basicInformation->model_name,
                    'type' => $trip->vehicle->basicInformation->readable_type,
                ],
                'vehicle_type' => ucfirst($trip->vehicle_type),
                'trip_type' => $trip->trip_readable_type,
                'pickup_address' => $trip->pickup_address,
                'dropoff_address' => $trip->dropoff_address,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'no_of_seats' => $trip->no_of_seats,
                'created_at' => $trip->created_at->toDateTimeString(),
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function createTrip(Request $request)
    {
        try {
            $this->validate($request, ['status' => 'required|string|in:accept,reject']);
            if ($request->has('trip_request_id')) {
                $business_trip_request = BusinessTripRequest::find((int)$request->trip_request_id);
            } else $business_trip_request = $this->storeTripRequest($request);
            if ($request->has('status') && $request->status == "accept") {
                $business_trip_request->vehicle_id = $request->vehicle_id;
                $business_trip_request->driver_id = $request->driver_id;
                $business_trip_request->status = 'accepted';
                $business_trip_request->update();
                $business_trip = $this->storeTrip($business_trip_request);
                return api_response($request, $business_trip, 200, ['id' => $business_trip->id]);
            } else {
                $business_trip_request->status = 'rejected';
                $business_trip_request->update();
                return api_response($request, null, 200, ['message' => 'Trip Request rejected successfully']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function createTripRequests(Request $request, VehicleScheduler $vehicleScheduler)
    {
        try {
            $business_member = $request->business_member;
            $business_trip_request = $this->storeTripRequest($request);
            $will_auto_assign = $business_member->actions()->where('tag', config('business.actions.trip_request.auto_assign'))->first();
            if ($will_auto_assign) {
                $vehicleScheduler->setStartDate($request->start_date)->setEndDate($request->end_date)
                    ->setBusinessDepartment($business_member->role->businessDepartment)->setBusiness($request->business);
                $vehicles = $vehicleScheduler->getFreeVehicles();
                $drivers = $vehicleScheduler->getFreeDrivers();
                if ($vehicles->count() > 0) $vehicle = $vehicles->random(1);
                if ($drivers->count() > 0) $driver = $drivers->random(1);
                $business_trip_request->vehicle_id = $vehicle;
                $business_trip_request->driver_id = $driver;
                $business_trip_request->status = 'accepted';
                $business_trip_request->update();
                $this->storeTrip($business_trip_request);
            }
            return api_response($request, $business_trip_request, 200, ['id' => $business_trip_request->id]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function commentOnTripRequest($member, $trip_request, Request $request)
    {
        try {
            $comment = (new CommentRepository('BusinessTripRequest', $trip_request, $request->member))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function commentOnTrip($member, $trip, Request $request)
    {
        try {
            $comment = (new CommentRepository('BusinessTrip', $trip, $request->member))->store($request->comment);
            return $comment ? api_response($request, $comment, 200) : api_response($request, $comment, 500);
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
        $business_trip->business_id = $business_trip_request->business_id;
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
        $business_trip_request->business_id = $request->business->id;
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