<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Dal\ApprovalRequest\ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalRequest\Updater;
use League\Fractal\Resource\Collection;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Item;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\ModificationFields;
use Throwable;

class ApprovalRequestController extends Controller
{
    use BusinessBasicInformation;
    use ModificationFields;

    private $approvalRequestRepo;

    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    public function show($approval_request, Request $request)
    {
        try {
            $approval_request = $this->approvalRequestRepo->find($approval_request);
            $model = $approval_request->requestable_type;
            $model = $model::find($approval_request->requestable_id);
            $approvers = $this->getApprover($model);
            $leave_business_member = $this->getBusinessMemberById($model->business_member_id);
            $member = $leave_business_member->member;
            $profile = $member->profile;
            $role = $leave_business_member->role;
            $leave_type = $model->leaveType;
            $approval_request_details = [
                'id' => $approval_request->id,
                'status' => $approval_request->status,
                'profile' => [
                    'name' => $profile->name,
                ],
                'contents' => [
                    'id' => $model->id,
                    'title' => $model->title,
                    'requested_on' => $model->created_at->format('M d') . ' at ' . $model->created_at->format('h:i a'),
                    'total_days' => $model->total_days,
                    'left' => 3,
                    'leave_type' => $leave_type->title,
                    'period' => Carbon::parse($model->start_date)->format('M d') . ' - ' . Carbon::parse($model->end_date)->format('M d'),
                    'status' => $model->status,
                ],
                'approvers' => $approvers,
                'department' => [
                    'department_id' => $role ? $role->businessDepartment->id : null,
                    'department' => $role ? $role->businessDepartment->name : null,
                    'designation' => $role ? $role->name : null
                ],
            ];
            return api_response($request, null, 200, ['approval_request_details' => $approval_request_details]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getApprover($model)
    {
        $approvers = [];
        foreach ($model->requests as $approval_request) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            array_push($approvers, [
                'name' => $profile->name,
                'status' => $approval_request->status,
            ]);
        }
        return $approvers;
    }

    public function index(Request $request)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            $approval_requests = ApprovalRequest::where('approver_id', $business_member->id)->get();
            $approval_requests_list = [];
            foreach ($approval_requests as $approval_request) {
                $model = $approval_request->requestable_type;
                $model = $model::find($approval_request->requestable_id);
                $leave_business_member = BusinessMember::findOrFAil($model->business_member_id);
                $member = $leave_business_member->member;
                $profile = $member->profile;
                $leave_type = $model->leaveType;
                $request_list = [
                    'id' => $approval_request->id,
                    'leave' => [
                        'name' => $profile->name,
                        'total_days' => $model->total_days,
                        'leave_type' => $leave_type->title,
                    ],
                    'status' => $approval_request->status,
                    'created_at' => $approval_request->created_at->format('M d,Y'),
                ];
                array_push($approval_requests_list, $request_list);
            }
            if (count($approval_requests_list) > 0) return api_response($request, $approval_requests_list, 200, ['approval_requests_list' => $approval_requests_list]);
            else  return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateStatus(Request $request, Updater $updater)
    {
        try {
            $type = $request->type;
            $type_ids = json_decode($request->type_id);
            $business_member = $this->getBusinessMember($request);
            $member = $this->getMember($request);
            $model = 'Sheba\\Dal\\' . ucfirst(camel_case($type)) . '\\Model';
            $models = $model::whereIn('id', $type_ids)->get();
            foreach ($models as $model) {
                $approval_request = $model->requests->where('approver_id', $business_member->id)->first();
                if (!$approval_request)
                    continue;
                $updater->setMember($member)
                    ->setBusinessMember($business_member)
                    ->setApprovalRequest($approval_request);
                if ($error = $updater->hasError())
                    return api_response($request, $error, 400, ['message' => $error]);
                $updater->setStatus($request->status)->change();
                if ($model->status != 'rejected') {
                    $this->setModifier($member);
                    $rejected_approval_requests = $model->requests->where('status', 'rejected');
                    if ($rejected_approval_requests) {#Rejected Request
                        $model->update($this->withBothModificationFields(['status' => 'rejected']));
                    }
                    $accepted_approval_requests = $model->requests->whereIn('status', ['pending', 'rejected']);#All Status Accepted
                    if ($accepted_approval_requests->isEmpty()) {
                        $model->update($this->withBothModificationFields(['status' => 'accepted']));
                    }
                }
            }
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