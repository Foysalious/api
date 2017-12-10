<?php namespace Sheba\Analysis\Sales;

use App\Models\DailyStats;
use Carbon\Carbon;
use Cache;
use Illuminate\Support\Collection;

class ShebaSalesStatsBeforeToday extends SalesStatsBeforeToday
{
    public function __construct()
    {
        parent::__construct();
        $this->redisCacheName = "sales_stats_before_today";
    }

    protected function calculateFromDB()
    {
        $year_data = DailyStats::whereBetween('date', $this->yearTimeFrame)->get();
        $month_data = collect([]);
        $week_data = collect([]);
        $year_data->each(function($item) use (&$month_data, &$week_data) {
            $date = Carbon::parse($item->date);
            if($date->between($this->weekTimeFrame[0], $this->weekTimeFrame[1])) {
                $week_data->push($item->data);
            }
            if($date->year == Carbon::today()->year && $date->month == Carbon::today()->month) {
                $month_data->push($item->data);
            }
        });

        $this->sumDataForATimeFrame($this->year, $year_data->pluck('data'));
        $this->sumDataForATimeFrame($this->month, $month_data);
        $this->sumDataForATimeFrame($this->week, $week_data);
    }

    protected function sumDataForATimeFrame(SalesStat $timeFrameData, Collection $data)
    {
        $timeFrameData->sale = $data->sum('sale');
        $timeFrameData->orderClosed = $data->sum('orderClosed');
        $timeFrameData->jobServed = $data->sum('jobServed');
        $timeFrameData->profit = $data->sum('profit');
        $timeFrameData->customerRegistered = $data->sum('customerRegistered');
        $timeFrameData->collection = $data->sum('collection');
    }
}