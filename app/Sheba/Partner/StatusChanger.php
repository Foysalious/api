<?php namespace Sheba\Partner;

use App\Models\Partner;
use Carbon\Carbon;
use Sheba\Repositories\PartnerRepository;
use DB;

class StatusChanger
{
    private $partner;
    private $oldStatus;
    private $partnerRepo;
    private $data;

    public function __construct(Partner $partner, $data)
    {
        $this->partner = $partner;
        $this->data = $data;
        $this->oldStatus = $partner->status;
        $this->partnerRepo = new PartnerRepository();
    }

    public function hasError()
    {
        $new_status = $this->data['status'];
        if (!in_array($new_status, constants('PARTNER_STATUSES'))) return "Invalid Status.";
        if ($new_status == $this->partner->status) return "Status can't be changed to $new_status while status already is $new_status.";
        return false;
    }

    public function hasErrorForApi()
    {
        $new_status = $this->data['status'];
        if (!in_array($new_status, constants('PARTNER_STATUSES'))) return ['code' => 401, 'msg' => "Invalid Status."];
        if ($new_status == $this->partner->status) return ['code' => 401, "msg" => "Status can't be changed to $new_status while status already is $new_status."];
        return ['code' => 200];
    }

    public function change()
    {
        $partner_data['status'] = $this->data['status'];

        if (in_array($this->partner->status, ['Unverified', 'Paused']) && $this->data['status'] == 'Verified' && $this->partner->isFirstTimeVerified()) {
            $partner_data['verified_at'] = Carbon::now();
        }

        if (in_array($this->partner->status, ['Closed', 'Blacklisted', 'Waiting', 'Onboarded']) && $this->data['status'] == 'Verified') {
            $partner_data['verified_at'] = Carbon::now();
            $partner_data['billing_start_date'] = Carbon::now();
        }

        DB::transaction(function () use ($partner_data) {
            if ($this->data['status'] == constants('PARTNER_STATUSES')['Verified'] && in_array($this->partner->status, [constants('PARTNER_STATUSES')['Closed'], constants('PARTNER_STATUSES')['Blacklisted'], constants('PARTNER_STATUSES')['Waiting'], constants('PARTNER_STATUSES')['Onboarded']])) {
                $this->partner->runUpfrontSubscriptionBilling();
            }

            /**
             * TEMPORARY TURNED OFF AFFILIATION REWARD FOR READY TO VERIFIED STATUS.
             *
             * if ($this->data['status'] == constants('PARTNER_STATUSES')['Waiting']) {
                app('\Sheba\PartnerAffiliation\RewardHandler')->setPartner($this->partner)->waiting();
            }*/
            
            $this->partnerRepo->update($this->partner, $partner_data);
            $this->saveLog();
        });
    }

    private function saveLog()
    {
        $log_data = [
            'from' => $this->oldStatus,
            'to' => $this->data['status'],
            'log' => array_key_exists('log', $this->data) ? $this->data['log'] : null,
            'reason' => array_key_exists('reason', $this->data) ? $this->data['reason'] : null,
        ];
        $this->partnerRepo->saveStatusChangeLog($this->partner, $log_data);
    }
}