<?php namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Partner;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Notification\NotificationCreated;
use Throwable;

class PushSubscriptionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'subscriber_type' => 'required|string|in:customer,partner,member', 'device' => 'required|string', 'subscriber_id' => 'numeric'
            ]);
            $model_name = "App\\Models\\" . ucwords($request->subscriber_type);
            $push_sub = null;
            if ($request->filled('subscriber_id')) $push_sub = PushSubscription::where([['subscriber_id', $request->subscriber_id], ['device', $request->device]])->first();
            if (!$push_sub) {
                $push_sub = new PushSubscription();
                $push_sub->subscriber_type = $model_name;
                $push_sub->device = $request->device;
                $push_sub->device_type = 'browser';
                $push_sub->subscriber_id = $request->filled('subscriber_id') ? $request->subscriber_id : null;
                $push_sub->save();
            }
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function send()
    {
        event(new NotificationCreated([
            'notifiable_id' => 17,
            'notifiable_type' => "member",
            'event_id' => 314,
            'event_type' => "bid",
            "title" => "Test notification",
            'message' => "Test notification",
            'link' => "https://b2b.dev-sheba.xyz/dashboard/fleet-management/requests/151/details"
        ], 233, "App\Models\Partner"));
    }

    public function sendV2()
    {
        $partner = Partner::find(277);
        notify($partner)->send([
            'event_id' => 321,
            'event_type' => "procurement",
            "title" => "Test notification",
            'link' => "https://partners.dev-sheba.xyz/star-auto-power/procurements/321/summary"
        ]);
        print_r('DONE');
    }
}