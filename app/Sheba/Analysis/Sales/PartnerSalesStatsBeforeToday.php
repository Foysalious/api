<?php namespace Sheba\Analysis\Sales;

use App\Models\Partner;
use Carbon\Carbon;
use Cache;
use Illuminate\Support\Collection;

class PartnerSalesStatsBeforeToday extends SalesStatsBeforeToday
{
    private $partner;

    public function __construct(Partner $partner)
    {
        parent::__construct();
        $this->partner = $partner;
        $this->redisCacheName = "partner_sales_stats_before_today::$partner->id";
    }

    protected function calculateFromDB()
    {
        $data = $this->partner->dailyStats;
        $year_data = collect([]);
        $month_data = collect([]);
        $week_data = collect([]);
        $data->each(function($item) use (&$year_data, &$month_data, &$week_data) {
            $date = Carbon::parse($item->date);
            if($date->between($this->weekTimeFrame[0], $this->weekTimeFrame[1])) {
                $week_data->push($item->data);
            }
            if($date->year == Carbon::today()->year && $date->month == Carbon::today()->month) {
                $month_data->push($item->data);
            }
            if($date->year == Carbon::today()->year) {
                $year_data->push($item->data);
            }
        });

        $this->sumDataForATimeFrame($this->lifetime, $data->pluck('data'));
        $this->sumDataForATimeFrame($this->year, $year_data);
        $this->sumDataForATimeFrame($this->month, $month_data);
        $this->sumDataForATimeFrame($this->week, $week_data);
    }

    protected function sumDataForATimeFrame(SalesStat $timeFrameData, Collection $data)
    {
        $timeFrameData->sale = $data->sum('sale');
        $timeFrameData->orderClosed = $data->sum('orderClosed');
        $timeFrameData->jobServed = $data->sum('jobServed');
    }
}