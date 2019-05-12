<?php namespace Sheba\Reports;

use App\Models\Order;
use App\Models\PartnerOrder;
use Carbon\Carbon;

class Sales
{
    use LifetimeQueryHandler;

    private $data;
    private $timeLine;

    function __construct($timeLine)
    {
        $this->data = [];
        $this->timeLine = $timeLine;
        $this->_initializeDepartmentSalesData();
    }

    public function calculate()
    {
        $this->_calculateCustomerAcquisitionForSales();
        $this->_calculateGeneratedOrdersForSales();
        $this->_calculateSalesByClosedOrders();
        return $this->data;
    }

    private function _initializeDepartmentSalesData()
    {
        $fields = [
            'generated_order', 'job_on_total_generated_order', 'served_job_on_total_generated_order', 'cancelled_job_on_total_generated_order','sp_change_job_on_total_generated_order',
            'closed_order', 'job_on_closed_order', 'served_job_on_closed_order', 'cancelled_job_on_closed_order', 'sp_change_job_on_closed_order',
            'unique_customer', 'new_customer', 'returning_customer',
            'sales_price', 'charge_amount', 'discount', 'rounding_cut_off', 'sheba_collection', 'partner_collection', 'collection', 'due', 'cost',
            'profit_before_discount', 'profit_after_discount'
        ];
        foreach(getSalesChannels('short_name') as $row) {
            foreach($fields as $col) {
                $this->data[$row][$col] = 0;
            }
        }
    }

    private function _calculateGeneratedOrdersForSales()
    {
        $partner_orders_generated = $this->notLifetimeQuery(PartnerOrder::with('partner', 'order', 'jobs.partnerChangeLog'), $this->timeLine)->get();
        foreach ($partner_orders_generated as $partner_order) {
            $channel = $partner_order->order->shortChannel();
            $no_of_jobs = $partner_order->jobs->count();
            $this->data[$channel]['generated_order']++;
            $this->data[$channel]['job_on_total_generated_order'] += $no_of_jobs;
            foreach ($partner_order->jobs as $job) {
                if ($job->status == "Served") {
                    $this->data[$channel]['served_job_on_total_generated_order']++;
                } else if ($job->status == "Cancelled") {
                    $this->data[$channel]['cancelled_job_on_total_generated_order']++;
                    if ($job->partnerChangeLog) $this->data[$channel]['sp_change_job_on_total_generated_order']++;
                }
            }
        }
    }

    private function _calculateCustomerAcquisitionForSales()
    {
        $this->notLifetimeQuery(Order::with('customer'), $this->timeLine)->get()->groupBy('customer_id')->map(function ($customer_orders) {
            return [$customer_orders->first()->shortChannel(), $customer_orders->first()->customer->created_at];
        })->each(function ($item) {
            $this->data[$item[0]]['unique_customer']++;
            if ($item[1]->between(Carbon::parse($this->timeLine['start_date']), Carbon::parse($this->timeLine['end_date'])->addDay()->subSecond())) {
                $this->data[$item[0]]['new_customer']++;
            } else {
                $this->data[$item[0]]['returning_customer']++;
            }
        });
    }

    private function _calculateSalesByClosedOrders()
    {
        $partner_orders_closed = $this->notLifetimeQuery(PartnerOrder::with('partner', 'order', 'jobs.partnerChangeLog'), $this->timeLine, 'closed_at')->get();
        foreach ($partner_orders_closed as $partner_order) {
            $partner_order = $partner_order->calculate();
            $channel = $partner_order->order->shortChannel();
            $this->data[$channel]['closed_order']++;
            $this->data[$channel]['job_on_closed_order'] += $partner_order->jobs->count();
            foreach ($partner_order->jobs as $job) {
                if ($job->status == "Served") {
                    $this->data[$channel]['served_job_on_closed_order']++;
                } else if ($job->status == "Cancelled") {
                    $this->data[$channel]['cancelled_job_on_closed_order']++;
                    if ($job->partnerChangeLog) $this->data[$channel]['sp_change_job_on_closed_order']++;
                }
            }

            $this->data[$channel]['sales_price'] += $partner_order->jobPrices;
            $this->data[$channel]['discount'] += $partner_order->totalDiscount;
            $this->data[$channel]['rounding_cut_off'] += $partner_order->roundingCutOff;
            $this->data[$channel]['charge_amount'] += $partner_order->grossAmount;
            $this->data[$channel]['sheba_collection'] += $partner_order->sheba_collection;
            $this->data[$channel]['partner_collection'] += $partner_order->partner_collection;
            $this->data[$channel]['collection'] += $partner_order->paid;
            $this->data[$channel]['due'] += $partner_order->due;
            $this->data[$channel]['cost'] += $partner_order->totalCost;
            $this->data[$channel]['profit_before_discount'] += $partner_order->profitBeforeDiscount;
            $this->data[$channel]['profit_after_discount'] += $partner_order->profit;
            /*$this->>data[$channel]['margin_before_discount'] += $partner_order->marginBeforeDiscount;
            $this->>data[$channel]['margin_after_discount'] += $partner_order->marginAfterDiscount;*/
        }
    }
}