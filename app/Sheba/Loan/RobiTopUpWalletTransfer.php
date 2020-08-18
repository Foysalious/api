<?php namespace Sheba\Loan;


use App\Models\Affiliate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\RobiTopupWalletTransaction\Model as RobiTopupWalletTransaction;

Class RobiTopUpWalletTransfer
{
    private $affiliate, $amount, $type, $loan_id;

    public function setAffiliate(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setLoanId($loan_id)
    {
        $this->loan_id = $loan_id;
        return $this;
    }

    private function transfer()
    {
        $this->affiliate->robi_topup_wallet += $this->amount;
        $this->affiliate->update();
    }

    private function storeTransactionRecord()
    {
        $formatted_data = [
            'affiliate_id' => $this->affiliate->id,
            'type' => ucfirst($this->type),
            'log' => $this->getLog(),
            'created_at' => Carbon::now(),
            'balance' => $this->getCalculatedBalance(),
            'amount' => $this->amount,
        ];
        $transaction_data = (new RobiTopupWalletTransaction())->fill($formatted_data);
        $this->affiliate->robi_topup_wallet_transactions()->save($transaction_data);

    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function process($data = [])
    {
        DB::transaction(function () use ($data, &$transaction) {
            $this->transfer();
            $this->storeTransactionRecord();
            $this->sendNotificationToBankPortal();
        });
    }

    private function getLog()
    {
        return "Robi topup balance transfer for MICRO loan";
    }

    private function getCalculatedBalance()
    {
        $last_inserted_transaction = $this->affiliate->robi_topup_wallet_transactions()->orderBy('id', 'desc')->first();
        $last_inserted_balance = $last_inserted_transaction ? $last_inserted_transaction->balance : 0.00;
        return strtolower($this->type) == 'credit' ? $last_inserted_balance + $this->amount : $last_inserted_balance - $this->amount;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function sendNotificationToBankPortal()
    {
        $title = "Loan amount transferred to sManager";
        Notifications::toBankUser(1, $title, null, $this->loan_id);
    }

}