<?php


namespace Sheba\Loan;


use App\Http\Requests\Request;
use App\Models\Partner;
use App\Models\Resource;
use App\Sheba\Loan\DLSV2\Exceptions\InsufficientWalletCreditForRepayment;
use App\Sheba\Loan\DLSV2\LoanClaim;
use App\Sheba\Loan\DLSV2\Repayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Bank;
use Sheba\AccountingEntry\Accounts\AccountTypes\AccountKeys\Asset\Sheba;
use Sheba\AccountingEntry\Accounts\RootAccounts;
use Sheba\AccountingEntry\Repository\JournalCreateRepository;
use Sheba\FraudDetection\TransactionSources;
use Sheba\ModificationFields;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class LoanRepayments
{
    use ModificationFields;
    /** @var Partner $partner */
    private $partner;
    /** @var Resource $resource */
    private $resource;
    /**
     * @var LoanRepository
     */
    private $repo;

    private $transaction;

    public function __construct() {
        $this->repo           = new LoanRepository();
    }

    /**
     * @param Partner $partner
     * @return LoanRepayments
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Resource $resource
     * @return LoanRepayments
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }
    /**
     * @param $request
     * @throws InsufficientWalletCreditForRepayment
     */
    public function repaymentFromWallet($request)
    {
        $last_claim = (new LoanClaim())->setLoan($request->loan_id)->lastClaim();
        $this->balanceCheck($request->amount);
        DB::transaction(function () use ($last_claim, $request) {
            $this->debitFromWallet($request->loan_id, $request->amount);
            (new Repayment())->setLoan($request->loan_id)->setClaim($last_claim->id)->setAmount($request->amount)->repaymentFromWallet();
            $this->storeJournal($request);
        });
    }

    /**
     * @param $amount
     * @return bool
     * @throws InsufficientWalletCreditForRepayment
     */
    private function balanceCheck($amount)
    {

        if ((double)$this->partner->wallet < $amount)
            throw new InsufficientWalletCreditForRepayment();
        return true;

    }

    private function debitFromWallet($loan_id, $amount)
    {
        $this->setModifier($this->resource);
        $this->transaction = (new WalletTransactionHandler())->setModel($this->partner)->setAmount($amount)->setSource(TransactionSources::LOAN_REPAYMENT)->setType(Types::debit())->setLog("$amount BDT has been collected from {$this->resource->profile->name} as Loan Repayment  for  loan: $loan_id")->store();
        return true;
    }

    public function repaymentList($loan_id, $all = false, $month = null, $year = null)
    {
        $repayments = !$all ? (new Repayment())->getByYearAndMonth($loan_id, $month, $year) : (new Repayment())->getAll($loan_id);
        $last_claim = (new LoanClaim())->setLoan($loan_id)->lastClaim();

        $data['repayment_list'] = [];

        foreach ($repayments as $repayment) {
            array_push($data['repayment_list'], [
                'id'          => $repayment->id,
                'claim_id'    => $repayment->loan_claim_request_id,
                'amount'      => (int)$repayment->debit == 0 ? $repayment->credit : $repayment->debit,
                'amount_type' => (int)$repayment->debit == 0 ? 'credit' : 'debit',
                'log'         => $repayment->log,
                'created_at'  => Carbon::parse($repayment->created_at)->format('Y-m-d H:i:s')
            ]);
        }
        $data['credit_amount'] = $this->repo->find($loan_id)->loan_amount;
        $data['due_amount']    = $last_claim ? $this->getDue($last_claim->id) : 0;

        return $data;
    }

    /**
     * @param $claim_id
     * @return mixed
     */
    public function getDue($claim_id)
    {
        return (new Repayment())->setClaim($claim_id)->getDue();
    }

    private function storeJournal($request) {
        (new JournalCreateRepository())
            ->setTypeId($this->partner->id)
            ->setSource($this->transaction)
            ->setAmount($request->amount)
            ->setDebitAccountKey(Bank::CITY_BANK)
            ->setCreditAccountKey(Sheba::SHEBA_ACCOUNT)
            ->setDetails("Entry For Loan Repayment")
            ->setReference($request->loan_id)
            ->store();
    }
}
