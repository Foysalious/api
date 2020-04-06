<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\Business\ApprovalRequestTransformer;
use App\Transformers\Business\LeaveBalanceDetailsTransformer;
use App\Transformers\Business\LeaveBalanceTransformer;
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
use Sheba\Helpers\TimeFrame;
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
        $business_member = $request->business_member;
        $leave_approval_requests = $this->approvalRequestRepo->getApprovalRequestByBusinessMemberFilterBy($business_member, Type::LEAVE);
        if ($request->has('status')) $leave_approval_requests = $leave_approval_requests->where('status', $request->status);
        if ($request->has('department')) $leave_approval_requests = $this->filterWithDepartment($leave_approval_requests, $request);
        if ($request->has('employee')) $leave_approval_requests = $this->filterWithEmployee($leave_approval_requests, $request);
        if ($request->has('search')) $leave_approval_requests = $this->searchWithEmployeeName($leave_approval_requests, $request);
        $total_leave_approval_requests = $leave_approval_requests->count();
        if ($request->has('limit')) $leave_approval_requests = $leave_approval_requests->splice($offset, $limit);
        $leaves = [];
        foreach ($leave_approval_requests as $approval_request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var BusinessMember $business_member */
            $business_member = $requestable->businessMember;
            /** @var Member $member */
            $member = $business_member->member;
            /** @var Profile $profile */
            $profile = $member->profile;

            $manager = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($leaves, $approval_request);
        }
        if ($request->has('direction')) {
            $leaves = $this->leaveOrderBy($leaves, $request->direction)->values();
        }

        if (count($leaves) > 0) return api_response($request, $leaves, 200, [
            'leaves' => $leaves,
            'total_leave_requests' => $total_leave_approval_requests,
        ]);
        else return api_response($request, null, 404);
    }

    private function leaveOrderBy($leaves, $direction = 'asc')
    {
        if ($direction === 'asc') {
            $leaves = collect($leaves)->sortBy(function ($leave, $key) {
                return $leave['leave']['name'];
            });
        } elseif ($direction === 'desc') {
            $leaves = collect($leaves)->sortByDesc(function ($leave, $key) {
                return $leave['leave']['name'];
            });
        }
        return $leaves;
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
        $leave_requester_business_member = $requestable->businessMember;
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

    private function searchWithEmployeeName($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var Member $member */
            $member = $requestable->businessMember->member;
            /** @var Profile $profile */
            $profile = $member->profile;
            return starts_with($profile->name, $request->search);
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

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function allLeaveBalance(Request $request, TimeFrame $time_frame)
    {
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        /** @var Business $business */
        $business = $business_member->business;
        $leave_types = $business->leaveTypes()->withTrashed()->take(5)->select('id', 'title', 'total_days')->get()->toArray();
        $members = $business->members()->select('members.id', 'profile_id')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile');
            }, 'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id');
            }
        ])->get();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($members, new LeaveBalanceTransformer($leave_types, $time_frame));
        $leave_balance = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['leave_balances' => $leave_balance, 'leave_types' => $leave_types]);
    }


    /**
     * @param $business_id
     * @param $business_member_id
     * @param Request $request
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function leaveBalanceDetails($business_id, $business_member_id, Request $request, TimeFrame $time_frame)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMemberById($business_member_id);
        /** @var Business $business */
        $business = $business_member->business;
        $leave_types = $business->leaveTypes()->withTrashed()->take(5)->select('id', 'title', 'total_days')->get()->toArray();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new LeaveBalanceDetailsTransformer($leave_types,$time_frame));
        $leave_balance = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['leave_balance_details' => $leave_balance]);
    }
}
