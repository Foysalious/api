<?php namespace Sheba\TopUp\Commission;

use App\Models\TopUpOrder;
use Sheba\TopUp\MovieTicketCommission;

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

    private function storeAmbassadorCommission()
    {
        $this->topUpOrder->ambassador_commission = $this->calculateAmbassadorCommission($this->topUpOrder->amount);
        $this->topUpOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        $this->agent->ambassador->creditWallet($this->topUpOrder->ambassador_commission);
        $log = "{$this->agent->profile->name} gifted {$this->topUpOrder->ambassador_commission} Tk. for {$this->topUpOrder->amount} Tk. topup";;
        $this->agent->ambassador->walletTransaction(['amount' => $this->topUpOrder->ambassador_commission, 'type' => 'Credit', 'log' => $log]);
    }

    private function deductFromAmbassador($amount, $log)
    {
        $this->agent->debitWallet($amount);
        $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);
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
        } catch (\Throwable $e) {}
    }
}