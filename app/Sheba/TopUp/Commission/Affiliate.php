<?php namespace Sheba\TopUp\Commission;

use App\Models\TopUpOrder;
use Sheba\FraudDetection\TransactionSources;
use Sheba\TopUp\TopUpCommission;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Throwable;

class Affiliate extends TopUpCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        if ($this->agent->ambassador) {
            $this->storeAmbassadorCommission();
            $this->storeAmbassadorWalletTransaction();
        }
    }

    private function storeAmbassadorCommission()
    {
        $commission = (double)$this->calculateAmbassadorCommission($this->topUpOrder->amount);
        if ($commission == 0) return;
        $this->topUpOrder->ambassador_commission = $commission;
        $this->topUpOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        if ($this->topUpOrder->ambassador_commission == 0) return;
        $log = "{$this->agent->profile->name} gifted {$this->topUpOrder->ambassador_commission} Tk. for {$this->topUpOrder->amount} Tk. topup";
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         *  $this->agent->ambassador->creditWallet($this->topUpOrder->ambassador_commission);
         $this->agent->ambassador->walletTransaction(['amount' => $this->topUpOrder->ambassador_commission, 'type' => 'Credit', 'log' => $log, 'is_gifted' => 1]);*/
        $model = $this->agent->ambassador;
        (new WalletTransactionHandler())->setModel($model)->setSource(TransactionSources::TOP_UP)->setType('credit')
            ->setAmount($this->topUpOrder->ambassador_commission)->setLog($log)->dispatch();
    }

    private function deductFromAmbassador($amount, $log)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->agent->ambassador->debitWallet($amount);
        $this->agent->ambassador->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);*/
        $model = $this->agent->ambassador;
        (new WalletTransactionHandler())->setModel($model)->setSource(TransactionSources::TOP_UP)->setType('debit')
            ->setAmount($amount)->setLog($log)->dispatch();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $amount = $this->topUpOrder->amount;
        $amount_after_commission = round($amount - $this->calculateCommission($amount), 2);
        $log = "Your recharge TK $amount to {$this->topUpOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
        $this->sendRefundNotification($log);

        $ambassador = $this->topUpOrder->agent->ambassador;
        if (!is_null($ambassador)) {
            $ambassador_commission = $this->topUpOrder->ambassador_commission;
            $this->topUpOrder->ambassador_commission = 0.0;
            $this->topUpOrder->save();
            $this->deductFromAmbassador($ambassador_commission, "$ambassador_commission Tk. has been deducted due to refund top up.");
        }
    }

    private function sendRefundNotification($title)
    {
        try {
            notify()->affiliate($this->topUpOrder->agent)
                ->send([
                    "title" => $title,
                    "link" => url("affiliate/" . $this->topUpOrder->agent->id),
                    "type" => 'warning',
                    "event_type" => 'App\Models\Affiliate',
                    "event_id" => $this->topUpOrder->agent->id
                ]);
        } catch (Throwable $e) {
        }
    }
}
