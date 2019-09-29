<?php namespace Sheba\Reports;

use Sheba\Dal\Complain\Model as Complain;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class TopSheet
{
    use LifetimeQueryHandler;

    private $partner;
    private $timeFrame;

    private $data;

    public function __construct(Partner $partner, $session_times)
    {
        $this->partner = $partner;
        $this->timeFrame = $session_times;
        $this->data = $this->initialize();
    }

    public function calculate()
    {
        $partner_orders = $this->notLifetimeQuery($this->partner->orders()->with('jobs'), $this->timeFrame, 'closed_at')->get();
        $this->incrementDataForEach($partner_orders);
        $this->processComplains($partner_orders);
        $this->calculateProfitMargin();
        $this->calculateNetSales();
        $this->calculatePayableReceivable();
        $this->format();
        return $this->data;
    }

    private function initialize()
    {
        return [
            'total_order'             => 0,
            'total_order_amount'      => 0,
            'total_discount'          => 0,
            'total_discount_by_sp'    => 0,
            'total_discount_by_sheba' => 0,
            'total_charged_amount'    => 0,
            'collected_by_sheba'      => 0,
            'collected_by_sp'         => 0,
            'total_collection'        => 0,
            'total_due'               => 0,
            'total_order_cost'        => 0,
            'net_sales_by_amount'     => 0,
            'net_sales_by_collection' => 0,
            'complains'               => [
                'Open'        => 0,
                'Observation' => 0,
                'Resolved'    => 0,
            ],
            'profit'                  => 0,
            'margin'                  => 0,
            'profit_before_discount'  => 0,
            'margin_before_discount'  => 0,
        ];
    }

    private function incrementDataForEach($partner_orders)
    {
        foreach($partner_orders as $partner_order) {
            $partner_order = $partner_order->calculate($price_only = true);
            $this->data['total_order']++;
            $this->data['total_order_amount']     +=  $partner_order->jobPrices;
            $this->data['total_discount']         +=  $partner_order->totalDiscount + $partner_order->roundingCutOff;
            $this->data['total_discount_by_sp']   +=  $partner_order->totalPartnerDiscount;
            $this->data['total_discount_by_sheba']+=  $partner_order->totalShebaDiscount;
            $this->data['total_charged_amount']   +=  $partner_order->grossAmount;
            $this->data['collected_by_sheba']     +=  $partner_order->sheba_collection;
            $this->data['collected_by_sp']        +=  $partner_order->partner_collection;
            $this->data['total_collection']       +=  $partner_order->paid;
            $this->data['total_due']              +=  $partner_order->due;
            $this->data['total_order_cost']       +=  $partner_order->totalCost;
        }
    }

    /**
     * @param $partner_orders
     */
    private function processComplains($partner_orders)
    {
        $status_counters = Complain::select(DB::raw('status, count(id) as counter'))
            ->whereIn('job_id', function ($q) use ($partner_orders) {
                $q->select('id')->from('jobs')->whereIn('partner_order_id', $partner_orders->pluck('id')->toArray());
            })->groupBy('status')->get()->pluck('counter', 'status')->toArray();
        $this->data['complains'] = array_merge($this->data['complains'], $status_counters);
    }

    private function calculateProfitMargin()
    {
        $this->data['profit'] = $this->data['total_charged_amount'] - $this->data['total_order_cost'];
        if ($this->data['total_charged_amount']) {
            $this->data['margin'] = ($this->data['total_charged_amount'] - $this->data['total_order_cost']) * 100 / $this->data['total_charged_amount'];
        }

        $this->data['profit_before_discount'] = $this->data['total_order_amount'] - $this->data['total_order_cost'];
        if ($this->data['total_order_amount']) {
            $this->data['margin_before_discount'] = ($this->data['total_order_amount'] - $this->data['total_order_cost']) * 100 / $this->data['total_order_amount'];
        }
    }

    private function calculateNetSales()
    {
        $this->data['net_sales_by_amount'] = $this->data['total_order_amount'] - $this->data['total_discount'];
        $this->data['net_sales_by_collection'] = $this->data['total_collection'] + $this->data['total_due'];
    }

    private function calculatePayableReceivable()
    {
        $this->data['sp_payable'] = ($this->data['collected_by_sp'] < $this->data['total_order_cost']) ? ($this->data['total_order_cost'] - $this->data['collected_by_sp']) : 0;
        $this->data['sheba_receivable'] = ($this->data['collected_by_sp'] > $this->data['total_order_cost']) ? ($this->data['collected_by_sp'] - $this->data['total_order_cost']) : 0;
    }

    private function format()
    {
        $this->data['total_order_amount']      = formatTaka($this->data['total_order_amount']);
        $this->data['total_discount']          = formatTaka($this->data['total_discount']);
        $this->data['total_discount_by_sp']    = formatTaka($this->data['total_discount_by_sp']);
        $this->data['total_discount_by_sheba'] = formatTaka($this->data['total_discount_by_sheba']);
        $this->data['total_charged_amount']    = formatTaka($this->data['total_charged_amount']);
        $this->data['collected_by_sheba']      = formatTaka($this->data['collected_by_sheba']);
        $this->data['collected_by_sp']         = formatTaka($this->data['collected_by_sp']);
        $this->data['total_collection']        = formatTaka($this->data['total_collection']);
        $this->data['total_due']               = formatTaka($this->data['total_due']);
        $this->data['total_order_cost']        = formatTaka($this->data['total_order_cost']);
        $this->data['net_sales_by_amount']     = formatTaka($this->data['net_sales_by_amount']);
        $this->data['net_sales_by_collection'] = formatTaka($this->data['net_sales_by_collection']);
        $this->data['profit']                  = formatTaka($this->data['profit']);
        $this->data['margin']                  = formatTaka($this->data['margin']);
        $this->data['profit_before_discount']  = formatTaka($this->data['profit_before_discount']);
        $this->data['margin_before_discount']  = formatTaka($this->data['margin_before_discount']);
    }
}