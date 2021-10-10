<?php namespace App\Http\Controllers\Employee;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\Appreciation\CalculationForBadge;
use App\Sheba\Business\Appreciation\EmployeeAppreciations;
use App\Transformers\Business\AppreciationEmployeeTransformer;
use App\Transformers\Business\StickerCategoryList;
use App\Sheba\Business\BusinessBasicInformation;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use League\Fractal\Resource\Item;
use Sheba\Dal\Appreciation\Appreciation;
use Sheba\Dal\BusinessMemberBadge\BusinessMemberBadgeRepository;
use Sheba\Dal\StickerCategory\StickerCategory;
use App\Sheba\Business\Appreciation\Creator;
use League\Fractal\Resource\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use App\Sheba\Business\Appreciation\Updater;

class AppreciateController extends Controller
{
    use BusinessBasicInformation, ModificationFields;

    const EARLY_BIRD_COUNTER = 12;
    const LATE_LATEEF_COUNTER = 4;

    /** @var EmployeeAppreciations $employeeAppreciations */
    private $employeeAppreciations;

    public function __construct(EmployeeAppreciations $employee_appreciations)
    {
        $this->employeeAppreciations = $employee_appreciations;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        $business_members = $business->getActiveBusinessMember();

        if ($request->has('department')) {
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department);
                });
            });
        }

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($business_members->get(), new AppreciationEmployeeTransformer());
        $employees_with_dept_data = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, [
            'employees' => $employees_with_dept_data['employees'],
            'departments' => $employees_with_dept_data['departments']
        ]);
    }

    /**
     * @param Business $business
     * @param Request $request
     * @return mixed
     */
    private function accessibleBusinessMembers(Business $business, Request $request)
    {
        if ($request->has('for') && $request->for == 'phone_book') return $business->getActiveBusinessMember();
        return $business->getAccessibleBusinessMember();
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator)
    {
        $this->validate($request, [
            'sticker' => 'required',
            'receiver_id' => 'required'
        ]);

        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $creator->setSticker($request->sticker)
            ->setReceiver($request->receiver_id)
            ->setGiver($business_member->id)
            ->setComplement($request->complement);

        $appreciation = $creator->create();
        $appreciation->fresh();

        return api_response($request, null, 200, ['appreciation_id' => $appreciation->id]);
    }

    /**
     * @param $appreciation_id
     * @param Request $request
     * @param Updater $updater
     * @return JsonResponse
     */
    public function update($appreciation_id, Request $request, Updater $updater)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $appreciation = Appreciation::find((int)$appreciation_id);
        $updater->setAppreciation($appreciation)
            ->setSticker($request->sticker)
            ->setComplement($request->complement)
            ->update();

        return api_response($request, null, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryWiseStickers(Request $request)
    {
        $sticker_categories = StickerCategory::all(['id', 'name', 'title']);

        $fractal = new Manager();
        $resource = new Collection($sticker_categories, new StickerCategoryList());
        $sticker_categories = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $sticker_categories, 200, ['stickers' => $sticker_categories]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function myStickers(Request $request)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $employee_appreciations = $this->employeeAppreciations->getEmployeeAppreciations($business_member);

        return api_response($request, null, 200, [
            'stickers' => $employee_appreciations['stickers'],
            'complements' => $employee_appreciations['complements']
        ]);
    }

    /**
     * @param $business_member_id
     * @param Request $request
     * @return JsonResponse
     */
    public function coworkerStickers($business_member_id, Request $request)
    {
        $business_member = BusinessMember::find((int)$business_member_id);
        if (!$business_member) return api_response($request, null, 404);

        $employee_appreciations = $this->employeeAppreciations->getEmployeeAppreciations($business_member);

        return api_response($request, null, 200, [
            'stickers' => $employee_appreciations['stickers'],
            'complements' => $employee_appreciations['complements']
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getNewJoiner(Request $request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        $business_members = $business->getActiveBusinessMember()->get();

        $new_employers = [];
        foreach ($business_members as $business_member) {
            if (!$business_member->isNewJoiner()) continue;
            /** @var Member $member */
            $member = $business_member->member;
            /** @var Profile $profile */
            $profile = $member->profile;
            array_push($new_employers, [
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic
            ]);
        }

        return api_response($request, null, 200, ['new_employees' => $new_employers]);
    }

    /**
     * @param Request $request
     * @param CalculationForBadge $calculation_for_badge
     * @return JsonResponse
     */
    public function badgeCounter(Request $request, CalculationForBadge $calculation_for_badge)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $badge_counter = [
            'late_lateef' => [
                'counter' => $business_member->late_loteef_counter,
                'threshold' => self::LATE_LATEEF_COUNTER
            ],
            'early_bird' => [
                'counter' => $business_member->early_bird_counter,
                'threshold' => self::EARLY_BIRD_COUNTER
            ]
        ];

        return api_response($request, null, 200, ['badge_counter' => $badge_counter]);
    }

    /**
     * @param Request $request
     * @param BusinessMemberBadgeRepository $badgeRepository
     * @return JsonResponse
     */
    public function badgeSeen(Request $request, BusinessMemberBadgeRepository $badgeRepository)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        /** Check Employee Already Get a Badge or Not */
        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();
        $business_member_badge = $badgeRepository->where('business_member_id', $business_member->id)
            ->whereBetween('end_date', [$start_date, $end_date])->first();

        if (!$business_member_badge) return api_response($request, null, 200);
        $business_member_badge->update(['is_seen' => 1]);
        return api_response($request, null, 200);
    }
}