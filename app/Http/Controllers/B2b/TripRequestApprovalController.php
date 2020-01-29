<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\TripRequestApproval\Updater;
use Sheba\Dal\TripRequestApproval\TripRequestApprovalRepositoryInterface;
use Sheba\Dal\TripRequestApproval\Model as TripRequestApproval;
use Illuminate\Validation\ValidationException;
use Sheba\Business\TripRequestApproval\Creator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TripRequestApprovalController extends Controller
{
    public function statusUpdate($member, $approval, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, ['status' => 'required|string']);
            $updater->setMember($request->member)
                ->setTripRequestApproval((int)$approval)
                ->setData($request->all());
            if ($error = $updater->hasError())
                return api_response($request, $error, 400, ['message' => $error]);
            $updater->change();
            return api_response($request, null, 200);
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