<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\FraudDetection\TransactionSources;
use Sheba\Transactions\Wallet\HasWalletTransaction;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Transport\Bus\BusTicketCommission;

class Affiliate extends BusTicketCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        if ($this->agent->ambassador) {
            $vendor_ambassador_commission = $this->vendorCommission->ambassador_amount;
            if ($vendor_ambassador_commission > 0) {
                $this->storeAmbassadorCommission();
                $this->storeAmbassadorWalletTransaction();
            }
        }
    }

    private function storeAmbassadorCommission()
    {
        $this->transportTicketOrder->ambassador_amount = $this->calculateAmbassadorCommissionForTransportTicket();
        $this->transportTicketOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        $log = "{$this->agent->profile->name} gifted {$this->transportTicketOrder->ambassador_amount} point for {$this->transportTicketOrder->amount} Tk. transport ticket purchase";
        /*
         * WALLET TRANSACTION NEED TO REMOVE
         * $this->agent->ambassador->creditWallet($this->transportTicketOrder->ambassador_amount);
        $this->agent->ambassador->walletTransaction(['amount' => $this->transportTicketOrder->ambassador_amount, 'type' => 'Credit', 'log' => $log]);*/
        /** @var HasWalletTransaction $ambassador */
        $ambassador = $this->agent->ambassador;
        (new WalletTransactionHandler())->setModel($ambassador)->setType('credit')->setLog($log)
            ->setSource(TransactionSources::TRANSPORT)->setAmount($this->transportTicketOrder->ambassador_amount)->dispatch();
    }

    public function refund(){}
}
