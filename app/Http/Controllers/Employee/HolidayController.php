<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use App\Transformers\Business\HolidayListTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;

class HolidayController extends Controller
{
    public function getHolidays(Request $request, BusinessHolidayRepoInterface $business_holiday_repo,BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $firstDayofPreviousMonth = Carbon::parse(Carbon::now()->startOfMonth()->subMonth()->toDateString());
        $lastDayofNextMonth = Carbon::parse(Carbon::now()->startOfMonth()->addMonths(1)->endOfMonth()->toDateString());
        $business_holidays = $business_holiday_repo->getAllByBusiness($business_member->business);
        $weekend = $business_weekend_repo->weekendDates($business_member->business);
        $fractal = new Manager();
        $resource = new Collection($business_holidays, new HolidayListTransformer($firstDayofPreviousMonth,$lastDayofNextMonth));
        $holidays = $fractal->createData($resource)->toArray()['data'];
        $holidays = call_user_func_array('array_merge', $holidays);
        return api_response($request, null,200, ['holidays' => $holidays, 'weekends' => $weekend]);
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::find($business_member['id']);
    }
}
