<?php namespace Sheba\Business\Support;


use App\Models\BusinessMember;
use Sheba\Dal\Support\Model as Support;
use Sheba\Dal\Support\Statuses;
use Sheba\Dal\Support\SupportRepositoryInterface;

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

    public function resolve()
    {
        if (!$this->businessMember->isSuperAdmin()) return null;
        $this->supportRepository->update($this->support, ['status' => Statuses::$CLOSED]);
    }
}