<?php namespace Sheba\Charts;

use App\Models\Partner;
use Carbon\Carbon;
use App\Models\PartnerOrder;

class SalesGrowth
{
    /** @var null */
    private $month;
    /** @var null */
    private $year;
    /** @var null */
    private $location;

    /** @var null */
    private $service;
    private $category;
    private $resource;

    /** @var null */
    private $partner;

    /**
     * @param $partner
     * @param null $month
     * @param null $year
     * @param null $location
     * @param null $service
     * @param null $category
     * @param null $resource
     */
    public function __construct($partner, $month = null, $year = null, $location = null, $service = null, $category = null, $resource = null)
    {
        $this->partner = $partner instanceof Partner ? $partner : Partner::find($partner);
        $this->month = $month;
        $this->year = $year;
        $this->location = $location;
        $this->service = $service;
        $this->category = $category;
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->getData($this->month, $this->year, $this->location, $this->service, $this->category, $this->resource);
    }

    public function getWeekData()
    {
        $start_time = Carbon::now()->startOfWeek(Carbon::SATURDAY);
        $end_time = Carbon::now()->endOfWeek(Carbon::FRIDAY);
        $this->processOrders($data, "day", $start_time, $end_time, $this->location, $this->service, $this->category, $this->resource);
        return $this->_getBreakdown($data);
    }

    /**
     * @param null $month
     * @param null $year
     * @param null $location
     * @param null $service
     * @param null $category
     * @param null $resource
     * @return mixed
     */
    public function getData($month = null, $year = null, $location = null, $service = null, $category = null, $resource = null)
    {
        $location = (strpos($location, '-') !== false) ? str_replace('-', '/', $location) : $location;
        if (is_null($month)) $month = Carbon::now()->month;
        if (empty($year)) $year = Carbon::now()->year;
        $data = [];

        if ($month == 0 && $year != 0) {
            for ($i = 1; $i <= 12; $i++) {
                $this->processOrderWithMonth($data, 'month', $i, $year, $location, $service, $category, $resource);
            }
            return $data;
        } else if ($month && $year) {
            $this->processOrderWithMonth($data, 'day', $month, $year, $location, $service, $category, $resource);
            return $this->_getBreakdown($data);
        }
    }

    /**
     * @param $month
     * @param $year
     * @return array
     */
    private function getStartEndTime($month, $year)
    {
        $startEndDate = findStartEndDateOfAMonth($month, $year);
        return array($startEndDate['start_time'], $startEndDate['end_time']);
    }

    private function processOrderWithMonth(&$data, $base, $month, $year, $location, $service, $category, $resource)
    {
        list($start_time, $end_time) = $this->getStartEndTime($month, $year);
        $this->processOrders($data, $base, $start_time, $end_time, $location, $service, $category, $resource);
    }

    /**
     * @param $data
     * @param $base
     * @param $start_time
     * @param $end_time
     * @param $location
     * @param $service
     * @param $category
     * @param $resource
     * @internal param $month
     * @internal param $year
     */
    private function processOrders(&$data, $base, $start_time, $end_time, $location, $service, $category, $resource)
    {
        PartnerOrder::with('order.location', 'jobs.usedMaterials')->where('partner_id', $this->partner->id)->whereBetween('closed_at', [$start_time, $end_time])
            ->get()->filter(function ($partner_order) use ($location, $service, $category, $resource) {
                if (!empty($location) && ($partner_order->order->location->name != $location)) return false;
                if (!empty($service) || !empty($category) || !empty($resource)) {
                    $partner_order->setRelations([
                        'order' => $partner_order->order,
                        'jobs' => $partner_order->jobs->filter(function ($job) use ($service, $category, $resource) {
                            $service_matched = (!empty($service)) ? $job->service_id == $service : true;
                            $category_matched = (!empty($category)) ? $job->service->category_id == $category : true;
                            $resource_matched = (!empty($resource)) ? $job->resource_id == $resource : true;
                            return ($service_matched && $category_matched && $resource_matched);
                        })
                    ]);
                    return $partner_order->jobs->count();
                };
                return true;
            })->each(function ($partner_order) use (&$data, $start_time, $end_time, $base) {
                $this->incrementTempData($data, $partner_order->closed_at, $base, $partner_order->calculate($price_only = true)->totalCost);
            });
    }

    /**
     * @param $data
     * @param $closed_date
     * @param $base
     * @param $sale_amount
     */
    private function incrementTempData(&$data, $closed_date, $base, $sale_amount)
    {
        if (empty($data[$base][$closed_date->$base])) {
            $data[$base][$closed_date->$base] = $sale_amount;
        } else {
            $data[$base][$closed_date->$base] += $sale_amount;
        }
    }

    /**
     * @param $data
     * @param $month
     * @param $year
     * @return mixed
     */
    private function makeChartDataDayWise($data, $month, $year)
    {
        $chart_data = [];
        for ($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++) {
            $chart_single_data[0] = $i;
            $chart_single_data[1] = (empty($data['day'][$i])) ? 0 : formatTaka($data['day'][$i]);
            $chart_data[] = $chart_single_data;
        }
        return $chart_data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function makeChartDataMonthWise($data)
    {
        $chart_data = [];
        foreach (getMonthsName() as $key => $month) {
            $i = $key + 1;
            $chart_single_data[0] = $month;
            $chart_single_data[1] = (empty($data['month'][$i])) ? 0 : formatTaka($data['month'][$i]);
            $chart_data[] = $chart_single_data;
        }
        return $chart_data;
    }

    private function _getBreakdown($data)
    {
        $breakdown = collect(array_fill(1, Carbon::create($this->year, $this->month)->daysInMonth, 0));
        if (empty($data)) {
            return $breakdown;
        }
        $collection = collect($data['day'])->map(function ($item, $key) {
            return (double)$item;
        })->sortBy(function ($item, $key) {
            return $key;
        });
        $breakdown = $breakdown->map(function ($item, $key) use ($collection) {
            return $collection->has($key) ? $collection->get($key) : 0;
        });
        return $breakdown;
    }
}