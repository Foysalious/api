<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\LeaveV2\Creator as LeaveCreator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Sheba\Dal\ApprovalFlow\Model as ApprovalFlow;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\ModificationFields;

class LeaveV2Controller extends Controller
{
    use ModificationFields, BusinessBasicInformation;

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