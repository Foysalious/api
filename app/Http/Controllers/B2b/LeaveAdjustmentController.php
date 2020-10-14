<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\Leave\SuperAdmin\LeaveEditType as Type;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveType\Contract as LeaveTypeRepo;
use Sheba\Helpers\HasErrorCodeAndMessage;
use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;

class LeaveAdjustmentController extends Controller
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $leaveTypeRepo;
    private $leaveLogRepo;

    public function __construct(LeaveTypeRepo $leave_type_repo, LeaveLogRepo $leave_log_repo)
    {
        $this->leaveTypeRepo = $leave_type_repo;
        $this->leaveLogRepo = $leave_log_repo;
    }


    public function leaveAdjustment(Request $request, LeaveCreator $leave_creator)
    {
        $validation_data = [
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|before_or_equal:end_date',
            'end_date' => 'required',
            'note' => 'required',
            'is_half_day' => 'sometimes|required|in:1,0',
            'half_day_configuration' => "required_if:is_half_day,==,1|in:first_half,second_half",
            'approver_id' => 'required|integer',
        ];
        $this->validate($request, $validation_data);

        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        if (!$business_member) return api_response($request, null, 404);

        $leave = $leave_creator->setIsLeaveAdjustment(true)
            ->setTitle('Manual Leave Adjustment')
            ->setBusinessMember($business_member)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setIsHalfDay($request->is_half_day)
            ->setHalfDayConfigure($request->half_day_configuration)
            ->setNote($request->note)
            ->setApproverId($request->approver_id);

        if ($leave_creator->hasError())
            return api_response($request, null, $leave_creator->getErrorCode(), ['message' => $leave_creator->getErrorMessage()]);

        $leave = $leave->create();
        $this->storeLeaveLog($leave);

        return api_response($request, null, 200, ['leave' => $leave->id]);
    }

    private function storeLeaveLog($leave)
    {
        $leave_type = $this->leaveTypeRepo->find($leave->leave_type_id);
        $log_data = [
            'leave_id' => $leave->id,
            'type' => Type::LEAVE_ADJUSTMENT,
            'log' => $leave->total_days . ' ' . $leave_type->title . ' were manually synced in leave balance record for this coworker.',
        ];
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
    }
}