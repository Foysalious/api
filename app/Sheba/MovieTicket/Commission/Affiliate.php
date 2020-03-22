<?php namespace Sheba\MovieTicket\Commission;

use Sheba\FraudDetection\TransactionSources;
use Sheba\MovieTicket\MovieTicketCommission;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class Affiliate extends MovieTicketCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        if ($this->agent->ambassador) {
            $this->storeAmbassadorCommission();
            $this->storeAmbassadorWalletTransaction();
        }
    }

    public function disburseNew()
    {
        $this->storeAgentsCommissionNew();
        if ($this->agent->ambassador) {
            $this->storeAmbassadorCommission();
            $this->storeAmbassadorWalletTransaction();
        }
    }

    private function storeAmbassadorCommission()
    {
        $this->movieTicketOrder->ambassador_commission = $this->calculateAmbassadorCommissionForMovieTicket($this->movieTicketOrder->amount);
        $this->movieTicketOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {

        $log = "{$this->agent->profile->name} gifted {$this->movieTicketOrder->ambassador_commission} point for {$this->movieTicketOrder->amount} Tk. movie ticket purchase";;
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->agent->ambassador->creditWallet($this->movieTicketOrder->ambassador_commission);
        $this->agent->ambassador->walletTransaction(['amount' => $this->movieTicketOrder->ambassador_commission, 'type' => 'Credit', 'log' => $log]);*/
        (new WalletTransactionHandler())->setModel($this->agent->ambassador)->setSource(TransactionSources::MOVIE)
            ->setLog($log)->setAmount($this->movieTicketOrder->ambassador_commission)->setType('credit')
            ->dispatch();
    }

    private function deductFromAmbassador($amount, $log)
    {
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->agent->ambassador->debitWallet($amount);
        $this->agent->ambassador->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);*/
        (new WalletTransactionHandler())->setModel($this->agent->ambassador)->setSource(TransactionSources::MOVIE)
            ->setLog($log)->setAmount($amount)->setType('debit')
            ->dispatch();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $amount = $this->movieTicketOrder->amount;
        $amount_after_commission = round($amount - $this->calculateMovieTicketCommission($amount), 2);
        $log = "Your movie ticket request of TK $amount has failed, TK $amount_after_commission is refunded in your account.";
        $this->sendRefundNotification($log);

        $ambassador = $this->movieTicketOrder->agent->ambassador;
        if (!is_null($ambassador)) {
            $ambassador_commission = $this->movieTicketOrder->ambassador_commission;
            $this->movieTicketOrder->ambassador_commission = 0.0;
            $this->movieTicketOrder->save();
            $this->deductFromAmbassador($ambassador_commission, "$ambassador_commission Tk. has been deducted due to refund movie ticket.");
        }
    }

    private function sendRefundNotification($title)
    {
        try {
            notify()->affiliate($this->movieTicketOrder->agent)
                ->send([
                    "title" => $title,
                    "link" => url("affiliate/" . $this->movieTicketOrder->agent->id),
                    "type" => 'warning',
                    "event_type" => 'App\Models\Affiliate',
                    "event_id" => $this->movieTicketOrder->agent->id
                ]);
        } catch (\Throwable $e) {}
    }
}
