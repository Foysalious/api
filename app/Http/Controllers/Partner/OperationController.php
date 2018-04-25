<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerWorkingHour;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class OperationController extends Controller
{
    public function index($partner, Request $request)
    {
        try {
            $partner = $request->partner->load(['locations' => function ($q) {
                $q->select('id', 'name', 'partner_id');
            }, 'categories' => function ($q) {
                $q->select('categories.id', 'categories.name', 'partner_id');
            }, 'basicInformations']);
            $working_hours = $partner->workingHours()->select('id', 'partner_id', 'day', 'start_time', 'end_time')->get();
            $final = collect($partner)->only(['id', 'name']);
            $final->put('address', $partner->basicInformations->address);
            $final->put('working_schedule', $working_hours);
            $final->put('locations', $partner->locations->each(function ($location) {
                removeRelationsAndFields($location);
            }));
            $final->put('categories', $partner->categories->each(function ($category) {
                removeRelationsAndFields($category);
            }));
            return api_response($request, $final, 200, ['partner' => $final]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'address' => "string",
                'locations' => "required",
                'categories' => "required",
                'working_schedule' => "required",
            ]);
            $partner = $request->partner;
            return $this->saveInDatabase($partner, $request) ? api_response($request, $partner, 200) : api_response($request, $partner, 500);
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

    private function saveInDatabase($partner, Request $request)
    {
        try {
            DB::transaction(function () use ($request, $partner) {
                $partner->locations()->sync(json_decode($request->locations));
                $partner->categories()->sync(json_decode($request->categories));
                $partner->basicInformations()->update(['address' => $request->address]);
                $partner->workingHours()->delete();
                foreach (json_decode($request->working_schedule) as $working_schedule) {
                    $partner->workingHours()->save(new PartnerWorkingHour([
                        'day' => $working_schedule->day,
                        'start_time' => $working_schedule->start_time,
                        'end_time' => $working_schedule->end_time
                    ]));
                }
            });
            return true;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}