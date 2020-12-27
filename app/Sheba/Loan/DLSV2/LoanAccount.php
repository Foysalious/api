<?php


namespace App\Sheba\Loan\DLSV2;


use App\Repositories\FileRepository;
use Carbon\Carbon;
use Sheba\Dal\LoanClaimRequest\Statuses;
use Sheba\Loan\LoanRepository;
use Sheba\Loan\Statics\GeneralStatics;

class LoanAccount
{

    /**
     * @var LoanRepository
     */
    private $repo;
    /**
     * @var FileRepository|null
     */
    private $fileRepository;

    public function __construct(FileRepository $file_repository = null)
    {
        $this->repo           = new LoanRepository();
        $this->fileRepository = $file_repository;
    }

    /**
     * @param $request
     * @return array
     */
    public function accountInfo($request)
    {
        $data                   = [];
        $partner_loan           = $this->repo->find($request->loan_id);
        $last_claim             = (new LoanClaim())->setLoan($request->loan_id)->lastClaim();
        $data['loan_status']    = $partner_loan->status;
        $data['granted_amount'] = $partner_loan->loan_amount;
        if (!$last_claim || ($last_claim && $last_claim->status == Statuses::APPROVED && $this->hasClearedDue($last_claim->id))) {
           $data =$this->getInfoType($partner_loan,$data);
        }
        if ($last_claim && $last_claim->status == Statuses::PENDING) {
            $data =$this->getWarningType($data);
        }
        if ($last_claim && $last_claim->status == Statuses::APPROVED && ($due = $this->getDue($last_claim->id)) > 0) {
            $data =$this->getDefaulterOrSuccessType($last_claim,$data,$due);
        }

        if ($last_claim && $last_claim->status == Statuses::DECLINED) {
            $data =$this->getErrorType($data);
        }
        $data['recent_claims']       = $this->getRecentClaims($request->loan_id);
        $data['recent_transactions'] = $this->getRecentRepayments($request->loan_id);

        return $data;
    }

    private function getInfoType($partner_loan,$data)
    {
        $data['loan_balance']             = 0;
        $data['due_balance']              = 0;
        $data['status_message']           = 'আপনি সর্বোচ্চ ' . convertNumbersToBangla($partner_loan->loan_amount, true, 0) . ' পর্যন্ত টাকা গ্রহণ করতে পারবেন';
        $data['status_type']              = 'info';
        $data['can_claim']                = 1;
        $data['should_pay']               = 0;
        $data['is_defaulter']             = 0;
        $data['success_msg_seen']         = 0;
        $data['minimum_repayment_amount'] = GeneralStatics::getMinimumRepaymentAmount();

        return $data;
    }

    private function getWarningType($data)
    {
        $data['loan_balance']             = 0;
        $data['due_balance']              = 0;
        $data['status_message']           = 'টাকা দাবির আবেদনটি বিবেচনাধীন রয়েছে! অতি শীঘ্রই সেবা প্লাটফর্ম থেকে আপনার সাথে যোগাযোগ করা হবে। বিস্তারিত জানতে কল করুন ১৬৫১৬।';
        $data['status_type']              = 'warning';
        $data['can_claim']                = 0;
        $data['should_pay']               = 0;
        $data['is_defaulter']             = 0;
        $data['success_msg_seen']         = 0;
        $data['minimum_repayment_amount'] = GeneralStatics::getMinimumRepaymentAmount();

        return $data;
    }

    private function getDefaulterOrSuccessType($last_claim,$data,$due)
    {
        $is_defaulter                     = $this->isDefaulter($last_claim->defaulter_date);
        $data['loan_balance']             = $data['granted_amount'] - $due;
        $data['due_balance']              = $due;
        $data['status_message']           = $is_defaulter ? 'আপনার টাকা পরিশোধ করার শেষ দিন অতিক্রম হয়ে গিয়েছে, আপনি এখন ডিফল্টার হয়ে গিয়েছেন, অতিরিক্ত প্রতিদিনের জন্য হাজারে '. convertNumbersToBangla(GeneralStatics::getDefaulterFine(),true, 2) .' টাকা করে যোগ হচ্ছে।' : 'টাকা দাবির আবেদনটি গৃহীত হয়েছে। দাবীকৃত টাকার পরিমাণ আপনার রবি ব্যালেন্সে যুক্ত হয়েছে, বন্ধু অ্যাপ-এ লগইন করে দেখে নিন।';
        $data['status_type']              = $is_defaulter ? 'defaulter' : 'success';
        $data['can_claim']                = 0;
        $data['should_pay']               = 1;
        $data['is_defaulter']             = $is_defaulter;
        $data['success_msg_seen']         = $last_claim->approved_msg_seen;
        $data['minimum_repayment_amount'] = GeneralStatics::getMinimumRepaymentAmount();

        return $data;
    }

    private function getErrorType($data)
    {
        $data['loan_balance']             = 0;
        $data['due_balance']              = 0;
        $data['status_message']           = 'টাকা দাবির আবেদনটি গৃহীত হয়নি। দয়া করে পুনরায় আবেদন করুন অথবা বিস্তারিত জানতে কল করুন ১৬৫১৬।';
        $data['status_type']              = 'error';
        $data['can_claim']                = 1;
        $data['should_pay']               = 0;
        $data['is_defaulter']             = 0;
        $data['success_msg_seen']         = 0;
        $data['minimum_repayment_amount'] = GeneralStatics::getMinimumRepaymentAmount();

        return $data;
    }

    /**
     * @param $claim_id
     * @return bool
     */
    public function hasClearedDue($claim_id)
    {
        return $this->getDue($claim_id) > 0 ? false : true;
    }

    /**
     * @param $claim_id
     * @return mixed
     */
    public function getDue($claim_id)
    {
        return (new Repayment())->setClaim($claim_id)->getDue();
    }

    /**
     * @param $loan_id
     * @return array|mixed
     */
    private function getRecentClaims($loan_id)
    {
        $data['recent_claims'] = [];
        $recent_claims         = (new LoanClaim())->getRecent($loan_id);
        if ($recent_claims) {
            foreach ($recent_claims as $claim) {
                array_push($data['recent_claims'], [
                    'id'         => $claim->id,
                    'status'     => $claim->status,
                    'amount'     => $claim->amount,
                    'log'        => $claim->log,
                    'created_at' => Carbon::parse($claim->created_at)->format('Y-m-d H:i:s')
                ]);
            }
        }
        return $data['recent_claims'];
    }

    /**
     * @param $loan_id
     * @return array|mixed
     */
    private function getRecentRepayments($loan_id)
    {
        $data['recent_repayments'] = [];
        $recent_repayments         = (new Repayment())->getRecent($loan_id);
        if ($recent_repayments) {
            foreach ($recent_repayments as $repayment) {
                array_push($data['recent_repayments'], [
                    'id'          => $repayment->id,
                    'claim_id'    => $repayment->loan_claim_request_id,
                    'amount'      => (int)$repayment->debit == 0 ? $repayment->credit ? : 0 : $repayment->debit ? : 0,
                    'amount_type' => (int)$repayment->debit == 0 ? 'credit' : 'debit',
                    'log'         => $repayment->log,
                    'created_at'  => Carbon::parse($repayment->created_at)->format('Y-m-d H:i:s')
                ]);
            }
        }
        return $data['recent_repayments'];
    }

    /**
     * @param $date
     * @return int
     */
    private function isDefaulter($date)
    {
        return Carbon::parse($date) > (Carbon::today()) ? 0 : 1;
    }

}