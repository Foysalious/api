<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Business\Support\Creator;
use Sheba\Business\Support\Updater;
use Sheba\Dal\Support\SupportRepositoryInterface;
use Sheba\ModificationFields;
use Sheba\Dal\Support\Model as Support;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Throwable;

class SupportController extends Controller
{
    use ModificationFields;

    /** @var SupportRepositoryInterface */
    private $repo;

    /**
     * SupportController constructor.
     * @param SupportRepositoryInterface $repo
     */
    public function __construct(SupportRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * @param Request $request
     * @param Creator $creator
     * @param MemberRepositoryInterface $member_repository
     * @return JsonResponse
     */
    public function store(Request $request, Creator $creator, MemberRepositoryInterface $member_repository)
    {
        $this->validate($request, ['description' => 'required|string']);

        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!$business_member) return api_response($request, null, 401);

        /** @var Member $member */
        $member = $member_repository->where('id', $business_member['member_id'])->first();
        $this->setModifier($member);

        /** @var Support $support */
        $support = $creator->setMember($member)->setBusinessId($business_member['business_id'])->setDescription($request->description)->create();

        return api_response($request, $support, 200, ['support' => ['id' => $support->id]]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'status' => 'string|in:open,closed',
            'limit' => 'numeric',
            'offset' => 'numeric'
        ]);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];

        list($offset, $limit) = calculatePagination($request);
        if (!$business_member) return api_response($request, null, 401);

        $supports = Support::where('member_id', $business_member['member_id'])
            ->select('id', 'member_id', 'status', 'long_description', 'created_at')
            ->orderBy('id', 'desc');

        if ($request->filled('status')) $supports = $supports->where('status', $request->status);
        if ($request->filled('limit')) $supports = $supports->skip($offset)->limit($limit);
        $supports = $supports->get();
        if (count($supports) == 0) return api_response($request, null, 404);

        $supports->map(function (&$support) {
            $support['date'] = $support->created_at->format('M d');
            $support['time'] = $support->created_at->format('h:i A');
            return $support;
        });

        return api_response($request, $supports, 200, ['supports' => $supports]);
    }

    public function show(Request $request, $support)
    {
        try {
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $support = $this->repo->find($support);
            if (!$support) return api_response($request, null, 404);
            $support['date'] = $support->created_at->format('M d');
            $support['time'] = $support->created_at->format('h:i A');
            return api_response($request, $support, 200, ['support' => $support]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function feedback(Request $request, $support, Updater $updater)
    {
        try {
            $this->validate($request, ['is_satisfied' => 'required|numeric|in:0,1',]);
            $auth_info = $request->auth_info;
            $business_member = $auth_info['business_member'];
            if (!$business_member) return api_response($request, null, 401);
            $support = $this->repo->find($support);
            if (!$support) return api_response($request, null, 404);
            $support = $updater->setSupport($support)->setSatisfaction($request->is_satisfied)->giveFeedback();
            if (!$support) return api_response($request, null, 500);
            return api_response($request, $support, 200);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
