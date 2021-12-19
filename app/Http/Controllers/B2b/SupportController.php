<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Models\Member;
use Exception;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\Support\Model as Support;
use Illuminate\Http\Request;
use Sheba\Business\Support\Updater;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Throwable;

class SupportController extends Controller
{
    /** @var SupportRepositoryInterface */
    private $repo;

    public function __construct(SupportRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param $member
     * @param $support
     * @param Updater $updater
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resolve($member, $support, Updater $updater, Request $request)
    {
        $support = $this->repo->find($support);
        if (!$support) return api_response($request, null, 404);
        $business_member = $request->business_member;
        $support = $updater->setSupport($support)->setBusinessMember($business_member)->resolve();
        if (!$support) return api_response($request, null, 500);
        return api_response($request, $support, 200);
    }

    /**
     * @param $business
     * @param Request $request
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @return JsonResponse
     */
    public function index($business, Request $request, BusinessMemberRepositoryInterface $business_member_repository)
    {
        $members = $business_member_repository->where('business_id', $business)->select('id', 'member_id')->get()->pluck('member_id')->toArray();

        list($offset, $limit) = calculatePagination($request);
        $supports = Support::whereIn('member_id', $members)->select('id', 'member_id', 'status', 'long_description', 'created_at', 'closed_at', 'is_satisfied');

        if ($request->filled('status')) $supports = $supports->where('status', $request->status);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $time_frame = (new TimeFrame())->forTwoDates($request->start_date, $request->end_date);
            $supports = $supports->whereBetween('created_at', $time_frame->getArray());
        }
        $supports_without_limit = clone $supports;

        if ($request->filled('limit')) $supports = $supports->skip($offset)->limit($limit);
        $supports = $supports->orderBy('id', 'desc')->get();
        if (count($supports) == 0) return api_response($request, null, 404);

        $supports->map(function (&$support) {
            $support['date'] = $support->created_at->format('M d');
            $support['time'] = $support->created_at->format('h:i A');
            return $support;
        });

        return api_response($request, $supports, 200, [
            'supports' => $supports,
            'filtered_supports' => $supports_without_limit->count(),
        ]);
    }

    /**
     * @param Request $request
     * @param $business
     * @param $support
     * @return JsonResponse
     */
    public function show(Request $request, $business, $support)
    {
        $support = $this->repo->find($support);
        if (!$support) return api_response($request, null, 404);

        $support['date'] = $support->created_at->format('M d');
        $support['time'] = $support->created_at->format('h:i A');

        /** @var Member $member */
        $member = $support->member;
        /** @var BusinessMember $business_member */
        $business_member = $member->businessMemberWithoutStatusCheck();
        $support['requested_by'] = [
            'name' => $member->profile->name,
            'image' => $member->profile->pro_pic,
            'designation' => $business_member->role ? $business_member->role->name : ''
        ];
        removeRelationsAndFields($support);

        return api_response($request, $support, 200, ['support' => $support]);
    }
}
