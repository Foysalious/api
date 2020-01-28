<?php namespace App\Http\Controllers\B2b;

use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalFlow\Creator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ApprovalFlowController extends Controller
{
    public function createApprovalFlow(Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'business_department_id' => 'required|integer',
                'business_member_ids' => 'required'
            ]);
            $approval_flow = $creator->setMember($request->member)
                ->setTitle($request->title)
                ->setBusinessDepartmentId($request->business_department_id)
                ->setBusinessMemberIds($request->business_member_ids)
                ->store();
            return api_response($request, $approval_flow, 200, ['id' => $approval_flow->id]);
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