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
use Sheba\Business\ShiftSetting\Creator as ShiftSettingCreator;
use Sheba\Business\ShiftSetting\Requester as ShiftSettingRequester;
use Sheba\Business\ShiftSetting\ShiftAssign\ShiftRemover;
use Sheba\Business\ShiftSetting\Updater as ShiftSettingUpdater;
use Sheba\Business\ShiftCalendar\Updater as ShiftCalendarUpdater;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;
use Sheba\Business\ShiftSetting\ShiftAssign\Requester as ShiftCalendarRequester;
use Sheba\ModificationFields;

class ShiftSettingController extends Controller
{
    use ModificationFields;

    /*** @var ShiftSettingRequester */
    private $shiftRequester;
    /*** @var ShiftSettingCreator */
    private $shiftCreator;
    /** * @var BusinessShiftRepository */
    private $businessShiftRepository;
    /*** @var ShiftAssignmentRepository */
    private $shiftAssignmentRepo;
    /*** @var ShiftCalendarRequester */
    private $shiftCalendarRequester;
    /** * @var ShiftRemover */
    private $shiftRemover;
    /*** @var ShiftSettingUpdater */
    private $shiftUpdater;
    /*** @var ShiftCalendarUpdater */
    private $shiftCalendarUpdater;

    public function __construct(ShiftSettingRequester     $shift_requester, ShiftSettingCreator $shift_creator, ShiftSettingUpdater $shift_updater, BusinessShiftRepository $business_shift_repository,
                                ShiftAssignmentRepository $shift_assignment_repo, ShiftCalendarRequester $shift_calendar_requester, ShiftRemover $shift_remover, ShiftCalendarUpdater $shift_calendar_updater)
    {
        $this->shiftRequester = $shift_requester;
        $this->shiftCreator = $shift_creator;
        $this->shiftUpdater = $shift_updater;
        $this->businessShiftRepository = $business_shift_repository;
        $this->shiftAssignmentRepo = $shift_assignment_repo;
        $this->shiftCalendarRequester = $shift_calendar_requester;
        $this->shiftRemover = $shift_remover;
        $this->shiftCalendarUpdater = $shift_calendar_updater;
    }

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

    public function create(Request $request)
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

        $this->shiftRequester->setBusiness($business)
            ->setName($request->name)
            ->setTitle($request->title)
            ->setStartTime($request->start_time)
            ->setEndTime($request->end_time)
            ->setIsCheckInGraceAllowed($request->is_checkin_grace_allow)
            ->setIsCheckOutGraceAllowed($request->is_checkout_grace_allow)
            ->setCheckInGraceTime($request->checkin_grace_time)
            ->setCheckOutGraceTime($request->checkout_grace_time)
            ->setIsHalfDayActivated($request->is_half_day);

        if ($this->shiftRequester->hasError()) return api_response($request, null, $this->shiftRequester->getErrorCode(), ['message' => $this->shiftRequester->getErrorMessage()]);

        $this->shiftCreator->setShiftRequester($this->shiftRequester)->create();
        return api_response($request, null, 200);
    }

    public function delete($business, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $this->businessShiftRepository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $shift_calender = $this->shiftAssignmentRepo->where('shift_id', $business_shift->id)->where('date', '>', Carbon::now()->addDay()->toDateString())->get();
        $business_shift->delete();
        $this->shiftCalendarRequester
            ->setIsUnassignedActivated(1)
            ->setIsGeneralActivated(0)
            ->setIsShiftActivated(0);

        foreach ($shift_calender as $shift) {
            $this->shiftRemover->setShiftCalenderRequester($this->shiftCalendarRequester)->update($shift);
        }
        return api_response($request, null, 200);
    }

    public function details($business, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $this->businessShiftRepository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($business_shift, new ShiftDetailsTransformer());
        $business_shift = $manager->createData($member)->toArray()['data'];
        return api_response($request, $business_shift, 200, ['shift_details' => $business_shift]);
    }

    public function updateColor($business, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $business_shift = $this->businessShiftRepository->find($id);
        if (!$business_shift) return api_response($request, null, 404);
        $shift_calender = $this->shiftAssignmentRepo->where('shift_id', $business_shift->id)->where('date', '>', Carbon::now()->addDay()->toDateString())->get();
        $this->shiftRequester->setShift($business_shift)->setColor($request->color);
        $this->shiftUpdater->setShiftRequester($this->shiftRequester)->updateColor();
        $this->shiftCalendarRequester->setColorCode($request->color);
        foreach ($shift_calender as $shift) {
            $this->shiftCalendarUpdater->setShiftCalenderRequester($this->shiftCalendarRequester)->update($shift);
        }

        return api_response($request, null, 200);
    }

    public function updateShift($business, $id, Request $request)
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
        $business_shift = $this->businessShiftRepository->find($id);
        if (!$business_shift) return api_response($request, null, 404);

        $this->setModifier($business_member->member);

        $this->shiftRequester->setBusiness($business)
            ->setShift($business_shift)
            ->setName($request->name);
        $this->shiftRequester->checkUniqueName();
        if ($this->shiftRequester->hasError()) return api_response($request, null, $this->shiftRequester->getErrorCode(), ['message' => $this->shiftRequester->getErrorMessage()]);
        $this->shiftRequester->setTitle($request->title)
            ->setStartTime($request->start_time)
            ->setEndTime($request->end_time);
        if ($this->shiftRequester->hasError()) return api_response($request, null, $this->shiftRequester->getErrorCode(), ['message' => $this->shiftRequester->getErrorMessage()]);
        $this->shiftRequester->setIsCheckInGraceAllowed($request->is_checkin_grace_allow)
            ->setIsCheckOutGraceAllowed($request->is_checkout_grace_allow)
            ->setCheckInGraceTime($request->checkin_grace_time)
            ->setCheckOutGraceTime($request->checkout_grace_time)
            ->setIsHalfDayActivated($request->is_half_day)
            ->shiftConflictCheck();
        if ($this->shiftRequester->hasError()) return api_response($request, null, $this->shiftRequester->getErrorCode(), ['message' => $this->shiftRequester->getErrorMessage()]);
        $this->shiftUpdater->setShiftRequester($this->shiftRequester)->update();
        return api_response($request, null, 200);
    }

    public function getColor($business, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        return api_response($request, null, 200, ['colors' => config('b2b.SHIFT_COLORS')]);
    }
}
