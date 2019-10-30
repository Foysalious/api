<?php namespace Sheba\Analysis\Sales;

use App\Models\SaleTargets;
use Carbon\Carbon;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\CustomerRepository;
use Sheba\Repositories\OrderRepository;
use Sheba\Repositories\PartnerOrderPaymentRepository;
use Sheba\Repositories\PartnerOrderRepository;
use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;

class SalesStatistics
{
    private $commaFormattedMoney;
    private $today;
    private $week;
    private $month;
    private $year;
    private $target;
    private $orders;
    private $partnerOrders;
    private $customers;
    private $complains;
    private $collections;
    private $beforeToday;
    private $timeFrame;

    public function __construct(ComplainRepo $complain)
    {
        $this->commaFormattedMoney = true;
        $this->partnerOrders = new PartnerOrderRepository();
        $this->orders = new OrderRepository();
        $this->customers = new CustomerRepository();
        $this->complains = $complain;
        $this->collections = new PartnerOrderPaymentRepository();
        $this->beforeToday = new ShebaSalesStatsBeforeToday();
        $this->today = new SalesStat();
        $this->timeFrame = new TimeFrame();
    }

    public function calculate()
    {
        $this->calculateStatsForToday();
        $this->calculateStatsBeforeToday();
        $this->addTodayStatsWithPreviousStats();
        $this->target = ($target = SaleTargets::currentMonth()->first()) ? $target->targets : 0;
        return $this->formatDataForView();
    }

    public function get(Carbon $date)
    {
        $orders = $this->partnerOrders->getClosedOrdersOf($date);
        $data = new SalesStat();
        $data->sale = $orders->sum('jobPrices');
        $data->orderClosed = $orders->count();
        $data->orderCreated = $this->orders->countCreatedOrdersOf($date);
        $data->jobServed = $orders->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum();
        $data->profit = $orders->sum('profit');
        $data->revenue = $orders->sum('revenue');
        $data->collection = $this->collections->getCollectionOf($date);
        $data->customerRegistered = $this->customers->countRegisteredCustomersOf($date);
        $data->uniqueCustomer = $this->orders->countUniqueCustomerFromOrdersOf((new TimeFrame())->forADay($date));
        $data->uniqueCustomerServed = $this->partnerOrders->countUniqueCustomerFromServedJobsOf((new TimeFrame())->forADay($date));
        $data->complain = $this->complains->countComplainsOf($date);
        return $data;
    }

    private function calculateStatsForToday()
    {
        $orders = $this->partnerOrders->getTodayClosedOrders();
        $this->today->sale = $orders->sum('jobPrices');
        $this->today->orderClosed = $orders->count();
        $this->today->orderCreated = $this->orders->countTodayCreatedOrders();
        $this->today->jobServed = $orders->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum();
        $this->today->profit = $orders->sum('profit');
        $this->today->revenue = $orders->sum('revenue');
        $this->today->collection = $this->collections->getTodayCollection();
        $this->today->customerRegistered = $this->customers->countTodayRegisteredCustomers();
        $this->today->uniqueCustomer = $this->orders->countTodayUniqueCustomer();
        $this->today->uniqueCustomerServed = $this->partnerOrders->countTodayUniqueCustomerFromServedJobs();
        $this->today->complain = $this->complains->countComplainsOf(Carbon::today());
    }

    private function calculateStatsBeforeToday()
    {
        $sales_stats_before_today = $this->beforeToday->get();
        $this->week  = $sales_stats_before_today->week;
        $this->month = $sales_stats_before_today->month;
        $this->year  = $sales_stats_before_today->year;
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
        $this->$time_frame->orderCreated += $this->today->orderCreated;
        $this->$time_frame->jobServed += $this->today->jobServed;
        $this->$time_frame->profit += $this->today->profit;
        $this->$time_frame->revenue += $this->today->revenue;
        $this->$time_frame->collection += $this->today->collection;
        $this->$time_frame->customerRegistered += $this->today->customerRegistered;
        $this->$time_frame->uniqueCustomer = $this->getUniqueCustomerInTimeFrame($time_frame);
        $this->$time_frame->uniqueCustomerServed = $this->getUniqueCustomerServedInTimeFrame($time_frame);
        $this->$time_frame->complain += $this->today->complain;
    }

    private function getUniqueCustomerInTimeFrame($time_frame)
    {
        if (!in_array($time_frame, ["week", "month", "year"])) return 0;

        if ($time_frame == "week") {
            $time_frame = $this->timeFrame->forCurrentWeek();
        } else if ($time_frame == "month") {
            $time_frame = $this->timeFrame->forAMonth(Carbon::now()->month, Carbon::now()->year);
        } else {
            $time_frame = $this->timeFrame->forAYear(Carbon::now()->year);
        }

        return $this->orders->countUniqueCustomerFromOrdersOf($time_frame);
    }

    private function getUniqueCustomerServedInTimeFrame($time_frame)
    {
        if (!in_array($time_frame, ["week", "month", "year"])) return 0;

        if ($time_frame == "week") {
            $time_frame = $this->timeFrame->forCurrentWeek();
        } else if ($time_frame == "month") {
            $time_frame = $this->timeFrame->forAMonth(Carbon::now()->month, Carbon::now()->year);
        } else {
            $time_frame = $this->timeFrame->forAYear(Carbon::now()->year);
        }

        return $this->partnerOrders->countUniqueCustomerFromServedJobsOf($time_frame);
    }

    private function formatDataForView()
    {
        return [
            "Today" => $this->formatDataPerTimeFrame($this->today),
            "Week"  => $this->formatDataPerTimeFrame($this->week),
            "Month" => $this->formatDataPerTimeFrame($this->month) + ["Target" => intval(formatToLakhs($this->target))],
            "Year"  => $this->formatDataPerTimeFrame($this->year)
        ];
    }

    private function formatDataPerTimeFrame(SalesStat $data)
    {
        return [
            "Sale"          => formatTaka($data->sale, $this->commaFormattedMoney),
            "OrderCreated"  => commaSeparate($data->orderCreated),
            "ClosedOrder"   => commaSeparate($data->orderClosed),
            "ServedJob"     => commaSeparate($data->jobServed),
            "Profit"        => formatTaka($data->profit, $this->commaFormattedMoney),
            "Revenue"       => formatTaka($data->revenue, $this->commaFormattedMoney),
            "Collection"    => formatTaka($data->collection, $this->commaFormattedMoney),
            "Customer"      => commaSeparate($data->customerRegistered),
            "UniqueCustomer"=> commaSeparate($data->uniqueCustomer),
            "UniqueCustomerServed" => commaSeparate($data->uniqueCustomerServed),
            "Complain"      => commaSeparate($data->complain)
        ];
    }
}