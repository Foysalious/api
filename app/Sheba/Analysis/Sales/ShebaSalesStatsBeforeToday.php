<?php namespace Sheba\Analysis\Sales;

use Carbon\Carbon;
use Cache;
use Illuminate\Support\Collection;
use Sheba\Repositories\DailyStatsRepository;

class ShebaSalesStatsBeforeToday extends SalesStatsBeforeToday
{
    private $statsRepository;

    public function __construct()
    {
        parent::__construct();
        $this->redisCacheName = "sales_stats_before_today";
        $this->statsRepository = new DailyStatsRepository();
    }

    protected function calculateFromDB()
    {
        $year_data  = $this->statsRepository->getStatisticsData($this->yearTimeFrame);
        $month_data = collect([]);
        $week_data  = collect([]);

        $year_data->each(function($item) use (&$month_data, &$week_data) {
            $date = Carbon::parse($item->date)->addSecond();
            /**
             * Add extra 1 second, because carbon::parse return 218-07-29 00:00:00.000000
             * But $this->weekTimeFrame[0] returns 2018-07-29 00:00:00.519272
             */
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
        $timeFrameData->orderCreated = $data->sum('orderCreated');
        $timeFrameData->orderClosed = $data->sum('orderClosed');
        $timeFrameData->jobServed = $data->sum('jobServed');
        $timeFrameData->profit = $data->sum('profit');
        $timeFrameData->revenue = $data->sum('revenue');
        $timeFrameData->customerRegistered = $data->sum('customerRegistered');
        $timeFrameData->collection = $data->sum('collection');
        $timeFrameData->complain = $data->sum('complain');
    }
}