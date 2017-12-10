<?php namespace Sheba\Analysis\Sales;

use App\Models\SaleTargets;
use Carbon\Carbon;
use Sheba\Repositories\CustomerRepository;
use Sheba\Repositories\PartnerOrderPaymentRepository;
use Sheba\Repositories\PartnerOrderRepository;

class SalesStatistics
{
    private $commaFormattedMoney;
    private $today;
    private $week;
    private $month;
    private $year;
    private $target;
    private $partnerOrders;
    private $customers;
    private $collections;
    private $beforeToday;

    public function __construct()
    {
        $this->commaFormattedMoney = true;
        $this->partnerOrders = new PartnerOrderRepository();
        $this->customers = new CustomerRepository();
        $this->collections = new PartnerOrderPaymentRepository();
        $this->beforeToday = new ShebaSalesStatsBeforeToday();
        $this->today = new SalesStat();
    }

    public function calculate()
    {
        $this->calculateStatsForToday();
        $this->calculateStatsBeforeToday();
        $this->addTodayStatsWithPreviousStats();
        $this->target = SaleTargets::currentMonth()->first()->targets;
        return $this->formatDataForView();
    }

    public function get(Carbon $date)
    {
        $orders = $this->partnerOrders->getClosedOrdersOf($date);
        $data = new SalesStat();
        $data->sale = $orders->sum('jobPrices');
        $data->orderClosed = $orders->count();
        $data->jobServed = $orders->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum();
        $data->profit = $orders->sum('profit');
        $data->collection = $this->collections->getCollectionOf($date);
        $data->customerRegistered = $this->customers->countRegisteredCustomersOf($date);
        return $data;
    }

    private function formatDataForView()
    {
        return [
            "Today" => $this->formatDataPerTimeFrame($this->today),
            "Week" => $this->formatDataPerTimeFrame($this->week),
            "Month" => $this->formatDataPerTimeFrame($this->month) + ["Target" => intval(formatToLakhs($this->target))],
            "Year" => $this->formatDataPerTimeFrame($this->year)
        ];
    }

    private function formatDataPerTimeFrame(SalesStat $data)
    {
        return [
            "Sale" => formatTaka($data->sale, $this->commaFormattedMoney),
            "ClosedOrder" => commaSeparate($data->orderClosed),
            "ServedJob" => commaSeparate($data->jobServed),
            "Profit" => formatTaka($data->profit, $this->commaFormattedMoney),
            "Collection" => formatTaka($data->collection, $this->commaFormattedMoney),
            "Customer" => commaSeparate($data->customerRegistered),
        ];
    }

    private function calculateStatsForToday()
    {
        $orders = $this->partnerOrders->getTodayClosedOrders();
        $this->today->sale = $orders->sum('jobPrices');
        $this->today->orderClosed = $orders->count();
        $this->today->jobServed = $orders->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum();
        $this->today->profit = $orders->sum('profit');
        $this->today->collection = $this->collections->getTodayCollection();
        $this->today->customerRegistered = $this->customers->countTodayRegisteredCustomers();
    }

    private function calculateStatsBeforeToday()
    {
        $sales_stats_before_today = $this->beforeToday->get();
        $this->week = $sales_stats_before_today->week;
        $this->month = $sales_stats_before_today->month;
        $this->year = $sales_stats_before_today->year;
    }

    private function addTodayStatsWithPreviousStats()
    {
        $this->addTodayStatsWithPreviousStatsForATimeFrame('week');
        $this->addTodayStatsWithPreviousStatsForATimeFrame('month');
        $this->addTodayStatsWithPreviousStatsForATimeFrame('year');
    }

    private function addTodayStatsWithPreviousStatsForATimeFrame($time_frame)
    {
        $this->$time_frame->sale += $this->today->sale;
        $this->$time_frame->orderClosed += $this->today->orderClosed;
        $this->$time_frame->jobServed += $this->today->jobServed;
        $this->$time_frame->profit += $this->today->profit;
        $this->$time_frame->collection += $this->today->collection;
        $this->$time_frame->customerRegistered += $this->today->customerRegistered;
    }
}