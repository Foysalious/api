<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\ShiftCalenderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Business\ShiftSetting\ShiftAssign\Requester;
use Sheba\Business\ShiftSetting\ShiftAssign\Creator;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;
use Sheba\ModificationFields;

class ShiftCalenderController extends Controller
{
    use ModificationFields;

    public function index(Request $request, ShiftCalenderRepository $shift_calender_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $start_date = $request->start_date ?: Carbon::now()->addDay()->toDateString();
        $end_date = $request->end_date ?: Carbon::now()->addDays(7)->toDateString();
        $active_business_member_ids = $business->getActiveBusinessMember()->pluck('id')->toArray();
        $shift_calender = $shift_calender_repository->builder()->whereIn('business_member_id', $active_business_member_ids)->whereBetween('date', [$start_date, $end_date]);
        $shift_calender_data = (new ShiftCalenderTransformer())->transform($shift_calender->get());
        $total_data = count($shift_calender_data['data']);
        $shift_calender_employee_data = collect($shift_calender_data['data'])->splice($offset, $limit)->toArray();
        usort($shift_calender_employee_data, array($this,'employeeSortByPDisplayPriority'));
        return api_response($request, null, 200, ['shift_calender_employee' => $shift_calender_employee_data, 'shift_calender_header' => $shift_calender_data['header'], 'total' => $total_data]);
    }

    public function assignShift($business, $id, Request $request, BusinessShiftRepository $business_shift_repository, ShiftCalenderRepository $shift_calender_repository,
    Requester $shift_calender_requester, Creator $shift_calender_creator)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->validate($request, [
            'shift_id'                  => 'required|integer',
            'date'                      => 'required|date_format:Y-m-d'
        ]);
        $this->setModifier($request->manager_member);
        $business_shift = $business_shift_repository->find($request->shift_id);
        if (!$business_shift) return api_response($request, null, 404);

        $business_shift = $business_shift_repository->find($request->shift_id);

        $shift_calender_requester->setShiftId($request->shift_id)
            ->setShiftName($business_shift->name)
            ->setStartTime($business_shift->start_time)
            ->setEndTime($business_shift->end_time)
            ->setIsHalfDayActivated($business_shift->is_halfday_enable)
            ->setIsGeneralActivated(0)
            ->setIsUnassignedActivated(0)
            ->setIsShiftActivated(1)
            ->setColorCode($business_shift->color_code);

        //if ($shift_calender_requester->hasError()) return api_response($request, null, $shift_calender_requester->getErrorCode(), ['message' => $shift_calender_requester->getErrorMessage()]);

        $shift_calender = $shift_calender_repository->find($id);
//        return api_response($request, null, 200, ['shift_details' => $shift_calender]);
        $shift_calender_creator->setShiftCalenderRequester($shift_calender_requester)->update($shift_calender);
        return api_response($request, null, 200);
    }

    private function employeeSortByPDisplayPriority($a, $b)
    {
        if ($a['display_priority'] < $b['display_priority']) return 0;
        return 1;
    }
}
