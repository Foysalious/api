<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessBasicInformation;
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
}
