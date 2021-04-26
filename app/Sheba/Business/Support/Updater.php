<?php namespace Sheba\Business\Support;

use App\Models\BusinessMember;
use Carbon\Carbon;
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
    private $satisfaction;

    public function __construct(SupportRepositoryInterface $support_repository)
    {
        $this->supportRepository = $support_repository;
        $this->satisfaction = null;
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

    public function setSatisfaction($satisfaction)
    {
        $this->satisfaction = $satisfaction;
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
            $this->supportRepository->update($this->support, ['status' => Statuses::CLOSED, 'closed_at' => Carbon::now()]);
            notify()->member($this->support->member)->send([
                'title' => 'Admin closed your Support Ticket',
                'type' => 'warning',
                'event_type' => 'Sheba\Dal\Support\Model',
                'event_id' => $this->support->id
            ]);
        });
        return 1;
    }

    public function giveFeedback()
    {
        if ($this->satisfaction != null && $this->support->status == Statuses::CLOSED) {
            $this->supportRepository->update($this->support, ['is_satisfied' => $this->satisfaction]);
            return 1;
        } else {
            return null;
        }
    }
}