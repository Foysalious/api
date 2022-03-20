<?php namespace Sheba\Repositories;

use App\Models\Affiliate;
use App\Models\AffiliateStatusChangeLog;
use App\Models\AffiliateTransaction;
use App\Models\Resource;
use Sheba\Affiliate\VerificationStatus;
use Sheba\Voucher\Creator\BroadcastPromo;
use Sheba\Voucher\VoucherCodeGenerator;

class AffiliateRepository extends BaseRepository
{
    public function creditWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet + $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function walletTransaction(Affiliate $affiliate, $data)
    {
        $affiliate->transactions()->save(new AffiliateTransaction($this->withCreateModificationField($data)));
    }

    public function debitWallet(Affiliate $affiliate, $amount, $log_data)
    {
        $affiliate->update(['wallet' => $affiliate->wallet - $amount]);
        $this->walletTransaction($affiliate, $log_data);
    }

    public function makeAmbassador(Affiliate $affiliate)
    {
        $voucher = (new BroadcastPromo($affiliate, null))->getVoucher();
        $affiliate->update(["is_ambassador" => 1, 'ambassador_code' => $voucher->code]);
    }

    public function saveStatusChangeLog(Affiliate $affiliate, $data)
    {
        $affiliate->statusChangeLogs()->save(new AffiliateStatusChangeLog($this->withCreateModificationField($data)));
    }

    public function updateVerificationStatusToPending(Affiliate $affiliate)
    {
        $previous_status = $affiliate->verification_status;
        $pending_status = VerificationStatus::PENDING;

        if ($previous_status != $pending_status) {
            $affiliate->update($this->withUpdateModificationField(['verification_status' => $pending_status]));

            $log_data = [
                'from' => $previous_status,
                'to' => $pending_status,
                'log' => null,
                'reason' => 're-submitted NID',
            ];
            $this->saveStatusChangeLog($affiliate, $log_data);
        }
    }

    public function updateData($affiliate, $data)
    {
        $affiliate = $affiliate instanceof Affiliate ? $affiliate : Affiliate::find($affiliate);
        $affiliate->update($this->withUpdateModificationField($data));
    }

    public function storeStatusUpdateLog($affiliate, $previous_status, $new_status, $reason, $log)
    {
        $affiliate = $affiliate instanceof Affiliate ? $affiliate : Affiliate::find($affiliate);

        if ($previous_status != $new_status) {
            $log_data = [
                'from' => $previous_status,
                'to' => $new_status,
                'affiliate_id' => $affiliate->id,
                'reason' => $reason,
                'log' => $log,
            ];
            $this->saveStatusChangeLog($affiliate, $log_data);
        }
    }
}
