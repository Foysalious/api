<?php namespace App\Http\Controllers;

use App\Models\JobCancelReason;

use Illuminate\Http\Request;
use Sheba\CancelRequest\PartnerRequestor;
use Illuminate\Validation\ValidationException;

class PartnerCancelRequestController extends Controller
{
    public function store($partner, $job, Request $request, PartnerRequestor $partner_requestor)
    {
        try {
            $job = $request->job;
            $reasons = JobCancelReason::where('is_published_for_sp', 1)->pluck('key');
            $reasons = implode(',', array_flatten($reasons));
            $this->validate($request, ['cancel_reason' => "in:$reasons"]);
            $partner_requestor->setJob($job)->setReason($request->cancel_reason)->setEscalatedStatus(0);

            $error = $partner_requestor->hasError($request);
            if ($error) return api_response($request, $error['msg'], $error['code'], ['message' => $error['msg']]);

            $partner_requestor->request();
            return api_response($request, 1, 200, ['message' => "Cancel Request create successfully"]);
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

    public function cancelReasons(Request $request)
    {
        try {
            $reasons = JobCancelReason::select('id', 'key', 'name')->where('is_published_for_sp', 1)->get();
            return api_response($request, $reasons, 200, ['reasons' => $reasons]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
