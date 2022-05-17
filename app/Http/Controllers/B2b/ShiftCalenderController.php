<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\PayReportListTransformer;
use App\Transformers\Business\ShiftCalenderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;

class ShiftCalenderController extends Controller
{
    public function index(Request $request, ShiftCalenderRepository $shift_calender_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $start_date = $request->start_date ?: Carbon::now()->addDay()->toDateString();
        $end_date = $request->end_date ?: Carbon::now()->addDays(7)->toDateString();
        $active_business_member_ids = $business->getActiveBusinessMember()->pluck('id')->toArray();
        $shift_calender = $shift_calender_repository->builder()->whereIn('business_member_id', $active_business_member_ids)->whereBetween('date', [$start_date, $end_date]);
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $shift_calender_transformer = new ShiftCalenderTransformer();
        $shift_calender = new Collection($shift_calender, $shift_calender_transformer);
        $shift_calender = collect($manager->createData($shift_calender)->toArray()['data']);
    }
}
