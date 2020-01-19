<?php namespace Sheba\Business\Support;


use App\Models\Member;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use DB;
use Sheba\Dal\Support\Model as Support;

class Creator
{
    private $supportRepository;
    private $businessMemberRepository;
    private $member;
    private $description;
    private $pushNotification;

    public function __construct(SupportRepositoryInterface $support_repository, BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->supportRepository = $support_repository;
        $this->businessMemberRepository = $business_member_repository;
        $this->pushNotification = new PushNotificationHandler();
    }

    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function create()
    {
        $support = null;
        DB::transaction(function () use (&$support) {
            $support = $this->supportRepository->create([
                'member_id' => $this->member->id,
                'long_description' => $this->description
            ]);
            $this->notifySuperAdmins($support);
        });
        return $support;
    }

    private function notifySuperAdmins(Support $support)
    {
        $super_admins = $this->businessMemberRepository->where('is_super', 1)
            ->where('business_id', $this->member->businesses()->first()->id)->get();
        foreach ($super_admins as $super_admin) {
            $title = $this->member->profile->name . ' #' . $this->member->id . ' has created a Support Ticket';
            notify()->member($super_admin->member)->send([
                'title' => $title,
                'type' => 'warning',
                'event_type' => 'Sheba\Dal\Support\Model',
                'event_id' => $support->id,
                'link' => config('sheba.business_url') . '/dashboard/support/' . $support->id
            ]);
            $topic = config('sheba.push_notification_topic_name.employee') . $super_admin->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $this->pushNotification->send([
                "title" => 'New support created',
                "message" => $title,
                "event_type" => 'support',
                "event_id" => $support->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }

    }
}