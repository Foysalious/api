<?php namespace App\Http\Controllers\B2b;

use App\Models\BusinessMember;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Sheba\Business\ApprovalFlow\Updater;
use Sheba\Dal\TripRequestApprovalFlow\Model as TripRequestApprovalFlow;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalFlow\Creator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;
use Sheba\Dal\ApprovalFlow\Type;

class ApprovalFlowController extends Controller
{
    /**
     * @param $business
     * @param Request $request
     * @return JsonResponse
     */
    public function index($business, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $approvals_flows = TripRequestApprovalFlow::whereHas('businessDepartment', function ($q) use ($business) {
                $q->whereHas('business', function ($q) use ($business) {
                    $q->where('businesses.id', (int)$business);
                });
            })->orderBy('id', 'desc');
            if ($request->has('business_department_id')) {
                $approvals_flows = $approvals_flows->where('business_department_id', $request->business_department_id)->with('approvers');
            }

            $start_date = $request->has('start_date') ? $request->start_date : null;
            $end_date = $request->has('end_date') ? $request->end_date : null;
            if ($start_date && $end_date) {
                $approvals_flows->whereBetween('created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            }
            $approvals_flows = $approvals_flows->get();
            $total_approvals_flow = $approvals_flows->count();
            if ($request->has('limit')) $approvals_flows = $approvals_flows->splice($offset, $limit);
            $approval = [];
            foreach ($approvals_flows as $approval_flow) {
                $business_department = $approval_flow->businessDepartment;
                $business_members = $approval_flow->approvers;
                $approvers_names = collect();
                $approvers_images = collect();
                foreach ($business_members->groupBy('member_id') as $business_member) {
                    $business_member = $business_member->first();
                    $approvers_names->push($business_member->member->profile->name);
                    $approvers_images->push($business_member->member->profile->pro_pic);
                }
                array_push($approval, [
                    'id' => $approval_flow->id, 'title' => $approval_flow->title, 'department' => $business_department->name, 'approvers_name' => $approvers_names, 'approvers_images' => $approvers_images
                ]);
            }
            if (count($approval) > 0) return api_response($request, $approval, 200, [
                'approval' => $approval, 'total_approvals_flow' => $total_approvals_flow
            ]); else  return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $approval
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $approval, Request $request)
    {
        try {
            $approval_flow = TripRequestApprovalFlow::findOrFail((int)$approval);
            $business_department = $approval_flow->businessDepartment;
            $business_members = $approval_flow->approvers;
            $approvers = [];
            if ($business_members) {
                foreach ($business_members as $business_member) {
                    $member = $business_member->member;
                    $profile = $member->profile;
                    array_push($approvers, [
                        'id' => $member->id, 'name' => $profile->name ? $profile->name : null, 'pro_pic' => $profile->pro_pic ? $profile->pro_pic : null, 'designation' => $business_member->role ? $business_member->role->name : '', 'department' => $business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null,
                    ]);
                }
            }

            $approval_flow_details = [
                'id' => $approval_flow->id, 'title' => $approval_flow->title, 'department' => [
                    'id' => $business_department->id, 'name' => $business_department->name
                ], 'request_approvers' => $approvers
            ];

            if (count($approval) > 0) return api_response($request, $approval_flow_details, 200, ['approval_flow_details' => $approval_flow_details]); else  return api_response($request, null, 404);
        } catch (ModelNotFoundException $e) {
            return api_response($request, null, 404, ["message" => "Model Not found."]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store($business, Request $request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'type' => 'required|in:' . implode(',', Type::get()),
                'business_department_id' => 'required|integer|unique:approval_flows,business_department_id,NULL,id,type,' . $request->type,
                'employee_ids' => 'required'
            ]);
            
            $business_member_ids = BusinessMember::where('business_id', $business)
                ->whereIn('member_id', json_decode($request->employee_ids))
                ->select('id')->get()->pluck('id')->toArray();

            $approval_flow = $creator->setMember($request->manager_member)
                ->setTitle($request->title)
                ->setType($request->type)
                ->setBusinessDepartmentId($request->business_department_id)
                ->setBusinessMemberIds($business_member_ids)
                ->store();

            return api_response($request, $approval_flow, 200, ['id' => $approval_flow->id]);
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

    /**
     * @param $business
     * @param $approval
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update($business, $approval, Request $request, Updater $updater)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string', 'employee_ids' => 'required'
            ]);
            $business_member_ids = BusinessMember::where('business_id', $business)->whereIn('member_id', json_decode($request->employee_ids))->select('id')->get()->pluck('id')->toArray();
            $approval_flow = $updater->setMember($request->manager_member)->setApproval((int)$approval)->setTitle($request->title)->setBusinessMemberIds($business_member_ids)->update();
            return api_response($request, $approval_flow, 200, ['id' => $approval_flow->id]);
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
}
