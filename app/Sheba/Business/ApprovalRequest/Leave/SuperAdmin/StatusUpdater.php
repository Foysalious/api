<?php namespace Sheba\Business\ApprovalRequest\Leave\SuperAdmin;

use App\Models\BusinessMember;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Business\Leave\SuperAdmin\LeaveEditType as EditType;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface as BusinessMemberRepo;

class StatusUpdater
{
    use ModificationFields;

    private $leaveRepository;
    private $leave;
    private $status;
    private $member;
    private $leaveLogRepo;
    private $previousStatus;
    private $isLeaveAdjustment;
    /**@var BusinessMember $businessMember */
    private $businessMember;
    private $businessMemberRepo;
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;


    public function __construct(LeaveRepository $leave_repository, BusinessMemberRepo $business_member_repo,
                                LeaveLogRepo $leave_log_repo, PushNotificationHandler $push_notification_handler)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveLogRepo = $leave_log_repo;
        $this->isLeaveAdjustment = false;
        $this->businessMemberRepo = $business_member_repo;
        $this->pushNotificationHandler = $push_notification_handler;
    }

    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setIsLeaveAdjustment($is_leave_adjustment)
    {
        $this->isLeaveAdjustment = $is_leave_adjustment;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $business_member->member;
        $this->setModifier($this->member);
        return $this;
    }

    public function updateStatus()
    {
        $this->previousStatus = $this->leave->status;
        $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['status' => $this->status]));
        $this->createLog();
        try {
            if ($this->status === Status::CANCELED) {
                $this->sendPushNotification();
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return;
        }
    }

    private function createLog()
    {
        $data = $this->withCreateModificationField([
            'leave_id' => $this->leave->id,
            'type' => $this->isLeaveAdjustment ? EditType::LEAVE_ADJUSTMENT : EditType::STATUS,
            'from' => $this->previousStatus,
            'to' => $this->status,
            'log' => 'Super Admin changed this leave status from ' . $this->formatText($this->previousStatus) . ' to ' . $this->formatText($this->status),
            'is_changed_by_super' => 1,
        ]);

        $this->leaveLogRepo->create($data);
    }

    private function formatText($value)
    {
        if ($value === Status::ACCEPTED) {
            return 'Approved';
        }
        return ucfirst($value);
    }

    private function sendPushNotification()
    {
        $business_member = $this->leave->businessMember;
        $member = $business_member->member;
        $leave_applicant = $member->profile->name;
        $this->sendNotification($business_member, $leave_applicant, 'leave', $this->leave->id);
        foreach ($this->leave->requests as $approval_request) {
            $business_member_id = $approval_request->approver_id;
            $approver_business_member = $this->businessMemberRepo->find($business_member_id);
            $this->sendNotification($approver_business_member, $leave_applicant, 'leave_request', $approval_request->id);
        }
    }

    private function sendNotification($business_member, $applicant_name, $event_type, $event_id)
    {
        $topic = config('sheba.push_notification_topic_name.employee') . (int)$business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $start_date = $this->leave->start_date->format('d/m/Y');
        $end_date = $this->leave->end_date->format('d/m/Y');
        $notification_data = [
            "title" => 'Leave Canceled',
            "message" => "$applicant_name's leave from $start_date to $end_date has been Cancelled",
            "event_type" => $event_type,
            "event_id" => $event_id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $this->pushNotificationHandler->send($notification_data, $topic, $channel);
    }
}