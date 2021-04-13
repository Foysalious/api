<?php

namespace App\Repositories;

use App\Models\Affiliate;
use Illuminate\Http\Request;

class AffiliateTransactionRepository
{
    private $start_date;
    private $end_date;
    private $affiliate;

    /**
     * @param Request $request
     * @return $this
     */
    public function setDates(Request $request)
    {
        $this->start_date = $request->start_date. ' 00:00:00';
        $this->end_date   = $request->end_date. ' 23:59:59';
        return $this;
    }

    /**
     * @param Affiliate $affiliate
     * @return $this
     */
    public function setAffiliate(Affiliate $affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    public function getHistory()
    {
        $data = [
            "balance_in"  => $this->balanceIn(),
            "balance_out" => $this->balanceOut(),
            "earning"     => $this->earning(),
        ];
        $data = array_merge($data, ["categoryWiseTransaction" => $this->categoryWiseTransaction()]);
        return $data;
    }

    /**
     * @return mixed
     */
    private function balanceIn()
    {
        $amount = $this->affiliate->transactions()->credit()->between($this->start_date, $this->end_date)->sum('amount');
        return $amount == null ? 0 : $amount;
    }

    /**
     * @return mixed
     */
    private function balanceOut()
    {
        $amount = $this->affiliate->transactions()->debit()->between($this->start_date, $this->end_date)->sum('amount');
        return $amount == null ? 0 : $amount;
    }

    /**
     * @return mixed
     */
    private function earning()
    {
        $amount = $this->affiliate->transactions()->earning()->between($this->start_date, $this->end_date)->sum('amount') + $this->topUps()->sum('agent_commission') + $this->topUps()->sum('otf_agent_commission');
        return $amount == null ? 0 : $amount;
    }

    /**
     * @param $amount
     * @param $count
     * @param $title
     * @param $title_bn
     * @param $key
     * @param $sign
     * @return array
     */
    private function makeData($amount, $count, $title, $title_bn, $key, $sign='')
    {
        return [
            "key"      => $key,
            "title"    => $title,
            "title_bn" => $title_bn,
            "amount"   => $sign.$amount,
            "count"    => $count
        ];
    }

    /**
     * @return array
     */
    private function categoryWiseTransaction()
    {
        $category_wise_transaction = [];
        $balance_recharge          = $this->affiliate->transactions()->credit()->balanceRecharge()->between($this->start_date, $this->end_date);
        $topUp                     = $this->topUps();
        $service_commission        = $this->affiliate->transactions()->credit()->serviceCommission()->between($this->start_date, $this->end_date);
        $bus_ticket                = $this->affiliate->transactions()->debit()->transportTicket()->between($this->start_date, $this->end_date);
        $movie_ticket              = $this->affiliate->transactions()->debit()->movieTicket()->between($this->start_date, $this->end_date);
        $movie_ticket_commission   = $this->affiliate->transactions()->credit()->movieTicketCommission()->between($this->start_date, $this->end_date);
        $refunds                   = $this->affiliate->transactions()->credit()->refunds()->between($this->start_date, $this->end_date);
        $manual_disbursement       = $this->affiliate->transactions()->credit()->manualDisbursement()->between($this->start_date, $this->end_date);
        $sheba_facilitated         = $this->affiliate->transactions()->credit()->shebaFacilitated()->between($this->start_date, $this->end_date);
        $service_purchase          = $this->affiliate->transactions()->debit()->servicePurchase()->between($this->start_date, $this->end_date);
        $point_purchase_charge    = $this->affiliate->transactions()->debit()->pointPurchaseCommission()->between($this->start_date, $this->end_date);
        $bus_ticket_commission     = $this->affiliate->transactions()->credit()->busTicketCommission()->between($this->start_date, $this->end_date);

        if($count = $balance_recharge->count()) $category_wise_transaction[] = $this->makeData($balance_recharge->sum('amount'), $count, "Balance Recharge", "ব্যালেন্স রিচার্জ", "balance_recharge");
        if($count = $topUp->count()) $category_wise_transaction[] = $this->makeData($topUp->sum('amount'), $count, "Top Up", "টপ আপ", "topup", "-");
        if(($count + $manual_disbursement->count())) $category_wise_transaction[] = $this->makeData($topUp->sum('agent_commission')+$manual_disbursement->sum('amount')+$topUp->sum('otf_agent_commission'), $count + $manual_disbursement->count(), "Top Up Commission", "টপ আপ কমিশন", "topup_commission");
        if($count = $service_commission->count()) $category_wise_transaction[] = $this->makeData($service_commission->sum('amount'), $count, "Service Refer Commission", "সার্ভিস রেফার কমিশন", "service_refer_commission");
        if($count = $bus_ticket->count()) $category_wise_transaction[] = $this->makeData($bus_ticket->sum('amount'), $count, "Bus Ticket", "বাস টিকেট", "bus_ticket",'-');
        if($count = $movie_ticket->count()) $category_wise_transaction[] = $this->makeData($movie_ticket->sum('amount'), $count, "Movie Ticket", "সিনেমা টিকেট", "movie_ticket",'-');
        if($count = $movie_ticket_commission->count()) $category_wise_transaction[] = $this->makeData($movie_ticket_commission->sum('amount'), $count, "Movie Ticket Commission", "সিনেমা টিকেট কমিশন", "movie_ticket_commission");
        if($count = $refunds->count()) $category_wise_transaction[] = $this->makeData($refunds->sum('amount'), $count, "Refunds", "রিফান্ড", "refunds");
        if($count = $sheba_facilitated->count()) $category_wise_transaction[] = $this->makeData($sheba_facilitated->sum('amount'), $count, "Facilitated Amount", "ফ্যাসিলিটি অ্যামাউন্ট", "facilitated_amount");
        if($count = $service_purchase->count()) $category_wise_transaction[] = $this->makeData($service_purchase->sum('amount'), $count, "Service Purchase", "সার্ভিস ক্রয়", "service_purchase",'-');
        if($count = $point_purchase_charge->count()) $category_wise_transaction[] = $this->makeData($point_purchase_charge->sum('amount'), $count, "Point Purchase Commission", "পয়েন্ট ক্রয় কমিশন", "point_purchase_commission",'-');
        if($count = $bus_ticket_commission->count()) $category_wise_transaction[] = $this->makeData($bus_ticket_commission->sum('amount'), $count, "Bus Ticket Commission", "বাস টিকিট কমিশন", "bus_ticket_commission");

        return $category_wise_transaction;
    }

    private function topUps()
    {
        return $this->affiliate->topups()->between($this->start_date, $this->end_date)->where('status','Successful');
    }

}