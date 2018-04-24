<?php

namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersonalInformationController extends Controller
{
    public function index($resource, Request $request)
    {
        try {
            $resource = $request->resource;
            $profile = $resource->profile;
            $info = array(
                'name' => $profile->name,
                'gender' => $profile->gender,
                'birthday' => $profile->dob,
                'address' => $profile->address,
                'nid_no' => $resource->nid_no,
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($resource, Request $request)
    {
        try {
            $this->validate($request, [
                'nid_no' => "bail|required|string",
                'name' => "sometimes|required|string",
                'gender' => 'sometimes|required|string|in:Male,Female,Other',
                'birthday' => 'sometimes|required|date_format:Y-m-d|before:' . date('Y-m-d'),
                'address' => 'sometimes|required|string',
                'picture' => 'sometimes|required|file',
                'nid_back' => 'sometimes|required|file',
                'nid_front' => 'sometimes|required|file',
            ]);
            $resource = $request->resource;
            $profile = $resource->profile;
            $profile->update(array_merge($request->only(['name', 'gender', 'address']), ['dob' => $request->birthday]));
            $resource->update($request->only(['nid_no']));
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