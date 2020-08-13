<?php namespace App\Sheba\Loan\DLSV2;


use App\Models\PartnerBankLoan;
use App\Sheba\Loan\DLSV2\Notification\SMS\SMSHandler;
use Carbon\Carbon;
use Sheba\Dal\LoanClaimRequest\Model as LoanClaimModel;
use Sheba\Dal\LoanClaimRequest\EloquentImplementation as LoanClaimRepo;
use Sheba\Dal\LoanClaimRequest\Statuses;
use Sheba\Loan\Notifications;
use Sheba\Loan\RobiTopUpWalletTransfer;
use Sheba\Loan\Statics\GeneralStatics;
use Sheba\ModificationFields;

class LoanClaim
{

    use ModificationFields;
    private $loanClaimRequest;
    private $loanId;
    private $claimId;

    /**
     * @param $loan_id
     * @return $this
     */
    public function setLoan($loan_id)
    {
         $this->loanId = $loan_id;
         return $this;
    }

    /**
     * @param $claim_id
     * @return $this
     */
    public function setClaim($claim_id)
    {
        $this->claimId = $claim_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function lastClaim()
    {
        return LoanClaimModel::lastClaim($this->loanId)->first();
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     * @throws \Exception
     */
    public function updateStatus($from, $to)
    {
        $claim = (new LoanClaimRepo(new LoanClaimModel()))->find($this->claimId);
        if($claim && $claim->status == $from){
            $claim->status = $to;
            $claim->log = $this->getLog($claim->amount, $to);
            $claim->update();
            if ($to == Statuses::APPROVED) {
                (new Repayment())->setLoan($this->loanId)->setClaim($this->claimId)->setAmount($claim->amount)->storeCreditPaymentEntry();
                $this->setDefaulterDate($claim);
                $claim_amount = $claim->amount;
                $affiliate = $claim->resource->profile->affiliate;
                if (isset($affiliate) && $claim_amount > 0)
                    (new RobiTopUpWalletTransfer())->setAffiliate($affiliate)->setAmount($claim_amount)->setType("credit")->process();
                $this->deductClaimApprovalFee();
                $this->checkAndDeductAnnualFee($claim);
                $this->sendNotificationToBankPortal();
            }
            $this->sendSms($to,$this->loanId,$claim->amount);
        }

        return true;
    }

    /**
     * @param $to
     * @param $loan_id
     * @param $claim_amount
     */
    private function sendSms($to, $loan_id, $claim_amount)
    {
        $message = null;
        $type = null;
        $partner_bank_loan = PartnerBankLoan::find($loan_id);
        if ($to == Statuses::APPROVED) {
            $message = 'প্রিয় ' . $partner_bank_loan->partner->getContactPerson() . ', অভিনন্দন! আপনার ' . $claim_amount . ' টাকার ক্লেইম রিকুয়েস্টটি অনুমোদিত হয়েছে।আপনি এই টাকা দিয়ে বন্ধু অ্যাপ-এর মাধ্যমে রবি রিচার্জ ব্যবসা পরিচালনা করতে বন্ধু অ্যাপ ওপেন করুন। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type = 'Claim Approved';
        }

        if ($to == Statuses::DECLINED) {
            $message = 'প্রিয় ' . $partner_bank_loan->partner->getContactPerson() . ', আপনার রবি লোন ক্লেইম রিকুয়েস্টটি  অনুমোদিত হয়নি। প্রয়োজনে কল করুন ১৬৫১৬-এ।';
            $type = 'Claim Declined';
        }

        (new SMSHandler())->setMsg($message)->setMobile($partner_bank_loan->partner->getContactNumber())->setMsgType($type)->setLoanId($partner_bank_loan->id)->shoot();

    }

    private function setDefaulterDate($claim)
    {
        $duration = $claim->loan->duration;
        $claim->defaulter_date =  Carbon::now()->addDays($duration);
        $claim->update();
    }

    /**
     * @param $claim
     * @return mixed
     */
    private function checkAndDeductAnnualFee($claim)
    {
        $last_annual_payment_date = $claim->loan->last_annual_fee_payment_at;
        if(empty($last_annual_payment_date) || Carbon::parse(Carbon::now())->diffInDays($last_annual_payment_date) > 365)
        {
            (new Repayment())->setLoan($this->loanId)->setClaim($this->claimId)->setAmount(GeneralStatics::getMicroLoanAnnualFee())->storeCreditPaymentEntryForAnnualFee();
            $claim->loan->last_annual_fee_payment_at = Carbon::now()->addDays(365);
            return $claim->loan->update();
        }
    }

    /**
     * @param $claim
     */
    private function deductClaimApprovalFee()
    {
        (new Repayment())->setLoan($this->loanId)->setClaim($this->claimId)->setAmount(GeneralStatics::getClaimTransactionFee())->storeCreditPaymentEntryForClaimTransactionFee();
    }


    /**
     * @param $amount
     * @param $to
     * @return string
     */
    public function getLog($amount, $to)
    {

        $log = [
            'approved' => '৳' . convertNumbersToBangla($amount,true,0) . ' লোন দাবি গৃহীত হয়েছে',
            'declined' => '৳' . convertNumbersToBangla($amount,true, 0) . ' লোন দাবি বাতিল করা হয়েছে',
            'pending' => '৳' . convertNumbersToBangla($amount, true, 0) . ' লোন দাবি করা হয়েছে'
        ];

        return $log[$to];
    }
    /**
     * @param $data
     * @return bool
     */
    public function createRequest($data)
    {
        $this->loanClaimRequest = new LoanClaimModel($this->withCreateModificationField($data));
        return $this->loanClaimRequest->save();
    }

    /**
     * @param $loan_id
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getByYearAndMonth($loan_id, $year, $month)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getByYearAndMonth($loan_id,$year,$month);
    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getAll($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getByLoanID($loan_id);

    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getRecent($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getRecent($loan_id);
    }

    /**
     * @param $loan_id
     * @return mixed
     */
    public function getPending($loan_id)
    {
        return (new LoanClaimRepo(new LoanClaimModel()))->getPending($loan_id);
    }

    /**
     * @param $to
     * @return mixed
     */
    public function updateApprovedMsgSeen($to)
    {
        $claim = (new LoanClaimRepo(new LoanClaimModel()))->find($this->claimId);
        $claim->approved_msg_seen = $to;
        return $claim->update();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function sendNotificationToBankPortal()
    {
        $title = "Loan amount transferred to sManager";
        Notifications::toBankUser(1, $title, null, $this->loanId);
    }

}
