<?php

namespace App\Http\Controllers;


use App\Models\Event;
use App\Sheba\UserRequestInformation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class EventController extends Controller
{
    use ModificationFields;

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'tag' => 'required|string',
                'value' => 'required|string',
                'user_id' => 'numeric',
                'user_type' => 'string|in:customer,resource'
            ]);
            $event = new Event();
            $event->tag = $request->tag;
            $event->value = $request->value;
            $event = $this->setCreatedInformation($request, $event);
            $event->fill((new UserRequestInformation($request))->getInformationArray());
            $event->save();
            return api_response($request, $event, 200);
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

    private function setCreatedInformation(Request $request, $event)
    {
        if ($request->filled('user_id') && $request->filled('user_type')) {
            $class_name = "App\\Models\\" . ucfirst($request->user_type);
            $user = $class_name::find((int)$request->user_id);
            $this->setModifier($user);
            $this->withCreateModificationField($event);
            $event->created_by_type = $class_name;
        } else {
            $event->created_at = Carbon::now();
        }
        return $event;
    }
}