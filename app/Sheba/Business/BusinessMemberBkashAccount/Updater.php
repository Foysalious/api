<?php namespace App\Sheba\Business\BusinessMemberBkashAccount;

use Sheba\Dal\BusinessMemberBkashInfo\BusinessMemberBkashInfoRepository;
use Illuminate\Support\Facades\DB;

class Updater
{
    private $bkashAccData = [];
    private $bkashAccRequest;
    /** @var BusinessMemberBkashInfoRepository $bkashAccRepository */
    private $bkashAccRepository;
    private $bkashInfo;
    private $oldBkashAcc;

    public function __construct()
    {
        $this->bkashAccRepository = app(BusinessMemberBkashInfoRepository::class);
    }

    /**
     * @param Requester $bkash_acc_request
     * @return Updater
     */
    public function setBkashAccRequester(Requester $bkash_acc_request)
    {
        $this->bkashAccRequest = $bkash_acc_request;
        return $this;
    }

    public function setBkashInfo($bkash_info)
    {
        $this->bkashInfo = $bkash_info;
        $this->oldBkashAcc = $this->bkashInfo->account_no;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            if ($this->oldBkashAcc != $this->bkashAccRequest->getBkashNumber()) {
                $this->bkashAccRepository->update($this->bkashInfo, $this->bkashAccData);
            }
        });
        return true;
    }

    private function makeData()
    {
        $this->bkashAccData['account_no'] = $this->bkashAccRequest->getBkashNumber();
    }
}
