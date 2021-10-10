<?php namespace App\Http\Controllers\Employee;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Sheba\Business\ACL\AccessControl;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use App\Transformers\Business\LeaveListTransformer;
use App\Transformers\Business\LeaveTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Business\Leave\Breakdown\LeaveBreakdown;
use Sheba\Business\Leave\RejectReason\RejectReason;
use Sheba\Business\LeaveType\OtherSettings\BasicInfo as OthersInfo;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Dal\LeaveType\Model as LeaveType;
use App\Sheba\Business\Leave\Logs\Formatter as LogFormatter;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Throwable;
use DB;

class LeaveController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /**
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @param ApprovalRequestRepositoryInterface $approval_request_repository
     * @return JsonResponse
     */
    public function index(Request $request, LeaveRepoInterface $leave_repo, ApprovalRequestRepositoryInterface $approval_request_repository)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leaves = $leave_repo->getLeavesByBusinessMember($business_member)->orderBy('id', 'desc');
        if ($request->has('type')) $leaves = $leaves->where('leave_type_id', $request->type);
        $leaves = $leaves->get();
        $fractal = new Manager();
        $resource = new Collection($leaves, new LeaveListTransformer());
        $leaves = $fractal->createData($resource)->toArray()['data'];
        $pending_approval_requests = $approval_request_repository->getPendingApprovalRequestByBusinessMember($business_member);
        $pending_approval_requests_count = $approval_request_repository->countPendingLeaveApprovalRequests($business_member);
        return api_response($request, null, 200, [
            'leaves' => $leaves,
            'pending_approval_request' => $pending_approval_requests,
            'approval_requests' => ['pending_request' => $pending_approval_requests_count]
        ]);
    }

    /**
     * @param $leave
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @param LogFormatter $log_formatter
     * @return JsonResponse
     */
    public function show($leave, Request $request, LeaveRepoInterface $leave_repo, LogFormatter $log_formatter)
    {
        $leave = $leave_repo->find($leave);
        if (!$leave) return api_response($request, null, 404);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        $is_substitute_required = $this->isNeedSubstitute($business_member) ? 1 : 0;
        /*if (!$leave || $leave->business_member_id != $business_member->id)
            return api_response($request, null, 403);*/

        $leave = $leave->load(['leaveType' => function ($q) {
            return $q->withTrashed();
        }]);

        $leave_log_details = $log_formatter->setLeave($leave)->format();

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($leave, new LeaveTransformer($business, $leave_log_details, $is_substitute_required));
        $leave = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $leave, 200, ['leave' => $leave]);
    }

    /**
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request, LeaveCreator $leave_creator)
    {
        $validation_data = [
            'start_date' => 'required|before_or_equal:end_date',
            'end_date' => 'required',
            'attachments.*' => 'file',
            'is_half_day' => 'sometimes|required|in:1,0',
            'half_day_configuration' => "required_if:is_half_day,==,1|in:first_half,second_half"
        ];

        $business_member = $this->getBusinessMember($request);
        if ($this->isNeedSubstitute($business_member)) $validation_data['substitute'] = 'required|integer';
        $this->validate($request, $validation_data);

        $member = $this->getMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $substitute = $request->has('substitute') ? $request->substitute : null;
        $is_half_day = $request->has('is_half_day') ? $request->is_half_day : 0;

        $leave = $leave_creator->setTitle($request->title)
            ->setSubstitute($substitute)
            ->setBusinessMember($business_member)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setIsHalfDay($is_half_day)
            ->setHalfDayConfigure($request->half_day_configuration)
            ->setNote($request->note)
            ->setCreatedBy($member);

        if ($request->attachments && is_array($request->attachments)) $leave_creator->setAttachments($request->attachments);
        if ($leave_creator->hasError())
            return api_response($request, null, $leave_creator->getErrorCode(), ['message' => $leave_creator->getErrorMessage()]);

        $leave = $leave->create();
        return api_response($request, null, 200, ['leave' => $leave->id]);
    }

    /**
     * @param $leave
     * @param Request $request
     * @param AccessControl $accessControl
     * @param LeaveUpdater $leaveUpdater
     * @return JsonResponse
     */
    public function updateStatus($leave, Request $request, AccessControl $accessControl, LeaveUpdater $leaveUpdater)
    {

        $business_member = $this->getBusinessMember($request);
        $this->setModifier($business_member->member);
        $accessControl->setBusinessMember($business_member);
        if (!$accessControl->hasAccess('leave.rw')) return api_response($request, null, 403);
        $leave = Leave::findOrFail((int)$leave);
        $leaveUpdater->setLeave($leave)->setStatus($request->status)->updateStatus();
        return api_response($request, null, 200);
    }

    /**
     * @param $leave
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @param LeaveUpdater $leave_updater
     * @return JsonResponse
     */
    public function cancel($leave, Request $request, LeaveRepoInterface $leave_repo, LeaveUpdater $leave_updater)
    {
        $this->validate($request, ['status' => 'required']);
        /** @var Leave $leave */
        $leave = $leave_repo->find((int)$leave);

        $current_time = Carbon::now();
        $leave_end_time = $leave->end_date;

        if ($current_time > $leave_end_time)
            return api_response($request, null, 404, ['message' => "You can't cancel this request anymore."]);

        $business_member = $this->getBusinessMember($request);

        if ($leave->business_member_id != $business_member->id)
            return api_response($request, null, 404, ['message' => "You are not authorised to cancel the request."]);

        $this->setModifier($business_member->member);
        $approval_requests = $leave->requests;

        $leave_updater->setLeave($leave)->setApprovalRequests($approval_requests)->setBusinessMember($business_member)->setStatus($request->status)->updateStatus();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function getLeaveTypes(Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        /** @var Business $business */
        $business = $this->getBusiness($request);
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leave_types = $leave_types_repo->getAllLeaveTypesByBusinessMember($business_member);

        foreach ($leave_types as $leave_type) {
            /** @var LeaveType $leaves_taken */
            $leaves_taken = $business_member->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type->id);
            $leave_type->available_days = $leave_type->total_days - $leaves_taken;
        }

        $half_day_configuration = null;
        if ($business->is_half_day_enable) {
            $half_day_configuration = $business->getBusinessHalfDayConfiguration();
            foreach ($half_day_configuration as $key => $item) {
                $half_day_configuration[$key]['start_time'] = Carbon::parse($half_day_configuration[$key]['start_time'])->format('h:i A');
                $half_day_configuration[$key]['end_time'] = Carbon::parse($half_day_configuration[$key]['end_time'])->format('h:i A');
            }
        }

        $fiscal_year_time_frame = $business_member->getBusinessFiscalPeriod();

        $fiscal_year = [
            'start_date' => $fiscal_year_time_frame->start->format('Y-m-d'),
            'end_date' => $fiscal_year_time_frame->end->format('Y-m-d')
        ];

        return api_response($request, null, 200, ['leave_types' => $leave_types, 'half_day_configuration' => $half_day_configuration, 'fiscal_year' => $fiscal_year]);
    }

    /**
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @param LeaveBreakdown $leave_breakdown
     * @return JsonResponse
     */
    public function getLeaveDates(Request $request, LeaveRepoInterface $leave_repo, LeaveBreakdown $leave_breakdown)
    {
        /**@var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        $leaves = $leave_repo->builder()
            ->select('id', 'title', 'business_member_id', 'leave_type_id', 'start_date', 'end_date', 'is_half_day', 'half_day_configuration', 'status')
            ->where('business_member_id', $business_member->id)
            ->where(function ($query) {
                $query->where('status', 'pending')->orWhere('status', 'accepted');
            })->where('start_date', '>=', Carbon::now()->subMonths(1)->toDateString())
            ->get();

        list($leaves, $leaves_date_with_half_and_full_days) = $leave_breakdown->formatLeaveAsDateArray($leaves);

        $full_day_leaves = [];
        $half_day_leaves = [];

        foreach ($leaves_date_with_half_and_full_days as $date => $leaves_date_with_half_and_full_day) {
            !$leaves_date_with_half_and_full_day['is_half_day_leave'] ? $full_day_leaves[] = $leaves_date_with_half_and_full_day['date'] :
                array_push($half_day_leaves, [
                    'date' => $leaves_date_with_half_and_full_day['date'],
                    'which_half_day' => $leaves_date_with_half_and_full_day['which_half_day'],
                ]);
        }

        return api_response($request, null, 200, ['full_day_leaves' => $full_day_leaves, 'half_day_leaves' => $half_day_leaves]);
    }

    /**
     * @param Request $request
     * @param RejectReason $reject_reason
     * @return JsonResponse
     */
    public function rejectReasons(Request $request, RejectReason $reject_reason)
    {
        $reject_reasons = $reject_reason->reasons();

        return api_response($request, $reject_reasons, 200, ['reject_reasons' => $reject_reasons]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLeaveSettings(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $is_substitute_required = $this->isNeedSubstitute($business_member) ? 1 : 0;
        $settings = ['is_substitute_required' => $is_substitute_required];

        return api_response($request, null, 200, ['settings' => $settings]);
    }

    public function update($leave, Request $request, LeaveUpdater $leave_updater, LeaveRepoInterface $leave_repo)
    {
        $member = $this->getMember($request);
        $business_member = $this->getBusinessMember($request);
        $this->setModifier($member);
        $leave = $leave_repo->find((int)$leave);
        $leave_updater->setLeave($leave)->setBusinessMember($business_member)
            ->setSubstitute($request->substitute_id)
            ->setNote($request->note)->setAttachments($request->attachments)->setCreatedBy($member);
        if ($leave_updater->hasError()) return api_response($request, null, $leave_updater->getErrorCode(), ['message' => $leave_updater->getErrorMessage()]);
        $leave_updater->update();
        return api_response($request, null, 200);
    }

    /**
     * @param $leave
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @param LeaveUpdater $leave_updater
     * @return JsonResponse
     */
    public function statusUpdate($leave, Request $request, LeaveRepoInterface $leave_repo, LeaveUpdater $leave_updater)
    {
        $this->validate($request, [
            'status' => 'required',
        ]);
        $member = $this->getMember($request);
        $business_member = $this->getBusinessMember($request);
        $this->setModifier($member);
        $leave = $leave_repo->find((int)$leave);
        $leave_updater->setLeave($leave)->setBusinessMember($business_member)->setStatus($request->status)->statusUpdate();

        return api_response($request, null, 200);
    }

    public function getPolicySettings(Request $request, LeaveTypesRepoInterface $leave_types_repo, BusinessMemberRepositoryInterface $business_member_repo, OthersInfo $info)
    {
        $business = $this->getBusiness($request);
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $leave_types = $leave_types_repo->getAllLeaveTypesByBusiness($business);
        $others_info = $info->setBusiness($business)->getInfo();
        return api_response($request, null, 200, ['leave_types' => $leave_types, 'is_sandwich_enable' => $others_info['sandwich_leave']]);
    }

    /**
     * @param BusinessMember $business_member
     * @return bool
     */
    private function isNeedSubstitute(BusinessMember $business_member)
    {
        $leave_approvers = [];
        ApprovalFlow::with('approvers')->where('type', Type::LEAVE)->get()->each(function ($approval_flow) use (&$leave_approvers) {
            $leave_approvers = array_unique(array_merge($leave_approvers, $approval_flow->approvers->pluck('id')->toArray()));
        });
        if (in_array($business_member->id, $leave_approvers)) return true;
        return false;
    }
}
