<?php namespace App\Http\Controllers\Employee;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
#use Sheba\Business\ApprovalRequest\Updater;
use App\Http\Controllers\Controller;
use Sheba\Business\ApprovalRequest\Updater;

class ApprovalRequestController extends Controller
{
    public function updateStatus(Request $request, Updater $updater)
    {
        try {
            dd(1233, $request->all());
            #return api_response($request, $approval_request, 200, ['id' => $approval_request->id]);
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