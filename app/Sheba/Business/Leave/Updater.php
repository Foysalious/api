<?php namespace App\Sheba\Business\Leave;

use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\Leave\Model as Leave;
use DB;
use App\Sheba\Business\LeaveStatusChangeLog\Creator as LeaveStatusChangeLogCreator;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Updater
{
    private $leaveRepository;
    private $leave;
    private $status;
    private $leaveStatusLogCreator;
    private $businessMemberRepository;
    private $pushNotification;

    public function __construct(LeaveRepository $leave_repository, LeaveStatusChangeLogCreator $leave_status_change_log_creator, BusinessMemberRepositoryInterface $business_member_repo)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveStatusLogCreator = $leave_status_change_log_creator;
        $this->businessMemberRepository = $business_member_repo;
        $this->pushNotification = new PushNotificationHandler();
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

    public function updateStatus()
    {
        DB::transaction(function () {
            $previous_status = $this->leave->status;
            $this->leaveRepository->update($this->leave, ['status' => $this->status]);
            $this->leaveStatusLogCreator->setLeave($this->leave)->setPreviousStatus($previous_status)->setStatus($this->status)->create();
        });
        if ($this->status == 'accepted') $this->sendLeaveAcceptedNotification();
        elseif ($this->status == 'rejected') $this->sendLeaveRejectedNotification();
    }

    private function sendLeaveAcceptedNotification()
    {
        $business_member = $this->businessMemberRepository->where('id', $this->leave->business_member_id)->first();
        notify()->member($business_member->member)->send([
            'title' => 'Admin has accepted your leave request #' . $this->leave->id,
            'type' => 'Info',
            'event_type' => 'Sheba\Dal\Leave\Model',
            'event_id' => $this->leave->id,
            /*'link' => config('sheba.business_url') . '/dashboard/employee/leaves/'.$this->leave->id*/
        ]);
        $topic = config('sheba.push_notification_topic_name.employee') . $business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $this->pushNotification->send([
            "title" => 'New support created',
            "message" => 'Admin has accepted your leave request #' . $this->leave->id,
            "event_type" => 'leave',
            "event_id" => $this->leave->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ], $topic, $channel);
    }

    public function sendLeaveRejectedNotification()
    {
        $business_member = $this->businessMemberRepository->where('id', $this->leave->business_member_id)->first();
        notify()->member($business_member->member)->send([
            'title' => 'Admin has rejected your leave request #' . $this->leave->id,
            'type' => 'Info',
            'event_type' => 'Sheba\Dal\Leave\Model',
            'event_id' => $this->leave->id,
            /*'link' => config('sheba.business_url') . '/dashboard/employee/leaves/'.$this->leave->id*/
        ]);
        $topic = config('sheba.push_notification_topic_name.employee') . $business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $this->pushNotification->send([
            "title" => 'New support created',
            "message" => 'Admin has rejected your leave request #' . $this->leave->id,
            "event_type" => 'leave',
            "event_id" => $this->leave->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ], $topic, $channel);
    }
}