<?php namespace Sheba\Analysis\Sales;

use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Repositories\PartnerOrderRepository;

class PartnerSalesStatistics
{
    public $today;
    public $week;
    public $month;
    public $year;
    public $lifetime;

    private $partnerOrders;
    private $beforeToday;
    private $partner;
    private $commaFormattedMoney;

    public function __construct(Partner $partner = null)
    {
        $this->partnerOrders = new PartnerOrderRepository();
        if ($partner instanceof Partner) $this->beforeToday = new PartnerSalesStatsBeforeToday($partner);
        $this->today = new SalesStat();
        $this->commaFormattedMoney = true;
        $this->partner = $partner;
    }

    public function calculate()
    {
        $this->calculateStatsForToday();
        $this->calculateStatsBeforeToday();
        $this->addTodayStatsWithPreviousStats();
        return $this;
    }

    public function format()
    {
        return $this->formatDataForView();
    }

    public function get(Carbon $date)
    {
        $orders = $this->partnerOrders->getClosedOrdersOfDateByPartner($date, $this->partner);
        return $this->calculateSalesFromOrders($orders);
    }

    private function calculateSalesFromOrders(Collection $orders)
    {
        $data = new SalesStat();
        $data->sale = $orders->sum('totalCost');
        $data->orderTotalPrice = $orders->sum('totalPrice');
        $data->orderClosed = $orders->count();
        $data->jobServed = $orders->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum();
        $data->totalPartnerDiscount = $orders->sum('totalPartnerDiscount');
        $data->totalCostWithoutDiscount = $orders->sum('totalCostWithoutDiscount');
        return $data;
    }

    public function getAll(Carbon $date)
    {
        $partner_orders = $this->partnerOrders->getClosedOrdersOfDateGroupedByPartner($date);
        return $partner_orders->map(function($orders) {
            return $this->calculateSalesFromOrders($orders);
        });
    }

    private function calculateStatsForToday()
    {
        $orders = $this->partnerOrders->getTodayClosedOrdersByPartner($this->partner);
        $this->today = $this->calculateSalesFromOrders($orders);
    }

    private function calculateStatsBeforeToday()
    {
        $sales_stats_before_today = $this->beforeToday->get();
        $this->week = $sales_stats_before_today->week;
        $this->month = $sales_stats_before_today->month;
        $this->year = $sales_stats_before_today->year;
        $this->lifetime = $sales_stats_before_today->lifetime;
    }

    private function addTodayStatsWithPreviousStats()
    {
        $this->addTodayStatsWithPreviousStatsForATimeFrame('week');
        $this->addTodayStatsWithPreviousStatsForATimeFrame('month');
        $this->addTodayStatsWithPreviousStatsForATimeFrame('year');
        $this->addTodayStatsWithPreviousStatsForATimeFrame('lifetime');
    }

    private function addTodayStatsWithPreviousStatsForATimeFrame($time_frame)
    {
        $this->$time_frame->sale += $this->today->sale;
        $this->$time_frame->orderTotalPrice += $this->today->orderTotalPrice;
        $this->$time_frame->orderClosed += $this->today->orderClosed;
        $this->$time_frame->jobServed += $this->today->jobServed;
        $this->$time_frame->totalPartnerDiscount += $this->today->totalPartnerDiscount;
        $this->$time_frame->totalCostWithoutDiscount += $this->today->totalCostWithoutDiscount;
    }

    private function formatDataForView()
    {
        return [
            "Today" => $this->formatDataPerTimeFrame($this->today),
            "Week" => $this->formatDataPerTimeFrame($this->week),
            "Month" => $this->formatDataPerTimeFrame($this->month),
            "Year" => $this->formatDataPerTimeFrame($this->year),
            "Lifetime" => $this->formatDataPerTimeFrame($this->lifetime)
        ];
    }

    private function formatDataPerTimeFrame(SalesStat $data)
    {
        return [
            "Sale" => formatTaka($data->sale, $this->commaFormattedMoney),
            "orderTotalPrice" => formatTaka($data->orderTotalPrice, $this->commaFormattedMoney),
            "ClosedOrder" => commaSeparate($data->orderClosed),
            "ServedJob" => commaSeparate($data->jobServed),
            "Discount" => commaSeparate($data->totalPartnerDiscount),
            "SaleWithoutDiscount" => commaSeparate($data->totalCostWithoutDiscount),
        ];
    }
}