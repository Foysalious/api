<?php namespace Sheba\MovieTicket\Commission;

use Sheba\MovieTicket\MovieTicketCommission;

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
        $this->movieTicketOrder->ambassador_commission = $this->calculateAmbassadorCommissionForMovieTicket($this->movieTicketOrder->amount);
        $this->movieTicketOrder->save();
    }

    private function storeAmbassadorWalletTransaction()
    {
        $this->agent->ambassador->creditWallet($this->movieTicketOrder->ambassador_commission);
        $log = "{$this->agent->profile->name} gifted {$this->movieTicketOrder->ambassador_commission} Tk. for {$this->movieTicketOrder->amount} Tk. topup";;
        $this->agent->ambassador->walletTransaction(['amount' => $this->movieTicketOrder->ambassador_commission, 'type' => 'Credit', 'log' => $log]);
    }

    private function deductFromAmbassador($amount, $log)
    {
        $this->agent->debitWallet($amount);
        $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Debit', 'log' => $log]);
    }

    public function refund()
    {
        $this->refundAgentsCommission();
        $amount = $this->movieTicketOrder->amount;
        $amount_after_commission = round($amount - $this->calculateMovieTicketCommission($amount), 2);
        $log = "Your recharge TK $amount to {$this->movieTicketOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
        $this->sendRefundNotification($log);

        $ambassador = $this->movieTicketOrder->agent->ambassador;
        if (!is_null($ambassador)) {
            $ambassador_commission = $this->movieTicketOrder->ambassador_commission;
            $this->movieTicketOrder->ambassador_commission = 0.0;
            $this->movieTicketOrder->save();
            $this->deductFromAmbassador($ambassador_commission, "$ambassador_commission Tk. has been deducted due to refund top up.");
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