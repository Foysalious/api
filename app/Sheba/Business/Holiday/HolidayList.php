<?php namespace Sheba\Business\Holiday;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\GovernmentHolidays\Contract as GovtHolidaysRepoInterface;
use Illuminate\Http\Request;

class HolidayList
{
    private $business;
    private $govt_holidays_repo;

    public function __construct(Business $business, GovtHolidaysRepoInterface $govt_holidays_repo)
    {
        $this->business = $business;
        $this->govt_holidays_repo = $govt_holidays_repo;
    }

    public function getHolidays(Request $request)
    {
        $holiday_list = [];
        $year_range = $this->getYearRange();
        $govt_holidays = $this->govt_holidays_repo->getAllByBusinessForTwoYears($this->business, $year_range);
        if($request->has('search')) $govt_holidays = $this->searchWithHolidayName($govt_holidays,$request);
        if($request->has('sort')) $govt_holidays = $this->holidaySort($govt_holidays,$request->sort);
        foreach ($govt_holidays as $holiday) {
            $diff_in_days = $holiday->start_date->diffInDays($holiday->end_date);
            array_push($holiday_list, [
                'date' => $diff_in_days === 0 ? $holiday->start_date->format('d M, Y') : $holiday->start_date->format('d M, Y') . ' - ' . $holiday->end_date->format('d M, Y'),
                'total_days' => $diff_in_days === 0 ? ($diff_in_days + 1).' day' : ($diff_in_days + 1).' days',
                'name' => $holiday->title
            ]);
        }
        return $holiday_list;
    }

    private function getYearRange()
    {
        $time_range = [];
        $first_date_of_current_year = Carbon::now()->startOfYear();
        $last_date_of_next_year = Carbon::now()->addYear()->endOfYear();
        $time_range = [$first_date_of_current_year, $last_date_of_next_year];
        return $time_range;
    }

    private function searchWithHolidayName($govt_holidays, Request $request)
    {
        return $govt_holidays->filter(function ($govt_holiday) use ($request){
            return str_contains(strtoupper($govt_holiday->title), strtoupper($request->search));
        });
    }

    private function holidaySort($govt_holidays, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $govt_holidays->$sort_by(function ($govt_holiday) {
            return strtoupper($govt_holiday->title);
        });
    }
}