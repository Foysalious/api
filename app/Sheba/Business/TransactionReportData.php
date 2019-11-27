<?php namespace Sheba\Business;


use App\Models\Business;
use App\Models\BusinessTransaction;
use Sheba\Helpers\TimeFrame;

class TransactionReportData
{
    /** @var TimeFrame */
    private $timeFrame;

    /** @var Business */
    private $business;

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function get()
    {
        $balance = 0;
        return $this->business->transactions->map(function (BusinessTransaction $transaction) use (&$balance) {
            return [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'type' => $this->getEventType($transaction),
                'log' => $transaction->log,
                'debit' => $transaction->isDebit() ? $transaction->amount : '',
                'credit' => $transaction->isCredit() ? $transaction->amount : '',
                'balance' => $balance = $transaction->balance($balance),
                'created_at' => $transaction->created_at->toDateTimeString(),
            ];
        })->toArray();
    }

    private function getEventType(BusinessTransaction $transaction)
    {
        if (str_contains($transaction->log, "topped up")) $event_type = "Top Up";
        elseif (strContainsAll($transaction->log, ["recharge", "failed", "refunded"])) $event_type = "Top Up Refund";
        elseif ($transaction->isCredit()) $event_type = "Cash in";
        elseif ($transaction->isDebit()) $event_type = "Purchase";
        else $event_type = "N/F";
        return $event_type;
    }
}
