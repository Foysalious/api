<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;

class Affiliate extends TopUpCommission
{
    public function disburse()
    {
       $this->storeAgentsCommission();
       $this->storeAmbassadorCommission();
       $this->storeWalletTransaction();
    }

    private function storeAmbassadorCommission() {
        $this->topUpOrder->ambassador_commission =  $this->calculateAmbassadorCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }

    private function storeWalletTransaction(){
        $this->agent->ambassador()->creditWallet($this->amount);
        $this->agent->ambassador()->walletTransaction(['amount' => $this->amount, 'type' => 'Credit', 'log' => "$this->amount Tk. has been gifted from agent id: {$this->agent->id}"]);}
}