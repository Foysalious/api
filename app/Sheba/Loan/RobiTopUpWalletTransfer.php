<?php namespace Sheba\Loan;


use App\Models\Affiliate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

Class RobiTopUpWalletTransfer
{
    private $affiliate, $amount, $type;

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

    private function transfer()
    {
        $this->affiliate->robi_topup_wallet += $this->amount;
        $this->affiliate->update();
    }

    private function storeTransactionRecord()
    {
        $formatted_data = [
            'type' => ucfirst($this->type),
            'log' => $this->getLog(),
            'created_at' => Carbon::now(),
            'balance' => $this->getCalculatedBalance(),
            'amount' => $this->amount
        ];
        $this->affiliate->robi_topup_wallet_transactions()->save($formatted_data);
    }

    public function process($data = [])
    {
        DB::transaction(function () use ($data, &$transaction) {
            $this->transfer();
            $this->storeTransactionRecord();
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

}