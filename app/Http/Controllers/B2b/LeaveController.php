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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Business\ApprovalRequest\Updater;
use Sheba\Business\ApprovalRequest\Leave\SuperAdmin\StatusUpdater as StatusUpdater;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Business\Leave\Balance\Excel as BalanceExcel;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\ApprovalRequestPresenter as ApprovalRequestPresenter;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Reports\Exceptions\NotAssociativeArray;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Business\Leave\SuperAdmin\Updater as LeaveUpdater;
use Sheba\Business\Leave\SuperAdmin\LeaveEditType as EditType;
use Sheba\Business\Leave\Adjustment\Approvers as AdjustmentApprovers;

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
        $this->validate($request, ['sort' => 'sometimes|required|string|in:asc,desc']);

        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 420);
        /** @var Business $business */
        $business = $request->business;

        list($offset, $limit) = calculatePagination($request);

        $leave_approval_requests = $this->approvalRequestRepo->getApprovalRequestByBusinessMemberFilterBy($business_member, Type::LEAVE);
        if ($request->has('status')) $leave_approval_requests = $leave_approval_requests->where('status', $request->status);

        /*if ($request->has('department') || $request->has('employee') || $request->has('search')) {
            $leave_approval_requests = $this->filterWithDepartmentOrEmployeeOrSearchWithEmployee($leave_approval_requests, $request);
        }*/
        if ($request->has('department')) $leave_approval_requests = $this->filterWithDepartment($leave_approval_requests, $request);
        if ($request->has('employee')) $leave_approval_requests = $this->filterWithEmployee($leave_approval_requests, $request);
        if ($request->has('search')) $leave_approval_requests = $this->searchWithEmployeeName($leave_approval_requests, $request);

        $total_leave_approval_requests = $leave_approval_requests->count();
        $leave_approval_requests = $this->sortByStatus($leave_approval_requests);
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
            $resource = new Item($approval_request, new ApprovalRequestTransformer($profile, $business));
            $approval_request = $manager->createData($resource)->toArray()['data'];

            array_push($leaves, $approval_request);
        }
        if ($request->has('sort')) {
            $leaves = $this->leaveOrderBy($leaves, $request->sort)->values();
        }

        if (count($leaves) > 0) return api_response($request, $leaves, 200, [
            'leaves' => $leaves,
            'total_leave_requests' => $total_leave_approval_requests,
        ]);
        else return api_response($request, null, 404);
    }

    /**
     * @param $business
     * @param $approval_request
     * @param Request $request
     * @param LeaveLogRepo $leave_log_repo
     * @return JsonResponse
     */
    public function show($business, $approval_request, Request $request, LeaveLogRepo $leave_log_repo)
    {
        /** @var Business $business */
        $business = $request->business;
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
        $resource = new Item($approval_request, new LeaveRequestDetailsTransformer($business, $profile, $role, $leave_log_repo));
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
            return str_contains(strtoupper($profile->name), strtoupper($request->search));
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
     * @param $leave_approval_requests
     * @param Request $request
     * @return mixed
     */
    private function filterWithDepartmentOrEmployeeOrSearchWithEmployee($leave_approval_requests, Request $request)
    {
        return $leave_approval_requests->filter(function ($approval_request) use ($request) {
            /** @var Leave $requestable */
            $requestable = $approval_request->requestable;
            /** @var BusinessMember $business_member */
            $business_member = $requestable->businessMember;
            /** @var Member $member */
            $member = $business_member->member;
            /** @var BusinessRole $role */
            $role = $business_member->role;
            /** @var Profile $profile */
            $profile = $member->profile;

            if ($request->has('department') && $role) return $role->businessDepartment->id == $request->department;
            if ($request->has('employee')) return $member->id == $request->employee;
            if ($request->has('search')) return str_contains(strtoupper($profile->name), strtoupper($request->search));
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
                'status' => ApprovalRequestPresenter::statuses()[$approval_request->status],
            ]);
        }
        return $approvers;
    }

    /**
     * @param Request $request
     * @param TimeFrame $time_frame
     * @param BalanceExcel $balance_excel
     * @return JsonResponse | void
     * @throws NotAssociativeArray
     */
    public function allLeaveBalance(Request $request, TimeFrame $time_frame, BalanceExcel $balance_excel)
    {
        $this->validate($request, [
            'sort' => 'sometimes|string|in:asc,desc',
            'file' => 'sometimes|string|in:excel'
        ]);

        list($offset, $limit) = calculatePagination($request);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 420);
        $time_frame = $business_member->getBusinessFiscalPeriod();
        /** @var Business $business */
        $business = $business_member->business;

        $leave_types = [];
        $business->leaveTypes()->with(['leaves' => function ($q) {
            return $q->accepted();
        }])
            ->withTrashed()->select('id', 'title', 'total_days', 'deleted_at')
            ->get()
            ->each(function ($leave_type) use (&$leave_types) {
                if ($leave_type->trashed() && $leave_type->leaves->isEmpty()) return;
                $leave_type_data = [
                    'id' => $leave_type->id,
                    'title' => $leave_type->title,
                    'total_days' => $leave_type->total_days
                ];
                array_push($leave_types, $leave_type_data);
            });

        $members = $business->members()->select('members.id', 'profile_id')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile');
            },
            'businessMember' => function ($q) use ($time_frame) {
                $q->with([
                    'role' => function ($query) {
                        $query->select('business_roles.id', 'business_department_id', 'name')->with(['businessDepartment' => function ($query) {
                            $query->select('business_departments.id', 'business_id', 'name');
                        }]);
                    },
                    'leaves' => function ($q) use ($time_frame) {
                        $q->accepted()->between($time_frame)->with([
                            'leaveType' => function ($query) {
                                $query->withTrashed()->select('id', 'business_id', 'title', 'total_days', 'deleted_at');
                            }])->select('id', 'title', 'business_member_id', 'leave_type_id', 'start_date', 'end_date', 'note', 'total_days', 'left_days', 'status');
                    }
                ])->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id');
            }
        ])->get();

        if ($request->has('department') || $request->has('search'))
            $members = $this->membersFilterByDeptSearchByName($members, $request);

        $total_records = $members->count();
        if ($request->has('limit')) $members = $members->splice($offset, $limit);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($members, new LeaveBalanceTransformer($leave_types, $business));
        $leave_balances = $manager->createData($resource)->toArray()['data'];

        if ($request->has('sort')) {
            $leave_balances = $this->leaveBalanceOrderBy($leave_balances, $request->sort)->values()->toArray();
        }

        if ($request->file == 'excel') {
            return $balance_excel->setBalance($leave_balances)->setLeaveType($leave_types)->get();
        }

        return api_response($request, null, 200, [
            'leave_balances' => $leave_balances,
            'leave_types' => $leave_types,
            'total_records' => $total_records
        ]);
    }

    /**
     * @param $business_id
     * @param $business_member_id
     * @param Request $request
     * @param TimeFrame $time_frame
     * @param LeaveLogRepo $leave_log_repo
     * @return JsonResponse
     */
    public function leaveBalanceDetails($business_id, $business_member_id, Request $request, TimeFrame $time_frame, LeaveLogRepo $leave_log_repo)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMemberById($business_member_id);
        /** @var Business $business */
        $business = $business_member->business;
        $leave_types = $business->leaveTypes()->withTrashed()->select('id', 'title', 'total_days', 'deleted_at')->get();

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_member, new LeaveBalanceDetailsTransformer($leave_types, $time_frame, $leave_log_repo));
        $leave_balance = $manager->createData($resource)->toArray()['data'];

        if ($request->file == 'pdf') {
            return App::make('dompdf.wrapper')
                ->loadView('pdfs.employee_leave_balance', compact('leave_balance'))
                ->download("employee_leave_balance.pdf");
        }

        return api_response($request, null, 200, ['leave_balance_details' => $leave_balance]);
    }

    /**
     * @param $leave_balances
     * @param string $sort
     * @return Collection
     */
    private function leaveBalanceOrderBy($leave_balances, $sort = 'asc')
    {
        $sort_by = ($sort == 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($leave_balances)->$sort_by(function ($leave_balance, $key) {
            return strtoupper($leave_balance['employee_name']);
        });
    }

    /**
     * @param $leaves
     * @param string $sort
     * @return mixed
     */
    private function leaveOrderBy($leaves, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return collect($leaves)->$sort_by(function ($leave, $key) {
            return strtoupper($leave['leave']['name']);
        });
    }

    private function sortByStatus($leaves)
    {
        $pending = $leaves->where('status', Status::PENDING)->sortByDesc('created_at');
        $accepted = $leaves->where('status', Status::ACCEPTED)->sortByDesc('created_at');
        $rejected = $leaves->where('status', Status::REJECTED)->sortByDesc('created_at');

        return $pending->merge($accepted)->merge($rejected);
    }

    /**
     * @param $members
     * @param Request $request
     * @return mixed
     */
    private function membersFilterByDeptSearchByName($members, Request $request)
    {
        return $members->filter(function ($member) use ($request) {
            $is_dept_matched = false;
            $is_name_matched = false;

            if ($request->has('department')) {
                /** @var BusinessMember $business_member */
                $business_member = $member->businessMemberWithoutStatusCheck();
                /** @var BusinessRole $role */
                $role = $business_member->role;
                if ($role) $is_dept_matched = $role->businessDepartment->id == $request->department;
            }

            if ($request->has('search')) {
                /** @var Profile $profile */
                $profile = $member->profile;
                $is_name_matched = str_contains(strtoupper($profile->name), strtoupper($request->search));
            }

            if ($request->has('department') && $request->has('search')) return $is_dept_matched && $is_name_matched;
            if ($request->has('department') || $request->has('search')) return $is_dept_matched || $is_name_matched;
        });
    }

    public function statusUpdateBySuperAdmin(Request $request, LeaveRepository $leave_repo, StatusUpdater $updater)
    {
        $this->validate($request, [
            'leave_id' => 'required|string',
            'status' => 'required|string',
        ]);

        $leave = $leave_repo->find($request->leave_id);

        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;

        $updater->setLeave($leave)->setStatus($request->status)->setBusinessMember($business_member)->updateStatus();

        return api_response($request, null, 200);
    }

    public function infoUpdateBySuperAdmin(Request $request, LeaveRepository $leave_repo, LeaveUpdater $updater)
    {
        $this->validate($request, [
            'leave_id' => 'required',
            'data' => 'required|string',
        ]);
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);
        $leave = $leave_repo->find($request->leave_id);

        $edit_values = json_decode($request->data);

        foreach ($edit_values as $value) {
            if ($value->type === EditType::LEAVE_TYPE) {
                $updater->setLeave($leave)->setUpdateType($value->type)->setLeaveTypeId($value->leave_type_id)->updateLeaveType();
            }
            if ($value->type === EditType::LEAVE_DATE) {
                $updater->setLeave($leave)->setUpdateType($value->type)->setStartDate($value->start_date)->setEndDate($value->end_date)->updateLeaveDate();
            }
            if ($value->type === EditType::SUBSTITUTE) {
                $updater->setLeave($leave)->setUpdateType($value->type)->setSubstituteId($value->substitute_id)->updateSubstitute();
            }
        }

        return api_response($request, null, 200);
    }


    /**
     * @param $business_id
     * @param Request $request
     * @param AdjustmentApprovers $approvers
     * @return JsonResponse
     */
    public function getSuperAdmins($business_id, Request $request, AdjustmentApprovers $approvers)
    {
        $approvers = $approvers->setBusiness($request->business)->getApprovers();
        return api_response($request, null, 200, [
            'approvers' => $approvers
        ]);
    }
}
