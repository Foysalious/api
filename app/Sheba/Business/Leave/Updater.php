<?php namespace App\Sheba\Business\Leave;

use App\Models\BusinessMember;
use App\Sheba\Attachments\Attachments;
use Exception;
use Illuminate\Http\UploadedFile;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Contract as LeaveRepository;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;
use Sheba\Dal\Leave\Model as Leave;
use DB;
use App\Sheba\Business\LeaveStatusChangeLog\Creator as LeaveStatusChangeLogCreator;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Updater
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $leaveRepository;
    private $leave;
    private $status;
    private $leaveStatusLogCreator;
    private $businessMemberRepository;
    private $pushNotification;
    private $member;
    private $substitute;
    private $note;
    /**@var BusinessMember $businessMember */
    private $businessMember;
    private $createdBy;

    /** @var UploadedFile[] */
    private $attachments = [];
    private $data = [];
    /** @var Attachments $attachmentManager */
    private $attachmentManager;

    /**
     * Updater constructor.
     * @param LeaveRepository $leave_repository
     * @param LeaveStatusChangeLogCreator $leave_status_change_log_creator
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param Attachments $attachment_manager
     */
    public function __construct(LeaveRepository $leave_repository, LeaveStatusChangeLogCreator $leave_status_change_log_creator, BusinessMemberRepositoryInterface $business_member_repo, Attachments $attachment_manager)
    {
        $this->leaveRepository = $leave_repository;
        $this->leaveStatusLogCreator = $leave_status_change_log_creator;
        $this->businessMemberRepository = $business_member_repo;
        $this->pushNotification = new PushNotificationHandler();
        $this->attachmentManager = $attachment_manager;
    }

    /**
     * @param Leave $leave
     * @return $this
     */
    public function setLeave(Leave $leave)
    {
        $this->leave = $leave;
        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $attachments UploadedFile[]
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * @param $substitute_id
     * @return $this
     */
    public function setSubstitute($substitute_id)
    {
        $this->substitute = $substitute_id;
        if ($this->substitute == $this->businessMember->id) {
            $this->setError(422, 'You can\'t be your own substitute!');
            return $this;
        }
        return $this;
    }

    /**
     * @param $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param $created_by
     * @return $this
     */
    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
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
        return $this;
    }

    /**
     * @param Leave $leave
     */
    private function createAttachments(Leave $leave)
    {
        foreach ($this->attachments as $attachment) {
            $this->attachmentManager->setAttachableModel($leave)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
        }
    }

    public function updateStatus()
    {
        $this->setModifier($this->member);
        DB::transaction(function () {
            $previous_status = $this->leave->status;
            $this->leaveRepository->update($this->leave, $this->withUpdateModificationField(['status' => $this->status]));
            $this->leaveStatusLogCreator->setLeave($this->leave)->setPreviousStatus($previous_status)->setStatus($this->status)
                ->setBusinessMember($this->businessMember)
                ->create();
        });

        try {
            $this->sendNotification($this->status);
        } catch (Exception $e) {
        }
    }

    /**
     * @param $status
     * @throws Exception
     */
    public function sendNotification($status)
    {
        $status = LeaveStatusPresenter::statuses()[$status];
        $business_member = $this->businessMemberRepository->where('id', $this->leave->business_member_id)->first();
        $sheba_notification_data = [
            'title' => "Your leave request has been $status",
            'type' => 'Info',
            'event_type' => 'Sheba\Dal\Leave\Model',
            'event_id' => $this->leave->id,
            /*'link' => config('sheba.business_url') . '/dashboard/employee/leaves/'.$this->leave->id*/
        ];
        notify()->member($business_member->member)->send($sheba_notification_data);

        $topic = config('sheba.push_notification_topic_name.employee') . $business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $push_notification_data = [
            "title" => 'Leave request update',
            "message" => "Your leave request has been $status",
            "event_type" => 'leave',
            "event_id" => $this->leave->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $this->pushNotification->send($push_notification_data, $topic, $channel);
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->leaveRepository->update($this->leave, $this->withUpdateModificationField($this->data));
            if ($this->attachments) $this->createAttachments($this->leave);
        });
    }

    private function makeData()
    {
        $this->data['note'] = $this->note;
        if ($this->substitute) $this->data['substitute_id'] = $this->substitute;
    }
}
