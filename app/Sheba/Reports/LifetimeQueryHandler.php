<?php namespace Sheba\Reports;

use Carbon\Carbon;

trait LifetimeQueryHandler
{
    protected function notLifetimeQuery($query, $request_data, $field = 'created_at')
    {
        $query->where($field, '<>', null);
        if (empty($request_data['is_lifetime'])) {
            $start_time = $request_data['start_date'] . ' 00:00:00';
            $end_time = $request_data['end_date'] . ' 23:59:59';
            $query = $query->whereBetween($field, [$start_time, $end_time]);
        }
        return $query;
    }
    protected function getStartEnd($request){
        $from_date = $request->has('start_date') ? $request->start_date : '2016-01-01';
        $to_date = $request->has('end_date') ? $request->end_date : Carbon::today()->toDateString();
        $from_date .= ' 00:00:00';
        $to_date .= ' 23:59:59';
        return [$from_date,$to_date];
    }
}