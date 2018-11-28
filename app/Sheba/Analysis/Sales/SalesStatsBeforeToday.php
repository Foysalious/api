<?php namespace Sheba\Analysis\Sales;

use Carbon\Carbon;
use Cache;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;

abstract class SalesStatsBeforeToday
{
    public $week;
    public $month;
    public $year;
    public $lifetime;

    protected $redisCacheName;
    
    /** @var  TimeFrame */
    protected $yearTimeFrame;
    protected $weekTimeFrame;

    public function __construct()
    {
        $this->initializeTimeFrames();
        $this->week     = new SalesStat();
        $this->month    = new SalesStat();
        $this->year     = new SalesStat();
        $this->lifetime = new SalesStat();
    }

    public function get()
    {
        if (!$this->getFromRedis()) {
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
        $this->yearTimeFrame = (new TimeFrame())->forAYear(Carbon::now()->year);
        $currentWeek = (new TimeFrame())->forCurrentWeek();
        $this->weekTimeFrame = [$currentWeek->start, $currentWeek->end];
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