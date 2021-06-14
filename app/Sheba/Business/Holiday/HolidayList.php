<?php namespace Sheba\Business\Holiday;

use App\Models\Business;
use Carbon\Carbon;
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
        $previous_year = Carbon::now()->subYear()->format('Y');
        $previous_year_last_date = Carbon::parse($previous_year.'-12'.'-31')->endOfDay();
        $business_holidays = $this->business_holidays_repo->getAllByBusiness($this->business)->filter(function ($holiday) use ($previous_year_last_date) {
            return $previous_year_last_date->lt($holiday->start_date);
        });
        if($request->has('search')) $business_holidays = $this->searchWithHolidayName($business_holidays,$request);
        foreach ($business_holidays as $holiday) {
            $diff_in_days = $holiday->start_date->diffInDays($holiday->end_date);
            array_push($holiday_list, [
                'id' => $holiday->id,
                'start_date' => $holiday->start_date->format('d/m/Y'),
                'end_date' => $holiday->end_date->format('d/m/Y'),
                'day_difference' => $holiday->start_date->diffInDays($holiday->end_date),
                'date' => $diff_in_days === 0 ? $holiday->start_date->format('d F, Y') : $holiday->start_date->format('d F, Y') . ' - ' . $holiday->end_date->format('d F, Y'),
                'total_days' => $diff_in_days === 0 ? ($diff_in_days + 1).' day' : ($diff_in_days + 1).' days',
                'name' => $holiday->title
            ]);
        }
        $business_holidays = collect($holiday_list);
        if($request->has('sort_on_date')) $business_holidays = $this->holidaySortOnDate($business_holidays,$request->sort_on_date)->values();
        if($request->has('sort_on_days')) $business_holidays = $this->holidaySortOnDays($business_holidays,$request->sort_on_days)->values();
        if($request->has('sort_on_name')) $business_holidays = $this->holidaySortOnName($business_holidays,$request->sort_on_name)->values();
        return $business_holidays;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAllHolidayDates(Request $request)
    {
        $holiday_list = [];
        $business_holidays = $this->business_holidays_repo->getAllByBusiness($this->business);

        foreach ($business_holidays as $holiday) {
            for ($d = $holiday->start_date; $d->lte($holiday->end_date); $d->addDay()) {
                $holiday_list[] = $d->format('Y-m-d');
            }
        }

        return array_unique($holiday_list);
    }

    private function searchWithHolidayName($business_holidays, Request $request)
    {
        return $business_holidays->filter(function ($business_holiday) use ($request){
            return str_contains(strtoupper($business_holiday->title), strtoupper($request->search));
        });
    }

    private function holidaySortOnDate($business_holidays, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $business_holidays->$sort_by(function ($business_holiday, $key) {
            return Carbon::createFromFormat('d/m/Y',  $business_holiday['start_date']);
        });
    }

    private function holidaySortOnDays($business_holidays, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $business_holidays->$sort_by(function ($business_holiday, $key) {
            return strtoupper($business_holiday['day_difference']);
        });
    }

    private function holidaySortOnName($business_holidays, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $business_holidays->$sort_by(function ($business_holiday, $key) {
            return strtoupper($business_holiday['name']);
        });
    }
}