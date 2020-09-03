<?php namespace Sheba\CmDashboard;

use Carbon\Carbon;
use App\Models\PartnerOrder;
use Illuminate\Support\Facades\Auth;

class SalesGrowthForCm
{
    /** @var null */
    private $month;
    /** @var null */
    private $year;
    /** @var null */
    private $location;
    /** @var null */
    private $cm;
    /** @var null */
    private $valueField;

    /**
     * @param null $month
     * @param null $year
     * @param null $location
     * @param null $value_field
     */
    public function __construct($month = null, $year = null, $location = null, $value_field = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->location = $location;
        $this->valueField = $value_field;
        $this->cm = Auth::user()->id;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->getData( $this->month, $this->year, $this->location );
    }

    /**
     * @param null $month
     * @param null $year
     * @param null $location
     * @return mixed
     */
    public function getData($month = null, $year = null, $location = null)
    {
        $location = (strpos($location, '-') !== false) ? str_replace('-', '/', $location) : $location;
        if (is_null($month)) $month = Carbon::now()->month;
        if (empty($year)) $year = Carbon::now()->year;
        $data = [];

        if($month == 0 && $year != 0) {
            for($i=1; $i<=12; $i++) {
                $this->processOrders($data, "month", $i, $year, $location);
            }
            $data = $this->makeChartDataMonthWise($data);
        } else if($month && $year) {
            $this->processOrders($data, "day", $month, $year, $location);
            $data = $this->makeChartDataDayWise($data, $month, $year);
        }
        return $data;
    }

    /**
     * @param $data
     * @param $base
     * @param $month
     * @param $year
     * @param $location
     */
    private function processOrders(&$data, $base, $month, $year, $location)
    {
        $startEndDate = findStartEndDateOfAMonth($month, $year);
        $start_time = $startEndDate['start_time'];
        $end_time = $startEndDate['end_time'];
        PartnerOrder::with('order.location', 'jobs.usedMaterials')->whereBetween('closed_at', [$start_time, $end_time])
            ->get()->filter(function($partner_order) use ($location) {
                if ( !empty($location) && ($partner_order->order->location->name != $location) ) return false;
                $partner_order->setRelations([
                    'order' => $partner_order->order,
                    'jobs' => $partner_order->jobs->filter(function ($job) {
                        return $job->crm_id == $this->cm;
                    })
                ]);
                return $partner_order->jobs->count();
            })->each(function($partner_order) use (&$data, $start_time, $end_time, $base) {
                $this->incrementTempData($data, $partner_order->closed_at, $base, $partner_order->calculate($price_only = true)->jobPrices);
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
        if(empty($data[$base][$closed_date->$base])) {
            $data[$base][$closed_date->$base] = ($this->valueField == "count") ? 1 : $sale_amount;
        } else {
            $data[$base][$closed_date->$base] += ($this->valueField == "count") ? 1 : $sale_amount;
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
        foreach(getMonthsName() as $key => $month) {
            $i = $key + 1;
            $chart_single_data[0] = $month;
            $chart_single_data[1] = (empty($data['month'][$i])) ? 0 : formatTaka($data['month'][$i]);
            $chart_data[] = $chart_single_data;
        }
        return $chart_data;
    }
}