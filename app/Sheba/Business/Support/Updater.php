<?php namespace Sheba\Business\Support;


use App\Models\BusinessMember;
use Sheba\Dal\Support\Model as Support;
use Sheba\Dal\Support\Statuses;
use Sheba\Dal\Support\SupportRepositoryInterface;
use DB;

class Updater
{
    private $supportRepository;
    private $support;
    /** @var BusinessMember */
    private $businessMember;

    public function __construct(SupportRepositoryInterface $support_repository)
    {
        $this->supportRepository = $support_repository;
    }

    public function setSupport(Support $support)
    {
        $this->support = $support;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function resolve()
    {
        if (!$this->businessMember->isSuperAdmin()) return null;
        DB::transaction(function () {
            $this->supportRepository->update($this->support, ['status' => Statuses::CLOSED]);
            notify()->member($this->support->member)->send([
                'title' => 'Admin closed your Support Ticket',
                'type' => 'warning',
                'event_type' => 'App\Models\Support',
                'event_id' => $this->support->id
            ]);
        });
        return 1;
    }
}