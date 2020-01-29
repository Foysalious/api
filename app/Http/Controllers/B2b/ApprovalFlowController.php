<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\ApprovalFlow\Updater;
use Sheba\Dal\TripRequestApprovalFlow\TripRequestApprovalFlowRepositoryInterface;
use Sheba\Dal\TripRequestApprovalFlow\Model as TripRequestApprovalFlow;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalFlow\Creator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ApprovalFlowController extends Controller
{
    public function store(Request $request, Creator $creator)
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

    public function index(Request $request)
    {
        try {
            $approvals_flow = TripRequestApprovalFlow::query();
            $approval = [];
            foreach ($approvals_flow->get() as $approval_flow) {
                $business_department = $approval_flow->businessDepartment;
                $business_members = $approval_flow->approvers;
                $approvers_names = collect();
                $approvers_images = collect();
                foreach ($business_members as $business_member) {
                    $approvers_names->push($business_member->member->profile->name);
                    $approvers_images->push($business_member->member->profile->pro_pic);
                }
                array_push($approval, [
                    'id' => $approval_flow->id,
                    'title' => $approval_flow->title,
                    'department' => $business_department->name,
                    'approvers_name' => $approvers_names,
                    'approvers_images' => $approvers_images
                ]);
            }
            if (count($approval) > 0) return api_response($request, $approval, 200, ['approval' => $approval]);
            else  return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($member, $approval, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'business_member_ids' => 'required'
            ]);
            $approval_flow = $updater->setMember($request->member)
                ->setApproval((int)$approval)
                ->setTitle($request->title)
                ->setBusinessMemberIds($request->business_member_ids)
                ->update();
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