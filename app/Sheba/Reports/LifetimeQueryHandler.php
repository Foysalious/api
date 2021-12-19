<?php namespace Sheba\Reports;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;

trait LifetimeQueryHandler
{
    protected function isNotLifetime($request_data)
    {
        return empty($request_data['is_lifetime']);
    }

    protected function isLifetime($request_data)
    {
        return !$this->isNotLifetime($request_data);
    }

    /**
     * @param QueryBuilder | EloquentBuilder $query
     * @param $request_data
     * @param string $field
     * @return mixed
     */
    protected function notLifetimeQuery($query, $request_data, $field = 'created_at')
    {
        $query->where($field, '<>', null);
        if ($this->isNotLifetime($request_data)) {
            $start_time = $request_data['start_date'] . ' 00:00:00';
            $end_time = $request_data['end_date'] . ' 23:59:59';
            $query = $query->whereBetween($field, [$start_time, $end_time]);
        }
        return $query;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getStartEnd($request)
    {
        $from_date = $request->filled('start_date') ? $request->start_date : '2016-01-01';
        $to_date = $request->filled('end_date') ? $request->end_date : Carbon::today()->toDateString();
        $from_date .= ' 00:00:00';
        $to_date .= ' 23:59:59';

        return [$from_date, $to_date];
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getStartEndCarbon($request)
    {
        $from_date = $request->filled('start_date') ? $request->start_date : '2016-01-01';
        $to_date = $request->filled('end_date') ? $request->end_date : Carbon::tomorrow()->subSecond();

        $from_date = Carbon::parse($from_date);
        $to_date = Carbon::parse($to_date)->endOfDay();

        return [$from_date, $to_date];
    }
}