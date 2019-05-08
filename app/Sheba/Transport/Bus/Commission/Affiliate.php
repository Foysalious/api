<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\Transport\Bus\BusTicketCommission;

class Affiliate extends BusTicketCommission
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
        $this->transportTicketOrder->ambassador_amount = $this->calculateAmbassadorCommissionForMovieTicket();
        $this->transportTicketOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        $this->agent->ambassador->creditWallet($this->transportTicketOrder->ambassador_commission);
        $log = "{$this->agent->profile->name} gifted {$this->transportTicketOrder->ambassador_commission} Tk. for {$this->transportTicketOrder->amount} Tk. movie ticket purchase";;
        $this->agent->ambassador->walletTransaction([
            'amount' => $this->transportTicketOrder->ambassador_commission,
            'type' => 'Credit',
            'log' => $log
        ]);
    }

    public function refund(){}
}