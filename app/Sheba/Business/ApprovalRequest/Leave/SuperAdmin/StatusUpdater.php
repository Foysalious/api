<?php namespace Sheba\Business\ApprovalRequest\Leave\SuperAdmin;

use App\Models\BusinessMember;
use Illuminate\Support\Facades\DB;
use Sheba\Business\LeaveRejection\Requester as LeaveRejectionRequester;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\LeaveRejection\LeaveRejectionRepository;
use Sheba\ModificationFields;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Business\Leave\SuperAdmin\LeaveEditType as EditType;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface as BusinessMemberRepo;
use Throwable;

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
    /*** @var LeaveRejectionRequester */
    private $leaveRejectionRequester;
    private $leaveRejectionData;
    /*** @var LeaveRejectionRepository */
    private $leaveRejectionRepository;


    public function __construct(LeaveRepository $leave_repository, BusinessMemberRepo $business_member_repo,
                                LeaveLogRepo $leave_log_repo, PushNotificationHandler $push_notification_handler, LeaveRejectionRepository $leave_rejection_repository)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveLogRepo = $leave_log_repo;
        $this->isLeaveAdjustment = false;
        $this->businessMemberRepo = $business_member_repo;
        $this->pushNotificationHandler = $push_notification_handler;
        $this->leaveRejectionRepository = $leave_rejection_repository;
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

    public function setLeaveRejectionRequester(LeaveRejectionRequester $leave_rejection_requester)
    {
        $this->leaveRejectionRequester = $leave_rejection_requester;
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
       DB::transaction(function () {
            $left_leave_days = $this->calculateDays($this->status);
            $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['status' => $this->status, 'left_days' => $left_leave_days]));
            $this->makeLeaveRejectionData();
            $this->leaveRejectionRepository->create($this->leaveRejectionData);
            $this->createLog();
        });
        try {
            if ($this->status === Status::CANCELED) {
                $this->sendPushNotification();
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return;
        }
    }

    /**
     * @return mixed
     */
    private function makeLeaveRejectionData()
    {
        if ($this->leaveRejectionRequester->getNote()) $this->leaveRejectionData['note'] = $this->leaveRejectionRequester->getNote();
        $this->leaveRejectionData['leave_id'] = $this->leave->id;
        $this->leaveRejectionData['is_rejected_by_super_admin'] = 1;
        return $this->leaveRejectionData;
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
        $sound = config('sheba.push_notification_sound.employee');
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

        $this->pushNotificationHandler->send($notification_data, $topic, $channel, $sound);
    }

    private function calculateDays($type)
    {
        $business_member = $this->leave->businessMember;
        $used_leave_days = $business_member->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($this->leave->leave_type_id);
        $leave_type_total_days = $business_member->getTotalLeaveDaysByLeaveTypes($this->leave->leave_type_id);
        $leave_days = $this->leave->total_days;
        if ($type == Status::ACCEPTED) return (($leave_type_total_days - $used_leave_days) - $leave_days);
        if ($type == Status::REJECTED || $type == Status::CANCELED) return (($leave_type_total_days - $used_leave_days) + $leave_days);
    }
}
