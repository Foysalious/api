<?php namespace Sheba\CmDashboard;

use Carbon\Carbon;
use App\Models\PartnerOrder;
use Illuminate\Support\Facades\Auth;

class SalesStatisticsForCm
{
    private $commaFormattedMoney;
    private $cm;

    public function __construct()
    {
        $this->commaFormattedMoney = true;
        $this->cm = Auth::user()->id;
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 180);
    }

    public function get()
    {
        $startEndDate = findStartEndDateOfAMonth(0, Carbon::now()->year);
        $start_time = $startEndDate['start_time'];
        $end_time = $startEndDate['end_time'];
        $timeFrame = [$start_time, $end_time];

        $orders = PartnerOrder::with('order', 'jobs.usedMaterials')->whereBetween('closed_at', $timeFrame)->get()
            ->filter(function ($partner_order) {
                $partner_order->setRelations([
                    'order' => $partner_order->order,
                    'jobs' => $partner_order->jobs->filter(function ($job) {
                        return $job->crm_id == $this->cm;
                    })
                ]);
                return $partner_order->jobs->count();
            })->map(function($order) {
                return $order->calculate($price_only = true);
            });

        $order_today = $orders->filter(function($order) {
            return $order->closed_at >= Carbon::today() && $order->closed_at <= Carbon::tomorrow()->subSecond();
        });
        $order_this_week = $orders->filter(function($order) {
            $week_start = Carbon::today()->subDays(Carbon::today()->dayOfWeek);
            $week_end = Carbon::today()->addDays(7 - Carbon::today()->dayOfWeek)->subSecond();
            return $order->closed_at->between($week_start, $week_end);
        });
        $order_this_month = $orders->filter(function($order) {
            return $order->closed_at->year == Carbon::today()->year && $order->closed_at->month == Carbon::today()->month;
        });
        $order_this_year = clone $orders;

        return [
            "Today" => [
                "Sale" => formatTaka($order_today->sum('jobPrices'), $this->commaFormattedMoney),
                "Order" => commaSeparate($order_today->pluck('order_id')->unique()->count()),
                "Job" => commaSeparate($order_today->pluck('jobs')->map(function($item) {return $item->count();})->sum()),
                "ServedJob" => commaSeparate($order_today->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum()),
                "Profit" => formatTaka($order_today->sum('profit'), $this->commaFormattedMoney),
            ],
            "Week" => [
                "Sale" => formatTaka($order_this_week->sum('jobPrices'), $this->commaFormattedMoney),
                "Order" => commaSeparate($order_this_week->pluck('order_id')->unique()->count()),
                "Job" => commaSeparate($order_this_week->pluck('jobs')->map(function($item) {return $item->count();})->sum()),
                "ServedJob" => commaSeparate($order_this_week->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum()),
                "Profit" => formatTaka($order_this_week->sum('profit'), $this->commaFormattedMoney)
            ],
            "Month" => [
                "Sale" => formatTaka($order_this_month->sum('jobPrices'), $this->commaFormattedMoney),
                "Order" => commaSeparate($order_this_month->pluck('order_id')->unique()->count()),
                "Job" => commaSeparate($order_this_month->pluck('jobs')->map(function($item) {return $item->count();})->sum()),
                "ServedJob" => commaSeparate($order_this_month->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum()),
                "Profit" => formatTaka($order_this_month->sum('profit'), $this->commaFormattedMoney)
            ],
            "Year" => [
                "Sale" => formatTaka($order_this_year->sum('jobPrices'), $this->commaFormattedMoney),
                "Order" => commaSeparate($order_this_year->pluck('order_id')->unique()->count()),
                "Job" => commaSeparate($order_this_year->pluck('jobs')->map(function($item) {return $item->count();})->sum()),
                "ServedJob" => commaSeparate($order_this_year->pluck('jobs')->map(function($jobs) { return $jobs->where('status', 'Served')->count(); })->sum()),
                "Profit" => formatTaka($order_this_year->sum('profit'), $this->commaFormattedMoney)
            ],
        ];
    }
}