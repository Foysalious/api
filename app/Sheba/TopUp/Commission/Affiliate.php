<?php namespace Sheba\TopUp\Commission;

use App\Models\TopUpOrder;
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
        if($this->agent->ambassador) {
            $this->agent->ambassador->creditWallet($this->amount);
            $this->agent->ambassador->walletTransaction(['amount' => $this->amount, 'type' => 'Credit', 'log' => "$this->amount Tk. has been gifted from agent id: {$this->agent->id}"]);
        }
    }

    public function deductFromAmbassador($amount, $log)
    {
        $this->agent->debitWallet($amount);
        $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $amount = $this->topUpOrder->amount;
        $amount_after_commission = round($amount - $this->calculateCommission($amount, $this->topUpOrder->vendor), 2);
        $log = "Your recharge TK $amount to {$this->topUpOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
        $this->sendRefundNotification($log);

        $ambassador = $this->topUpOrder->agent->ambassador;
        if(!is_null($ambassador)) {
            $ambassador_commission = $this->topUpOrder->ambassador_commission;
            $this->topUpOrder->ambassador_commission = 0.0;
            $this->topUpOrder->save();
            $this->deductFromAmbassador($ambassador_commission, "$ambassador_commission Tk. has been deducted due to refund top up.");
        }
    }

    private function sendRefundNotification($title)
    {
        try {
            notify()->affiliate($this->topUpOrder->agent)->send([
                "title" => $title,
                "link" => url("affiliate/" . $this->topUpOrder->agent->id),
                "type" => 'warning',
                "event_type" => 'App\Models\Affiliate',
                "event_id" => $this->topUpOrder->agent->id
            ]);
        } catch (\Throwable $e) {
        }
    }
}