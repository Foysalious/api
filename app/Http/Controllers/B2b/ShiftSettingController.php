<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\ShiftDetailsTransformer;
use App\Transformers\Business\ShiftListTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\ShiftSetting\Creator;
use Sheba\Business\ShiftSetting\Requester;
use Sheba\Business\ShiftSetting\ShiftAssign\ShiftRemover;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;
use Sheba\Business\ShiftSetting\ShiftAssign\Requester as ShiftCalendarRequester;
use Sheba\ModificationFields;

class ShiftSettingController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $shifts = new Collection($business->shifts, new ShiftListTransformer());
        $shifts = collect($manager->createData($shifts)->toArray()['data']);
        return api_response($request, $shifts, 200, ['shift' => $shifts]);
    }

    public function create(Request $request, Requester $shift_requester, Creator $shift_creator)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->validate($request, [
            'name' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_checkin_grace_allow' => 'required|in:0,1',
            'is_checkout_grace_allow' => 'required|in:0,1',
            'checkin_grace_time' => 'required_if:is_checkin_grace_allow, == , 1',
            'checkout_grace_time' => 'required_if:is_checkout_grace_allow, == , 1',
            'is_half_day' => 'required|in:0,1'
        ]);

        $this->setModifier($business_member->member);

        $shift_requester->setBusiness($business)
            ->setName($request->name)
            ->setTitle($request->title)
            ->setStartTime($request->start_time)
            ->setEndTime($request->end_time)
            ->setIsCheckInGraceAllowed($request->is_checkin_grace_allow)
            ->setIsCheckOutGraceAllowed($request->is_checkout_grace_allow)
            ->setCheckInGraceTime($request->checkin_grace_time)
            ->setCheckOutGraceTime($request->checkout_grace_time)
            ->setIsHalfDayActivated($request->is_half_day);

        if ($shift_requester->hasError()) return api_response($request, null, $shift_requester->getErrorCode(), ['message' => $shift_requester->getErrorMessage()]);

        $shift_creator->setShiftRequester($shift_requester)->create();
        return api_response($request, null, 200);
    }

    public function delete($business, $id, Request $request, BusinessShiftRepository $business_shift_repository, ShiftCalenderRepository $shift_calender_repo, ShiftCalendarRequester $shift_calendar_requester, ShiftRemover $shift_remover)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $business_shift_repository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $shift_calender = $shift_calender_repo->where('shift_id', $business_shift->id)->where('date', '>', Carbon::now()->addDay()->toDateString())->get();
        $business_shift->delete();
        $shift_calendar_requester
            ->setIsGeneralActivated(1)
            ->setIsShiftActivated(0);

        foreach($shift_calender as $shift)
        {
            $shift_remover->setShiftCalenderRequester($shift_calendar_requester)->update($shift);
        }
        return api_response($request, null, 200);
    }

    public function details($business, $id, Request $request, BusinessShiftRepository $business_shift_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $business_shift_repository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($business_shift, new ShiftDetailsTransformer());
        $business_shift = $manager->createData($member)->toArray()['data'];
        return api_response($request, $business_shift, 200, ['shift_details' => $business_shift]);
    }

}
