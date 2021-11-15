<?php namespace App\Sheba\Business\BusinessMemberBkashAccount;

use Sheba\Dal\BusinessMemberBkashInfo\BusinessMemberBkashInfoRepository;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessMember;

class Creator
{
    /** @var Requester */
    private $bkashAccRequest;
    private $businessMember;
    private $bkashAccData = [];
    /** @var BusinessMemberBkashInfoRepository $bkashAccRepository */
    private $bkashAccRepository;

    public function __construct(BusinessMemberBkashInfoRepository $bkash_acc_repository)
    {
        $this->bkashAccRepository = $bkash_acc_repository;
    }

    /**
     * @param Requester $bkash_acc_request
     * @return Creator
     */
    public function setBkashAccRequester(Requester $bkash_acc_request)
    {
        $this->bkashAccRequest = $bkash_acc_request;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        DB::transaction(function () {
           $this->bkashAccRepository->create($this->bkashAccData);
        });

        return true;
    }

    private function makeData()
    {
        $this->bkashAccData['business_member_id'] = $this->bkashAccRequest->getBusinessMember()->id;
        $this->bkashAccData['account_no'] = $this->bkashAccRequest->getBkashNumber();
    }
}
