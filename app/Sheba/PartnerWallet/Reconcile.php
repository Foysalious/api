<?php namespace Sheba\PartnerWallet;

use App\Models\Partner;
use App\Models\PartnerOrderReconcileLog;
use App\Models\PartnerTransaction;
use Carbon\Carbon;
use Sheba\ModificationFields;

class Reconcile
{
    use ModificationFields;

    private $partner;
    private $partnerOrders;

    public function __construct($partner_id)
    {
        $this->partner = Partner::find($partner_id);
        $this->partnerOrders = $this->partner->orders()
                                ->with('jobs.usedMaterials')
                                ->whereNotNull('closed_and_paid_at')
                                ->where('is_reconciled', 0)
                                ->get();
    }

    public function collect($amount, $transaction_details)
    {
        $amount = floatValFormat($amount);
        $amount_copy = $amount;
        foreach ($this->partnerOrders as $key => $partner_order) {
            $amount -= $this->collectFromOrder($partner_order->calculate($price_only = true), $amount);
            if (!$amount) break;
        }
        $this->incrementWallet($amount_copy, $transaction_details);
    }

    public function pay($amount)
    {
        $amount = floatValFormat($amount);
        $amount_copy = $amount;
        foreach ($this->partnerOrders as $key => $partner_order) {
            $amount -= $this->payFromOrder($partner_order->calculate($price_only = true), $amount);
            if (!$amount) break;
        }
        $this->decrementWallet($amount_copy);
    }

    private function collectFromOrder($partner_order, $amount)
    {
        $prev_sheba_collection = $partner_order->sheba_collection;
        $prev_partner_collection = $partner_order->partner_collection;

        $sheba_receive = $partner_order->partner_collection - $partner_order->totalCost;
        $is_reconciled = $sheba_receive <= $amount;
        $sheba_receive = $is_reconciled ? $sheba_receive : $amount;

        $partner_order->update([
            'partner_collection' => $prev_partner_collection - $sheba_receive,
            'finance_collection' => $prev_sheba_collection + $sheba_receive,
            'sheba_collection' => $prev_sheba_collection + $sheba_receive,
            'is_reconciled' => $is_reconciled
        ]);

        $this->saveReconcileLog($partner_order, $sheba_receive, $prev_partner_collection, $prev_sheba_collection);

        return $sheba_receive;
    }

    private function saveReconcileLog($partner_order, $amount, $prev_partner_collection, $prev_sheba_collection)
    {
        // positive amount => Sheba receive.
        $to = ($amount > 0) ? 'Sheba' : 'SP';
        $from = ($amount > 0) ? 'SP' : 'Sheba';
        $amount = abs($amount);
        $partner_order->reconcileLogs()->save(new PartnerOrderReconcileLog([
            'partner_collection' => $prev_partner_collection,
            'sheba_collection' => $prev_sheba_collection,
            'amount' => $amount,
            'to' => $to,
            'log' => "$to took $amount from $from.",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]));
    }

    private function incrementWallet($amount, $transaction_details)
    {
        $this->partner->update($this->withUpdateModificationField(['wallet' => (floatval($this->partner->wallet) + $amount)]));
        $this->partner->transactions()->save(new PartnerTransaction([
            'type' => "Credit",
            'amount' => formatTaka($amount),
            'log' => $amount. " paid to SHEBA.",
            'transaction_details' => $transaction_details,
            'created_at' => Carbon::now(),
        ]));
    }

    private function payFromOrder($partner_order, $amount)
    {
        $prev_sheba_collection = $partner_order->sheba_collection;
        $prev_partner_collection = $partner_order->partner_collection;

        $sp_receive = $partner_order->sheba_collection - $partner_order->profit;
        $is_reconciled = $sp_receive <= $amount;
        $sp_receive = $is_reconciled ? $sp_receive : $amount;

        $partner_order->update([
            'partner_collection' => $prev_partner_collection + $sp_receive,
            'finance_collection' => $prev_sheba_collection - $sp_receive,
            'sheba_collection' => $prev_sheba_collection - $sp_receive,
            'is_reconciled' => $is_reconciled
        ]);

        $sheba_receive = (-1 * $sp_receive); // positive amount => Sheba receive.
        $this->saveReconcileLog($partner_order, $sheba_receive, $prev_partner_collection, $prev_sheba_collection);

        return $sp_receive;
    }

    /**
     * @param $amount
     */
    private function decrementWallet($amount)
    {
        $this->partner->update($this->withUpdateModificationField(['wallet' => (floatval($this->partner->wallet) - $amount)]));
        $this->partner->transactions()->save(new PartnerTransaction([
            'type' => "Debit",
            'amount' => formatTaka($amount),
            'log' => $amount. " collected from SHEBA.",
            'created_at' => Carbon::now(),
        ]));
    }
}