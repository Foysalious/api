<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use Sheba\Business\ShiftSetting\Creator;
use Sheba\Business\ShiftSetting\Requester;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\ModificationFields;

class ShiftSettingController extends Controller
{
    use ModificationFields;

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

    public function delete($business, $id, Request $request, BusinessShiftRepository $business_shift_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $business_shift_repository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $business_shift->delete();
        return api_response($request, null, 200);
    }

}
