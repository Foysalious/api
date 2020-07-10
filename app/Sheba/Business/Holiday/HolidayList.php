<?php namespace Sheba\Business\Holiday;

use App\Models\Business;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Illuminate\Http\Request;

class HolidayList
{
    private $business;
    private $business_holidays_repo;

    public function __construct(Business $business, BusinessHolidayRepoInterface $business_holidays_repo)
    {
        $this->business = $business;
        $this->business_holidays_repo = $business_holidays_repo;
    }

    public function getHolidays(Request $request)
    {
        $holiday_list = [];
        $business_holidays = $this->business_holidays_repo->getAllByBusiness($this->business);
        if($request->has('search')) $business_holidays = $this->searchWithHolidayName($business_holidays,$request);
        if($request->has('sort')) $business_holidays = $this->holidaySort($business_holidays,$request->sort);
        foreach ($business_holidays as $holiday) {
            $diff_in_days = $holiday->start_date->diffInDays($holiday->end_date);
            array_push($holiday_list, [
                'id' => $holiday->id,
                'start_date' => $holiday->start_date->format('d/m/Y'),
                'end_date' => $holiday->end_date->format('d/m/Y'),
                'day_difference' => $holiday->start_date->diffInDays($holiday->end_date),
//                'date' => $diff_in_days === 0 ? $holiday->start_date->format('d M, Y') : $holiday->start_date->format('d M, Y') . ' - ' . $holiday->end_date->format('d M, Y'),
                'total_days' => $diff_in_days === 0 ? ($diff_in_days + 1).' day' : ($diff_in_days + 1).' days',
                'name' => $holiday->title
            ]);
        }
        return $holiday_list;
    }

    private function searchWithHolidayName($business_holidays, Request $request)
    {
        return $business_holidays->filter(function ($business_holiday) use ($request){
            return str_contains(strtoupper($business_holiday->title), strtoupper($request->search));
        });
    }

    private function holidaySort($business_holidays, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $business_holidays->$sort_by(function ($business_holiday) {
            return strtoupper($business_holiday->title);
        });
    }
}