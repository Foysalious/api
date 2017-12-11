<?php namespace Sheba\Analysis\Sales;

use Carbon\Carbon;
use Cache;
use Illuminate\Support\Collection;

abstract class SalesStatsBeforeToday
{
    public $week;
    public $month;
    public $year;
    public $lifetime;

    protected $redisCacheName;
    protected $yearTimeFrame;
    protected $weekTimeFrame;

    public function __construct()
    {
        $this->initializeTimeFrames();
        $this->week = new SalesStat();
        $this->month = new SalesStat();
        $this->year = new SalesStat();
        $this->lifetime = new SalesStat();
    }

    public function get()
    {
        if(!$this->getFromRedis()) {
            $this->saveToRedis();
        }
        return $this;
    }

    public function saveToRedis()
    {
        $this->calculateFromDB();
        $this->setToRedis();
    }

    private function initializeTimeFrames()
    {
        $startEndDate = findStartEndDateOfAMonth(0, Carbon::now()->year);
        $year_start = $startEndDate['start_time'];
        $year_end = $startEndDate['end_time'];
        $this->yearTimeFrame = [$year_start, $year_end];
        Carbon::setWeekStartsAt(Carbon::SATURDAY);
        $this->weekTimeFrame = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
    }

    private function getFromRedis()
    {
        $data = Cache::store('redis')->get($this->redisCacheName);
        if($this->redisHasProperData($data)) {
            $this->week = $data->week;
            $this->month = $data->month;
            $this->year = $data->year;
            $this->lifetime = $data->lifetime;
            return true;
        }
        return false;
    }

    private function setToRedis()
    {
        Cache::store('redis')->put($this->redisCacheName, $this, Carbon::tomorrow());
    }

    private function redisHasProperData($data)
    {
        return !empty($data) && property_exists($data, 'week') && property_exists($data, 'month') && property_exists($data, 'year');
    }

    abstract protected function calculateFromDB();
    abstract protected function sumDataForATimeFrame(SalesStat $timeFrameData, Collection $data);
}