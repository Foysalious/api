<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as DBTransaction;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\TripRequestApprovalFlow\Model as TripRequestApprovalFlow;

class ApprovalFlowController extends Controller
{
    public function createApprovalFlow(Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'required|string|in:accept,reject'
            ]);
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