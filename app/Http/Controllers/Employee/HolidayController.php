<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
use App\Sheba\Business\Holiday\MonthlyHolidayDates;
use App\Sheba\Business\Weekend\MonthlyWeekendDates;
use App\Transformers\Business\HolidayListTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Helpers\TimeFrame;
use App\Sheba\Business\Leave\MonthlyLeaveDates;

class HolidayController extends Controller
{
    use BusinessBasicInformation;

    /**
     * @param Request $request
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @return JsonResponse
     */
    public function getHolidays(Request $request, BusinessHolidayRepoInterface $business_holiday_repo, BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $business = $business_member->business;

        if(!$business) return api_response($request, null, 404);

        $firstDayOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth();
        $lastDayOfNextMonth = Carbon::now()->startOfMonth()->addMonths(1)->endOfMonth();

        $business_holidays = $business_holiday_repo->getAllByBusiness($business);
        $weekend = $business_weekend_repo->weekendDates($business);
        sort($weekend);
        $fractal = new Manager();
        $resource = new Collection($business_holidays, new HolidayListTransformer($firstDayOfPreviousMonth, $lastDayOfNextMonth));
        $holidays = $fractal->createData($resource)->toArray()['data'];
        
        $holidays = $holidays ? call_user_func_array('array_merge', $holidays) : [];
        sort($holidays);
        return api_response($request, null, 200, ['holidays' => $holidays, 'weekends' => $weekend, 'is_sandwich_leave_enable' => $business->is_sandwich_leave_enable]);
    }

    /**
     * @param Request $request
     * @param MonthlyLeaveDates $leave_dates
     * @param MonthlyWeekendDates $weekend_dates
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param MonthlyHolidayDates $holiday_dates
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function getMonthlyLeavesHolidays(Request $request, MonthlyLeaveDates $leave_dates, MonthlyWeekendDates $weekend_dates, BusinessHolidayRepoInterface $business_holiday_repo, MonthlyHolidayDates $holiday_dates, TimeFrame $time_frame)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $business = $business_member->business;
        if(!$business) return api_response($request, null, 404);
        $this->validate($request, ['year' => 'required|string', 'month' => 'required|string']);

        $time_frame = $time_frame->forAMonth($request->month, $request->year);

        $business_holidays = $business_holiday_repo->getAllByBusiness($business);
        $leaves = $leave_dates->setTimeFrame($time_frame)->setBusinessMember($business_member)->getLeaveDates();
        $weekends = $weekend_dates->setBusiness($business)->setTimeFrame($time_frame)->getWeekends();
        $time_frame = $time_frame->forAMonth($request->month, $request->year);
        $holidays = $holiday_dates->setTimeFrame($time_frame)->setBusinessHolidays($business_holidays)->getHolidays();

        return api_response($request, null, 200, ['holidays' => $holidays, 'weekends' => $weekends, 'leave_dates' => $leaves, 'is_sandwich_leave_enable' => $business->is_sandwich_leave_enable]);
    }
}
