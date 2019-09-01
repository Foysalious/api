<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\Transport\Bus\BusTicketCommission;

class Partner extends BusTicketCommission
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
        $this->transportTicketOrder->ambassador_amount = $this->calculateAmbassadorCommissionForMovieTicket();
        $this->transportTicketOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        $this->agent->ambassador->creditWallet($this->transportTicketOrder->ambassador_amount);
        $log = "{$this->agent->profile->name} gifted {$this->transportTicketOrder->ambassador_amount} Tk. for {$this->transportTicketOrder->amount} Tk. transport ticket purchase";
        $this->agent->ambassador->walletTransaction(['amount' => $this->transportTicketOrder->ambassador_amount, 'type' => 'Credit', 'log' => $log]);
    }
    public function refund(){}
}
