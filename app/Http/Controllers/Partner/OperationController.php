<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            $final = collect($partner)->only(['id', 'name']);
            $final->put('address', $partner->basicInformations->address);
            $final->put('working_days', json_decode($partner->basicInformations->working_days));
            $final->put('working_hours', json_decode($partner->basicInformations->working_hours));
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
                'locations' => "required|array",
                'categories' => "required|array",
                'working_days' => "required|array",
                'working_hours' => "required|string",
            ]);
            $partner = $request->partner;
            $partner->locations->sync($request->locations);
            $partner->categories->sync($request->categories);
            $partner->basicInformation()->update(['working_days' => json_encode($request->working_days), 'working_hours' => $request->working_hours, 'address' => $request->address]);
            return api_response($request, $partner, 200);
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
}