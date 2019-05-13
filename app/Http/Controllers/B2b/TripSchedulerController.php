<?php


namespace App\Http\Controllers\B2b;


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
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date'
            ]);
            $trips = BusinessTrip::whereNotIn('status', ['cancelled', 'completed'])->with(['vehicle.basicInformation', 'member.profile', 'driver.profile'])
                ->where('business_id', $business)->get();
            $filter = $request->filter;
            $final = [];
            if ($filter == 'vehicle') {
                $group_by_vehicles = $trips->groupBy('vehicle_id');
                foreach ($group_by_vehicles as $key => $trips) {
                    $vehicle = $trips->first()->vehicle;
                    $data = [
                        'id' => $vehicle->id,
                        'name' => $vehicle->basicInformation->model_name,
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
            } else {
                dd($trips->groupBy('driver_id'));
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
                'name' => $trip->vehicle->basicInformation->model_name
            ],
        ];
    }
}