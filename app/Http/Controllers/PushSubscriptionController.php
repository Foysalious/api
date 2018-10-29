<?php

namespace App\Http\Controllers;


use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PushSubscriptionController extends Controller
{

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'subscriber_type' => 'required|string|in:customer',
                'device' => 'required|string',
                'subscriber_id' => 'numeric'
            ]);
            $model_name = "App\\Models\\" . ucwords($request->subscriber_type);
            $push_sub = null;
            if ($request->has('subscriber_id')) $push_sub = PushSubscription::where([['subscriber_id', $request->subscriber_id], ['device', $request->device]])->first();
            if (!$push_sub) {
                $push_sub = new PushSubscription();
                $push_sub->subscriber_type = $model_name;
                $push_sub->device = $request->device;
                $push_sub->device_type = 'browser';
                $push_sub->subscriber_id = $request->has('subscriber_id') ? $request->subscriber_id : null;
                $push_sub->save();
            }
            return api_response($request, 1, 200);
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