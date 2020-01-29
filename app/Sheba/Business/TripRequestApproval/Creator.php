<?php namespace Sheba\Business\TripRequestApproval;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\TripRequestApproval\TripRequestApprovalRepositoryInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    private $member;
    private $data = [];
    private $tripRequestApprovalRepository;

    public function __construct(TripRequestApprovalRepositoryInterface $tripRequestApprovalRepository)
    {
        $this->tripRequestApprovalRepository = $tripRequestApprovalRepository;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function makeData()
    {
        $this->data;
    }

    public function store()
    {
        return $this;

    }

}
