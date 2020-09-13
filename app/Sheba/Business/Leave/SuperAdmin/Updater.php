<?php namespace Sheba\Business\Leave\SuperAdmin;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\ModificationFields;
use Sheba\Dal\LeaveType\Contract as LeaveTypeRepo;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface as BusinessMemberRepo;
use Sheba\Business\Leave\SuperAdmin\LeaveEditType as Type;

class Updater
{
    use ModificationFields;

    private $leave;
    private $updateType;
    private $leaveTypeId;
    private $startDate;
    private $endDate;
    private $substituteId;
    private $previousLeaveTypeId;
    private $previousStartDate;
    private $previousEndDate;
    private $previousSubstituteId;
    private $leaveRepository;
    private $leaveLogRepo;
    private $leaveTypeRepo;
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;
    private $businessMemberRepo;

    public function __construct(LeaveRepository $leave_repository, LeaveLogRepo $leave_log_repo,
                                LeaveTypeRepo $leave_type_repo, BusinessMemberRepo $business_member_repo,
                                PushNotificationHandler $push_notification_handler)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveLogRepo = $leave_log_repo;
        $this->leaveTypeRepo = $leave_type_repo;
        $this->businessMemberRepo = $business_member_repo;
        $this->pushNotificationHandler = $push_notification_handler;
    }

    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function setUpdateType($update_type)
    {
        $this->updateType = $update_type;
        return $this;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = $leave_type_id;
        return $this;
    }

    public function setStartDate($start_date)
    {
        $this->startDate = $start_date;
        return $this;
    }

    public function setEndDate($end_date)
    {
        $this->endDate = $end_date;
        return $this;
    }

    public function setSubstituteId($substitute_id)
    {
        $this->substituteId = $substitute_id;
        return $this;
    }

    public function updateLeaveType()
    {
        $this->previousLeaveTypeId = $this->leave->leave_type_id;
        if ($this->previousLeaveTypeId === $this->leaveTypeId) return;
        $log_data = [
            'leave_id' => $this->leave->id,
            'type' => Type::LEAVE_TYPE,
            'from' => $this->previousLeaveTypeId,
            'to' => $this->leaveTypeId,
            'log' => "Super Admin edited the leave type from " . $this->getLeaveTypeName($this->previousLeaveTypeId) . ' to ' . $this->getLeaveTypeName($this->leaveTypeId),
            'is_changed_by_super' => 1,
        ];
        $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['leave_type_id' => $this->leaveTypeId]));
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
    }

    public function updateLeaveDate()
    {
        $this->previousStartDate = $this->leave->start_date->format('d/m/Y');
        $this->previousEndDate = $this->leave->end_date->format('d/m/Y');
        $new_start_date = $this->formatDate($this->startDate);
        $new_end_date = $this->formatDate($this->endDate);
        if($this->previousStartDate === $new_start_date) return;
        $data = [
            'start_date' => $this->startDate . ' ' . '00:00:00',
            'end_date' => $this->endDate . ' ' . '23:59:59'
        ];
        $log_data = [
            'leave_id' => $this->leave->id,
            'type' => Type::LEAVE_DATE,
            'from' => $this->previousStartDate . ' - ' . $this->previousEndDate,
            'to' => $new_start_date . ' - ' . $new_end_date,
            'log' => "Super Admin edited the leave dates from " . $this->previousStartDate . ' - ' . $this->previousEndDate . ' to ' . $new_start_date . ' - ' . $new_end_date,
            'is_changed_by_super' => 1,
        ];

        $this->leaveRepository->update($this->leave, $this->withUpdateModificationField($data));
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
    }

    public function updateSubstitute()
    {
        $this->previousSubstituteId = $this->leave->substitute_id;
        if($this->previousSubstituteId === $this->substituteId) return;
        $previous_substitute_name = $this->previousSubstituteId ? $this->getSubstituteName($this->previousSubstituteId) : 'None';
        $new_substitute_name = $this->substituteId ? $this->getSubstituteName($this->substituteId) : 'None';
        $log_data = [
            'leave_id' => $this->leave->id,
            'type' => Type::SUBSTITUTE,
            'from' => $this->previousSubstituteId ? $this->previousSubstituteId : 'None',
            'to' => $this->substituteId ? $this->substituteId : 'None',
            'log' => 'Super Admin changed substitute from ' . $previous_substitute_name . ' to ' . $new_substitute_name,
            'is_changed_by_super' => 1,
        ];
        $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['substitute_id' => $this->substituteId]));
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
        if ($this->substituteId) $this->sendPushToSubstitute($this->leave);
    }

    private function getLeaveTypeName($leave_type_id)
    {
        $leave_type = LeaveType::withTrashed()->findOrFail($leave_type_id);
        return $leave_type->title;
    }

    private function formatDate($date)
    {
        return Carbon::parse($date)->format('d/m/Y');
    }

    private function getSubstituteName($substitute_id)
    {
        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = $this->businessMemberRepo->find($substitute_id);
        /** @var Member $member */
        $substitute_member = $substitute_business_member ? $substitute_business_member->member : null;
        /** @var Profile $profile */
        $leave_substitute = $substitute_member ? $substitute_member->profile : null;

        return $leave_substitute->name;
    }

    public function sendPushToSubstitute(Leave $leave)
    {
        /** @var BusinessMember $business_member */
        $business_member = $leave->businessMember;
        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = $this->businessMemberRepo->find($this->substituteId);
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $leave_applicant = $member->profile->name;
        $topic = config('sheba.push_notification_topic_name.employee') . (int)$substitute_business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $start_date = $leave->start_date->format('d/m/Y');
        $end_date = $leave->end_date->format('d/m/Y');
        $notification_data = [
            "title" => 'Leave substitute',
            "message" => "You have been chosen as $leave_applicant's substitute from $start_date to $end_date",
            "event_type" => 'substitute',
            "event_id" => $leave->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $this->pushNotificationHandler->send($notification_data, $topic, $channel);
    }
}