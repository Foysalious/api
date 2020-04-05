<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\Business\LeaveRequestDetailsTransformer;
use App\Transformers\CustomSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Business\ApprovalRequest\Updater;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;

class LeaveController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    private $approvalRequestRepo;

    /**
     * ApprovalRequestController constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        list($offset, $limit) = calculatePagination($request);
        $business_member = $request->business_member;# for the time being i am working on the controller
        $type = "Sheba\Dal\Leave\Model";
        $leave_approval_requests = $this->approvalRequestRepo->builder()
            ->with(['requestable' => function ($query) {
                $query->with(['businessMember' => function ($query) {
                    $query->with(['member' => function ($query) {
                        $query->select('members.id', 'members.profile_id')->with(['profile' => function ($query) {
                            $query->select('profiles.id', 'profiles.name');
                        }]);
                    }, 'role' => function ($query) {
                        $query->select('business_roles.id', 'business_department_id', 'name')->with(['businessDepartment' => function ($query) {
                            $query->select('business_departments.id', 'business_id', 'name');
                        }]);
                    }
                    ]);
                }, 'leaveType']);
            }])->where('requestable_type', $type)->where('approver_id', $business_member->id);

        if ($request->has('department_id')) {#this filter does bot working
            $leave_approval_requests = $leave_approval_requests->whereHas('requestable', function ($q) use ($request) {
                $q->whereHas('businessMember', function ($q) use ($request) {
                    $q->whereHas('role', function ($q) use ($request) {
                        $q->whereHas('businessDepartment', function ($q) use ($request) {
                            $q->where('business_departments.id', $request->department_id);
                        });
                    });
                });
            });
        }
        #if ($request->has('status')) $leave_approval_requests = $leave_approval_requests->where('status', $request->status);
        #if ($request->has('department')) $leave_approval_requests = $this->filterWithDepartment($leave_approval_requests, $request);
        #if ($request->has('employee')) $leave_approval_requests = $this->filterWithEmployee($leave_approval_requests, $request);
        /*$total_leave_approval_requests = $leave_approval_requests->count();
        if ($request->has('limit')) $leave_approval_requests = $leave_approval_requests->splice($offset, $limit);*/
        $leaves = [];
        foreach ($leave_approval_requests->get() as $approval_request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var BusinessMember $business_member */
            $business_member = $requestable->businessMember;
            /** @var Member $member */
            $member = $business_member->member;
            /** @var Profile $profile */
            $profile = $member->profile;
            /** @var BusinessRole $role */
            $role = $business_member->role;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($leaves, $approval_request + [
                    'department' => [
                        'department_id' => $role ? $role->businessDepartment->id : null,
                        'department' => $role ? $role->businessDepartment->name : null,
                        'designation' => $role ? $role->name : null
                    ]
                ]);
        }

        if (count($leaves) > 0) return api_response($request, $leaves, 200, [
            'leaves' => $leaves,
            'total_leave_requests' => 1,
        ]);
        else return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $approval_request
     * @param Request $request
     * @return JsonResponse
     */
    public function show($business, $approval_request, Request $request)
    {
        $approval_request = $this->approvalRequestRepo->find($approval_request);
        /** @var Leave $requestable */
        $requestable = $approval_request->requestable;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if ($business_member->id != $approval_request->approver_id)
            return api_response($request, null, 403, ['message' => 'You Are not authorized to show this request']);
        $leave_requester_business_member = $this->getBusinessMemberById($requestable->business_member_id);
        /** @var Member $member */
        $member = $leave_requester_business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $leave_requester_business_member->role;

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($approval_request, new LeaveRequestDetailsTransformer($profile, $role));
        $approval_request = $manager->createData($resource)->toArray()['data'];

        $approvers = $this->getApprover($requestable);
        $approval_request = $approval_request + ['approvers' => $approvers];
        return api_response($request, null, 200, ['approval_details' => $approval_request]);
    }

    /**
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function updateStatus(Request $request, Updater $updater)
    {
        $this->validate($request, [
            'type_id' => 'required|string',
            'status' => 'required|string',
        ]);

        /** type_id approval_request id*/
        $type_ids = json_decode($request->type_id);

        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        $this->approvalRequestRepo->getApprovalRequestByIdAndType($type_ids, Type::LEAVE)
            ->each(function ($approval_request) use ($business_member, $updater, $request) {
                /** @var ApprovalRequest $approval_request */
                if ($approval_request->approver_id != $business_member->id) return;
                $updater->setBusinessMember($business_member)->setApprovalRequest($approval_request);
                $updater->setStatus($request->status)->change();
            });

        return api_response($request, null, 200);
    }

    private function filterWithDepartment($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var BusinessMember $business_member */
            $business_member = $requestable->businessMember;
            /** @var BusinessRole $role */
            $role = $business_member->role;
            if ($role) return $role->businessDepartment->id == $request->department;
        });
    }

    private function filterWithEmployee($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            return $member->id == $request->employee;
        });
    }

    /**
     * @param $requestable
     * @return array
     */
    private function getApprover($requestable)
    {
        $approvers = [];
        foreach ($requestable->requests as $approval_request) {
            $business_member = $this->getBusinessMemberById($approval_request->approver_id);
            $member = $business_member->member;
            $profile = $member->profile;
            $role = $business_member->role;
            array_push($approvers, [
                'name' => $profile->name,
                'designation' => $role ? $role->name : null,
                'department' => $role ? $role->businessDepartment->name : null,
                'phone' => $profile->mobile,
                'profile_pic' => $profile->pro_pic,
                'status' => $approval_request->status,
            ]);
        }
        return $approvers;
    }
}