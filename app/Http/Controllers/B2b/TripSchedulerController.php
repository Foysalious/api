<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TripSchedulerController extends Controller
{

    public function getList($business, Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:vehicle,employee,driver',
                'from' => 'sometimes|date|required_with:to',
                'to' => 'sometimes|date|required_with:from'
            ]);
            $trips = BusinessTrip::whereNotIn('status', ['cancelled', 'completed'])->with(['vehicle' => function ($q) {
                $q->with(['basicInformation', 'businessDepartment']);
            }, 'member.profile', 'driver.profile'])->where('business_id', $business);
            $from = $request->from;
            $to = $request->to;
            if ($request->filled('from') && $request->filled('to')) {
                $trips->where(function ($q) use ($from, $to) {
                    $q->whereBetween('start_date', [$from, $to])->orWhere(function ($q) use ($from, $to) {
                        $q->where('start_date', '<', $from)->whereBetween('end_date', [$from, $to])->orWhere(function ($q) use ($from, $to) {
                            $q->where('end_date', '>', $to)->whereBetween('start_date', [$from, $to]);
                        });
                    });
                });
            }
            $business_member = $request->business_member;
            if (!$business_member->is_super) $trips = $trips->where('member_id', $business_member->member_id);
            $trips = $trips->get();
            $filter = $request->filter;
            $final = [];
            if ($filter == 'vehicle') {
                $group_by_vehicles = $trips->groupBy('vehicle_id');
                foreach ($group_by_vehicles as $key => $trips) {
                    $vehicle = $trips->first()->vehicle;
                    $data = [
                        'id' => $vehicle->id,
                        'name' => $vehicle->basicInformation->model_name,
                        'status' => ucfirst($vehicle->status),
                        'department' => $vehicle->businessDepartment->name,
                        'type' => $vehicle->basicInformation->readable_type,
                        'image' => $vehicle->basicInformation->vehicle_image
                    ];
                    $trip_data = [];
                    foreach ($trips as $trip) {
                        array_push($trip_data, $this->formatTrip($trip));
                    }
                    $data['trips'] = $trip_data;
                    array_push($final, $data);
                }
            } elseif ($filter == 'employee') {
                $group_by_members = $trips->groupBy('member_id');
                foreach ($group_by_members as $key => $trips) {
                    $member = $trips->first()->member;
                    $data = [
                        'id' => $member->id,
                        'name' => $member->profile->name,
                        'image' => $member->profile->pro_pic,
                    ];
                    $trip_data = [];
                    foreach ($trips as $trip) {
                        array_push($trip_data, $this->formatTrip($trip));
                    }
                    $data['trips'] = $trip_data;
                    array_push($final, $data);
                }
            } else {
                $group_by_drivers = $trips->groupBy('driver_id');
                foreach ($group_by_drivers as $key => $trips) {
                    $driver = $trips->first()->driver;
                    $data = [
                        'id' => $driver->id,
                        'name' => $driver->profile->name,
                        'image' => $driver->profile->pro_pic,
                    ];
                    $trip_data = [];
                    foreach ($trips as $trip) {
                        array_push($trip_data, $this->formatTrip($trip));
                    }
                    $data['trips'] = $trip_data;
                    array_push($final, $data);
                }
            }
            return api_response($request, $final, 200, ['data' => $final]);
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

    private function formatTrip(BusinessTrip $trip)
    {
        return [
            'id' => $trip->id,
            'start_date' => $trip->start_date,
            'end_date' => $trip->end_date,
            'driver' => [
                'id' => $trip->driver->id,
                'name' => $trip->driver->profile->name,
                'image' => $trip->driver->profile->pro_pic,
            ],
            'member' => [
                'id' => $trip->member->id,
                'name' => $trip->member->profile->name,
                'image' => $trip->member->profile->pro_pic,
            ],
            'vehicle' => [
                'id' => $trip->vehicle->id,
                'name' => $trip->vehicle->basicInformation->model_name,
                'status' => ucfirst($trip->vehicle->status),
                'department' => $trip->vehicle->businessDepartment->name,
                'type' => $trip->vehicle->basicInformation->readable_type,
                'vehicle_image' => $trip->vehicle->basicInformation->vehicle_image
            ],
        ];
    }
}
