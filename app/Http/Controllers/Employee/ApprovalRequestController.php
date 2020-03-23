<?php namespace App\Http\Controllers\Employee;

use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalRequest\Updater;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApprovalRequestController extends Controller
{
    public function updateStatus(Request $request, Updater $updater)
    {
        try {
            $type = $request->type;
            $type_ids = json_decode($request->type_id);
            $business_member = $request->auth_info['business_member'];
            $business_member_id = $business_member['id'];
            $member_id = $business_member['member_id'];
            #$model = "App\\Models\\" . ucfirst(camel_case($type));
            $model = 'Sheba\\Dal\\' . ucfirst(camel_case($type)) . '\\Model';
            $models = $model::whereIn('id', $type_ids)->get();
            foreach ($models as $model) {
                #dd($model as $leave);
                $model_status = $model->status;#check leave status rejected ki na??
                $approval_requests = $model->requests->where('status', 'rejected');#check approval_requests status rejected ki na??
                #dd($approval_requests->isEmpty());

                $approval_request = $model->requests->where('approver_id', $business_member_id)->first();#null check korte hobe
                dump($approval_request);
                $updater->setMember($member_id)
                    ->setBusinessMember($business_member_id)
                    ->setApprovalRequest($approval_request)
                    ->setStatus($request->status);
                if ($error = $updater->hasError())
                    return api_response($request, $error, 400, ['message' => $error]);
                $updater->change();
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}