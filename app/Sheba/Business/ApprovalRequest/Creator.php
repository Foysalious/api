<?php namespace Sheba\Business\ApprovalRequest;

use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\ApprovalRequest\ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;

class Creator
{
    private $approvalRequestRepo;
    
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    public function create()
    {

    }

}