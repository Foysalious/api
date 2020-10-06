<?php namespace Sheba\Business\Support;

use App\Jobs\Business\SendSupportPushNotificationToSuperAdminEmployee;
use App\Models\Member;
use Exception;
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
    private $business_id;

    /**
     * Creator constructor.
     * @param SupportRepositoryInterface $support_repository
     * @param BusinessMemberRepositoryInterface $business_member_repository
     */
    public function __construct(SupportRepositoryInterface $support_repository, BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->supportRepository = $support_repository;
        $this->businessMemberRepository = $business_member_repository;
        $this->pushNotification = new PushNotificationHandler();
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * @param $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function create()
    {
        $support = $this->supportRepository->create([
            'member_id' => $this->member->id,
            'long_description' => $this->description
        ]);

        try {
            $this->notifySuperAdmins($support);
        } catch (Exception $e) {
            app('sentry')->captureException($e);
        }

        return $support;
    }

    /**
     * @param Support $support
     * @throws Exception
     */
    private function notifySuperAdmins(Support $support)
    {
        $super_admins = $this->businessMemberRepository
            ->where('is_super', 1)
            ->where('business_id', $this->business_id)->get();

        $title = $this->member->profile->name . ' #' . $this->member->id . ' has created a Support Ticket';
        notify()->members($super_admins->pluck('member_id')->toArray())->send([
            'title' => $title,
            'type' => 'warning',
            'event_type' => 'Sheba\Dal\Support\Model',
            'event_id' => $support->id,
            'link' => config('sheba.business_url') . '/dashboard/support/' . $support->id
        ]);

        foreach ($super_admins as $super_admin) {
            dispatch(new SendSupportPushNotificationToSuperAdminEmployee($super_admin, $title, $support));
        }
    }

    /**
     * @param $business_id
     * @return $this
     */
    public function setBusinessId($business_id)
    {
        $this->business_id = $business_id;
        return $this;
    }
}
