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
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\Leave\Contract as LeaveRepoInterface;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\Helpers\TimeFrame;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\ModificationFields;
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
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);

            $leaves = $leave_repo->getLeavesByBusinessMember($business_member)->orderBy('id', 'desc');
            if ($request->has('type')) $leaves = $leaves->where('leave_type_id', $request->type);
            $leaves = $leaves->get();
            $fractal = new Manager();
            $resource = new Collection($leaves, new LeaveListTransformer());
            $leaves = $fractal->createData($resource)->toArray()['data'];
            $pending_approval_requests = $approval_request_repository->getPendingApprovalRequestByBusinessMember($business_member);
            return api_response($request, null, 200, [
                'leaves' => $leaves,
                'pending_approval_request' => $pending_approval_requests
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $leave
     * @param Request $request
     * @param LeaveRepoInterface $leave_repo
     * @return JsonResponse
     */
    public function show($leave, Request $request, LeaveRepoInterface $leave_repo)
    {
        $leave = $leave_repo->find($leave);
        $business_member = $this->getBusinessMember($request);
        if (!$leave || $leave->business_member_id != $business_member->id) return api_response($request, null, 403);
        $leave = $leave->load(['leaveType' => function ($q) {
            return $q->withTrashed();
        }]);

        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Item($leave, new LeaveTransformer());
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
        $leave = $leave_creator->setTitle($request->title)
            ->setSubstitute($substitute)
            ->setBusinessMember($business_member)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setIsHalfDay($request->is_half_day)
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

        $leave_types = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);

        foreach ($leave_types as $leave_type) {
            /** @var LeaveType $leaves_taken */
            $leaves_taken = $business_member->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($leave_type->id);
            $leave_type->available_days = $leave_type->total_days - $leaves_taken;
        }
        $is_half_day_enable = $business->is_half_day_enable;
        $half_day_configuration = $is_half_day_enable ? json_decode($business->half_day_configuration, 1) : null;
        return api_response($request, null, 200, [
            'leave_types' => $leave_types,
            'is_half_day_enable' => $is_half_day_enable,
            'half_day_configuration' => $half_day_configuration
        ]);
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
}
